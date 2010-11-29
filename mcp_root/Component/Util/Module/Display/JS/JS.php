<?php 
/*
* Imports JS files 
*/
class MCPUtilDisplayJS extends MCPModule {
	
	public function execute($arrArgs) {
		
		/*
		* Sites JavaScript that will placed in head of master template 
		*/
		return $this->_objMCP->getConfigValue('site_js_template');
		
	}
	
}
?>