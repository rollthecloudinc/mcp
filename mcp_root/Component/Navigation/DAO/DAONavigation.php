<?php 
/*
* Navigation data access layer 
*/
class MCPDAONavigation extends MCPDAO {
	
	/*
	* List all navigation menus 
	* 
	* @param str select columns
	* @param str where clause
	* @param str order by clause
	* @param str limit clause
	* @return array navigation menus
	*/
	public function listAllNavs($strSelect='n.*',$strWhere=null,$strSort=null,$strLimit=null) {
		$strSQL = sprintf(
			'SELECT 
			      %s %s 
			   FROM 
			      MCP_NAVIGATION n
               LEFT OUTER
               JOIN
                  MCP_USERS u
                 ON
                  n.users_id = u.users_id
               LEFT OUTER
               JOIN
                  MCP_SITES s
                 ON
                  n.sites_id = s.sites_id
			      %s 
			      %s 
			      %s'
			,$strLimit === null?'':'SQL_CALC_FOUND_ROWS'
			,$strSelect
			,$strWhere === null?'':"WHERE $strWhere"
			,$strSort === null?'':"ORDER BY $strSort"
			,$strLimit === null?'':"LIMIT $strLimit"
		);
		
		$arrRows = $this->_objMCP->query($strSQL);
		
		if($strLimit === null) {
			return $arrRows;
		} else {
			return array(
				$arrRows
				,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
			);
		}
	}
	
	/*
	* Get datasources dynamic link data
	* 
	* @param int navigation links id
	* @return array dynamic link data
	*/
	public function fetchDynamicLinks($intNavigationLinksId) {
		
		/*
		* Get navigation link datasource info 
		*/
		$arrLink = $this->fetchLinkById($intNavigationLinksId);
		
		/*
		* Get all dynamic hard links 
		*/
		$arrRows = $this->_objMCP->query(sprintf(
			'SELECT * FROM MCP_NAVIGATION_LINKS WHERE datasources_id = %s'
			,$this->_objMCP->escapeString($arrLink['navigation_links_id'])
		));
		
		$arrData = array();
		foreach($arrRows as $arrRow) {
			$arrData["{$arrRow['datasources_id']}-{$arrRow['datasources_row_id']}"] = $arrRow;
		}
				
		if(!empty($arrLink['datasource_query'])) {
				
			/*
			* Run raw SQL query for data
			*/
			$arrDynamicLinks = $this->_objMCP->query(str_replace(array('SITES_ID'),array($this->_objMCP->escapeString($this->_objMCP->getSitesId())),$arrLink['datasource_query']));
			
		} else {
				
			/*
			* Replace argument magical constants such as; SITES_ID and transform empty string to null
			*/
			$args = $arrLink['datasource_dao_args'] === null?array():unserialize(base64_decode($arrLink['datasource_dao_args']));
			array_walk(
				$args
				,create_function('&$item,$key,$mcp','if($item == \'\') { $item=null; return; } $item = str_replace(array(\'SITES_ID\'),array($mcp->escapeString($mcp->getSitesId())),$item);')
				,$this->_objMCP
			);
				
			/*
			* Get DAO instance and call binding 
			*/
			$arrDynamicLinks = call_user_func_array(
					array(
					$this->_objMCP->getInstance($arrLink['datasource_dao'],array($this->_objMCP))
					,$arrLink['datasource_dao_method']
				)
				,$args
			);
			
		}
		
		/*
		* Convert links 
		*/
		return empty($arrDynamicLinks)?array():$this->_convertDataSourceOutputToLinks($arrDynamicLinks,$arrLink,$arrData,($arrLink['parent_type'] == 'link'?$this->fetchLinkById($arrLink['parent_id']):null),$arrLink);
	}
	
	/*
	* Fetch menu by id
	* 
	* @param int navigation id
	* @param str select
	* @return arr navigation data
	*/
	public function fetchNavById($intId,$strSelect='*') {
		$strSQL = sprintf(
			'SELECT %s FROM MCP_NAVIGATION WHERE navigation_id = %s'
			,$strSelect
			,$this->_objMCP->escapeString($intId)
		);
		return array_pop($this->_objMCP->query($strSQL));
	}
	
