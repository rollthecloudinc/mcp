<?php
$this->import('App.Core.Permission');

/*
*  Manage low-level permissions
* Consistent interface to interact with permission plugins 
*/
class MCPPermissionManager extends MCPResource {
    
        protected static 
    
        /*
        * User data cache to avoid mumtiple look-ups per request. 
        */
        $_userDataCache = array();

        protected
	
	/*
	* Plugins that have been loaded cache
	*/
	$_arrLoadedPlugins = array(),
                               
        /*
        * Local copy of user DAO 
        */
        $_objDAOUser;

	public function __construct(MCP $objMCP) {
		parent::__construct($objMCP);
                
                // instantiate user DAO
                $this->_objDAOUser = $this->_objMCP->getInstance('Component.User.DAO.DAOUser',array($this->_objMCP));
	}

	/*
	* Check permissions for given action
	*
	* @param str permission such as; read, delete, edit or add
	* @param str entity such as; navigation, navigation link, etc
	* @param mix entity id such as; id of nav to delete or id or vocab to add term
	* @param obj permission object
	*/
	public function getPermission($strAction,$strEntity,$arrId=null,$intUserId=null) {
	
		$permissions = array();
                
                /*
                * Super users bypass all permission checks - they can do everything. Only
                * devs should really have this permission. All other access should granted
                * through role and user permissions.  
                */
                if($this->_isSuperUser($intUserId)) {
                    
                    if($arrId !== null) {
                        foreach($arrId as $id) {
                            $permissions[$id] = array('allow'=>true);
                        }
                    } else {
                        $permissions[] = array('allow'=>true);
                    }
                
                    return $permissions;
                }
	
		/*
		* Get requested permissions
		*/
		switch($strAction) {
			case MCPPermission::READ:
				$permissions = $this->_getPlugin($strEntity)->read($arrId,$intUserId);
				break;
			
			case MCPPermission::DELETE:
				$permissions = $this->_getPlugin($strEntity)->delete($arrId,$intUserId);
				break;
			
			case MCPPermission::EDIT:
				$permissions = $this->_getPlugin($strEntity)->edit($arrId,$intUserId);
				break;
			
			case MCPPermission::ADD:
				$permissions = $this->_getPlugin($strEntity)->add($arrId,$intUserId);
				break;
				
			default:
				
		}
		
		return $permissions;
	
	}
        
        /*
        * Get list of all available plugins
        * 
        * @return array plugins  
        */
        public function getPlugins() {
            
            $arrPlugins = array();
            
            // Get all plugin files
            $arrFiles = scandir(dirname(__FILE__).'/Plugin');
            
            
            // Collect all plugins
            foreach($arrFiles as $strFile) {
                if(strpos($strFile,'.') !== 0) {
                    $arrPlugins[] = array(
                        'entity'=>str_replace('.php','',$strFile)
                    );
                    // test $this->_getPlugin(str_replace('.php','',$strFile));
                }
                
            }
            
            return $arrPlugins;
            
        }
        
        /*
        * Determine if the given user is a super user.
        * 
        * @param int users id
        * @return bool 
        */
        protected function _isSuperUser($intUserId=null) {
            
            if($intUserId === null || $intUserId == 0) {
                return false;
            }
            
            if(isset(self::$_userDataCache[$intUserId])) {
                return (bool) self::$_userDataCache[$intUserId]['super_user'];
            }
            
            self::$_userDataCache[$intUserId] = $this->_objDAOUser->fetchById($intUserId);
            return (bool) self::$_userDataCache[$intUserId]['super_user'];
            
        }

	/*
	* Get a plugin
	*
	* @param str plugin name
	* @return obj permission plugin instance
	*/
	protected function _getPlugin($strName) {
		
		if(isset($this->_arrLoadedPlugins[$strName])) {
			return $this->_arrLoadedPlugins[$strName];
		}
		
		/*
		* Import class file 
		*/
		$this->_objMCP->import("App.Resource.Permission.Plugin.$strName");
		
		/*
		* Instantiate plugin
		*/
		$className = "MCPPermission$strName";
		$this->_arrLoadedPlugins[$strName] = new $className($this->_objMCP);
		
		return $this->_arrLoadedPlugins[$strName];
		
	}

}
?>