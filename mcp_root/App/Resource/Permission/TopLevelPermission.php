<?php
$this->import('App.Core.DAO');
$this->import('App.Core.Permission');

abstract class MCPTopLevelPermission extends MCPDAO implements MCPPermission {
	
	/*
	* To level items are things like: vocabulary, Navigation, Node Type, Role, etc - anything
	* that does not have a virtual parent. For example, Terms have a virtual parent of a Vocabulary
	* so terms will not use this method to create a SQL permission statement. The same is true with
	* navigation links and nodes. Nodes have a virtual parent of node type and navigation links
	* have a virtual parent of navigation.
	* 
	* This method exists because most if not all top level items use the same logic to
	* derive permissions. I am getting wick of copying and pasting the same SQL so
	* this method can now be used to create the statement for any entity were it is needed
	* without essentially replicating the same SQL statement and tweaking a few things.
	* 
	* - note this uses variable binding. YOU MUST PASS :users_id and :entity_type from the callee
	* when executing the query. This method does not execute a query just provides a template
	* to do so. Template in this sense means a SQL statement ready to be executed w/ provided
	* placeholders for dynamic pieces such as users ID, entity type and messages.
	* 
	* @return str SQL template
	*
	* placeholders:
	* - :users_id
	* - :entity_type
	* - :deny_add_msg_dev
	* - :deny_add_msg_user
	* 
	*/
	protected function _getCreateSQLTemplate() {

		$strSQL =
			"SELECT
			       u.users_id item_id
			      ,CASE
			     
					WHEN pu.add IS NOT NULL
					THEN pu.add
			     
			     	WHEN MAX(pr.add) IS NOT NULL
			     	THEN MAX(pr.add)
			     	
			     	ELSE 0   	
			     END allow_add
			     
			     ,CASE
			     
					WHEN pu.add IS NOT NULL
					THEN 'Permission assigned directly to user prevents action.'
			     
			     	WHEN MAX(pr.add) IS NOT NULL
			     	THEN 'Permission assigned to one of users roles prevents action.'
			     	
			     	ELSE 'Not allowed to carry out action because no permissions exist.'   	
			     	
			     END deny_add_msg_dev
			     
			     ,CASE
			     
					WHEN pu.add IS NOT NULL
					THEN 'You are not allowed to carry out given action due to insufficent permissions.'
			     
			     	WHEN MAX(pr.add) IS NOT NULL
			     	THEN 'You are not allowed to carry out given action due to insufficent permissions.'
			     	
			     	ELSE 'You are not allowed to carry out given action due to insufficent permissions.'  	
			     	
			     END deny_add_msg_user
			     
			  FROM
			     MCP_USERS u
			     
			  #user permission resolution#
			  LEFT OUTER
			  JOIN
				  MCP_PERMISSIONS_USERS pu
			    ON
				  pu.item_type = :entity_type
			   AND
			      pu.item_id = 0
			   AND
				  u.users_id = pu.users_id
				  
			  # role management resolution#
			  LEFT OUTER
			  JOIN
			      MCP_USERS_ROLES u2r
			    ON
			      u.users_id = u2r.users_id
			  LEFT OUTER
			  JOIN
			      MCP_ROLES r
			    ON
			      u2r.roles_id = r.roles_id
			   AND
			      r.deleted = 0
			  LEFT OUTER
			  JOIN
			      MCP_PERMISSIONS_ROLES pr #role permissions#
			    ON
			      pr.item_type = :entity_type
			   AND
			      pr.item_id = 0
			   AND
			      r.roles_id = pr.roles_id
			 WHERE
			      u.users_id = :users_id
			 GROUP
			    BY
			      u.users_id";
		
		
		return $strSQL;
		
	}
	
