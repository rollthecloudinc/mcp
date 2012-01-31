<?php
class MCPUserLogin extends MCPModule {

	private static
	
	/*
	* Number of times class has been created
	*/
	$_intInstances = 0;
	
	private
	
	/*
	* Unique instance number for object
	*/
	$_intInstance
        
        /*
        * Validation object 
        */
        ,$_objValidator
        
        /*
        * Form raw post values 
        */
        ,$_arrFrmPost
                
        /*
        * Form derived values 
        */
        ,$_arrFrmValues
           
        /*
        * Form field errors
        */
        ,$_arrFrmErrors;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_intInstance = ++self::$_intInstances;
		$this->_init();
	}
	
        /*
        * Intial set-up 
        */
	protected function _init() {
            
            
                // Get validator
                $this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
            
                // Get post data
                $this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
                
                // Set values and errors array
                $this->_arrFrmValues = array();
                $this->_arrFrmErrors = array();
                
        }
        
        /*
        * Process any submitted form values. 
        */
        protected function _process() {
            
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
		
		/*
		* Authenticate user
		*/
		if($this->_arrFrmPost !== null && empty($this->_arrFrmErrors) ) {
			$this->_frmSave();
		}
		
	}
        
        /*
        * Determine the propr handler to use for setting form values based
        * on whether the form has been sumbitted yet or not.  
        */
        protected function _setFrmValues() {
            if($this->_arrFrmPost !== null) {
                $this->_setFrmAuth();
            } else {
                $this->_setFrmLogin();
            }
        }
        
        /*
        * When form has been submitted this handler will run to set the form
        * values based on the posted values. 
        */
        protected function _setFrmAuth() {
            
           foreach($this->_getFrmFields() as $strField) {
                switch($strField) {
                    
                    case 'rememeber':
                        $this->_arrFrmValues[$strField] = isset($this->_arrFrmPost[$strField])?$this->_arrFrmPost[$strField]:0;
                        continue;
                    
                    default:
                        $this->_arrFrmValues[$strField] = isset($this->_arrFrmPost[$strField])?$this->_arrFrmPost[$strField]:'';
                }                 
           }
            
        }
        
        /*
        * When form has not been submitted this handler will run to set-up any
        * defaults for the form values.  
        */
        protected function _setFrmLogin() {
            
            foreach($this->_getFrmFields() as $strField) {
                switch($strField) {
                    
                    case 'rememeber':
                        $this->_arrFrmValues[$strField] = 0;
                        continue;
                    
                    default:
                        $this->_arrFrmValues[$strField] = '';
                }
            }
            
        }
        
        /*
        * When form is sumbitted successfully without eror this handler
        * handler will be called in order to authenticate the user, set any necessary
        * cookies, etc. 
        */
        protected function _frmSave() {
            
            /*
            * All the logic to handle logging in a user is handled else where. Theerefore,
            * the only thing to do is here is check whether authentication fails and if
            * so provide an error message and clear the form values.  
            */
            if($this->_objMCP->loginUser($this->_arrFrmValues['username'],$this->_arrFrmValues['password'],((bool) $this->_arrFrmValues['rememeber']) ) === false) {
                $this->_setFrmLogin();
                $this->_objMCP->addSystemErrorMessage('Unable to login user.');
            }
            
            
        }
        
        /*
        * Get the derived form name
        * 
        * @return str form name  
        */
        protected function _getFrmName() {
            return "frmUtilLogin_{$this->_intInstance}";
        }
        
        /*
        * Get form field names
        * 
        * @return array form field names  
        */
        protected function _getFrmFields() {
            return array_keys($this->_getFrmConfig());
        }
	
	/*
	* Get login forms configuration
	*
	* @return array login form configuration settings
	*/
	protected function _getFrmConfig() {  
                return $this->_objMCP->getFrmConfig($this->getPkg(),'frm',false);
	}
	
	public function redo() {
		$this->_init();
	}

	public function execute($arrArgs) {
            
                // process the form
                $this->_process();
	
                // set template data
		$this->_arrTemplateData['name'] = $this->_getFrmName();
		$this->_arrTemplateData['action'] = $this->_objMCP->getBasePath();
		$this->_arrTemplateData['method'] = 'post';
		$this->_arrTemplateData['config'] = $this->_getFrmConfig();
		$this->_arrTemplateData['errors'] = $this->_arrFrmErrors;
                $this->_arrTemplateData['values'] = $this->_arrFrmValues;
                $this->_arrTemplateData['legend'] = 'Login';
		$this->_arrTemplateData['instance_num'] = $this->_intInstance;
		
                // form render template
		return 'Login/Login.php';
	}
	
}
?>