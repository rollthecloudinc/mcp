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
        $this->_objValdidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
        
        // get permission DAO
        $this->_objDAOPerm = $this->_objMCP->getInstance('Component.Permission.DAO.DAOPermission',array($this->_objMCP));
        
        // Get post data
        $this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
        
        // Set values and errors
        $this->_arrFrmValues = array();
        $this->_arrFrmErrors = array();
        
    }
    
    /*
    * Entry point to process form. 
    */
    protected function _process() {
        
        // Set form values
        $this->_setFrmValues();
        
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
        
    }
    
    /*
    * Handle edit form 
    */
    protected function _setFrmEdit() {
        
        $arrRole = $this->_getRole();
        
        $this->_objMCP->debug($arrRole);
        
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
        
        // Tab configuration
        $this->_arrTemplateData['tabs'] = $this->_getTabMenu();
        
        $this->_testPerms();
        
	return 'Role/Role.php';
    }
    
    private function _testPerms() {
        
        $arrPerms = $this->_objDAOPerm->fetchUsersPermissions($this->_objMCP->getUsersId());
        
        $this->debug($arrPerms);
        
    }
    
    public function getBasePath() {
        
        $strBasePath = parent::getBasePath();
        
        if($this->_strTab !== null) {
            $strBasePath.= '/'.$this->_strTab;
        }
        
        $arrRole = $this->_getRole();
        if($arrRole !== null) {
            $strVasePath.= '/'.$arrRole['roles_id'];
        }
        
        return $strBasePath;
        
    }
	
} 
?>
