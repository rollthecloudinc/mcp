<?php 
/*
* Display debug messages 
*/
class MCPUtilSystemMessageDebug extends MCPModule {
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
	}
	
	public function execute($arrArgs) {
		
		// Assign all messages to template variable
		$this->_arrTemplateData['messages'] = $this->_objMCP->getDebugMessages();
		
		return 'Debug/Debug.php';
	}
	
}
?>