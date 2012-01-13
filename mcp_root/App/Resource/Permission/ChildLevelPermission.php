<?php
$this->import('App.Core.DAO');
$this->import('App.Core.Permission');

abstract class MCPChildLevelPermission extends MCPDAO implements MCPPermission {
	
	/*
	* Child level create permissions will be granted based on the childs
	* context or parent. For example, a node may be created based on
	* its context. This makes it possible to allow creation of a "project"
	* and restrict creation of "blog" or anything. This allows a very granular
	* level of control over who can create what.
	*
	* 
	* @param str base table
	* @param str base table primary key column name
	* @param array contextual ids
	* @param base table creators column
	* @return str SQL statement
	* 
	* query placeholders:
	* 
	* - :item_type
	* - :users_id
	* - :default_allow_add
	* - :deny_add_msg_dev
	* - :deny_add_msg_user
	*/
	protected function _getCreateSQLTemplate($strBaseTable,$strPrimaryKey,$arrIds,$strCreator='creators_id') {
		
		$strSQL =
			"SELECT
			     b.{$this->_objMCP->escapeString($strPrimaryKey)} item_id #the generic entity id#
				 ,CASE
						      
					#user permissions have precedence over roles. Determoine whether user has explicit setting to create a node of the type#
				    WHEN pu.add_child IS NOT NULL
					THEN pu.add_child
				
					#user permissions have precedence over roles. Determine whether user has explicit setting to create node of a type they created#   
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = pu.users_id AND pu.add_own_child IS NOT NULL
					THEN pu.add_own_child
				
					#determines whether user is assigned to role that has settings for creating node of type#
					WHEN MAX(pr.add_child) IS NOT NULL
					THEN MAX(pr.add_child)
					
					#Determines whether user is assigned to role that has settings for creating node of a type that they created#
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = :users_id AND MAX(pr.add_own_child) IS NOT NULL
					THEN MAX(pr.add_own_child)
					
					#by default creator of node type can create nodes of that type#
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = :users_id
					THEN 1
						
					#When nothing has been matched deny creation of node of specified type#      	
					ELSE
					:default_allow_add
						      
					END allow_add

			      #same thing as top but copied to determine case matched#
				 ,CASE
						      
					#user permissions have precedence over roles. Determoine whether user has explicit setting to create a node of the type#
				    WHEN pu.add_child IS NOT NULL
					THEN 1
				
					#user permissions have precedence over roles. Determine whether user has explicit setting to create node of a type they created#   
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = pu.users_id AND pu.add_own_child IS NOT NULL
					THEN 2
				
					#determines whether user is assigned to role that has settings for creating node of type#
					WHEN MAX(pr.add_child) IS NOT NULL
					THEN 3
					
					#Determines whether user is assigned to role that has settings for creating node of a type that they created#
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = :users_id AND MAX(pr.add_own_child) IS NOT NULL
					THEN 4
					
					#by default creator of node type can create nodes of that type#
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = :users_id
					THEN 5
						
					#When nothing has been matched deny creation of node of specified type#      	
					ELSE
					6
						      
					END debug_case_matched
					
					,:deny_add_msg_dev deny_add_msg_dev
					,:deny_add_msg_user deny_add_msg_user
					
			  FROM
			      {$this->_objMCP->escapeString($strBaseTable)} b #base table entity#
			  LEFT OUTER
			  JOIN
			      MCP_PERMISSIONS_USERS pu #current logged-in users explicit permission settings#
			    ON
			      pu.item_type = :item_type #base entity type#
			   AND
				  b.{$this->_objMCP->escapeString($strPrimaryKey)} = pu.item_id #base entity primary key#
			   AND
				  pu.users_id = :users_id
			  LEFT OUTER
			  JOIN
				  MCP_USERS_ROLES u2r #roles that the current user is assigned to. This is the look-up table that assigns a role to a user# 
			    ON
				  u2r.users_id = :users_id
			  LEFT OUTER
			  JOIN
			      MCP_ROLES r
			    ON
			      u2r.roles_id = r.roles_id
			   AND
			      r.deleted = 0 #ignore roles that have been deleted ie. when deleted is null the role has beeen deleted#
			  LEFT OUTER
			  JOIN
				  MCP_PERMISSIONS_ROLES pr #permission settings for the roles that the current user has been assigned to#
				ON
			      pr.item_type = :item_type #base entity type#
			    AND
				  b.{$this->_objMCP->escapeString($strPrimaryKey)} = pr.item_id #base entity primary key#
			    AND
			      r.roles_id = pr.roles_id #role#
			  WHERE
				  b.{$this->_objMCP->escapeString($strPrimaryKey)} IN ({$this->_objMCP->escapeString(implode(',',$arrIds))})
			  GROUP
				 BY
				  b.{$this->_objMCP->escapeString($strPrimaryKey)}";
			     
		// echo "<p>$strSQL</p>";
		return $strSQL;
		
	}
	
