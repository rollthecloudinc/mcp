<?php 
$this->import('App.Core.DAO');
$this->import('App.Core.Permission');

/*
* Allow and deny access to pages based on specific routes such as; 
* 
* - Component/*
* - PlatForm/*
* - Admin/*
* 
* This permission only implements read all others deny.
*/
class MCPPermissionRoute extends MCPDAO implements MCPPermission {

	/*
	* Determine whether user is able to access given routes (pages) associated
	* with routes.
	* 
	* @param array collection of strings representing routes
	* @return array permissions
	*/
	public function read($ids,$intUserId=null) {
		
		$return = array();
		
		foreach($ids as $id) {

			$strSQL =
				"SELECT
				       u.users_id item_id
				      ,pu.read allow_read
				   FROM
				      MCP_USERS u
				  INNER
				   JOIN
				      MCP_PERMISSIONS_USERS pu
				     ON
				      u.users_id = pu.users_id
				    AND
				      pu.item_id = 0
				    AND
				      pu.item_type = :item_type
				  WHERE
				      u.users_id = :users_id";
			
			$perm = array_pop( $this->_objMCP->query($strSQL,array(
				 ':item_type'=>"MCP_ROUTE:$id"
				,':users_id'=>$intUserId?$intUserId:0
			)));
			
			$return[$id] = array(
				'allow'=>(bool) $perm?$perm['allow_read']:false
				,'msg_dev'=>"You are not allowed to access the route {$id}"
				,'msg_user'=>'You are not allowed to access this page.'
			);
			
		}
		
		return $return;
	}
	
	public function add($ids,$intUserId=null) {
		return $this->_deny($ids);
	}	
	
	public function delete($ids,$intUserId=null) {
		return $this->_deny($ids);
	}
	
	public function edit($ids,$intUserId=null) {
		return $this->_deny($ids);
	}
	
	/*
	* Deny request
	* 
	* @param array ids
	* @return array permissions
	*/
	private function _deny($ids) {
		
		$return = array();	
		foreach($ids as $id) $return[$id] = array(
			'allow'=>false
			,'msg_dev'=>'The route permission does not implement edit, delete or add.'
			,'msg_user'=>'Permission Denied'				
		);
		
		return $return;
		
	}
	
}
?>