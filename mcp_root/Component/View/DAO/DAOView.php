<?php
$this->import('App.Core.DAO');

class MCPDAOView extends MCPDAO {
	
	protected 
	
	/*
	* Tables compatible with view
	*/
	$_arrTables = array(
		array(
			'human_name'=>'Users'
			,'system_name'=>'User'
			,'table'=>'MCP_USERS'
		)
		,array(
			'human_name'=>'Sites'
			,'system_name'=>'Site'
			,'table'=>'MCP_SITES'
		)
		,array(
			'human_name'=>'Vocabularies'
			,'system_name'=>'Vocabulary'
			,'table'=>'MCP_VOCABULARY'
		)
		,array(
			'human_name'=>'Node Types'
			,'system_name'=>'NodeType'
			,'table'=>'MCP_NODE_TYPES'
		)
		,array(
			'human_name'=>'Menus'
			,'system_name'=>'Menu'
			,'table'=>'MCP_MENUS'
		)
		,array(
			'human_name'=>'Config'
			,'system_name'=>'Config'
			,'table'=>'MCP_CONFIG'
		)
		,array(
			'human_name'=>'Nodes'
			,'system_name'=>'Node'
			,'table'=>'MCP_NODES'
		)
		,array(
			'human_name'=>'Terms'
			,'system_name'=>'Term'
			,'table'=>'MCP_TERMS'
		)
		,array(
			'human_name'=>'Links'
			,'system_name'=>'MenuLink'
			,'table'=>'MCP_MENU_LINKS'
		)
		,array(
			'human_name'=>'Images'
			,'system_name'=>'Image'
			,'table'=>'MCP_MEDIA_IMAGES'
		)
		,array(
			'human_name'=>'Files'
			,'system_name'=>'File'
			,'table'=>'MCP_MEDIA_FILES'
		)
		,array(
			'human_name'=>'Roles'
			,'system_name'=>'Role'
			,'table'=>'MCP_ROLES'
		)
	)
	
	/*
	* Cached describe queries 
	*/
	,$_arrCachedDescribe = array()
	
	/*
	* Cached dynamic fields by view type 
	*/
	,$_arrCachedDynamicFieldViewType = array();
	
