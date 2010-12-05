<?php 
/*
* Create or edit a view (very likely to change, just experimental at this point) 
*/
class MCPViewForm extends MCPModule {
	
	public function __construct(MCP $objMCP,$objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
	}
	
	public function execute($arrArgs) {
		return 'Form/Form.php';
	}
	
}
?>