<?php 
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
	public function read($ids) {
		
		$return = array();
		
		foreach($ids as $id) {

			$strSQL = sprintf(
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
				      pu.item_type = 'MCP_ROUTE:%s'
				  WHERE
				      u.users_id = %s"
				,$this->_objMCP->escapeString( $id )
				,$this->_objMCP->escapeString( $this->_objMCP->getUsersId()?$this->_objMCP->getUsersId():0 )
			);
			
			$perm = array_pop( $this->_objMCP->query($strSQL) );
			
			$return[$id] = array(
				'allow'=>(bool) $perm?$perm['allow_read']:false
				,'msg_dev'=>"You are not allowed to access the route {$id}"
				,'msg_user'=>'You are not allowed to access this page.'
			);
			
		}
		
		return $return;
	}
	
	public function add($ids) {
		return $this->_deny($ids);
	}	
	
	public function delete($ids) {
		return $this->_deny($ids);
	}
	
	public function edit($ids) {
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