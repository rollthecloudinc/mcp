<?php
$this->import('App.Core.DAO');
$this->import('App.Core.Permission');

/*
* MCP_CONFIG 0 (all site configs)
* MCP_CONFIG 1 (full site config) - id represents the site - 0 represents all sites
* MCP_CONFIG:name_of_field - 1
* MCP_CONFIG:name_of_field - 0 
*/
class MCPPermissionConfig extends MCPDAO implements MCPPermission {
	
	public function edit($ids,$intUserId=null) {
		
        }
	
	public function read($ids,$intUserId=null) {
		
	}
        
        /*
        * It is possible to add configuation fields using fields
        * this is not something that is supported by the GUI. Though
        * is is completely supported entering the data manually into the db. 
        */
	public function add($ids,$intUserId=null) {
		
	}
        
        /*
        * It will not be possible to delete configuaration fields at this time. In
        * Theory it could be supported in that one would be able to delete fields
        * that have been added to configuration as fields. Though for now I am going
        * to leave it be.  
        */
	public function delete($ids,$intUserId=null) {
		
	}
        
	
}

?>
