<?php 
/*
* Create or edit a view (very likely to change, just experimental at this point) 
*/
class MCPVDForm extends MCPModule {
	
	public function __construct(MCP $objMCP,$objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
	}
	
	public function execute($arrArgs) {
		
		// testing algorithm development
		$objDAOVD = $this->_objMCP->getInstance('Component.VD.DAO.DAOVD',array($this->_objMCP));
		//$objDAOVD->fetchViewById();
		$display = $objDAOVD->fetchDisplayById(1);
		
		return 'Form/Form.php';
	}
	
}
?>