<?php
class MCPPermissionFormRoleUsers extends MCPModule {
    
    protected
    
    /*
    * Permissions data access method 
    */
    $_objDAOPerm
    
    /*
    * User data access layer 
    */
    ,$_objDAOuser
    
    /*
    * Form validation 
    */
    ,$_objValidator
    
    /*
    * Current page 
    */
    ,$_intPage = 1
      
    /*
    * Form post values 
    */
    ,$_arrFrmValues;
    
    public function __construct(MCP $objMCP,MCPPermissionFormRole $objParentModule,$arrConfig=null) {
        parent::__construct($objMCP,$objParentModule,$arrConfig);
        
        if($objParentModule->getRole() === null) {
            throw new Exception('Module requires parent module with role.');
        }
        
        $this->_init();
    }
    
    protected function _init() {
        
        // Get permissios data access instance
        $this->_objDAOPerm = $this->_objMCP->getInstance('Component.Permission.DAO.DAOPermission',array($this->_objMCP));
        
        // Get user data access layer
        $this->_objDAOUser = $this->_objMCP->getInstance('Component.User.DAO.DAOUser',array($this->_objMCP));
        
        // Get form validator
        $this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
        
        // reste form errors
        $this->_arrFrmErrors = array();
        
        // Set-up extra validation values
        $this->_addValidationRules();
        
    }
    
    /*
    * Add extra validation rules. 
    */
    protected function _addValidationRules() {
        
        $dao = $this->_objDAOUser;
        $role = $this->_objParentModule->getRole();
        $mcp = $this->_objMCP;
        
        /*
        * make sure user is within site. Users may only be added to roles
        * which belong to the same site. 
        */
        $this->_objValidator->addRule('username',function($strValue,$strLabel) use ($dao,$mcp,$role) {
            
            /*
            * Find user
            */
            $arrUsers = $dao->listAll(
                 'users_id'
                ,"username = '".$mcp->escapeString(trim($strValue))."' and sites_id = ".((int) $mcp->getSitesId())." and deleted = 0"
            ); 
            
            /*
            * When user does not exist error
            */
            if(empty($arrUsers)) {
                return 'Unable to add user to role because user does not exist.';
            } else {
                return '';
            }
            
        });
        
    }
    
    /*
    * Main form processing handler. 
    */
    protected function _process() {
        
        if($this->_objMCP->getPost($this->_getFrmAddName()) !== null) {
            $this->_arrFrmValues = $this->_objMCP->getPost($this->_getFrmAddName());
            $this->_processAdd();
        } else if($this->_objMCP->getPost($this->_getFrmListName()) !== null) {
            $this->_arrFrmValues = $this->_objMCP->getPost($this->_getFrmListName());
            $this->_processList();
        }
        
    }
    
    /*
    * Add form processing handler. 
    */
    protected function _processAdd() {
        
        $arrErrors = $this->_objValidator->validate($this->_getFrmConfig(),$this->_arrFrmValues);
        
        /*
        * When errors exist add them to top rather than inline. 
        */
        if(!empty($arrErrors)) {
            
            foreach($arrErrors as $strError) {
                $this->_objMCP->addSystemErrorMessage($strError);
            }
         
        /*
        * otherwise when username exists locate user id and add to current role. 
        */
        } else if(isset($this->_arrFrmValues['username']) && !empty($this->_arrFrmValues['username'])) {
            
            $strUsername = $this->_arrFrmValues['username'];
            $arrRole = $this->_objParentModule->getRole();
            
            /*
            * Add user to role 
            */
            try {
                $this->_objDAOPerm->assignRoleToUsersByName($arrRole['roles_id'],array($strUsername));
                $this->_objMCP->addSystemStatusMessage('User has been assigned to role.');
            } catch(MCPDAOException $e) {
                $this->_objMCP->addSystemErrorMessage($e->getMessage());
            }
            
        }
        
    }
    
