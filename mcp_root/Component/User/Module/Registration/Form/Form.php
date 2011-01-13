<?php 
/*
* User registration form 
*/
class MCPUserRegistrationForm extends MCPModule {
	
	protected
	
	/*
	* User DAO 
	*/
	$_objDAOUser
	
	/*
	* Validation object 
	*/
	,$_objValidator
	
	/*
	* Form post data 
	*/
	,$_arrFrmPost
	
	/*
	* Form values 
	*/
	,$_arrFrmValues
	
	/*
	* Form errors 
	*/
	,$_arrFrmErrors
	
	/*
	* User to edit
	*/
	,$_arrUser;
	
	public function __construct(MCP $objMCP,MCPModule $objModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objModule,$arrConfig);
		$this->_init();
	}
	
	protected function _init() {
		
		// Get user DAO
		$this->_objDAOUser = $this->_objMCP->getInstance('Component.User.DAO.DAOUser',array($this->_objMCP));
		
		// Get validator
		$this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
		
		// Set post data
		$this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
		
		// Set form values and error as empty
		$this->_arrFrmValues = array();
		$this->_arrFrmErrors = array();
		
		// add custom email and username validation rules
		$this->_objValidator->addRule('custom_username',array($this,'validateUsername'));
		$this->_objValidator->addRule('custom_email',array($this,'validateEmail'));
		
	}
	
	/*
	* Process form data 
	*/
	protected function _frmHandle() {
		
		/*
		* Set form values 
		*/
		$this->_setFrmValues();
		
		/*
		* Validate form 
		*/
		if($this->_arrFrmPost !== null) {
			$this->_arrFrmErrors = $this->_objValidator->validate($this->_getFrmConfig(),$this->_arrFrmValues);
		}
		
		if($this->_getUser() !== null) unset($this->_arrFrmErrors['pwd']);
		
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
	protected function _setFrmValues() {
		if($this->_arrFrmPost !== null) {
			$this->_setFrmSave();
		} else if($this->_getUser() !== null) {
			$this->_setFrmEdit();
		} else {
			$this->_setFrmCreate();
		}
	}
	
	/*
	* Set form values as submited 
	*/
	protected function _setFrmSave() {
		
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				default:
					$this->_arrFrmValues[$strField] = isset($this->_arrFrmPost[$strField])?$this->_arrFrmPost[$strField]:'';
			}
		}
		
	}
	
	/*
	* Set form values as editing user 
	*/
	protected function _setFrmEdit() {
		
		/*
		* Get current user 
		*/
		$arrUser = $this->_getUser();
		
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				default:
					$this->_arrFrmValues[$strField] = $arrUser[$strField];
			}
		}
		
	}
	
	/*
	* Set form values as create new user 
	*/
	protected function _setFrmCreate() {
		
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
	protected function _frmSave() {
		
		$arrSave = $this->_arrFrmValues;
		
		if($this->_getUser() !== null) {
			$arrUser = $this->_getUser();
			$arrSave['users_id'] = $arrUser['users_id'];
		} else {
			$arrSave['sites_id'] = $this->_objMCP->getSitesId();
		}
		
		/*
		* @TODO: upon successful registration send email confirmation
		* 
		* Perhaps require email confirmation to activate acount. Could
		* make this a config variable - configure per site.
		*/
		
		return $this->_objDAOUser->saveUser($arrSave);
		
	}
	
	/*
	* Get form configuration
	* 
	* @return array form configuration
	*/
	protected function _getFrmConfig() {
		
		/*
		* get the user being edited
		*/
		$arrUser = $this->_getUser();
		
		/*
		* Resolve entity id for adding dynamic fields 
		*/
		if($arrUser !== null) {
			$entity_id = $arrUser['sites_id'];
		} else {
			$entity_id = $this->_objMCP->getSitesId();
		}
		
		return $this->_objMCP->getFrmConfig($this->getPkg(),'frm',true,array('entity_type'=>'MCP_SITES','entities_id'=>$entity_id));
	}
	
	/*
	* Get form fields
	* 
	* @return array form fields
	*/
	protected function _getFrmFields() {
		return array_keys($this->_getFrmConfig());
	}
	
	/*
	* Get form name
	* 
	* @return str form name
	*/
	protected function _getFrmName() {
		return 'frmUserRegistration';
	}
	
	/*
	* Set user to edit
	* 
	* @param array user data
	*/
	protected function _setUser($arrUser) {
		$this->_arrUser = $arrUser;
	}
	
	/*
	* Get current user to edit 
	* 
	* @return array user data
	*/
	protected function _getUser() {
		return $this->_arrUser;
	}
	
	public function execute($arrArgs) {
		
		/*
		* Get user to edit if exists 
		*/
		$intUser = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		if($intUser !== null) {
			$this->_setUser($this->_objDAOUser->fetchById($intUser));
		}
		
		/*
		* Check permissions 
		* Can person edit or register user?
		*/
		$perm = $this->_objMCP->getPermission(($intUser === null?MCP::ADD:MCP::EDIT),'User',($intUser === null?$this->_objMCP->getSitesId():$intUser) );
		if(!$perm['allow']) {
			throw new MCPPermissionException($perm);
		}
		
		$this->_frmHandle();
		
		/*
		* Set template data 
		*/
		$this->_arrTemplateData['action'] = $this->getBasePath();
		$this->_arrTemplateData['method'] = 'POST';
		$this->_arrTemplateData['name'] = $this->_getFrmName();
		$this->_arrTemplateData['config'] = $this->_getFrmConfig();
		$this->_arrTemplateData['values'] = $this->_arrFrmValues;
		$this->_arrTemplateData['errors'] = $this->_arrFrmErrors;
		$this->_arrTemplateData['legend'] = $this->_getUser() === null?'Register':'Edit User';
		
		return 'Form/Form.php';
	}
	
	/*
	* Path to module current state
	* 
	* @return str base path
	*/
	public function getBasePath() {
		$strBasePath = parent::getBasePath();
		
		$arrUser = $this->_getUser();
		
		if($arrUser !== null) {
			$strBasePath.= "/{$arrUser['users_id']}";
		}
		
		return $strBasePath;
	}
	
	/*
	* Custom username validation rule 
	* 
	* @param str value
	* @param str form field label
	* @return str error string
	*/
	public function validateUsername($strValue,$strLabel) {
		
		/*
		* Get current user 
		*/
		$arrUser = $this->_getUser();
		
		/*
		* alpha numeric and unique per site
		*/	
		if(!preg_match('/^[a-zA-Z0-9-_]*?$/',$strValue)) {
			return "$strLabel may only contain letter, number, hyphen and underscore characters";
		}
		
		$strFilter = sprintf(
			"deleted = 0 AND sites_id = %s AND username = '%s' %s"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($strValue)
			,$arrUser !== null?" AND users_id <> {$this->_objMCP->escapeString($arrUser['users_id'])}":''
		);
		
		if(array_shift($this->_objDAOUser->listAll('*',$strFilter))) {
			return "$strLabel $strValue is taken by another user";
		}
		
		return '';
		
	}
	
	/*
	* Custom email address validation rule
	* 
	* @param str value
	* @param str form field label
	* @return str error string
	*/
	public function validateEmail($strValue,$strLabel) {
		
		/*
		* Get current user 
		*/
		$arrUser = $this->_getUser();
		
		/*
		* make sure email is valid and unique per site 
		*/
		$arrConfig = $this->_getFrmConfig();
		$arrConfig['email_address']['type'] = 'email';
		
		$strValidEmail = $this->_objValidator->validate(array('email_address'=>$arrConfig['email_address']),array('email_address'=>$strValue));
		$strValidEmail = isset($strValidEmail['email_address'])?$strValidEmail['email_address']:'';
		
		$strFilter = sprintf(
			"deleted = 0 AND sites_id = %s AND email_address = '%s' %s"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($strValue)
			,$arrUser !== null?" AND users_id <> {$this->_objMCP->escapeString($arrUser['users_id'])}":''
		);
		
		if(empty($strValidEmail) && array_shift($this->_objDAOUser->listAll('*',$strFilter))) {
			$strValidEmail = "$strLabel $strValue is already registered";
		}
		
		return $strValidEmail;
		
	}
	
}
?>