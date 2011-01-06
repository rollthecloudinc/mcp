<?php
/*
* View data access layer (will probably eventually split up amoung multiple classes, but for now its all here)
*/
$this->import('App.Core.DAO');

//include('plugins.php');
class MCPDAOVD extends MCPDAO {
	
	public function __construct(MCP $objMCP) {
		
		parent::__construct($objMCP);
		
		// load all items for now eventually use a plugin manager of some kind
		$this->_objMCP->import('App.Resource.VD.Item.Node');
		$this->_objMCP->import('App.Resource.VD.Item.NodeType');
		$this->_objMCP->import('App.Resource.VD.Item.Site');
		$this->_objMCP->import('App.Resource.VD.Item.Term');
		$this->_objMCP->import('App.Resource.VD.Item.User');
		$this->_objMCP->import('App.Resource.VD.Item.Vocabulary');
		
	}

	public function fetchViewById($fields) {
	
		/*
		* Selected fields
		*/
		$fields = array(
		
			//'Node/classification/system_name'
			
			/*"Node/classification/filter:{this.alias}.system_name = 'product' AND {this.alias}.pkg IS NULL AND {this.alias}.sites_id = {#SITES_ID#}"
			,"Node/sort:{this.alias}.node_title ASC"*/
			
			'User/username'
			,'User/field/profile'
			,'User/field/avatar/image_size'
			,'User/site/creator/id'
			,'User/site/id'
			,'User/site/filter:{this.alias}.sites_id IN (1,4)'
			,'User/site/sort:{this.alias}.site_name ASC'
		
			/*'Node/title'
			,'Node/classification/system_name'
			,'Node/field/ad_image/image_size'
			,'Node/field/manufacturers_id/field/category'
			,'Node/field/manufacturers_id/field/category/field/blah'
			,'Node/body'
			,'Node/field/main_image/image_size'
			,'Node/field/manufacturer/id'
			,'Node/field/msrp'
			,'Node/field/price'
			,'Node/author/id'
			,'Node/site/id'
			,'Node/author/field/profile'
			,'Node/classification/creator/site/creator/field/profile'
			,'Node/classification/filter:{this.alias}.node_types_id = 89'
			,'Node/sort:{this.alias}.node_title ASC'*/
		
			//'NodeType/nodes/id'
			//,'NodeType/nodes/title'
			
			//'Vocabulary/terms/id'
		);

		/*
		* Convert paths to hierarchical tree representation
		*/
		$tree = array();
		foreach($fields as $field) {
			$current =& $tree;
			foreach(explode('/',$field) as $piece) {
				$current =& $current[$piece];
			}
		}
		
		//echo '<pre>',print_r($tree),'</pre>';

		/*
		* Walk the tree to build sql
		*/
		$counter=0;
		$dao = $this;
		
		// select, from, where and sorting clauses (so another function isn't needed to piece together everything)
		$query = array(
			'select'=>array()
			,'from'=>''
			,'where'=>array()
			,'orderby'=>array()
		);
		
		$walk = function($branches,$ancestory,$walk) use (&$counter,&$query,$dao) {

			if(!$branches) return;
			$sql = '';
	
			$return = array();
			foreach($branches as $branch=>$children) {
				
				$branchData = array();
	
				$data = array(
					'name'=>$branch
					,'alias'=>'t'.(++$counter)
				);
				
				$branchData['name'] = $data['name'];
				$branchData['alias'] = $data['alias'];
				
				$branchData['select'] = array();
				$branchData['filter'] = array();
				$branchData['sort'] = array();
		
				$join = array('sql'=>'');
				if($branch !== 'field') {	
					$join = $dao->views_join($data,$ancestory,$children,$counter);
					/*$sql.= $join['sql'];*/ $query['from'].= $join['sql'];
					$branchData['from'] = $join['sql'];
				}
		
				$copy = $ancestory;
				array_unshift($copy,$data);
				
				$alias = $data['alias'];
				
				if(isset($join['add']) && !empty($join['add'])) {
					foreach($join['add'] as $add) {
						$alias = $add['alias']; // override the base with the left most alias for added tables
						array_unshift($copy,$add);
					}
				}
		
				//$sql.= $walk($children,$copy,$walk);
				$walked = $walk($children,$copy,$walk);
				
				// bind any columns without SQL to this entity for selection (atomic field) - ommit special value field
				$remove = array();
				
				// bug: not selecting atomic fields
				foreach($walked as $index=>$item) {
					if( (empty($item['from']) && $item['name'] !== 'field')) {

						// determine whether item is a filter, sort or normal column selection
						if(strpos($item['name'],'filter:') === 0) {
							$branchData['filter'][] = array(
								'sql'=>str_replace('{this.alias}',$alias,substr($item['name'],7)) // strip out the filter flag and replace alias
							);
							
							$query['where'][] = str_replace('{this.alias}',$alias,substr($item['name'],7));
							
						} else if(strpos($item['name'],'sort:') === 0) {
							$branchData['sort'][] = array(
								'sql'=>str_replace('{this.alias}',$alias,substr($item['name'],5)) // strip out the sot flag and replace alias
							);
							
							$query['orderby'][] = str_replace('{this.alias}',$alias,substr($item['name'],5));
							
						} else {
							
							$copy2 = $copy;
							array_shift($copy2,array('name'=>$item['name']));
							
							$fields = array();
							
							if($copy2) {
								$entity = $dao->get_entity(array_reverse($copy2));
								$fields = $entity->getFields();
							}
							
							$field = isset($fields[$item['name']],$fields[$item['name']]['column'])?$fields[$item['name']]['column']:$item['name'];
							
							$branchData['select']["{$alias}_{$item['name']}"] = array(
								'name'=>$item['name']
								,'sql'=>"$alias.$field {$alias}_{$item['name']}"
							);
							
							$query['select'][] = "$alias.$field {$alias}_{$item['name']}";
							
						}
						
						$remove[] = $index;
					}
				}
				
				// remove the atomic columns that will be selected in the query
				foreach($remove as $index) unset($walked[$index]);
				
				$branchData['children'] = $walked;
				$return[] = $branchData;
		
			}
	
			//return $sql;
			return $return;
	
		};

		//echo '<pre>',print_r($walk($tree,array(),$walk)),'</pre>';
		$walk($tree,array(),$walk);
		
		//echo '<pre>',print_r($query),'</pre>';
		
		$sql = sprintf(
			'SELECT %s FROM %s %s %s'
			,implode(',',$query['select'])
			,$query['from']
			,!empty($query['where'])?'WHERE '.implode(' AND ',$query['where']):''
			,!empty($query['orderby'])?'ORDER BY '.implode(',',$query['orderby']):''
		);
		
		echo "<p>$sql</p>";
		
	
	}

