<?php
/*
* Session data access layer
*/
$this->import('App.Core.DAO');
class MCPDAOSessionHandler extends MCPDAO {
	
	private
	
	/*
	* Time of initial request 
	*/
	$_intRequestTime
	
	/*
	* Max life for session 
	*/
	,$_intMaxLife;
	
	public function __construct($objMCP) {
		parent::__construct($objMCP);
		
		$this->_intRequestTime = time();
		$this->_intMaxLife = 5*60;
	}
	
	/*
	* Check whether a session is still active
	* 
	* @param str session_id
	* @param str session pass key
	* @param int time of request
	* @return bool true/false
	*/
	public function isActiveSession($strSID,$strPID) {
		
		if(!$strSID || !$strPID) return false;
		
		/*$strSQL = sprintf(
			"SELECT 
			      sessions_id
			   FROM
			      MCP_SESSIONS
			  WHERE
			      sid = '%s'
			    AND
			      expires_on_timestamp > FROM_UNIXTIME(%u)
			    AND
			      deleted IS NULL
			    AND
			      pid = '%s'"
			 ,$this->_objMCP->escapeString($strSID)
			 ,$this->_intRequestTime
			 ,$this->_objMCP->escapeString($strPID)
		);*/
		
		$arrRow = array_pop($this->_objMCP->query(
            'SELECT 
			      sessions_id
			   FROM
			      MCP_SESSIONS
			  WHERE
			      sid = :sid
			    AND
			      expires_on_timestamp > FROM_UNIXTIME(:expires_on_timestamp)
			    AND
			      deleted IS NULL
			    AND
			      pid = :pid'
			,array(
				 ':sid'=>(string) $strSID
				,':expires_on_timestamp'=>$this->_intRequestTime
				,':pid'=>(string) $strPID
			)
		));
		
		return $arrRow === null?false:true;
	}
	
	/*
	* Begin a new session 
	* 
	* @return array [SID,PID]
	*/
	public function beginNewSession() {
		
		$strSID = sha1(time().time());
		$strPID = sha1(time().time().'nautica');
		
		/*$strSQL = sprintf(
			"INSERT INTO MCP_SESSIONS (sid,pid,created_on_timestamp,expires_on_timestamp) VALUES ('%s','%s',NOW(),FROM_UNIXTIME(%u))"
			,$strSID
			,$strPID
			,$this->_intRequestTime+$this->_intMaxLife
		);*/
		
		$this->_objMCP->query(
			'INSERT INTO MCP_SESSIONS (sid,pid,created_on_timestamp,expires_on_timestamp) VALUES (:sid,:pid,NOW(),FROM_UNIXTIME(:expires_on_timestamp))'
			,array(
				 ':sid'=>(string) $strSID
				,':pid'=>(string) $strPID
				,':expires_on_timestamp'=>($this->_intRequestTime + $this->_intMaxLife)
			)
		);
		
		return array($strSID,$strPID);
	}
	
	/*
	* Changes session pass key every request to prevent session hijacking
	*
	* @param str SID
	* @return str new pass key
	*/
	public function changeSessionPassKey($strSID) {
		
		$strPID = sha1(time().time().'xrs564rGh1lk-9760');
		
		/*$strSQL = sprintf(
			"UPDATE MCP_SESSIONS SET pid='%s' WHERE sid='%s' AND expires_on_timestamp > FROM_UNIXTIME(%u) AND deleted IS NULL"
			,$strPID
			,$strSID
			,$this->_intRequestTime
		);*/
		
		$this->_objMCP->query(
			'UPDATE MCP_SESSIONS SET pid = :pid WHERE sid = :sid AND expires_on_timestamp > FROM_UNIXTIME(:expires_on_timestamp) AND deleted IS NULL'
			,array(
				 ':sid'=>(string) $strSID
				,':pid'=>(string) $strPID
				,':expires_on_timestamp'=>$this->_intRequestTime
			)
		);
		
		return $strPID;
	}
	
