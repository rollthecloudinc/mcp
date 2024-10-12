<?php 
/*
* Displays breadcrumb trail 
*/
class MCPUtilBreadcrumb extends MCPModule {
	
	public function execute($arrArgs) {
		
		//$this->_arrTemplateData['breadcrumbs'] = $this->_objParentModule->getBreadcrumbData();
                $this->_arrTemplateData['breadcrumbs'] = array_reverse($this->_objMCP->getBreadcrumbs());
		
		return 'Breadcrumb/Breadcrumb.php';
		
	}
	
}
?>