	/*
	* The types of items view may display
	* 
	* NOTE: formated for compatibility with select menu UI builder -
	* method is used as a form config DAO callback
	* 
	* @return array types of items to display
	*/
	public function fetchViewTypes() {
		
		// Node DAO is required to generate list of node types
		$objDAONode = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP));
		
		// Taxonomy DAO is required to generate list of vocabularies
		$objDAOTaxonomy = $this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP));
		
		// Menu DAO is required to generate list of menus
		$objDAOMenu = $this->_objMCP->getInstance('Component.Menu.DAO.DAOMenu',array($this->_objMCP));
		
		$types = array();
		
		// populate types array with static items
		foreach($this->_arrTables as $type) {
			
			// skip over nodes, terms and links
			if( in_array($type['system_name'],array('Node','Term','MenuLink')) ) continue;
			
			$types[] = array(
				'value'=>$type['system_name']
				,'label'=>$type['human_name']
			);
		}
		
		// Get all node types for site
		$nodeTypes = $objDAONode->fetchNodeTypes(
			"CONCAT('Node:',t.node_types_id) value,t.human_name label"
			,"t.sites_id = {$this->_objMCP->escapeString($this->_objMCP->getSitesId())} AND t.deleted = 0"
		);
		
		// Get all vocabularies for site
		$vocabs = $objDAOTaxonomy->listVocabulary(
			"CONCAT('Term:',v.vocabulary_id) value,v.human_name label"
			,"v.sites_id = {$this->_objMCP->escapeString($this->_objMCP->getSitesId())} AND v.deleted = 0"
		);
		
		// Get all menus for site
		$menus = $objDAOMenu->listAllMenus(
			"CONCAT('MenuLink:',m.menus_id) value,m.menu_title label"
			,"m.sites_id = {$this->_objMCP->escapeString($this->_objMCP->getSitesId())} AND m.deleted = 0"
		);
		
		// populate types with all dynamic types
		foreach(array_merge($nodeTypes,$vocabs,$menus) as $type) {
			$types[] = array(
				'value'=>$type['value']
				,'label'=>$type['label']
			);
		}
		
		return $types;
		
	}
	
	/*
	* Get the available fields for a view type 
	* 
	* NOTE: this will include dynamic fields
	* 
	* @return array fields
	*/
	public function fetchFieldsByViewType($strViewType) {
		
		$fields = array();
		
		// Get the table for the view type
		$table = $this->_fetchTableByViewType($strViewType);
		
		// Get the table schema
		$columns = $this->_describe($table);
		
		/*
		* Config is a special case in that the presented schema is virtually
		* derived from the config values dynamically.
		* 
		* There may be other cases like this in the future that is why
		* in_array() is being used over direct string comparision.
		*/
		if( !in_array($table,array('MCP_CONFIG')) ) {
		
			// Get all concrete fields
			foreach($columns as $column) {
			
				// Chance to augment/change types
				$field = $this->_tableColumnToField($column,$table);
			
				// False return indicates field to ignore
				if($field === false) continue;
			
				$fields[] = $field;
			}
		
		}
		
		// Add any dynamic column
                $arrDynamicFields = $this->_fetchDynamicFieldsByViewType($strViewType);
                
                if($arrDynamicFields) {
                    foreach($arrDynamicFields as $field) {			
                            $fields[] = $field;
                    }
                }
		
		return $fields;
		
	}
	
	/*
	* Get fields for a view path such as;  Node:2/field/manufacturer/name
	* 
	* @param str view path
	* @return array fields 
	*/
	public function fetchFieldsByViewPath($strViewPath) {
		
		$viewType = null;
		$fields = array();
		
		// Each slash separates a separate part of the path
		foreach( explode('/',$strViewPath) as $index=>$piece) {
			
			// first item will ALWAYS represent type type
			if($index == 0) {
				$fields = $this->fetchFieldsByViewType($piece);
				$viewType = $piece;
				continue;
			}
			
			$matched = false;
			foreach($fields as &$field) {
				if($field['path'] == $piece) {
					$matched = true;
					
					// atomic field
					if($field['relation'] === null) {
						$fields = array();
						break;
					}
					
					$fields = $this->fetchFieldsByViewType("{$field['relation']}");
					$viewType = $field['relation'];
					
					continue;
				}
			}
			
			if($matched === false) {
				$fields = array();
				$viewType = null;
				break;
			}
			
		}
		
		return $fields;
		
	}
	
	/*
	* Get individual field data for a view path 
	* 
	* @param str view path
	* @return array view field data
	*/
	public function fetchFieldByViewPath($strViewPath) {
		
		$viewType = null;
		$fields = array();
		
		$pieces = count(explode('/',$strViewPath)) - 1;
		
		// Each slash separates a separate part of the path
		foreach( explode('/',$strViewPath) as $index=>$piece) {
			
			// first item will ALWAYS represent type type
			if($index == 0) {
				$fields = $this->fetchFieldsByViewType($piece);
				$viewType = $piece;
				continue;
			}
			
			foreach($fields as &$field) {
				if($field['path'] == $piece) {
					
					if($pieces == $index) {
						return $field;
					}
					
					// atomic field
					if($field['relation'] === null) {
						$fields = array();
						break;
					}
					
					$fields = $this->fetchFieldsByViewType("{$field['relation']}");
					$viewType = $field['relation'];
					
					continue;
				}
			}
			
		}
		
		return null;
		
	}
	
	/*
	* Get the name of the primary key for a table correspondig to
	* a view path. 
	* 
	* @param str view path
	*/
	public function fetchTablePrimaryKeyByViewPath($strViewPath) {
		
		$arrFields = $this->fetchFieldsByViewPath($strViewPath);
		
		if(!$arrFields) {
			return;
		}
		
		/*
		* The primary key will always be the id path
		*/
		foreach($arrFields as $arrField) {
			if( strcmp('id',$arrField['path']) == 0) {
				return $arrField['column'];
			}
		}
		
	}
	
	/*
	* Get all the ancestors of a given view 
	* 
	* @param int view id
	* @return array views
	*/
	public function fetchAncestory($intViewsId) {
		
		$arrViews = array();
		while($intViewsId !== null && $objView = $this->fetchViewById($intViewsId)) {
			array_unshift($arrViews,$intViewsId);
			$intViewsId = $objView->parent_id;
			
		}
		
		return $arrViews;
		
	}
	
	/*
	* Fetch a single view display by its ID
	* 
	* NOTE: This will fully resolve a view including
	* overrides. Its "final" state will be represented.
	* 
	* @todo: handle overrides and parent/child resolution. Views can be nested to
	* an infinite depth. Right now though this only accounts for a single depth. Also
	* add arguments integration IE. declare dynamic variables from user interface
	* that reference function return value, dao, static value, or even another view
	* that may be injected into the filter, sort and field as options, values, etc depending
	* on the case.
	* 
	* @param int view displays id
	* @param array view data
	*/
	public function fetchViewById($intId) {
		
		/*
		* Build query to pull back all view data including fields, filters, sorting and arguments 
		*/
		$strSQL = sprintf(
			"SELECT
			      d.id display_id
			      
			      ,d.base
			      ,d.base_id
			      
			      ,d.human_name
			      ,d.system_name
			      
			      ,COALESCE(d.opt_paginate,0) paginate
			      ,CASE
			          
			      	 WHEN COALESCE( d.opt_paginate, 0 ) = 1
			      	 THEN COALESCE( d.opt_rows_per_page , 20)
			      	 
			      	 ELSE NULL
			      
			       END `limit`
			       ,d.opt_theme_wrap template_wrap
			       ,d.opt_theme_row template_row
			      
			      ,a.id argument_id
			      ,a.system_name argument_system_name
			      ,a.human_name argument_human_name
			      ,a.type argument_type
			      ,a.context argument_context
			      ,a.context_routine argument_routine
			      ,a.context_args argument_args
			      ,a.value argument_value
			      
			      ,c.id field_id
			      ,CONCAT(d.base, IF(d.base_id IS NULL,'',CONCAT(':',d.base_id) ) ,'/',c.path) field_path
			      ,c.sortable field_sortable
			      ,c.editable field_editable
			      ,c.removed field_removed
			      
			      ,co.id field_option_id
			      ,co.option_name field_option_name  
			      ,CASE
			          WHEN co.value_field_id IS NOT NULL
			          THEN co.value_field_id
			          
			          WHEN co.value_argument_id IS NOT NULL
			          THEN co.value_argument_id
			          
			          ELSE
			          co.value_static
			      END field_option_value		      
			      ,CASE
			          WHEN co.value_field_id IS NOT NULL
			          THEN 'field'
			          
			          WHEN co.value_argument_id IS NOT NULL
			          THEN 'argument'
			          
			          ELSE
			          'static'
			      END field_option_type
			      ,co.value_static field_option_actual_value
			      
			      ,f.id filter_id
			      ,CONCAT(d.base, IF(d.base_id IS NULL,'',CONCAT(':',d.base_id) ) ,'/',f.path) filter_path
			      ,f.comparision filter_comparision
			      ,f.conditional filter_conditional
			      ,f.wildcard filter_wildcard
			      ,f.regex filter_regex
			      ,f.removed filter_removed
			      
			      ,fv.id filter_value_id
			      ,CASE
			          WHEN fv.value_field_id IS NOT NULL
			          THEN fv.value_field_id
			          
			          WHEN fv.value_argument_id IS NOT NULL
			          THEN fv.value_argument_id
			          
			          ELSE
			          fv.value_static
			      END filter_value_value      
			      ,CASE
			          WHEN fv.value_field_id IS NOT NULL
			          THEN 'field'
			          
			          WHEN fv.value_argument_id IS NOT NULL
			          THEN 'argument'
			          
			          ELSE
			          'static'
			      END filter_value_type
			      ,fv.wildcard filter_value_wildcard
			      ,fv.regex filter_value_regex
			      ,fv.value_static filter_value_actual_value
			      
			      ,s.id sorting_id
			      ,CONCAT(d.base, IF(d.base_id IS NULL,'',CONCAT(':',d.base_id) ) ,'/',s.path) sorting_path
			      ,s.ordering sorting_order
			      ,s.priority sorting_priority
			      ,s.removed sorting_removed
			      
			      ,sp.id sorting_priority_id
			      ,CASE
			          WHEN sp.value_field_id IS NOT NULL
			          THEN sp.value_field_id
			          
			          WHEN sp.value_argument_id IS NOT NULL
			          THEN sp.value_argument_id
			          
			          ELSE
			          sp.value_static
			      END sorting_priority_value  
			      ,CASE
			          WHEN sp.value_field_id IS NOT NULL
			          THEN 'field'
			          
			          WHEN sp.value_argument_id IS NOT NULL
			          THEN 'argument'
			          
			          ELSE
			          'static'
			      END sorting_priority_type
			      ,sp.value_static sorting_priority_actual_value
			      ,sp.weight sorting_priority_weight
			      
			  FROM
			      MCP_VIEW_DISPLAYS d
			  LEFT OUTER
			  JOIN
			      MCP_VIEW_ARGUMENTS a
			    ON
			      d.id = a.displays_id
			   AND
			      a.deleted = 0
			  LEFT OUTER
			  JOIN
			      MCP_VIEW_FIELDS c
			    ON
			      d.id = c.displays_id
			   AND
			      c.deleted = 0
			  LEFT OUTER
			  JOIN
			      MCP_VIEW_FIELD_OPTIONS co
			    ON
			      c.id = co.fields_id
			   AND
			      co.deleted = 0
			  LEFT OUTER
			  JOIN
			      MCP_VIEW_FILTERS f
			    ON
			      d.id = f.displays_id
			   AND
			      f.deleted = 0
			  LEFT OUTER
			  JOIN
			     MCP_VIEW_FILTER_VALUES fv
			    ON
			     f.id = fv.filters_id
			   AND
			     fv.deleted = 0
			  LEFT OUTER
			  JOIN
			     MCP_VIEW_SORTING s 
			    ON
			     d.id = s.displays_id
			   AND
			     s.deleted = 0
			  LEFT OUTER
			  JOIN
			     MCP_VIEW_SORTING_PRIORITY sp
			    ON
			     s.id = sp.sorting_id
			   AND
			     sp.deleted = 0 
			 WHERE
			     d.id = %s"
			,$this->_objMCP->escapeString($intId)
		);
		
		// echo "<p>$strSQL</p>";
		
		$rows = $this->_objMCP->query($strSQL);

		$view = new stdClass();	
		$view->config = array();
		$view->arguments = array();
		$view->fields = array();
		$view->filters = array();
		$view->sorting = array();
		
		/*
		* Build domain level data structure 
		*/
		foreach($rows as $row) {
			
			// Views basic info
			$view->id = $row['display_id'];
			$view->base = $row['base'];
			$view->base_id = $row['base_id'];
			$view->base_path = $row['base'].($row['base_id']?":{$row['base_id']}":'');
			$view->system_name = $row['system_name'];
			$view->human_name = $row['human_name'];
			
			// turn pagination on
			$view->paginate = (bool) $row['paginate'];
			
			// Rows per page when pagination is turned on
			$view->limit = $row['limit'];
			
			// Wrapper template
			$view->template_wrap = $row['template_wrap'];
			
			// Individual row template
			$view->template_row = $row['template_row'];
			
			// parse arguments
			if($row['argument_id'] !== null && !isset($view->arguments[$row['argument_id']])) {
				foreach($row as $col=>$value) {
					if(strpos($col,'argument_') !== 0) continue;
					$view->arguments[$row['argument_id']][substr($col,9)] = $value;
				}
			}
			
			// parse fields
			if($row['field_id'] !== null && !isset($view->fields[$row['field_id']])) {
				foreach($row as $col=>$value) {
					if(strpos($col,'field_') !== 0 || strpos($col,'field_option_') === 0) continue;
					$view->fields[$row['field_id']][substr($col,6)] = $value;
				}
			}
			
			// parse field options
			if($row['field_option_id'] !== null) {
				foreach($row as $col=>$value) {
					if(strpos($col,'field_option_') !== 0) continue;
					$view->fields[$row['field_id']]['options'][$row['field_option_id']][substr($col,13)] = $value;
				}
			}
			
			// parse filters
			if($row['filter_id'] !== null && !isset($view->filters[$row['filter_id']])) {
				foreach($row as $col=>$value) {
					if(strpos($col,'filter_') !== 0 || strpos($col,'filter_value_') === 0) continue;
					$view->filters[$row['filter_id']][substr($col,7)] = $value;
				}
			}
			
			// parse filter values
			if($row['filter_value_id'] !== null) {
				foreach($row as $col=>$value) {
					if(strpos($col,'filter_value_') !== 0) continue;
					$view->filters[$row['filter_id']]['values'][$row['filter_value_id']][substr($col,13)] = $value;
				}
			}
			
			// parse sorting
			if($row['sorting_id'] !== null && !isset($view->sorting[$row['sorting_id']])) {
				foreach($row as $col=>$value) {
					if(strpos($col,'sorting_') !== 0 || strpos($col,'sorting_priority_') === 0) continue;
					$view->sorting[$row['sorting_id']][substr($col,8)] = $value;
				}
			}
			
			// parse sorting priorities
			if($row['sorting_priority_id'] !== null) {
				foreach($row as $col=>$value) {
					if(strpos($col,'sorting_priority_') !== 0) continue;
					$view->sorting[$row['sorting_id']]['priorities'][$row['sorting_priority_id']][substr($col,17)] = $value;
				}
			}
		}
		
                //$this->debug($view);
		
		// ----------------------------------------------------------------------------
		// Replace arguments with actual values for field options, filter values and sorting priorities
		// ----------------------------------------------------------------------------
		// Post processing
		
		// field options argument resolution
		foreach($view->fields as &$fields) {
			if( !isset($fields['options']) ) continue;
			foreach($fields['options'] as &$option) {
				if( strcmp('argument',$option['type']) != 0) continue;
				
				$option['actual_value'] = $this->_getArgumentsActualValue(
					 $view->arguments[ $option['value'] ]['value']
					,$view->arguments[ $option['value'] ]['context']
					,$view->arguments[ $option['value'] ]['type']
					,$view->arguments[ $option['value'] ]['routine']
					,$view->arguments[ $option['value'] ]['args']
				);
				
			}
		}
		
		// filter values argument resolution
		foreach($view->filters as &$filter) {
			if( !isset($filter['values']) ) continue;
                        
                        // Path data reference
                        $arrPath = $this->fetchFieldByViewPath($filter['path']);
                        
			foreach($filter['values'] as &$value) {
                            
				if( strcmp('argument',$value['type']) === 0) {
				
                                    $value['actual_value'] = $this->_getArgumentsActualValue(
					 $view->arguments[ $value['value'] ]['value']
					,$view->arguments[ $value['value'] ]['context']
					,$view->arguments[ $value['value'] ]['type']
					,$view->arguments[ $value['value'] ]['routine']
					,$view->arguments[ $value['value'] ]['args']
                                    );
                                
                                }
				
			}
		}
		
		// sorting priorities argument resolution
		foreach($view->sorting as &$sorting) {
			if( !isset($filter['priorities']) ) continue;
			foreach($filter['priorities'] as &$priority) {
				if( strcmp('argument',$priority['type']) != 0) continue;
				
				$priority['actual_value'] = $this->_getArgumentsActualValue(
					 $view->arguments[ $priority['value'] ]['value']
					,$view->arguments[ $priority['value'] ]['context']
					,$view->arguments[ $priority['value'] ]['type']
					,$view->arguments[ $priority['value'] ]['routine']
					,$view->arguments[ $priority['value'] ]['args']
				);
				
			}
		}
		
		/*
		* -----------------------------------------------------------------------------
		* Resolve permission to create items of base view type 
		* -----------------------------------------------------------------------------
		*/
		switch( $view->base ) {
			
			case 'Node':
				
				$perm = $this->_objMCP->getPermission(MCP::ADD,'Node',$view->base_id);
				$view->create = $perm['allow'];
				break;
				
			case 'NodeType':
				
			case 'Term':
				
			case 'Vocabulary':
				
			case 'Site':
				
			case 'User':
				
			default:
				$view->create = false;
			
		}
		
		// echo '<pre>',print_r($view),'</pre>';
                //$this->debug($view);
                
                /*foreach($view->filters as $filter) {
                    $this->debug($this->fetchFieldByViewPath($filter['path']));
                }*/
		
		return $view;
		
	}
	
	/*
	* Build view SQL data structure/tree
	* 
	* @todo: argument integration, log-points (there are to many failure points to this without logging things
	* for making debugging less trechourous), hierarchy support - IE. build entire vocab or even nav menu
	* with child items.
	* 
	* @param array view data
	* @param int SQL offset for pagination
	* @param obj module that made request - necessary to build URLs relative to current module/page taking into consideration modules
	* may be nested.
	* @return ?
	*/
	public function fetchRows($objView,$intOffset=null,$objModule=null) {
		
		$arrReturn = array();
		$arrBind = array();
		$intBind = 0;
		
		/*
		* Select handler -------------------------------------------------------------- 
		*/
		
		foreach($objView->fields as $arrField) {
			/*
			* The field is needed so that options can be passed to the
			* preprocess and postprocess select closures for a field
			* when they exist. 
			*/
			$arrReturn[ $arrField['path'] ]['select'] = $arrField;
		}
		
		/*
		* Filter Handler --------------------------------------------------------------- 
		* 
		* NOTE: variable binding is used for filters only at this time. That seems to
		* be where the highest security risk would lie anyway. In addition, there is not
		* need to determine whether to escape items and what not.
		*/
		
		foreach($objView->filters as $arrFilter) {
			
                        $arrPath = $this->fetchFieldByViewPath($arrFilter['path']);
			$arrParts = array();
			
			// @todo: handle special IN and NOT IN case
			
			// Get all the values
			if(isset($arrFilter['values']) && !empty($arrFilter['values'])) {
				
				foreach($arrFilter['values'] as $arrValue) {
					
					/*
					* @todo: Temporary fix to skip over arguments that don't exist. This makes
					* all arguments optional though which isn't exactly what we want. Instead
					* we need to make sure to skip over only filter valyes bound to arguments that have required 
					* marked 0. Thiswill do for now though.
					*/
					if( strcmp('argument',$arrValue['type']) === 0 && $arrValue['actual_value'] === null ) {
						continue;
                                        }
                                        
                                        /*
                                        * This magic here given a term reference by id will activate a full tree search. For example
                                        * When the category News exists and has several child categories such as; local, sports, etc
                                        * Searching for the News term will pull back all data assigned to children of News as well. So
                                        * in this example all news, sports, local, etc stories would be pulled back. 
                                        * 
                                        * Right now this is only compatible with a term reference by id. this is because all other
                                        * information is not unique. For example, given a system_name of News it is possible that a news
                                        * term exists at mutiple places in a tree under the same vocabulary. So the only concrete way
                                        * At this way to find a single unique term for a vocabulary is by id.
                                        * 
                                        * The exception would be using a path search such as; /News/Topics but this is not supported
                                        * yet and would require additional Taxonomy DAO methods and integration into views via magical
                                        * search fields to be supported. So for now term id will be the only thing tht triggers this
                                        * for simplicity. 
                                        * 
                                        * @todo: Add option for recursive search to term id filter value          
                                        */
                                        if(isset($arrPath['table']) && strcmp($arrFilter['comparision'],'=') === 0 && strcasecmp($arrPath['table'],'Term') === 0 && strcmp($arrPath['path'],'id') === 0) {
                                            
                                            // Get taxonomy DAO
                                            $objDAOTax = $this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP));
                                            
                                            // Set all subterms (at every depth below passed term)
                                            $arrTerms = $objDAOTax->getAllSubTerms($arrValue['actual_value']);
                                            
                                            // push root term
                                            $arrTerms[] = $objDAOTax->fetchTermById($arrValue['actual_value']);
                                            
                                            // Add parts and values for each term and the child
                                            $arrParts[] = '{#column#}'.' IN ('.implode(',',array_map(function($term) use (&$arrBind,&$intBind) { 
                                                $arrBind[':bind_var_'.(++$intBind)] = $term['terms_id'];
                                                return ":bind_var_$intBind";
                                            },$arrTerms)).')';
                                            
                                        } else {
					
					// like, regex and fulltext edge case handling w/ default
					switch($arrFilter['comparision']) {
						
						case 'like':
							
							// like format ie. %s,%s%,s%
							$strWildcard = $arrFilter['wildcard'];
							
							// value may change the default wildcard for the entire filter
							if($arrValue['wildcard'] !== null) {
								$strWildcard = $arrValue['wildcard'];
							}
							
							// negation edge case ie. LIKE and NOT LIKE
							$strOperator = strcmp($arrFilter['conditional'],'none') === 0?' NOT LIKE ':' LIKE ';
							
							/*
							* I am pretty sure the correct way to do this is %?% - I may be wrong though 
							*/
							// $arrParts[] = '{#column#}.'.$strOperator."'".str_replace('s',$arrValue['actual_value'],$strWildcard)."'";
							$arrParts[] = '{#column#}.'.$strOperator."'".str_replace('s',":bind_var_{++$intBind}",$strWildcard)."'";
							$arrBind[":bind_var_$intBind"] = $arrValue['actual_value'];
							
							break;
						
						case 'regex':
							
							// regular expression
							$strRegex = $arrFilter['regex'];
							
							// value may override regex
							if($arrValue['regex'] !== null) {
								$strRegex = $arrValue['regex'];
							}
							
							// negation edge case ie. NOT REGEXP and REGEXP
							$strOperator = strcmp($arrFilter['conditional'],'none') === 0?' NOT REGEXP ':' REGEXP ';
							
							/*
							* @todo: Can a regular expression be bound? 
							*/
							$arrParts[] = '{#column#}.'.$strOperator."'".$strRegex."'";
							break;
							
						case 'fulltext':
							
							// escape value for security reasons
							// $arrParts[] = "FULLTEXT({#column#},'{$this->_objMCP->escapeString($arrValue['actual_value'])}')";
							$arrParts[] = 'FULLTEXT({#column#},:bind_var_'.(++$intBind).')';
							$arrBind[":bind_var_$intBind"] = $arrValue['actual_value'];
							
							break;
						
						default:
							
							$strOperator = $arrFilter['comparision'];
							
							// negation edge case for = and !=
							if(strcmp($strOperator,'=') === 0 && strcmp($arrFilter['conditional'],'none') === 0) {
								$strOperator = '<>';
							}
							
							// $arrParts[] = '{#column#}'.' '.$strOperator.' '.(is_numeric($arrValue['actual_value'])?$arrValue['actual_value']:"'{$arrValue['actual_value']}'");
							
							/*
							* This makes it possible to send a array of values back for an argument
							* as an actual value and not break the the query. Each value is treated
							* as a separate condtion.
							* 
							* Seems like this is the only hadnler that needs to support this case. So for
							* now I will leave support for array actual values here.
							*/
							if( is_array($arrValue['actual_value']) ) {
								foreach($arrValue['actual_value'] as $mixActualValue) {
									$arrParts[] = '{#column#}'.' '.$strOperator.' :bind_var_'.(++$intBind);
									$arrBind[":bind_var_$intBind"] = $mixActualValue;
								}
							} else {
								$arrParts[] = '{#column#}'.' '.$strOperator.' :bind_var_'.(++$intBind);
								$arrBind[":bind_var_$intBind"] = $arrValue['actual_value'];
							}
							
					}
				}
                                
                                } // end if/else
			}
			
			// The conditional will determine the format of the values and separator ie. and | or
			// If parts don't exist that means the filter has no values in which case doing the below would
			// break the query ie. AND {space}
			if( !empty($arrParts) ) {
			
				switch($arrFilter['conditional']) {
				
					case 'all':	
						$arrReturn[ $arrFilter['path'] ]['filters'][] = '('.implode(' AND ',$arrParts).')';
						break;
				
					case 'none':
						$arrReturn[ $arrFilter['path'] ]['filters'][] = '('.implode(' AND ',$arrParts).')';
						break;
					
					case 'one':
						$arrReturn[ $arrFilter['path'] ]['filters'][] = '('.implode(' OR ',$arrParts).')';
						break;
					
					default: // when none of them are met, there is a problem - error
				
				}
			
			}
			
			
		}
		
		/*
		* Sorting handler ------------------------------------------------------------- 
		*/
		
		// build sorting field format for query builder
		foreach($objView->sorting as $arrSorting) {
			
			// handling basic and field() based sorting
			// @todo add RAND() support handler
			if(isset($arrSorting['priorities']) && !empty($arrSorting['priorities'])) {
				
				$arrValues = array();
				foreach($arrSorting['priorities'] as $arrPriority) {
					// @todo: determine whether value needs to be enclosed in quotes
					// lighter values have precedence over heavier ones ie. -1 comes before 6
					$arrValues[] = is_numeric($arrPriority['value'])?$arrPriority['value']:"'{$arrPriority['value']}'";
				}
				
				// place values in correct order without incurring query costs
				usort($arrValues,function($a,$b) {
    				return $a['weight'] != $b['weight']?($a['weight'] < $b['weight']) ? 1 : -1 : 0;
				});
				
				$arrReturn[ $arrSorting['path'] ]['sorting'][] = 'FIELD({#column#} ,'.implode(',',$arrValues).')'.' '.strtoupper($arrSorting['order']);
				unset($values);
				
			} else {
				
				$arrReturn[ $arrSorting['path'] ]['sorting'][] = '{#column#} '.strtoupper($arrSorting['order']);
				
			}
			
		}
		
		
		// echo '<pre>',print_r($arrReturn),'</pre>';
		//exit;
		
		// ----------------------------------------------------------------------------
		// convert to hierarical structure
		// ----------------------------------------------------------------------------
		
		$tree = array();
		foreach($arrReturn as $field=>$data) {
			
			$current =& $tree;
			$pieces = explode('/',$field);
			array_shift($pieces); // remove the the base sonce its not a field
			$last = count($pieces) - 1;
			
			foreach($pieces as $index => $piece) {
				$current =& $current[$piece];
				
				if($index == $last) {
					$current = $data;
					$current['leaf'] = true;
				}
				
			}
		}
		
		// echo '<pre>',print_r($tree),'</pre>';
		// exit;
		
		
		// ---------------------------------------------------------------------------
		// begin building SQL
		// ---------------------------------------------------------------------------
		

		// Counter is used build unique table aliases (1 is the base table)
		$intCounter = 0;
		
		// View is used to build join SQL
		$objDAOView = $this;
		
		// Query data
		$objQuery = new StdClass();
		$objQuery->select = array();
		$objQuery->from = array();
		$objQuery->where = array();
		$objQuery->orderby = array();
		$objQuery->limit = null;
		$objQuery->offset = null;
		
		// Build out base
		$objBase = new StdClass();
		$objBase->path = $objView->base_path;
		$objBase->alias = 't'.(++$intCounter);
		
		// Add base table to query
		$objQuery->from[] = "{$this->_fetchTableByViewType($objBase->path)} {$objBase->alias}";
		
		// Add contextual where clause - this is responsible for limiting entities to the type
		$this->_addQueryEntityContextFilter($objQuery,$objView->base_path);
		
		$toSQL = function($arrBranches,$objParent,$toSQL) use (&$intCounter,&$objModule,$objDAOView,$objQuery) {
			
			// collected branches - necessary to rebuild result set as hierarchy of relations
			$arrReturn = array();
	
			foreach($arrBranches as $strPiece => $arrChildren) {
				
				// All the branch objects as a collection to return - necessary to rebuild result set as object hierarchy from raw data
				$arrChildBranches = array();
				
				$objBranch = new StdClass();
				
				// Full view path of the branch
				$objBranch->path = "{$objParent->path}/$strPiece";
				
				// Alias placeholder for the branch (alias will be declared within join method)
				$objBranch->alias = null;	
				
				// Get possible join
				$objJoin = $objDAOView->getJoin($objBranch,$objParent,$intCounter);
				
				if($objJoin !== false && $objJoin->sql) {
					$objQuery->from[] = $objJoin->sql;
				}
				
				
				// Use the right most table if any tables were added
				if( !empty($objJoin->add) ) {
					$objBranch = array_pop($objJoin->add);
				}
				
				// Recure
				if( $arrChildren && !isset($arrChildren['leaf']) ) {				
					$arrChildBranches = $toSQL($arrChildren,$objBranch,$toSQL);
				}
				
				// Handle select, where, orderby clauses for leaf nodes
				if( isset($arrChildren['leaf']) ) {
					
					$arrField = $objDAOView->fetchFieldByViewPath($objBranch->path);
					
					/*
					* EDGE CASE: Dynamic fields need to reference the branch rather than
					* parent because the physical field belongs to the branch table not
					* the parent. 
					*/
					$strAlias = $arrField['dynamic']?$objBranch->alias:$objParent->alias;
					
					// columns to select
					if( isset($arrChildren['select']) ) {
						
						/*
						* This is some special sauce to mutate a given field at run time
						* using a closure. This is mainly used for special fields such as;
						* links and image urls. The column being selected may be the primary key
						* but a translation can be applied to turn it into a URL either here
						* or even at the collection stage (postprocess_select).
						* 
						* @see: _fetchSpecialNodeFields() or _fetchDynamicImageFields for usage reference
						*/
						if( !empty($arrField['preprocess_select']) ) {
							
							// -----------------------------------------------------------------------------------------
							// Create associative array of all options for the field to send to preprocess_select closure
							$arrOptions = array();
							foreach( $arrField['options'] as &$arrOption) {
								$arrOptions[$arrOption['name']] = null; // could use a default here
								
								// Get options actual value
								if( isset($arrChildren['select']['options']) && !empty($arrChildren['select']['options']) ) {
									foreach( $arrChildren['select']['options'] as &$arrOpt ) {
										if( strcmp($arrOpt['name'],$arrOption['name']) === 0 ) {
											$arrOptions[$arrOption['name']] = $arrOpt['actual_value'];
										}
									}
								}
								
							}
							// --------------------------------------------------------------------------------------

							$objQuery->select[] = call_user_func($arrField['preprocess_select'],"$strAlias.{$arrField['column']}",$arrOptions,$objModule)." {$strAlias}_{$strPiece}";
							
						} else {
							
							$objQuery->select[] = "$strAlias.{$arrField['column']} {$strAlias}_{$strPiece}";
							
						}
						
					}
					
					// where clause parts
					if( isset($arrChildren['filters']) ) {
						foreach($arrChildren['filters'] as $strFilter) {
							$objQuery->where[] = str_replace('{#column#}',"$strAlias.{$arrField['column']}",$strFilter);
						}
					}
					
					// orderby clause parts
					if( isset($arrChildren['sorting']) ) {
						foreach($arrChildren['sorting'] as $strSort) {
							$objQuery->orderby[] = str_replace('{#column#}',"$strAlias.{$arrField['column']}",$strSort);
						}
					}
					
				}
				
				// collected so that result set can be built that mimics relational hierarchy
				$arrReturn[] = array('branch'=>$objBranch,'children'=>$arrChildBranches);
				
			}
			
			/*
			* Get the primary key - used to remove duplicates and build relational multi-dimensional array structure
			* This is necessary otherwise its nearly impossible to remove duplicates and build a nice relational hierarchy
			*/
			$strPrimaryKeyColumn = $objDAOView->fetchTablePrimaryKeyByViewPath( $objParent->path );
			
			if( $strPrimaryKeyColumn ) {
				$objQuery->select[] = "{$objParent->alias}.$strPrimaryKeyColumn {$objParent->alias}_id";
			}
			
			return $arrReturn;
			
	
		};
		
		$arrNodes = $toSQL($tree,$objBase,$toSQL);	
		// echo '<pre>',print_r($objQuery),'</pre>';
		
		
		//---------------------------------------------------------------------
		// Create final query
		// --------------------------------------------------------------------
		$strSQL = sprintf(
			'SELECT SQL_CALC_FOUND_ROWS %s FROM %s %s %s %s'
			
			,implode(',',$objQuery->select)
			,implode(' ',$objQuery->from)
			,!empty($objQuery->where)?'WHERE '.implode(' AND ',$objQuery->where):''
			,!empty($objQuery->orderby)?'ORDER BY '.implode(',',$objQuery->orderby):''
			
			// apply pagination limit 
			,$objView->paginate?"LIMIT {$this->_objMCP->escapeString($intOffset)},{$this->_objMCP->escapeString($objView->limit)}":''
		);
		
		// echo "<p>$strSQL</p>";
		// echo '<pre>',print_r($arrBind),'</pre>';
		// $this->_objMCP->addSystemStatusMessage("View Query: $strSQL");
                        
                $this->debug($strSQL);
		
		// ------------------------------------------------------------------
		// fetch result set
		// ------------------------------------------------------------------
		try {
			
			$arrRows = $this->_objMCP->query($strSQL,$arrBind);
			
		} catch( MCPDBException $e) {
                        
			$this->_objMCP->addSystemStatusMessage("View Query Failed: $strSQL");
			return;
			
		}
		
		// Number of found rows
		$intFoundRows = array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')));
		
		//echo '<pre>',print_r($tree),'</pre>';
		//echo '<pre>',print_r($arrNodes),'</pre>';
		
		
		// ----------------------------------------------------------------
		// Build out result set as relational hierarchy
		// -------------------------------------------------------------------
		$toEntity = function($arrNodes,$objParent,$toEntity,$checkBelongs=null) use (&$arrRows,$objDAOView) {
			
			$arrDomainRows = array();
			
			foreach($arrNodes as $arrNode) {
				
				// Extract the branch
				$objBranch = $arrNode['branch'];
				
				// Get the field data
				$arrField = $objDAOView->fetchFieldByViewPath($objBranch->path);
				
				if($arrField) {
					
					// When the branch doesn't have an alias define assume it is part of the parent (atomic)
					$strAlias = $objBranch->alias?$objBranch->alias:$objParent->alias;
					
					// Build out the expected alais
					$strExpectedAlias = "{$strAlias}_{$arrField['path']}";
					
					// Alias for the primary key column - to remove duplicates
					$strUniqueRowAlias = "{$objParent->alias}_id";
					
					// Map the raw result set to the proper domain level result set
					foreach($arrRows as &$arrRow) {
						
						// $arrDomainRows[ $arrRow[$strUniqueRowAlias] ]['id'] = $arrRow[$strUniqueRowAlias];
						
						// Make sure the row exists within the result set
						// The second check is used is used skip over rows that don't belong to a parent for m:n,m:1 relationships
						if( array_key_exists($strExpectedAlias,$arrRow) && ($checkBelongs === null || $checkBelongs($arrRow) )) {
							
							// Map the raw row column to domain row column
							$arrDomainRows[ $arrRow[$strUniqueRowAlias] ][$arrField['path']] = $arrRow[$strExpectedAlias];
						
						}
						
						/*
						* Just because a node is part of the result set doesn't mean anything. In most
						* cases any relational nodes will not have concrete columns in the result set.
						* In this case its likely that the fields for the entitities relationship
						* are defined at the next level below the entity itself. 
						*/
						if( !empty( $arrNode['children'] ) && ($checkBelongs === null || $checkBelongs($arrRow) ) ) {
							
							// used to check if a row is a child of this one when called recusively
							$mixUniqueAliasValue = $arrRow[$strUniqueRowAlias];
							
							// The function is used to determine whether a row should be considered a child of this one for relational mapping
							$arrChildDomainRows = $toEntity($arrNode['children'],$objBranch,$toEntity,function($arrRow) use ($strUniqueRowAlias,$mixUniqueAliasValue)  {
								return $arrRow[$strUniqueRowAlias] == $mixUniqueAliasValue;
							});
							
							/*
							* When relationship is 1:m or m:n use the the collection otherwise use the first item
							* IE. A node has a single author so so $row['author']['username'] should yield
							* the authrors name. However, a node may have several images in that case 
							* $row['images'] is an aray of all the images that belong to the node or entity
							* in question.
							*/
							if( $arrField['relation_type'] && strcmp($arrField['relation_type'],'many') == 0 ) {
								$arrDomainRows[$arrRow[$strUniqueRowAlias]][$arrField['path']] =  $arrChildDomainRows;
							} else {
								$arrDomainRows[$arrRow[$strUniqueRowAlias]][$arrField['path']] =  array_pop($arrChildDomainRows);
							}
							
						} 
					
					}
					
				}
				
			}
			
			return $arrDomainRows;
			
		};
		
		$arrDomainRows = $toEntity($arrNodes,$objBase,$toEntity);	
		
		// echo '<pre>',print_r($arrRows),'</pre>';
		// echo '<pre>',print_r($arrDomainRows),'</pre>';
		
		/*
		* Add "magical" boolean allow_delete,allow_edit and allow_delete
		* keys to determine whether current user is able to carry out the action
		* on the base entity type. For example, use if($row['allow_edit']) ...; to
		* display a link to edit the entity for users who are allowed to do so.
		*/
		$this->_addPermissionsToViewResultSet($arrDomainRows,$objView);
		
		// when pagination is disabled
		if(!$objView->paginate) {
			return $arrDomainRows;
		}
		
		// When pagination is enabled 
		return array(
			 $arrDomainRows
			,$intFoundRows
		);
		
	}
	
	/*
	* Get the base table for a view type
	* 
	* @return str full table name for view type
	*/
	private function _fetchTableByViewType($strViewType) {
		
		$strBase = null;
		$intId = null;
		
		if(strpos($strViewType,':') !== false) {
			list($strBase,$intId) = explode(':',$strViewType,2);
		} else {
			$strBase = $strViewType;
		}
		
		foreach($this->_arrTables as $type) {
			if($type['system_name'] == $strBase) {
				return $type['table'];
			}
		}
		
	}
	
	/*
	* Transforms a table column via DESCRIBE to a field. This
	* wil return false if the column should be skipped / should
	* not be added / is not compatible. Some columns are note compatible
	* such as; deleted. Deleted is a special internal column.
	* 
	* @param array describe column row
	* @param str table name
	* @return array view entity field
	*/
	private function _tableColumnToField($arrColumn,$strTable) {
            
                $arrTable = $this->_fetchTableByName($strTable);
		
                $table          = $arrTable['system_name'];
		$label 		= $arrColumn['Field'];
		$path 		= $arrColumn['Field'];
		$column		= $arrColumn['Field'];
		$type		= $arrColumn['Type'];
		$compatible = 'select,filter,sort';
		$relation 	= null;
		$relation_type = null;
		$dynamic 	= false;
		$actions	= '';
		$primary 	= false;
		$options	= array();
		$preprocess_select = null;
		$postprocess_select = null;
		
		/*
		* A bunch of translations for common column names that have the same meaning
		* accross tables. 
		*/
		switch($column) {
			
			case 'parent_id':
				$label = 'Parent';
				$relation = 'Parent';
				$relation_type = 'one';
				$path = 'parent';
				break;
                            
			case 'menus_id':
				$label = 'Menu';
				$relation = 'Menu';
				$relation_type = 'one';
				$path = 'menu';
				break;
			
			case 'nodes_id':
				$label = 'Node';
				$relation = 'Node';
				$path = 'node';
				break;
				
			case 'node_types_id':
				$label = 'Node Type';
				$relation = 'NodeType';
				$relation_type = 'one';
				$path = 'nodetype';
				break;
				
			case 'terms_id':
				$label = 'Term';
				$relation = 'Term';
				$relation_type = 'one';
				$path = 'term';
				break;
				
			case 'vocabulary_id':
				$label = 'Vocabulary';
				$relation = 'Vocabulary';
				$relation_type = 'one';
				$path = 'vocabulary';
				break;
			
			case 'users_id':
				$label = 'User';
				$relation = 'User';
				$relation_type = 'one';
				$path = 'user';
				break;
			
			case 'creators_id':
				$label = 'Creator';
				$relation = 'User';
				$relation_type = 'one';
				$path = 'creator';
				break;
				
			case 'authors_id':
				$label = 'Author';
				$relation = 'User';
				$relation_type = 'one';
				$path = 'author';
				break;
			
			case 'sites_id':
				$label = 'Site';
				$relation = 'Site';
				$relation_type = 'one';
				$path = 'site';
				break;
			
			case 'node_title':
				$label = 'Title';
				break;
				
			case 'node_subtitle':
				$label = 'Subtitle';
				break;
				
			case 'intro_content':
				$label = 'Teaser';
				break;
				
			case 'node_content':
				$label = 'Body';
				
				/*
				* Options to truncate content 
				*/
				$options[] = array('name'=>'max_chars','type'=>'int');
				
				break;
				
			case 'node_url':
				$label = 'URL';
				break;
				
			case 'node_published':
				$label = 'Published';
				break;
				
			case 'site_name':
			case 'site_directory':
			case 'site_module_prefix':
				$path = substr($path,5);
				$label = ucwords(substr(str_replace('_',' ',$label),5));
				break;
				
			case 'image_label':
			case 'image_width':
			case 'image_height':
			case 'image_size':
			case 'image_mime':
				$label = ucwords(substr($label,6));
				break;

			case 'user_data':
			case 'uuid':
			case 'pwd':
			case 'md5_checksum':
			case 'parent_type':
			case 'deleted_on_timestamp':
			case 'deleted':
				return false;
				
			default:
				$label = ucwords( str_replace(array('_','timestamp'),array(' ','Date'),$label) );
				
				
				
		}
		
		/*
		* primary key handling 
		*/
		if($arrColumn['Key'] == 'PRI') {
			$label = 'id';
			$path = 'id';
			$relation = null;
			$relation_type = null;
			$primary = true;
		}
		
		return array(
                         'table'=>$table // table that owns field
			,'label'=>$label
			,'path'=>$path 
			,'column'=>$column
			,'type'=>$type
			,'compatible'=>$compatible
			,'relation'=>$relation
			,'relation_type'=>$relation_type
			,'dynamic'=>false
			,'actions'=>$actions
			,'primary'=>$primary
			,'options'=>$options
			
			// These columns are only used for dynamic fields
			,'entity_type'=>null
			,'entities_id'=>null 
			,'sites_id'=>null 
			,'entities_primary_key'=>null
			
			/*
			* Closure that mutates column during query (query phase)
			* @param column w/ alias
			* @param options
			* @param module (module that initiated fetchRows() call) - useful for building URls relative to page
			*/
			,'preprocess_select'=>$preprocess_select
			
			/*
			* Closure that mutates column after query (collection phase)
			* @param column w/ alias
			* @param options
			* @param module (module that initiated fetchRows() call) - useful for building URls relative to page 
			*/
			,'postprocess_select'=>$postprocess_select
		);
		
	}
        
        /*
        * Get details for a table by name. 
        * 
        * @param table name
        * @return array table definition 
        */
        private function _fetchTableByName($strName) {
            
            foreach($this->_arrTables as $table) {
                if(strcasecmp($strName,$table['table']) === 0) {
                    return $table;
                }
            }
            
        }
	
	/*
	* Get dynamic columns for view type 
	* 
	* @param str view type
	* @return array dynamic columns
	*/
	private function _fetchDynamicFieldsByViewType($strViewType) {
		
		/*
		* First check the cache
		* 
		*  This SIGNIFICANTLY reduces the number of queries in combination
		*  with describe per request caching. 
		*/
		if( isset($this->_arrCachedDynamicFieldViewType[$strViewType]) ) {
			return $this->_arrCachedDynamicFieldViewType[$strViewType];
		}
		
		$entity_type = null;
		$entities_id = null;
		
		$strBase = null;
		$intId = null;
		
		if(strpos($strViewType,':') !== false) {
			list($strBase,$intId) = explode(':',$strViewType,2);
		} else {
			$strBase = $strViewType;
		}
		
		$entities_id = $intId;
		
		// Get the Field DAO
		$objDAOField = $this->_objMCP->getInstance('Component.Field.DAO.DAOField',array($this->_objMCP));
		
		// fields to mixin with dynamic
		$add = array();
		
		/*
		* Translate to correct parent entity type or fields 
		*/
		switch($strBase) {
			case 'Node':
				$entity_type = 'MCP_NODE_TYPES';
				$primaryKey = 'nodes_id';
				
				// Get special node fields such as; link builder, ect
				$add = $this->_fetchSpecialNodeFields();
				
				break;
				
			case 'User':
				$entity_type = 'MCP_SITES';
				$entities_id = $this->_objMCP->getSitesId();
				$primaryKey = 'sites_id';
				break;
				
			case 'Term':
				$entity_type = 'MCP_VOCABULARY';
				$primaryKey = 'terms_id';
				break;
				
			case 'Config':
				$entity_type = 'MCP_CONFIG';
				$primaryKey = '';
				$entities_id = null;
				
				// Get static fields as virtual fields
				$add = $this->_fetchStaticConfigFields();
				
				break;

			// Image has special type with options
			case 'Image':
				return $this->_fetchDynamicImageFields();
				
			case 'Site':
			case 'NodeType':
			case 'Vocabulary':
			default:
				return false;
		}
		
		// Case statement to convert db_value to types	
		$values = $this->_describe('MCP_FIELD_VALUES');
		
		$type = 'CASE ';
		foreach($values as $column) {
			if(strpos($column['Field'],'db_') === 0) {
				$type.= sprintf(
					"WHEN f.db_value = '%s' THEN '%s' "
					,substr($column['Field'],3)
					,$column['Type']
				);
			}
		}
		$type.= ' ELSE NULL END';
		
		// Case statement for possible relations
		$relation = 'CASE ';
		foreach($this->_arrTables as $table) {
			$relation.= sprintf(
				"WHEN f.db_ref_table = '%s' THEN '%s' "
				,$table['table']
				,$table['system_name']
			);
		}
		$relation.= ' ELSE NULL END';
		
		// Mimic Describe with dynamic fields
		$data = array_merge( $add , $objDAOField->listFields(
			" f.cfg_label label
			 ,f.cfg_name path
			 ,f.cfg_name `name`
			 ,CONCAT('db_',f.db_value) `column`
			 ,$type type
			 ,'select,filter,sort' compatible
			 ,$relation relation
			 ,IF(cfg_multi = 1,'many','one') relation_type
			 ,1 dynamic
			 ,'' actions
			 ,0 `primary`
			 ,0 options
			 ,f.sites_id
			 ,f.entity_type
		     ,f.entities_id
			 ,'$primaryKey' entities_primary_key
			 ,f.db_ref_col
			 ,NULL preprocess_select
			 ,NULL postprocess_select" // NOTE: sites_id,entity_type, entities_id, entities_primary_key are necessary to build join for dynamic fields
		   ,sprintf(
				"f.sites_id = %s AND f.entity_type = '%s' AND f.entities_id %s"
				,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
				,$this->_objMCP->escapeString($entity_type)
				,$entities_id === null?"IS NULL":"= {$this->_objMCP->escapeString($entities_id)}"
			)
		));
		
		// cache the dynamic fields ( I don't believe they change dynamically )
		$this->_arrCachedDynamicFieldViewType[$strViewType] = $data;
		
		return $data;
		
	}
	
	/*
	*  Special: Extra node fields
	*  
	*  - node URL field
	* 
	*  @return array extra node fields
	*/
	private function _fetchSpecialNodeFields() {
		
		$mcp = $this->_objMCP;
		
		/*
		* node_link:
		* This is a fully URL to view the node page
		* relative to the view (nested) with a provided
		* back button to the view state (persistent args and pagination).
		*/
		return array(
			array(
				'label' 				=> '*Link'
				,'path' 				=> 'node_link'
				,'column'				=> 'nodes_id'
				,'type'					=> null
				,'compatible' 			=> 'select'
				,'relation' 			=> null
				,'relation_type' 		=> null
				,'dynamic' 				=> false
				,'actions'				=> ''
				,'primary' 				=> false
				,'options'				=> array(
					 array('name'=>'friendly_url','type'=>'bool')
				)
				
				// These columns are only used for dynamic fields
				,'entity_type'=>null
				,'entities_id'=>null 
				,'sites_id'=>null 
				,'entities_primary_key'=>null
				
				// first argument is the full column name including the alias
				// second argument is the view module instance - use getBasePath() to get URL relative to current page for building
				// pages that nest within one another.
				,'preprocess_select'=>function($strColumn,$arrOptions,$objModule) use ($mcp) {
					return "IF($strColumn IS NOT NULL,CONCAT('{$mcp->escapeString( $objModule->getBasePath(true,true) )}/View/',$strColumn,'{$mcp->getQueryString()}'),NULL)";
				}
				
				,'postprocess_select'=>function($intNodesId) {
					
				}
				
			)
		);
		
	}
	
	/*
	*  Special: Extra image fields
	* 
	*  @return array extra image fields
	*/
	private function _fetchDynamicImageFields() {
		
		$mcp = $this->_objMCP;
		
		/*
		* This "magical" field will display the image. When displaying
		* an image there are several options available to manipulate it.
		* 
		* width: desired image width 
		* height: desired image height
		* grayscale: grayscale transformation
		* b/w: black and white transformation
		*/
		return array(
			array(
				'label' 				=> 'Image'
				,'path' 				=> 'image'
				,'column'				=> 'images_id'
				,'type'					=> null
				,'compatible' 			=> 'select'
				,'relation' 			=> null
				,'relation_type' 		=> null
				,'dynamic' 				=> false
				,'actions'				=> ''
				,'primary' 				=> false
				,'options'				=> array(
					 array('name'=>'width','type'=>'int')
					,array('name'=>'height','type'=>'int')
					,array('name'=>'grayscale','type'=>'bool')
					,array('name'=>'b/w','type'=>'bool')
				)
				
				// These columns are only used for dynamic fields
				,'entity_type'=>null
				,'entities_id'=>null 
				,'sites_id'=>null 
				,'entities_primary_key'=>null
				
				/*
				* The column being selected here is the images primary key. The translation
				* below converts it to a image URL w/ options applied to transform
				* the image as specified. The end result is a URL that can be printed
				* out and displays the image with the option translations applied. 
				*/
				,'preprocess_select'=>function($strColumn,$arrOptions,$objModule) use ($mcp) {
					
					$strImageURL = '/img.php/';
					$strMutation = '';
					
					// width resize
					if( $arrOptions['width'] !== null ) {
						$strMutation.= "/w/{$mcp->escapeString($arrOptions['width'])}";
					}
					
					// height resize
					if( $arrOptions['height'] !== null ) {
						$strMutation.= "/h/{$mcp->escapeString($arrOptions['height'])}";
					}
					
					// grayscale
					if( $arrOptions['grayscale'] !== null ) {
						$strMutation.= "/gs/{$mcp->escapeString( (int) ((bool) $arrOptions['grayscale'])) }";
					}
					
					// @todo: b/w transformation
					
					return "IF($strColumn IS NOT NULL,CONCAT('/img.php/',$strColumn,'$strMutation'),NULL)";
				}
				
				,'postprocess_select'=>null
				
			)
		);
		
	}
	
	/*
	* Generate virtual field definitions for each config value except
	* those that are stored as fields - overload the config using the
	* dynamic fields.
	* 
	*  @return array static config fields defined via XML file
	*/
	private function _fetchStaticConfigFields() {
		
		$fields = array();
		
		// Get the entire configuration schema
		$config = $this->_objMCP->getConfigSchema();
		
		/*
		* Convert each static field to a virtual field
		* definition. Dynamic fields will be flaged with:
		* dynamic_field whereas those defined in the config
		* XML are not.
		*/
		
		foreach($config as $name=>&$def) {
			
			// skip over dynamic fields
			if( isset($def['dynamic_field']) ) continue;
			
			$fields[] = array(
				'label' 				=> $def['label']
				,'path' 				=> $name
				,'column'				=> $name
				,'type'					=> 'TEXT'
				,'compatible' 			=> 'select'
				,'relation' 			=> null
				,'relation_type' 		=> null
				,'dynamic' 				=> false
				,'actions'				=> ''
				,'primary' 				=> false
				,'options'				=> array()
				
				// These columns are only used for dynamic fields
				,'entity_type'=>null
				,'entities_id'=>null 
				,'sites_id'=>null 
				,'entities_primary_key'=>null
				
				,'preprocess_select'=>null
				,'postprocess_select'=>null
				
			);
			
		}
		
		return $fields;
		
	}
	
	/*
	* Add necessary filter to query for nodes, terms, etc of certain context
	* based the view base path. 
	* 
	* @param obj query object
	* @param str view type
	*/
	private function _addQueryEntityContextFilter($objQuery,$strViewType) {

		$strBase = null;
		$intId = null;
		
		if(strpos($strViewType,':') !== false) {
			list($strBase,$intId) = explode(':',$strViewType,2);
		} else {
			$strBase = $strViewType;
		}

		switch($strBase) {
			
			case 'Node':
				// The base table will always have an alias of t1 also only select non-deleted items
				$objQuery->where[] = "t1.node_types_id = {$this->_objMCP->escapeString($intId)} AND t1.deleted = 0";
				break;
				
			case 'Term':
				// @TODO: this requires a little more though considering the hierarchy
				// $objQuery->where[] = "t1.vocabulary_id = {$this->_objMCP->escapeString($intId)}";
				break;
				
			case 'User':
				// @todo: need to figure out the initial intention here
				// $objQuery->where[] = "t1.sites_id = {$this->_objMCP->escapeString($this->_getSitesId())}";
				break;
				
			case 'Config':
				// @todo: need to determine how to this
				break;
				
			default:
			
		}
		
	}
	
	/*
	* Get the actual value for a view argument
	* 
	* @param str value as stored in database
	* @param str context as stored in database
	* @param str type as stored in database
	* @param str context_routine as stored in database
	* @param str serialized arguments to send to function or method calls
	* @return mix true, resolved value of argument
	*/
	private function _getArgumentsActualValue($strValue,$strContext,$strType,$strRoutine=null,$strArgs=null) {
		
		$mixValue = null;
		
		// 'static','post','get','request','global_arg','module_arg','dao','function','class','view'
		switch($strContext) {
			
			// Use value as statically defined
			case 'static':
				$mixValue = $strValue;
				break;
			
			// from post array
			case 'post':				
				$mixValue = $this->_objMCP->getPost($strValue);				
				break;
			
			// from get array
			case 'get':
				$mixValue = $this->_objMCP->getGet($strValue);
				break;
			
			// from request array
			case 'request':
				$mixValue = $this->_objMCP->getRequest($strValue);
				break;
			
			// Get value located at index position (index position is the value)
			case 'global_arg':
				$arrRequestArgs = $this->_objMCP->getRequestArgs();
				$mixValue = isset($arrRequestArgs[$strValue])?$arrRequestArgs[$strValue]:null;
				break;

			// Get value located at index (index position is the value) - relative to args passed to module
			case 'module_arg':
				// @todo: need a way to reference arguments passed to module
				break;
			
			// Get dao, call method with possible arguments (value represents dao pkg)
			case 'dao':
				
				// Fetch dao
				$objDAO = $this->_objMCP->getInstance($strValue);
				
				// Unserialize possible arguments
				$arrDAOArgs = $strArgs !== null?base64_decode(unserialize($strArgs)):array();
				
				// call the method
				$mixValue = call_user_func_array(array($objDAO,$strRoutine),$arrDAOArgs);
				
				break;

			// Call a global or namespace function (value is functions name)
			case 'function':
				
				// Unserialize possible arguments
				$arrFuncArgs = $strArgs !== null?base64_decode(unserialize($strArgs)):array();

				// call the function
				$mixValue = call_user_func_array($strValue,$arrFuncArgs);
				
				break;
				
			// Call a static class method (value is the class name)
			case 'class':
				
				// Unserialize possible arguments
				$arrFuncArgs = $strArgs !== null?base64_decode(unserialize($strArgs)):array();

				// call the method
				$mixValue = call_user_func_array(array("$strValue::$strRoutine"),$arrFuncArgs);				
				
				break;

			// reference to another view - embedded views
			case 'view':
				
				// @todo: figure out how do this best - this is a special case
				
				break;
				
			default: // error out or something
			
		}
		
		/*
		* Support for argument arrays 
		*/
		$rebuild = array();			
		foreach( ( is_array($mixValue)?$mixValue:array($mixValue) ) as $value) {
			
			if( $value === null ) continue;
			
			// Cast the value to the correct type
			switch($strType) {
			
				case 'int':
					$rebuild[] = (int) $value;
					break;
				
				case 'string':
					$rebuild[] = (string) $value;
					break;
				
				case 'float':
					$rebuild[] = (float) $value;
					break;
				
				case 'bool':
					// cast to int because no bool value exists in SQL - uses 0 and 1
					$rebuild[] = (int) $value;
					break;
				
				default: //error out
			
			}
			
		}
		
		return is_array($mixValue)?$rebuild:array_pop($rebuild);
		
	}
	
	/*
	* Add base entity permissions to view result set rows
	* 
	* @param result set rows
	* @param obj view
	*/
	private function _addPermissionsToViewResultSet(&$arrRows,$objView) {
		
		$ids = array();
		
		foreach($arrRows as $id=>&$row) {
			$ids[] = $id;
		}
		
		if( empty($ids) ) return;
		
		$edit = array();
		$delete = array();
		$read = array();
		
		switch($objView->base) {
			
			case 'Node': // node base
				$edit = $this->_objMCP->getPermission(MCP::EDIT,'Node',$ids);
				$delete = $this->_objMCP->getPermission(MCP::DELETE,'Node',$ids);
				$read = $this->_objMCP->getPermission(MCP::READ,'Node',$ids);
				break;
				
			case 'Term': // term base
				$edit = $this->_objMCP->getPermission(MCP::EDIT,'Term',$ids);
				$delete = $this->_objMCP->getPermission(MCP::DELETE,'Term',$ids);
				$read = $this->_objMCP->getPermission(MCP::READ,'Term',$ids);
				break;
				
			case 'NodeType': // node type base
				$edit = $this->_objMCP->getPermission(MCP::EDIT,'NodeType',$ids);
				$delete = $this->_objMCP->getPermission(MCP::DELETE,'NodeType',$ids);
				$read = $this->_objMCP->getPermission(MCP::READ,'NodeType',$ids);
				break;
				
			case 'Vocabulary': // vocab base
				$edit = $this->_objMCP->getPermission(MCP::EDIT,'Vocabulary',$ids);
				$delete = $this->_objMCP->getPermission(MCP::DELETE,'Vocabulary',$ids);
				$read = $this->_objMCP->getPermission(MCP::READ,'Vocabulary',$ids);
				break;
				
			case 'Site': // site base
				$edit = $this->_objMCP->getPermission(MCP::EDIT,'Site',$ids);
				$delete = $this->_objMCP->getPermission(MCP::DELETE,'Site',$ids);
				$read = $this->_objMCP->getPermission(MCP::READ,'Site',$ids);
				break;
				
			default:
				return;
			
		}
		
		foreach($arrRows as $id=>&$row) {
			$row['allow_edit'] = $edit[$id]['allow'];
			$row['allow_delete'] = $delete[$id]['allow'];
			$row['allow_read'] = $read[$id]['allow'];
		}
		
	}
	
	/*
	* Run describe query on given table
	* 
	* @param str table name
	* @return array table schema
	*/
	private function _describe($table) {
		
		// Get all the columns for the table
		if( isset($this->_arrCachedDescribe[$table]) ) {
			
			// use the cached value to eliminate a query
			return  $this->_arrCachedDescribe[$table];
			
		}
			
		// Get the table schema
		$this->_arrCachedDescribe[$table] = $this->_objMCP->query("DESCRIBE $table");
			
		// cache the describe query for the table (it won't change)
		return $this->_arrCachedDescribe[$table];
		
	}
	
	/*
	* Get table from a path
	* 
	* @param StdClass branch data
	* - alias: unique alias name
	* - path: full view path
	* 
	* @param array ancestory array - closest to farthest
	* @param int counter (used to generate unique alias for additional tables)
	* 
	* @return obj StdClass w/ properties: 
	* - sql: Join SQL
	* - add: Additional tables used to resolve join w/ aliases
	*/
	public function getJoin($objBranch,$objParent,&$intCounter) {
		
		/*
		* Data structure to return containing the join SQL and possible additional tables
		* w/ alias data used to resolve full join. Additional tables will be present for dynamic fields
		* because dynamic fields require an additional "invisible" table MCP_FIELDS. At this time
		* dynamic fields are the only case that make use of additional tables being added at the join
		* step though its entirely possible other cases come along in the future. 
		*/
		$objJoin = new StdClass();
		$objJoin->sql = '';
		$objJoin->add = array();
		
		/*
		* Get the fields column definition including relation and dynamic flag (bool)
		* 
		* relation will be used to derive joins
		* 
		* dynamic will be used to derive joins for enitity dynamic fields which includes
		* the addition of a "invisible" lookup table.
		*/
		$arrField = $this->fetchFieldByViewPath($objBranch->path);
		// echo "<p>{$objBranch->path}<p>";
		
		/*
		* @todo: If the field does not exist we can either error or attempt to continue. It
		* is probably best to error or throw an exception. The exceptions could
		* be collected bthe the callee so that some useful feedback in regards to fields
		* that can not be found can be presented for debugging purposes. Valid paths
		* is probably only going to be the case when dealing with dynamic fields that may have
		* been deleted. In that case we can make people aware of the issue or even create a task
		* to run all views and provide feedback of missing/invalid paths.
		*/
		if( !$arrField ) {
			// perhaps throw an exception here rather than returning false? - seems like the best idea
			return false;
		}
		
		/*
		* Handler "dynamic field". This adds the necessary middle table to properly
		* resolve dynamic fields w/ available reference to possible default value
		* stored in the middle (field config) table. Referencing the middle table
		* will be important when building filters, or sorting when a row entity
		* does not have a field value defined for a field but the field has a default
		* value. So if the default valueo of file foo is 0 and one would like all items
		* where foo is 0 there needs to be some logic to see if a field value exists
		* otherwise compare against the default within the field config table. This is
		* probably something very easy the overlook but incredibly important considering
		* the purpose of the default value field config column which is push a value to
		* all entities of the type regardless if they have a explicit field vale defined.
		*/
		if( $arrField['dynamic'] ) {
			
			$mid = 't'.(++$intCounter);
			$objBranch->alias = 't'.(++$intCounter);
			
			// @todo: account for null entities_id
			$objJoin->sql =      
			          "LEFT OUTER
		                     JOIN
		                        MCP_FIELDS $mid 
		                       ON 
		                        $mid.sites_id = {$arrField['sites_id']}
		                      AND 
		                        $mid.entity_type = '{$arrField['entity_type']}' 
		                      AND 
		                        $mid.entities_id = {$arrField['entities_id']} 
		                      AND 
		                        $mid.cfg_name = '{$arrField['name']}' 
		                     LEFT OUTER 
		                     JOIN 
		                        MCP_FIELD_VALUES {$objBranch->alias} 
		                       ON 
		                        $mid.fields_id = {$objBranch->alias}.fields_id 
		                      AND 
		                        {$objBranch->alias}.rows_id = {$objParent->alias}.{$arrField['entities_primary_key']} ";		
			
		
		/*
		* Handler "basic" join. This handles a generic concrete
		* foreign key relationship. Examples are nodes to user, site to creator or anything
		* that is a explicitly defined relationship within tables using foreign keys.
		*/
		} else if( $arrField['relation'] ) {
			
			// create unique alias
			$objBranch->alias = 't'.(++$intCounter);
			
			// Get the primary key for the relation table
			$col = $this->fetchFieldByViewPath("{$arrField['relation']}/id");
			
			// Build out SQL
			$objJoin->sql =
			    "LEFT OUTER 
		               JOIN 
		                  {$this->_fetchTableByViewType($arrField['relation'])} {$objBranch->alias} 
		                 ON 
		                  {$objParent->alias}.{$arrField['column']} = {$objBranch->alias}.{$col['column']} ";
			
		} else {
			
		}
		
		/*
		* Dynamic field that has foreign key reference to another table such as; media
		*/
		if($arrField['dynamic'] && $arrField['relation']) {
				
			$alias = 't'.(++$intCounter);
		
			$objJoin->sql.= 
			          "LEFT OUTER 
			                 JOIN 
			                    {$this->_fetchTableByViewType($arrField['relation'])} $alias 
			                   ON 
			                    {$objBranch->alias}.{$arrField['column']} = $alias.{$arrField['db_ref_col']}";

			$objAdd = new StdClass();
			$objAdd->alias = $alias;
			$objAdd->path = $objBranch->path;
			
			$objJoin->add[] = $objAdd;
			    
		}
		
		return $objJoin;
	
	}
	
	
}
?>