	/*
	* Fetch active sessions data by session id
	*
	* @param str session id
	* @param str session password
	* @return arr session data
	*/
	public function fetchActiveSessionById($strId) {
		
		/*$strSQL = sprintf(
			"SELECT
			      s.session_data
			   FROM
			      MCP_SESSIONS s
			  WHERE
			      s.sid = '%s'
			    AND
			      s.expires_on_timestamp > FROM_UNIXTIME(%u)
			    AND
			      s.deleted IS NULL"
			,$this->_objMCP->escapeString($strId)
			,$this->_intRequestTime
		);*/
		
		return array_pop($this->_objMCP->query(
            'SELECT
			      s.session_data
			   FROM
			      MCP_SESSIONS s
			  WHERE
			      s.sid = :sid
			    AND
			      s.expires_on_timestamp > FROM_UNIXTIME(:expires_on_timestamp)
			    AND
			      s.deleted IS NULL'
			,array(
				 ':sid'=>(string) $strId
				,':expires_on_timestamp'=>$this->_intRequestTime
			)
		));
		
	}
	
	/*
	* Save sessions data to database
	*
	* @param str session id
	* @param str serialized session data
	*/
	public function saveSessionData($strId,$strData) {
		
		/*$strSQL = sprintf(
			"INSERT IGNORE INTO MCP_SESSIONS (sid,users_id,session_data,created_on_timestamp,expires_on_timestamp) VALUES ('%s',%s,'%s',NOW(),FROM_UNIXTIME(%u)) ON DUPLICATE KEY UPDATE session_data = VALUES(session_data),expires_on_timestamp = VALUES(expires_on_timestamp),users_id = VALUES(users_id)"
			,$this->_objMCP->escapeString($strId)
			,$this->_objMCP->getUsersId() === null?'NULL':$this->_objMCP->escapeString($this->_objMCP->getUsersId())
			,$this->_objMCP->escapeString($strData)
			,$this->_intRequestTime+$this->_intMaxLife
		);*/
		
		return $this->_objMCP->query(
			"INSERT IGNORE INTO MCP_SESSIONS (sid,users_id,session_data,created_on_timestamp,expires_on_timestamp) VALUES (:sid,:users_id,:session_data,NOW(),FROM_UNIXTIME(:expires_on_timestamp)) ON DUPLICATE KEY UPDATE session_data = VALUES(session_data),expires_on_timestamp = VALUES(expires_on_timestamp),users_id = VALUES(users_id)"
			,array(
				 ':sid'=>(string) $strId
				,':users_id'=>$this->_objMCP->getUsersId()
				,':session_data'=>$strData
				,':expires_on_timestamp'=> ($this->_intRequestTime + $this->_intMaxLife)
			)
		);
	}
	
	/*
	* Destroy an active session by id
	*
	* @param str session id
	*/
	public function destroySessionById($strId) {
		
		/*$strSQL = sprintf(
			"UPDATE MCP_SESSIONS SET deleted = NOW() WHERE sid = '%s'"
			,$this->_objMCP->escapeString($strId)
		);*/
		
		return $this->_objMCP->query(
			'UPDATE MCP_SESSIONS SET deleted = NOW() WHERE sid = :sid'
			,array(
				':sid'=>(string) $strId
			)
		);
		
	}
	
	/*
	* Soft delete all sessions past their expiration time
	*/
	public function expireSessionsPastExpiration() {
		
		/*$strSQL = sprintf(
			'UPDATE MCP_SESSIONS SET deleted=NOW() WHERE expires_on_timestamp < FROM_UNIXTIME(%u) AND deleted IS NULL'
			,time()
		);*/
		
		$this->_objMCP->query(
			'UPDATE MCP_SESSIONS SET deleted = NOW() WHERE expires_on_timestamp < FROM_UNIXTIME(:expires_on_timestamp) AND deleted IS NULL'
			,array(
				':expires_on_timestamp'=>time()	
			)
		);
		
	}

}
?>