<?php 
/*
* Simple email contact form 
*/
class MCPUtilEmail extends MCPModule {
	
	private
	
	/*
	* Validator object 
	*/
	$_objValidator
	
	/*
	* Emailer object 
	*/
	,$_objEmailer
	
	/*
	* Form values 
	*/
	,$_arrFrmValues
	
	/*
	* Form errors 
	*/
	,$_arrFrmErrors
	
	/*
	* send mail switch 
	*/
	,$_boolSendMail = false;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		// Get validator object
		$this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
		
		// Get emailer object
		$this->_objEmailer = $this->_objMCP->getInstance('App.Lib.Email.Emailer',array());
		
		// empty form values and errors
		$this->_arrFrmErrors = array();
		$this->_arrFrmValues = array();
		
		// get form post data
		$this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
	
	}
	
	/*
	* Handle form 
	*/
	private function _handleForm() {
		
		/*
		* Set form values 
		*/
		$this->_setFrmValues();
		
		/*
		* Validate form values 
		*/
		if($this->_arrFrmPost !== null) {
			$this->_arrFrmErrors = $this->_objValidator->validate($this->_getFrmConfig(),$this->_arrFrmValues);
		}
		
		/*
		* Save form data 
		*/
		if($this->_arrFrmPost !== null && empty($this->_arrFrmErrors)) {
			$this->_frmSave();
		}
		
	}
	
	/*
	* Set form values 
	*/
	private function _setFrmValues() {
		if($this->_arrFrmPost !== null) {
			$this->_setFrmSaved();
		} else {
			$this->_setFrmCreate();
		}
	}
	
	/*
	* Set form values from submited data
	*/
	private function _setFrmSaved() {
		
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				
				default:
					$this->_arrFrmValues[$strField] = isset($this->_arrFrmPost[$strField])?$this->_arrFrmPost[$strField]:'';
				
			}
		}
		
	}
	
	/*
	* Set form values as defaults
	*/
	private function _setFrmCreate() {
		
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				
				default:
					$this->_arrFrmValues[$strField] = '';	
				
			}
		}
		
	}
	
	/*
	* Save form data 
	*/
	private function _frmSave() {
		
		/*
		* Delay creation and sending of email until template is loaded with modules data
		*/
		/*$this->_objMCP->subscribe(
			$this
			,'TEMPLATE_LOAD'
			,array($this,'onTemplateLoad')
		);*/
		
		$this->_boolSendMail = true;
		
		
	}
	
	/*
	* Callback when template data is loaded into template 
	*/
	public function onTemplateLoad() {
		
		/*
		* remove event listener or infinate loop will occur 
		*/
		//$this->_objMCP->unsubscribe($this,'TEMPLATE_LOAD',array($this,'onTemplateLoad'));
		
		/*
		* Create email format to send to emailer 
		*/
		$arrEmail = array();
		
		/*
		* Get plain text and HTML email
		*/
		$arrEmail['body']['html'] = $this->_objMCP->executeComponent('Component.Util.Module.Master.Email',array('HTML'),null,array($this));
		$arrEmail['body']['plain_text'] = $this->_objMCP->executeComponent('Component.Util.Module.Master.Email',array('PlainText'),null,array($this));
		
		/*
		* Set headers 
		*/
		//$arrEmail['to'][] = '';
		//$arrEmail['from'][] = '';
		
		/*
		* Send out the email
		*/
		//$boolSent = $this->_objEmailer->email($arrEmail);
		
		// echo '<pre>',print_r($arrEmail),'</pre>';
		// return 'Email/MailSent.php';
		
		/*
		* for now send email directly as plain text
		*/
		if(strcmp('HTML',$this->getConfigValue('email_type')) == 0) {
			mail($this->getConfigValue('email'),$this->getConfigValue('email_subject'),$arrEmail['body']['html'],"From: {$this->_arrFrmValues['email']}\r\nReply-To: {$this->_arrFrmValues['email']}\r\nContent-type: text/html; charset=utf-8");
		} else {
			mail($this->getConfigValue('email'),$this->getConfigValue('email_subject'),$arrEmail['body']['plain_text'],"From: {$this->_arrFrmValues['email']}\r\nReply-To: {$this->_arrFrmValues['email']}");
		}
		
		return 'Email/MailSent.php';
		
		
	}
	
	/*
	* Get form configuration 
	* 
	* @return array form configuration
	*/
	private function _getFrmConfig() {
		return $this->_objMCP->getFrmConfig($this->getPkg());
	}
	
	/*
	* Get form fields
	* 
	* @return array form fields
	*/
	private function _getFrmFields() {
		return array_keys($this->_getFrmConfig());
	}
	
	/*
	* Get form name
	* 
	* @return str form name
	*/
	private function _getFrmName() {
		return 'frmEmail';
	}
	
	public function execute($arrArgs) {
		
		/*
		* process form 
		*/
		$this->_handleForm();
		
		/*
		* load template data 
		*/
		$this->_arrTemplateData['config'] = $this->_getFrmConfig();
		$this->_arrTemplateData['name'] = $this->_getFrmName();
		$this->_arrTemplateData['action'] = $this->getBasePath();
		$this->_arrTemplateData['method'] = 'POST';
		$this->_arrTemplateData['values'] = $this->_arrFrmValues;
		$this->_arrTemplateData['errors'] = $this->_arrFrmErrors;
		$this->_arrTemplateData['legend'] = $this->getConfigValue('form_legend');
		
		$this->loadTemplateData();
		
		if($this->_boolSendMail === true) {
			return $this->onTemplateLoad();
		} else {
			return 'Email/Email.php';
		}
		
	}
	
	/*
	* Get base path to this module
	* 
	* @return str base path
	*/
	public function getBasePath() {
		return parent::getBasePath();
	}
	
	/*
	* Get HTML email contents
	* 
	* @return str HTML email
	*/
	public function getHTMLEmailContent() {
		return $this->_objMCP->fetch("{$this->getTemplatePath()}/Email/HTML.php",$this);
	}
	
	/*
	* Get plain text email content
	* 
	* @return str plain text email
	*/
	public function getPlainTextEmailContent() {
		return $this->_objMCP->fetch("{$this->getTemplatePath()}/Email/PlainText.php",$this);
	}
	
}
?>