	/*
	* @param str base table
	* @param str base table primary key column
	* @param str parent table primary key column
	* @param array entity ids
	* @param str creators column name for base table
	* @return str SQL statement
	* 
	* palceholders:
	* 
	* - :users_id
	* - :default_allow_delete
	* - :default_allow_edit
	* - :default_allow_read
	* - :item_type
	* - :item_type_parent
	*/
	protected function _getEditSQLTemplate($strBaseTable,$strPrimaryKey,$strParentPrimaryKey,$arrIds,$strCreator='creators_id') {
		
		$strSQL = 
			"SELECT
			 	 b.{$this->_objMCP->escapeString($strPrimaryKey)} item_id #base item unique id#
			 	 
			 	 #can user delete node#
				 ,CASE 
						
					#user permission resolution (priority)#
					WHEN upe.`delete` IS NOT NULL
					THEN upe.`delete`
					
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = upe.users_id AND upe.`delete_own` IS NOT NULL
					THEN upe.`delete_own`
						      	
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = upp.users_id AND upp.`delete_own_child` IS NOT NULL
					THEN upp.`delete_own_child`
						      	
					WHEN upp.`delete_child` IS NOT NULL
					THEN upp.`delete_child`
					
					#role permission resolution#
					WHEN MAX(rpe.`delete`) IS NOT NULL
					THEN MAX(rpe.`delete`)
					
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = :users_id AND MAX(rpe.`delete_own`) IS NOT NULL
					THEN MAX(rpe.`delete_own`)
						      	
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = :users_id AND MAX(rpp.`delete_own_child`) IS NOT NULL
					THEN MAX(rpp.`delete_own_child`)
						      	
					WHEN MAX(rpp.`delete_child`) IS NOT NULL
					THEN MAX(rpp.`delete_child`)	
					
					#by default the creator of the node is allowed to delete it#
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = :users_id
					THEN 1
					
					#by default if user has no permissions to delete deny#
					ELSE
					:default_allow_delete
						      
				END allow_delete
				
				#can the user edit node#		      
				,CASE 
						
					#user permission resolution (priority)#
					WHEN upe.`edit` IS NOT NULL
					THEN upe.`edit`
					
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = upe.users_id AND upe.`edit_own` IS NOT NULL
					THEN upe.`edit_own`
						      	
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = upp.users_id AND upp.`edit_own_child` IS NOT NULL
					THEN upp.`edit_own_child`
						      	
					WHEN upp.`edit_child` IS NOT NULL
					THEN upp.`edit_child`
					
					#role permission resolution#
					WHEN MAX(rpe.`edit`) IS NOT NULL
					THEN MAX(rpe.`edit`)
					
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = :users_id AND MAX(rpe.`edit_own`) IS NOT NULL
					THEN MAX(rpe.`edit_own`)
						      	
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = :users_id AND MAX(rpp.`edit_own_child`) IS NOT NULL
					THEN MAX(rpp.`edit_own_child`)
						      	
					WHEN MAX(rpp.`edit_child`) IS NOT NULL
					THEN MAX(rpp.`edit_child`)
						      
					#by default creator of node is allowed to edit it#
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = :users_id
					THEN 1
						 
					#deny edit for everyone else#
					ELSE
					:default_allow_edit
						      
				END allow_edit	
				
				#can the user read node#		      
				,CASE 
						
					#user permission resolution (priority)#
					WHEN upe.`read` IS NOT NULL
					THEN upe.`read`
					
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = upe.users_id AND upe.`read_own` IS NOT NULL
					THEN upe.`read_own`
						      	
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = upp.users_id AND upp.`read_own_child` IS NOT NULL
					THEN upp.`read_own_child`
						      	
					WHEN upp.`read_child` IS NOT NULL
					THEN upp.`read_child`
					
					#role permission resolution#
					WHEN MAX(rpe.`read`) IS NOT NULL
					THEN MAX(rpe.`read`)
					
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = :users_id AND MAX(rpe.`read_own`) IS NOT NULL
					THEN MAX(rpe.`read_own`)
						      	
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = :users_id AND MAX(rpp.`read_own_child`) IS NOT NULL
					THEN MAX(rpp.`read_own_child`)
						      	
					WHEN MAX(rpp.`read_child`) IS NOT NULL
					THEN MAX(rpp.`read_child`)
					
					#by default author may read node#
					WHEN b.{$this->_objMCP->escapeString($strCreator)} = :users_id
					THEN 1
						
					#by default everyone may read the node#
					ELSE
					:default_allow_read
						      
				END allow_read
						      
			FROM
				{$this->_objMCP->escapeString($strBaseTable)} b #base entity table#
				
			# user entity permission#
			LEFT OUTER
			JOIN
				MCP_PERMISSIONS_USERS upe #explicit user node permissions(highest precedence) - user(u) permission(p) entity(e)#
			  ON
				b.{$this->_objMCP->escapeString($strPrimaryKey)} = upe.item_id
			 AND
			    upe.users_id = :users_id
			 AND
			    upe.item_type = :item_type
			    
			 #user entity parent permission#
			 LEFT OUTER
			 JOIN
			     MCP_PERMISSIONS_USERS upp #explicit user node type permissions (parent permission) - user(u) permission(p) parent(p)#
			   ON
			     b.{$this->_objMCP->escapeString($strParentPrimaryKey)} = upp.item_id
			  AND
			     upp.users_id = :users_id
			  AND
				 upp.item_type = :item_type_parent
			
			  # entity role permission#	 
			  LEFT OUTER
			  JOIN
			     MCP_USERS_ROLES u2r #roles user has been assigned to - for entity role permission resolution#
			    ON
			     u2r.users_id = :users_id
			  LEFT OUTER
			  JOIN
			     MCP_ROLES r #roles - resolving entity role permission#
			    ON
			      u2r.roles_id = r.roles_id
			   AND
			      r.deleted = 0
			  LEFT OUTER
			  JOIN
				  MCP_PERMISSIONS_ROLES rpe #role(r) permission(p) entity(e)#
				ON
			      rpe.item_type = :item_type
			    AND
				  b.{$this->_objMCP->escapeString($strPrimaryKey)} = rpe.item_id
			    AND
			      r.roles_id = rpe.roles_id
			      
			   # parent role permission#
			   LEFT OUTER
			   JOIN
			      MCP_USERS_ROLES u2r2 #roles users has been assigned to - for parent role permission resolution#
			     ON
			      u2r2.users_id = :users_id
			   LEFT OUTER
			   JOIN
			      MCP_ROLES r2 #roles - resolving parent entity role permission#
			     ON
			      u2r2.roles_id = r2.roles_id
			    AND
			      r2.deleted = 0
			   LEFT OUTER
			   JOIN
			      MCP_PERMISSIONS_ROLES rpp #role(r) permission(p) parent(p)#
			     ON
			      rpp.item_type = :item_type_parent
			    AND
			      b.{$this->_objMCP->escapeString($strParentPrimaryKey)} = rpp.item_id
			    AND
			      r2.roles_id = rpp.roles_id
				 
			 WHERE
				  b.{$this->_objMCP->escapeString($strPrimaryKey)} IN ({$this->_objMCP->escapeString(implode(',',$arrIds))})
			 GROUP
			    BY
			      b.{$this->_objMCP->escapeString($strPrimaryKey)}";
				
		//echo "<p>$strSQL</p>";
		return $strSQL;
		
	}
	
