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

	public function fetchViewById() {
	
		/*
		* Selected fields
		*/
		$fields = array(
		
			'Node/classification/system_name'
		
			/*'User/username'
			,'User/field/profile'
			,'User/field/avatar/size'
			,'User/site/creator/id'
			,'User/site/id'*/
		
			//'Node/title'
			//,'Node/classification/system_name'
			//,'Node/field/ad_image/size'
			//,'Node/field/manufacturers_id/field/category'
			//,'Node/field/manufacturers_id/field/category/field/blah'
			//,'Node/body'
			//,'Node/field/main_image/size'
			//,'Node/field/manufacturer/id'
			//,'Node/field/price'
			//,'Node/author/id'
			//,'Node/site/id'
			//,'Node/author/field/profile'
			//,'Node/classification/creator/site/creator/field/profile'
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

		/*
		* Walk the tree to build sql
		*/
		$counter=0;
		$dao = $this;
		$walk = function($branches,$ancestory,$walk) use (&$counter,$dao) {

			if(!$branches) return;
			$sql = '';
	
			$schema = array('sql'=>'');
			foreach($branches as $branch=>$children) {
	
				$data = array(
					'name'=>$branch
					,'alias'=>'t'.(++$counter)
				);
		
				$join = array('sql'=>'');
				if($branch !== 'field') {	
					$join = $dao->views_join($data,$ancestory,$children,$counter);
					$sql.= $join['sql'];
				}
		
				$copy = $ancestory;
				array_unshift($copy,$data);
				
				if(isset($join['add']) && !empty($join['add'])) {
					foreach($join['add'] as $add) array_unshift($copy,$add);
				}
		
				$sql.= $walk($children,$copy,$walk);
		
			}
	
			return $sql;
	
		};

		echo $walk($tree,array(),$walk);
	
	}

	/*
	* Get entity by path
	*
	* @param array
	* @return obj entity instance
	*/
	private function get_entity($path) {
		
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

}
?>