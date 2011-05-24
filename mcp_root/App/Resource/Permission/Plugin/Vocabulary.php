<?php
// abstract base class
$this->import('App.Resource.Permission.TopLevelPermission');

/*
* Vocabulary permission
*/
class MCPPermissionVocabulary extends MCPTopLevelPermission {
	
	protected function _getBaseTable() {
		return 'MCP_VOCABULARY';
	}
	
	protected function _getPrimaryKey() {
		return 'vocabulary_id';
	}
	
	protected function _getCreator() {
		return 'creators_id';
	}
	
	protected function _getItemType() {
		return 'MCP_VOCABULARY';
	}
	
}     
?>