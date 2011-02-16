<?php
$this->import('App.Core.DAO');
$this->import('App.Core.Permission');

/*
* Get permissions for dynamic fields
* 
* This manages the concrete field definitions, not
* the field value. At this time a user may change the value
* of a field just like any other for a entity type. So long
* as a user can edit the entity they may change the value of a field.
* 
* However, users may not alter the concrete definition of a field
* unless they have permission to. That is what the below class
* authorizes, not field values, field definition as changes to those
* are likely to have a drastic impact if someone doesn't know what they
* are doing.
*/
class MCPPermissionField extends MCPDAO implements MCPPermission {
	
	/*
	* Create a new dynamic field for entity type
	* 
	* @param array entity types
	* @return array permissions
	*/
	public function add($ids) {
		
		$permissions = $this->_c($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_add']
				,'msg_dev'=>$permission['deny_add_msg_dev']
				,'msg_user'=>'You are not allowed to create field for specified entity.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>$permission['deny_add_msg_dev']
				,'msg_user'=>'You are not allowed to create field for specified entity.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Read concete field definition
	* 
	* @param array fields ids
	* @return array permissions
	*/
	public function read($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_read']
				,'msg_dev'=>$permission['deny_read_msg_dev']
				,'msg_user'=>'You are not allowed to see specified field.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to see specified field.'
				,'msg_user'=>'You are not allowed to see specified field.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Edit concrete field definition
	* 
	* @param array fields ids
	* @return array permissions
	*/
	public function edit($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_edit']
				,'msg_dev'=>$permission['deny_edit_msg_dev']
				,'msg_user'=>'You may not edit field.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to edit specified field'
				,'msg_user'=>'You may not edit field.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Delete a concrete field definition
	*
	* @param array fields ids
	* @return array permissions
	*/
	public function delete($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_delete']
				,'msg_dev'=>$permission['deny_delete_msg_dev']
				,'msg_user'=>'You may not delete field.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to delete specified field'
				,'msg_user'=>'You may not delete field.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Read, update and edit query builder
	* 
	* NOTE: Though other _rud() method in plugins take
	* on a similar implementation this one is quite different
	* considering the additional logic necessary to deal with dynamic fields.
	* 
	* @param array array of fields ids
	* @param int users id
	* @return array permission data
	*/
	private function _rud($fieldIds,$usersId) {
		
		/*
		* First locate each fields raw data. This
		* must be done because the join logic will
		* change based on the fields entity.
		* 
		* Can't gaurentee all fields are for same entity - bad assumption.
		*/
		$fields = $this->_objMCP->query(sprintf(
			"SELECT
			       f.fields_id
			      ,CONCAT(f.entity_type,'-','FIELD') item_type
			   FROM 
			      MCP_FIELDS f
			  WHERE
			      f.fields_id IN (%s)"
			      
			,$this->_objMCP->escapeString(implode(',',$fieldIds))
		));
		
		/*
		* Dynamic field groupings for separate, optimized queries 
		*/
		$grouped = array();
		foreach($fields as &$field) {
			$grouped[$field['item_type']][] = $field['fields_id'];
			
		}
		
		/*
		* Build out SQL statements to resolve permissions
		*/		
		$sql = array();
		foreach($grouped as $item_type => $ids) {
			$sql[] = sprintf(
				"SELECT
				      m.fields_id item_id
				      
			     ,CASE
			     
			      	WHEN mp.`delete` IS NOT NULL
			      	THEN mp.`delete`
			      	
			      	WHEN m.creators_id = amp.users_id AND amp.delete_own IS NOT NULL
			      	THEN amp.delete_own
			      	
			      	WHEN amp.`delete` IS NOT NULL
			      	THEN amp.`delete`
			      	
			      	WHEN m.creators_id = %s
			      	THEN 1
			      	  	
			      	ELSE
			      	0
			     
			      END allow_delete
			     
			     ,CASE
			
			      	WHEN mp.edit IS NOT NULL
			      	THEN mp.edit
			      	
			      	WHEN m.creators_id = amp.users_id AND amp.edit_own IS NOT NULL
			      	THEN amp.edit_own
			      	
			      	WHEN amp.edit IS NOT NULL
			      	THEN amp.edit
			      	
			      	WHEN m.creators_id = %1\$s
			      	THEN 1
			      	  	
			      	ELSE
			      	0     
			     
			      END allow_edit
			      
			     ,CASE
			
			      	WHEN mp.read IS NOT NULL
			      	THEN mp.read
			      	
			      	WHEN m.creators_id = amp.users_id AND amp.read_own IS NOT NULL
			      	THEN amp.read_own
			      	
			      	WHEN amp.`read` IS NOT NULL
			      	THEN amp.`read`
			      	
			      	WHEN m.creators_id = %1\$s
			      	THEN 1
			      	  	
			      	ELSE
			      	1    
			     
			      END allow_read
			      
			     ,CASE
			     
			      	WHEN mp.`delete` IS NOT NULL
			      	THEN 'You are not allowed to delete the specified field'
			      	
			      	WHEN m.creators_id = amp.users_id AND amp.delete_own IS NOT NULL
			      	THEN 'You are not allowed to delete the specified field'
			      	
			      	WHEN amp.`delete` IS NOT NULL
			      	THEN 'You are not allowed to delete the specified field'
			      	
			      	WHEN m.creators_id = %1\$s
			      	THEN 'You are not allowed to delete the specified field'
			      	  	
			      	ELSE
			      	'You are not allowed to delete the specified field'
			     
			      END deny_delete_msg_dev
			      
			     ,CASE
			
			      	WHEN mp.edit IS NOT NULL
			      	THEN 'You are not allowed to edit specified field'
			      	
			      	WHEN m.creators_id = amp.users_id AND amp.edit_own IS NOT NULL
			      	THEN 'You are not allowed to edit specified field'
			      	
			      	WHEN amp.edit IS NOT NULL
			      	THEN 'You are not allowed to edit specified field'
			      	
			      	WHEN m.creators_id = %1\$s
			      	THEN 'You are not allowed to edit specified field'
			      	  	
			      	ELSE
			      	'You are not allowed to edit specified field'    
			     
			      END deny_edit_msg_dev
			      
			     ,CASE
			
			      	WHEN mp.read IS NOT NULL
			      	THEN 'You are not allowed to see specified field'
			      	
			      	WHEN m.creators_id = amp.users_id AND amp.read_own IS NOT NULL
			      	THEN 'You are not allowed to see specified field'
			      	
			      	WHEN amp.`read` IS NOT NULL
			      	THEN 'You are not allowed to see specified field'
			      	
			      	WHEN m.creators_id = %1\$s
			      	THEN 'You are not allowed to see specified field'
			      	  	
			      	ELSE
			      	'You are not allowed to see specified field'   
			     
			      END deny_read_msg_dev
				      
				   FROM
				      MCP_FIELDS m
				   LEFT OUTER
				   JOIN
				      MCP_PERMISSIONS_USERS mp
				     ON
				      mp.item_type = '%s'
				    AND
				      m.fields_id = mp.item_id
				    AND
				      mp.users_id = %1\$s
				   LEFT OUTER
				   JOIN
				      MCP_PERMISSIONS_USERS amp
				     ON 
				      amp.item_type = '%2\$sS'
				    AND
				      m.entities_id = amp.item_id
				    AND
				      amp.users_id = %1\$s
				  WHERE
				      m.fields_id IN (%s)"
				     
				// user that permissions are being checked for
				,$this->_objMCP->escapeString($usersId === null?0:$usersId)
				
				// entity type shared between fields for this query
				,$this->_objMCP->escapeString($item_type)
				
				// fields sharing entity type such as; node type, etc 
				,$this->_objMCP->escapeString(implode(',',$ids))
			);
		}
		
		/*
		* Flatten groups into a single dimensional array
		*/
		$data = array();
		foreach($sql as $query) {
			foreach($this->_objMCP->query($query) as $row) {
				$data[] = $row;
			}
		}
		
		return $data;
		
	}
	
	/*
	* Permissions to create a new field
	* 
	* Alittle different than other _c() implementations. This one
	* expects a an arrray of strings, rather than integer ids. Strings
	* should take the format entity_type-entities_id or strictly entities type without
	* a - separator. Ex. MCP_NODE_TYPES-567 or MCP_VOCABULARY-576. Both mean two
	* very different things. One means can a uset create a field for node type 567
	* and the other can a user create a field for vocabulary 576. This is very important,
	* as the only way to differentiate the entity fields are being added to is the string
	* name, before the -{id}.
	* 
	* @param array ids (strings with entity_type-entities_id)
	* @param int users id
	* @return permission data
	*/
	private function _c($ids,$userId) {
		
		/*
		* Group by entity type 
		*/
		$grouped = array();
		foreach($ids as $id) {
			
			$entity_type = $id;
			$entities_id = 0;
			
			if(strpos($id,'-') !== false) {
				list($entity_type,$entities_id) = explode('-',$id,2);
			}
			
			$grouped[$entity_type][] = $entities_id;
			
		}
		
		/*
		* Build out sql for each entity group 
		* 
		* This works a little different than others. The most specific case is that a permission
		* exist that is specific to adding fields to the entity. The next case is based on whether
		* the user is allowed to edit the item_type defined by the field. The nest case is whether the
		* user created the item type defined by the field. The last case is whether the user has global
		* level permission to edit the entity type (defined by item_id = 0 ).
		* 
		*/
		$sql = array();
		foreach($grouped as $item_type=>$item_ids) {
			$sql[] =
				"SELECT
				     CASE
				     
				     	WHEN f.entities_id = 0
				     	THEN '{$this->_objMCP->escapeString($item_type)}'
				     	
				     	ELSE
				     	CONCAT('{$this->_objMCP->escapeString($item_type)}','-',f.entities_id)
				     
				     END item_id
				     
					,CASE
							     	
						WHEN fp.add IS NOT NULL
						THEN fp.add
						
						WHEN ntp.edit IS NOT NULL
						THEN ntp.edit
						
						WHEN f.creators_id = {$this->_objMCP->escapeString($userId === null?0:$userId)}
						THEN 1
						
						WHEN gtp.edit IS NOT NULL
						THEN gtp.edit
							     	
						ELSE
						0
							     	
					END allow_add
							      
					,'You are not allowed to create a field for entity' deny_add_msg_dev
				     
				  FROM
				  
				     MCP_FIELDS f
				     
				  LEFT OUTER
				  JOIN
				  
				     MCP_PERMISSIONS_USERS fp
				    ON
				     CONCAT(f.entity_type,'-FIELDS') = fp.item_type
				   AND
				     COALESCE(f.entities_id,0) = fp.item_id
				   AND
				     fp.users_id = {$this->_objMCP->escapeString($userId === null?0:$userId)}
				        
				  LEFT OUTER
				  JOIN
				     MCP_PERMISSIONS_USERS ntp
				    ON
				     f.entity_type = ntp.item_type
				   AND
				     COALESCE(f.entities_id,0) = ntp.item_id
				   AND
				     ntp.users_id = {$this->_objMCP->escapeString($userId === null?0:$userId)}
				     
				     
				  LEFT OUTER
				  JOIN
				     MCP_PERMISSIONS_USERS gtp
				    ON
				     f.entity_type = gtp.item_type
				   AND
				     gtp.item_id = 0
				   AND
				     gtp.users_id = {$this->_objMCP->escapeString($userId === null?0:$userId)}
				     
				 WHERE
				     f.entity_type = '{$this->_objMCP->escapeString($item_type)}'
				   AND
				     f.entities_id IN ({$this->_objMCP->escapeString(implode(',',$item_ids))})";
		}
		
		/*
		* Flatten data 
		*/
		$data = array();
		foreach($sql as $query) {
			foreach($this->_objMCP->query($query) as $row) {
				$data[] = $row;
			}
		}
		
		return $data;
		
	}
	
}
?>