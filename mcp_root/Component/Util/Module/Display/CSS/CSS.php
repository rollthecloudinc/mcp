<?php 
/*
* Imports CSS files 
*/
class MCPUtilDisplayCSS extends MCPModule {
	
	public function execute($arrArgs) {
		
		/*
		* Sites CSS that will placed in head of master template 
		*/
		return $this->_objMCP->getConfigValue('site_css_template');
		
	}
	
}
?>