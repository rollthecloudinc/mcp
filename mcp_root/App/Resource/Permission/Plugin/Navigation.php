<?php
$this->import('App.Core.DAO');
$this->import('App.Core.Permission');

/*
* Navigation permission
*/
class MCPPermissionNavigation extends MCPDAO implements MCPPermission {
	
	/*
	* Determine whether user may ad a menu 
	*/
	public function add($ids) {
		
		$permission = $this->_c($this->_objMCP->getUsersId());
		
		$return = array();
		
		if($permission !== null) {
			$return[] = array(
				'allow'=>(bool) $permission['allow_add']
				,'msg_dev'=>$permission['deny_add_msg_dev']
				,'msg_user'=>'You may not add navigation menu.'
			);
		} else {
			$return[] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to create a navigation menu'
				,'msg_user'=>'You may not add navigation menu.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user may read menu 
	*/
	public function read($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_read']
				,'msg_dev'=>$permission['deny_read_msg_dev']
				,'msg_user'=>'You may not see navigation menu.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to see specified navigation menu'
				,'msg_user'=>'You may not see navigation menu.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determoine whether user may delete menu 
	*/
	public function delete($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_delete']
				,'msg_dev'=>$permission['deny_delete_msg_dev']
				,'msg_user'=>'You may not delete navigation menu.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to delete specified navigation menu'
				,'msg_user'=>'You may not delete navigation menu.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Dtermine whether user may edit a menu 
	*/
	public function edit($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_edit']
				,'msg_dev'=>$permission['deny_edit_msg_dev']
				,'msg_user'=>'You may not edit navigation menu.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to edit specified navigation menu'
				,'msg_user'=>'You may not edit navigation menu.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user is allowed to edit, read or delete specified menu(s)
	*
	* @param array menu ids
	* @param int users id (when unspecified defaults to current user)
	* @return array permission set
	*/
	private function _rud($arrMenuIds,$intUser=null) {
		
		$strSQL = sprintf(
			"SELECT
			     m.navigation_id item_id
			     ,CASE
			     
			      	WHEN mp.`delete` IS NOT NULL
			      	THEN mp.`delete`
			      	
			      	WHEN m.users_id = amp.users_id AND amp.delete_own IS NOT NULL
			      	THEN amp.delete_own
			      	
			      	WHEN amp.`delete` IS NOT NULL
			      	THEN amp.`delete`
			      	
			      	WHEN m.users_id = %s
			      	THEN 1
			      	  	
			      	ELSE
			      	0
			     
			      END allow_delete
			     
			     ,CASE
			
			      	WHEN mp.edit IS NOT NULL
			      	THEN mp.edit
			      	
			      	WHEN m.users_id = amp.users_id AND amp.edit_own IS NOT NULL
			      	THEN amp.edit_own
			      	
			      	WHEN amp.edit IS NOT NULL
			      	THEN amp.edit
			      	
			      	WHEN m.users_id = %1\$s
			      	THEN 1
			      	  	
			      	ELSE
			      	0     
			     
			      END allow_edit
			      
			     ,CASE
			
			      	WHEN mp.read IS NOT NULL
			      	THEN mp.read
			      	
			      	WHEN m.users_id = amp.users_id AND amp.read_own IS NOT NULL
			      	THEN amp.read_own
			      	
			      	WHEN amp.`read` IS NOT NULL
			      	THEN amp.`read`
			      	
			      	WHEN m.users_id = %1\$s
			      	THEN 1
			      	  	
			      	ELSE
			      	1    
			     
			      END allow_read
			      
			     ,CASE
			     
			      	WHEN mp.`delete` IS NOT NULL
			      	THEN 'You are not allowed to delete the specified navigation menu'
			      	
			      	WHEN m.users_id = amp.users_id AND amp.delete_own IS NOT NULL
			      	THEN 'You are not allowed to delete the specified navigation menu'
			      	
			      	WHEN amp.`delete` IS NOT NULL
			      	THEN 'You are not allowed to delete the specified navigation menu'
			      	
			      	WHEN m.users_id = %1\$s
			      	THEN 'You are not allowed to delete the specified navigation menu'
			      	  	
			      	ELSE
			      	'You are not allowed to delete the specified navigation menu'
			     
			      END deny_delete_msg_dev
			      
			     ,CASE
			
			      	WHEN mp.edit IS NOT NULL
			      	THEN 'You are not allowed to edit specified navigation menu'
			      	
			      	WHEN m.users_id = amp.users_id AND amp.edit_own IS NOT NULL
			      	THEN 'You are not allowed to edit specified navigation menu'
			      	
			      	WHEN amp.edit IS NOT NULL
			      	THEN 'You are not allowed to edit specified navigation menu'
			      	
			      	WHEN m.users_id = %1\$s
			      	THEN 'You are not allowed to edit specified navigation menu'
			      	  	
			      	ELSE
			      	'You are not allowed to edit specified navigation menu'    
			     
			      END deny_edit_msg_dev
			      
			     ,CASE
			
			      	WHEN mp.read IS NOT NULL
			      	THEN 'You are not allowed to see specified navigation menu'
			      	
			      	WHEN m.users_id = amp.users_id AND amp.read_own IS NOT NULL
			      	THEN 'You are not allowed to see specified navigation menu'
			      	
			      	WHEN amp.`read` IS NOT NULL
			      	THEN 'You are not allowed to see specified navigation menu'
			      	
			      	WHEN m.users_id = %1\$s
			      	THEN 'You are not allowed to see specified navigation menu'
			      	  	
			      	ELSE
			      	'You are not allowed to see specified navigation menu'   
			     
			      END deny_read_msg_dev
			      
			  FROM
			     MCP_NAVIGATION m
			  LEFT OUTER
			  JOIN
			     MCP_PERMISSIONS_USERS mp
			    ON
			     m.navigation_id = mp.item_id
			   AND
			     mp.users_id = %1\$s
			   AND
			     mp.item_type = 'MCP_NAVIGATION'
			  LEFT OUTER
			  JOIN
			     MCP_PERMISSIONS_USERS amp
			    ON
			     amp.item_id = 0
			   AND
			     amp.users_id = %1\$s
			   AND
			     amp.item_type = 'MCP_NAVIGATION'
			 WHERE
			     m.navigation_id IN (%s)"
			,$this->_objMCP->escapeString($intUser === null?0:$intUser)
			,$this->_objMCP->escapeString(implode(',',$arrMenuIds))
		);
		
		$arrPerms = $this->_objMCP->query($strSQL);
		
		return $arrPerms;
     
	}

	/*
	* Determine whether user is allowed to create a new menu
	*
	* @param int users id (when unspecified defaults to current user)
	* @return array permission set
	*/
	private function _c($intUser=null) {
		
		$strSQL = sprintf(
			"SELECT
			     amp.item_id
			     ,CASE
			     	
			     	WHEN amp.add IS NOT NULL
			     	THEN amp.add
			     	
			     	ELSE
			     	0
			     	
			      END allow_add
			      
			      ,'You are not allowed to create a navigation menu' deny_add_msg_dev
			  FROM
			     MCP_PERMISSIONS_USERS amp
			 WHERE
			     amp.item_id = 0
			   AND
			     amp.users_id = %s
			   AND
			     amp.item_type = 'MCP_NAVIGATION'"
			,$this->_objMCP->escapeString($intUser === null?0:$intUser)
		);
		
		$arrPerms = $this->_objMCP->query($strSQL);
		
		return array_pop($arrPerms);
     
	}
	
}     
?>