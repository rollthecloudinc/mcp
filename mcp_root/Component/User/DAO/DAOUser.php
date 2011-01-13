<?php
/*
* User data access layer 
*/
$this->import('App.Core.DAO');
class MCPDAOUser extends MCPDAO {
	
	/*
	* List all users
	* 
	* @param str select fields
	* @param str where clause
	* @param order by clause
	* @param limit clause
	* @return array users
	*/
	public function listAll($strSelect='*',$strFilter=null,$strSort=null,$strLimit=null) {
		
		/*
		* Build SQL 
		*/
		$strSQL = sprintf(
			'SELECT
			      %s %s
			      ,users_id tmp_users_id
			      ,sites_id tmp_sites_id
			   FROM
			      MCP_USERS
			      %s
			      %s
			      %s'
			,$strLimit === null?'':'SQL_CALC_FOUND_ROWS'
			,$strSelect
			,$strFilter === null?'':"WHERE $strFilter"
			,$strSort === null?'':"ORDER BY $strSort"
			,$strLimit === null?'':"LIMIT $strLimit"
		);
		
		$arrUsers = $this->_objMCP->query($strSQL);
		
		/*
		* Creates to much of a security risk to make this data available through http 
		*/
		if($this->_objMCP->isDAORequest() === true) {
			foreach($arrUsers as &$arrUser) {
				unset($arrUser['pwd'],$arrUser['email_address'],$arrUser['user_data']);
			}
		}
		
		/*
		* Add dynamic fields 
		*/
		foreach($arrUsers as &$arrUser) {
			$arrUser = $this->_objMCP->addFields($arrUser,$arrUser['tmp_users_id'],'MCP_SITES',$arrUser['tmp_sites_id']);
			unset($arrUser['tmp_users_id'],$arrUser['tmp_sites_id']);
		}
		
		return array(
			$arrUsers
			,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
		);
		
	}
	
	/*
	* Fetch a single user by id 
	* 
	* @param int users id
	* @param str fields to select
	* @return array user data
	*/
	public function fetchById($intId,$strSelect='*') {
		$strId = $this->_objMCP->escapeString($intId);
		$strSQL = "SELECT $strSelect,users_id tmp_users_id,sites_id tmp_sites_id FROM MCP_USERS WHERE users_id = ?";
		$arrUser = array_pop($this->_objMCP->query($strSQL,array($intId)));
		
		// add dynamic fields
		$arrUser = $this->_objMCP->addFields($arrUser,$arrUser['tmp_users_id'],'MCP_SITES',$arrUser['tmp_sites_id']);
		unset($arrUser['tmp_users_id'],$arrUser['tmp_sites_id']);
		
		return $arrUser;
	}
	
	/*
	* Locate a user with matching username and password that belongs to
	* the given site.
	* 
	* @param str username
	* @param str password
	* @param int sites id
	* @return array user data
	*/
	public function fetchUserByLoginCredentials($strUsername,$strPassword,$intSitesId) {
		
		$strSQL = 
			sprintf(
		          "SELECT 
		                users_id 
		             FROM
		                MCP_USERS
		            WHERE
		                sites_id = %s
		              AND
		                username = '%s'
		              AND
		                pwd = SHA1( CONCAT('%s','%s',created_on_timestamp) )
		              AND
		                deleted = 0"
		           ,$this->_objMCP->escapeString($intSitesId)
		           ,$this->_objMCP->escapeString($strUsername)
		           ,$this->_objMCP->escapeString($strPassword)
		           ,$this->_objMCP->escapeString($this->_objMCP->getSalt())
		 	);

		 return array_pop($this->_objMCP->query($strSQL));
	}
	
	/*
	* Update users data 
	* 
	* @param int users id
	* @param array user data
	*/
	public function updateUsersData($intId,$arrUserData) {
		$strSQL = sprintf(
			"UPDATE MCP_USERS SET user_data = '%s' WHERE users_id = %s"
			,base64_encode(serialize($arrUserData))
			,$this->_objMCP->escapeString($intId)
		);
	}
	
