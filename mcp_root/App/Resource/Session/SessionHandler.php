<?php
/*
* Manages sessions
*/
$this->import('App.Core.Resource');
class MCPSessionHandler extends MCPResource {

	private static 
	
	/*
	* Session Handler Singleton
	*/
	$_objSessionHandler;
	
	
	private
	
	/*
	* DAO Session Handler
	*/
	$_objDAOSessionHandler;

	public function __construct(MCP $objMCP) {
		parent::__construct($objMCP);
		$this->_init();
	}
	
	/*
	* Create new session handler instance
	*
	* @return obj SessionHandler
	*/
	public static function createInstance(MCP $objMCP) {
		if(self::$_objSessionHandler === null) {
			self::$_objSessionHandler = new MCPSessionHandler($objMCP);
		}
		return self::$_objSessionHandler;
	}

	public function _init() {	
		
		/*
		* Create instance of Session Handler DAO
		*/
		$this->_objDAOSessionHandler = $this->_objMCP->getInstance('App.Resource.Session.DAO.DAOSessionHandler',array($this->_objMCP));
		
		/*
		* Validate active session credentials
		*/
		$strSID = $this->_objMCP->getCookieValue('SID',true);
		$strPID = $this->_objMCP->getCookieValue('PID',true);
		
		if($this->_objDAOSessionHandler->isActiveSession($strSID,$strPID) === false) {			
			$strSID = $this->_registerNewSession();
		} else if($this->_objMCP->changeSessionPassKey() === true) {
			$this->_changeSessionPasskey($strSID);
		}

		/*
		* Set the current session id 
		*/
		session_id($strSID);
		
		/*
		* Reroute default session handling methods
		*/
		session_set_save_handler(
			array($this,'_open')
			,array($this,'_close')
			,array($this,'_read')
			,array($this,'_write')
			,array($this,'_destroy')
			,array($this,'_gc')
		);	
	}
	
	/*
	* Register a new session
	* 
	* @return str SID
	*/
	private function _registerNewSession() {
		list($strSID,$strPID) = $this->_objDAOSessionHandler->beginNewSession();
		
		/*
		* !Important: Domain cookie - avoid conflicting cookies on multiple sites
		*/
		$this->_objMCP->setCookieValue('SID',$strSID,true,0,true);
		$this->_objMCP->setCookieValue('PID',$strPID,true,0,true);
		return $strSID;		
	}
	
	/*
	* Cycles the current sessions pass key every request
	* to safe gaurd against session hijacking.
	* 
	* @param str SID
	*/
	private function _changeSessionPassKey($strSID) {
		$strPID = $this->_objDAOSessionHandler->changeSessionPassKey($strSID);
		
		/*
		* !Important: Domain cookie - avoid conflicting cookies on multiple sites
		*/
		$this->_objMCP->setCookieValue('PID',$strPID,true,0,true);
	}
	
	public function _open($strSavePath,$strSessionName) {	
		global $sessSavePath;		
		$sessSavePath = $strSavePath;		
		return true;
	}
	
	public function _close() {
		return true;
	}
	
	/*
	* Read a sessions data
	*/
	public function _read($intId) {
		$arrSession = $this->_objDAOSessionHandler->fetchActiveSessionById($intId);
		if($arrSession) return $arrSession['session_data'];
	}
	
	/*
	* Write data to a session
	*/
	public function _write($intId,$arrData) {
		return $this->_objDAOSessionHandler->saveSessionData($intId,$arrData);
	}
	
	/*
	* Destroy an active session
	*/
	public function _destroy($intId) {
		return $this->_objDAOSessionHandler->destroySessionById($intId);
	}
	
	/*
	* Destroy all expired sessions
	*/
	public function _gc($maxlifetime) {
		return $this->_objDAOSessionHandler->expireSessionsPastExpiration();
	}
	
	/*
	* Get a session value
	*
	* @return mix data
	*/
	public function getDataValue($strName,$boolGlobal=false) {	
		$strIndex = 'k'.($boolGlobal === true?0:$this->_objMCP->getSitesId());
		return isset($_SESSION[$strIndex],$_SESSION[$strIndex][$strName])?$_SESSION[$strIndex][$strName]:null;
	}
	
	/*
	* Set session value
	*
	* @param str values name
	* @param mix value
	* @param bool global value
	*/
	public function setDataValue($strName,$mixValue,$boolGlobal=false) {
		$strIndex = 'k'.($boolGlobal === true?0:$this->_objMCP->getSitesId());
		$_SESSION[$strIndex][$strName] = $mixValue;
	}
	
}
?>