	/*
	* Fetch navigation link by id
	* 
	* @param int links id
	* @param str columns to select
	*/
	public function fetchLinkById($intId,$strSelect='*') {
		$strSQL = sprintf(
			'SELECT %s FROM MCP_NAVIGATION_LINKS WHERE navigation_links_id = %s'
			,$strSelect
			,$this->_objMCP->escapeString($intId)
		);
		return array_pop($this->_objMCP->query($strSQL));		
	}
	
	/*
	* Get navigation link by route
	* 
	* @param str module name
	* @param int sites id
	* @return array route data 
	*/
	public function fetchRoute($strSitesInternalUrl,$intSitesId) {
		$strSQL = sprintf(
			"SELECT * FROM MCP_NAVIGATION_LINKS WHERE sites_internal_url = '%s' AND sites_id = %s AND deleted = 0"
			,$this->_objMCP->escapeString($strSitesInternalUrl)
			,$this->_objMCP->escapeString($intSitesId)
		);
		
		return array_pop($this->_objMCP->query($strSQL));
	}
	
	/*
	* Get dynamic link by id [datasource,datasource row id]
	* 
	* @param int datasources id
	* @param int datasources row id
	* @return array dynamic link data
	*/
	public function fetchDynamicLinkById($intDataSourcesId,$intDataSourcesRowId) {
		
		/*
		* Fetch all dynamic links for data source 
		*/
		$arrData = $this->fetchDynamicLinks($intDataSourcesId);
		
		/*
		* Locate link 
		*/
		$arrFound = $this->_locateDynamicLink($arrData,$intDataSourcesRowId);
		return $arrFound;
		
	}
	
	/*
	* Fetch menus by site and location
	* 
	* @param str valid menu location [top,left,bottom,right]
	* @param int sites id - null identifies master
	* @return array navigation menu data
	*/
	public function fetchNavBySiteLocation($strLocation,$intSitesId=null) {
		$strSQL = sprintf(
			"SELECT * FROM MCP_NAVIGATION WHERE menu_location = '%s' AND sites_id %s AND deleted = 0"
			,$this->_objMCP->escapeString($strLocation)
			,$intSitesId == null?' IS NULL':"= {$this->_objMCP->escapeString($intSitesId)}"
		);
		return array_pop($this->_objMCP->query($strSQL));
	}
	
