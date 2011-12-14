<?php 

class MCPMenuFormLink extends MCPModule {
	
	protected 
	
	/*
	* Validation object 
	*/
	$_objValidator
	
	/*
	* Menu data access object 
	*/
	,$_objDAOMenu
	
	/*
	* Proxy in form configuration (build once only) 
	*/
	,$_arrCachedFrmConfig
	
	/*
	* Row form post data 
	*/
	,$_arrFrmPost
	
	/*
	* Actuall values used to build the form 
	*/
	,$_arrFrmValues
	
	/*
	* Any field level errors for form 
	*/
	,$_arrFrmErrors
	
	/*
	* Data of menu link that is being edited
	*/
	,$_arrMenuLink
	
	/*
	* Menu to display links for 
	*/
	,$_arrMenu
	
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
	
	protected function _init() {
		
		// Get menu DAO
		$this->_objDAOMenu = $this->_objMCP->getInstance('Component.Menu.DAO.DAOMenu',array($this->_objMCP));
		
		// Get validator
		$this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
		
		// Assign the form post data to local var
		$this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
		
		// reset form values and errors
		$this->_arrFrmValues = array();
		$this->_arrFrmErrors = array();
		
		$this->_addCustomValidationRules();
                
                /*
                * Sponge any serialized data fields. This essentially removes any empty strings. Necessary
                * so that items that have been deleted using the delete button are ignored.
                */
                if($this->_arrFrmPost !== null) {
                    foreach(array('mod_args','datasource_args') as $strField) {
                        if(isset($this->_arrFrmPost[$strField]) && is_array($this->_arrFrmPost[$strField])) {
                            $this->_arrFrmPost[$strField] = $this->_spongeSerialized($this->_arrFrmPost[$strField]);
                        }
                    }
                }
		
	}
	
	/*
	* Add custom, module specific validation routines 
	*/
	protected function _addCustomValidationRules() {
		
		// $this->_objDAOValidator->addRule('pkg',);
		
	}
	
	/*
	* Handle the form processing 
	*/
	protected function _frmHandle() {
		
		// set form values
		$this->_setFrmValues();
	
		// Validatate form values 
		if($this->_arrFrmPost !== null) {
                    
			$this->_arrFrmErrors = $this->_objValidator->validate($this->_getFrmConfig(),$this->_arrFrmValues);
                        
                        /*
                        * Validate the target module condiguarion separately. 
                        */
                        if(isset($this->_arrFrmValues['mod_cfg'])) {
                            $arrTargetModErrors = $this->_objValidator->validate($this->_getFrmTargetModConfig(),$this->_arrFrmValues['mod_cfg']);
                            if(!empty($arrTargetModErrors)) {
                                $this->_arrFrmErrors['mod_cfg'] = $arrTargetModErrors;
                            }
                        }
                        
		}
		
		// Save form data to database 
		if($this->_arrFrmPost !== null && empty($this->_arrFrmErrors)) {
			$this->_frmSave();
		}
		
	}
	
	/*
	* Set form values as new link, editing or submitted link
	*/
	protected function _setFrmValues() {
		
		if($this->_arrFrmPost !== null) {
			$this->_setFrmSaved();
                        $this->_setFrmTargetModSaved();
		} else if($this->_getMenuLink() !== null) {
			$this->_setFrmEdit();
                        $this->_setFrmTargetModEdit();
		} else {
			$this->_setFrmCreate();
		}
		
	}
	
	/*
	* Handle form submission 
	*/
	protected function _setFrmSaved() {
		
		foreach( $this->_getFrmFields() as $strField ) {
			
			$this->_arrFrmValues[$strField] = isset($this->_arrFrmPost[$strField])?$this->_arrFrmPost[$strField]:'';
			
		}
		
	}
        
	/*
	* Handle form submission for target module configuration.
	*/
	protected function _setFrmTargetModSaved() {
		
		foreach( $this->_getFrmTargetModFields() as $strField ) {
			
			$this->_arrFrmValues['mod_cfg'][$strField] = isset($this->_arrFrmPost['mod_cfg'],$this->_arrFrmPost['mod_cfg'][$strField])?$this->_arrFrmPost['mod_cfg'][$strField]:'';
			
		}
		
	}
	