	protected function _rud($arrIds,$intUser=null) {
		
		$arrPerms = $this->_objMCP->query(
			$this->_getEditSQLTemplate(
				$this->_getBaseTable() //'MCP_NODES'
				,$this->_getPrimaryKey() // 'nodes_id'
				,$this->_getParentPrimaryKey() // 'node_types_id'
				,$arrIds
				,$this->_getCreator() // 'authors_id'
			)
			,array(
				 ':users_id'=>($intUser === null?0:$intUser)
				,':default_allow_delete'=>0
				,':default_allow_edit'=>0
				,':default_allow_read'=>1
				,':item_type'=> $this->_getItemType() // 'MCP_NODES'
				,':item_type_parent'=> $this->_getParentItemType() // 'MCP_NODE_TYPES'
			)
		);
		
		return $arrPerms;
     
	}
	
	protected function _c($arrIds,$intUser=null) {
		
		// echo "<p>{$this->_getParentItemType()}:$intUser</p>";
		
		$arrPerms = $this->_objMCP->query(
			$this->_getCreateSQLTemplate(
				$this->_getParentTable() //'MCP_NODE_TYPES'
				,$this->_getParentPrimaryKey() // 'node_types_id'
				,$arrIds
				,$this->_getParentCreator()
			)
			,array(
				 ':item_type'=> $this->_getParentItemType() // 'MCP_NODE_TYPES'
				,':users_id'=>(int) ( $intUser === null?0:$intUser )
				,':default_allow_add'=>0
				,':deny_add_msg_dev'=>''
				,':deny_add_msg_user'=>''
			)
		);
		
		return $arrPerms;
      
	}

