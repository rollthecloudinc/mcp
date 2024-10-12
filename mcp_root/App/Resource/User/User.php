<?php
/*
* manage current system user
*/
$this->import('App.Core.Resource');
class MCPUser extends MCPResource {

	private static
	
	/*
	* Singleton object
	*/
	$_objUser;
	
	private
	
	/*
	* User DAO 
	*/
	$_objDAOUser
	
	/*
	* Current users id
	*/
	,$_intUsersId
	
	/*
	* Unserialized data associated with
	* the current user.
	*/
	,$_arrData;

	public function __construct(MCP $objMCP) {
		parent::__construct($objMCP);
		$this->_init();
	}
	
	public static function createInstance(MCP $objMCP) {
		if(self::$_objUser === null) {
			self::$_objUser = new MCPUser($objMCP);
		}
		return self::$_objUser;
	}
	
	private function _init() {
		
		/*
		* Fetch the User DAO
		*/
		$this->_objDAOUser = $this->_objMCP->getInstance('Component.User.DAO.DAOUser',array($this->_objMCP));
	
		/*
		* If a user exists
		*/
		$this->_intUsersId = $this->_objMCP->getSessionValue('users_id',true);
                
                /*
                * When user exists no need to go any further. Otherwise, check to see if
                * auto login has been turned on. If is has attempt to login user automatically.  
                */
                if(!$this->_intUsersId) {
                    $this->_intUsersId = $this->_objDAOUser->fetchUsersIdByAutoLoginCredentials($this->_objMCP->getSitesId());
                    if($this->_intUsersId) {
                        $this->_objMCP->setSessionValue('users_id',$this->_intUsersId,true);
                    }
                }
		
		if(!$this->_intUsersId) return;
		
		/*
		* Fetch users information
		*/
		$arrRow = $this->_objDAOUser->fetchById($this->_intUsersId);
		
		/*
		* Set users data
		*/
		$this->_arrData = unserialize(base64_decode($arrRow['user_data']));
		
	}
	
	/*
	* Get the current users id
	*
	* @return int users id
	*/
	public function getUsersId() {
		return $this->_intUsersId;
	}
	
	/*
	* Authenticate client as the current user. For now this
	* uses a simple SHA() encryption technique. In the future
	* probably a good idea to add a salt.
	* 
	* @param str username
	* @param str password
        * @param bool can be used to enable auto login via cookie 
	* @return bool
	*/
	public function authenticate($strUsername,$strPassword,$boolAuto=false) {
		/*
		* Attempt to match login credentials to user for current site
		*/
		$intSitesId = $this->_objMCP->getSitesId();
		$arrRow = $this->_objDAOUser->fetchUserByLoginCredentials($strUsername,$strPassword,$intSitesId);
		 
		if($arrRow === null) {
			return false;
		}
                
                // enable or disable auto login
                if($boolAuto === true) {
                    $this->_objDAOUser->enableAutoLogin($arrRow['users_id']);
                } else {
                    $this->_objDAOUser->disableAutoLogin($arrRow['users_id']);
                }
		
		/*
		* Set the sessions user id
		*/
		$this->_objMCP->setSessionValue('users_id',$arrRow['users_id'],true);
		$this->_init();
		
		return true;
		 
	}
	
	/*
	* Logout the current user
	*/
	public function logout() {
                $this->_objDAOUser->disableAutoLogin($this->_intUsersId);
		$this->_objMCP->setSessionValue('users_id',null,true);
		$this->_init();
		return true;
	}
	
	/*
	* Get a value stored for a user
	*
	* @param str value name
	* @param bool global value
	* @return mix data value
	*/
	public function getDataValue($strName,$boolGlobal=false) {	
		$strIndex = 'k'.($boolGlobal === true?0:$this->_objMCP->getSitesId());
		return isset($this->_arrData[$strIndex],$this->_arrData[$strIndex][$strName])?$this->_arrData[$strIndex][$strName]:null;
	}
	
	/*
	* Set a data value to store for user. Similar to a session
	* but this data is kept in tact and doesn not expire
	* at the end of session like session data does.
	*
	* @param str value name
	* @param mix data value
	* @param bool global 
	*/
	public function setDataValue($strName,$mixValue,$boolGlobal=false) {
		$strIndex = 'k'.($boolGlobal === true?0:$this->_objMCP->getSitesId());
		$this->_arrData[$strIndex][$strName] = $mixValue;
	}
	
	/*
	* Saves user data to database
	*/
	public function saveUserData() {
		if(!$this->_intUsersId) return;
		$this->_objDAOUser->updateUsersData($this->_intUsersId,$this->_arrData);
	}

}
?>