<?php
/*
* Manages site configuration settings
*/
$this->import('App.Core.Resource');
class MCPConfig extends MCPResource {
	
	private static
	
	/*
	* Config singleton 
	*/
	$_objConfig;
	
	private
	
	/*
	* Config DAO 
	*/
	$_objDAOConfig;
	
	public function __construct(MCP $objMCP) {
		parent::__construct($objMCP);
		$this->_init();
	}
	
	public static function createInstance(MCP $objMCP) {
		if(self::$_objConfig === null) {
			self::$_objConfig = new MCPConfig($objMCP);
		}
		return self::$_objConfig;
	}
	
	private function _init() {
		// Get config DAO
		$this->_objDAOConfig = $this->_objMCP->getInstance('Component.Config.DAO.DAOConfig',array($this->_objMCP));
	}
	
	/*
	* Get sites config value
	* 
	* @param str config value name
	* @return str value
	*/
	public function getConfigValue($strName) {
		return $this->_objDAOConfig->fetchConfigValueByName($strName);
	}
	
	/*
	* Set config value for site
	* 
	* @param str config value name
	* @param str config value
	*/
	public function setConfigValue($strName,$strValue) {
		return $this->_objDAOConfig->setConfigValueByName($strName,$strValue);
	}
	
	/*
	* Save multiple config values at once
	* 
	* @param array config (key value pair)
	*/
	public function saveMultiConfig($arrConfig) {
		return $this->_objDAOConfig->saveMultiConfig($arrConfig);
	}
	
	/*
	* Get all config key value pairs
	* 
	* @retrun array config
	*/
	public function getEntireConfig() {
		return $this->_objDAOConfig->fetchEntireConfig();
	}
	
	/*
	* Get the form definition for sites global configuration data
	* 
	* @return array schema
	*/
	public function getConfigSchema() {
		return $this->_objDAOConfig->fetchConfigSchema();
	}
	
	/*
	* Reload config after an update perhaps in the same request 
	*/
	public function reload() {
		self::$_objConfig = new MCPConfig($this->_objMCP);
	}
	
}
?>
