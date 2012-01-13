<?php
$this->import('App.Core.DAO');
$this->import('App.Core.Permission');

/*
* MCP_CONFIG 0 (all site configs)
* MCP_CONFIG 1 (full site config) - id represents the site - 0 represents all sites
* MCP_CONFIG:name_of_field - 1
* MCP_CONFIGL:name_of_field - 0 
*/
class MCPPermissionConfig extends MCPDAO implements MCPPermission {
	
	public function add($ids,$intUserId=null) {
		
	}
	
	public function edit($ids,$intUserId=null) {
		
	}
	
	public function delete($ids,$intUserId=null) {
		
	}
	
	public function read($ids,$intUserId=null) {
		
	}
	
}

?>
