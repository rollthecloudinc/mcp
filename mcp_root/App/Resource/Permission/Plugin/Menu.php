<?php
// abstract base class
$this->import('App.Resource.Permission.TopLevelPermission');

/*
* menu permission
*/
class MCPPermissionMenu extends MCPTopLevelPermission {
	
	protected function _getBaseTable() {
		return 'MCP_MENUS';
	}
	
	protected function _getPrimaryKey() {
		return 'menus_id';
	}
	
	protected function _getCreator() {
		return 'users_id';
	}
	
	protected function _getItemType() {
		return 'MCP_MENU';
	}
	
}     
?>