	/*
	* Handle inital edit request 
	*/
	protected function _setFrmEdit() {
		
		$arrLink = $this->_getMenuLink();
		
		foreach( $this->_getFrmFields() as $strField ) {
			
			switch($strField) {
				
				case 'parent_id':
					
					if($arrLink['parent_id'] === null) {
						$this->_arrFrmValues[$strField] = "menu-{$arrLink['menus_id']}";
					} else {
						$this->_arrFrmValues[$strField] = $arrLink['parent_id'];
					}
					
					break;
				
				default:
					$this->_arrFrmValues[$strField] = $arrLink[$strField] === null?'':$arrLink[$strField];
			}
			
		}
		
	}
        
        /*
        * Set edit for target module. 
        */
        protected function _setFrmTargetModEdit() {
            
            $arrLink = $this->_getMenuLink();
            
            /*
            * Target module configuration 
            */
            foreach($this->_getFrmTargetModFields() as $strField) {
                $this->_arrFrmValues['mod_cfg'][$strField] = isset($arrLink['mod_cfg'],$arrLink['mod_cfg'][$strField])?$arrLink['mod_cfg'][$strField]:'';
            }
            
        }
	
	/*
	* handle request to create a new link 
	*/
	protected function _setFrmCreate() {
		
		foreach($this->_getFrmFields() as $strField) {
			
			switch($strField) {
				
				case 'parent_id':
					
					if( strpos($this->_strParentType,'Menu') !== false ) {
						$this->_arrFrmValues[$strField] = "menu-{$this->_intParentId}";
					} else {
						$this->_arrFrmValues[$strField] = "$this->_intParentId";
					}
					
					continue;
			
				default:
					$this->_arrFrmValues[$strField] = '';
			
			}
		}
		
	}
        
        /*
        * Given a numerical array will remove all elements that are an empty string
        * and rebuld the array. This is necessary for serialized arrays because when
        * items are deleted they will still exists in the post array. This is correct
        * considering deleted fields need to exist so that the fields values can be removed from
        * the db. However, in the case of serialized arguments this is not true since everytime
        * an item is saved the entire serialized array will be replaced with the new values.
        *
        * @param array array of values
        * @return array   
        */
        protected function _spongeSerialized($arrValues) {
            
            $arrNew = array();
            
            foreach($arrValues as $mixValue) {
                if(strcmp($mixValue,'') !== 0) {
                    $arrNew[] = $mixValue;
                }
            }
            
            return $arrNew;
            
        }
	
	protected function _frmSave() {
		
		$arrLink = $this->_getMenuLink();
		
		$arrSave = $this->_arrFrmValues;
                
            
                //$this->_objMCP->debug($arrSave);
                //return;
		
		/*
		* Foreign key reference to menu that link belongs to. 
		*/
		$arrMenu = $this->_getMenu();
		$arrSave['menus_id'] = $arrMenu['menus_id'];
		
		/*
		* Links located at the root of the menu will have a parent id
		* of menu-{pk}. The parent ID will actually be null for links
		* located at the root.
		*/
		if(!is_numeric($arrSave['parent_id'])) {
			$arrSave['parent_id'] = '';
		}
		
		/*
		* Presence of menu link primary key triggers update 
		*/
		if($arrLink !== null) {
			$arrSave['menu_links_id'] = $arrLink['menu_links_id'];
		} else {
			$arrSave['creators_id'] = $this->_objMCP->getUsersId();
		}
		
		
		/*
		* Save link to database 
		*/
		try {
			
			$this->_objDAOMenu->saveLink($arrSave);
			
			/*
			* Fire update event using this as the target
			*/
			$this->_objMCP->fire($this,'LINK_UPDATE');
		
			/*
			* Add success message 
			*/
			$this->_objMCP->addSystemStatusMessage( $this->_getSaveSuccessMessage() );
			
		} catch(MCPDAOException $e) {
			
			$this->_objMCP->addSystemErrorMessage(
				$this->_getSaveErrorMessage()
				,$e->getMessage()
			);
			
			return false;
			
		}
		
		return true;
		
	}
	
	/*
	* Message to be shown to user upon sucessful save of menu link
	* 
	* @return str message
	*/
	protected function _getSaveSuccessMessage() {
		return 'Link '.($this->_getMenuLink() !== null?'Updated':'Created' ).'!';
	}

