<?php
// abstract base class
$this->import('App.Resource.Permission.ChildLevelPermission');

/*
* Navigation link permissions data access layer
*/
class MCPPermissionNavigationLink extends MCPChildLevelPermission {
	
	protected
	
	/*
	* navigation data access layer used to resolve menu link belongs to 
	*/
	$_objDAONavigation;
	
	public function __construct(MCP $objMCP) {
		parent::__construct($objMCP);
		
		// get navigation data access layer
		$this->_objDAONavigation = $this->_objMCP->getInstance('Component.Navigation.DAO.DAONavigation',array($this->_objMCP));
		
	}
	
	protected function _getBaseTable() {
		return 'MCP_NAVIGATION_LINKS';
	}
	
	protected function _getParentTable() {
		return 'MCP_NAVIGATION';
	}
	
	protected function _getPrimaryKey() {
		return 'navigation_links_id';
	}
	
	protected function _getParentPrimaryKey() {
		return 'navigation_id';
	}
	
	protected function _getItemType() {
		return 'MCP_NAVIGATION_LINK';
	}
	
	protected function _getParentItemType() {
		return 'MCP_NAVIGATION';
	}
	
	protected function _getCreator() {
		return 'creators_id';
	}
	
	protected function _getParentCreator() {
		return 'users_id';
	}
	
	/*
	* Determine whether user may read given link data
	* 
	* @param array navigation links ids
	* @return array permissions
	*/
	public function read($ids) {
		
		/*
		* _rud method accepts single id, so for each item it must be called
		* individually. There may be a solution for rectifying this but at this
		* point its not much of an issue. Add in some caching, as planning and
		* it should be alright for now. 
		*/
		$return = array();
		foreach($ids as $id) {
			$perm = $this->_rud($id,$this->_objMCP->getUsersId());
			
			if($perm !== null) {
				$return[$id] = array(
					'allow'=>(bool) $perm['allow_read']
				);
			} else {
				$return[$id] = array(
					'allow'=>false
				);
			}
			
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user may edit navigation link
	* 
	* @param array navigation links ids
	* @return array permissions
	*/
	public function edit($ids) {
		
		/*
		* _rud method accepts single id, so for each item it must be called
		* individually. There may be a solution for rectifying this but at this
		* point its not much of an issue. Add in some caching, as planning and
		* it should be alright for now. 
		*/
		$return = array();
		foreach($ids as $id) {
			$perm = $this->_rud($id,$this->_objMCP->getUsersId());
			
			if($perm !== null) {
				$return[$id] = array(
					'allow'=>(bool) $perm['allow_edit']
				);
			} else {
				$return[$id] = array(
					'allow'=>false
				);
			}
			
		}
		
		return $return;
		
	}
	
	/*
	* Determine whether user may delete navigation link
	* 
	* @param array navigation link ids
	* @return array permissions
	*/
	public function delete($ids) {
		
		/*
		* _rud method accepts single id, so for each item it must be called
		* individually. There may be a solution for rectifying this but at this
		* point its not much of an issue. Add in some caching, as planning and
		* it should be alright for now. 
		*/
		$return = array();
		foreach($ids as $id) {
			$perm = $this->_rud($id,$this->_objMCP->getUsersId());
			
			if($perm !== null) {
				$return[$id] = array(
					'allow'=>(bool) $perm['allow_delete']
				);
			} else {
				$return[$id] = array(
					'allow'=>false
				);
			}
			
		}
		
		return $return;
		
	}

	/*
	* Determine whether user is allowed to edit, delete or read menu link
	*
	* @param array link id (only allows single link id - efficiency reasons)
	* @param int users id (when unspecified defaults to current user)
	* @return array permission set
	*/
	protected function _rud($intLink,$intUser=null) {
		
		/*
		* Dynamic links that have not been converted to hard links
		* may not have permissions assigned directly to them at this
		* time.
		*/
		if(!is_numeric("$intLink")) {
			return null;
		}
		
		$link = $this->_objDAONavigation->fetchLinkById($intLink);
		
		if($link === null) {
			return null;
		}
		
		/*
		* Get id of menu that link belongs to
		*/
		if(strcasecmp($link['parent_type'],'nav') === 0) {
			$arrMenu = array('navigation_id' => $link['parent_id']);
		} else {
			$arrMenu = $this->_objDAONavigation->fetchNavByLinkId($link['parent_id']);
		}

		$strSQL = sprintf(
			"SELECT
			       l.navigation_links_id item_id
			      ,CASE 
			      
			      	WHEN lp.delete IS NOT NULL
			      	THEN lp.delete
			      	
			      	WHEN l.creators_id = mp.users_id AND mp.delete_own_child IS NOT NULL
			      	THEN mp.delete_own_child
			      	
			      	WHEN mp.delete_child IS NOT NULL
			      	THEN mp.delete_child
			      	
			      	WHEN l.creators_id = %s
			      	THEN 1
			      	
			      	ELSE
			      	0
			      
			      END allow_delete
			      
			      ,CASE 
			      
			      	WHEN lp.edit IS NOT NULL
			      	THEN lp.edit
			      	
			      	WHEN l.creators_id = mp.users_id AND mp.edit_own_child IS NOT NULL
			      	THEN mp.edit_own_child
			      	
			      	WHEN mp.edit_child IS NOT NULL
			      	THEN mp.edit_child
			      	
			      	WHEN l.creators_id = %1\$s
			      	THEN 1
			      	
			      	ELSE
			      	0
			      
			      END allow_edit
			      
			      
			      ,CASE 
			      
			      	WHEN lp.read IS NOT NULL
			      	THEN lp.read
			      	
			      	WHEN l.creators_id = mp.users_id AND mp.read_own_child IS NOT NULL
			      	THEN mp.read_own_child
			      	
			      	WHEN mp.read_child IS NOT NULL
			      	THEN mp.read_child
			      	
			      	WHEN l.creators_id = %1\$s
			      	THEN 1
			      	
			      	ELSE
			      	1
			      
			      END allow_read
			      
			  FROM
			      MCP_NAVIGATION_LINKS l
			  LEFT OUTER
			  JOIN
			      MCP_PERMISSIONS_USERS lp
			    ON
			      l.navigation_links_id = lp.item_id
			   AND
			      lp.users_id = %1\$s
			   AND
			      lp.item_type = 'MCP_NAVIGATION_LINK'
			  LEFT OUTER
			  JOIN
			     MCP_PERMISSIONS_USERS mp
			    ON
			     mp.item_id = %s
			   AND
			     mp.users_id = %1\$s
			   AND
			     mp.item_type = 'MCP_NAVIGATION'
			 WHERE
			     l.navigation_links_id = %s"
			,$this->_objMCP->escapeString($intUser === null?0:$intUser)
			,$this->_objMCP->escapeString($arrMenu['navigation_id'])
			,$this->_objMCP->escapeString($intLink)
		);
		
		$arrPerms = $this->_objMCP->query($strSQL);
		
		return array_pop($arrPerms);
     
	}
	
}     
?>