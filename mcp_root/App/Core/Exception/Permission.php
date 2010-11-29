<?php 
/*
* Permission exception 
*/
class MCPPermissionException extends Exception {
	
	public function __construct($permission) {
		parent::__construct($permission['msg_user']);
	}
	
}
?>