	/*
	* Message to be shown to user when error occurs saving of menu link
	* 
	* @return str message
	*/
	protected function _getSaveErrorMessage() {
		return 'An internal issue has prevented the link from being '.($this->_getMenuLink() !== null?'updated':'created' );
	}
	
	/*
	* Get menu link form config
	* 
	* @return array menu link form config
	*/
	protected function _getFrmConfig() {
		
		/*
		* Only need to build configuration for the form once. Once it
		* has been built use the proxy version. 
		*/
		if( $this->_arrCachedFrmConfig !== null ) {
			return $this->_arrCachedFrmConfig;
		}
		
		$this->_arrCachedFrmConfig = $this->_objMCP->getFrmConfig($this->getPkg());
		
		// Load values for assigning parent
		$arrMenu = $this->_getMenu();
		
		//echo '<pre>',print_r($arrMenu),'</pre>';
		
		/*$arrLinks = $this->_objDAOMenu->fetchMenu($arrMenu['menus_id'],null,true,false,array(
			 'select'=>'l.menu_links_id value,l.display_title label,l.menu_links_id,l.menus_id'
			,'child_key'=>'values'
		));*/
		
		$arrLinks = $this->_objDAOMenu->fetchMenu($arrMenu['menus_id'],array(
			 'select'=>'l.menu_links_id value,l.display_title label'
			,'child_key'=>'values'
		));
		
		// echo '<pre>',print_r($arrLinks),'</pre>';
		
		$this->_arrCachedFrmConfig['parent_id']['values'][] = array(
			 'label'=>$arrMenu['menu_title']
			,'value'=>"menu-{$arrMenu['menus_id']}"
			,'values'=>$arrLinks
		);
		
		return $this->_arrCachedFrmConfig;
		
	}
        
        /*
        * Get configuration form options for target module
        * that the link points to if it points to a module. 
        * 
        * @return array target module configuration 
        */
        protected function _getFrmTargetModConfig() {
            
            $arrLink = $this->_getMenuLink();
            
            if($arrLink !== null && isset($arrLink['target']) && strcasecmp('module',$arrLink['target']) === 0 ) {
                return $this->_objMCP->getModConfig($arrLink['mod_path']);
            } else {
                return array();
            }
            
        }
	
	/*
	* Get menu link form fields
	* 
	* @return array menu link form fields
	*/
	protected function _getFrmFields() {
		return array_keys($this->_getFrmConfig());
	}
        
        /*
        * Get target module configuration form fields.
        * 
        * @return array module configuration fields
        */
        protected function _getFrmTargetModFields() {
            return array_keys($this->_getFrmTargetModConfig());
        }
	
	/*
	* get the form name
	* 
	* @param array menu link form name
	*/
	protected function _getFrmName() {
		return 'frmMenuLink';
	}
	
	/*
	* When editing a existing menu link get its data
	* 
	* @return array menu link data
	*/
	protected function _getMenuLink() {
		return $this->_arrMenuLink;
	}
	
	/*
	* Set link data when editing existing menu link 
	* 
	* @param array menu link data
	*/
	protected function _setMenuLink($arrMenuLink) {
		$this->_arrMenuLink = $arrMenuLink;
	}
	
	/*
	* Get menu to displat link for
	* 
	* @return array menu data
	*/
	protected function _getMenu() {
		return $this->_arrMenu;
	}
	
	/*
	* Set menu to display links for 
	* 
	* @param array menu data
	*/
	protected function _setMenu($arrMenu) {
		$this->_arrMenu = $arrMenu;
	}
        
        /*
        * Add in target module configuration. 
        */
        protected function _addTargetModTplVars() {
            
            $arrLink = $this->_getMenuLink();
            $arrFields = $this->_getFrmTargetModFields();
            
            if($arrLink === null || empty($arrFields)) {
                return;
            } 
            
            /*
            * Module configuation will be presented as a nested form. 
            */
            $this->_arrTemplateData['mod_form'] = array();
            $this->_arrTemplateData['mod_form']['legend'] = 'Module Config';
            $this->_arrTemplateData['mod_form']['nested'] = true;
            $this->_arrTemplateData['mod_form']['name'] = $this->_getFrmName().'[mod_cfg]';
            $this->_arrTemplateData['mod_form']['config'] = $this->_getFrmTargetModConfig();
            $this->_arrTemplateData['mod_form']['values'] = isset($this->_arrFrmValues['mod_cfg'])?$this->_arrFrmValues['mod_cfg']:array();
            $this->_arrTemplateData['mod_form']['errors'] = isset($this->_arrFrmErrors['mod_cfg'])?$this->_arrFrmErrors['mod_cfg']:array();
            
        }
	
