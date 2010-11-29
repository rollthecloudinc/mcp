<?php 
/*
* Create or edit navigation item 
*/
class MCPNavigationFormLink extends MCPModule {
	
	private
	
	/*
	* navigation data access layer 
	*/
	$_objDAONavigation
	
	/*
	* Validator object 
	*/
	,$_objValidator
	
	/*
	* Current navigation item data
	*/
	,$_arrLink
	
	/*
	* Form values 
	*/
	,$_arrFrmValues
	
	/*
	* Form errors 
	*/
	,$_arrFrmErrors
	
	/*
	* Identify dynamic links without placeholder 
	*/
	,$_intDataSourcesRowId = null
	
	/*
	* parent may either be link or nav. This property
	* is used to select a parent for the link. The submitted
	* value for the parent will actually be used as the end parent. The
	* same is true with the parent id.
	*/
	,$_strParentType
	,$_intParentId;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		
		// Get navigation DAO
		$this->_objDAONavigation = $this->_objMCP->getInstance('Component.Navigation.DAO.DAONavigation',array($this->_objMCP));
		
		// Get validator
		$this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
		
		// Reset form errors and values
		$this->_arrFrmValues = array();
		$this->_arrFrmErrors = array();
		
		// Get form POST data
		$this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
		
	}
	
	/*
	* Begin form processing 
	*/
	private function _handleForm() {
		
		/*
		* Set form values 
		*/
		$this->_setFrmValues();
		
		/*
		* Validatate form values 
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
	* Set form ravlues as new link, editing or submitted link
	*/
	private function _setFrmValues() {
		
		if($this->_arrFrmPost !== null) {
			$this->_setFrmSaved();
		} else if($this->_getLink() !== null) {
			$this->_setFrmEdit();
		} else {
			$this->_setFrmCreate();
		}
		
	}
	
	/*
	* Set form values as submitted 
	*/
	private function _setFrmSaved() {
		
		/*
		* Set form values 
		*/
		foreach($this->_getFrmFields() as $strField) {
			$this->_arrFrmValues[$strField] = isset($this->_arrFrmPost[$strField])?$this->_arrFrmPost[$strField]:'';
		}
		
		/*
		* Set DAO datasource arguments 
		*/
		$this->_arrFrmValues['datasource_dao_args'] = isset($this->_arrFrmPost['datasource_dao_args'])?$this->_arrFrmPost['datasource_dao_args']:null;
		
		/*
	 	* Set modules config values --------------------------------------------------------------------------
	 	*/
		$arrModuleConfig = !empty($this->_arrFrmValues['target_module'])?$this->_objMCP->getModConfig($this->_arrFrmValues['target_module']):null;
		
		/*
		* transfer config values to form values
		*/
		if($arrModuleConfig !== null) {
			foreach(array_keys($arrModuleConfig) as $strField) {
				$this->_arrFrmValues['module_config'][$strField] = !isset($this->_arrFrmPost['module_config'],$this->_arrFrmPost['module_config'][$strField])?isset($arrModuleConfig[$strField]['default'])?$arrModuleConfig[$strField]['default']:'':$this->_arrFrmPost['module_config'][$strField];
			}
		}
		
		/*
		* Add module args 
		*/
		$arrModArgs = isset($this->_arrFrmPost['target_module_args'])?$this->_arrFrmPost['target_module_args']:array('');
		
		foreach($arrModArgs as $strArg) {
			$this->_arrFrmValues['target_module_args'][] = $strArg;
			if(strlen($strArg) == 0) break;
		}
		
	}
	
	/*
	* Set form values from current link 
	*/
	private function _setFrmEdit() {
		
		/*
		* Get current link data 
		*/
		$arrLink = $this->_getLink();
		
		/*
		* Set form values 
		*/
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				
				case 'parent_id':
					$this->_arrFrmValues[$strField] = "{$arrLink['parent_type']}-{$arrLink['parent_id']}";
					break;
				
				default:
					$this->_arrFrmValues[$strField] = $arrLink[$strField];
			}
		}
		
		/*
	 	* Set modules config values --------------------------------------------------------------------------
	 	*/
		$arrModuleConfig = !empty($this->_arrFrmValues['target_module'])?$this->_objMCP->getModConfig($this->_arrFrmValues['target_module']):null;
		
		/*
		* Unserialize links module configuration 
		* Unserialize links dao module arguments
		*/
		$arrLinkConfig = $arrLink['target_module_config']?unserialize(base64_decode($arrLink['target_module_config'])):array();
		$arrDAOArgs = $arrLink['datasource_dao_args']?unserialize(base64_decode($arrLink['datasource_dao_args'])):array('','','','','');
		
		/*
		* transfer config values to form values
		*/
		if($arrModuleConfig !== null) {
			foreach(array_keys($arrModuleConfig) as $strField) {
				$this->_arrFrmValues['module_config'][$strField] = !isset($arrLinkConfig[$strField])?isset($arrModuleConfig[$strField]['default'])?$arrModuleConfig[$strField]['default']:'':$arrLinkConfig[$strField];
			}
		}
		
		/*
		* Transfer datasource dao arguments 
		*/
		$this->_arrFrmValues['datasource_dao_args'] = $arrDAOArgs;
		
		/*
		* Add module arguments 
		*/
		$arrModuleArgs = $arrLink['target_module_args'] === null?array(''):unserialize(base64_decode($arrLink['target_module_args']));
		
		foreach($arrModuleArgs as $strArg) {
			$this->_arrFrmValues['target_module_args'][] = $strArg;
			if(strlen($strArg) == 0) break;
		}
		
	}
	
	/*
	* Set form values as create new link 
	*/
	private function _setFrmCreate() {
		
		$arrConfig = $this->_getFrmConfig();
		
		/*
		* Set form values 
		*/
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				
				case 'parent_id':
					$this->_arrFrmValues[$strField] = $this->_intParentId !== null?"{$this->_strParentType}-{$this->_intParentId}":'';
					break;
				
				default:
					$this->_arrFrmValues[$strField] = isset($arrConfig[$strField]['default'])?$arrConfig[$strField]['default']:'';
			}
		}
		
		/*
		* Add inputs for five DAO arguments and three module arguments
		*/
		$this->_arrFrmValues['datasource_dao_args'] = array('','','','','');
		$this->_arrFrmValues['target_module_args'] = array('');
		
	}
	
	/*
	* Save form data to database 
	*/
	private function _frmSave() {
		
		$arrSave = array();
		
		/*
		* Set empty values as null
		*/
		foreach($this->_arrFrmValues as $strField=>$strValue) {
			/*
			* Module config assigned directly and remaped to true db column
			*/
			if(strcmp('module_config',$strField) == 0) {
				$arrSave["target_$strField"] = $strValue;
			} else if(strcmp('datasource_dao_args',$strField) == 0) {
				$arrSave[$strField] = $strValue;
			} else if(strcmp('target_module_args',$strField) == 0) {
				
				foreach($strValue as $strArg) {
					if(strlen($strArg) == 0) break;
					$arrSave[$strField][] = $strArg;
				}
				
				if(!isset($arrSave[$strField])) $arrSave[$strField] = null;
				
			} else {
				$arrSave[$strField] = strlen($strValue) == 0?null:$strValue;
			}
		}
		
		/*
		* Get current link 
		*/
		$arrLink = $this->_getLink();
		
		/*
		* Split parent id into parent id (left) and parent type (right)
		*/
		list($arrSave['parent_type'],$arrSave['parent_id']) = explode('-',$arrSave['parent_id'],2);
		
		/*
		* Format data for dubalicate key update or insert 
		*/
		if($arrLink !== null && $this->_intDataSourcesRowId !== null) {
			$arrSave['datasources_id'] = $arrLink['navigation_links_id'];
			$arrSave['datasources_row_id'] = $this->_intDataSourcesRowId;
			
			// unset datasource identifier
			unset($arrSave['datasource_query'],$arrSave['datasource_dao'],$arrSave['datasource_dao_method'],$arrSave['datasource_dao_args']);
		} else if($arrLink !== null) {
			$arrSave['navigation_links_id'] = $arrLink['navigation_links_id'];
		} else {			
			$arrSave['creators_id'] = $this->_objMCP->getUsersId();
			$arrSave['sites_id'] = $this->_objMCP->getSitesId()?$this->_objMCP->getSitesId():null;	
		}
		
		// Add sort order for new link or link that changed parent
		if($arrLink === null || strcmp("{$arrLink['parent_type']}-{$arrLink['parent_id']}","{$arrSave['parent_type']}-{$arrSave['parent_id']}") != 0) {
			$arrSave['sort_order'] = count($this->_objDAONavigation->fetchMenu($arrSave['parent_id'],$arrSave['parent_type']));
		}
		
		/*
		* Save link to database 
		*/
		$this->_objDAONavigation->saveLink($arrSave);
		
		/*
		* fire navigation link update 
		*/
		$this->_objMCP->fire($this,'NAVIGATION_LINK_UPDATE');
		
	}
	
	/*
	* Get form name
	* 
	* @return str form name
	*/
	private function _getFrmName() {
		return 'frmNavigationLink';
	}
	
	/*
	* Get form configuration 
	* 
	* @return array form config
	*/
	private function _getFrmConfig() {
		/*
		* Get base form configuration from MCP 
		*/
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
	* Get current navigation item link data
	* 
	* @return array current navigation item link data
	*/
	private function _getLink() {
		return $this->_arrLink;
	}
	
	/*
	* Set current navigation item link 
	* 
	* @param array navigation item link data
	*/
	private function _setLink($arrLink) {
		$this->_arrLink = $arrLink;
	}
	
	/*
	* Get targets drop down
	* 
	* @return aray target drop down
	*/
	private function _getTargetWindows() {
		
		$arrTargets = array('values'=>array(),'output'=>array(),'selected'=>$this->_arrFrmValues['target_window']);
		
		foreach($this->_objDAONavigation->fetchLinksTargetWindows() as $strTarget) {
			$arrTargets['values'][] = $strTarget;
			$arrTargets['output'][] = $strTarget;
		}
		
		return $arrTargets;
		
	}
	
	/*
	* Get content types drop down
	* 
	* @param str content field
	* @return array content types drop down
	*/
	private function _getContentTypes($strField) {
		$arrReturn = array('values'=>array(),'output'=>array(),'selected'=>$this->_arrFrmValues[$strField]);
		
		/*
		* Fetch content types from Navigation DAO 
		*/
		$arrTypes = $this->_objDAONavigation->fetchLinksContentTypes($strField);
		foreach($arrTypes as $strType) {
			$arrReturn['values'][] = $strType;
			$arrReturn['output'][] = $strType;
		}
		
		return $arrReturn;
		
	}
	
	public function execute($arrArgs) {
		
		// login access required to create links
		if(!$this->_objMCP->getUsersId()) return;
		
		// link to edit
		$intLinkId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		// dynamic link without placholder yet
		if(!empty($arrArgs) && strpos($arrArgs[0],'-') !== false) {
			$arrPieces = explode('-',array_shift($arrArgs));
			$intLinkId = $arrPieces[0];
			$this->_intDataSourcesRowId = $arrPieces[1];
		}
		
		// parent of new link either nav or link
		$this->_strParentType = !empty($arrArgs) && in_array($arrArgs[0],array('Link','Nav'))?strtolower(array_shift($arrArgs)):null;
		
		// parent id of new link
		$this->_intParentId = $this->_strParentType !== null && !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		// set curret link or parent
		if($intLinkId !== null) {
			
			// fetch current link data
			$arrLink = $this->_objDAONavigation->fetchLinkById($intLinkId);
			
			if($arrLink['datasources_row_id'] !== null) {
				$arrLink = $this->_objDAONavigation->fetchDynamicLinkById($arrLink['datasources_id'],$arrLink['datasources_row_id']);
			}
			
			// set the current link
			if($arrLink !== null) {
				$this->_setLink($arrLink);
			}
			
		}
		
		/*
		* Check permissions 
		* Can user add/ edit navigation link - based on menu?
		* - Users may be resricted to editing or adding links belonging to specific menu
		*/
		/*$perm = $this->_objMCP->getPermission('MCP_NAVIGATION_LINK',$intLinkId,array(
			'parent_id'=>$this->_intParentId
			,'parent_type'=>$this->_strParentType
		));
		if(!$perm->allowed()) {
			throw new MCPPermissionException($perm);
		}*/
		
		/*
		* Handle form data 
		*/
		$this->_handleForm();
		
		/*if($this->_arrFrmValues['target_module']) {
			echo '<pre>',print_r($this->_objMCP->getModConfig($this->_arrFrmValues['target_module'])),'</pre>';
		}*/
		
		/*
		* Assign template data 
		*/
		$this->_arrTemplateData['action'] = $this->getBasePath();
		$this->_arrTemplateData['method'] = 'POST';
		$this->_arrTemplateData['name'] = $this->_getFrmName();
		$this->_arrTemplateData['config'] = $this->_getFrmConfig();
		$this->_arrTemplateData['values'] = $this->_arrFrmValues;
		$this->_arrTemplateData['errors'] = $this->_arrFrmErrors;
		$this->_arrTemplateData['target_windows'] = $this->_getTargetWindows();
		$this->_arrTemplateData['header_content_types'] = $this->_getContentTypes('header_content_type');
		$this->_arrTemplateData['body_content_types'] = $this->_getContentTypes('body_content_type');
		$this->_arrTemplateData['footer_content_types'] = $this->_getContentTypes('footer_content_type');
		
		/*
		* Load target module config 
		*/
		$arrLink = $this->_getLink();
		$this->_arrTemplateData['module_config'] = $arrLink !== null && $arrLink['target_module']?$this->_objMCP->getModConfig($arrLink['target_module']):null;
		
		/*
		* On template load execute and assign target module config data 
		*/
		$this->_objMCP->subscribe(
			$this
			,'TEMPLATE_LOAD'
			,array($this,'onTemplateLoad')
		);
		
		return 'Link/Link.php';
	}
	
	public function onTemplateLoad() {
		
		/*
		* unsubscribe event handler 
		*/
		$this->_objMCP->unsubscribe(
			$this
			,'TEMPLATE_LOAD'
			,array($this,'onTemplateLoad')
		);
		
		/*
		* Module config template 
		*/
		$this->_arrTemplateData['MODULE_CONFIG_TPL'] = $this->_objMCP->fetch($this->getTemplatePath().'/ModuleConfig.php',$this);
		
		/*
		* reassign template data 
		*/
		$this->_objMCP->assign($this->getName(),$this->_arrTemplateData);
		
	}
	
	/*
	* Override base path to append link reference for edit
	* 
	* @return str base path
	*/
	public function getBasePath() {
		
		$strPath = parent::getBasePath();
		$arrLink = $this->_getLink();
		
		if($arrLink !== null /*&& !($this->_objParentModule instanceof RouteRouter)*/) {
			$strPath.= "/{$arrLink['navigation_links_id']}";
			
			// add datasource row identifier
			if($this->_intDataSourcesRowId !== null) {
				$strPath.= "-{$this->_intDataSourcesRowId}";
			}
			
		}
		
		return $strPath;
	}
	
	/*
	* get all menus for site.
	* 
	* @return array sites menus
	*/
	private function _getMenus() {
		
		$arrMenus = array();
		
		// get all navs for site
		$strWhere = sprintf('n.sites_id = %s',$this->_objMCP->escapeString($this->_objMCP->getSitesId()));
		
		// fetch all navs
		$arrNavs = $this->_objDAONavigation->listAllNavs('n.*',$strWhere);
		
		// fetch link hierarchy for nav
		foreach($arrNavs as $arrNav) {
			$arrMenus[] = array(
				'nav'=>$arrNav
				,'children'=>$this->_objDAONavigation->fetchMenu($arrNav['navigation_id'])
			);
		}
		
		return $arrMenus;
		
	}
	
	/*
	* Due to recursive nature of menu this method
	* is called from within the template to print
	* the menu.
	* 
	* @param str parent id [nav-2,link-2] used to check parent
	* @return str parent fieldset
	*/
	public function printParentFieldset($strParentId) {
		
		$strOut = '';
		
		// get all menus for site
		$arrMenus = $this->_getMenus();
		
		// for every menu print link hierarchy
		$strOut.= '<ul>';
		foreach($arrMenus as $arrMenu) {
			$strOut.= sprintf(
				'<li><input type="radio" name="%s[parent_id]" value="nav-%u" id="link-parent-nav-%2$u"%s><label for="link-parent-nav-%2$u">%s</label>
					%s
				</li>'
				,$this->_getFrmName()
				,$arrMenu['nav']['navigation_id']
				,strcmp("nav-{$arrMenu['nav']['navigation_id']}",$strParentId) == 0?' checked="checked"':''
				,htmlentities($arrMenu['nav']['menu_title'])
				,empty($arrMenu['children'])?'':$this->_printMenu($arrMenu['children'],$strParentId)
			);
		}
		$strOut.= '</ul>';
		
		return $strOut;
		
	}
	
	/*
	* Print menu hierarchy as undorderd list 
	* 
	* @param array navigation link
	* @param str parent id [link-1,nav-3] used to select parent
	* @param int runner specifying depth level
	* @param bool used to disable current link and its children
	* @return str HTML ordered list or printed children
	*/
	private function _printMenu($arrLinks,$strParentId,$intRunner=0,$boolDisable=false) {
		
		/*
		* Get current link 
		*/
		$arrCurrent = $this->_getLink();
		
		$strReturn = '<ul>';
		foreach($arrLinks as $arrLink) {
			
			/*
			* Link may not be its own parent or use a child as parent
			*/
			$boolDisableLink = $boolDisable === false?$arrCurrent !== null && $arrCurrent['navigation_links_id'] == $arrLink['navigation_links_id']?true:false:true;
			
			$strReturn.= 
			sprintf(
				'<li>
				     <input type="radio" name="%s[parent_id]" value="link-%u" id="navigation-link-%2$u"%s%s>
				     <label for="link-parent-link-%2$u">%s</label>
				  %s'
				,$this->_getFrmName()
				,$arrLink['navigation_links_id']
				,strcmp("link-{$arrLink['navigation_links_id']}",$strParentId) == 0?' checked="checked"':''
				,$boolDisableLink === true?' disabled="disabled"':''
				,htmlentities($arrLink['link_title'])
				,empty($arrLink['navigation_links'])?'</li>':$this->_printMenu($arrLink['navigation_links'],$strParentId,($intRunner+1),$boolDisableLink).'</li>'
			);
			
			unset($boolDisableLink);
			
		}
		$strReturn.= '</ul>';
		return $strReturn;
		
	}
	
}
?>