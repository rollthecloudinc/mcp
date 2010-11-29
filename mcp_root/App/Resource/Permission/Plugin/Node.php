<?php
$this->import('App.Core.DAO');
$this->import('App.Core.Permission');

/*
* Node permissions data access layer
*/
class MCPPermissionNode extends MCPDAO implements MCPPermission {
	
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

		$strSQL = sprintf(
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
		);
		
		$arrPerms = $this->_objMCP->query($strSQL);
		
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

		$strSQL = sprintf(
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
		);
		
		$arrPerms = $this->_objMCP->query($strSQL);
		
		return $arrPerms;
      
	}
	
}     
?>