<?php 
/*
* Index request will resolve here for sites without a index module 
*/
class MCPUtilIndex extends MCPModule {
	
	public function execute($arrArgs) {
		return 'Index/Index.php';
	}
	
}
?>