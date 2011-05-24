<?php
// abstract base class
$this->import('App.Resource.Permission.TopLevelPermission');

/*
* Navigation permission
*/
class MCPPermissionNavigation extends MCPTopLevelPermission {
	
	protected function _getBaseTable() {
		return 'MCP_NAVIGATION';
	}
	
	protected function _getPrimaryKey() {
		return 'navigation_id';
	}
	
	protected function _getCreator() {
		return 'users_id';
	}
	
	protected function _getItemType() {
		return 'MCP_NAVIGATION';
	}
	
}     
?>