<?php
// abstract base class
$this->import('App.Resource.Permission.PermissionBase');

/*
* MCP_CONFIG 0 (all site configs)
* MCP_CONFIG 1 (full site config) - id represents the site - 0 represents all sites
* MCP_CONFIG:name_of_field - 1
* MCP_CONFIGLname_of_field - 0 
*/
class MCPPermissionConfig extends MCPPermissionBase {
	
	public function add($ids) {
		
	}
	
	public function edit($ids) {
		
	}
	
	public function delete($ids) {
		
	}
	
	public function read($ids) {
		
	}
	
}

?>
