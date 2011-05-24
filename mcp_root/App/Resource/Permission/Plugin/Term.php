<?php
// abstract base class
$this->import('App.Resource.Permission.ChildLevelPermission');

/*
* Vocabulary term permissions data access layer
*/
class MCPPermissionTerm extends MCPChildLevelPermission {
	
	private 
	
	/*
	* taxonomy data access layer used to resolve vocabulary term belongs to 
	*/
	$_objDAOTaxonomy;
	
	public function __construct(MCP $objMCP) {
		parent::__construct($objMCP);
		
		// get taxonomy data access layer
		$this->_objDAOTaxonomy = $this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP));
		
	}
	
	protected function _getBaseTable() {
		return 'MCP_TERMS';
	}
	
	protected function _getParentTable() {
		return 'MCP_VOCABULARY';
	}
	
	protected function _getPrimaryKey() {
		return 'terms_id';
	}
	
	protected function _getParentPrimaryKey() {
		return 'vocabulary_id';
	}
	
	protected function _getItemType() {
		return 'MCP_TERMS';
	}
	
	protected function _getParentItemType() {
		return 'MCP_VOCABULARY';
	}
	
	protected function _getCreator() {
		return 'creators_id';
	}
	
	protected function _getParentCreator() {
		return 'creators_id';
	}
	
	/*
	* Determine whether user may read given term
	* 
	* @param array term ids
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
	* Determine whether user may edit term
	* 
	* @param array term ids
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
	* Determine whether user may delete term
	* 
	* @param array term ids
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
	* Determine whether user is allowed to edit, delete or read term
	*
	* @param array term id (only allows single term id - efficiency reasons)
	* @param int users id (when unspecified defaults to current user)
	* @return array permission set
	*/
	protected function _rud($intTerm,$intUser=null) {
		
		$term = $this->_objDAOTaxonomy->fetchTermById($intTerm);
		
		if($term === null) {
			return null;
		}
		
		/*
		* Get id of vocabulary that term belongs to
		*/
		$arrVocab = $this->_objDAOTaxonomy->fetchTermsVocabulary($intTerm);

		$strSQL = sprintf(
			"SELECT
			       l.terms_id item_id
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
			      MCP_TERMS l
			  LEFT OUTER
			  JOIN
			      MCP_PERMISSIONS_USERS lp
			    ON
			      l.terms_id = lp.item_id
			   AND
			      lp.users_id = %1\$s
			   AND
			      lp.item_type = 'MCP_TERMS'
			  LEFT OUTER
			  JOIN
			     MCP_PERMISSIONS_USERS mp
			    ON
			     mp.item_id = %s
			   AND
			     mp.users_id = %1\$s
			   AND
			     mp.item_type = 'MCP_VOCABULARY'
			 WHERE
			     l.terms_id = %s"
			,$this->_objMCP->escapeString($intUser === null?0:$intUser)
			,$this->_objMCP->escapeString($arrVocab['vocabulary_id'])
			,$this->_objMCP->escapeString($intTerm)
		);
		
		$arrPerms = $this->_objMCP->query($strSQL);
		
		return array_pop($arrPerms);
     
	}
	
}     
?>