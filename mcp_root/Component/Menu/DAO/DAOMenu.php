<?php 

/*
* Menu data access layer 
*/
class MCPDAOMenu extends MCPDAO {
	
	/*
	* Build navigation menu 
	* 
	* @param int menus id
	* @param int parent id: NULL = root level links for the menu
	* @param bool recursive?
	* @param bool accept cached version of COMPLETE menu?
	* @return array menu
	*/
	public function fetchMenu($intMenusId,$intParentId=null,$boolR=true,$boolCache=true,$arrOptions=array()) {
		
		$strSQL = sprintf(
			'SELECT 
		           %s
		       FROM 
		           MCP_MENU_LINKS l
		       WHERE 
		           l.menus_id = :menus_id 
		        AND
		           l.parent_id %s :parent_id
		        AND
		           l.deleted = 0'
		   	,isset($arrOptions['select'])?$arrOptions['select']:'l.*'
		   	,$intParentId !== null?'=':'IS'
		);
		
		$arrMenuLinks = $this->_objMCP->query(
			$strSQL
			,array(
				 ':menus_id'=>(int) $intMenusId
			    ,':parent_id'=>$intParentId === null?$intParentId:intval($intParentId)
			)
		);
		
		// when not recursive return links without parsing children
		if($boolR) {
			return $arrMenuLinks;
		}
		
		if(!empty($arrMenuLinks)) {
			foreach($arrMenuLinks as &$arrMenuLink) {
				$arrMenuLink[(isset($arrOptions['child_key'])?$arrOptions['child_key']:'menu_links')] = $this->fetchMenu($arrMenuLink['menus_id'],$arrMenuLink['menu_links_id'],$boolR,$boolCache,$arrOptions);
			}
		}
		
		return $arrMenuLinks;
		
	}
	
	/*
	* Get single menu by id
	* 
	* @param int menu id
	* @return arr menu data
	*/
	public function fetchMenuById($intId) {
		
		// uses old navigation - probably going to be the same anyway - don't anticpate the navigation table changing any
		$strSQL = 'SELECT *,navigation_id menus_id FROM MCP_NAVIGATION WHERE navigation_id = :menu_id';
		
		return array_pop($this->_objMCP->query(
			$strSQL
			,array(	
				':menu_id'=>(int) $intId
			)
		));
		
	}
	
	/*
	* Get data associated with a single menu link (dynamic or concrete) 
	* 
	* @param mix int=concrete link | str=dynamic link
	* @return array menu link data
	*/
	public function fetchLinkById($mixLinkId) {
		
		if( is_numeric($mixLinkId) ) {
			
			return $this->_fetchConcreteLinkById($mixLinkId);
			
		} else {
			
			$intBundleId = null;
			$intDatasourcesId = null;
			
			// extract the bundle id and data source id
			list($intBundleId,$intDatasourcesId) = explode('-',$mixLinkId,2);
			
			return $this->_fetchDynamicLinkById($intBundleId,$intDatasourcesId);
			
		}
		
	}
	
	/*
	* Create/update menu link
	* 
	* @param array link data
	*/
	public function saveLink($arrLink) {
		
		/*
		* Get fields native to table
		*/
		$schema = $this->_objMCP->query('DESCRIBE MCP_MENU_LINKS');
		
		$native = array();
		foreach($schema as $column) {
			$native[] = $column['Field'];
		}
		
		/*
		* Siphon other fields
		*/
		$dynamic = array();
		
		foreach(array_keys($arrLink) as $field) {
			if(!in_array($field,$native)) {
				$dynamic[$field] = $arrLink[$field];
				unset($arrLink[$field]);
			}
		}
		
		try {
			
			// start transaction
			$this->_objMCP->begin();
		
			$intId = $this->_save(
				$arrLink
				,'MCP_MENU_LINKS'
				,'menu_links_id'
				,array('display_title','browser_title','page_title','path','mod_path','mod_tpl','absolute_url','content_header','content_header_type','content_footer','content_footer_type')
				,'created_on_timestamp'
				,array('mod_args','mod_cfg','global_data')
				// ,array('display_title')
			);
		
			/*
			* When dealing with a data source uodate that also
			*/
			if( !empty($dynamic['datasource']) ) {
				
				$arrDataSource = array(
					 'menu_links_id'=>isset($arrLink['menu_links_id'])?$arrLink['menu_links_id']:$intId
					,'dao'=>$dynamic['datasource_dao']
					,'method'=>$dynamic['datasource_method']
					,'args'=>$dynamic['datasource_args']
					,'description'=>isset($dynamic['datasource_description'])?$dynamic['datasource_description']:null
				);
				
				$this->_save(
					$arrDataSource
					,'MCP_MENU_LINKS_DATASOURCES'
					,'menu_links_id'
					,array('dao','method','description')
					,null
					,array('args')
				);
				
			}
			
			// commit transaction
			$this->_objMCP->commit();
		
		} catch( MCPDBException $e) {
			
			// something went wrong - rollback transaction
			$this->_objMCP->rollback();
			
			// Throw more refined/specific exception 
			throw new MCPDAOException( $e->getMessage() );
			
		}
		
	}
	
