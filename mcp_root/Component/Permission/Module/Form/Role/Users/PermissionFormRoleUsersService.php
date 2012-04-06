<?php
$this->import('App.Core.Service');

/*
* Service is used to populate auto complete for selecting users
* to assign to role. 
*/
class MCPPermissionFormRoleUsersService extends MCPResource implements MCPService {
    
    public function checkPerms() {   
        
        /*
        * Get page arguments
        */
        $arrArgs = $this->_objMCP->getArgs();
        
        /*
        * Role id should be second argument
        */
        $intRoleId = isset($arrArgs[0])?$arrArgs[0]:null;
        
        if($intRoleId === null) {
            return array('allow'=>false);
        }
        
        /*
        * Check permissions 
        */
        return $this->_objMCP->getPermission(MCP::EDIT,'Role',$intRoleId);
        
    }
    
    public function exec() {
        
        $strUserName = $this->_objMCP->getGet('term');
        
        /*
        * Service requires user name to be specified 
        */
        if(!$strUserName) {
            return;
        }
        
        /*
        * Get user data access object
        */
        $objDAOUser = $this->_objMCP->getInstance('Component.User.DAO.DAOUser',array($this->_objMCP));
        
        /*
        * Get all users 
        */
        $arrUsers = $this->_objMCP->query(
            'SELECT username label,username value,users_id id FROM MCP_USERS WHERE sites_id = :sites_id AND deleted = 0 AND username LIKE :username'
            ,array(
                 ':sites_id'=>(int) $this->_objMCP->getSitesId()
                ,':username'=>$strUserName.'%'
            )
        );
        
        return $arrUsers;
        
    }
    
}