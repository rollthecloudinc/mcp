<?php 
/*
* Build complete menu for font-end display
* 
*  Accepts either a menu ID or name of menu
*  @todo: location support
*/
class MCPMenuMenu extends MCPModule {
	
	protected
	
	/*
	* Menu Data access object 
	*/
	$_objDAOMenu;
	
	public function __construct(MCP $objMCP,MCPModule $objParent=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParent,$arrConfg);
		$this->_init();
	}
	
	protected function _init() {
		
		// Get menu data access object
		$this->_objDAOMenu = $this->_objMCP->getInstance('Component.Menu.DAO.DAOMenu',array($this->_objMCP));
		
	}
	
	public function execute($arrArgs) {
		
		$mcp = $this->_objMCP;
		
		$mixMenuArg = !empty($arrArgs)?array_shift($arrArgs):null;
		
		// Get menu based on name or ID
		$arrMenu = null;
		if(is_numeric($mixMenuArg)) {
			$arrMenu = $this->_objDAOMenu->fetchMenuById($mixMenuArg);
		} else {
			$arrMenu = $this->_objDAOMenu->fetchSiteMenuByName($mixMenuArg,$this->_objMCP->getSitesId());
		}
		
		// Get menu links
		if($arrMenu !== null) {
			$this->_arrTemplateData['links'] = $this->_objDAOMenu->fetchMenu($arrMenu['menus_id'],array('dynamic_links'=>true));
		} else {
			$this->_arrTemplateData['links'] = array();
		}
		
		// Create mutation to turn menu items into links
		$this->_arrTemplateData['mutation'] = function($strTitle,$arrLink) use ($mcp) {
			
			return $mcp->ui('Common.Field.Link',array(
				 'url'=>$arrLink['url']
				,'label'=>$strTitle
			));
		};
		
		return 'Menu/Menu.php';
		
	}
	
}
?>