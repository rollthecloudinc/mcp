<?php
// abstract base class
$this->import('App.Resource.Permission.PermissionBase');

/*
* Role permissions data access layer
*/
class MCPPermissionRole extends MCPPermissionBase {
	
	/*
	* Can user read roles (see FULL configuration)
	* 
	* @param array role ids
	* @return array permissions
	*/
	public function read($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_read']
				,'msg_dev'=>$permission['deny_read_msg_dev']
				,'msg_user'=>'You may not vire role.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to view specified role'
				,'msg_user'=>'You may not view role.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Can user create new role for the current site
	* 
	* @return array role permissions
	*/
	public function add($ids) {
		
		$permission = $this->_c($this->_objMCP->getUsersId());
		
		$return = array();
		
		if($permission !== null) {
			$return[] = array(
				'allow'=>(bool) $permission['allow_add']
				,'msg_dev'=>$permission['deny_add_msg_dev']
				,'msg_user'=>'You may not add a role.'
			);
		} else {
			$return[] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to create a role'
				,'msg_user'=>'You may not add a role.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Can user delete roles
	* 
	* @param array role ids
	* @return array permissions
	*/
	public function delete($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_delete']
				,'msg_dev'=>$permission['deny_delete_msg_dev']
				,'msg_user'=>'You may not delete role.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to delete specified role'
				,'msg_user'=>'You may not delete role.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Can user edit role ids (configure role)
	* @return array permissions 
	*/
	public function edit($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_edit']
				,'msg_dev'=>$permission['deny_edit_msg_dev']
				,'msg_user'=>'You may not configure role.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to configure specified role'
				,'msg_user'=>'You may not configure role.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user can Read, Update and/or Delete a role
	* 
	* @param array role ids
	* @param int users id (defaults to current user)
	* @return array permissions
	*/
	private function _rud($intRoleIds,$intUser=null) {
		
		/*$strSQL = sprintf(
		   "SELECT
			 	 b.roles_id item_id #base item unique id#
			 	 
			 	 #can user delete role#
				 ,CASE 
						
					#user permission resolution (priority)#
					WHEN upe.`delete` IS NOT NULL
					THEN upe.`delete`
					
					WHEN b.`creators_id` = upe.users_id AND upe.`delete_own` IS NOT NULL
					THEN upe.`delete_own`
						      	
					WHEN b.`creators_id` = upp.users_id AND upp.`delete_own_child` IS NOT NULL
					THEN upp.`delete_own_child`
						      	
					WHEN upp.`delete_child` IS NOT NULL
					THEN upp.`delete_child`
					
					#role permission resolution#
					WHEN MAX(rpe.`delete`) IS NOT NULL
					THEN MAX(rpe.`delete`)
					
					WHEN MAX(rpe.`delete_own`) IS NOT NULL
					THEN MAX(rpe.`delete_own`)
						      	
					WHEN MAX(rpp.`delete_own_child`) IS NOT NULL
					THEN MAX(rpp.`delete_own_child`)
						      	
					WHEN MAX(rpp.`delete_child`) IS NOT NULL
					THEN MAX(rpp.`delete_child`)	
					
					#by default the creator of the node is allowed to delete it#
					WHEN b.`creators_id` = :users_id
					THEN 1
					
					#by default if user has no permissions to delete deny#
					ELSE
					0
						      
				END allow_delete
				
				#can the user edit role#		      
				,CASE 
						
					#user permission resolution (priority)#
					WHEN upe.`edit` IS NOT NULL
					THEN upe.`edit`
					
					WHEN b.`creators_id` = upe.users_id AND upe.`edit_own` IS NOT NULL
					THEN upe.`edit_own`
						      	
					WHEN b.`creators_id` = upp.users_id AND upp.`edit_own_child` IS NOT NULL
					THEN upp.`edit_own_child`
						      	
					WHEN upp.`edit_child` IS NOT NULL
					THEN upp.`edit_child`
					
					#role permission resolution#
					WHEN MAX(rpe.`edit`) IS NOT NULL
					THEN MAX(rpe.`edit`)
					
					WHEN MAX(rpe.`edit_own`) IS NOT NULL
					THEN MAX(rpe.`edit_own`)
						      	
					WHEN MAX(rpp.`edit_own_child`) IS NOT NULL
					THEN MAX(rpp.`edit_own_child`)
						      	
					WHEN MAX(rpp.`edit_child`) IS NOT NULL
					THEN MAX(rpp.`edit_child`)
						      
					#by default creator of role is allowed to edit it#
					WHEN b.`creators_id` = :users_id
					THEN 1
						 
					#deny edit for everyone else#
					ELSE
					0
						      
				END allow_edit	
				
				#can the user read role#		      
				,CASE 
						
					#user permission resolution (priority)#
					WHEN upe.`read` IS NOT NULL
					THEN upe.`read`
					
					WHEN b.`creators_id` = upe.users_id AND upe.`read_own` IS NOT NULL
					THEN upe.`read_own`
						      	
					WHEN b.`creators_id` = upp.users_id AND upp.`read_own_child` IS NOT NULL
					THEN upp.`read_own_child`
						      	
					WHEN upp.`read_child` IS NOT NULL
					THEN upp.`read_child`
					
					#role permission resolution#
					WHEN MAX(rpe.`read`) IS NOT NULL
					THEN MAX(rpe.`read`)
					
					WHEN MAX(rpe.`read_own`) IS NOT NULL
					THEN MAX(rpe.`read_own`)
						      	
					WHEN MAX(rpp.`read_own_child`) IS NOT NULL
					THEN MAX(rpp.`read_own_child`)
						      	
					WHEN MAX(rpp.`read_child`) IS NOT NULL
					THEN MAX(rpp.`read_child`)
					
					#by default author may read role#
					WHEN b.`creators_id` = :users_id
					THEN 1
						
					#by default restrict read#
					ELSE
					0
						      
				END allow_read
			FROM
			   MCP_ROLES b #base entity table#
			LEFT OUTER
			JOIN 
			   MCP_PERMISSIONS_USERS upe #user permission entity#
			  ON
			   b.roles_id = upe.item_id
			 AND
			   upe.users_id = :users_id
			 AND
			   upe.item_type = 'MCP_ROLES'
			LEFT OUTER
			JOIN
			   MCP_PERMISSIONS_USERS upp #user parent permission entity#
			  ON
			   upp.item_id = 0
			 AND
			   upp.users_id = :users_id
			 AND
			    upp.item_type = 'MCP_ROLES'
			 LEFT OUTER
			 JOIN
			    MCP_USERS_ROLES u2r #roles user is assigned to#
			   ON
			    u2r.users_id = :users_id
			 LEFT OUTER
			 JOIN
			    MCP_ROLES r #roles#
			   ON
			    u2r.roles_id = r.roles_id
			  AND
			    r.deleted = 0
			 LEFT OUTER
			 JOIN
			    MCP_PERMISSIONS_ROLES rpe #role permission entity#
			   ON
			    b.roles_id = rpe.item_id
			  AND
			    rpe.item_type = 'MCP_ROLES'
			  AND
			    r.roles_id = rpe.roles_id 
			 LEFT OUTER
			 JOIN
			    MCP_USERS_ROLES u2r2 #parent role permission#
			   ON
			    u2r2.users_id = :users_id
			 LEFT OUTER
			 JOIN
			    MCP_ROLES r2 #roles - resolving parent relationship#
			   ON
			    u2r2.roles_id = r2.roles_id
			  AND
			    r2.deleted = 0
			 LEFT OUTER
			 JOIN
			    MCP_PERMISSIONS_ROLES rpp #role permission parent#
			   ON
			    rpp.item_type = 'MCP_ROLES'
			  AND
			    rpp.item_id = 0
			  AND
			    r2.roles_id = rpp.roles_id
			WHERE
			    b.roles_id IN (%s)
			GROUP 
			   BY
			    b.roles_id"
			,$this->_objMCP->escapeString(implode(',',$intRoleIds))
		);
		
		// echo '<p>',str_replace(':users_id',$intUser,$strSQL),'<p>'; exit;
		
		$arrPerms = $this->_objMCP->query(
			$strSQL
			,array(
				':users_id'=>$intUser === null?0:$intUser
			)
		);
		
		return $arrPerms;*/
		
		$arrPerms = $this->_objMCP->query(
			 $this->_getTopLevelEntityEditSQLTemplate('MCP_ROLES','roles_id',$intRoleIds,'creators_id')
			,array(
				 ':users_id'=>$intUser === null?0:$intUser
				,':item_type'=>'MCP_ROLES'
				,':default_allow_delete'=>0
				,':default_allow_edit'=>0
				,':default_allow_read'=>1
			)
		);
		
		// echo '<pre>',print_r($arrPerms),'</pre>';
		
		return $arrPerms;
		
	}
	
	/*
	* Determine whether user is allowed to create a role for the current site
	* 
	* NOTE: Role creation can be controlled using roles. It is possible
	* and a very likely case that roles will be necessary to manage role
	* creation.
	* 
	* @param int sites ids
	* @param int users id (defaults to current user)
	* @return array permissions
	*/
	private function _c($intUser=null) {
		
		$arrPerms = $this->_objMCP->query(
			 $this->_getTopLevelEntityCreateSQLTemplate()
			,array(
				 ':users_id'=>$intUser === null?0:$intUser
				,':entity_type'=>'MCP_ROLES'
				,':deny_add_msg_dev'=>''
				,':deny_add_msg_user'=>''
			)
		);
		
		return array_pop($arrPerms);
		
	}
	
} 
?>