	/*
	* Get entity by path
	*
	* @param array
	* @return obj entity instance
	*/
	public function get_entity($path) {
		
		$base = array_shift($path);	
		$class = "MCPVDItem{$base['name']}";
	
		$obj = new $class($this->_objMCP);
		
		foreach($path as $piece) {
			
			$fields = $obj->getFields();
			
			$col = isset($fields[$piece['name']])?$fields[$piece['name']]:null;
			
			if($col !== null) {
				
				if(isset($col['binding'])) {
					$class = "MCPVDItem{$col['binding']}";				
					$obj = new $class($this->_objMCP);
				}
				
			}
			
		}
		
		return $obj;

	}

	/*
	* Get info for a field to resolve foreign key references to other tables for
	* a fielded calue such as; image or term.
	*/
	private function get_field_info($entity_type,$cfg_name) {
		
		$sql = sprintf(
			"SELECT
			      f.db_ref_table `table`
			      
			      ,CASE
			     
			         WHEN f.db_value = 'varchar'
			         THEN 'db_varchar'
			         
			         WHEN f.db_value = 'bool'
			         THEN 'db_bool'
			         
			         WHEN f.db_value = 'int'
			         THEN 'db_int'
			         
			         WHEN f.db_value = 'price'
			         THEN 'db_price'
			         
			         WHEN f.db_value = 'text'
			         THEN 'db_text'
			         
			         ELSE ''
			         
			      END `column`
			      
			      ,f.db_ref_col `column2`
			   FROM
			      MCP_FIELDS f
			  WHERE
			      f.entity_type = '%s'
			    AND
			      f.cfg_name = '%s'
			    AND
			      f.sites_id = %s"
			      
			,$this->_objMCP->escapeString($entity_type)
			,$this->_objMCP->escapeString($cfg_name)
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
		);

		$data = array_pop($this->_objMCP->query($sql));
		
		if($data === null) {
			$data = array(
				'table'=>'--'
				,'column'=>'--'
				,'column2'=>'--'
			);
		}
		
		return $data;

	}

