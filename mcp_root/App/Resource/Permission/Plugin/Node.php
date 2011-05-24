<?php
// abstract base class
$this->import('App.Resource.Permission.ChildLevelPermission');

/*
* Node permissions data access layer
*/
class MCPPermissionNode extends MCPChildLevelPermission {
	
	protected function _getBaseTable() {
		return 'MCP_NODES';
	}
	
 	protected function _getParentTable() {
 		return 'MCP_NODE_TYPES';
 	}
	
	protected function _getPrimaryKey() {
		return 'nodes_id';
	}
	
	protected function _getParentPrimaryKey() {
		return 'node_types_id';
	}
	
	protected function _getItemType() {
		return 'MCP_NODES';
	}
	
	protected function _getParentItemType() {
		return 'MCP_NODE_TYPES';
	}
	
	protected function _getCreator() {
		return 'authors_id';
	}
	
	protected function _getParentCreator() {
		return 'creators_id';
	}
	
}     
?>