	/*
	* Build navigation menu 
	* 
	* @param int parent id
	* @param str parent type [nav,link]
	* @param bool recursive?
	* @return array menu
	*/
	public function fetchMenu($intParentId,$strParentType='nav',$boolR=true) {
		
		/*
		* Locate all real navigation links 
		*/
		$strSQL = sprintf(
			"SELECT * FROM MCP_NAVIGATION_LINKS WHERE parent_type = '%s' AND parent_id = %s AND deleted = 0 ORDER BY sort_order ASC"
			,$this->_objMCP->escapeString($strParentType)
			,$this->_objMCP->escapeString($intParentId)
		);
		
		$arrNavigationLinks = $this->_objMCP->query($strSQL);
		
		$arrParentLink = strpos($strParentType,'link') === 0?$this->fetchLinkById($intParentId):null;
		$arrDynamicLinks = array();
		$arrDataSources = array();
		$arrPlaceholders = array();
		
		/*
		* Locate all dynamic links for parent link 
		*/
		if($arrParentLink !== null && $arrParentLink['datasources_row_id'] !== null) {
			$arrDynamicLinks = $this->fetchDynamicLinkById($arrParentLink['datasources_id'],$arrParentLink['datasources_row_id']);
			$arrDynamicLinks = $arrDynamicLinks?$arrDynamicLinks['navigation_links']:array();
		}
		
		/*
		* Replace all real navigation links placeholders with real-time data 
		*/
		foreach($arrNavigationLinks as &$arrLink) {
			if($arrLink['datasources_row_id'] !== null) {
				$arrLink = $this->fetchDynamicLinkById($arrLink['datasources_id'],$arrLink['datasources_row_id']);
				$arrPlaceholders["{$arrLink['datasources_id']}-{$arrLink['datasources_row_id']}"] = $arrLink;
			}
		}
		
		/*
		* Parse out datasource links 
		*/
		foreach($arrNavigationLinks as $intIndex=>&$arrLink) {
			if(isset($arrLink['datasource_dao']) || isset($arrLink['datasource_query'])) {
				$arrDynamicLinks = array_merge($arrDynamicLinks,$this->fetchDynamicLinks($arrLink['navigation_links_id']));
				array_unshift($arrDataSources,$intIndex);
			}
		}
		
		/*
		* Add dynamic links without hard link reference to end of navigation link array 
		*/
		foreach($arrDynamicLinks as $arrDynamicLink) {
			
			$strId = "{$arrDynamicLink['datasources_id']}-{$arrDynamicLink['datasources_row_id']}";
			$boolDataSource = $arrParentLink && (isset($arrParentLink['datasource_dao']) || isset($arrParentLink['datasource_query']))?true:false;
			
			/*
			* Support moving dynamic navigation links to new parent 
			*/
			if(!isset($arrPlaceholders[$strId]) && 
			   ($arrParentLink === null || ($boolDataSource === false && $arrDynamicLink['parent_id'] == $arrParentLink['navigation_links_id']) || ($boolDataSource === false && $arrDynamicLink['parent_id'] == $arrParentLink['parent_id']))
			) {
				$arrNavigationLinks[] = $arrDynamicLink;
			}
		}
		
		/*
		* Remove all datasource links 
		*/
		foreach($arrDataSources as $intIndex) {
			array_splice($arrNavigationLinks,$intIndex,1);
		}
		
		// when not recursive return links without parsing children
		if($boolR === false) return $arrNavigationLinks;
		
		if(!empty($arrNavigationLinks)) {
			foreach($arrNavigationLinks as &$arrNavigationLink) {
				$arrNavigationLink['navigation_links'] = !is_numeric($arrNavigationLink['navigation_links_id'])?$arrNavigationLink['navigation_links']:$this->fetchMenu($arrNavigationLink['navigation_links_id'],'link',true);
			}
		}
		
		return $arrNavigationLinks;
		
	}
	
	/*
	* Fetch links ancestory
	* 
	* @param int navigation links id
	* @return array navigation links
	*/
	public function fetchAncestory($intLinksId) {
		
		$arrLinks = array();
		while($arrLink = $this->fetchLinkById($intLinksId)) {
			
			if($arrLink['datasources_row_id'] !== null) {
				$arrLink = $this->fetchDynamicLinkById($arrLink['datasources_id'],$arrLink['datasources_row_id']);
			}
			
			array_unshift($arrLinks,$arrLink);
			$intLinksId = strcmp($arrLink['parent_type'],'link') == 0?$arrLink['parent_id']:0;
			
		}
		
		return $arrLinks;
		
	}
	
	/*
	* Get menu data that link belongs to, regardless of links depth
	* 
	* @param int links id
	* @return array menu data
	*/
	public function fetchNavByLinkId($intLinkId) {
		
		/*
		* Get links ancestory 
		*/
		$arrAncestory = $this->fetchAncestory($intLinkId);
		
		/*
		* Get the top link
		*/
		$arrTopLink = array_shift($arrAncestory);
		
		/*
		* Get menu for top link 
		*/
		return $this->fetchNavById($arrTopLink['parent_id']);
		
	}
	
	/*
	* Get list of all available navigation menu locations
	* 
	* @return array navigation menu locations
	*/
	public function fetchNavLocations() {
		$arrResult = $this->_objMCP->query('DESCRIBE MCP_NAVIGATION');
		$arrLocations = array();
		
		foreach($arrResult as $arrColumn) {
			if(strcmp('menu_location',$arrColumn['Field']) == 0) {
				foreach(explode(',',str_replace("'",'',trim(trim($arrColumn['Type'],'enum('),')'))) as $strLocation) {
					$arrLocations[] = array('value'=>$strLocation,'label'=>$strLocation);
				}
			}
		}
		
		return $arrLocations;
	}
	