	/*
	* @param str base table name 
	* @param str base table primary key column
	* @param int[] items ids
	* [@param] str base table creator column name
	* @return str SQL template
	* 
	* palceholders:
	* 
	* - :users_id
	* - :default_allow_delete
	* - :default_allow_edit
	* - :default_allow_read
	* - :item_type
	*/
	protected function _getEditSQLTemplate($strBaseTable,$strPrimaryKey,$arrIds,$strCreator='creators_id') {
		
		return 
		   "SELECT
			 	 b.{$this->_objMCP->escapeString($strPrimaryKey)} item_id #base item unique id#
			 	 
			 	 #can user delete role#
				 ,CASE 
						
					#user permission resolution (priority)#
					WHEN upe.`delete` IS NOT NULL
					THEN upe.`delete`
					
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = upe.users_id AND upe.`delete_own` IS NOT NULL
					THEN upe.`delete_own`
					
					WHEN upp.`delete` IS NOT NULL
					THEN upp.delete
					
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = upp.users_id AND upp.`delete_own` IS NOT NULL
					THEN upp.`delete_own`
					
					#role permission resolution#
					WHEN MAX(rpe.`delete`) IS NOT NULL
					THEN MAX(rpe.`delete`)
					
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = :users_id AND MAX(rpe.`delete_own`) IS NOT NULL
					THEN MAX(rpe.`delete_own`)
					
					WHEN MAX(rpp.`delete`) IS NOT NULL
					THEN MAX(rpp.`delete`)
					
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = :users_id AND MAX(rpp.`delete_own`) IS NOT NULL
					THEN MAX(rpp.`delete_own`)
					
					#by default the creator of the node is allowed to delete it#
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = :users_id
					THEN 1
					
					#by default if user has no permissions to delete deny#
					ELSE
					:default_allow_delete
						      
				END allow_delete
				
				#can the user edit role#		      
				,CASE 
						
					#user permission resolution (priority)#
					WHEN upe.`edit` IS NOT NULL
					THEN upe.`edit`
					
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = upe.users_id AND upe.`edit_own` IS NOT NULL
					THEN upe.`edit_own`
					
					WHEN upp.`edit` IS NOT NULL
					THEN upp.`edit`
					
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = upp.users_id AND upp.`edit_own` IS NOT NULL
					THEN upp.`edit_own`
					
					#role permission resolution#
					WHEN MAX(rpe.`edit`) IS NOT NULL
					THEN MAX(rpe.`edit`)
					
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = :users_id AND MAX(rpe.`edit_own`) IS NOT NULL
					THEN MAX(rpe.`edit_own`)
					
					WHEN MAX(rpp.`edit`) IS NOT NULL
					THEN MAX(rpp.`edit`)
					
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = :users_id AND MAX(rpp.`edit_own`) IS NOT NULL
					THEN MAX(rpp.`edit_own`)
						      
					#by default creator of role is allowed to edit it#
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = :users_id
					THEN 1
						 
					#deny edit for everyone else#
					ELSE
					:default_allow_edit
						      
				END allow_edit	
				
				#can the user read role#		      
				,CASE 
						
					#user permission resolution (priority)#
					WHEN upe.`read` IS NOT NULL
					THEN upe.`read`
					
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = upe.users_id AND upe.`read_own` IS NOT NULL
					THEN upe.`read_own`
					
					WHEN upp.`read` IS NOT NULL
					THEN upp.`read`
					
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = upp.users_id AND upp.`read_own` IS NOT NULL
					THEN upp.`read_own`
					
					#role permission resolution#
					WHEN MAX(rpe.`read`) IS NOT NULL
					THEN MAX(rpe.`read`)
					
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = :users_id AND MAX(rpe.`read_own`) IS NOT NULL
					THEN MAX(rpe.`read_own`)
					
					WHEN MAX(rpp.`read`) IS NOT NULL
					THEN MAX(rpp.`read`)
					
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = :users_id AND MAX(rpp.`read_own`) IS NOT NULL
					THEN MAX(rpp.`read_own`)
					
					#by default author may read role#
					WHEN b.`{$this->_objMCP->escapeString($strCreator)}` = :users_id
					THEN 1
						
					#by default restrict read#
					ELSE
					:default_allow_read
						      
				END allow_read
			FROM
			   {$this->_objMCP->escapeString($strBaseTable)} b #base entity table#
			LEFT OUTER
			JOIN 
			   MCP_PERMISSIONS_USERS upe #user permission entity#
			  ON
			   b.{$this->_objMCP->escapeString($strPrimaryKey)} = upe.item_id
			 AND
			   upe.users_id = :users_id
			 AND
			   upe.item_type = :item_type
			LEFT OUTER
			JOIN
			   MCP_PERMISSIONS_USERS upp #user parent permission entity#
			  ON
			   upp.item_id = 0
			 AND
			   upp.users_id = :users_id
			 AND
			    upp.item_type = :item_type
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
			    b.{$this->_objMCP->escapeString($strPrimaryKey)} = rpe.item_id
			  AND
			    rpe.item_type = :item_type
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
			    rpp.item_type = :item_type
			  AND
			    rpp.item_id = 0
			  AND
			    r2.roles_id = rpp.roles_id
			WHERE
			    b.{$this->_objMCP->escapeString($strPrimaryKey)} IN ({$this->_objMCP->escapeString(implode(',',$arrIds))})
			GROUP 
			   BY
			    b.{$this->_objMCP->escapeString($strPrimaryKey)}";
		
	}

