<?php 
/*
* Displays meta data 
*/
class MCPUtilDisplayMeta extends MCPModule {
	
	public function execute($arrArgs) {
		
		/*
		* Sites Meta data that will placed in head of master template 
		*/
		return $this->_objMCP->getConfigValue('site_meta_template');
		
	}
	
}
?>