	/*
	* Insert or update user entry
	* 
	* NOTE: Does not use generic _save method due to customization with password handling. This
	* is perfectly acceptable considering the generic save method exists as a helper more
	* than anything else. However, when the circumstance calls for it its fine to not use it
	* or copy and modify it as needed to achieve goals that are outside the base functionality.
	* 
	* @param array user data
	*/
	public function saveUser($arrUser) {
		
		$dynamic = array();
		
		/*
		* Get fields native to table
		*/
		$schema = $this->_objMCP->query('DESCRIBE MCP_USERS');
		
		$native = array();
		foreach($schema as $column) {
			$native[] = $column['Field'];
		}
		
		$boolUpdate = false;
		$arrUpdate = array();
		
		$arrValues = array();
		$arrColumns = array();
		$now = time();
		
		foreach($arrUser as $strField=>$mixValue) {
			
			// move dynamic fields to dynamic array
			if(!in_array($strField,$native)) {
				$dynamic[$strField] = $mixValue;
				continue;
			}
			
			if(strcmp('users_id',$strField) == 0) {
				$boolUpdate = true;
			} else {
				$arrUpdate[] = "$strField = VALUES($strField)";
			}
			
			if($boolUpdate === false && strcmp('pwd',$strField) == 0) {
				$mixValue = sprintf(
					"SHA1(CONCAT('%s','%s',FROM_UNIXTIME(%s)))"
					,$this->_objMCP->escapeString($mixValue)
					,$this->_objMCP->escapeString($this->_objMCP->getSalt())
					,$this->_objMCP->escapeString($now)
				);
			} else if(in_array($strField,array(
				'username'
				,'email_address'
				,'pwd'
				,'user_data'
			))) {
				$mixValue = "'".$this->_objMCP->escapeString($mixValue)."'";
			} else {
				$mixValue = $this->_objMCP->escapeString($mixValue);
			}
			
			$arrColumns[] = $strField;  
			$arrValues[] = $mixValue;
			
		}
		
		if(!array_key_exists('created_on_timestamp',$arrColumns)) {
			$arrColumns[] = 'created_on_timestamp';
			$arrValues[] = "FROM_UNIXTIME($now)";
		}
		
		$strSQL = sprintf(
			'INSERT INTO MCP_USERS (%s) VALUES (%s) %s'
			,implode(',',$arrColumns)
			,implode(',',$arrValues)
			,$boolUpdate === true?' ON DUPLICATE KEY UPDATE '.implode(',',$arrUpdate):''
		);
		
		$intId = $this->_objMCP->query($strSQL);
		
		// Get user
		$user = $this->fetchById(isset($arrUser['users_id'])?$arrUser['users_id']:$intId);
		
		// Save dynamic fields
		$this->_objMCP->saveFieldValues($dynamic,$user['users_id'],'MCP_SITES',$user['sites_id']);
		
		return $intId;
		
	}
	
	/*
	* Delete a user 
	* 
	* @param mix single integer value or array of integers (MCP_USERS primary key)
	*/
	public function deleteUsers($mixUsersId) {
		
		/*
		* Build SQL to soft-delete user
		*/
		$strSQL = sprintf(
			"UPDATE
			      MCP_USERS
			    SET
			       MCP_USERS.deleted = NULL
			      ,MCP_USERS.deleted_on_timestamp = NOW()
			  WHERE
			      MCP_USERS.users_id IN (%s)"
			,is_array($mixUsersId)?$this->_objMCP->escapeString(implode(',',$mixUsersId)):$this->_objMCP->escapeString($mixUsersId)
		);
		
		echo "<p>$strSQL</p>";
		// return $this->_objMCP->query($strSQL);
		
	}
	
}
?>