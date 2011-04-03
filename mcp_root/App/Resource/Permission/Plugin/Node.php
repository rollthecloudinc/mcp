<?php
// abstract base class
$this->import('App.Resource.Permission.PermissionBase');

/*
* Node permissions data access layer
*/
class MCPPermissionNode extends MCPPermissionBase {
	
	/*
	* Determine whether user is allowed to create node of specified type(s)
	* 
	* @param array node type ids
	* @return array permissions
	*/
	public function add($ids) {
		
		$permissions = $this->_c($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_add']
				,'msg_dev'=>$permission['deny_add_msg_dev']
				,'msg_user'=>'You are not allowed to create content of specified classification.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to create content of specified classification'
				,'msg_user'=>'You are not allowed to create content of specified classification.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user may read given node data
	* 
	* @param array node ids
	* @return array permissions
	*/
	public function read($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_read']
				,'msg_dev'=>$permission['deny_read_msg_dev']
				,'msg_user'=>'You are not allowed to see specified content.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to see specified content.'
				,'msg_user'=>'You are not allowed to see specified content.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user may edit node
	* 
	* @param array node ids
	* @return array permissions
	*/
	public function edit($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_edit']
				,'msg_dev'=>$permission['deny_edit_msg_dev']
				,'msg_user'=>'You are not allowed to edit specified content.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to edit specified content.'
				,'msg_user'=>'You are not allowed to edit specified content.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user may delete node
	* 
	* @param array node ids
	* @return array permissions
	*/
	public function delete($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_delete']
				,'msg_dev'=>$permission['deny_delete_msg_dev']
				,'msg_user'=>'You are not allowed to delete specified content.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to delete specified content.'
				,'msg_user'=>'You are not allowed to delete specified content.'
			);
		}
		
		return $return;
		
	}

	/*
	* Determine whether user is allowed to edit, delete or read node
	*
	* @param array node id(s)
	* @param int users id (when unspecified defaults to current user)
	* @return array permission set
	*/
	private function _rud($arrNodeIds,$intUser=null) {

		/*$strSQL = sprintf( old query w/o role management
			"SELECT
			       l.nodes_id item_id
			      ,CASE 
			      
			      	WHEN lp.delete IS NOT NULL
			      	THEN lp.delete
			      	
			      	WHEN l.authors_id = mp.users_id AND mp.delete_own_child IS NOT NULL
			      	THEN mp.delete_own_child
			      	
			      	WHEN mp.delete_child IS NOT NULL
			      	THEN mp.delete_child
			      	
			      	WHEN l.authors_id = %s
			      	THEN 1
			      	
			      	ELSE
			      	0
			      
			      END allow_delete
			      
			      ,CASE 
			      
			      	WHEN lp.edit IS NOT NULL
			      	THEN lp.edit
			      	
			      	WHEN l.authors_id = mp.users_id AND mp.edit_own_child IS NOT NULL
			      	THEN mp.edit_own_child
			      	
			      	WHEN mp.edit_child IS NOT NULL
			      	THEN mp.edit_child
			      	
			      	WHEN l.authors_id = %1\$s
			      	THEN 1
			      	
			      	ELSE
			      	0
			      
			      END allow_edit
			      
			      
			      ,CASE 
			      
			      	WHEN lp.read IS NOT NULL
			      	THEN lp.read
			      	
			      	WHEN l.authors_id = mp.users_id AND mp.read_own_child IS NOT NULL
			      	THEN mp.read_own_child
			      	
			      	WHEN mp.read_child IS NOT NULL
			      	THEN mp.read_child
			      	
			      	WHEN l.authors_id = %1\$s
			      	THEN 1
			      	
			      	ELSE
			      	1
			      
			      END allow_read
			      
			      ,CASE 
			      
			      	WHEN lp.delete IS NOT NULL
			      	THEN 'You are not allowed to delete specified content'
			      	
			      	WHEN l.authors_id = mp.users_id AND mp.delete_own_child IS NOT NULL
			      	THEN 'You are not allowed to delete specified content'
			      	
			      	WHEN mp.delete_child IS NOT NULL
			      	THEN 'You are not allowed to delete specified content'
			      	
			      	WHEN l.authors_id = %1\$s
			      	THEN 'You are not allowed to delete specified content'
			      	
			      	ELSE
			      	'You are not allowed to delete specified content'
			      
			      END deny_delete_msg_dev
			      
			      ,CASE 
			      
			      	WHEN lp.edit IS NOT NULL
			      	THEN 'You are not allowed to edit specified content'
			      	
			      	WHEN l.authors_id = mp.users_id AND mp.edit_own_child IS NOT NULL
			      	THEN 'You are not allowed to edit specified content'
			      	
			      	WHEN mp.edit_child IS NOT NULL
			      	THEN 'You are not allowed to edit specified content'
			      	
			      	WHEN l.authors_id = %1\$s
			      	THEN 'You are not allowed to edit specified content'
			      	
			      	ELSE
			      	'You are not allowed to edit specified content'
			      
			      END deny_edit_msg_dev
			      
			      ,CASE 
			      
			      	WHEN lp.read IS NOT NULL
			      	THEN 'You are not allowed to read specified content'
			      	
			      	WHEN l.authors_id = mp.users_id AND mp.read_own_child IS NOT NULL
			      	THEN 'You are not allowed to read specified content'
			      	
			      	WHEN mp.read_child IS NOT NULL
			      	THEN mp.read_child
			      	
			      	WHEN l.authors_id = %1\$s
			      	THEN 'You are not allowed to read specified content'
			      	
			      	ELSE
			      	'You are not allowed to read specified content'
			      
			      END deny_read_msg_dev
			      
			  FROM
			      MCP_NODES l
			  LEFT OUTER
			  JOIN
			      MCP_PERMISSIONS_USERS lp
			    ON
			      l.nodes_id = lp.item_id
			   AND
			      lp.users_id = %1\$s
			   AND
			      lp.item_type = 'MCP_NODES'
			  LEFT OUTER
			  JOIN
			     MCP_PERMISSIONS_USERS mp
			    ON
			     l.node_types_id = mp.item_id
			   AND
			     mp.users_id = %1\$s
			   AND
			     mp.item_type = 'MCP_NODE_TYPES'
			 WHERE
			     l.nodes_id IN (%s)"
			,$this->_objMCP->escapeString($intUser === null?0:$intUser)
			,$this->_objMCP->escapeString(implode(',',$arrNodeIds))
		);*/
		
		/*$strSQL = sprintf(
			"SELECT
			 	 b.nodes_id item_id #base item unique id#
			 	 
			 	 #can user delete node#
				 ,CASE 
						
					#user permission resolution (priority)#
					WHEN upe.`delete` IS NOT NULL
					THEN upe.`delete`
					
					WHEN b.`authors_id` = upe.users_id AND upe.`delete_own` IS NOT NULL
					THEN upe.`delete_own`
						      	
					WHEN b.`authors_id` = upp.users_id AND upp.`delete_own_child` IS NOT NULL
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
					WHEN b.`authors_id` = %s
					THEN 1
					
					#by default if user has no permissions to delete deny#
					ELSE
					0
						      
				END allow_delete
				
				#can the user edit node#		      
				,CASE 
						
					#user permission resolution (priority)#
					WHEN upe.`edit` IS NOT NULL
					THEN upe.`edit`
					
					WHEN b.`authors_id` = upe.users_id AND upe.`edit_own` IS NOT NULL
					THEN upe.`edit_own`
						      	
					WHEN b.`authors_id` = upp.users_id AND upp.`edit_own_child` IS NOT NULL
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
						      
					#by default creator of node is allowed to edit it#
					WHEN b.`authors_id` = %1\$s
					THEN 1
						 
					#deny edit for everyone else#
					ELSE
					0
						      
				END allow_edit	
				
				#can the user read node#		      
				,CASE 
						
					#user permission resolution (priority)#
					WHEN upe.`read` IS NOT NULL
					THEN upe.`read`
					
					WHEN b.`authors_id` = upe.users_id AND upe.`read_own` IS NOT NULL
					THEN upe.`read_own`
						      	
					WHEN b.`authors_id` = upp.users_id AND upp.`read_own_child` IS NOT NULL
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
					
					#by default author may read node#
					WHEN b.`authors_id` = %1\$s
					THEN 1
						
					#by default everyone may read the node#
					ELSE
					1
						      
				END allow_read
						      
			FROM
				`MCP_NODES` b #base entity table#
				
			# user entity permission#
			LEFT OUTER
			JOIN
				MCP_PERMISSIONS_USERS upe #explicit user node permissions(highest precedence) - user(u) permission(p) entity(e)#
			  ON
				b.nodes_id = upe.item_id
			 AND
			    upe.users_id = %1\$s
			 AND
			    upe.item_type = 'MCP_NODES'
			    
			 #user entity parent permission#
			 LEFT OUTER
			 JOIN
			     MCP_PERMISSIONS_USERS upp #explicit user node type permissions (parent permission) - user(u) permission(p) parent(p)#
			   ON
			     b.node_types_id = upp.item_id
			  AND
			     upp.users_id = %1\$s
			  AND
				 upp.item_type = 'MCP_NODE_TYPES'
			
			  # entity role permission#	 
			  LEFT OUTER
			  JOIN
			     MCP_USERS_ROLES u2r #roles user has been assigned to - for entity role permission resolution#
			    ON
			     u2r.users_id = %1\$s
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
			      rpe.item_type = 'MCP_NODES'
			    AND
				  b.nodes_id = rpe.item_id
			    AND
			      r.roles_id = rpe.roles_id
			      
			   # parent role permission#
			   LEFT OUTER
			   JOIN
			      MCP_USERS_ROLES u2r2 #roles users has been assigned to - for parent role permission resolution#
			     ON
			      u2r2.users_id = %1\$s
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
			      rpp.item_type = 'MCP_NODE_TYPES'
			    AND
			      b.node_types_id = rpp.item_id
			    AND
			      r2.roles_id = rpp.roles_id
				 
			 WHERE
				  b.nodes_id IN (%s)
			 GROUP
			    BY
			      b.nodes_id"
			,$this->_objMCP->escapeString($intUser === null?0:$intUser)
			,$this->_objMCP->escapeString(implode(',',$arrNodeIds))
		);
		
		$arrPerms = $this->_objMCP->query($strSQL);*/
		
		$arrPerms = $this->_objMCP->query(
			$this->_getChildLevelEntityEditSQLTemplate('MCP_NODES','nodes_id','node_types_id',$arrNodeIds,'authors_id')
			,array(
				 ':users_id'=>($intUser === null?0:$intUser)
				,':default_allow_delete'=>0
				,':default_allow_edit'=>0
				,':default_allow_read'=>1
				,':item_type'=>'MCP_NODES'
				,':item_type_parent'=>'MCP_NODE_TYPES'
			)
		);
		
		return $arrPerms;
     
	}
	

	/*
	* Determine whether user is allowed to create node of specified node type(s)
	*
	* @param array node type id(s)
	* @param int users id (when unspecified defaults to current user)
	* @return array permission set
	*/
	private function _c($arrNodeTypeIds,$intUser=null) {

		/*$strSQL = sprintf( // old query w/o role management
			"SELECT
			      m.node_types_id item_id
			      ,CASE
			      
			        WHEN mp.add_child IS NOT NULL
			        THEN mp.add_child
			      
			      	WHEN m.creators_id = mp.users_id AND mp.add_own_child IS NOT NULL
			      	THEN mp.add_own_child
			      	
			      	WHEN m.creators_id = %s
			      	THEN 1
			      	
			      	ELSE
			      	0
			      
			       END allow_add
			       
			      ,CASE
			      
			        WHEN mp.add_child IS NOT NULL
			        THEN 'You are not allowed to create content of specified classification'
			      
			      	WHEN m.creators_id = mp.users_id AND mp.add_own_child IS NOT NULL
			      	THEN 'You are not allowed to create content of specified classification'
			      	
			      	WHEN m.creators_id = %1\$s
			      	THEN 'You are not allowed to create content of specified classification'
			      	
			      	ELSE
			      	'You are not allowed to create content of specified classification'
			      
			       END deny_add_msg_dev
			  FROM
			      MCP_NODE_TYPES m
			  LEFT OUTER
			  JOIN
			      MCP_PERMISSIONS_USERS mp
			    ON
			      m.node_types_id = mp.item_id
			   AND
			      mp.users_id = %1\$s
			   AND
			      mp.item_type = 'MCP_NODE_TYPES'
			 WHERE
			      m.node_types_id IN (%s)"
			,$this->_objMCP->escapeString($intUser === null?0:$intUser)
			,$this->_objMCP->escapeString(implode(',',$arrNodeTypeIds))
		);*/
		
		/*$strSQL = sprintf(
			"SELECT
			     b.node_types_id item_id #the generic entity id#
				 ,CASE
						      
					#user permissions have precedence over roles. Determoine whether user has explicit setting to create a node of the type#
				    WHEN pu.add_child IS NOT NULL
					THEN pu.add_child
				
					#user permissions have precedence over roles. Determine whether user has explicit setting to create node of a type they created#   
					WHEN b.creators_id = pu.users_id AND pu.add_own_child IS NOT NULL
					THEN pu.add_own_child
				
					#determines whether user is assigned to role that has settings for creating node of type#
					WHEN MAX(pr.add_child) IS NOT NULL
					THEN MAX(pr.add_child)
					
					#Determines whether user is assigned to role that has settings for creating node of a type that they created#
					WHEN MAX(pr.add_own_child) IS NOT NULL
					THEN MAX(pr.add_own_child)
					
					#by default creator of node type can create nodes of that type#
					WHEN b.creators_id = %s
					THEN 1
						
					#When nothing has been matched deny creation of node of specified type#      	
					ELSE
					0
						      
					END allow_add	       
			  FROM
			      MCP_NODE_TYPES b #base table entity#
			  LEFT OUTER
			  JOIN
			      MCP_PERMISSIONS_USERS pu #current logged-in users explicit permission settings#
			    ON
			      pu.item_type = 'MCP_NODE_TYPES' #base entity type#
			   AND
				  b.node_types_id = pu.item_id #base entity primary key#
			   AND
				  pu.users_id = %1\$s #current user primary key#
			  LEFT OUTER
			  JOIN
				  MCP_USERS_ROLES u2r #roles that the current user is assigned to. This is the look-up table that assigns a role to a user# 
			    ON
				  u2r.users_id = %1\$s
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
			      pr.item_type = 'MCP_NODE_TYPES' #base entity type#
			    AND
				  b.node_types_id = pr.item_id #base entity primary key#
			    AND
			      r.roles_id = pr.roles_id #role#
			  WHERE
				  b.node_types_id IN (%s)
			  GROUP
				 BY
				  b.node_types_id"
			,$this->_objMCP->escapeString($intUser === null?0:$intUser)
			,$this->_objMCP->escapeString(implode(',',$arrNodeTypeIds))
		);*/
		
		// $arrPerms = $this->_objMCP->query($strSQL);
		
		$arrPerms = $this->_objMCP->query(
			$this->_getChildLevelEntityCreateSQLTemplate('MCP_NODE_TYPES','node_types_id',$arrNodeTypeIds)
			,array(
				 ':item_type'=>'MCP_NODE_TYPES'
				,':users_id'=>(int) ( $intUser === null?0:$intUser )
				,':default_allow_add'=>0
				,':deny_add_msg_dev'=>''
				,':deny_add_msg_user'=>''
			)
		);
		
		return $arrPerms;
      
	}
	
}     
?>