<?php
$this->import('App.Core.DAO');
$this->import('App.Core.Permission');

/*
* MCP_CONFIG 0 (all site configs)
* MCP_CONFIG 1 (full site config) - id represents the site - 0 represents all sites
* MCP_CONFIG:name_of_field - 1
* MCP_CONFIG:name_of_field - 0 
*/
class MCPPermissionConfig extends MCPDAO implements MCPPermission {
    
        /*
        * Configuration item type prefix identifier. This
        * will make it possible to identify all permissions
        * for config fields by merely looking for cfg-.
        *
        * Permissions for the master config will still use MCP_CONFIG
        * as the item type. However, individual fields will be in the form
        * of cfg-name_of_field    
        */
        const FIELD_PREFIX = 'cfg:';
        
	public function edit($ids,$intUserId=null) {
            
            return $this->_fetchAllPerms('edit',$ids,$intUserId);
		
        }
	
	public function read($ids,$intUserId=null) {
            
            return $this->_fetchAllPerms('read',$ids,$intUserId);
		
	}
        
        /*
        * It is possible to add configuation fields using fields
        * this is not something that is supported by the GUI. Though
        * is is completely supported entering the data manually into the db. 
        */
	public function add($ids,$intUserId=null) {
		
            // this will be handled by the field permission plugin - extending the config
            
	}
        
        /*
        * It will not be possible to delete configuaration fields at this time. In
        * Theory it could be supported in that one would be able to delete fields
        * that have been added to configuration as fields. Though for now I am going
        * to leave it be.  
        */
	public function delete($ids,$intUserId=null) {	
	}
        
        
        /*
        * Get a list of all available configuration fields for the current site. 
        * 
        * @param str optional prefix to supply to each field name
        * @return array fields 
        */
        protected function _getConfigFields($boolPrefix=false) {
            
            // Get all available fields
            $arrFields = array_keys($this->_objMCP->getConfigSchema());
            
            // When no prefix needs to be applied return data as is
            if($boolPrefix === false) {
                return $arrFields;
            }
            
            // Otherwise apply prefix
            $arrPrefixed = array();
            foreach($arrFields as $strField) {
                $arrPrefixed[] = self::FIELD_PREFIX.$strField;
            }
            
            return $arrPrefixed;
            
        }
        
        /*
        * Get individual field permissions
        * 
        * @param int uers id  
        */
        protected function _fetchFieldPerms($intUser=null) {
            
            $strSQL =
                "SELECT
                       SUBSTR(p.item_type,5) item_id
                      ,MAX(p.edit) allow_edit
                      ,MAX(p.read) allow_read
                      ,'User does not have user perm or perm assigned to role which user belongs to that allows edit of config field value.' deny_edit_msg_dev
                      ,'User does not have user perm or perm assigned to role which user belongs to that allows read config field value.' deny_read_msg_dev
                   FROM
                      (SELECT
                            'user_perm' type
                            ,pu.item_type
                            ,NULL has_user_perm
                            ,pu.edit
                            ,pu.read
                         FROM
                            MCP_PERMISSIONS_USERS pu
                        WHERE
                            pu.users_id = :users_id
                          AND
                            pu.item_type LIKE 'cfg:%'
                          AND
                            pu.item_id = 0
                    UNION ALL
                       SELECT
                            'role_perm'
                            ,pr.item_type
                            ,CASE
                                WHEN pu.permissions_id IS NULL
                                THEN 0

                                ELSE
                                1
                             END
                            ,CASE
                                WHEN pu.edit IS NOT NULL
                                THEN pu.edit

                                ELSE
                                MAX(pr.edit)
                             END
                            ,CASE
                                WHEN pu.read IS NOT NULL
                                THEN pu.read

                                ELSE
                                MAX(pr.read)
                             END
                         FROM
                            MCP_PERMISSIONS_ROLES pr
                        INNER
                         JOIN
                            MCP_ROLES r
                           ON
                            pr.roles_id = r.roles_id
                          AND
                            r.deleted = 0
                        INNER
                         JOIN
                            MCP_USERS_ROLES ur
                           ON
                            r.roles_id = ur.roles_id
                          AND
                            ur.users_id = :users_id
                       INNER
                        JOIN
                           MCP_PERMISSIONS_USERS pu
                          ON
                           ur.users_id = pu.users_id
                         AND
                           pr.item_id = pu.item_id
                         AND
                           pr.item_type = pu.item_type
                       WHERE
                           pr.item_type LIKE 'cfg:%'
                         AND
                           pr.item_id = 0
                       GROUP
                          BY
                           pr.item_type) p
                       WHERE
                           p.type = 'user_perm'
                          OR
                          (p.type = 'role_perm' AND p.has_user_perm = 0)
                 GROUP
                    BY
                     p.item_type";
            
            
            $arrData = $this->_objMCP->query(
                    $strSQL,
                    array(
                        ':users_id'=>((int) ($intUser === null?0:$intUser))
                    )
            );
            
            // case nulls
            foreach($arrData as &$arrRow) {
                if(strcasecmp($arrRow['allow_edit'],'null') === 0) {
                    $arrRow['allow_edit'] = null;                  
                }
                if(strcasecmp($arrRow['allow_read'],'null') === 0) {
                    $arrRow['allow_read'] = null;
                }
            }
            
            return $arrData;
            
        }
        