    /*
    * List form processing handler. 
    */
    protected function _processList() {
        
        // Get user ids
        $arrUsers = isset($this->_arrFrmValues['items'])?$this->_arrFrmValues['items']:array();
        
        // Get role
        $arrRole = $this->_objParentModule->getRole();
        
        // remove users
        if(!empty($arrUsers)) {
            try {
                $this->_objDAOPerm->removeRoleFromUsers($arrRole['roles_id'],$arrUsers);
                $this->_objMCP->addSystemStatusMessage('Users successfully removed from role.');
            } catch(MCPDAOException $e) {
                $this->_objMCP->addSystemErrorMessage($e->getMessage());
            }
        }
        
    }
    
    /*
    * Get name of form to add new user to role.
    *
    * @return str form name   
    */
    protected function _getFrmAddName() {
        return 'frmRoleUsersAdd';
    }
    
    /*
    * Get name of form to remove users from role.
    * 
    * @return str form name  
    */
    protected function _getFrmListName() {
        return 'frmRoleUsersList';
    }
    
    protected function _getFrmConfig() {
        
        return array(
            'username'=>array(
                'label'=>'UserName',
                'widget'=>'autocomplete',
                'type'=>'username',
                'service'=>array(
                    'pkg'=>'/service.php/Component.Permission.Module.Form.Role.Users.PermissionFormRoleUsersService'
                )
            )
        );
        
    }
    
    /*
    * Get header config definition for building table to display 
    * users assigned to role.
    * 
    * @return array   
    */
    protected function _getHeaders() {
        
        $mcp = $this->_objMCP;
        $name = $this->_getFrmListName();
        
        return array(
            array(
                 'label'=>'ID'
                ,'column'=>'users_id'
                ,'mutation'=>null
            )
            ,array(
                 'label'=>'Username'
                ,'column'=>'username'
                ,'mutation'=>null
            )
            ,array(
                 'label'=>'Email'
                ,'column'=>'email_address'
                ,'mutation'=>null
            )
            ,array(
                 'label'=>'Remove'
                ,'column'=>'users_id'
                ,'mutation'=>function($val,$row,$header) use ($mcp,$name) {
                    return $mcp->ui('Common.Form.Checkbox',array(
                         'name'=>$name.'[items][]'
                        ,'id'=>'role-users-list-item-'.$val
                        ,'value'=>$val
                        ,'checked'=>false
                    ));
                }
            )
        );
        
    }
    
    /*
    * Assign users that belong to role as template data.
    * 
    * @param int offset
    * @param int limit   
    */
    public function paginate($intOffset,$intLimit) {
        
        /*
        * Get role 
        */
        $arrRole = $this->_objParentModule->getRole();
        
        /*
        * Get users assigned to te role.
        */
        $arrData = $this->_objDAOPerm->listUsersByRole($arrRole['roles_id'],array('limit'=>"$intOffset,$intLimit"));
        
        /*
        * Assign data 
        */
        $this->_arrTemplateData['users'] = $arrData[0];
        
        /*
        * Return number of of founds rows. 
        */
        return $arrData[1];
        
        
    }
    
    public function execute($arrArgs) {
        
        $intLimit = 25;
        
        // Get page
        $this->_intPage = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):1;
        
        // handle form(s)
        $this->_process();
        
        // paginate module
        $this->_arrTemplateData['PAGINATION_TPL'] = $this->_objMCP->executeComponent('Component.Util.Module.Pagination',array($intLimit,$this->_intPage),'Component.Util.Template',array($this));
        
        // shared
        $this->_arrTemplateData['action'] = $this->getBasePath();
        $this->_arrTemplateData['method'] = 'post';
        
        // add user to form
        $this->_arrTemplateData['config'] = $this->_getFrmConfig();
        $this->_arrTemplateData['frmadd_name'] = $this->_getFrmAddName();
        $this->_arrTemplateData['frmadd_legend'] = 'Add User';
        $this->_arrTemplateData['frmadd_errors'] = array();
        
        // table that displays users assigned to roles
        $this->_arrTemplateData['headers'] = $this->_getHeaders();
        $this->_arrTemplateData['frmlist_name'] = $this->_getFrmListName();
        $this->_arrTemplateData['frmlist_legend'] = 'Roles Users';
        
        return 'Users/Users.php';
    }
    
    public function getBasePath() {
        $strBasePath = parent::getBasePath();
        
        if($this->_intPage !== null) {
            $strBasePath.= '/'.$this->_intPage;
        }
        
        return $strBasePath;
        
    }
    
}