	/*
	* Get table from a path
	*/
	public function views_join($branch,$ancestory,$children,&$counter) {

		$return = array('sql'=>'','add'=>array());
	
		/*
		* Dynamic field
		*/
		if($ancestory && isset($ancestory[0]) && $ancestory[0]['name'] === 'field') {
	
			// resolve entity primary key column name, entity name and entities_id mapping column
			$copy = $ancestory;
			array_shift($copy);
		
			// Edge case to handle nested fields
			if(isset($ancestory[3]) && $ancestory[3]['name'] === 'field') {
				$entity = $this->get_entity( array($ancestory[1]) );
				$fields = $entity->getFields();
			} else {
				$entity = $this->get_entity( array_reverse($copy) );
				$fields = $entity->getFields();				
			}
		
			// name of entities primary key column
			$id = isset($fields['id'],$fields['id']['column'])?$fields['id']['column']:'';
		
			// entity type name
			$entity_type = $entity->getFieldEntityType();
		
			// entities_id relational column
			$entities_id = $entity->getFieldEntityId();
	
			$mid = 't'.(++$counter);
			$return['sql'] = "      LEFT OUTER
		                     JOIN
		                        MCP_FIELDS $mid 
		                       ON 
		                        $mid.sites_id = {$this->_objMCP->getSitesId()}
		                      AND 
		                        $mid.entity_type = '$entity_type' 
		                      AND 
		                        $mid.entities_id = {$ancestory[1]['alias']}.$entities_id 
		                      AND 
		                        $mid.cfg_name = '{$branch['name']}' 
		                     LEFT OUTER 
		                     JOIN 
		                        MCP_FIELD_VALUES {$branch['alias']} 
		                       ON 
		                        $mid.fields_id = {$branch['alias']}.fields_id 
		                      AND 
		                        {$branch['alias']}.rows_id = {$ancestory[1]['alias']}.$id ";
		                        
	
		/*
		* Basic join
		*/
		} else if($ancestory && $children) {
	
			// callback used to flatten ancestory array
			$flatten = function($item) { return $item['name']; };
		
			// get entity info
			$entity = $this->get_entity( array_reverse(  array_merge(array($branch),$ancestory)   )  );
		
			// get parent entity info
			$parent = $this->get_entity( array_reverse($ancestory) );
		
			// get relational column
			$fields = $parent->getFields();
			$col = isset($fields[$branch['name']],$fields[$branch['name']]['column'])?$fields[$branch['name']]['column']:'';
		
			// get other relational column
			$fields = $entity->getFields();
			$col2 = isset($fields['id'],$fields['id']['column'])?$fields['id']['column']:'';
	
			$return['sql'] = "LEFT OUTER 
		               JOIN 
		                  {$entity->getTable()} {$branch['alias']} 
		                 ON 
		                  {$ancestory[0]['alias']}.$col = {$branch['alias']}.$col2 ";
	
		/*
		* Root level item
		*/
		} else if($children) {
	
			// get entity info
			$entity = $this->get_entity(array($branch));
	
			$return['sql'] = "{$entity->getTable()} {$branch['alias']} ";
	
		/*
		* atomic field such as; node/id - in which case a join is not needed
		*/
		} else {
	
		}
		
		/*
		* Dynamic field that has foreign key reference to another table such as; media
		*/
		if($ancestory && isset($ancestory[0]) && $ancestory[0]['name'] === 'field') {
			
			// Get entity data
			$copy = $ancestory;
			$entity = $this->get_entity( array_reverse($copy) );
	
			// I think this will need to be resolved based on the node type or vocabulary being filtered.
			$info = $this->get_field_info($entity->getFieldEntityType(),$branch['name']);
	
			if(!in_array($info['table'],array('','--'))) {
				// get table name
				$table = $info['table'];
			
				// get relational column
				$col = $info['column'];
			
				// get other relational column
				$col2 = $info['column2'];
				
				$alias = 't'.(++$counter);
		
				$return['sql'].= "LEFT OUTER 
			                     JOIN 
			                        $table $alias 
			                       ON 
			                        {$branch['alias']}.$col = $alias.$col2 ";
			                        
			    $return['add'][] = array('name'=>'Term','alias'=>$alias);
			}
		}
		
	
		return $return;
	
	}
	
	
	/*
	* Get display data from db (testing) 
	* 
	* @param int displays id
	*/
	public function fetchDisplayById($intDisplaysId) {
		
		/*
		* Build query to pull back all view data including fields, filters, sorting and arguments 
		*/
		$strSQL = sprintf(
			"SELECT
			      d.id display_id
			      
			      ,d.human_name
			      ,d.system_name
			      
			      ,c.id field_id
			      ,c.path field_path
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
			      ,f.path filter_path
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
			      ,s.path sorting_path
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
			      MCP_VD_DISPLAYS d
			  LEFT OUTER
			  JOIN
			      MCP_VD_FIELDS c
			    ON
			      d.id = c.displays_id
			   AND
			      c.active = 1
			  LEFT OUTER
			  JOIN
			      MCP_VD_FIELD_OPTIONS co
			    ON
			      c.id = co.fields_id
			   AND
			      co.active = 1
			  LEFT OUTER
			  JOIN
			      MCP_VD_FILTERS f
			    ON
			      d.id = f.displays_id
			   AND
			      f.active = 1
			  LEFT OUTER
			  JOIN
			     MCP_VD_FILTER_VALUES fv
			    ON
			     f.id = fv.filters_id
			   AND
			     fv.active = 1
			  LEFT OUTER
			  JOIN
			     MCP_VD_SORTING s 
			    ON
			     d.id = s.displays_id
			   AND
			     s.active = 1
			  LEFT OUTER
			  JOIN
			     MCP_VD_SORTING_PRIORITY sp
			    ON
			     s.id = sp.sorting_id
			   AND
			     sp.active = 1 
			 WHERE
			     d.id = %s"
			,$this->_objMCP->escapeString($intDisplaysId)
		);
		
		//echo "<p>$strSQL</p>";
		
		$rows = $this->_objMCP->query($strSQL);

		$display = array(
			'display'=>array()
			,'fields'=>array()
			,'filters'=>array()
			,'sorting'=>array()
		);
		
		/*
		* Build domain level data structure 
		*/
		foreach($rows as $row) {
			
			// Basic display info
			$display['display']['id'] = $row['display_id'];
			$display['display']['system_name'] = $row['system_name'];
			$display['display']['human_name'] = $row['human_name'];
			
			// parse fields
			if($row['field_id'] !== null && !isset($display['fields'][$row['field_id']])) {
				foreach($row as $col=>$value) {
					if(strpos($col,'field_') !== 0 || strpos($col,'field_option_') === 0) continue;
					$display['fields'][$row['field_id']][substr($col,6)] = $value;
				}
			}
			
			// parse field options
			if($row['field_option_id'] !== null) {
				foreach($row as $col=>$value) {
					if(strpos($col,'field_option_') !== 0) continue;
					$display['fields'][$row['field_id']]['options'][$row['field_option_id']][substr($col,13)] = $value;
				}
			}
			
			// parse filters
			if($row['filter_id'] !== null && !isset($display['filters'][$row['filter_id']])) {
				foreach($row as $col=>$value) {
					if(strpos($col,'filter_') !== 0 || strpos($col,'filter_value_') === 0) continue;
					$display['filters'][$row['filter_id']][substr($col,7)] = $value;
				}
			}
			
			// parse filter values
			if($row['filter_value_id'] !== null) {
				foreach($row as $col=>$value) {
					if(strpos($col,'filter_value_') !== 0) continue;
					$display['filters'][$row['filter_id']]['values'][$row['filter_value_id']][substr($col,13)] = $value;
				}
			}
			
			// parse sorting
			if($row['sorting_id'] !== null && !isset($display['sorting'][$row['sorting_id']])) {
				foreach($row as $col=>$value) {
					if(strpos($col,'sorting_') !== 0 || strpos($col,'sorting_priority_') === 0) continue;
					$display['sorting'][$row['sorting_id']][substr($col,8)] = $value;
				}
			}
			
			// parse sorting priorities
			if($row['sorting_priority_id'] !== null) {
				foreach($row as $col=>$value) {
					if(strpos($col,'sorting_priority_') !== 0) continue;
					$display['sorting'][$row['sorting_id']]['priorities'][$row['sorting_priority_id']][substr($col,17)] = $value;
				}
			}
		}
		
		$this->_parseDisplay($display);
		
		return $display;
		
	}
	
