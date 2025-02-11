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
	* 
	* @todo: convert to variable binding - support
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
		
		// may break something
		if( $strLimit === null ) {
			return $arrUsers;
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
	* @param bool accept cached version?
	* @return array user data
	*/
	public function fetchById($intId,$boolCache=true) {
		
		/*
		* Cache handling 
		*/
		/*if( $boolCache === true ) {
			$arrCachedUser = $this->_getCachedUser($intId);
			if( $arrCachedUser !== null ) {
				return $arrCachedUser;
			}
		}*/
		
		$arrUser = array_pop(
			$this->_objMCP->query(
				'SELECT u.* FROM MCP_USERS u WHERE u.users_id = :users_id'
				,array(
					':users_id'=>(int) $intId
				)
		));
		
		// add dynamic fields
		if( $arrUser !== null ) {
			$arrUser = $this->_objMCP->addFields($arrUser,$arrUser['users_id'],'MCP_SITES',$arrUser['sites_id']);
		}
		
		// unset($arrUser['tmp_users_id'],$arrUser['tmp_sites_id']);
		
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

		 return array_pop($this->_objMCP->query(
                  'SELECT 
		                users_id 
		             FROM
		                MCP_USERS
		            WHERE
		                sites_id = :sites_id
		              AND
		                username = :username
		              AND
		                pwd = SHA1( CONCAT(:password,:salt,created_on_timestamp) )
		              AND
		                deleted = 0'
		 	,array(
		 		 ':sites_id'=>(int) $intSitesId
		 		,':username'=>(string) $strUsername
		 		,':password'=>(string) $strPassword
		 		,':salt'=>(string) $this->_objMCP->getSalt()
		 	)
		 ));
	}
        
        /*
        * This is somewhat of a helper method to easily map auto login cookie
        * credentials to a user. When a sucessful mapping exists the user id
        * will be returned. Otherwise, null will be returned.   
        * 
        * @param sites id
        * @return int  
        */
        public function fetchUsersIdByAutoLoginCredentials($intSitesId) {
            
            // First check to make sure auto login is on.
            if($this->_objMCP->getConfigValue('auto_login_enabled') != 1) {
                return;
            }
            
            // Get the cookie name
            $strCookie = $this->_objMCP->getConfigValue('auto_login_cookie_name');
            
            // Attempt to get the cookie value
            $strValue = $this->_objMCP->getCookieValue($strCookie);
            
            //$this->debug($strValue);
            
            // When cookie does not exist we are finished
            if(!$strValue) {
                return;
            }
            
            // Otheriwse attempt to make the match           
            $arrUsers = $this->_objMCP->query(
                 'SELECT users_id FROM MCP_USERS WHERE sites_id = :sites_id AND uuid = SHA1( CONCAT(:key,users_id,:salt,created_on_timestamp) )',
                 array(
                      ':sites_id'=> (int) $intSitesId
                     ,':key'=>(string) $strValue
                     ,':salt'=>(string)  $this->_objMCP->getSalt()
                 )
            );
            
            // In the rare case that more than one is matched return nothing
            if(count($arrUsers) > 1) {
                return;
            }
            
            // Otherwise pop the first of the stack and return the users id
            $arrUser = array_pop($arrUsers);
            
            if($arrUser) {
                $this->_objMCP->addSystemStatusMessage('Auto login success');
                return $arrUser['users_id'];
            }
            
        }
        
        /*
        * Auto login provides a mechanism that allows a user to close session
        * and next time they come to the site will be logged back in automatically. This
        * will be accomplished by dropping a random string in a cookie and matching it to
        * the uuid stored in the users table. Upon intial entry into the application when a user
        * is detected with a cookie that has a correlating UUID they will automatically be logged
        * back in. I think it will best though to allow sites to turn this feature on and off for
        * security reasons. Some sites may need to be more secure than other. In which case this
        * feature can be turned on but will off by default since why it does provide an enhanced
        * user experience will make the site more susceptible to hacking. 
        * 
        * @param int users id
        * @return bool       
        */
        public function enableAutoLogin($intId) {
            
            // for now the time will be good enough
            $strKey = md5((string) time());
            
            // Grab the salt
            $strSalt = (string) $this->_objMCP->getSalt();
            
            // Config value for cookie authentication (used as cookie name)
            $strCookie = $this->_objMCP->getConfigValue('auto_login_cookie_name');
            
            try {
                
                // Set encrypted passcode
                $this->_objMCP->query(
                        'UPDATE MCP_USERS SET uuid = SHA1( CONCAT(:key,users_id,:salt,created_on_timestamp) ) WHERE users_id = :users_id',
                        array(
                             ':key'=> $strKey
                            ,':salt'=> $strSalt
                            ,':users_id'=>(int) $intId
                        )
                );
                
                // drop cookie 
                $this->_objMCP->setCookieValue($strCookie,$strKey);
                
                return true;
                
            } catch(MCPDBException $e) {
                
                throw new MCPDAOException('Unable to create auto login credentials.');
                
            }
            
        }
        
        /*
        * Disable auto account login for user. 
        */
        public function disableAutoLogin($intId) {
            
            // Config value for cookie authentication (used as cookie name)
            $strCookie = $this->_objMCP->getConfigValue('auto_login_cookie_name');
            
            try {
                
                // unset uuid
                $this->_objMCP->query(
                     'UPDATE MCP_USERS SET uuid = NULL WHERE users_id = :users_id',
                     array(
                         ':users_id'=> (int) $intId
                     )
                );
                
                // destroy cookie (set expires in past)
                $this->_objMCP->setCookieValue($strCookie,'',false,(time()-3600));
                
                return true;
                
            } catch(MCPDBException $e) {
                
                throw new MCPDAOException('Unable to disable auto login for given user.');
                
            }
            
        }
	
	/*
	* Update users data 
        * 
        * Think of this as preferences. Why I didn't call it preferences before
        * aludes me but that is essentially what user data is. Any configuration
        * per user basis. Where as, session data is lost once a users session is ended
        * user data (preferences) will not be lost. 
	* 
	* @param int users id
	* @param array user data
	*/
	public function updateUsersData($intId,$arrUserData) {
		
		return $this->_objMCP->query(
			'UPDATE MCP_USERS SET user_data = :user_data WHERE users_id = :users_id'
			,array(
				 ':user_data'=>(string) base64_encode(serialize($arrUserData))
				,':users_id'=>(int) $intId
			)
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
	* @todo: convert to variable binding
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
		
		/*
		* Start transaction 
		*/
		$this->_objMCP->begin();
		
		try {
		
			$intId = $this->_objMCP->query($strSQL);
		
			// Get user
			$user = $this->fetchById(isset($arrUser['users_id'])?$arrUser['users_id']:$intId);
		
			// Save dynamic fields
			$this->_objMCP->saveFieldValues($dynamic,$user['users_id'],'MCP_SITES',$user['sites_id']);
		
			// update cache
			$this->_setCachedUser( isset($arrUser['users_id'])?$arrUser['users_id']:$intId );
			
			/*
			* Commit transaction 
			*/
			$this->_objMCP->commit();
			
		} catch(MCPDBException $e) {
			
			/*
			* If something went wrong rollback transaction 
			*/
			$this->_objMCP->rollback();
			
			/*
			* Throw more refined/specific exception 
			*/
			throw new MCPDAOException( $e->getMessage() );
			
		}
		
		return $intId;
		
	}
	
	/*
	* Delete a user 
	* 
	* @param mix single integer value or array of integers (MCP_USERS primary key)
	* 
	* @todo: convert to variable binding
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
	
	/*
	* Get cached user
	* 
	* @param int users id
	* @return array user data
	*/
	private function _getCachedUser($intId) {
		return $this->_objMCP->getCacheDataValue("user_{$intId}",$this->getPkg());
	}
	
	/*
	* Set cached user
	* 
	* @param int users id
	* @return bool
	*/
	private function _setCachedUser($intId) {
		
		/*
		* Get most recent snapshot 
		*/
		$arrUser = $this->fetchById($intId,'*',false);
		
		/*
		* Update cache 
		*/
		return $this->_objMCP->setCacheDataValue("user_{$intId}",$arrUser,$this->getPkg());
		
	}
	
}
?>