	/*
	* Get list of all available navigation links window targets
	* 
	* @return array navigation links targets
	*/
	public function fetchLinksTargetWindows() {
		$arrResult = $this->_objMCP->query('DESCRIBE MCP_NAVIGATION_LINKS');
		
		foreach($arrResult as $arrColumn) {
			if(strcmp('target_window',$arrColumn['Field']) == 0) {
				return explode(',',str_replace("'",'',trim(trim($arrColumn['Type'],'enum('),')')));
			}
		}
		
		return array();
	}
	
	/*
	* Fetch available content types for links
	* 
	* @param str content link field
	* @return array content types
	*/
	public function fetchLinksContentTypes($strField) {
		
		$arrResult = $this->_objMCP->query('DESCRIBE MCP_NAVIGATION_LINKS');
		
		foreach($arrResult as $arrColumn) {
			if(strcmp($strField,$arrColumn['Field']) == 0) {
				return explode(',',str_replace("'",'',trim(trim($arrColumn['Type'],'enum('),')')));
			}
		}
		
		return array();
	}
	
	/*
	* Insert or update navigation menu
	*/
	public function saveNav($arrNav) {	
		return $this->_save(
			$arrNav
			,'MCP_NAVIGATION'
			,'navigation_id'
			,array('menu_title','menu_location','system_name')
			,'created_on_timestamp'
		);	
	}
	
	/*
	* Insert or update navigation link
	*/
	public function saveLink($arrLink) {		
		$this->_save(
			$arrLink
			,'MCP_NAVIGATION_LINKS'
			,'navigation_links_id'
			,array('parent_type','link_title','browser_title','page_heading','link_url','sites_internal_url','target_module','target_template','target_window','new_window_name','header_content','body_content','footer_content','header_content_type','body_content_type','footer_content_type','datasource_query','datasource_dao','datasource_dao_method')
			,'created_on_timestamp'
			,array('target_module_args','target_module_config','links_data','datasource_dao_args')
		);	
	}
	
	/*
	* Create hard link to represent dynamic link
	* 
	* @param int datasources id
	* @param int datasources row id
	*/
	public function createHardLinkFromDynamic($intDatasourcesId,$intDatasourcesRowId) {
		
		/*
		* Get dynamic link info
		*/
		$arrDataSource = $this->fetchDynamicLinkById($intDatasourcesId,$intDatasourcesRowId);//$this->fetchLinkById($intDatasourcesId);
		
		/*
		* Copy datasources information 
		*/
		$arrSave = $arrDataSource;
		
		/*
		* Set datasources id and row id for dynamic link
		*/
		//$arrSave['datasources_id'] = $arrDataSource['navigation_links_id'];
		//$arrSave['datasources_row_id'] = $intDatasourcesRowId;		
		$arrSave['link_title'] = '';
		
		/*
		* This info will be resolved dynamically for null values at menu request time
		*/
		unset(
		    $arrSave['navigation_links_id']
		    ,$arrSave['browser_heading']
		    ,$arrSave['page_heading']
		    ,$arrSave['link_url']
		    ,$arrSave['sites_internal_url']
		    ,$arrSave['target_module']
		    ,$arrSave['target_module_template']
		    ,$arrSave['target_module_args']
		    ,$arrSave['target_module_config']
		    ,$arrSave['header_content']
		    ,$arrSave['body_content']
		    ,$arrSave['footer_content']
		    ,$arrSave['header_content_type']
		    ,$arrSave['body_content_type']
		    ,$arrSave['footer_content_type']
		    ,$arrSave['target_window']
		    ,$arrSave['new_window_name']
		    ,$arrSave['links_data']
			,$arrSave['datasource_query']
			,$arrSave['datasource_dao']
			,$arrSave['datasource_dao_method']
			,$arrSave['datasource_dao_args']
			,$arrSave['updated_on_timestamp']
			,$arrSave['created_on_timestamp']
			,$arrSave['navigation_links']
			,$arrSave['dynamic_vars']
		);
		
		/*
		* Create new link 
		*/
		return $this->saveLink($arrSave);
	
	}
	
