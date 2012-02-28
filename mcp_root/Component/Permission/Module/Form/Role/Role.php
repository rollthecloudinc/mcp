<?php
class MCPPermissionFormRole extends MCPModule {
    
    const
    
     TAB_GENERAL    = 'General'
    ,TAB_USERS      = 'Users'
    ,TAB_PERMS      = 'Perms';       
    
    protected
    
    /*
    * Validation helper 
    */
    $_objValidator
    
    /*
    * Permission data access object 
    */
    ,$_objDAOPerm
    
    /*
    * Form post array 
    */
    ,$_arrFrmPost
           
    /*
    * Form values 
    */
    ,$_arrFrmValues
    
    /*
    * Validation errors 
    */
    ,$_arrFrmErrors
    
    /*
    * Current tab 
    */
    ,$_strTab
    
    /*
    * Current role 
    */
    ,$_arrRole
    
    /*
    * Cached form configuration 
    */
    ,$arrFrmConfig;
    
    public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
        parent::__construct($objMCP,$objParentModule,$arrConfig);
        $this->_init();
    }
    
    protected function _init() {
        
        // Get validation object
        $this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
        
        // get permission DAO
        $this->_objDAOPerm = $this->_objMCP->getInstance('Component.Permission.DAO.DAOPermission',array($this->_objMCP));
        
        // Get post data
        $this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
        
        // Set values and errors
        $this->_arrFrmValues = array();
        $this->_arrFrmErrors = array();
        
        // Add custom validation rules
        $this->_addCustomValidationRules();
        
    }
    
    /*
    * Define custom validation rules specific to this form only. 
    */
    protected function _addCustomValidationRules() {
        
        $dao = $this->_objDAOPerm;
        $values =& $this->_arrFrmValues;
        $role = $this->_getRole();
        $mcp = $this->_objMCP;
        $mod = $this;
        
        // system name
        $this->_objValidator->addRule('role_system_name',function($value,$label) use ($dao,&$values,$mod,$mcp) {
            
            $role = $mod->getRole();
            
            /*
            * Check system name conforms to standard convention 
            */
            if(!preg_match('/^[a-z0-9_]*?$/',$value)) {
		return "$label may only contain numbers, underscores and lower alphabetic characters.";
            }
            
            /*
            * Build filter to see if role already exists 
            */
            $strFilter = sprintf(
                "r.deleted = 0 AND r.sites_id = %s AND r.system_name = '%s' AND r.pkg %s %s"
                ,$mcp->escapeString($mcp->getSitesId())
		,$mcp->escapeString($value)
			
		// edit edge case
		,$role !== null?" AND r.roles_id <> {$mcp->escapeString($role['roles_id'])}":''
		,empty($values['pkg'])?"= ''":"= '{$mcp->escapeString($values['pkg'])}'"
            );
                
            /*
            * Check site, system name and pkg uniqueness 
            */
            if(array_pop($dao->listRoles('r.roles_id',$strFilter)) !== null) {
                return "$label $value already exists".(empty($values['pkg'])?'.':" for package {$values['pkg']}.");
            }
            
            return '';
        });
        
        // human name
        $this->_objValidator->addRule('role_human_name',function($value,$label) use ($dao,&$values,$mod,$mcp) {
            
            $role = $mod->getRole();
            
            /*
            * Build filter to see if role already exists 
            */
            $strFilter = sprintf(
		"r.deleted = 0 AND r.sites_id = %s AND r.human_name = '%s' AND r.pkg %s %s"
		,$mcp->escapeString($mcp->getSitesId())
		,$mcp->escapeString($value)
			
		// edit edge case
		,$role !== null?" AND r.roles_id <> {$mcp->escapeString($role['roles_id'])}":''
		,empty($values['pkg'])?"= ''":"= '{$mcp->escapeString($values['pkg'])}'"
            );
		
            /*
            * Check site, system name and pkg uniqueness 
            */
            if(array_pop($dao->listRoles('r.roles_id',$strFilter)) !== null) {
                return "$label $value already exists".(empty($values['pkg'])?'.':" for package {$values['pkg']}.");
            }
		
            return '';
            
        });
        
    }
    
    /*
    * Entry point to process form. 
    */
    protected function _process() {
        
        // Set form values
        $this->_setFrmValues();
        
        // validate form
        if($this->_arrFrmPost !== null) {
            $this->_arrFrmErrors = $this->_objValidator->validate($this->_getFrmConfig(),$this->_arrFrmValues);
        }
        
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
        } else if($this->_getRole() !== null) {
            $this->_setFrmEdit();
        } else {
            $this->_setFrmEdit();
        }
    }
    
    /*
    * Handle POST request 
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
    * Handle edit form 
    */
    protected function _setFrmEdit() {
        
        $arrRole = $this->_getRole();
        
        foreach($this->_getFrmFields() as $strField) {
            switch($strField) {
                default:
                    $this->_arrFrmValues[$strField] = isset($arrRole[$strField])?$arrRole[$strField]:'';
            }
        }
        
    }
    
    /*
    * Handle create new role 
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
    * Save role data to database. 
    */
    protected function _frmSave() {
        
        // copy values
        $arrValues = $this->_arrFrmValues;
        
        // Get role
        $arrRole = $this->_getRole();
        
        if($arrRole !== null) {
            $arrValues['roles_id'] = $arrRole['roles_id'];// pk triggers update
        } else {
            $arrValues['creators_id'] = (int) $this->_objMCP->getUsersId();
            $arrValues['sites_id'] = (int) $this->_objMCP->getSitesId();
        }
        
        // save
        try {
            
            // save role
            $intId = $this->_objDAOPerm->saveRole($arrValues);
            
            // Add success message
            $this->_objMCP->addSystemStatusMessage('Role sucessfully saved.');
            
            /*
            * Refresh role and recompile data 
            */
            if($arrRole !== null) {
                $this->_arrRole = $this->_objDAOPerm->fetchRoleById($arrRole['roles_id']);
            } else {
                $this->_arrRole = $this->_objDAOPerm->fetchRoleById($intId);
            }
                        
            $this->_arrFrmValues = array();
            $this->_setFrmEdit();
            
            
        } catch(MCPDAOException $e) {
            
            $this->_objMCP->addSystemErrorMessage('An unknown error has occurred that has prevented role from being saved.');
            
        }
        
    }
    
    /*
    * Get base name of form
    * 
    * @return str form name  
    */
    protected function _getFrmName() {
       return 'frmRole';
    }
    
    /*
    * Get form fields
    * 
    * @return arr fields  
    */
    protected function _getFrmFields() {
        return array_keys($this->_getFrmConfig());
    }
    
    /*
    * Get form configuration
    * 
    * @return array 
    */
    protected function _getFrmConfig() {
        
        if($this->_arrFrmConfig !== null) {
            return $this->_arrFrmConfig;
        }
        
        $arrConfig = array();

        switch($this->_strTab) {
            
            case self::TAB_PERMS:
                $arrConfig = $this->_objMCP->getFrmConfig('Component.Permission.Module.Form.Role','frm_perms');
                break;
            
            case self::TAB_USERS:
                $arrConfig = $this->_objMCP->getFrmConfig('Component.Permission.Module.Form.Role','frm_users');
                break;
            
            case self::TAB_GENERAL:
            default:
                $arrConfig = $this->_objMCP->getFrmConfig('Component.Permission.Module.Form.Role','frm_general');
                
        }
        
        $this->_arrFrmConfig = $arrConfig;
        return $arrConfig;
        
    }
    
    /*
    * Set current role - turns on edit mode 
    * 
    * @param array role 
    */
    protected function _setRole($arrRole) {
        $this->_arrRole = $arrRole;
    }
    
    /*
    * Get current role when in edit mode 
    * 
    * @return arr role
    */
    protected function _getRole() {
        return $this->_arrRole;
    }
    
    /*
    * Get role
    * 
    * @return array role  
    */
    public function getRole() {
        return $this->_getRole();
    }
    
    /*
    * Configuation to build tab menu 
    * 
    * @return array ui tree config 
    */
    protected function _getTabMenu() {
        $mod = $this;
        $mcp = $this->_objMCP;
        $base = parent::getBasePath();
        $tab = $this->_strTab?$this->_strTab:self::TAB_GENERAL;
        $role = $this->_getRole();
        
        return array(
             'data'=>array(
                 array('value'=>self::TAB_GENERAL)
                ,array('value'=>self::TAB_USERS)
                ,array('value'=>self::TAB_PERMS)
             )
            ,'cls'=>'tabs'
            ,'mutation'=>function($mixValue) use ($mcp,$mod,$base,$tab,$role) {
            
                switch($mixValue) {
                    case $mod::TAB_PERMS:
                        $strLabel = 'Permissions';
                        break;
                    
                    case $mod::TAB_USERS:
                        $strLabel = 'Users';
                        break;
                    
                    default:
                        $strLabel = 'General';
                }
                
                return $mcp->ui('Common.Field.Link',array(
                    'label'=>$strLabel
                    ,'url'=>$base.'/'.$mixValue.($role!==null?"/{$role['roles_id']}":'')
                    ,'class'=>strcasecmp($tab,$mixValue) === 0?'current':''
                ));
                
            }
        );
        
    }

    public function execute($arrArgs) {
        
        // tab is required
        $this->_strTab = array_shift($arrArgs);
        
        // Get current role
        $intRole = !empty($arrArgs) && is_numeric($arrArgs[0])?((int) array_shift($arrArgs)):null;
        
        // When role exists set data
        if($intRole !== null) {
            $this->_setRole($this->_objDAOPerm->fetchRoleById($intRole));
        }
        
        // Tab selection
        $strTpl = 'Role';
        $this->_arrTemplateData['TPL_REDIRECT'] = '';
        
        // Tab which displays users assigned to role
        if($this->_strTab === self::TAB_USERS && $this->_getRole() !== null) {
            
            $this->_arrTemplateData['TPL_REDIRECT'] = $this->_objMCP->executeComponent(
                'Component.Permission.Module.Form.Role.Users'
		,$arrArgs
		,null
		,array($this)
            );
            $strTpl = 'Redirect';
            
        // Tab which displays permissions assigned to role
        } else if($this->_strTab === self::TAB_PERMS && $this->_getRole() !== null) {
            
            $this->_arrTemplateData['TPL_REDIRECT'] = $this->_objMCP->executeComponent(
                'Component.Permission.Module.Form.Perm.Intro'
		,$arrArgs
		,null
		,array($this)
            );
            $strTpl = 'Redirect';
            
        // default general form details
        } else {
            
            // Process form
            $this->_process();
        
            // Set template data
            $this->_arrTemplateData['name'] = $this->_getFrmName();
            $this->_arrTemplateData['action'] = $this->getBasePath();
            $this->_arrTemplateData['method'] = 'POST';
            $this->_arrTemplateData['legend'] = 'Role Form';
            $this->_arrTemplateData['config'] = $this->_getFrmConfig();
            $this->_arrTemplateData['values'] = $this->_arrFrmValues;
            $this->_arrTemplateData['errors'] = $this->_arrFrmErrors;
            
        }
        
        // Tab configuration
        $this->_arrTemplateData['tabs'] = $this->_getTabMenu();
        
	return "Role/$strTpl.php";
    }
    
    public function getBasePath() {
        
        $strBasePath = parent::getBasePath();
        
        if($this->_strTab !== null) {
            $strBasePath.= '/'.$this->_strTab;
        }
        
        $arrRole = $this->_getRole();
        if($arrRole !== null) {
            $strBasePath.= '/'.$arrRole['roles_id'];
        }
        
        return $strBasePath;
        
    }
	
} 
?>
