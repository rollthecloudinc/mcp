<?php
$this->import('App.Core.Permission');

/*
*  Manage low-level permissions
* Consistent interface to interact with permission plugins 
*/
class MCPPermissionManager extends MCPResource {

	protected
	
	/*
	* Plugins that have been loaded cache
	*/
	$_arrLoadedPlugins = array();

	public function __construct(MCP $objMCP) {
		parent::__construct($objMCP);
	}

	/*
	* Check permissions for given action
	*
	* @param str permission such as; read, delete, edit or add
	* @param str entity such as; navigation, navigation link, etc
	* @param mix entity id such as; id of nav to delete or id or vocab to add term
	* @param obj permission object
	*/
	public function getPermission($strAction,$strEntity,$arrId=null) {
	
		$permissions = array();
	
		/*
		* Get requested permissions
		*/
		switch($strAction) {
			case MCPPermission::READ:
				$permissions = $this->_getPlugin($strEntity)->read($arrId);
				break;
			
			case MCPPermission::DELETE:
				$permissions = $this->_getPlugin($strEntity)->delete($arrId);
				break;
			
			case MCPPermission::EDIT:
				$permissions = $this->_getPlugin($strEntity)->edit($arrId);
				break;
			
			case MCPPermission::ADD:
				$permissions = $this->_getPlugin($strEntity)->add($arrId);
				break;
				
			default:
				
		}
		
		return $permissions;
	
	}

	/*
	* Get a plugin
	*
	* @param str plugin name
	* @return obj permission plugin instance
	*/
	protected function _getPlugin($strName) {
		
		if(isset($this->_arrLoadedPlugins[$strName])) {
			return $this->_arrLoadedPlugins[$strName];
		}
		
		/*
		* Import class file 
		*/
		$this->_objMCP->import("App.Resource.Permission.Plugin.$strName");
		
		/*
		* Instantiate plugin
		*/
		$className = "MCPPermission$strName";
		$this->_arrLoadedPlugins[$strName] = new $className($this->_objMCP);
		
		return $this->_arrLoadedPlugins[$strName];
		
	}

}
?>