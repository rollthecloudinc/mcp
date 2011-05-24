<?php
// abstract base class
$this->import('App.Resource.Permission.TopLevelPermission');

/*
* Node type permission data access layer 
*/
class MCPPermissionNodeType extends MCPTopLevelPermission {
	
	protected function _getBaseTable() {
		return 'MCP_NODE_TYPES';
	}
	
	protected function _getPrimaryKey() {
		return 'node_types_id';
	}
	
	protected function _getCreator() {
		return 'creators_id';
	}
	
	protected function _getItemType() {
		return 'MCP_NODE_TYPES';
	}
	
}
?>
