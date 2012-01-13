<?php

/*
* Permission data acess layer 
*/
class MCPDAOPermission extends MCPDAO {
	
	/*
	* List roles
	* 
	* @param str select clause
	* @param str where clause
	* @param str orderby clause
	* @param str limit,offset
	*/
	public function listRoles($strSelect='r.*',$strFilter=null,$strSort=null,$strLimit=null) {
		
		/*
		* Build SQL 
		*/
		$strSQL = sprintf(
			'SELECT
			      %s %s
			   FROM
			      MCP_ROLES r
			      %s
			      %s
			      %s'
			,$strLimit === null?'':'SQL_CALC_FOUND_ROWS'
			,$strSelect
			,$strFilter !== null?"WHERE $strFilter":null
			,$strSort !== null?"WHERE $strSort":null
			,$strLimit !== null?"LIMIT $strLimit":''
		);
		
		/*
		* Query db 
		*/
		$arrRoles = $this->_objMCP->query($strSQL);
		
		/*
		* When without limit just return result set 
		*/
		if($strLimit === null) {
			return $arrRoles;
		}
		
		/*
		* Return bundle of data and number of total rows 
		*/
		return array(
			$arrRoles
			,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
		);
		
	}
        
        /*
        * Fetch role by id
        * 
        * @param int id
        * @return array role data  
        */
        public function fetchRoleById($intId) {
            
            return array_pop($this->_objMCP->query(
                'SELECT * FROM MCP_ROLES WHERE roles_id = :roles_id'
                ,array(
                    ':roles_id'=>(int) $intId
                )
            ));
            
        }
        
        /*
        * Get raw definition for user permission
        */
        public function fetchUserPermission($intUserId,$strItemType,$intItemId=null) {
            
            $arrPerm = array_pop($this->_objMCP->query(
                'SELECT * FROM MCP_PERMISSIONS_USERS WHERE users_id = :users_id AND item_type = :item_type AND item_id = :item_id'
                ,array(
                     ':users_id'=>$intUserId
                    ,':item_type'=>$strItemType
                    ,':item_id'=>$intItemId === null?0:((int) $intItemId)
                )
            ));
            
            if($arrPerm !== null) {
                return $arrPerm;
            }
            
            // otherwise create  placeholder with default info
            $arrPerm = array();
            
            foreach($this->_objMCP->query('DESCRIBE MCP_PERMISSIONS_USERS') as $arrField) {
                $arrPerm[$arrField['Field']] = 0;
            }
            
            $arrPerm['permissions_id'] = null;
            $arrPerm['users_id'] = $intUserId;
            $arrPerm['item_type'] = $strItemType;
            $arrPerm['item_id'] = $intItemId === null?0:$intItemId;
            
            return $arrPerm;
            
        }
        
        /*
        * Get all given users permissions (-child entities) 
        * 
        * This will probably need a alot of clean-up but we have to start some where. 
        */
        public function fetchUsersPermissions($intUserId) {
            
            $arrPerms = array();
            
            $arrIds = array();
            
            $arrAdd = array();
            $arrDelete = array();
            $arrEdit = array();
            $arrRead = array();
            
            
            // Sites ------------------------------------------------------------------
            
            $arrSites = $this->_objMCP->getInstance('Component.Site.DAO.DAOSite',array($this->_objMCP))->listAll('s.sites_id,s.site_name');
            
            foreach($arrSites as &$arrSite) {
                $arrIds[] = $arrSite['sites_id'];
            }
            
            $arrAdd = $this->_objMCP->getPermission(MCP::ADD,'Site',null,$intUserId);
            $arrEdit = $this->_objMCP->getPermission(MCP::EDIT,'Site',$arrIds,$intUserId);
            $arrDelete = $this->_objMCP->getPermission(MCP::DELETE,'Site',$arrIds,$intUserId);
            $arrRead = $this->_objMCP->getPermission(MCP::READ,'Site',$arrIds,$intUserId);
            
            // Get raw definition
            $arrPerms['site'] = $this->fetchUserPermission($intUserId,'MCP_SITES',null);
            $arrPerms['site']['allow_add'] = $arrAdd['allow'];
            
            // map user permissions for each site to raw site permission data
            foreach($arrSites as &$arrSite) {
                $arrPerms['site']['items'][$arrSite['sites_id']] = $this->fetchUserPermission($intUserId,'MCP_SITES',$arrSite['sites_id']);
                $arrPerms['site']['items'][$arrSite['sites_id']]['item_label'] = $arrSite['site_name'];
                $arrPerms['site']['items'][$arrSite['sites_id']]['allow_read'] = $arrRead[$arrSite['sites_id']]['allow'];
                $arrPerms['site']['items'][$arrSite['sites_id']]['allow_edit'] = $arrEdit[$arrSite['sites_id']]['allow'];
                $arrPerms['site']['items'][$arrSite['sites_id']]['allow_delete'] = $arrDelete[$arrSite['sites_id']]['allow'];
            }
            

            
            return $arrPerms;
            
            
        }
	
}

?>