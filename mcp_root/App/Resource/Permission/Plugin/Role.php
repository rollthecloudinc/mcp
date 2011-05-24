<?php
// abstract base class
$this->import('App.Resource.Permission.TopLevelPermission');

/*
* Role permissions data access layer
*/
class MCPPermissionRole extends MCPTopLevelPermission {
	
	protected function _getBaseTable() {
		return 'MCP_ROLES';
	}
	
	protected function _getPrimaryKey() {
		return 'roles_id';
	}
	
	protected function _getCreator() {
		return 'creators_id';
	}
	
	protected function _getItemType() {
		return 'MCP_ROLES';
	}
	
} 
?>