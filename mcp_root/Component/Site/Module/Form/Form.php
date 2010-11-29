<?php
/*
* Create/Edit Site 
*/
class MCPSiteForm extends MCPModule {
	
	protected
	
	/*
	* Site data access layer 
	*/
	$_objDAOSite
	
	/*
	* Validator object 
	*/
	,$_objValidator
	
	/*
	* Post data 
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
	* Current site data (being edited) 
	*/
	,$_arrSite;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	protected function _init() {
		// Get Site data access layer
		$this->_objDAOSite = $this->_objMCP->getInstance('Component.Site.DAO.DAOSite',array($this->_objMCP));
	
		// Get validator
		$this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
		
		// Get posted data
		$this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
	
		// Reset form values and errors
		$this->_arrFrmValues = array();
		$this->_arrFrmErrors = array();
		
		// Add custom validation rule callbacks
		$this->_addCustomValidationRules();
	}
	
	/*
	* Overload validator with custom validation rule callbacks 
	*/
	protected function _addCustomValidationRules() {
		
		// Add custom validation rules
		$site =& $this->_arrSite;
		$dao = $this->_objDAOSite;
		$mcp = $this->_objMCP;
		
		// validate site name as unique
		$this->_objValidator->addRule('site_name',function($value,$label) use (&$site,$dao,$mcp) {
				
			$filter = sprintf(
				"s.site_name = '%s' %s"
				,$mcp->escapeString($value)
				,$site === null?'':"AND s.sites_id <> {$mcp->escapeString($site['sites_id'])}"
			);
				
			if(array_pop( $dao->listAll('s.sites_id',$filter) ) !== null) {
				return "$label Must be unique. Chosen name is already taken, please choose another $label.";
			}
				
			return '';
		});
		
		// validate site directory as unique and following proper format
		$this->_objValidator->addRule('site_directory',function($value,$label) use (&$site,$dao,$mcp) {
				
			if(!preg_match('/^[A-Za-z0-9_]*?$/',$value)) {
				return "$label may only contain alpha-numeric characters and underscores.";
			}
				
			$filter = sprintf(
				"s.site_directory = '%s' %s"
				,$mcp->escapeString($value)
				,$site === null?'':"AND s.sites_id <> {$mcp->escapeString($site['sites_id'])}"
			);
				
			if(array_pop( $dao->listAll('s.sites_id',$filter) ) !== null) {
				return "$label Must be unique. Chosen $label is already taken, please choose another $label.";
			}
				
			return '';
				
		});
		
		// validate site modeul prefix as unique and following proper format
		$this->_objValidator->addRule('site_module_prefix',function($value,$label) use (&$site,$dao,$mcp) {
				
			if(!preg_match('/^[A-Za-z0-9_]*?$/',$value)) {
				return "$label may only contain alpha-numeric characters and underscores.";
			}
				
			$filter = sprintf(
				"s.site_module_prefix = '%s' %s"
				,$mcp->escapeString($value)
				,$site === null?'':"AND s.sites_id <> {$mcp->escapeString($site['sites_id'])}"
			);
				
			if(array_pop( $dao->listAll('s.sites_id',$filter) ) !== null) {
				return "$label Must be unique. Chosen $label is already taken, please choose another $label.";
			}
				
			return '';
				
		});	
	}
	
	/*
	* Process the form 
	*/
	protected function _process() {
		
		// Set form values
		$this->_setFrmValues();
		
		/*
		* Validate form values 
		*/
		if($this->_arrFrmPost !== null) {
			$this->_arrFrmErrors = $this->_objValidator->validate($this->_getFrmConfig(),$this->_arrFrmValues);
		}
		
		/*
		* Save form data to database 
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
			$this->_setFrmSaved();
		} else if($this->_getSite() !== null) {
			$this->_setFrmEdit();
		} else {
			$this->_setFrmCreate();
		}
	}
	
	/*
	* Set form values from post array 
	*/
	protected function _setFrmSaved() {
		
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				default:
					$this->_arrFrmValues[$strField] = isset($this->_arrFrmPost[$strField])?$this->_arrFrmPost[$strField]:'';
			}
		}
		
	}
	
	/*
	* Set form values from site being edited 
	*/
	protected function _setFrmEdit() {
		
		/*
		* Get current site 
		*/
		$arrSite = $this->_getSite();
		
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				default:
					$this->_arrFrmValues[$strField] = isset($arrSite[$strField])?$arrSite[$strField]:'';
			}
		}
		
	}
	
	/*
	* Set form values as new site 
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
	* Save form values 
	*/
	protected function _frmSave() {
		
		echo '<pre>',print_r($this->_arrFrmValues),'</pre>';
		
	}
	
	/*
	* Get site form config
	* 
	* @return array site form configuration
	*/
	protected function _getFrmConfig() {
		return $this->_objMCP->getFrmConfig('Component.Site.Module.Form','frm',true);
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
		return 'frmSite';
	}
	
	/*
	* Get the current site 
	* 
	* @return array site data
	*/
	protected function _getSite() {
		return $this->_arrSite;
	}
	
	/*
	* Set the current site
	* 
	* @param arr site data
	*/
	protected function _setSite($arrSite) {
		$this->_arrSite = $arrSite;
	}
	
	public function execute($arrArgs) {
		
		/*
		* Extract site to edit
		*/
		$intSitesId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		/*
		* Set site for edit 
		*/
		if($intSitesId !== null) {
			$this->_setSite($this->_objDAOSite->fetchById($intSitesId));
		}
		
		/*
		* Check permissions 
		* Can user add/ edit site?
		*/
		/*$perm = $this->_objMCP->getPermission('MCP_SITE',$intSitesId);
		if(!$perm->allowed()) {
			throw new MCPPermissionException($perm);
		}*/
		
		/*
		* Process the form 
		*/
		$this->_process();
		
		$this->_arrTemplateData['name'] = $this->_getFrmName();
		$this->_arrTemplateData['action'] = $this->getBasePath();
		$this->_arrTemplateData['method'] = 'POST';
		$this->_arrTemplateData['config'] = $this->_getFrmConfig();
		$this->_arrTemplateData['values'] = $this->_arrFrmValues;
		$this->_arrTemplateData['errors'] = $this->_arrFrmErrors;
		$this->_arrTemplateData['legend'] = 'Site';
		
		return 'Form/Form.php';
	}
	
	/*
	* Override base path to append site reference to edit
	* 
	* @return str base path
	*/
	public function getBasePath() {
		
		$strPath = parent::getBasePath();
		$arrSite = $this->_getSite();
		
		if($arrSite !== null) {
			$strPath.= "/{$arrSite['sites_id']}";
		}
		
		return $strPath;
	}
	
}
?>