	/*
	* Experimental to transform display into expected array format to feed to query builder 
	*/
	private function _parseDisplay($display) {
		
		$paths = array();
		//echo '<pre>',print_r($display),'</pre>';
		
		foreach($display['fields'] as &$field) {
			$paths[] = $field['path'];
		}
		
		/*
		* Filter Handler --------------------------------------------------------------- 
		*/
		
		foreach($display['filters'] as &$filter) {
			
			$arrPath = array();
			foreach(explode('/',$filter['path']) as $name) $arrPath[] = array('name'=>$name);
			
			$entity = $this->get_entity($arrPath);
			$fields = $entity->getFields();
			
			// add filter: flag to denote field as filter param
			$index = strrpos($filter['path'],'/') + 1;
			$path = substr($filter['path'],0,$index);
			$field = substr($filter['path'],$index);
			
			// Resolve field to true column name when not exist assume field
			$field = isset($fields[$field],$fields[$field]['column'])?$fields[$field]['column']:$field;
			
			$parts = array();
			
			// @todo: handle special IN and NOT IN case
			
			// Get all the values
			if(isset($filter['values']) && !empty($filter['values'])) {
				foreach($filter['values'] as &$value) {
					// @todo determine whether value needs to enclosed in quotes or use placehodlers w/ binding
					
					// like, regex and fulltext edge case handling w/ default
					switch($filter['comparision']) {
						
						case 'like':
							
							// like format ie. %s,%s%,s%
							$wildcard = $filter['wildcard'];
							
							// value may change the default wildcard for the entire filter
							if($value['wildcard'] !== null) {
								$wildcard = $value['wildcard'];
							}
							
							// negation edge case ie. LIKE and NOT LIKE
							$operator = strcmp($filter['conditional'],'none') === 0?' NOT LIKE ':' LIKE ';
							
							$parts[] = '{this.alias}.'.$filter.$operator."'".sprintf($wildcard,$value['value'])."'";
							break;
						
						case 'regex':
							
							// regular expression
							$regex = $filter['regex'];
							
							// value may override regex
							if($value['regex'] !== null) {
								$regex = $value['regex'];
							}
							
							// negation edge case ie. NOT REGEXP and REGEXP
							$operator = strcmp($filter['conditional'],'none') === 0?' NOT REGEXP ':' REGEXP ';
							
							$parts[] = '{this.alias}.'.$filter.$operator."'".$regex."'";
							break;
							
						case 'fulltext':
							// @todo build in fulltext searching capabilities
							break;
						
						default:
							
							$operator = $filter['comparision'];
							
							// negation edge case for = and !=
							if(strcmp($operator,'=') === 0 && strcmp($filter['conditional'],'none') === 0) {
								$operator = '<>';
							}
							
							$parts[] = '{this.alias}.'.$field.' '.$operator.' '.(is_numeric($value['value'])?$value['value']:"'{$value['value']}'");
					}
				}
			}
			
			// The conditional will determine the format of the values and separator ie. and | or
			switch($filter['conditional']) {
				
				case 'all':	
					$paths[] = $path.'filter:'.implode(' AND ',$parts);
					break;
				
				case 'none':
					$paths[] = $path.'filter:'.implode(' AND ',$parts);
					break;
					
				case 'one':
					$paths[] = $path.'filter:'.implode(' OR ',$parts);
					break;
					
				default: // when none of them are met, there is a problem - error
				
			}
			
			
		}
		
		/*
		* Sorting handler ------------------------------------------------------------- 
		*/
		
		// build sorting field format for query builder
		foreach($display['sorting'] as &$sorting) {
			
			$arrPath = array();
			foreach(explode('/',$sorting['path']) as $name) $arrPath[] = array('name'=>$name);
			
			$entity = $this->get_entity($arrPath);
			$fields = $entity->getFields();
			
			// add sorting: flag to denote field as sort param
			$index = strrpos($sorting['path'],'/') + 1;
			$path = substr($sorting['path'],0,$index);
			$field = substr($sorting['path'],$index);
			
			// Resolve field to true column name when not exist assume field
			$field = isset($fields[$field],$fields[$field]['column'])?$fields[$field]['column']:$field;
			
			// handling basic and field() based sorting
			// @todo add RAND() support handler
			if(isset($sorting['priorities']) && !empty($sorting['priorities'])) {
				
				$values = array();
				foreach($sorting['priorities'] as &$priority) {
					// @todo: determine whether value needs to be enclosed in quotes
					$values[] = is_numeric($priority['value'])?$priority['value']:"'{$priority['value']}'";
				}
				
				// place values in correct order without incurring query costs
				usort($values,function($a,$b) {
    				return $a['weight'] != $b['weight']?($a['weight'] < $b['weight']) ? -1 : 1 : 0;
				});
				
				$paths[] = $path.'sort:FIELD({this.alias}.'.$field.','.implode(',',$values).')'.' '.strtoupper($sorting['order']);
				unset($values);
				
			} else {
				
				$paths[] = 'sort:{this.alias}.'.$field.' '.strtoupper($sorting['order']);
				
			}
			
		}
		
		$this->fetchViewById($paths);
		
		//echo '<pre>',print_r($paths),'</pre>';
		
	}

}
?>