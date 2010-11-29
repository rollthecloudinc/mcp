<?php 
/*
* Displays breadcrumb trail 
*/
class MCPUtilBreadcrumb extends MCPModule {
	
	public function execute($arrArgs) {
		
		$this->_arrTemplateData['breadcrumbs'] = $this->_objParentModule->getBreadcrumbData();
		
		return 'Breadcrumb/Breadcrumb.php';
		
	}
	
}
?>