	/*
	* Moves link down one
	* 
	* @param int navigation links id
	* @return affected rows
	*/
	public function moveLinkDown($intLinksId) {
		
		/*
		* Get links data 
		*/
		$arrTarget = $this->fetchLinkById($intLinksId);
		
		/*
		* Get all links 
		*/
		$arrLinks = $this->fetchMenu($arrTarget['parent_id'],$arrTarget['parent_type'],false);
		$arrIds = array();
		
		/*
		* collect all link ids
		*/
		$boolMove = false;
		foreach($arrLinks as $intIndex=>$arrLink) {
			
			if($arrLink['navigation_links_id'] == $arrTarget['navigation_links_id'] && $intIndex != (count($arrLinks)-1)) {
				$boolMove = true;
				continue;
			}
			
			$arrIds[] = $arrLink['navigation_links_id'];
			
			if($boolMove === true) {
				$boolMove = false;
				$arrIds[] = $arrTarget['navigation_links_id'];
			}
		}
		
		$arrUpdate = array();
		foreach($arrIds as $intSort=>$intId) {
			$arrUpdate[] = sprintf('(%u,%u)',$intId,$intSort);
		}
		
		$strSQL = sprintf(
			'INSERT IGNORE INTO MCP_NAVIGATION_LINKS (navigation_links_id,sort_order) VALUES %s ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order)'
			,implode(',',$arrUpdate)
		);
		
		$this->_objMCP->query($strSQL);
		return 1;
		
	}
	
	/*
	* Moves link up one
	* 
	* @param int navigation links id
	* @return affected rows
	*/
	public function moveLinkUp($intLinksId) {
		
		/*
		* Get links data 
		*/
		$arrTarget = $this->fetchLinkById($intLinksId);
		
		/*
		* Get all links 
		*/
		$arrLinks = $this->fetchMenu($arrTarget['parent_id'],$arrTarget['parent_type'],false);
		$arrIds = array();
		
		/*
		* collect all link ids
		*/
		$boolSkip = false;
		foreach($arrLinks as $intIndex=>$arrLink) {
			
			if($boolSkip === true) {
				$boolSkip = false;
				continue;
			}
			
			if(isset($arrLinks[($intIndex+1)]) && $arrLinks[($intIndex+1)]['navigation_links_id'] == $arrTarget['navigation_links_id']) {
				$boolSkip = true;
				$arrIds[] = $arrTarget['navigation_links_id'];
			}
			
			$arrIds[] = $arrLink['navigation_links_id'];
			
		}
		
		$arrUpdate = array();
		foreach($arrIds as $intSort=>$intId) {
			$arrUpdate[] = sprintf('(%u,%u)',$intId,$intSort);
		}
		
		$strSQL = sprintf(
			'INSERT IGNORE INTO MCP_NAVIGATION_LINKS (navigation_links_id,sort_order) VALUES %s ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order)'
			,implode(',',$arrUpdate)
		);
		
		$this->_objMCP->query($strSQL);
		return 1;
		
	}
	
	/*
	* Delete a link 
	* 
	* @param int navigation links id
	* @return int affected rows
	*/
	public function deleteLink($intLinksId) {
		
		/*
		* Get links data 
		*/
		$arrTarget = $this->fetchLinkById($intLinksId);
		
		/*
		* Get all links 
		*/
		$arrLinks = $this->fetchMenu($arrTarget['navigation_links_id'],'link');
		$objIds = new ArrayObject(array($arrTarget['navigation_links_id']));
		
		/*
		* recursive function to collect all child link ids 
		*/
		$func = create_function('$value,$index,$ids','if(strcmp(\'navigation_links_id\',$index) == 0) $ids[] = $value;');
		
		/*
		* Collect all child ids 
		*/
		array_walk_recursive($arrLinks,$func,$objIds);
		
		/*
		* Collect ids into normal array to use implode 
		*/
		$arrIds = array();
		foreach($objIds as $intId) {
			$arrIds[] = $intId;
		}
		
		/*
		* Create SQL 
		*/
		$strSQL = sprintf(
			//'DELETE FROM MCP_NAVIGATION_LINKS WHERE navigation_links_id IN (%s)'
			'UPDATE
			       MCP_NAVIGATION_LINKS
			    SET
			       MCP_NAVIGATION_LINKS.deleted = NULL
			  WHERE
			       MCP_NAVIGATION_LINKS.navigation_links_id IN (%s)'
			,$this->_objMCP->escapeString(implode(',',$arrIds))
		);
		
		return $this->_objMCP->query($strSQL);
	
	}
	
