<?php 

/*
* Menu data access layer 
*/
class MCPDAOMenu extends MCPDAO {
	
	/*
	* @param int menus id
	* @param array options 
	* @return array menu
	*/
	public function fetchMenu($intMenusId,$arrOptions=array()) {
		
		// get all links for the menu
		$strSQL = sprintf(
			'SELECT 
		           l.*  

		           #full URL path#
		           ,CASE
		               WHEN l.absolute_url IS NOT NULL
		               THEN l.absolute_url
		               
		               WHEN l.path IS NOT NULL
		               THEN CONCAT(:base_url,l.path)
		               
		               ELSE
		               NULL
		           END url
		           
		           #datasource info#
		           ,CASE
		               WHEN d.menu_links_id IS NOT NULL
		               THEN 1
		               
		               ELSE 0
		            END datasource
		           ,d.dao datasource_dao
		           ,d.method datasource_method
		           ,d.args datasource_args
		           ,d.description datasource_description
		           %s
		       FROM 
		           MCP_MENU_LINKS l
		        LEFT OUTER
		        JOIN
		           MCP_MENU_LINKS_DATASOURCES d
		          ON
		           l.menu_links_id = d.menu_links_id
		        LEFT OUTER
		        JOIN
		           MCP_MENU_LINKS_DYNAMIC v
		          ON
		           l.menu_links_id = v.menu_links_id
		       WHERE 
		           l.menus_id = :menus_id
		        AND
		           v.menu_links_id IS NULL
		        AND
		           l.deleted = 0'      
			,isset($arrOptions['select'])?",{$arrOptions['select']}":''
		);
		
		$arrLinks = $this->_objMCP->query(
			$strSQL
			,array(
				':menus_id'=>(int) $intMenusId
				,':base_url'=>'/index.php/'
			)
		);
		
		$arrFinalLinks = $arrLinks;
		
		if( !empty($arrLinks) ) {
			
			/*
			* Option required to include dynamic links 
			*/
			if( isset($arrOptions['dynamic_links']) && $arrOptions['dynamic_links'] === true) {
				foreach($arrLinks as $intIndex=>&$arrLink) {
					if($arrLink['datasource'] == 0) continue;
				
					// expand the datasource
					$arrDynamicLinks = $this->_expandDataSource($arrLink);
					
					// move the links up replacing datasource
					foreach($arrDynamicLinks as &$arrDynamicLink) {
						if($arrDynamicLink['parent_id'] === $arrLink['menu_links_id']) {
							$arrDynamicLink['parent_id'] = $arrLink['parent_id'];
						}
					}

					$arrFinalLinks = array_merge($arrFinalLinks,$arrDynamicLinks);
				
				}
			}
			
			/*
			* @todo: option to ommit datasource links replacing it with dynamic ones. This
			* will be needed to build the menu for display. 
			*/
			
			// parses menu into tree w/o multiple trips to the db
			$arrFinalLinks = $this->_toTree(
				 null
				,$arrFinalLinks
				,'menu_links_id'
				,'parent_id'
				,(isset($arrOptions['child_key'])?$arrOptions['child_key']:'menu_links')
			);
		
		}
		
		//echo '<pre>',print_r( $arrFinalLinks),'</pre>';
		return $arrFinalLinks;
		
		
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
		
		if( !is_numeric($mixLinkId) ) {
			return null;
		}
		
		$strSQL = 
		"SELECT 
		       l.* 
		       
		       #full URL path#
		       ,CASE
		          WHEN l.absolute_url IS NOT NULL
		          THEN l.absolute_url
		               
		          WHEN l.path IS NOT NULL
		          THEN CONCAT(:base_url,l.path)
		               
		          ELSE
		          NULL
		       END url
		       
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
				':menu_links_id'=>(int) $mixLinkId
				,':base_url'=>'/index.php/'
			)
		));
		
		// unserialize scalar BLOB fields
		foreach(array('mod_args','mod_cfg','datasource_args') as $strColumn) {
			$arrLink[$strColumn] = $arrLink[$strColumn] !== null?unserialize(base64_decode($arrLink[$strColumn])):null;
		}
		
		return $arrLink;
		
	}
	
	/*
	* Converts dynamic links to concrete ones
	* 
	* @param array raw dynamic links 
	* @param array datasource link data
	* @return array converted link
 	*/
	private function _convertDynamicLinksToConcrete($arrRawDynamicLinks,$arrDatasourceLink,$strParentId=null) {
		
		$arrRebuild = array();
		
		foreach( $arrRawDynamicLinks as $intIndex=>$arrDynamicLink) {
			
			$arrNewLink = array();
		
			foreach($arrDatasourceLink as $strColumn=>$mixValue) {
			
				// skip over datasource only columns
				if( in_array($strColumn,array('datasource','datasource_dao','datasource_method','datasource_args','datasource_description')) ) {
					continue;
				}
			
				$arrNewLink[$strColumn] = isset($arrDynamicLink[$strColumn])?$arrDynamicLink[$strColumn]:null;
				
				if( strcmp('menu_links_id',$strColumn) === 0 ) {
					
					$arrNewLink[$strColumn] = "dynamic-{$arrDatasourceLink['menu_links_id']}-{$arrDynamicLink['id']}";
					
				} else if( strcmp('parent_id',$strColumn) === 0 ) {
					
					if( $strParentId === null ) {
						$arrNewLink[$strColumn] = $arrDatasourceLink['menu_links_id'];
					} else {
						$arrNewLink[$strColumn] = $strParentId;
					}
				} else if( strcmp('menus_id',$strColumn) === 0 ) {
					
					$arrNewLink[$strColumn] = $arrDatasourceLink['menus_id'];
					
				}
			
			}
			
			$arrRebuild[] = $arrNewLink;
			
			if( isset($arrDynamicLink['menu_links']) ) {
				$arrRebuild = array_merge($arrRebuild,$this->_convertDynamicLinksToConcrete($arrDynamicLink['menu_links'],$arrDatasourceLink,$arrNewLink['menu_links_id']));
			}
		
		}
		
		return $arrRebuild;
		
	}
	
	/*
	* Get all the dynamic links for a specific datasource  
	* 
	* @param array data source link
	* @return array dynamic links
	*/
	private function _expandDataSource($arrDataSource) {
		
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
				
				,unserialize(base64_decode($arrDataSource['datasource_args']))
				
			)
		);
		
		// convert to concrete links
		$arrConcreteLinks = $this->_convertDynamicLinksToConcrete($arrRawDynamicLinks,$arrDataSource);
		
		return $arrConcreteLinks;
		
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
	
	public function testDatasource() {
		/*return array(
			array(
				'id'=>78
				,'display_title'=>"Test ".rand(1,100)
			)
			,array(
				'id'=>23
				,'display_title'=>"Test ".rand(1,100)
			)
			,array(
				'id'=>12
				,'display_title'=>"Test ".rand(1,100)
				,'menu_links'=>array(
					array(
						'id'=>786
						,'display_title'=>"Test ".rand(1,100)
						,'menu_links'=>array(
							array(
								'id'=>907
								,'display_title'=>'hello'
							)
						)
					)
				)
			)
		);*/
		
		
		/*$return = array();
		
		$num = rand(1,10);
		for($i=0;$i<$num;$i++) {
			$return[] = array(
				'id'=>$i
				,'display_title'=>"Test $i"
			);
		}
		
		return $return;*/
		
		
		$objDAONode = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP));
		
		
		return array_shift($objDAONode->listAll("n.node_title display_title,n.nodes_id id,'Component.Node.Module.View.Entry' mod_path,CONCAT('Component/Node.Module.View.Entry/',n.nodes_id) path",array('n.sites_id = :sites_id',':sites_id'=>$this->_objMCP->getSitesId()),'RAND()',"0,1"));
		
		
	}
	
}

?>