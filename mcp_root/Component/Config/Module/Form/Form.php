<?php 
/*
* Declare config settings 
*/
class MCPConfigForm extends MCPModule {
	
	private
	
	/*
	* Form validator 
 	*/
	$_objValidator
	
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
	,$_arrFrmErrors;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		
		/*
		* Get form validator 
		*/
		$this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
		
		/*
		* Add dynamic rules to validator for validating component and path type
		*/
		$this->_objValidator->addRule('component',array($this,'validate_component'));
		$this->_objValidator->addRule('path',array($this,'validate_path'));
		
		/*
		* Get form post 
		*/
		$this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
		
		/*
		* Empty values and errors
		*/
		$this->_arrFrmValues = array();
		$this->_arrFrmErrors = array();
	}
	
	/*
	* Handle form 
	*/
	private function _handleFrm() {
		
		/*
		* Set form values 
		*/
		$this->_setFrmValues();
		
		/*
		* validate form values
		*/
		if($this->_arrFrmPost !== null) {
			
			/*
			* copy and reformat values to be compatible w/ validator
			*/
			$arrValidate = $this->_getFrmConfig();
			
			foreach($arrValidate as &$arrItem) {
				if(!isset($arrItem['values'])) continue;
				
				$arrCollect = array();
				foreach($arrItem['values'] as $arrValue) {
					$arrCollect[] = $arrValue['value'];
				}
				
				$arrItem['values'] = $arrCollect;
			}
			
			$this->_arrFrmErrors = $this->_objValidator->validate($arrValidate,$this->_arrFrmValues);
		}
		
		/*
		* Commit site configuration changes to database 
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
			$this->_setFrmEdit();
		}
	}
	
	/*
	* Set form as being edited after a submit occurs 
	*/
	private function _setFrmSaved() {
		
		/*
		* Base config schema - empty values fallback to defaults
		*/
		$arrSchema = $this->_getFrmConfig();
		
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				default:
					$this->_arrFrmValues[$strField] = isset($this->_arrFrmPost[$strField]) && (!is_string($this->_arrFrmPost[$strField]) || strlen($this->_arrFrmPost[$strField]) != 0)?$this->_arrFrmPost[$strField]:$arrSchema[$strField]['value'];
			}
		}
		
	}
	
	/*
	* Set form as being edited before submit
	*/
	private function _setFrmEdit() {
		
		/*
		* Sites configuration values
		*/
		$arrSchema = $this->_objMCP->getEntireConfig();
		
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				default:
					$this->_arrFrmValues[$strField] = $arrSchema[$strField];
			}
		}
		
	}
	
	/*
	* Save form data to db 
	*/
	private function _frmSave() {
            
                try {
		
                    /*
                    * Update sites configuration settings w/ new values 
                    */
                    $this->_objMCP->setMultiConfigValues($this->_arrFrmValues);
                    
                    /*
                    *Provide status message for successful save operation 
                    */
                    $this->_objMCP->addSystemStatusMessage('Config has been successfully updated.');
                
                    /*
                    * Reset form values to reflect new values
                    */
                    $this->_setFrmEdit();
                    
                    
                } catch(Exception $e) {
                    
                    $this->_objMCP->addSystemErrorMessage('An error has occurred that prevented configuation form saving. Please try again.');
                    
                }
		
	}
	
	/*
	* Get forms configuration definition
	* 
	* @return array configuration
	*/
	private function _getFrmConfig() {
		return $this->_objMCP->getConfigSchema();
	}
	
	/*
	* Get form name 
	* 
	* @return str form name
	*/
	private function _getFrmName() {
		return 'frmConfig';
	}
	
	/*
	* Get form fields
	* 
	* @return array form fields
	*/
	private function _getFrmFields() {
		return array_keys($this->_getFrmConfig());
	}
	
	public function execute($arrArgs) {
		
		/*
		* Check permissions 
		* Can user add/ edit config
		* 
		* - todo break into sections - permission handling per section of config
		*/
		$perm = $this->_objMCP->getPermission(MCP::EDIT,'Config');
		if(!$perm['allow']) {
			throw new MCPPermissionException($perm);
		}
		
		/*
		* process form 
		*/
		$this->_handleFrm();
		
		/*
		* Load template data 
		*/
		$this->_arrTemplateData['name'] = $this->_getFrmName();
		$this->_arrTemplateData['action'] = $this->getBasePath();
		$this->_arrTemplateData['config'] = $this->_getFrmConfig();
		$this->_arrTemplateData['values'] = $this->_arrFrmValues;
		$this->_arrTemplateData['errors'] = $this->_arrFrmErrors;
		$this->_arrTemplateData['legend'] = 'Site Config';
		
		// echo '<pre>',print_r($this->_arrFrmValues),'</pre>';
		
		/*
		* Return control to MCP w/ template 
		*/
		return 'Form/Form.php';
	}
	
	/*
	* Validate component 
	* For use with validator as dynamic rule
	* 
	* @param str value
	* @param str label for value
	* @return str error - empty string identifies no error
	*/
	public function validate_component($strValue,$strLabel) {
		// need think on this
		return '';
	}
	
	/*
	* Validate path
	* For use with validator as dynamic rule
	* 
	* @param str value
	* @param str label for value
	* @return str error - empty string identifies no error
	*/
	public function validate_path($strValue,$strLabel) {
		// need to think on this one
		return '';
	}
	
}
?>