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
		* 
		* NEVER overwrite the salt. The salt is used for one way encrypting users passwords. If
		* the salt is lost ALL passwords MUST be reset. other things may also rely on the salt
		* but the most obvious one is user passwords. A sites salt should always be used when data
		* encrytion is needed.
		*/
		if(!isset($arrSite['sites_id'])) {
			$dynamic['site_salt'] = sha1(time().time().'nautica');
		}
		
		/*
		* Update/insert the site data stored in the db
		*/
		$intId = $this->_save(
			$arrSite
			,'MCP_SITES'
			,'sites_id'
			,array('site_name','site_directory','site_module_prefix')
			,'created_on_timestamp'
		);
		
		/*
		* When updating existing site the return value of save is number of rows affected 
		*/
		$intId = isset($arrSite['sites_id'])?$arrSite['sites_id']:$intId;
		
		if( !$intId ) {
			// @todo: error or throw exception
			return;
		}
		
		/*
		* When a new site is successfully created make site folder
		*/
		if(!isset($arrSite['sites_id']) && $intId) {
			
			$dir = ROOT.DS.'Site'.DS.$arrSite['site_directory'];
			
			/*
			* Attempt to create directory, if directory can't be created
			* an error either needs to be thrown or a message may need
			* to displayed telling the user to create the directory
			* manually.
			*/
			if(!mkdir($dir)) {
				// @todo error or throw exception
				return;
			}
		}
		
		/*
		* Save site data stored inside XML config file 
		*/
		$this->_saveXMLSiteData($dynamic,$intId);
		
		//echo '<pre>',print_r($arrSite),'</pre>';
		//echo '<pre>',print_r($dynamic),'</pre>';
		
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
		
		// echo '<pre>',print_r($arrData),'</pre>';
		
		/*
		* The necessary modifications require DOMDocument over simplexml
		*/
		$objXML = new DOMDocument();
		
		/*
		* Load the XML site config file 
		*/
		if( $objXML->load( CONFIG.'/Main.xml' ) === false) {
			// @todo: error or throw exception when file can't be loaded
			return;
		}
		
		/*
		* Use xpath to determine whether a definition exists for the site
		* or not. When a definition exists for the site use that node
		* otherwise start a new.
		*/
		$objXPath = new DOMXPath( $objXML );
		
		/*
		* Attempt to locate a node for the sites configuration definition.
		*/
		$objResult = $objXPath->query("//site[@id='$intSitesId']");
		
		/*
		* Check to make sure the query was at least well formed, otherwise go no further. A 
		* well-formed query without a result will return an empty result set. On the otherhand,
		* a query that is malformed will return false. 
		*/
		if( $objResult === false ) {
			// @todo: error or throw some type of exception
			return;
		}
		
		// Get the matched node, this will be null if nothing was matched IE. definition for site doesn't exist
		$objSite = $objResult->item(0);
		
		/*
		* When a site doesn't exist configure a new site element node entry. Otherwise,
		* use the node that was matched and overwrite as necessary.
		*/
		if( $objSite === null) {
			
			/*
			* In this case create a new node 
			*/
			$objSite = new DOMElement('site');
			
			/*
			* Add the new node to the document and refresh/reset reference 
			*/
			$objSite = $objXML->documentElement->appendChild($objSite);
			
			/*
			* Add the id attribute 
			*/
			if( $objSite->setAttribute('id',(string) $intSitesId) === false) {
				// @todo: error or throw exception
				return;
			}
			
			/*
			* Mixin database authentication info based the default (site 0). For security
			* reasons this information is not controllable neither viewable from the front-end
			* interface. It is set one here when creating new sites and may be modified
			* manually, if need be.
			*/
			$arrData['site_db_pass'] = $objXPath->query("//site[@id='0']/db/pass")->item(0)->nodeValue;
			$arrData['site_db_user'] = $objXPath->query("//site[@id='0']/db/user")->item(0)->nodeValue;
			$arrData['site_db_host'] = $objXPath->query("//site[@id='0']/db/host")->item(0)->nodeValue;
			$arrData['site_db_db'] = $objXPath->query("//site[@id='0']/db/db")->item(0)->nodeValue;
			
		}
		
		/* -------------------------------------------------------------------------------------- */
		// Begin modifying/adding the actual data on the site node as neccessary
		
		/*
		* Array defines the available site entry child nodes 
		*/
		$arrStructure = array(
			 'domain'=>true
			,'db'=>array(
				'pass'=>true // perhaps good to use a default here? - for new entries
				,'user'=>true // perhaps good to use a default here? - for new entries
				,'host'=>true // perhaps good to use a default here? - for new entries
				,'db'=>true // perhaps good to use a default here? - for new entries
				,'adapter'=>true
			)
			,'salt'=>true
		);
		
		/*
		* Recursive function used to update and add necessary nodes and values
		* to the site node. This accounts for all cases including partial and full
		* updates or full create.
		*/
		$func = function($objNode,$arrStructure,$strAncestory,$func) use (&$arrData) {
			
			foreach($arrStructure as $strName => $mixValue) {
				
				// Get the corresponding node
				$objChild = $objNode->getElementsByTagName($strName)->item(0);
				
				if( is_array($mixValue) ) {
					
					// It need to be created at this point
					if( $objChild === null ) {
						$objChild = $objNode->appendChild( new DOMElement($strName) );
					}
					
					$func($objChild,$mixValue,"$strAncestory{$strName}_",$func);
					
				} else {
					
					// check that a value exists within the data array. If not move on.
					if( !isset( $arrData["$strAncestory$strName"] ) ) {
						continue;
					}
					
					// if the node doesn't exist create it
					if( $objChild === null ) {
						$objChild = $objNode->appendChild( new DOMElement($strName) );
					}
					
					// Set the value
					$objChild->nodeValue = (string) $arrData["$strAncestory$strName"];
					
				}
				
			}
			
		};
		
		$func($objSite,$arrStructure,'site_',$func);
		
		/*
		* Before save nicely format output 
		* 
		* Reload the XML to format it properly. There seems
		* to be an issue with formating the XML before
		* reloading it. None the less, this takes care of the issue
		* and properly formats the XML.
		*/
		$strXML = $objXML->saveXML();
		$objXML = new DOMDocument();
		$objXML->preserveWhiteSpace = false;
		$objXML->formatOutput = true;
		$objXML->loadXML($strXML);
		
		/*
		* Save XML back to file (test file for now)
		*/
		if( $objXML->save( CONFIG.'/Main.xml' ) === false) {
			// @todo: something went wrong - XML was not saved - throw exception or raise error
			// echo "<p>Could not write xml file</p>";
			// exit;
			return;
		}
		
		return;
		
		//return;
		/* -----------------------------------------------------------------------------------------------
		* TEST result: Seems to be functioning - test with new site
		* Seems to be working properly with new site and update - need to add
		* default info for database that is a copy of the site 0
		*/
		ob_clean();
		header('Content-Type: text/xml');
		echo $objXML->saveXML();
		exit;
		
		
	}
	
}
?>