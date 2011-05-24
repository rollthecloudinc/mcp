<?php 
class MCPMenuTest extends MCPModule {
	
	public function execute($arrArgs) {
		
		$objDAOMenu = $this->_objMCP->getInstance('Component.Menu.DAO.DAOMenu',array($this->_objMCP));
		
		echo '<pre>',var_dump($objDAOMenu->fetchMenuImproved(5)),'</pre>';
		
		return 'Test/Test.php';
		
	}
	
}
?>