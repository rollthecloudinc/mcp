<?php

/*
* Permission data acess layer 
*/
class MCPDAOPermission extends MCPDAO {
    
        const
    
        // Permission table item type and item id field names
        ITEM_TYPE = 'item_type',
        ITEM_ID = 'item_id',
        
        // User and role contextual references
        CONTEXT_USER = 'user',
        CONTEXT_ROLE = 'role',
        
        // user and role permission table names 
        TBL_PERMISSIONS_USERS = 'MCP_PERMISSIONS_USERS',
        TBL_PERMISSIONS_ROLES = 'MCP_PERMISSIONS_ROLES',
        
        // Permission, User and Role primary keys
        PERMISSIONS_ID = 'permissions_id',
        USERS_ID = 'users_id',
        ROLES_ID = 'roles_id';
    
        protected
    
        /*
        * Permission fields shared accross user and role permission tables.
        */
        $_arrPermFields = array(
            'add',
            'add_own',
            'read',
            'read_own',
            'delete',
            'delete_own',
            'edit',
            'edit_own',
            'add_child',
            'add_own_child',
            'read_child',
            'read_own_child',
            'delete_child',
            'delete_own_child',
            'edit_child',
            'edit_own_child'
        );
	
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
        * Get users assigned to role.
        * 
        * @param int roles id
        * @param mix array extra options
        * @return array    
        */
        public function listUsersByRole($intRolesId,$arrOptions=array()) {
            
            $strLimit = isset($arrOptions['limit'])?$arrOptions['limit']:null;
            
            $strSQL =
               'SELECT
                      %s
                      u.*
                  FROM
                      MCP_USERS u
                 INNER
                  JOIN
                      MCP_USERS_ROLES u2r
                    ON
                      u.users_id = u2r.users_id
                 WHERE
                      u2r.roles_id = :roles_id
                   AND
                      u.deleted = 0
                      %s';         
            
            $arrUsers = $this->_objMCP->query(
                sprintf(
                     $strSQL
                    ,$strLimit !== null?'SQL_CALC_FOUND_ROWS':''
                    ,$strLimit !== null?'LIMIT '.$strLimit:''
                )
                ,array(
                    ':roles_id'=> (int) $intRolesId
                )
            );
            
            if($strLimit === null) {
                return $arrUsers;
            }
            
            return array(
                $arrUsers
                ,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
            );
            
            
        }
        
        /*
        * Get plugin definitions for a three tier architecture.
        */
        public function fetchPlugins() {
            
            $arrPlugins = array();
            
            /*
            * Get all permission plugins
            */
            foreach($this->_objMCP->getPermissionPlugins() as $arrPlugin) {
                
            }
            
            
            
            
        }
        
        /*
        * Fetch role by id
        * 
        * @param int id
        * @return array role data  
        */
        public function fetchRoleById($intId) {
            
            $arrRole = array_pop($this->_objMCP->query(
                'SELECT * FROM MCP_ROLES WHERE roles_id = :roles_id'
                ,array(
                    ':roles_id'=>(int) $intId
                )
            ));
            
            /*
            * Add indirect permission data 
            */
            if($arrRole !== null) {
                
                $arrAdmin = $this->fetchRolePermissions($intId,'MCP_ROUTE:Admin/*',array(0));
                $arrCmp = $this->fetchRolePermissions($intId,'MCP_ROUTE:Component/*',array(0));
                $arrPlt = $this->fetchRolePermissions($intId,'MCP_ROUTE:PlatForm/*',array(0));
                
                $arrRole['access_admin'] = $arrAdmin[0]['read'];
                $arrRole['access_cmp_backdoor'] = $arrCmp[0]['read'];
                $arrRole['access_plt_backdoor'] = $arrPlt[0]['read'];
                
            }
            
            return $arrRole;
            
        }
        
        /*
        * Get entity permissions for given user.
        *  
        * @param int users id 
        * @param str item type 
        * @param mix[] ids
        * @return array permissions 
        */
        public function fetchUserPermissions($intId,$strItemType,$arrItemId) {
            return $this->_fetchPermissions(self::CONTEXT_ROLE,$intId,$strItemType,$arrItemId); 
        }
        
