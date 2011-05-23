<?php 
$this->import('App.Core.DAO');
$this->import('App.Core.Permission');

/*
* Template permission. Extend this class when creating permissions. This
* class provides several "helper" methods to build the SQL "commonly" associated 
* with permissions for different entity levels. The normal entity permissions
* are represented as a three level tree.
*
*/
abstract class MCPPermissionBase extends MCPDAO implements MCPPermission {
	
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
	protected function _getTopLevelEntityCreateSQLTemplate() {

		return
			"SELECT
			      CASE
			     
					WHEN pu.add IS NOT NULL
					THEN pu.add
			     
			     	WHEN MAX(pr.add) IS NOT NULL
			     	THEN MAX(pr.add)
			     	
			     	ELSE 0   	
			     END allow_add
			     
			     ,:deny_add_msg_dev deny_add_msg_dev 
			     ,:deny_add_msg_user deny_add_msg_user
			     
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
	protected function _getTopLevelEntityEditSQLTemplate($strBaseTable,$strPrimaryKey,$arrIds,$strCreator='creators_id') {
		
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
	protected function _getChildLevelEntityCreateSQLTemplate($strBaseTable,$strPrimaryKey,$arrIds,$strCreator='creators_id') {
		
		return
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
	protected function _getChildLevelEntityEditSQLTemplate($strBaseTable,$strPrimaryKey,$strParentPrimaryKey,$arrIds,$strCreator='creators_id') {
		
		return
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
		
	}
	
}
?>