	/*
	* Remove navigation link
	* 
	* @param int navigation links id
	* @return int affected rows
	*/
	public function removeLink($intLinksId) {
		
		/*
		* Get links data 
		*/
		$arrTarget = $this->fetchLinkById($intLinksId);
		
		/*
		* Get targets children
		*/
		$arrChildren = $this->fetchMenu($arrTarget['navigation_links_id'],'link',false);
		
		/*
		* Get targets siblings
		*/
		$arrLinks = $this->fetchMenu($arrTarget['parent_id'],$arrTarget['parent_type'],false);
		
		/*
		* reorder array 
		*/
		$arrIds = array();
		
		foreach($arrLinks as $arrLink) {
			
			/*
			* Replace links position with children 
			*/
			if($arrLink['navigation_links_id'] == $arrTarget['navigation_links_id']) {
				foreach($arrChildren as $arrChild) {
					$arrIds[] = $arrChild['navigation_links_id'];
				}
				continue;	
			}
			
			$arrIds[] = $arrLink['navigation_links_id'];
		}
		
		/*
		* Build update 
		*/
		$arrUpdate = array();
		foreach($arrIds as $intIndex=>$intId) {
			$arrUpdate[] = sprintf(
				"(%s,%s,'%s',%s)"
				,$this->_objMCP->escapeString($intId)
				,$this->_objMCP->escapeString($arrTarget['parent_id'])
				,$this->_objMCP->escapeString($arrTarget['parent_type'])
				,$this->_objMCP->escapeString($intIndex)
			);
		}
		
		/*
		* Build update query 
		*/
		$strSQL = sprintf(
			'INSERT IGNORE INTO MCP_NAVIGATION_LINKS (navigation_links_id,parent_id,parent_type,sort_order) VALUES %s ON DUPLICATE KEY UPDATE parent_id=VALUES(parent_id),parent_type=VALUES(parent_type),sort_order=VALUES(sort_order)'
			,implode(',',$arrUpdate)
		);
		
		/*
		* Create delete query (soft-delete)
		*/
		$strSQLDelete = sprintf(
			//'DELETE FROM MCP_NAVIGATION_LINKS WHERE navigation_links_id = %s'
			'UPDATE 
			      MCP_NAVIGATION_LINKS
			    SET 
			      MCP_NAVIGATION_LINKS.deleted = NULL 
			  WHERE 
			      MCP_NAVIGATION_LINKS.navigation_links_id = %s'
			,$this->_objMCP->escapeString($arrTarget['navigation_links_id'])
		);
		
		/*
		* Delete link and update children 
		*/
		$this->_objMCP->query($strSQLDelete);
		$this->_objMCP->query($strSQL);
		
		return 1;
		
		
	}
	
	/*
	* Delete navigation menu(s)
	* 
	* @param mix single integer value or array of integers ( MCP_NAVIGATION primary key )
	*/
	public function deleteNavs($mixNavigationId) {
		
		$strSQL = sprintf(
			'UPDATE
			      MCP_NAVIGATION
			    SET
			      MCP_NAVIGATION.deleted = NULL
			  WHERE
			      MCP_NAVIGATION.navigation_id IN (%s)'
			      
			,is_array($mixNavigationId) ? $this->_objMCP->escapeString(implode(',',$mixNavigationId)) : $this->_objMCP->escapeString($mixNavigationId)
		);
		
		echo "<p>$strSQL</p>";
		
	}
	