        /*
        * Get entity permissions for given role.
        *  
        * @param int roles id 
        * @param str item type
        * @param mix[] ids  
        * @return array permissions 
        */
        public function fetchRolePermissions($intId,$strItemType,$arrItemId) {           
            return $this->_fetchPermissions(self::CONTEXT_ROLE,$intId,$strItemType,$arrItemId);          
        }
        
        /*
        * Save role data
        * 
        * @param array role data  
        * @return int new row id/affected rows 
        */
        public function saveRole($arrRole) {
            
            /*
            * Permission data that can be saved indirectly through
            * a role. 
            */
            $arrPerm = array();
            
            // parse out any permissions
            foreach(array('access_admin','access_cmp_backdoor','access_plt_backdoor') as $strPermField) {
                if(isset($arrRole[$strPermField])) {
                    
                    $arrPermItem = null;
                    
                    switch($strPermField) {
                        case 'access_admin':
                            $arrPermItem = array('item_type'=>'MCP_ROUTE:Admin/*','item_id'=>0,'read'=>null);
                            break;
                        
                        case 'access_cmp_backdoor':
                            $arrPermItem = array('item_type'=>'MCP_ROUTE:Component/*','item_id'=>0,'read'=>null);
                            break;
                        
                        case 'access_plt_backdoor':
                            $arrPermItem = array('item_type'=>'MCP_ROUTE:PlatForm/*','item_id'=>0,'read'=>null);
                            break;
                        
                        default:
                    }
                    
                    if($arrPermItem !== null) {
                        
                        if(strcmp($arrRole[$strPermField],'1') === 0) {
                            $arrPermItem['read'] = 1;
                        } else if(strcmp($arrRole[$strPermField],'0') === 0) {
                            $arrPermItem['read'] = 0;
                        } else {
                            $arrPermItem['read'] = null;
                        }
                        
                        // trigger update regardless of null - delete is ignored for routes anyway
                        $arrPermItem['delete'] = 0;
                        
                        $arrPerm[] = $arrPermItem;
                        
                    }
                    
                    // not saved to role table
                    unset($arrRole[$strPermField]);
                }
            }
            
            // var_dump($arrPerm);
            
            try {
                
                // begin new transaction
                $this->_objMCP->begin();
                
                // save data to db
                $intId = $this->_save(
                     $arrRole
                    ,'MCP_ROLES'
                    ,'roles_id'
                    , array('human_name','system_name','description','pkg')
                    ,'created_on_timestamp'
                    , null
                    , array('pkg')
		); 
                
                // commit transaction
                $this->_objMCP->commit();
                
                // save roles indirect permission settings
                if(!empty($arrPerm)) {
                    $this->saveRolePermissions((isset($arrRole['roles_id'])?$arrRole['roles_id']:$intId),$arrPerm);
                }
                
                // return new row id or affected row number based on insert or update
                return $intId;
                
            } catch(MCPDBException $e) {
                
                // rollback transaction
                $this->_objMCP->rollback();
                
                // throw more refined exception
                throw new MCPDAOException($e->getMessage());
                
            }
            
        }
        
        /*
        * Save user permissions
        *
        * @param int users id
        * @param array permissions    
        */
        public function saveUserPermissions($intId,$arrPerms) {
            return $this->_savePermissions(self::CONTEXT_USER,$intId,$arrPerms);
        }
        
        /*
        * Save role permissions 
        * 
        * @param int roles id 
        * @param array permissions 
        */
        public function saveRolePermissions($intId,$arrPerms) {
            return $this->_savePermissions(self::CONTEXT_ROLE,$intId,$arrPerms);
        }
        
