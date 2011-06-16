<?php
$this->import('App.Resource.Permission.ChildLevelPermission');

/*
* Menu link permissions data access layer
*/
class MCPPermissionMenuLink extends MCPChildLevelPermission {
	
	protected function _getBaseTable() {
		return 'MCP_MENU_LINKS';
	}
	
	protected function _getParentTable() {
		return 'MCP_MENUS';
	}
	
	protected function _getPrimaryKey() {
		return 'menu_links_id';
	}
	
	protected function _getParentPrimaryKey() {
		return 'menus_id';
	}
	
	protected function _getItemType() {
		return 'MCP_MENU_LINK';
	}
	
	protected function _getParentItemType() {
		return 'MCP_MENU';
	}
	
	protected function _getCreator() {
		return 'creators_id';
	}
	
	protected function _getParentCreator() {
		return 'users_id';
	}
	
}
?>