	public function add($ids,$intUserId=null) {
		
		$permissions = $this->_c($ids,$intUserId);
		
		$return = array();
		
		/*if($permission !== null) {
			$return[] = array(
				'allow'=>(bool) $permission['allow_add']
				,'msg_dev'=>$permission['deny_add_msg_dev']
				,'msg_user'=>'You may not add %s.'
				,'case_matched'=>$permission['debug_case_matched']
			);
		} else {
			$return[] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to create a %s'
				,'msg_user'=>'You may not add %s.'
				,'case_matched'=>null
			);
		}*/
		
		// echo '<pre>',print_r($return),'</pre>';
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_add']
				,'msg_dev'=>isset($permission['deny_add_msg_dev'])?$permission['deny_add_msg_dev']:''
				,'msg_user'=>'You may not add %s.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to add specified %s'
				,'msg_user'=>'You may not add %s.'
			);
		}
		
		return $return;
		
	}
	
	public function read($ids,$intUserId=null) {
		
		$permissions = $this->_rud($ids,$intUserId);
		
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
	
	public function delete($ids,$intUserId=null) {
		
		$permissions = $this->_rud($ids,$intUserId);
		
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
	
	public function edit($ids,$intUserId=null) {
		
		$permissions = $this->_rud($ids,$intUserId);
		
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
	abstract protected function _getParentTable(); // @return string
	abstract protected function _getPrimaryKey(); // @return string
	abstract protected function _getParentPrimaryKey(); // @return string
	abstract protected function _getItemType(); // @return string
	abstract protected function _getParentItemType(); // @return string
	abstract protected function _getCreator(); // @return string
	abstract protected function _getParentCreator(); // @return string

}
?>