        /*
        * Assign a role to users. 
        * 
        * @param int roles id
        * @param array usernames[]
        */
        public function assignRoleToUsersByName($intRole,$arrUsers) {
            
            $arrBind = array((int) $intRole);
            $arrValues = array();
            
            foreach($arrUsers as $strUsername) {
                $arrBind[] = (string) $strUsername;
                $arrValues[] = '?';
            }
            
            /*
            */
            $strSQL = '
                INSERT INTO MCP_USERS_ROLES (roles_id,users_id)
                  SELECT
                        r.roles_id
                       ,u.users_id
                   FROM
                       MCP_ROLES r
                  INNER 
                   JOIN
                       MCP_USERS u
                     ON
                       r.sites_id = u.sites_id
                   LEFT OUTER
                   JOIN
                       MCP_USERS_ROLES u2r
                     ON
                       u.users_id = u2r.users_id
                    AND
                       r.roles_id = u2r.roles_id
                  WHERE
                       r.roles_id = ?
                    AND
                       r.deleted = 0
                    AND
                       u.deleted = 0
                    AND
                       u.username IN ('.implode(',',$arrValues).')
                    AND
                       u2r.roles_id IS NULL';
            
            
            try {
                $this->_objMCP->begin();
                $this->_objMCP->query($strSQL,$arrBind);
                $this->_objMCP->commit();
            } catch(Exception $e) {
                throw new Exception('Error occurred assigning users to role.');
            }
            
            
        }
        
        /*
        * Remove role from given users
        * 
        * @param int roles id
        * @param array user id[]   
        */
        public function removeRoleFromUsers($intRole,$arrUsers) {
            
            $arrBind = array((int) $intRole);
            $arrValues = array();
            
            foreach($arrUsers as $mixUsersId) {
               $arrBind[] = (int) $mixUsersId;
               $arrValues[] = '?';
            }
            
            $strSQL = 'DELETE FROM MCP_USERS_ROLES WHERE roles_id = ? AND users_id IN ('.implode(',',$arrValues).')';
            
            try {
                $this->_objMCP->begin();
                $this->_objMCP->query($strSQL,$arrBind);
                $this->_objMCP->commit();
            } catch(Exception $e) {
                throw new Exception('Error ocurred removing users from role.');
            }
            
        }
        
        /*
        * Generic method to fetch permissions for both user and roles.
        * 
        * @param str context [self::CONTEXT_USER,self::CONTEXT_ROLE]
        * @param int id user=users_id and role=roles_id
        * @param str item type 
        * @param mix[] item ids  
        * @return array permissions 
        */
        protected function _fetchPermissions($strContext,$intId,$strItemType,$arrItemId) {
            
            switch($strContext) {
                case self::CONTEXT_USER:
                    $strTable = self::TBL_PERMISSIONS_USERS;
                    $strContextId = self::USERS_ID;
                    break;
                
                case self::CONTEXT_ROLE:
                    $strTable = self::TBL_PERMISSIONS_ROLES;
                    $strContextId = self::ROLES_ID;
                    break;
                
                default:
                    throw new MCPDAOException('First argument for DAOPermission::_fetchPermissions must be one of: '.self::CONTEXT_USER.' or '.self::CONTEXT_ROLE.'.');
            }

            $arrBind = array();
            $strSQL = 'SELECT * FROM '.$strTable.' WHERE '.$strContextId.' = ? AND '.self::ITEM_TYPE.' = ? and '.self::ITEM_ID.' IN ('.implode(',',array_fill(0,count($arrItemId),'?')).')';
            
            $arrBind[] = (int) $intId;
            $arrBind[] = (string) $strItemType;
            
            foreach($arrItemId as $mixItemId) {
                $arrBind[] = (int) $mixItemId;
            }
            
            $arrResult = $this->_objMCP->query($strSQL,$arrBind);
            
            $arrPerms = array();
            foreach($arrResult as $arrData) {
                $arrPerms[$arrData[self::ITEM_ID]] = $arrData;
            }
            
            // create placeholders with defaults for permissions that have not been defined
            foreach($arrItemId as $mixItemId) {
                if(!isset($arrPerms[$mixItemId])) {
                    $arrPerms[$mixItemId] = $this->_getDefaultPermission($strContext,$intId,$strItemType,$mixItemId);
                }
            }
            
            return $arrPerms;
            
        }
        
