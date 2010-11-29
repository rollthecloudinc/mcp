<?php 
/*
* Master email template 
*/
class MCPUtilMasterEmail extends MCPModule {
	
	public function execute($arrArgs) {
		
		$strEmailType = !empty($arrArgs) && in_array($arrArgs[0],array('HTML','PlainText'))?array_shift($arrArgs):'HTML';
		
		switch($strEmailType) {
			
			case 'PlainText':
				$this->_arrTemplateData['MESSAGE_CONTENT'] = $this->_objParentModule->getPlainTextEmailContent();
				return '/'.$this->_objMCP->getEmailPlainTextMasterTemplate();
				
			case 'HTML':
			default:
				$this->_arrTemplateData['MESSAGE_CONTENT'] = $this->_objParentModule->getHTMLEmailContent();
				return '/'.$this->_objMCP->getEmailHTMLMasterTemplate();		
			
		}
		
	}
	
}
?>