        /*
        * Get global configuration permissions
        * 
        * @param int users id  
        * @return array global user config permission settings 
        */
        protected function _fetchGlobalPerms($intUser=null) {
            
            $strSQL =
                "SELECT
                       p.item_id
                      ,COALESCE(MAX(p.edit),0) allow_edit
                      ,COALESCE(MAX(p.`read`),0) allow_read
                   FROM  
                (SELECT
                     'user_perm' `type`
                     ,pu.item_id
                     ,NULL has_user_perm
                     ,pu.`read`
                     ,pu.edit
                  FROM
                     MCP_PERMISSIONS_USERS pu
                 WHERE
                     pu.users_id = :users_id
                   AND
                     pu.item_type = 'MCP_CONFIG'
                   AND
                     pu.item_id = 0
                UNION ALL
                SELECT
                     'role_perm'
                     ,pr.item_id
                     ,CASE
                          WHEN pu.permissions_id IS NULL
                          THEN 0

                          ELSE
                          1
                      END
                     ,CASE
                          WHEN pu.edit IS NOT NULL
                          THEN pu.edit

                          ELSE
                          MAX(pr.edit)
                      END
                     ,CASE
                         WHEN pu.`read` IS NOT NULL
                         THEN pu.`read`

                         ELSE
                         MAX(pr.`read`)
                      END
                  FROM
                     MCP_PERMISSIONS_ROLES pr
                 INNER
                  JOIN
                     MCP_ROLES r
                    ON
                     pr.roles_id = r.roles_id
                   AND
                     r.deleted = 0
                 INNER
                  JOIN
                     MCP_USERS_ROLES ur
                    ON
                     r.roles_id = ur.roles_id
                   AND
                     ur.users_id = :users_id
                 LEFT OUTER
                 JOIN
                     MCP_PERMISSIONS_USERS pu
                   ON
                     pr.item_type = pu.item_type
                  AND
                     pr.item_id = pu.item_id
                  AND
                     ur.users_id = pu.users_id
                WHERE
                     pr.item_type = 'MCP_CONFIG'
                  AND
                     pr.item_id = 0) p
                       WHERE
                           p.`type` = 'user_perm'
                          OR
                          (p.`type` = 'role_perm' AND p.has_user_perm = 0 )
                 GROUP
                    BY
                     p.item_id";
            
            $arrData = $this->_objMCP->query(
                $strSQL
                ,array(
                    ':users_id'=>((int) ($intUser === null?0:$intUser)
                )
            ));
            
            if(empty($arrData)) {
                $arrData = array(
                    'allow_read'=>0,
                    'allow_edit'=>0
                );
            } else {
                $arrData = $arrData[0];
            }
            
            return $arrData;
            
        }
        
        /*
        * Get perms for all passed fields virtual or directly defined
        * as permissions stored in users or roles permissions tables. This
        * takes into account global configuration permission settings and
        * individual config field permission settings.  
        * 
        * @param array specific fields | null === all fields
        * @param int user to check 
        */
        protected function _fetchAllPerms($strPermName,$arrIds=null,$intUserId=null) {
            
            // Get name of all config fields
            $arrNames = $this->_getConfigFields(false);
            
            if(!empty($arrIds)) {
                $arrNames = array_intersect($arrNames,$arrIds);
            }
            
            // Fetch all field level permissions
            $arrFields = $this->_fetchFieldPerms($intUserId);
            
            // Fetch global config field settings
            $arrGlobals = $this->_fetchGlobalPerms($intUserId);
            
            // merged fields and globals
            $arrMerged = array();
           
            foreach($arrFields as $arrPerm) {
                if(in_array($arrPerm['item_id'],$arrNames)) {
                    $arrMerged[$arrPerm['item_id']] = array(
                        'allow'=> ((bool) (is_null($arrPerm["allow_$strPermName"])?$arrGlobals["allow_$strPermName"]:$arrPerm["allow_$strPermName"]))
                        ,'msg_dev'=> ''
                        ,'msg_user'=> ''
                    ); 
                }
            }
            
            foreach(array_diff($arrNames,array_keys($arrMerged)) as $id) {
                $arrMerged[$id] = array(
                    'allow'=>(bool) $arrGlobals["allow_$strPermName"]
                    ,'msg_dev'=>''
                    ,'msg_user'=>''
                );
            }
            
            return $arrMerged;
            
        }
        
	
}

?>
