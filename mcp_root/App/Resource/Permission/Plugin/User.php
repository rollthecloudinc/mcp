<?php
// abstract base class
$this->import('App.Resource.Permission.PermissionBase');

/*
* User permission data access layer 
*/
class MCPPermissionUser extends MCPPermissionBase {
	
	/*
	* Determine whether user is allowed to create a new user
	*/
	public function add($ids) {
		
		$permissions = $this->_c($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_add']
				,'msg_dev'=>$permission['deny_add_msg_dev']
				,'msg_user'=>'You are not allowed to register a user.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to register a user'
				,'msg_user'=>'You are not allowed to register a user.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user is allowed to see user(s)
	*/
	public function read($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_read']
				,'msg_dev'=>$permission['deny_read_msg_dev']
				,'msg_user'=>'You may not view member info.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to view member info'
				,'msg_user'=>'You may not view member info.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user is allowed to delete user(s)
	*/
	public function delete($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_delete']
				,'msg_dev'=>$permission['deny_delete_msg_dev']
				,'msg_user'=>'You may not delete member.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to delete specified member'
				,'msg_user'=>'You may not delete member.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user is allowed to edit user(s) 
	*/
	public function edit($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_edit']
				,'msg_dev'=>$permission['deny_edit_msg_dev']
				,'msg_user'=>'You may not edit user info.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to edit specified user info'
				,'msg_user'=>'You may not edit user info.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user is allowed to edit, read or delete specified user(s)
	*
	* @param array user ids
	* @param int users id (when unspecified defaults to current user)
	* @return array permission set
	*/
	private function _rud($arrUserIds,$intUser=null) {
		
		$strSQL = sprintf(
			"SELECT
			     m.users_id item_id
			     ,CASE
			     
			      	WHEN mp.`delete` IS NOT NULL
			      	THEN mp.`delete`
			      	
			      	WHEN m.users_id = amp.users_id AND amp.delete_own IS NOT NULL
			      	THEN amp.delete_own
			      	
			      	WHEN amp.`delete` IS NOT NULL
			      	THEN amp.`delete`
			      	  	
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
			      	
			      	WHEN m.users_id = %s
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
			      	THEN 'You are not allowed to delete the specified member'
			      	
			      	WHEN m.users_id = amp.users_id AND amp.delete_own IS NOT NULL
			      	THEN 'You are not allowed to delete the specified member'
			      	
			      	WHEN amp.`delete` IS NOT NULL
			      	THEN 'You are not allowed to delete the specified member'
			      	  	
			      	ELSE
			      	'You are not allowed to delete the specified member'
			     
			      END deny_delete_msg_dev
			      
			     ,CASE
			
			      	WHEN mp.edit IS NOT NULL
			      	THEN 'You are not allowed to edit specified member info'
			      	
			      	WHEN m.users_id = amp.users_id AND amp.edit_own IS NOT NULL
			      	THEN 'You are not allowed to edit specified member info'
			      	
			      	WHEN amp.edit IS NOT NULL
			      	THEN 'You are not allowed to edit specified member info'
			      	
			      	WHEN m.users_id = %1\$s
			      	THEN 'You are not allowed to edit specified member info'
			      	  	
			      	ELSE
			      	'You are not allowed to edit specified member info'    
			     
			      END deny_edit_msg_dev
			      
			     ,CASE
			
			      	WHEN mp.read IS NOT NULL
			      	THEN 'You are not allowed to see specified member info'
			      	
			      	WHEN m.users_id = amp.users_id AND amp.read_own IS NOT NULL
			      	THEN 'You are not allowed to see specified member info'
			      	
			      	WHEN amp.`read` IS NOT NULL
			      	THEN 'You are not allowed to see specified member info'
			      	
			      	WHEN m.users_id = %1\$s
			      	THEN 'You are not allowed to see specified member info'
			      	  	
			      	ELSE
			      	'You are not allowed to see specified member info'   
			     
			      END deny_read_msg_dev
			      
			  FROM
			     MCP_USERS m
			  LEFT OUTER
			  JOIN
			     MCP_PERMISSIONS_USERS mp
			    ON
			     m.users_id = mp.item_id
			   AND
			     mp.users_id = %1\$s
			   AND
			     mp.item_type = 'MCP_USERS'
			  LEFT OUTER
			  JOIN
			     MCP_PERMISSIONS_USERS amp
			    ON
			     amp.item_id = 0
			   AND
			     amp.users_id = %1\$s
			   AND
			     amp.item_type = 'MCP_USERS'
			 WHERE
			     m.users_id IN (%s)"
			,$this->_objMCP->escapeString($intUser === null?0:$intUser)
			,$this->_objMCP->escapeString(implode(',',$arrUserIds))
		);
		
		$arrPerms = $this->_objMCP->query($strSQL);
		
		return $arrPerms;
		
	}
	
	/*
	* Determine whether user is allowed to create a new user
	*
	* @param in site ids
	* @param int users id (when unspecified defaults to current user)
	* @return array permission set
	*/
	private function _c($arrSiteIds,$intUser=null) {
		
		/*$strSQL = sprintf(
			"SELECT
			     amp.item_id
			     ,CASE
			     	
			     	WHEN amp.add IS NOT NULL
			     	THEN amp.add
			     	
			     	ELSE
			     	0
			     	
			      END allow_add
			      
			      ,'You are not allowed to create a user' deny_add_msg_dev
			  FROM
			     MCP_PERMISSIONS_USERS amp
			 WHERE
			     amp.item_id = 0
			   AND
			     amp.users_id = %s
			   AND
			     amp.item_type = 'MCP_USERS'"
			,$this->_objMCP->escapeString($intUser === null?0:$intUser)
		);*/
		
		$arrPerms = array(); // $this->_objMCP->query($strSQL);
		
		/*
		* @TODO: implement for logged in users and restrict banned ips 
		*/
		
		/*
		* If user is not logged in and ip isn't banned they may register / create
		* a new user. 
		*/
		if( /*empty($intUser)*/ true ) {
			foreach($arrSiteIds as $id) {
				$arrPerms[] = array(
					'item_id'=>$id
					,'allow_add'=>1
					,'deny_add_msg'=>'You are not allowed to create a user'
				);
			}
		}
		
		return $arrPerms;
     
	}
	
}
?>