	/*
	* Converts data source data to dynamic links recursive
	* 
	* @param array data
	* @param array datasource link
	* @param array datasource parent
	* @param array 
	* @return array links
	*/
	private function _convertDataSourceOutputToLinks($arrData,$arrLink,$arrDataSourcesData,$arrParentLink=null,$arrTrueParent,$i=0) {
		
		$arrReturn = array();
		
		foreach($arrData as $arrDynamicLink) {
			
			$link = array(		
			
				'navigation_links_id'=>"{$arrLink['navigation_links_id']}-{$arrDynamicLink['id']}"
			
				,'link_title'=>$arrDynamicLink['label']
				,'datasources_row_id'=>$arrDynamicLink['id']
				
				,'sites_id'=>$arrLink['sites_id']
				,'creators_id'=>$arrLink['creators_id']	
				,'parent_type'=>$arrLink['parent_type']
				,'parent_id'=>$i==0?$arrLink['parent_id']:$arrTrueParent['navigation_links_id']
				,'parent_type'=>$i==0?$arrLink['parent_type']:$arrTrueParent['parent_type']
				,'datasources_id'=>$arrLink['navigation_links_id']			
						
				,'sites_internal_url'=>$arrParentLink !== null?$arrParentLink['sites_internal_url']:$arrLink['sites_internal_url']
				,'link_url'=>$arrParentLink !== null?$arrParentLink['link_url']:$arrLink['link_url']			
				,'page_heading'=>$arrParentLink !== null?$arrParentLink['page_heading']:$arrLink['page_heading']
				,'target_window'=>$arrParentLink !== null?$arrParentLink['target_window']:$arrLink['target_window']
				,'browser_title'=>$arrParentLink !== null?$arrParentLink['browser_title']:$arrLink['browser_title']		
				,'target_module'=>$arrParentLink !== null?$arrParentLink['target_module']:$arrLink['target_module']
				,'target_template'=>$arrParentLink !== null?$arrParentLink['target_template']:$arrLink['target_template']
				,'target_module_args'=>$arrParentLink !== null?$arrParentLink['target_module_args']:$arrLink['target_module_args']
				,'target_module_config'=>$arrParentLink !== null?$arrParentLink['target_module_config']:$arrLink['target_module_config']
				
				,'header_content_type'=>$arrLink['header_content_type']
				,'footer_content_type'=>$arrLink['footer_content_type']
				,'body_content_type'=>$arrLink['body_content_type']
				,'header_content'=>$arrLink['header_content']
				,'footer_content'=>$arrLink['footer_content']
				,'body_content'=>$arrLink['body_content']
				
				,'datasource_query'=>null
				,'datasource_dao'=>null
				,'datasource_dao_method'=>null
				,'datasource_dao_args'=>null
				
				,'sort_order'=>0
				,'dynamic_vars'=>isset($arrDynamicLink['vars'])?explode(',',$arrDynamicLink['vars']):array($arrDynamicLink['id'])
			);
			
			/*
			* Override default link data with hard link data 
			*/
			if(isset($arrDataSourcesData["{$arrLink['navigation_links_id']}-{$arrDynamicLink['id']}"])) {
				
				$arrHardLink = $arrDataSourcesData["{$arrLink['navigation_links_id']}-{$arrDynamicLink['id']}"];
				
				foreach($link as $strField=>&$mixValue) {
					switch($strField) {
						case 'navigation_links_id':
							$mixValue = $arrHardLink[$strField];
							break;
							
						case 'parent_id':
							$mixValue = $arrHardLink[$strField];
							break;
							
						case 'parent_type':
							$mixValue = $arrHardLink[$strField];
							break;
							
						case 'sort_order':
							$mixValue = $arrHardLink[$strField];
							break;
							
						default:
					}
				}
			}
			
			$link['navigation_links'] = isset($arrDynamicLink['children'])?$this->_convertDataSourceOutputToLinks($arrDynamicLink['children'],$arrLink,$arrDataSourcesData,$arrParentLink,$link,($i+1)):array();
			$arrReturn[] = $link;
			
		}
		
		return $arrReturn;
		
	}
	
	/*
	* Locate link
	*/
	private function _locateDynamicLink($arrData,$intDataSourcesRowId) {
		
		$arrFound = null;
		
		foreach($arrData as $arrDynamicLink) {
			if($arrDynamicLink['datasources_row_id'] == $intDataSourcesRowId) {
				$arrFound = $arrDynamicLink;
				break;
			}
		}
		
		if($arrFound !== null) return $arrFound; 
		
		foreach($arrData as $arrDynamicLink) {
			$arrFound = $this->_locateDynamicLink($arrDynamicLink['navigation_links'],$intDataSourcesRowId);
			if($arrFound !== null) return $arrFound;
		}
		
		return $arrFound;
		
	}
	
}
?>