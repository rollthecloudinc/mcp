<?php
/*
* manage state of current site
*/
$this->import('App.Core.Resource');
class MCPSite extends MCPResource {

	private static
	
	/*
	* Current site
	*/
	$_objSite;
	
	private
	
	/*
	* Site data access layer 
	*/
	$_objDAOSite
	
	/*
	* Current site data
	*/
	,$_arrSite;

	public function __construct(MCP $objMCP) {
		parent::__construct($objMCP);
		$this->_init();
	}
	
	public static function createInstance(MCP $objMCP) {
		if(self::$_objSite === null) {
			self::$_objSite = new MCPSite($objMCP);
		}
		return self::$_objSite;
	}
	
	private function _init() {
		
		// Get data access layer object for sites
		$this->_objDAOSite = $this->_objMCP->getInstance('Component.Site.DAO.DAOSite',array($this->_objMCP));
		
		// Fetch site data and set program critial data
		$this->_arrSite = $this->_objDAOSite->fetchById($this->_objMCP->getSitesId());
	}
	
	/*
	* Get the sites id
	*
	* @return int sites id
	*/
	public function getSitesId() {
		return $this->_arrSite['sites_id'];
	}
	
	/*
	* Get the current sites directory
	*
	* @return str directory
	*/
	public function getDirectory() {
		return $this->_arrSite['site_directory'];
	}
	
	/*
	* prefix for site modules t partially prevent class naming conflicts
	* 
	* @return str site module prefix
	*/
	public function getModulePrefix() {
		return $this->_arrSite['site_module_prefix'];
	}

}
?>