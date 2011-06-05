<?php 
class MCPMenuTest extends MCPModule {
	
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
		
		// Get menu links
		$this->_arrTemplateData['links'] = $this->_objDAOMenu->fetchMenu(5,array('dynamic_links'=>true));
		
		// Create mutation to turn items into links
		$this->_arrTemplateData['mutation'] = function($strTitle,$arrLink) use ($mcp) {
			
			return $mcp->ui('Common.Field.Link',array(
				 'url'=>$arrLink['url']
				,'label'=>$strTitle
			));
		};
		
		return 'Test/Test.php';
		
	}
	
}
?>