        /*
        * Create a placeholder permssion for the given item type and item id 
        * 
        * @param str context [user,role]
        * @param int context id user=users_id role=roles_id
        * @param str item type
        * @param str item id
        * @return array placeholder permission   
        */
        protected function _getDefaultPermission($strContext,$intId,$strItemType,$intItemId) {
            
            switch($strContext) {
                case self::CONTEXT_USER:
                    $strContextId = self::USERS_ID;
                    break;
                
                case self::CONTEXT_ROLE:
                    $strContextId = self::ROLES_ID;
                    break;
                
                default:
                    throw new MCPDAOException('First argument for DAOPermission::_getDefaultPermission must be one of: '.self::CONTEXT_USER.' or '.self::CONTEXT_ROLE.'.');
            }
            
            $arrPerm = array();
            $arrPerm[self::PERMISSIONS_ID] = null;
            $arrPerm[$strContextId] = (int) $intId;
            $arrPerm[self::ITEM_TYPE] = $strItemType;
            $arrPerm[self::ITEM_ID] = $intItemId;
            
            foreach($this->_arrPermFields as $strField) {
                $arrPerm[$strField] = null;
            }
            
            return $arrPerm;
            
        }
        
        /*
        * Save permissions
        * 
        * @param str context [user,role]  
        * @param int id role=roles_id user=users_id
        * @param array permissions
        */
        protected function _savePermissions($strContext,$intId,$arrPerms) {
           
            switch($strContext) {
                case self::CONTEXT_USER:
                    $strTable = self::TBL_PERMISSIONS_USERS;
                    $strContextId = self::USERS_ID;
                    break;
                
                case self::CONTEXT_ROLE:
                    $strTable = self::TBL_PERMISSIONS_ROLES;
                    $strContextId = self::ROLES_ID;
                    break;
                
                default:
                    throw new MCPDAOException('First argument for DAOPermission::_savePermissions must be one of: '.self::CONTEXT_USER.' or '.self::CONTEXT_ROLE.'.');
            }
            
            /*
            * Every permission MUST have a item_type and item_id defined. Unlike
            * many other entities the primary key will not be used for the update. Instead
            * the unique key will be used.   
            */
            foreach($arrPerms as $arrPerm) {
                if(!isset($arrPerm[self::ITEM_TYPE]) || !isset($arrPerm[self::ITEM_ID])) {
                    throw new MCPDAOException('Saving permissions requires an item type and item id be defined for every permission being saved.');
                }
            }
            
            /*
            * Omit permissions which all fields are null.
            */
            $arrData = array();
            foreach($arrPerms as $arrPerm) {
                foreach($this->_arrPermFields as $strField) {
                    if(array_key_exists($strField,$arrPerm) && strlen($arrPerm[$strField]) === 1) {
                        $arrData[] = $arrPerm;
                        break;
                    }
                }
            }
            //$this->debug($arrPerms);
            
            /*
            * Otherwise procede to build insert statement. 
            */
            $arrInsert = array();
            $arrBind = array();
            $arrUpdate = array();
            
            // Create duplicate key update fields
            foreach($this->_arrPermFields as $strField) {
                $arrUpdate[] = ' `'.$strField.'` = VALUES(`'.$strField.'`) ';
            }
            
            // create placeholders for each item
            for($i=0;$i<count($arrData);$i++) {
                $arrInsert[] = '('.implode(',',array_fill(0,count($this->_arrPermFields) + 3,'?')).')';
            }
            
            // Get data
            foreach($arrData as $arrPerm) {
                $arrBind[] = (int) $intId;
                $arrBind[] = (string) $arrPerm[self::ITEM_TYPE];
                $arrBind[] = (int) $arrPerm[self::ITEM_ID];
                
                foreach($this->_arrPermFields as $strField) {
                    $arrBind[] = isset($arrPerm[$strField]) && strlen($arrPerm[$strField]) !== 0?$arrPerm[$strField]:null;
                }
        
            }
            
            // Build insert/update statement
            $strSQL = 'INSERT INTO '.$strTable.' (`'.$strContextId.'`,`item_type`,`item_id`,`'.implode('`,`',$this->_arrPermFields).'`) VALUES '.implode(',',$arrInsert).' ON DUPLICATE KEY UPDATE '.implode(',',$arrUpdate);
            
            
            //$this->debug($strSQL);
            //$this->debug($arrBind);
            
            
            if(!empty($arrBind)) {
                try {
                    $this->_objMCP->begin();
                    $this->_objMCP->query($strSQL,$arrBind);
                    $this->_objMCP->commit();
                } catch(MCPDBException $e) {
                    $this->_objMCP->rollback();
                    throw new MCPDAOException($e->getMessage());
                } catch(Exception $e) {
                    throw new MCPDAOException($e->getMessage());
                }
            }
            
        }
}

?>