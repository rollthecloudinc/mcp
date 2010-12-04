<?php 
$this->import('App.Core.DAO');
/*
* Site data access layer 
*/
class MCPDAOSite extends MCPDAO {
	
	/*
	* List all sites
	* 
	* @param str select fields
	* @param str where clause
	* @param order by clause
	* @param limit clause
	* @return array users
	*/
	public function listAll($strSelect='s.*',$strFilter=null,$strSort=null,$strLimit=null) {
		
		/*
		* Build SQL 
		*/
		$strSQL = sprintf(
			'SELECT
			      %s %s
			   FROM
			      MCP_SITES s
			      %s
			      %s
			      %s'
			,$strLimit === null?'':'SQL_CALC_FOUND_ROWS'
			,$strSelect
			,$strFilter === null?'':"WHERE $strFilter"
			,$strSort === null?'':"ORDER BY $strSort"
			,$strLimit === null?'':"LIMIT $strLimit"
		);
		
		$arrSites = $this->_objMCP->query($strSQL);
		
		/*
		* Load extra XML data 
		*/
		foreach($arrSites as &$arrSite) {
			$arrSite = $this->_loadXMLSiteData($arrSite);
		}
		
		if($strLimit === null) {
			return $arrSites;
		}
		
		return array(
			$arrSites
			,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
		);
		
	}
	
	/*
	* Fetch site data by sites id 
	* 
	* @param int site id
	* @param str select fields
	* @return array site data
	*/
	public function fetchById($intId,$strSelect='*') {
		$strSQL = sprintf(
			'SELECT %s FROM MCP_SITES WHERE sites_id = %s'
			,$strSelect
			,$this->_objMCP->escapeString($intId)
		);	
			
		$arrSite = array_pop($this->_objMCP->query($strSQL));
		
		if($arrSite !== null) {
			$arrSite = $this->_loadXMLSiteData($arrSite);
		}
		
		return $arrSite;
		
	}
	
	/*
	* Update/insert data data - logic includes saving XML stored fields properly
	* 
	* @param array site data
	* @return int affected rows/sites id
	*/
	public function save($arrSite) {
		
		/*
		* Get fields native to sites table
		*/
		$schema = $this->_objMCP->query('DESCRIBE MCP_SITES');
		
		$native = array();
		foreach($schema as $column) {
			$native[] = $column['Field'];
		}
		
		/*
		* Siphon dynamic fields
		*/
		$dynamic = array();
		
		foreach(array_keys($arrSite) as $field) {
			if(!in_array($field,$native)) {
				$dynamic[$field] = $arrSite[$field];
				unset($arrSite[$field]);
			}
		}
		
		/*
		* When creating a new site generate a random salt
		* This is a one time only thing otherwise data corruption of encrypted values would occur.
		* The SALT should never be exposed to the front-end API.
		*/
		if(!isset($arrSite['sites_id'])) {
			$dynamic['site_salt'] = sha1(time().time().'nautica');
		}
		
		/*
		* Update/insert the site data stored in the db
		*/
		/*$intId = $this->_save(
			$arrSite
			,'MCP_SITES'
			,'sites_id'
			,array('site_name','site_directory','site_module_prefix')
			,'created_on_timestamp'
		);*/ 
		
		/*
		* When a new site is successfully created make site folder
		*/
		/*if(!isset($arrSite['sites_id']) && $intId) {
			
			$dir = ROOT.DS.'Site'.DS.$arrSite['site_directory'];
			
			/*
			* Attempt to create directory, if directory can't be created
			* an error either needs to be thrown or a message may need
			* to displayed telling the user to create the directory
			* manually.
			*/
			/*if(!mkdir($dir)) {
				
			}
		}*/
		
		/*
		* Save site data stored inside XML config file 
		*/
		$this->_saveXMLSiteData($dynamic);
		
		echo '<pre>',print_r($arrSite),'</pre>';
		echo '<pre>',print_r($dynamic),'</pre>';
		
	}
	
	/*
	* Load data for site not stored in database, such as domain, salt, etc
	* stored inside Main config file stored above site root.
	* 
	* @param array site
	* @return array site w/ mixin data
	*/
	private function _loadXMLSiteData($site) {
		
		/*
		* Load XML config file 
		*/
		$objXML = simplexml_load_file(CONFIG.'/Main.xml');
		
		/*
		* Get the site node 
		*/
		$node = array_pop($objXML->xpath("//site[@id='{$site['sites_id']}']"));
		
		if($node === null) return $site;
		
		/*
		* Recursive function to map XML to flat site array keys
		*/
		$func = function($node,$path,$func) use (&$site) {
			
			if(!$node->children()) {
				$site[$path] = (string) $node;
				return;
			}
			
			foreach($node->children() as $child) {
				call_user_func($func,$child,"{$path}_{$child->getName()}",$func);
			}
		};
		
		/*
		* Map XML data 
		*/
		call_user_func($func,$node,'site',$func);
		
		return $site;
		
	}
	
	/*
	* Save data stored inside XML configuration file
	* 
	* @param array site XML fields
	* @param int sites id
	*/
	private function _saveXMLSiteData($arrData,$intSitesId) {
		
	}
	
}
?>