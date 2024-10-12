<?php
class MCPUserLogout extends MCPModule {

	public function execute($arrArgs) {	

		$arrFrmPost = $this->_objMCP->getPost();
		if(isset($arrFrmPost['frmUtilLogout'])) $this->_objMCP->logoutUser();
		
		$this->_arrTemplateData['frm_action'] = $this->_objMCP->getBasePath();
		$this->_arrTemplateData['frm_name'] = 'frmUtilLogout';
		$this->_arrTemplateData['frm_method'] = 'POST';
		
		return 'Logout/Logout.php';
	}

}
?>