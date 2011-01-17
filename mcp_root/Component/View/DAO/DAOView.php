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
			,'system_name'=>'Navigation'
			,'table'=>'MCP_NAVIGATION'
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
			,'system_name'=>'NavigationLinks'
			,'table'=>'MCP_NAVIGATION_LINKS'
		)
		,array(
			'human_name'=>'Images'
			,'system_name'=>'Image'
			,'table'=>'MCP_MEDIA_IMAGES'
		)
	);
	
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
		
		// Navigation DAO is required to generate list of menus
		$objDAONavigation = $this->_objMCP->getInstance('Component.Navigation.DAO.DAONavigation',array($this->_objMCP));
		
		$types = array();
		
		// populate types array with static items
		foreach($this->_arrTables as $type) {
			
			// skip over nodes, terms and links
			if( in_array($type['system_name'],array('Node','Term','NavigationLink')) ) continue;
			
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
		$menus = $objDAONavigation->listAllNavs(
			"CONCAT('NavigationLink:',n.navigation_id) value,n.menu_title label"
			,"n.sites_id = {$this->_objMCP->escapeString($this->_objMCP->getSitesId())} AND n.deleted = 0"
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
		
		// Get all the columns for the table
		$columns = $this->_objMCP->query("DESCRIBE $table");
		
		// Get all concrete fields
		foreach($columns as $column) {
			
			// Chance to augment/change types
			$field = $this->_tableColumnToField($column,$table);
			
			// False return indicates field to ignore
			if($field === false) continue;
			
			$fields[] = $field;
		}
		
		// Add any dynamic column
		foreach($this->_fetchDynamicFieldsByViewType($strViewType) as $field) {			
			$fields[] = $field;
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
	* Fetch a single view display by its ID
	* 
	* NOTE: This will fully resolve a view including
	* overrides. Its "final" state will be represented.
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
			      ,sp.weight sorting_priority_weight
			      
			  FROM
			      MCP_VIEW_DISPLAYS d
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
			$view->system_name = $row['system_name'];
			$view->human_name = $row['human_name'];
			
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
		
		return $view;
		
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
	* not be added / is not compatible. 
	* 
	* @param array describe column row
	* @param str table name
	* @return array view entity field
	*/
	private function _tableColumnToField($arrColumn,$strTable) {
		
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
			
			case 'nodes_id':
				$label = 'Node';
				$relation = 'Node';
				$path = 'node';
				break;
				
			case 'node_types_id':
				$label = 'Node Type';
				$relation = 'NodeType';
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
				break;
				
			case 'node_url':
				$label = 'URL';
				break;
				
			case 'node_published':
				$label = 'Published';
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
			'label'=>$label
			,'path'=>$path 
			,'column'=>$column
			,'type'=>$type
			,'usedby'=>$compatible
			,'relation'=>$relation
			,'relation_type'=>$relation_type
			,'dynamic'=>false
			,'actions'=>$actions
			,'primary'=>$primary
		);
		
	}
	
	/*
	* Get dynamic columns for view type 
	* 
	* @param str view type
	* @return array dynamic columns
	*/
	private function _fetchDynamicFieldsByViewType($strViewType) {
		
		$entity_type = null;
		$entities_id = null;
		
		$strBase = null;
		$intId = null;
		
		list($strBase,$intId) = explode(':',$strViewType,2);
		$entities_id = $intId;
		
		// Get the Field DAO
		$objDAOField = $this->_objMCP->getInstance('Component.Field.DAO.DAOField',array($this->_objMCP));
		
		/*
		* Translate to correct parent entity type or fields 
		*/
		switch($strBase) {
			case 'Node':
				$entity_type = 'MCP_NODE_TYPES';
				break;
				
			case 'User':
				$entity_type = 'MCP_SITES';
				$entities_id = $this->_objMCP->getSitesId();
				break;
				
			case 'Term':
				$entity_type = 'MCP_TERMS';
				break;
				
			case 'Config':
				$entity_type = 'MCP_CONFIG';
				$entities_id = 0;
				break;
				
			case 'Image':
			case 'Site':
			case 'NodeType':
			case 'Vocabulary':
			default:
				return false;
		}
		
		// Case statement to convert db_value to type
		$values = $this->_objMCP->query('DESCRIBE MCP_FIELD_VALUES');	
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
		return $objDAOField->listFields(
			" f.cfg_label label
			 ,f.cfg_name path
			 ,f.cfg_name `name`
			 ,CONCAT('db_',f.db_value) `column`
			 ,$type type
			 ,'select,filter,sort' usedby
			 ,$relation relation
			 ,IF(cfg_multi = 1,'many','one') relation_type
			 ,1 dynamic
			 ,'' actions
			 ,0 `primary`"
			,sprintf(
				"f.sites_id = %s AND f.entity_type = '%s' AND f.entities_id %s"
				,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
				,$this->_objMCP->escapeString($entity_type)
				,$entities_id === null?"IS NULL":"= {$this->_objMCP->escapeString($entities_id)}"
			)
		);
		
	}
	
}

?>
