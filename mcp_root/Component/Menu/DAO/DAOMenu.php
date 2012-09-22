<?php 

/*
* Menu data access layer 
*/
class MCPDAOMenu extends MCPDAO {
	
	/*
	* List all navigation menus 
	* 
	* @param str select columns
	* @param str where clause
	* @param str order by clause
	* @param str limit clause
	* @return array navigation menus
	*/
	public function listAllMenus($strSelect='m.*',$strWhere=null,$strSort=null,$strLimit=null) {
		
		$strSQL = sprintf(
			'SELECT 
			      %s %s 
			   FROM 
			      MCP_MENUS m
               LEFT OUTER
               JOIN
                  MCP_USERS u
                 ON
                  m.users_id = u.users_id
               LEFT OUTER
               JOIN
                  MCP_SITES s
                 ON
                  m.sites_id = s.sites_id
			      %s 
			      %s 
			      %s'
			,$strLimit === null?'':'SQL_CALC_FOUND_ROWS'
			,$strSelect
			,$strWhere === null?'':"WHERE $strWhere"
			,$strSort === null?'':"ORDER BY $strSort"
			,$strLimit === null?'':"LIMIT $strLimit"
		);
		
		// echo "<p>$strSQL</p>";
		
		$arrRows = $this->_objMCP->query($strSQL);
		
		// echo '<pre>',print_r($arrRows),'</pre>';
		
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
	* @param int menus id
	* @param array options 
	* 
	* 
	* @return array menu
	*/
	public function fetchMenu($intMenusId,$arrOptions=array()) {
		
		// get all links for the menu
		$strSQL = sprintf(
			'SELECT 
		           l.*  
		           
		           #all links physically stored in table are not dynamic#
		           ,0 dynamic

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
		       WHERE 
		           l.menus_id = :menus_id
		        AND
		           l.deleted = 0
                      ORDER
                         BY
                          l.weight ASC'      
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
			
			/*
			* Mixin permissions before converting to a tree because it would be very tedious
			* afterwards, collecting all ids and reapplying when it can easily be done here while
			* the result is in raw form. 
			* 
			* NOTE: execlude dynamic links
			*/
			if( isset($arrOptions['include_perms']) && $arrOptions['include_perms'] === true) {
			
				$ids = array();
				foreach($arrFinalLinks as &$arrLink) {
					if($arrLink['dynamic']) continue; // skip over dynamic links
					$ids[] = $arrLink['menu_links_id'];
				}
				
				// Get the permissions (only amounts to three queries regardless of number of links)
				$arrRead = $this->_objMCP->getPermission(MCP::READ,'MenuLink',$ids);
				$arrDelete = $this->_objMCP->getPermission(MCP::DELETE,'MenuLink',$ids);
				$arrEdit = $this->_objMCP->getPermission(MCP::EDIT,'MenuLink',$ids);
				
				// add is global for menu
				$add = $this->_objMCP->getPermission(MCP::ADD,'MenuLink',$intMenusId);
				
				// Apply permissions to links
				foreach($arrFinalLinks as &$arrLink) {
					if($arrLink['dynamic']) {
						$arrLink['allow_read'] = false;
						$arrLink['allow_delete'] = false;
						$arrLink['allow_edit'] = false;
						$arrLink['allow_add'] = false;
						continue;
					}
					$arrLink['allow_read'] = $arrRead[$arrLink['menu_links_id']]['allow'];
					$arrLink['allow_delete'] = $arrDelete[$arrLink['menu_links_id']]['allow'];
					$arrLink['allow_edit'] = $arrEdit[$arrLink['menu_links_id']]['allow'];
					$arrLink['allow_add'] = $add['allow'];
				}
				
			}
			
			//echo '<pre>',print_r($arrFinalLinks),'</pre>';
			
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
	* Fetch all links recursive
	* 
	* @param int parent id
	* @param str parent type [menu or link]
	* @param bool recursive
	* @param array option set for selecting specific columns, adding filters or changing default sort order
	* @return array links
	*/
	public function fetchLinks($intParentId,$strParentType='menu',$boolR=true,$arrOptions=null) {
		
		/*
		* Build SQL 
		*/
		$strSQL = sprintf(
			"SELECT
			      m.menu_links_id tmp_id
                              ,%s
			   FROM
			      MCP_MENU_LINKS m
			  WHERE
			  	  %s
			      m.parent_id %s
			      %s
			      %s"
			,$arrOptions !== null && isset($arrOptions['select'])?$arrOptions['select']:'m.*'
			
			,strcasecmp('menu',$strParentType) === 0?"m.menus_id = ".((int) $intParentId)." AND ":''
			,strcasecmp('menu',$strParentType) === 0?'IS NULL':" = ".((int) $intParentId)
			
			,$arrOptions !== null && isset($arrOptions['filter'])?"AND {$arrOptions['filter']}":''
			,$arrOptions !== null && isset($arrOptions['sort'])?"ORDER BY {$arrOptions['sort']}":''
		);
		
                // $this->_objMCP->debug(var_dump($boolR,true));
		
		/*
		* Fetch links 
		*/
		$arrLinks = $this->_objMCP->query($strSQL);
                
                // echo '<pre>'.print_r($arrTerms,true).'</pre>';
		
		/*
		* Recure 
                * 
                * Loose type so that argument can be defined in XML as 0 or 1 
		*/
		if($boolR) {
			foreach($arrLinks as &$arrLink) {
				$children = $arrOptions !== null && isset($arrOptions['children'])?$arrOptions['children']:'links';
                                $arrLink[$children] = $this->fetchLinks($arrLink['tmp_id'],'link',$boolR,$arrOptions);
                                unset($arrLink['tmp_id']);
			}
                }
		
		return $arrLinks;	
		
	}
	
	/*
	* Get single menu by id
	* 
	* @param int menu id
	* @return arr menu data
	*/
	public function fetchMenuById($intId) {
		
		$strSQL = 'SELECT * FROM MCP_MENUS WHERE menus_id = :menu_id';
		
		return array_pop($this->_objMCP->query(
			$strSQL
			,array(	
				':menu_id'=>(int) $intId
			)
		));
		
	}
	
	/*
	* Get a menu by unique name within a site
	* 
	* @param str name
	* @param int site id
	* @return array menu data
	*/
	public function fetchSiteMenuByName($strName,$intSitesId) {
		
		$arrMenu = array_pop( $this->_objMCP->query(
			'SELECT menus_id FROM MCP_MENUS WHERE sites_id = :sites_id AND system_name = :system_name LIMIT 1'
			,array(
				':sites_id'=>(int) $intSitesId
				,':system_name'=>(string) $strName
			)
		));
		
		if($arrMenu === null) {
			return null;
		}
		
		// eliminate repeating the same code
		return $this->fetchMenuById($arrMenu['menus_id']);
		
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
	* Get list of all available navigation menu locations
	* 
	* @return array navigation menu locations
	*/
	public function fetchMenuLocations() {
		$arrResult = $this->_objMCP->query('DESCRIBE MCP_MENUS');
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
			
			// mark link as dynamic - control whether it can be dited, deleted, etc from UI
			$arrNewLink['dynamic'] = 1;
			
			// normalize links URL
			if($arrNewLink['absolute_url'] !== null) {
				$arrNewLink['url'] = $arrNewLink['absolute_url'];
			} else if($arrNewLink['path'] !== null) {
				$arrNewLink['url'] = $this->_objMCP->getBaseUrl().'/'.$arrNewLink['path'];
			} else {
				$arrNewLink['url'] = null;
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
	* Insert or update menu
	* 
	* @param array menu data
	*/
	public function saveMenu($arrMenu) {	
		return $this->_save(
			$arrMenu
			,'MCP_MENUS'
			,'menus_id'
			,array('menu_title','menu_location','system_name')
			,'created_on_timestamp'
		);	
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
        
        /**
         * Delete a link
         * 
         * Removes link and all children.
         * 
         * @param int link id
         */
        public function deleteLink($intLinksId) {

                $queries = $this->_makeDeleteLinkQueries($intLinksId);
                
                // start transaction
                $this->_objMCP->begin();

                try {

                    // run each query
                    foreach($queries as &$query) {
                        $this->_objMCP->query($query['sql'],$query['bind']);
                    }

                    // commit the transaction
                    $this->_objMCP->commit();

                } catch(Exception $e) {

                    // rollback the transaction
                    $this->_objMCP->rollback();

                    // throw DAO exception
                    throw new MCPDAOException($e->getMessage());

                }
            
        }
        
        /**
         * Remove a link
         * 
         * Remove will delete the single link moving all
         * direct children up one branch in the menu tree.
         * 
         * @param int links id
         */
        public function removeLink($intLinksId) {

		/*
		* Get links data 
		*/
		$arrTarget = $this->fetchLinkById($intLinksId);
		
		/*
		* Get targets children
		*/
		$arrChildren = $this->fetchLinks($arrTarget['menu_links_id'],'link',false,array(
			'filter'=>'m.deleted = 0'
		));
           
		
		/*
		* Get targets siblings
		*/
		$arrLinks = $this->fetchLinks(($arrTarget['parent_id'] === null?$arrTarget['menus_id']:$arrTarget['parent_id']),($arrTarget['parent_id'] === null?'menu':'link'),false,array(
			'filter'=>'m.deleted = 0'
		));
		
		/*
		* reorder array 
		*/
		$arrIds = array();
		
		foreach($arrLinks as $arrLink) {
			
			/*
			* Replace links position with children 
			*/
			if($arrLink['menu_links_id'] == $arrTarget['menu_links_id']) {
				foreach($arrChildren as $arrChild) {
					$arrIds[] = $arrChild['menu_links_id'];
				}
				continue;	
			}
			
			$arrIds[] = $arrLink['menu_links_id'];
		}
		
		/*
		* Build update 
		*/
		$arrUpdate = array();
		foreach($arrIds as $intIndex=>$intId) {
			$arrUpdate[] = sprintf(
				"(%s,%s,%s)"
				,$this->_objMCP->escapeString($intId)
				,$arrTarget['parent_id'] === null?'NULL':$this->_objMCP->escapeString($arrTarget['parent_id'])
				,$this->_objMCP->escapeString($intIndex)
			);
		}
		
		/*
		* Build update query 
		*/
		$strSQL = sprintf(
			'INSERT IGNORE INTO MCP_MENU_LINKS (menu_links_id,parent_id,weight) VALUES %s ON DUPLICATE KEY UPDATE parent_id=VALUES(parent_id),weight=VALUES(weight)'
			,implode(',',$arrUpdate)
		);
                
                // start transaction
                $this->_objMCP->begin();

                try {
                    
                    /**
                     * When this query is ran the target term will not have any child terms Therefore,
                     * it is completely safe to merely delete the term afterwards.
                     */
                    if(!empty($arrUpdate)) {
                        $this->_objMCP->query($strSQL);
                    }
                    
                    /**
                     * Get delete queries (must be ran after updating the direct children)
                     */
                    $queries = $this->_makeDeleteLinkQueries($intLinksId);

                    // run each query
                    foreach($queries as &$query) {
                        $this->_objMCP->query($query['sql'],$query['bind']);
                    }

                    // commit the transaction
                    $this->_objMCP->commit();

                } catch(Exception $e) {

                    // rollback the transaction
                    $this->_objMCP->rollback();

                    // throw DAO exception
                    throw new MCPDAOException($e->getMessage());

                }
		
		return 1;
            
        }
	
        
        /**
         * Get collection of queries that will be used to delete a single
         * link. This will include queries used to delete all children and
         * permissions when a target term is deleted.
         * 
         * @param in links id
         * @return array
         */
        protected function _makeDeleteLinkQueries($intLinksId) {
            
		/*
		* Get links data 
		*/
		$arrTarget = $this->fetchLinkById($intLinksId);
		
		/*
		* Get all child links 
		*/
		$arrLinks = $this->fetchLinks($arrTarget['menu_links_id'],'link',true,array(
			'filter'=>'m.deleted = 0'
		));
		
		$objIds = new ArrayObject(array($arrTarget['menu_links_id']));
		
		/*
		* recursive function to collect all child term ids 
		*/
		$func = create_function('$value,$index,$ids','if(strcmp(\'menu_links_id\',$index) == 0) $ids[] = $value;');
		
		/*
		* Collect all child ids 
		*/
		array_walk_recursive($arrLinks,$func,$objIds);
		
		/*
		* Collect ids into normal array to use implode 
		*/
		$arrIds = array();
		foreach($objIds as $intId) {
			$arrIds[] = (int) $intId;
		}
                
                /*
                * All rows deleted in this transaction will share
                * the same timestamp. This will provide ease of
                * debugging and tracking what has been deleted.    
                */
                $time = time();
                
                /**
                 * Create string to embed in SQL
                 */
                $ids = implode(',',$arrIds);
                
                /**
                 * Collection of queries to run.
                 */
                $queries = array();
                
                /**
                 * Delete menu link role and user permissions
                 */
                $queries['user_perms'] = array(
                    'sql'=> "
                        DELETE
                          FROM
                             MCP_PERMISSIONS_USERS
                         WHERE
                             item_type = 'MCP_MENU_LINK'
                           AND
                             item_id IN (".$ids.")
                     ",
                    'bind'=> array()
                );

                $queries['role_perms'] = array(
                    'sql'=> "
                        DELETE
                          FROM
                             MCP_PERMISSIONS_ROLES
                         WHERE
                             item_type = 'MCP_MENU_LINK'
                           AND
                             item_id IN (".$ids.")
                     ",
                    'bind'=> array()
                );

                /**
                 * Delete links
                 */
                $queries['links'] = array(
                    'sql'=> "
                        UPDATE 
                              MCP_MENU_LINKS m
                           SET
                              m.deleted = NULL
                             ,m.deleted_on_timestamp = :ts1
                         WHERE
                             m.menu_links_id IN (".$ids.")
                    ",
                    'bind'=> array(
                         ':ts1'=> $time
                    )
                );
                
                return $queries;
            
        }
        
        
        
        
        
        
        
        
        
        /**
         * Testing datasource
         */
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