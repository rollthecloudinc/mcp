<?php
// abstract base class
$this->import('App.Resource.Permission.PermissionBase');

/*
* Site permission data access layer 
*/
class MCPPermissionSite extends MCPPermissionBase {
	
	/*
	* Determine whether user is allowed to create a new site 
	*/
	public function add($ids) {
		
		$permission = $this->_c($this->_objMCP->getUsersId());
		
		$return = array();
		
		if($permission !== null) {
			$return[] = array(
				'allow'=>(bool) $permission['allow_add']
				,'msg_dev'=>$permission['deny_add_msg_dev']
				,'msg_user'=>'You may not create a new site.'
			);
		} else {
			$return[] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to create a new site'
				,'msg_user'=>'You may not create a new site.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user is allowed to site(s)
	*/
	public function read($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_read']
				,'msg_dev'=>$permission['deny_read_msg_dev']
				,'msg_user'=>'You may not view site.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to view site'
				,'msg_user'=>'You may not view site.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user is allowed to delete site(s)
	*/
	public function delete($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_delete']
				,'msg_dev'=>$permission['deny_delete_msg_dev']
				,'msg_user'=>'You may not delete site.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to delete specified site'
				,'msg_user'=>'You may not delete site.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user is allowed to edit site(s) 
	*/
	public function edit($ids) {
		
		$permissions = $this->_rud($ids,$this->_objMCP->getUsersId());
		
		$return = array();
		foreach($permissions as $permission) {
			$return[$permission['item_id']] = array(
				'allow'=>(bool) $permission['allow_edit']
				,'msg_dev'=>$permission['deny_edit_msg_dev']
				,'msg_user'=>'You may not edit site.'
			);
		}
		
		foreach(array_diff($ids,array_keys($return)) as $id) {
			$return[$id] = array(
				'allow'=>false
				,'msg_dev'=>'You are not allowed to edit specified site'
				,'msg_user'=>'You may not edit site.'
			);
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user is allowed to edit, read or delete specified site(s)
	*
	* @param array site ids
	* @param int users id (when unspecified defaults to current user)
	* @return array permission set
	*/
	private function _rud($arrSiteIds,$intUser=null) {
		
		/*$strSQL = sprintf(
			"SELECT
			     m.sites_id item_id
			     ,CASE
			     
			      	WHEN mp.`delete` IS NOT NULL
			      	THEN mp.`delete`
			      	
			      	WHEN m.creators_id = amp.users_id AND amp.delete_own IS NOT NULL
			      	THEN amp.delete_own
			      	
			      	WHEN amp.`delete` IS NOT NULL
			      	THEN amp.`delete`
			      	
			      	WHEN m.creators_id = %s
			      	THEN 1
			      	  	
			      	ELSE
			      	0
			     
			      END allow_delete
			     
			     ,CASE
			
			      	WHEN mp.edit IS NOT NULL
			      	THEN mp.edit
			      	
			      	WHEN m.creators_id = amp.users_id AND amp.edit_own IS NOT NULL
			      	THEN amp.edit_own
			      	
			      	WHEN amp.edit IS NOT NULL
			      	THEN amp.edit
			      	
			      	WHEN m.creators_id = %1\$s
			      	THEN 1
			      	  	
			      	ELSE
			      	0     
			     
			      END allow_edit
			      
			     ,CASE
			
			      	WHEN mp.read IS NOT NULL
			      	THEN mp.read
			      	
			      	WHEN m.creators_id = amp.users_id AND amp.read_own IS NOT NULL
			      	THEN amp.read_own
			      	
			      	WHEN amp.`read` IS NOT NULL
			      	THEN amp.`read`
			      	
			      	WHEN m.creators_id = %1\$s
			      	THEN 1
			      	  	
			      	ELSE
			      	1    
			     
			      END allow_read
			      
			     ,CASE
			     
			      	WHEN mp.`delete` IS NOT NULL
			      	THEN 'You are not allowed to delete the specified site'
			      	
			      	WHEN m.creators_id = amp.users_id AND amp.delete_own IS NOT NULL
			      	THEN 'You are not allowed to delete the specified site'
			      	
			      	WHEN amp.`delete` IS NOT NULL
			      	THEN 'You are not allowed to delete the specified site'
			      	
			      	WHEN m.creators_id = %1\$s
			      	THEN 'You are not allowed to delete the specified site'
			      	  	
			      	ELSE
			      	'You are not allowed to delete the specified site'
			     
			      END deny_delete_msg_dev
			      
			     ,CASE
			
			      	WHEN mp.edit IS NOT NULL
			      	THEN 'You are not allowed to edit specified site'
			      	
			      	WHEN m.creators_id = amp.users_id AND amp.edit_own IS NOT NULL
			      	THEN 'You are not allowed to edit specified site'
			      	
			      	WHEN amp.edit IS NOT NULL
			      	THEN 'You are not allowed to edit specified site'
			      	
			      	WHEN m.creators_id = %1\$s
			      	THEN 'You are not allowed to edit specified site'
			      	  	
			      	ELSE
			      	'You are not allowed to edit specified site'    
			     
			      END deny_edit_msg_dev
			      
			     ,CASE
			
			      	WHEN mp.read IS NOT NULL
			      	THEN 'You are not allowed to see specified site'
			      	
			      	WHEN m.creators_id = amp.users_id AND amp.read_own IS NOT NULL
			      	THEN 'You are not allowed to see specified site'
			      	
			      	WHEN amp.`read` IS NOT NULL
			      	THEN 'You are not allowed to see specified site'
			      	
			      	WHEN m.creators_id = %1\$s
			      	THEN 'You are not allowed to see specified site'
			      	  	
			      	ELSE
			      	'You are not allowed to see specified site'   
			     
			      END deny_read_msg_dev
			      
			  FROM
			     MCP_SITES m
			  LEFT OUTER
			  JOIN
			     MCP_PERMISSIONS_USERS mp
			    ON
			     m.sites_id = mp.item_id
			   AND
			     mp.users_id = %1\$s
			   AND
			     mp.item_type = 'MCP_SITES'
			  LEFT OUTER
			  JOIN
			     MCP_PERMISSIONS_USERS amp
			    ON
			     amp.item_id = 0
			   AND
			     amp.users_id = %1\$s
			   AND
			     amp.item_type = 'MCP_SITES'
			 WHERE
			     m.sites_id IN (%s)"
			,$this->_objMCP->escapeString($intUser === null?0:$intUser)
			,$this->_objMCP->escapeString(implode(',',$arrSiteIds))
		);
		
		$arrPerms = $this->_objMCP->query($strSQL);
		
		return $arrPerms;*/
		
		$arrPerms = $this->_objMCP->query(
			 $this->_getTopLevelEntityEditSQLTemplate('MCP_SITES','sites_id',$arrSiteIds,'creators_id')
			,array(
				 ':users_id'=>$intUser === null?0:$intUser
				,':item_type'=>'MCP_SITES'
				,':default_allow_delete'=>0
				,':default_allow_edit'=>0
				,':default_allow_read'=>1
			)
		);
		
		// echo '<pre>',print_r($arrPerms),'</pre>';
		
		return $arrPerms;
		
	}
	
	/*
	* Determine whether user is allowed to create a new site
	*
	* @param int users id (when unspecified defaults to current user)
	* @return array permission set
	*/
	private function _c($intUser=null) {
		
		/*$strSQL = sprintf(
			"SELECT
			     amp.item_id
			     ,CASE
			     	
			     	WHEN amp.add IS NOT NULL
			     	THEN amp.add
			     	
			     	ELSE
			     	0
			     	
			      END allow_add
			      
			      ,'You are not allowed to create a site' deny_add_msg_dev
			  FROM
			     MCP_PERMISSIONS_USERS amp
			 WHERE
			     amp.item_id = 0
			   AND
			     amp.users_id = %s
			   AND
			     amp.item_type = 'MCP_SITES'"
			,$this->_objMCP->escapeString($intUser === null?0:$intUser)
		);*/
		
		//$arrPerms = $this->_objMCP->query($strSQL);
		
		$arrPerms = $this->_objMCP->query(
			 $this->_getTopLevelEntityCreateSQLTemplate()
			,array(
				 ':users_id'=>$intUser === null?0:$intUser
				,':entity_type'=>'MCP_SITES'
				,':deny_add_msg_dev'=>''
				,':deny_add_msg_user'=>''
			)
		);
		
		return array_pop($arrPerms);
     
	}
	
}
?>
