<?php
// abstract base class
$this->import('App.Resource.Permission.TopLevelPermission');

/*
* Site permission data access layer 
*/
class MCPPermissionSite extends MCPTopLevelPermission {
	
	protected function _getBaseTable() {
		return 'MCP_SITES';
	}
	
	protected function _getPrimaryKey() {
		return 'sites_id';
	}
	
	protected function _getCreator() {
		return 'creators_id';
	}
	
	protected function _getItemType() {
		return 'MCP_SITES';
	}
	
}
?>