	protected function _rud($arrIds,$intUser=null) {
		
		$arrPerms = $this->_objMCP->query(
			 $this->_getEditSQLTemplate(
			 	 $this->_getBaseTable() // 'MCP_NAVIGATION'
			 	,$this->_getPrimaryKey() // 'navigation_id'
			 	,$arrIds,$this->_getCreator() // 'users_id'
			 )
			,array(
				 ':users_id'=>$intUser === null?0:$intUser
				,':item_type'=> $this->_getItemType() // 'MCP_NAVIGATION'
				,':default_allow_delete'=>0
				,':default_allow_edit'=>0
				,':default_allow_read'=>1
			)
		);
		
		return $arrPerms;
     
	}

	protected function _c($intUser=null) {
		
		$arrPerms = $this->_objMCP->query(
			 $this->_getCreateSQLTemplate()
			,array(
				 ':users_id'=>$intUser === null?0:$intUser
				,':entity_type'=> $this->_getItemType() // 'MCP_NAVIGATION'
				//,':deny_add_msg_dev'=>''
				//,':deny_add_msg_user'=>''
			)
		);
		
		return $arrPerms;
     
	}

	public function add($ids) {
		
		$permissions = $this->_c($this->_objMCP->getUsersId());
		
		//echo '<pre>',print_r($permissions),'</pre>';
		/*$return = array();
		
		if($permission !== null) {
			$return[] = array(
				'allow'=>(bool) $permission['allow_add']
				,'msg_dev'=>$permission['deny_add_msg_dev']
				,'msg_user'=>'You may not add %s.'
			);
		} else {
			$return[] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to create a %s'
				,'msg_user'=>'You may not add %s.'
			);
		}*/
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_add']
				,'msg_dev'=>isset($permission['deny_add_msg_dev'])?$permission['deny_add_msg_dev']:''
				,'msg_user'=>'You may not add %s.'
			);
		}
		
		//echo '<pre>',print_r($return),'</pre>';
		
		foreach(array_diff( array($this->_objMCP->getUsersId()) ,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to add specified %s'
				,'msg_user'=>'You may not add %s.'
			);
		}
		
		//echo '<pre>',print_r($return),'</pre>';
		
		return $return;
		
	}
	
	public function read($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_read']
				,'msg_dev'=>isset($permission['deny_read_msg_dev'])?$permission['deny_read_msg_dev']:''
				,'msg_user'=>'You may not see %s.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to see specified %s'
				,'msg_user'=>'You may not see %s.'
			);
		}
		
		return $return;
		
	}
	
	public function delete($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_delete']
				,'msg_dev'=>isset($permission['deny_delete_msg_dev'])?$permission['deny_delete_msg_dev']:''
				,'msg_user'=>'You may not delete %s.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to delete specified %s'
				,'msg_user'=>'You may not delete %s.'
			);
		}
		
		return $return;
		
	}
	
	public function edit($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_edit']
				,'msg_dev'=>isset($permission['deny_edit_msg_dev'])?$permission['deny_edit_msg_dev']:''
				,'msg_user'=>'You may not edit %s.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to edit specified %s'
				,'msg_user'=>'You may not edit %s.'
			);
		}
		
		return $return;
		
	}
	
	abstract protected function _getBaseTable(); // @return string
	abstract protected function _getPrimaryKey(); // @return string
	abstract protected function _getCreator(); // @return string
	abstract protected function _getItemType(); // @return string

}
?>