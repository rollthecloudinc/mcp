<?php
/*
* All unresolved modules are passed though here
*/
class MCPNavigationRouter extends MCPModule {
	
	private
	
	/*
	* Navigation data access layer 
	*/
	$_objDAONavigation
	
	,$_strComponent
	,$_arrArgs
	
	/*
	* Navigation links associated with request 
	*/
	,$_arrLink
	,$_boolEdit
	,$_arrExternalArgs
	
	,$_strContent
	,$_strTpl
	
	/*
	* content switch 
	*/
	,$_strContentType = null;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		// Fetch Navigation DAO
		$this->_objDAONavigation = $this->_objMCP->getInstance('Component.Navigation.DAO.DAONavigation',array($this->_objMCP));
		
		// Set defaults
		$this->_strContent = '';
		$this->_strTpl = 'PageNotFound';
	}
	
	public function execute($arrArgs) {
	
		$strModule = empty($arrArgs)?null:array_shift($arrArgs);
		
		if($strModule !== null) {
			if(strcmp($strModule,'Index') == 0) {
				$this->_executeGlobalIndex();
			} else if(strcmp($strModule,'Master') == 0) {
				$this->_executeGlobalMaster();
			} else if(strcmp($strModule,'') == 0) {
				$this->_executeSiteIndex();
			} else if(strcmp($strModule,'Component') == 0) {
				$this->_executeComponent($arrArgs);
			} else if(strcmp($strModule,'PlatForm') == 0) {
				$this->_executePlatForm($arrArgs);				
			} else if(strcmp($strModule,'Admin') == 0) {
				$this->_executeAdmin($arrArgs);
			} else if(strcmp($strModule,'Permission Denied') == 0) {
				$this->_executePermissionDenied($arrArgs);
			} else {
				$this->_executeDynamic($strModule,$arrArgs);
			}
		}
		
		$this->_arrTemplateData['ROUTE_CONTENT'] = $this->_strContent;
		$this->_arrTemplateData['nav_link'] = $this->_arrLink;
		$this->_arrTemplateData['edit_link'] = $this->_boolEdit;
		$this->_arrTemplateData['link_path'] = $this->_getLinkPath(!$this->_boolEdit);
		$this->_arrTemplateData['content_type'] = $this->_strContentType;
		$this->_arrTemplateData['display_edit_link'] = $this->_objMCP->getUsersId()?1:0;
		
		return "Router/{$this->_strTpl}.php";
		
	}
	
	private function _executePermissionDenied($arrArgs) {
		$this->_arrTemplateData['message'] = !empty($arrArgs)?array_shift($arrArgs):'Permission Denied';
		$this->_strTpl = 'PermissionDenied';
	}
	
	private function _executeGlobalIndex() {		
		$this->_strContent = $this->_objMCP->executeComponent('Component.Util.Module.Index',array());
		$this->_strTpl = 'Redirect';
	}
	
	private function _executeSiteIndex() {
		$this->_strContent = $this->_objMCP->executeModule('Site.*.Module.Index',array(),'Site.*.Template',null,null,1);
		$this->_strTpl = 'Redirect';
	}
	
	private function _executeGlobalMaster() {		
		$this->_strContent = $this->_objMCP->executeComponent('Component.Util.Module.Master',array());
		$this->_strTpl = 'Redirect';
	}
	
	private function _executeAdmin($arrArgs) {
		/*
		* The admin module is able to be changed or extended by doing so and changing the config
		* admin module path to the location of the new admin module.
		*/
		$this->_strContent = $this->_objMCP->executeComponent($this->_objMCP->getConfigValue('site_admin_module'),$arrArgs,null,array($this));
		$this->_strTpl = 'Redirect';
	}
	
	/*
	* Provides global access to components
	* 
	* @param array request args
	*/
	private function _executeComponent($arrArgs) {
		
		/*
		* Get requested component name 
		*/
		$this->_strComponent = !empty($arrArgs)?array_shift($arrArgs):'';
		$this->_arrArgs = $arrArgs;
		
		/*
		* Execute component and assign content to template
		*/
		$this->_strContent = $this->_objMCP->executeComponent("Component.{$this->_strComponent}",$this->_arrArgs,'',array($this));
		
		$this->_strTpl = 'Redirect';
	}
	
	/*
	* Provides global access to platforms
	* 
	* @param array request args
	*/
	private function _executePlatForm($arrArgs) {
		
		/*
		* Get requested component name 
		*/
		$this->_strComponent = !empty($arrArgs)?array_shift($arrArgs):'';
		$this->_arrArgs = $arrArgs;
		
		/*
		* Execute component and assign content to template
		*/
		$this->_strContent = $this->_objMCP->executeComponent("PlatForm.{$this->_strComponent}",$this->_arrArgs,'',array($this));
		
		$this->_strTpl = 'Redirect';
	}
	
	private function _executeDynamic($strModule,$arrArgs) {
		
		/*
		* Attempt to locate navigation item that matches requested modules name 
		*/
		$arrRoute = $this->_objDAONavigation->fetchRoute($strModule,$this->_objMCP->getSitesId());
		
		/*
		* Content or target module must exist 
		*/
		if($arrRoute === null || (empty($arrRoute['target_module']) && empty($arrRoute['body_content']))) {
			return;
		}
		
		// assign link associated with request
		$this->_arrLink = $arrRoute;
		
		// check for special edit keyword
		$this->_boolEdit = !empty($arrArgs) && strcmp('Link-Edit',$arrArgs[0]) == 0 && array_shift($arrArgs)?true:false; 
		
		// set external arguments
		$this->_arrExternalArgs = $arrArgs;
		
		// on edit match internal redirect to link editor
		if($this->_boolEdit === true) {
			
			$this->_strContent = $this->_objMCP->executeComponent(
				'Component.Navigation.Module.Form.Link'
				,array($arrRoute['navigation_links_id'])
				,null
				,array($this)
				,array(array($this,'onLinkUpdate'),'NAVIGATION_LINK_UPDATE')
			);
			$this->_strTpl = 'Redirect';
			return;
		}
							
		$arrRouteArgs = array(
			$arrRoute['target_module']
			,empty($arrRoute['target_module_args'])?$arrArgs:array_merge(unserialize(base64_decode($arrRoute['target_module_args'])),$arrArgs)
		);
				
		if(!empty($arrRoute['target_template'])) {
			$arrRouteArgs[] = $arrRoute['target_template'];
		} else {
			$arrRouteArgs[] = null;
		}
		
		$arrRouteArgs[] = array(
			$this
			,!empty($arrRoute['target_module_config'])?unserialize(base64_decode($arrRoute['target_module_config'])):null
		);
		
		// fow now make this magical - in the future perhaps add boolean to navigation_links table to differentiate component
		if($arrRoute['body_content']) {
			$this->_strContent = $arrRoute['body_content'];
			$this->_strContentType = $arrRoute['body_content_type'];;
		} else if(strpos($arrRoute['target_module'],'Component.') === 0 || strpos($arrRoute['target_module'],'PlatForm.') === 0) {
			$this->_strContent = call_user_func_array(array($this->_objMCP,'executeComponent'),$arrRouteArgs);	
		} else {
			$this->_strContent = call_user_func_array(array($this->_objMCP,'executeModule'),$arrRouteArgs);	
		}
		
		$this->_strTpl = 'Redirect';
	}
	
	public function getBasePath() {
		
		/*
		* Get parent base path 
		*/
		$strBasePath = parent::getBasePath();
		
		if($this->_boolEdit === true) {
			$strBasePath = "$strBasePath/Link-Edit";
			
			if(!empty($this->_arrExternalArgs)) {
				$strBasePath.= '/'.implode('/',$this->_arrExternalArgs);
			}
			
		}
		
		if($this->_strComponent === null) {
			return $strBasePath;
		} else {
			return "$strBasePath/{$this->_strComponent}";
		}
		
	}
	
	/*
	* Creates edit and back path for a navigation link
	* 
	* @param bool edit
	* @return str back or edit path for navigation link
	*/
	private function _getLinkPath($boolEdit=false) {
		if($this->_arrLink === null) return null;
		
		$strLinkPath = parent::getBasePath();
		
		if($boolEdit === true) {
			$strLinkPath.= "/Link-Edit";
		}
		
		if(!empty($this->_arrExternalArgs)) {
			$strLinkPath.= '/'.implode('/',$this->_arrExternalArgs);
		}
		
		return $strLinkPath;
		
	}
	
	/*
	* Called when is updated
	*
	* @param obj event target
	*/
	public function onLinkUpdate($objTarget) {
		/*
		* update link data
		*/
		$this->_arrLink = $this->_objDAONavigation->fetchLinkById($this->_arrLink['navigation_links_id']);
		
		/*
		* Reset the request module
		*/
		$this->_objMCP->setModule($this->_arrLink['sites_internal_url']);
	}
	
}
?>