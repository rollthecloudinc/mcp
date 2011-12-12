<?php 
/*
* Display system messages 
*/
class MCPUtilSystemMessageUser extends MCPModule {
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
	}
	
	public function execute($arrArgs) {
		
		// Assign all messages to template variable
		$this->_arrTemplateData['messages'] = $this->_objMCP->getSystemMessages();
		
		return 'User/User.php';
	}
	
}
?>