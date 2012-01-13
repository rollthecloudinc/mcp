<?php
// abstract base class
$this->import('App.Resource.Permission.TopLevelPermission');

/*
* User permission data access layer 
*/
class MCPPermissionUser extends MCPTopLevelPermission {
	
	protected function _getBaseTable() {
		return 'MCP_USERS';
	}
	
	protected function _getPrimaryKey() {
		return 'users_id';
	}
	
	protected function _getCreator() {
		return 'users_id';
	}
	
	protected function _getItemType() {
		return 'MCP_USERS';
	}
	
	/*
	* Determine whether user is allowed to create a new user
	*/
	public function add($ids,$intUserId=null) {
		
		$permissions = $this->_c($ids,$intUserId);
		
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
	* Determine whether user is allowed to create a new user
	*
	* @param in site ids
	* @param int users id (when unspecified defaults to current user)
	* @return array permission set
	*/
	protected function _c($arrSiteIds,$intUser=null) {
		
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