	/*
	* Get concrete link by id
	* 
	* @param int links id
	* @return array link data
	*/
	private function _fetchConcreteLinkById($intId) {
		
		$strSQL = 
		"SELECT 
		       l.* 
		       
		       #datasource info#
               ,IF(d.menu_links_id IS NOT NULL,1,0) datasource
		       ,d.dao datasource_dao
		       ,d.method datasource_method
		       ,d.args datasource_args
		       ,d.description datasource_description
		       
		       #target resolution#
		       ,CASE
		       	  WHEN l.mod_path IS NOT NULL
		       	  THEN 'MODULE'
		       	  
		       	  ELSE 'URL'
		       END target
		       
		   FROM 
		       MCP_MENU_LINKS l 
		   LEFT OUTER
		   JOIN
		       MCP_MENU_LINKS_DATASOURCES d
		     ON
		      l.menu_links_id = d.menu_links_id     
          WHERE 
		      l.menu_links_id = :menu_links_id";
		
		$arrLink = array_pop($this->_objMCP->query(
			$strSQL
			,array(
				':menu_links_id'=>(int) $intId
			)
		));
		
		// unserialize scalar BLOB fields
		foreach(array('mod_args','mod_cfg','datasource_args') as $strColumn) {
			$arrLink[$strColumn] = $arrLink[$strColumn] !== null?unserialize(base64_decode($arrLink[$strColumn])):null;
		}
		
		return $arrLink;
		
	}
	
	/*
	* Get dynamic link by id
	* @param mix bundle id
	* @param int datasource id
	* @return array menu link data 
	*/
	private function _fetchDynamicLinkById($mixBundleId,$intDatasourcesId) {
		
		// Get datasource info
		$arrDataSource = $this->fetchLinkById($intDatasourcesId);
		
		// Get the dynamic links associated with the data source
		$mcp = $this->_objMCP;
		$arrRawDynamicLinks = call_user_func_array(
			array(
				 $this->_objMCP->getInstance($arrDataSource['datasource_dao'],array($this->_objMCP))
				,$arrDataSource['datasource_method']
			)
			,$arrDataSource['datasource_args'] === null?array():array_map(
			
				// arguments may contain magical keyword SITES_ID - replace with current site ID
				function($arg) use ($mcp) {
					return str_replace(
						 array('SITES_ID')
						,array( $mcp->getSitesId() )
						,$arg
					);
				}
				
				,base64_decode(unserialize($arrDataSource['datasource_args']))
				
			)
		);
		
		// Locate the single need we need to fetch
		$arrDynamicLink = $this->_fetchDynamicLinkFromDatasourceResultSet($mixBundleId,$arrRawDynamicLinks);
		
		// Once the link is found make it look like a standard concrete link (same interface besides for ID)
		$arrConvertedLink = $this->_convertDynamicLinkToConrete($arrDynamicLink);
		
	}
	
	/*
	* Locate a return single link inside datasource result set by the unique bundle id
	* 
	* @param mix bundle id
	* @param array datasource dynamic link result set
	*/
	private function _fetchDynamicLinkFromDatasourceRawResultSet($mixBundleId,$arrRawDynamicLinks) {
		
		$arrFound = null;
		
		foreach($arrRawDynamicLinks as $arrDynamicLink) {
			if($arrDynamicLink['id'] == $mixBundleId) {
				$arrFound = $arrDynamicLink;
				break;
			}
		}
		
		if($arrFound !== null) return $arrFound; 
		
		foreach($arrRawDynamicLinks as $arrDynamicLink) {
			$arrFound = $this->_fetchDynamicLinkFromDatasourceRawResultSet($arrDynamicLink['menu_links'],$mixBundleId);
			if($arrFound !== null) return $arrFound;
		}
		
		return $arrFound;
		
	}
	
	/*
	* Makes a dynamic link look like a concrete one
	* 
	* @param array raw dynamic link 
	* @param array datasource link data
	* @return array converted link
 	*/
	private function _convertDynamicLinkToConcrete($arrRawDynamicLink,$arrDatasourceLink) {
		
		$arrRebuild = array();
		foreach($arrDatasourceLink as $strColumn=>$mixValue) {
			
			// skip over datasource only columns
			if( in_array($strColumn,array('datasource','datasource_dao','datasource_method','datasource_args')) ) continue;
			
			$arrRebuild[$strColumn] = $mixValue;
			
		}
		
		return $arrRebuild;
		
	}
	
}

?>