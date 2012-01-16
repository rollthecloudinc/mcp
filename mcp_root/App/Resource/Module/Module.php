<?php
/*
* Abstract class definition for a module
* All modules should extend this class.
*/
$this->import('App.Core.Resource');
abstract class MCPModule extends MCPResource {

	protected
	
	/*
	* Data to be passed to template
	*/
	$_arrTemplateData
	
	/*
	* Module that is responsible for instantation of this module
	*/
	,$_objParentModule;
	
	private
	
	/*
	* Module configuration data
	*/
	$_arrModConfig;
	
	/*
	* @param obj MCP
	* [@param] obj parent module 
	* [@param] obj override config values
	*/
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP);
		
		$this->_objParentModule = $objParentModule;
		$this->_arrTemplateData = array();
		$this->_arrModConfig = array();
		
		/*
		* Get modules base configuration
		*/
		$arrBaseConfig = $this->_objMCP->getModConfig($this->getPkg());
		
		/*
		* Set config values 
		*/
		if($arrBaseConfig !== null) {
			foreach($arrBaseConfig as $strSetting=>$arrConfigSetting) {
				$this->_arrModConfig[$strSetting] = !isset($arrConfig[$strSetting])?isset($arrConfigSetting['default'])?$arrConfigSetting['default']:null:$arrConfig[$strSetting];
			}
		}
                
                /*
                * Additional settings 
                * 
                * - Define settings not explictily declared in config. 
                */
                foreach($arrConfig as $strSetting=>$mixValue) {
                    if(!isset($this->_arrModConfig[$strSetting])) {
                        $this->_arrModConfig[$strSetting] = $mixValue;
                    }
                }
		
	}

	/*
	* Get modules name
	*
	* @return str module name
	*/
	public function getName() {
		return 'mod'.substr(get_class($this),3); // Remove MCP prefix
	}
	
	/*
	* Assigns modules template data to 
	* Template object under modules name
	*/
	public function loadTemplateData() {
		$this->_objMCP->assign($this->getName(),$this->_arrTemplateData);
		
		/*
		* Fire template load event 
		*/
		$this->_objMCP->fire($this,'TEMPLATE_LOAD');
	}
	
	/*
	* Short-cut method to execute module
	* NOTE: Passes object as parent module automatically. When different behavior is desired call from MCP directly 
	*/
	public function executeModule($strPkg,$arrArgs=null,$strTpl='Site.*.Template',$arrModuleArgs=null,$arrListener=null) {
		return $this->_objMCP->executeModule($strPkg,$arrArgs,$strTpl,($arrModuleArgs === null?array($this):$arrModuleArgs),$arrListener);
	}
	
	/*
	* Short-cut method to execute component
	* NOTE: Passes send object as parent module automatically. When different behavior is desired call from MCP directly.
	*/
	public function executeComponent($strPkg,$arrArgs=null,$strTpl=null,$arrModuleArgs=null,$arrListener=null) {
		return $this->_objMCP->executeComponent($strPkg,$arrArgs,$strTpl,($arrModuleArgs === null?array($this):$arrModuleArgs),$arrListener);
	}
	
	/*
	* Get path to template folder
	* 
	* @return str application file path to templates
	*/
	public function getTemplatePath() {
		return ROOT.'/'.str_replace(PKG,DS,str_replace(PKG.'Module'.PKG,PKG.'Template'.PKG,$this->getPkg()));
	}
	
	/*
	* Get the base path to the module
	*/
	public function getBasePath() {	
		if($this->_objParentModule === null) {
			return $this->_objMCP->getBaseUrl().'/'.$this->_objMCP->getModule();
		} else {
			return $this->_objParentModule->getBasePath();
		}
	}
	
	/*
	* Get module config value
	* 
	* @param str config name
	* @return str config value
	*/
	public function getConfigValue($strName) {
		return isset($this->_arrModConfig[$strName])?$this->_arrModConfig[$strName]:null;
	}
	
	/*
	* Makes all modules compatible with pagination component 
	* 
	* @param int SQL offset
	* @param int SQL limit
	* @return int found rows (total items)
	*/
	public function paginate($intOffset,$intLimit) {		
	}
	
	/*
	* Makes all modules compatible with alphabetization pagination module
	* 
	* @param str letter
	* @return int found rows
	*/
	public function alphabetize($strLetter) {
	}
	
	/*
	* Makes all modules compatible with master email template
	* 
	* @return str modules HTML email content
	*/
	public function getHTMLEmailContent() {
		return '';
	}
	
	/*
	* Makes all modules compatible with master email template
	* 
	* @return str modules email plain text content
	*/
	public function getPlainTextEmailContent() {
		return '';
	}
	
	/*
	* Get object to bubble event to
	* 
	* @param obj parent module
	*/
	public function getBubbleTarget() {
		return $this->_objParentModule;
	}
	
	/*
	* Makes all modules compatible with breadcrumb utility
	* 
	* @return array breadcrumb data
	*/
	public function getBreadcrumbData() {
		return null;
	}
	
	/*
	* Execute Module
	*
	* @param arr modules options
	* @return string modules template path
	*/
	abstract public function execute($arrArgs);

}
?>