	public function execute($arrArgs) {
		
		/*
		* The link id will be one of the following:
		* - numeric integer - represents physical link
		* - virtual link that has not been made into a concrete link ex. 2-3,Name-4 where [bundle_id]-[datasources_id] 
		* - NULL - new physical link (datasource of normal link)
		*/
		$mixLinkId = !empty($arrArgs) && ( is_numeric($arrArgs[0]) || strpos($arrArgs[0],'-') !== false )?array_shift($arrArgs):null;
		
		/*
		* When editing a concrete or virtual link locate the data for the link 
		*/
		if( $mixLinkId !== null ) {
			$this->_setMenuLink( $this->_objDAOMenu->fetchLinkById($mixLinkId) );
		}
		
		/*
		* When creating a new link require the menu that the link will be added to be passed
		* as a argument. This is needed to determine the proper menu to display to select
		* a parent for the brand new link. 
		*/
		if( $mixLinkId === null ) {
			
			// parent of new link either nav or link
			$this->_strParentType = !empty($arrArgs) && in_array($arrArgs[0],array('Link','Menu'))?array_shift($arrArgs):null;
		
			// parent id of new link
			$this->_intParentId = $this->_strParentType !== null && !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
			$intMenuId = null;
			
			if( strpos($this->_strParentType,'Menu') !== false) {
				$intMenuId = $this->_intParentId;
			} else {
				$arrLink = $this->_objDAOMenu->fetchLinkById($this->_intParentId);
				$intMenuId = $arrLink['menus_id'];
			}
			
			$this->_setMenu( $this->_objDAOMenu->fetchMenuById($intMenuId) );

		/*
		* Use menu link is assigned to 
		*/
		} else {
			
			$arrLink = $this->_getMenuLink();	
			$this->_setMenu( $this->_objDAOMenu->fetchMenuById($arrLink['menus_id']) );
			
		}
		
		/*
		* Check user permissions 
		*/
		if($this->_getMenuLink() !== null) {
			$perm = $this->_objMCP->getPermission(MCP::EDIT,'MenuLink',$mixLinkId);
		} else {
			$arrMenu = $this->_getMenu();
			$perm = $this->_objMCP->getPermission(MCP::ADD,'MenuLink',$arrMenu['menus_id']);
		}
		
		// echo '<pre>',print_r($perm),'</pre>';
		
		if(!$perm['allow']) {
			throw new MCPPermissionException($perm);
		}
		
		// $arrMenu =  $this->_getMenu();
		//$this->_objDAOMenu->fetchMenuImproved($arrMenu['menus_id']);
		
		// handle form processing
		$this->_frmHandle();
		
		// assign template data
		$this->_arrTemplateData['action'] = $this->getBasePath();
		$this->_arrTemplateData['method'] = 'POST';
		$this->_arrTemplateData['name'] = $this->_getFrmName();
		$this->_arrTemplateData['config'] = $this->_getFrmConfig();
		$this->_arrTemplateData['values'] = $this->_arrFrmValues;
		$this->_arrTemplateData['errors'] = $this->_arrFrmErrors;
		$this->_arrTemplateData['legend'] = 'Link';
		$this->_arrTemplateData['layout'] = ROOT.'/Component/menu/Template/Form/Link/Layout.php';
                
                // Load nested module config form template variables
                $this->_addTargetModTplVars();
                
		return 'Link/Link.php';
	}
	
	public function getBasePath() {
		
		$strBasePath = parent::getBasePath();
		
		$arrLink = $this->_getMenuLink();
		
		if( $arrLink === null ) {
			$strBasePath.= "/{$this->_strParentType}/{$this->_intParentId}";
		} else {
			$strBasePath.= "/{$arrLink['menu_links_id']}";
		}
		
		return $strBasePath;
		
	}
	
}

?>