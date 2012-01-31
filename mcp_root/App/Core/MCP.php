<?php

/*
* The bread and butter
* 
* Framework API Facade
* 
* All MCP Resources may access this API through
* referencing the objMCP property. 
*
*/
class MCP {
	
	// permission contsants copy - easier to access from here, less code
	const
	 READ 		= 1
	,ADD 		= 2
	,DELETE 	= 3
	,EDIT 		= 4
	,PURGE		= 5
              
        // Name of session value that stores assets
        ,SESSION_ASSET_KEY = 'mcp_assets';

	private
	
	/*
	* MCP Singleton object
	*/
	static $_objMCP;

	private
	
	/*
	* Console object
	*/
	$_objConsole
	
	/*
	* Import Object
	*/
	,$_objImport
	
	/*
	* Request Object
	*/
	,$_objRequest
	
	/*
	* Template Singleton
	*/
	,$_objTemplate
	
	/*
	* User interface drawing library (replaces drawing API) 
	*/
	,$_objUI
	
	/*
	* Session Handler
	*/
	,$_objSessionHandler
	
	/*
	* Cookie Manager
	*/
	,$_objCookieManager
	
	/*
	* Event handler 
	*/
	,$_objEventHandler
	
	/*
	* DB Object
	*/
	,$_objDB
	
	/*
	* Current user object
	*/
	,$_objUser
	
	/*
	* Current site object
	*/
	,$_objSite
	
	/*
	* Permission handler 
	*/
	,$_objPermissionHandler
	
	/*
	* Config object
	*/
	,$_objConfig
	
	/*
	*  Cache handling layer
	*/
	,$_objCacheHandler
	
	/*
	* Path to the master template
	*/
	,$_strMasterTemplatePath
	
	/*
	* Path to plain text email master template 
	*/
	,$_strEmailPlainTextMasterTemplatePath
	
	/*
	* Path to HTML email master template 
	*/
	,$_strEmailHTMLMasterTemplatePath
	
	/*
	* data to outputed at end of request. Mainly used for debugging purpose
	* wihout disrupting the rest of the application. 
	*/
	,$_arrCapture
	
	/*
	* Request output to dumped to browser on MCP deconstruct 
	*/
	,$_strRequest
	
	/*
	* main Simple XML element 
	*/
	,$_objXMLMain
	
	/*
	* Modules Simple XML element 
	*/
	,$_objXMLModules
	
	/*
	* Injects instantiated requested resource with this current path
	*/
	,$_strInstancePkg = ''
	
	/*
	* Site database enrypted data salt 
	*/
	,$_strSalt
                
        /*
        * Meta data associated with page request. 
        */
        ,$_arrMetaData = array()
                
        /*
        * Breadcrumbs 
        */
        ,$_arrBreadcrumbs = array()
                
        /*
        * JavaScript and CSS Assets 
        */        
        ,$_arrAssets = array(
             'css'    => array()
            ,'js'     => array()
        )
	
	/*
	* List of system messages 
	* 
	* There are messages that will be displayed to the user every request. Messages
	* are useful for actions that have failure points or possibly making users
	* aware that they have not filled out all required fields or that an action
	* has sucessfully occured.
	* 
	* Use the below methods to add messages 
	* 
	* addSystemErrorMessage($strMessage)
	* addSystemWarningMessage($strMessage)
	* addSystemStatusMessage($strMessage);
        * debug() - quick utility for debug messages
	* 
	* System messages DO NOT persist between requests. System messages
	* are only available for the duration of a request.
	*/
	,$_arrSystemMessages = array(
		// errors
		 'error'			=> array()
	
		// warnings
		,'warning'			=> array()
		
		// status message
		,'status'			=> array()
	),
        
        /*
        * Messages that will appear above all content when in a none production
        * environment. This can be used to easily dump variables for inspection
        * in the software development phase. Unlike system messages this should only
        * be used for inspecting data during development since all debug infromation
        * will be ignored (hidden) in production environment.
        */
        $_arrDebugMessages = array();
	
	/*
	* Create MCP instance
	*
	* @param object Console
	* @oaram object import
	* @param object Request
	* @return object MCP
	*/
	public static function createInstance(Console $objConsole,Import $objImport,Request $objRequest) {
		if(self::$_objMCP === null) {
			self::$_objMCP = new MCP($objConsole,$objImport,$objRequest);
		}
		return self::$_objMCP;
	}
	
	private function __construct(Console $objConsole,Import $objImport,Request $objRequest) {
		$this->_objConsole = $objConsole;
		$this->_objImport = $objImport;
		$this->_objRequest = $objRequest;
		$this->_arrCapture = array();
		
		$this->_init();
	}
	
	/*
	* Initiate MCP
	*/
	private function _init() {
	
		/*
		* Primary low-level application resources
		*/
		$this->import('App.Resource.Template');
		$this->import('App.Resource.Module');
		$this->import('App.Resource.Site');
		$this->import('App.Resource.User');
		$this->import('App.Resource.Config');
		$this->import('App.Lib.UI.Manager');
		//$this->import('App.Resource.ACL');
		
		// Exception classes
		$this->import('App.Core.Exception.Permission');
		$this->import('App.Core.Exception.DAO');
		$this->import('App.Core.Exception.DB');
		
		/*
		* Load main XML config 
		*/
		$this->_objXMLMain = simplexml_load_file(CONFIG.'/Main.xml');
		
		/*
		* Load modules XML config 
		*/
		$this->_objXMLModules = simplexml_load_file(ROOT.'/App/Config/Modules.xml');
		
		/*
		* Get base info to establish database connection
		*/
		$objDBConfig = array_pop($this->_objXMLMain->xpath("//site[@id='{$this->getSitesId()}']/db"));
		
		/*
		* Create database object and connect
		* 
		* Different adapters may be used by changing the adapter XML config value. The value
		* should reflect the full path to the adpater. The adapter is required to implement
		* MCPDB and extend MCPResource. This is required so all the adapters can be interchanged.
		* Default adapters are stored inside App.Resource.DB. Custom adapters should not be placed
		* here. Instead you should dedicate a pkg or site directory to them so they aren't lost
		* when updating to new versions of MCP.
		*/
		$this->_objDB = $this->getInstance((string) $objDBConfig->adapter,array($this));
		$this->_objDB->connect(
			 (string) $objDBConfig->host
			,(string) $objDBConfig->user
			,(string) $objDBConfig->pass
			,(string) $objDBConfig->db
		);
		
		/*
		* Create MCP event handler
		*/
		$this->_objEventHandler = $this->getInstance('App.Resource.Event.EventHandler',array($this));
		
		/*
		* Set the current site
		*/
		$this->_objSite = MCPSite::createInstance($this);
		
		/*
		* Initiate cookie manager - required for proper session handling
		*/
		$this->_objCookieManager = $this->getInstance('App.Resource.Cookie.CookieManager',array($this));
		
		/*
		* Initiate cache handler
		*/
		$this->import('App.Resource.Cache.CacheHandler');
		$this->_objCacheHandler = MCPCacheHandler::createInstance($this);
		
		/*
		* Initiate session handler
		*/
		$this->import('App.Resource.Session.SessionHandler');
		$this->_objSessionHandler = MCPSessionHandler::createInstance($this);
		
		/*
		* Required for garbage collector to function appropriatly
		*/
		ini_set('session.gc_probability', 100);
		ini_set('session.gc_divisor', 100);
		
		/*
		* Begin session once session handler has been created
		*/
		session_start();
		
		/*
		* Create config instance 
		*/
		$this->_objConfig = MCPConfig::createInstance($this);
				
		/*
		* Create user instance
		*/
		$this->_objUser = MCPUser::createInstance($this);
		
		/*
		* Create permission handler
		*/
		$this->_objPermissionHandler = $this->getInstance('App.Resource.Permission.PermissionManager',array($this));
		
		/*
		* Create Template singleton
		*/
		$this->_objTemplate = MCPTemplate::createInstance($this);
		
		/*
		* Instatiate UI drawing library 
		*/
		$this->_objUI = new \UI\Manager(ROOT.'/App/Lib/UI/Element');
                
                /*
                * Register some UI plugin paths. This will probably need to crawl
                * all component directories eventually to automatically discover
                * them. However, for testing integration at this point hard coding
                * them seems fine.    
                */
                $this->_objUI->registerPath(ROOT.'/Component/Node/Theme');
		
		/*
		* Assign default master template path
		*/
		//$this->_strMasterTemplatePath = 'Site'.DS.'*'.DS/*.'Template'.DS*/.'master.php';
		$this->_strMasterTemplatePath = $this->getConfigValue('site_master_template');
		
		/*
		* Assign default email master template path 
		*/
		$this->_strEmailHTMLMasterTemplatePath = $this->getConfigValue('site_email_html_master_template');
		
		/*
		* Assign default email plain text master template path 
		*/
		$this->_strEmailPlainTextMasterTemplatePath = $this->getConfigValue('site_email_plain_text_master_template');
		
		/*
		* Execute login
		*/
		$this->executeLogin();
		
	}
	
	/*
	* Execute a module
	* 
	* Module now allows overrides by prefixing module with unique prefix for site. This
	* makes it possible to override the master module for example by defining a module
	* Sitename_Module. This also enables the ability to extend or add to an existing
	* module without naming conflicts.
	*
	* @param str absolute module package path
	* @param array
	* [@param] str site location of template file * means current site
	* [@param] array arguments to be passed to module upon creation
	* [@param] array event listeners for module
	* [@param] int attempt dictates whether site override execution will be attempted
	*/
	public function executeModule($strPkg,$arrArgs=null,$strTplPkg='Site.*.Template',$arrModuleArgs=null,$arrListener=null,$intTry=1) {
		
		$this->import(str_replace('*',$this->getSite(),$strPkg),false);
		
		$intPos = strrpos($strPkg,'.');	
			
		$strModuleClass = substr($strPkg,$intPos+1);
		$strReserveModuleClass = $strModuleClass;
		
		/*
		* Added to allow overrides of modules via site prexix for class.
		* This seems to work for now, but may not persist well into the future.
		*/
		if($intTry == 1 && strpos($strPkg,'Site.') === 0) {
			$strModuleClass = "{$this->_objSite->getModulePrefix()}_$strModuleClass";
		}
		
		// Route to default module to resolve route
		if(!class_exists("MCP$strModuleClass")) { // Add MCP prefix
			// old return $this->executeComponent('Component.Navigation.Module.Router',array_merge(array($strReserveModuleClass),$arrArgs),'Component.Navigation.Template');
                        return $this->executeComponent('Component.Route.Module.Router',array_merge(array($strReserveModuleClass),$arrArgs),'Component.Route.Template');
		}
		
		// Create module
		$objModule = $this->getInstance(
			str_replace('*',$this->getSite(),$strPkg)
			,$arrModuleArgs === null?array($this):array_merge(array($this),$arrModuleArgs)
			,$strModuleClass
		);
		
		// Execute module
		$strDisplayTemplate = $objModule->execute(empty($arrArgs)?array():$arrArgs);
		
		// Load modules template data into Template object
		$objModule->loadTemplateData();
		
		// The / character triggers absolute path to template mode
		if(strpos($strDisplayTemplate,'/') === 0) {
			return $this->fetch(ROOT.str_replace('*',$this->getSite(),$strDisplayTemplate),$objModule);
		}
		
		// Base template path location
		$strTplBasePath = str_replace(array('*','.'),array($this->getSite(),DS),$strTplPkg);
		
		// Get contents for modules template
		return $this->fetch(ROOT."/$strTplBasePath/$strDisplayTemplate",$objModule);
		
	}
	
	/*
	* Component and module are essentially one of the same. The functional difference is that
	* a components name is a derivitive of its relative path. For example, a component residing
	* inside App.Resource.Content.Modue.View should have a class name of ContentView rather than
	* View (like a regular module).
	* 
	* @param str absolute component path
	* @param array component execute arguments
	* @param str site location of template file * means current site
	* [@param] array arguments to be passed to component upon creation
	* [@param] array event listener [[object,method],evt]
	*/
	public function executeComponent($strPkg,$arrArgs=null,$strTplPkg=null,$arrModuleArgs=null,$arrListener=null) {
		
		$this->import(str_replace('*',$this->getSite(),$strPkg),false);
		
		$strComponentClass = str_replace(array('.Module.',PKG),'',substr($strPkg,strrpos(substr($strPkg,0,(strpos($strPkg,'Module')-1)),'.')));
		
		// Configure default template path 
		$strTplPkg = str_replace('.Module','.Template',(empty($strTplPkg)?substr($strPkg,0,strrpos($strPkg,PKG)):$strTplPkg));
		
		try {	
			$objComponent = $this->getInstance(
				str_replace('*',$this->getSite(),$strPkg)
				,$arrModuleArgs === null?array($this):array_merge(array($this),$arrModuleArgs)
				,$strComponentClass
			);
		
			// subscribe any possible event listeners
			if($arrListener !== null) {
				$this->subscribe($objComponent,array_pop($arrListener),array_shift($arrListener));
			}
		
			// Execute component
			$strDisplayTemplate = $objComponent->execute(empty($arrArgs)?array():$arrArgs);	
		} catch(MCPPermissionException $objException) {
                    
			/*
			* Permission denied "module" 
			*/
			$objComponent = $this->getInstance(
				// 'Component.Navigation.Module.Router' old
                                'Component.Route.Module.Router'
				,array($this)
                                ,'RouteRouter'
				//,'NavigationRouter' old
			);
			
			$strDisplayTemplate = '/'.str_replace(PKG,DS,$objComponent->getPkg('../..')).'/Template/'.$objComponent->execute(array('Permission Denied',$objException->getMessage()));
			
		}
		
		// Load component template data into Template object
		$objComponent->loadTemplateData();
		
		// The / character triggers absolute path to template mode
		if(strpos($strDisplayTemplate,'/') === 0) {
			return $this->fetch(ROOT.str_replace('*',$this->getSite(),$strDisplayTemplate),$objComponent);
		}
		
		// Base template path location 
		$strTplBasePath = str_replace(array('*','.'),array($this->getSite(),DS),$strTplPkg);
		
		// Site component template override - Not ready for prime time just yet...
		/*if( strpos($strTplBasePath,'Component/') === 0 && file_exists(ROOT."/Site/{$this->getSite()}/$strTplBasePath/$strDisplayTemplate") ) {
			return $this->fetch(ROOT."/Site/{$this->getSite()}/$strTplBasePath/$strDisplayTemplate",$objComponent);
		}*/
		
		// Get contents for components template
		return $this->fetch(ROOT."/$strTplBasePath/$strDisplayTemplate",$objComponent);
			
		
	}
	
	/*
	* Gateway to user interface drawing API.
	* 
	* This API may be built and extended to supporting rendering
	* on common elements such as; checkboxes, dates, etc or even
	* application level entities such as; nodes, etc.
	* 
	* Advantages of using the UI Library are full portability
	* to other formats such as; HTML5, HTML6, etc. So long as
	* a render is added for the format an entire application or site
	* can be converted to a new format w/o touching MCP code, just
	* he UI elements.
	* 
	* Other advantages are guarenteed consistent, valid code. Never forget
	* to close or accidental include an important/required attribute. The UI Manager
	* will scream (throw an exception) at the application level if things such as attributes
	* are expected/required yet not included when rendering elements.
	* 
	* Other advantages include the ability to build complex, reusable snippets
	* of HTML for items such as pagers, data grids, etc. 
	* 
	* Lastly, the UI library will be portable to JavaScript. Plans are underway
	* to create a JQuery method $.mcp.ui() that provides the same interface
	* as the one here, but will return a node or HTML string. Making
	* it possible build out elements in JavaScript with choice of a node
	* or HTML string.
	* 
	* Its not wront to not use this library, but it will make future upgrades
	* and bug fixing much less tedious and prone to bugs. That being because
	* code will only need to be changed in one place vs. possibly several.
	* 
	* @param str name of package (namespace based - use . to separate namespace)
	* @param array elements options name=>value pairs
	* @return str element rendered
	*/
	public function ui($strName,$arrOptions) {
		return $this->_objUI->draw($strName,$arrOptions);
	}
	
	/*
	* Session pass key will only be changed for normal requests. Requests for css, images and javascript
	* will not change the pass key to avoid conflicts. This is a type of "pass-thru" to avoid the problems
	* with the session handler for simultaneous requests.
	* 
	* @return bool true/false
	*/
	public function changeSessionPassKey() {
		strpos($_SERVER['SCRIPT_NAME'],'index.php') !== false?true:false;		
	}
	
	/*
	* Determine whether request is being ued to access DAO method directly
	* 
	* @return bool
	*/
	public function isDAORequest() {
		return strcmp(basename($_SERVER['SCRIPT_NAME']),'dao.php') == 0?true:false;
	}
	
	/*
	* Get absolute path to image cache directory
	* 
	* @return str image cache path
	*/
	public function getImageCachePath() {
		return CACHE.'/images';
	}
	
	/*
	* Get absolute path to uploaded image files
	* 
	* @return str image file path
	*/
	public function getImageFilePath() {
		return FILES.DS.'images';
	}
	
	/*
	* Get absolute path to uploaded normal files 
	* 
	* @return str normal file image path
	*/
	public function getNormalFilePath() {
		return FILES.DS.'files';
	}
	
	/*
	* kick-off application request 
	*/
	public function kick_off($strEntry) {
			
		$this->_strRequest = $this->executeModule('Site.*.Module.Master',array($_SERVER['SCRIPT_NAME']));
		
		
	}
	
	/*
	* Executes default login component
	*
	* @param str login template pkg.
	*/
	public function executeLogin($strTpl='Component.User.Template') {
		
		/*
		* This needs to be done manually to avoid the first login module
		* not recognizing the second upon logging out. 
		*/
		
		$objLogin = $this->getInstance('Component.User.Module.Login',array($this),'UserLogin');
		$strTpl = ROOT."/Component/User/Template/{$objLogin->execute(array())}";
		$objModule = $objLogin;
		
		if($this->getUsersId() !== null) {
			
			$objLogout = $this->getInstance('Component.User.Module.Logout',array($this),'UserLogout');
			$strTpl = ROOT."/Component/User/Template/{$objLogout->execute(array())}";
			$objModule = $objLogout;
			
			if($this->getUsersId() === null) {
				$objLogin->redo();
				$strTpl = ROOT."/Component/User/Template/{$objLogin->execute(array())}";
				$objModule = $objLogin;
			}
			
		}
		
		$objModule->loadTemplateData();
		$this->assign('LOGIN',$this->fetch($strTpl,$objModule));
			
	}
	
	/*
	* Get the current site directory name
	*
	* @return str site directory name
	*/
	public function getSite() {
	
		if($this->_objSite->getSitesId() === null) {
			$this->triggerError('A site has not been defined.');
		}
	
		return $this->_objSite->getDirectory();
	}
	
	/*
	* Get the current sites id
	*
	* @return int sites id
	*/
	public function getSitesId() {
		/*
		* When site initally makes this request the site id is resolved by looking to the main
		* config file and matching the domain. From there on out it is pulled from the site object
		* itself rather than running the xpath query everytime the site id is requested. 
		*/
		if($this->_objSite === null) {
			$objXMLSite = array_pop($this->_objXMLMain->xpath("//site[domain='{$this->getDomain()}']"));
			return (int) $objXMLSite['id'];
		} else {
			return $this->_objSite->getSitesId();
		}
	}
	
	/*
	* Get sites salt 
	* 
	* @return str encryption salt
	*/
	public function getSalt() {
		
		/*
		* Don't think its necessary or appropriate to store this in memory until its needed 
		*/
		if($this->_strSalt === null) {
			$this->_strSalt = (string) array_pop($this->_objXMLMain->xpath("//site[@id='{$this->getSitesId()}']/salt"));
		}
		return $this->_strSalt;	
	}
	
	/*
	* Get path to folder containing sites CSS files 
	* 
	* @return str css folder
	*/
	public function getCSSFolder() {
		return ROOT.DS.str_replace('*',$this->getSite(),$this->getConfigValue('site_css_folder'));
	}
	
	/*
	* Get path to folder containing sites JavaScript files 
	* 
	* @return str JavaScript folder
	*/
	public function getJSFolder() {
		return ROOT.DS.str_replace('*',$this->getSite(),$this->getConfigValue('site_js_folder'));
	}
	
	/*
	* Get path to folder containing sites image files 
	* 
	* @return str image folder
	*/
	public function getImgFolder() {
		return ROOT.DS.str_replace('*',$this->getSite(),$this->getConfigValue('site_img_folder'));
	}
	
	/*
	* Get path to folder containing sites downloadable files 
	* 
	* @return str download folder
	*/
	public function getDownloadFolder() {
		return ROOT.DS.str_replace('*',$this->getSite(),$this->getConfigValue('site_download_folder'));
	}
	
	/*
	* Get path to folder that mirrors www directory. All files in this folder
	* are accessible through http.
	* 
	* For example, if you don't need dynamic CSS capabilities stylesheets may be placed
	* inside this folder and referenced through public.php entry point.
	* 
	* @return str public folder
	*/
	public function getPublicFolder() {
		return ROOT.DS.str_replace('*',$this->getSite(),$this->getConfigValue('site_public_folder'));
	}
	
	/*
	* Get a configuration setting value 
	* 
	* @param str config value name
	* @return str config setting value
	*/
	public function getConfigValue($strName) {
		return $this->_objConfig->getConfigValue($strName);
	}
	
	/*
	* Set a configuration value
	* 
	* @param str config setting name
	* @param str config setting value
	*/
	public function setConfigValue($strName,$strValue) {
		return $this->_objConfig->setConfigValue($strName,$strValue);
	}
	
	/*
	* Set multiple configuration values at once
	* 
	* @param array config (key value pairs)
	*/
	public function setMultiConfigValues($arrConfig) {
		return $this->_objConfig->saveMultiConfig($arrConfig);
	}
	
	/*
	* Get all config values for current site
	* 
	* @return array config values
	*/
	public function getEntireConfig() {
		return $this->_objConfig->getEntireConfig();
	}
	
	/*
	* Get global config form schema 
	* 
	* @return array config schema
	*/
	public function getConfigSchema() {
		return $this->_objConfig->getConfigSchema();
	}
	
	/*
	* Get data for the current logged in user
	*
	* @return int users id
	*/
	public function getUsersId() {
		return $this->_objUser->getUsersId();
	}
	
	/*
	* Check permissions for given action
	*
	* @param str permission such as; read, delete, edit or add
	* @param str entity such as; navigation, navigation link, etc
	* @param mix entity id such as; id of nav to delete or id or vocab to add term
        * @param int users id 
	* @return arr permissions
	*/
	public function getPermission($strAction,$strEntity,$mixId=null,$intUserId=null) {
		
		/*
		* get permissions 
		*/
		$perms = $this->_objPermissionHandler->getPermission(
			 $strAction
			,$strEntity
			,$mixId !== null?is_array($mixId)?$mixId:array($mixId):null
                        ,$intUserId !== null?$intUserId:$this->getUsersId()
		);
		
		/*
		* Send back permissions 
		*/
		return is_array($mixId)?$perms:array_pop($perms);
	}
        
        /*
        * Get all plugin permission definitions
        * 
        * @return array permission plugin definitions  
        */
        public function getPermissionPlugins() {
            return $this->_objPermissionHandler->getPlugins();
        }
	
	/*
	* Login user
	*
	* @param str username
	* @param str password
        * @param bool can be used to enable auto login based on cookie
	* @return bool true/false
	*/
	public function loginUser($strUsername,$strPassword,$boolEnableAuto=false) {
		return $this->_objUser->authenticate($strUsername,$strPassword,$boolEnableAuto);
	}
	
	/*
	* Log the current user out
	* @return bool success/failure
	*/
	public function logoutUser() {
		return $this->_objUser->logout();
	}
	
	/*
	* The current domain name
	* 
	* @return str domain
	*/
	public function getDomain() {
		$arrServerData = $this->_objRequest->getServerData();
		return $arrServerData['HTTP_HOST'];
	}
	
	/*
	* Get base url to current site
	*
	* @param bool append entry point (index.php)
	* @return str base url
	*/
	public function getBaseUrl($boolEntry=true) {
		$arrServerData = $this->_objRequest->getServerData();
		return 'http://'.$arrServerData['HTTP_HOST'].($boolEntry === true?'/index.php':'');
	}
	
	/*
	* Get the base path
	*
	* @return str base path to current module
	*/
	public function  getBasePath() {
	
		$strRequestModule = $this->getModule();
		$arrRequestArgs = $this->getArgs();
		
		return $this->getBaseUrl().'/'.$strRequestModule.'/'.(empty($arrRequestArgs)?'':implode('/',$arrRequestArgs).'/');
	}
	
	/*
	* Get name of the requested module
	*
	* @return str requested modules name
	*/
	public function getModule() {
		return $this->_objRequest->getRequestModule();
	}
	
	/*
	* Override the current module request
	* 
	* @param str module
	*/
	public function setModule($strModule) {
		$this->_objRequest->setRequestModule($strModule);
	}
	
	/*
	* Get request arguments.
	* Request arguments are all strings in URL following
	* the module name separated by a slash IE. /Product/5/Page/6
	*
	* @return array request arguments
	*/
	public function getArgs() {
		return $this->_objRequest->getRequestArgs();
	}
	
	/*
	* Create instance of class
	* 
	* Application resources SHOULD NEVER be created directly, using new. All
	* application resource instantiation should be done though this method.
	* 
	* One of the primary things this allows is being able to inject the path
	* to the object being instantiated into the object w/o resorting to
	* some contribed naming resolution logic. Having the path to the class
	* being instantiated makes it possible to get paths to others classes
	* relative to the current object rather than allows specifying a absolute
	* path to the resource.
	* 
	* IE.  $this->_objMCP->getPkg('../..').'.DAO.DAOSomething';
	* 
	* Prefixing is also done here, so that all resources can be referenced w/o
	* MCP prefix for unique class naming and pissible conflict resolution
	* with third-party APIs and site/platform custom classes.
	* 
	* @param str absolute package path
	* @param array arguments to be passed to instance
	* @param str class name override - used for component execution 
	* @return obj instance
	*/
	public function getInstance($strPkg,$arrArgs,$strClassName='') {
		
		$this->import($strPkg);
		
		$intPos = strrpos($strPkg,'.');		
		$strModuleClass = $strClassName?$strClassName:substr($strPkg,$intPos+1);
		
		/*
		* Set instatiation resource path 
		*/
		$this->_strInstancePkg = $strPkg;
		
		/*
		* Add MCP prefix to EVERYTHING except App.Lib classes - classes not dependent on MCP such as; external libraries, etc
		*/
		if(strpos($strPkg,'App.Lib') !== 0) $strModuleClass = "MCP$strModuleClass";
		
		$objReflection = new \ReflectionClass($strModuleClass); 
		if($objReflection->hasMethod('__construct')) {
			$obj = $objReflection->newInstanceArgs($arrArgs);
		} else {
			$obj = $objReflection->newInstance();
		}
		
		/*
		* unset instatiation resource path 
		*/
		$this->_strInstancePkg = null;
		
		return $obj;
		
	}
	
	/*
	* Get package of last instantiated resource
	* 
	* @return str resource path
	*/
	public function getInstancePkg() {
		return $this->_strInstancePkg;
	}
	
	/*
	* Get query string
	* 
	* @return str query string
	*/
	public function getQueryString() {
		
		// fetch query string data
		$strQueryString = $this->_objRequest->getServerData('QUERY_STRING');
		
		// make it easy to call this method when building URLs
		if( strlen($strQueryString) === 0 ) {
			return '';
		}
		
		return strpos($strQueryString,'?') === 1?$strQueryString:"?$strQueryString";
		
	}
	
	/*
	* Get POST data
	*
	* [@param] str key name
	* @return mix value
	*/
	public function getPost($strName=null) {
		return $this->_objRequest->getPostData($strName);
	}
	
	/*
	* Get GET data
	*
	* [@param] str key name
	* @return mix value
	*/
	public function getGet($strName=null) {
		return $this->_objRequest->getGetData($strName);
	}
	
	/*
	* Get modules form configuration 
	* 
	* @param str module application path
	* @param str type of config
	* @param bool include mixins
	* @param array dynamic : Adds dynamic fields
	*    2 keys allowed: entity_type (required) and entities_id (optional)
	* @return array form configuration
	*/
	public function getFrmConfig($strPkg,$strType='frm',$boolMixin=true,$arrDynamic=null) {
		
		/*
		* Dynamic array triggers dynamic field resolution 
		*/
		if($arrDynamic !== null) {
			$entities_id = isset($arrDynamic['entities_id'])?$arrDynamic['entities_id']:null;
			
			/*
			* This procedure is relient on the Field Component, no other great way around it 
			*/
			return $this->getInstance('Component.Field.DAO.DAOField',array($this))->getFrmConfig(
				$arrDynamic['entity_type'] // notice this is ALWAYS required
				,$entities_id
				,null
				,$strPkg
			);
		}
		
		/*
		* Form mixins 
		*/
		$arrMixin = array();
		
		/*
		* Query simple XML object for form configuration and module to take of mixins
		* 
		* Addition: ability to pass XML object as target
		*/
		if(is_string($strPkg)) {
			$arrConfig = $this->_objXMLModules->xpath("//mod[@pkg='$strPkg']/$strType");
		} else {
			/* in this case str package is a Simple XML object */
			$arrConfig = $strPkg->xpath("/mod/$strType");
		}
		
		/*
		* On query error return null 
		*/
		if($arrConfig === false || empty($arrConfig)) {
			return null;
		}
		
		/*
		* Add in mixins
		*/
		if($boolMixin === true) {
			
			/*
			* Addition: able to specify XML file target, for adding in dynamic fields 
			*/
			if(is_string($strPkg)) {
				$arrAdd = $this->_objXMLModules->xpath("//mod[@pkg='$strPkg']/$strType".'[@mixin]/@mixin');
			} else {
				$arrAdd = $strPkg->xpath("/mod/$strType".'[@mixin]/@mixin');
			}
			
			if($arrAdd !== false && !empty($arrAdd)) {
				/*
				* Element may mixin any number of other items by separating each package with a | 
				*/
				foreach(explode('|',(string) array_pop($arrAdd)) as $strMixin) {
                                    
                                        $arrMixinFrmConfig = $this->getFrmConfig((string) $strMixin,$strType);
                                    
                                        if($arrMixinFrmConfig && is_array($arrMixinFrmConfig)) {
                                            $arrMixin = array_merge($arrMixin,$arrMixinFrmConfig);
                                        }
                                        
                                        unset($arrMixinFrmConfig);
                                        
				}
			}
		}
		
		/*
		* Convert form configuration to expected array format 
		*/
		$arrReturn = array();
		foreach(array_pop($arrConfig) as $objField) {
			
			/*
			* The static attribute may be used to flag and field as being unexposed
			* to the user but indirectly passed the post array. This makes it possible to
			* remove fields from display inherited from a mixin yet still pass the data
			* to a processor. Most useful if creating a custom node type and the node type or some
			* other thing is set in stone. A static flag can be used to still pass the data to the node
			* processor yet not expose control to the client. 
			*/
			foreach($objField->attributes() as $strAttr=>$strValue) {
				if(strcmp('static',$strAttr) == 0 && strcmp('Y',$strValue) == 0) {
					$arrMixin[$objField->getName()]['static'] = (string) $strValue;
					break;
				}	
			}
			
			foreach($objField->children() as $objConfig) {
				
				$mixValue = null;
				
				/*
				* Used to cast dao, sql and values into values key at end 
				*/
				$boolValues = false;
				
				if(strcmp('dao',$objConfig->getName()) == 0) {
					
					$boolValues = true;
					
					// dao package
					$strPkg = (string) $objConfig->pkg;
					
					// dao method to call
					$strMethod = (string) $objConfig->method;
					
					// arguments to pass
					$arrArgs = array();
					
					// site and user id constants
					$const = array($this->escapeString($this->getSitesId()),$this->escapeString($this->getUsersId()));
					
					// collect arguments
					if($objConfig->args) {
						// foreach($objConfig->args->children() as $objArg) {
							
						/*
						* Algorithm supports multi-dimensional associative arrays using xml 
						* 
						* Parses XML DAO argument structure into multi-dimensional array uses arg
						* to differentiate between numeric and associative keys.
						*/
                                                $objMCP = $this;
						$toArgs = function($objArg,$toArgs) use (&$const,$objMCP) {
								
							$arrReturn = array();
							$intIndex = 0;
								
							foreach($objArg->children() as $objChild) {
								
								// arg is used to deliniate numeric index vs. associative
								$strUseIndex = strcmp('arg', $objChild->getName() ) === 0?$intIndex++:$objChild->getName();
									
								if( $objChild->count() ) {
									$arrReturn[$strUseIndex] = $toArgs($objChild,$toArgs);
								} else {
                                                                    
                                                                        // Support boolean and integer type casting
                                                                        // This is necessary so that strict comparisions can occur
                                                                        $boolParsed = false;
                                                                        foreach($objChild->attributes() as $strAttr=>$strAttrVal) {
                                                                            if(strcasecmp($strAttr,'type') === 0) {
                                                                                
                                                                                switch((string) $strAttrVal) {
                                                                                    case 'bool':
                                                                                        $arrReturn[$strUseIndex] = (bool) ((string) $objChild);
                                                                                        $boolParsed = true;
                                                                                        break;
                                                                                        
                                                                                    case 'int':
                                                                                        $arrReturn[$strUseIndex] = (int) ((string) $objChild);
                                                                                        $boolParsed = true;
                                                                                        break;
                                                                                        
                                                                                    default:
                                                                                        break;
                                                                                }
                                                                                
                                                                            }
                                                                        }
                                                                    
                                                                        // This will be false when the value has already been casted and assigned
                                                                        if($boolParsed === false) {
                                                                            // replace special site id and user id constants
                                                                            $arrReturn[$strUseIndex] = str_replace(array('SITES_ID','USERS_ID'),$const,$objChild);
                                                                        }
                                                                        
								}
									
							}
								
							return $arrReturn;
								
						};
							
							
						$arrArgs = $toArgs($objConfig->args,$toArgs);					
							
					}
					
					// get dao
					$objDAO = $this->getInstance($strPkg,array($this));
					
					// call dao method
					$mixValue = call_user_func_array(array($objDAO,$strMethod),$arrArgs);
					
					// add a blank item
					array_unshift($mixValue,array('label'=>'--','value'=>''));
				
				} else if(strcmp('sql',$objConfig->getName()) == 0) {
					
					$boolValues = true;
					
					// replace magical values in query
					$strSQL = str_replace(array('SITES_ID','USERS_ID'),array($this->escapeString($this->getSitesId()),$this->escapeString($this->getUsersId())),(string) $objConfig);
					$mixValue = $this->query($strSQL);
					
				} else if(strcmp('values',$objConfig->getName()) != 0) {
					
					if(strcmp('default',$objConfig->getName()) == 0) {
						$mixValue = str_replace(array('SITES_ID','USERS_ID'),array($this->getSitesId(),$this->getUsersId()),(string) $objConfig);
					} else {
						$mixValue = (string) $objConfig;
					}
					
				} else {
					
					$boolValues = true;
					
					$mixValue = array();
					foreach($objConfig->children() as $objOption) {
						$mixValue[] = array(
							'label'=> (string) $objOption->label
							,'value'=> (string) $objOption->value
						);
					}
				}
				
				/*
				* Mixin values have precedence. If a conflict occurs with a mixin the mixin definition
				* is overrided but not replaced. This makes it possible to abstract labels and change 
				* things about the base definition while keeping others. Its a override in terms of XML. 
				*/
				if(isset($arrMixin[$objField->getName()])) {
					if($boolValues === true) {
						$arrMixin[$objField->getName()]['values'] = $mixValue;
					} else {
						$arrMixin[$objField->getName()][$objConfig->getName()] = $mixValue;
					}
				} else {
					if($boolValues === true) {
						$arrReturn[$objField->getName()]['values'] = $mixValue;
					} else {
						$arrReturn[$objField->getName()][$objConfig->getName()] = $mixValue;
					}
				}
				
			}
			
		}
		
		/*
		* If mixins exist add them 
		*/
		if(!empty($arrMixin)) {
			
			// mixins take precedence and actual config overrides 
			foreach($arrMixin as $strName=>$arrValue) {
				if(isset($arrReturn[$strName])) {
					unset($arrReturn[$strName]);
				}
			}
			
			$arrReturn = array_merge($arrMixin,$arrReturn);
		}
		
		return $arrReturn;
	}
	
	/*
	* Get modules setting configuration 
	* 
	* @param str module application path
	* @param bool include mixins
	* @return array module settings
	*/
	public function getModConfig($strPkg,$boolMixin=true) {		
		/*
		* Same as frm config but change type 
		*/
		return $this->getFrmConfig($strPkg,'config',$boolMixin);
	}
	
	/*
	* Assign variable to template
	*
	* @param str variable name
	* @param mix value
	*/
	public function assign($strValue,$mixValue) {
		$this->_objTemplate->assign($strValue,$mixValue);
	}
	
	/*
	* Execute Template
	*
	* @param str template file path
	* @param obj module
	* @return str HTML
	*/
	public function fetch($strTpl,$objModule=null) {
		return $this->_objTemplate->fetch($strTpl,$objModule);
	}
	
	/*
	* Execute query
	*
	* @param str SQL query
	* @param str bind paramters
	* @return array rows
	*/
	public function query($strSQL,$arrBind=array()) {
		static $i=0;
		
		// echo "<p>".($i++).": $strSQL</p>";
		
		return $this->_objDB->query($strSQL,$arrBind);
	}
	
	/*
	* Cleans a string for insertion into query
	*
	* @param mix value
	* @return str escaped string
	*/
	public function escapeString($mixValue) {
		return $this->_objDB->escapeString($mixValue);
	}
	
	/*
	* Start a transaction 
 	*/
	public function begin() {
		return $this->_objDB->beginTransaction();
	}
	
	/*
	* Commit a transaction 
	*/
	public function commit() {
		return $this->_objDB->commit();
	}
	
	/*
	* Rollback a transaction 
	*/
	public function rollback() {
		return $this->_objDB->rollback();
	}
	
	/*
	* Imports requested package
	*
	* @param str package
	* [@param] bool ignore error
	* @return bool
	*/
	public function import($strPkg,$boolError=true) {
		return $this->_objImport->import($strPkg,$boolError);
	}
	
	/*
	* Trigger program error
	*
	* @param str message
	*/
	public function triggerError($strMessage) {
		$this->_objConsole->triggerError($strMessage);
	}
	
	/*
	* Subscribe event handler to object event
	* 
	* @param obj event target (object that fires event)
	* @param str event that is fired
	* @param arr [obj,method] handker to call when event is fired
	*/
	public function subscribe($objTarget,$strEvt,$arrHandler) {
		$this->_objEventHandler->subscribe($objTarget,$strEvt,$arrHandler);
	}
	
	/*
	* Fire event
	* 
	* @param obj target for event
	* @param str event name
	*/
	public function fire($objTarget,$strEvt) {
		$this->_objEventHandler->fire($objTarget,$strEvt);
	}
	
	/*
	* Unsubscribe event handler to object event
	* 
	* @param obj event target (object that fires event)
	* @param str event that is fired
	* @param arr [obj,method] handker to call when event is fired
	*/
	public function unsubscribe($objTarget,$strEvt,$arrHandler) {
		$this->_objEventHandler->unsubscribe($objTarget,$strEvt,$arrHandler);
	}
	
	/*
	* Access session data
	*
	* @param str value name
	* @param bool global value
	* @return mix data
	*/
	public function getSessionValue($strName,$boolGlobal=false) {
		return $this->_objSessionHandler->getDataValue($strName,$boolGlobal);
	}
	
	/*
	* Set a session value
	* 
	* @param str name
	* @param mix data
	* @param bool global value
	*/
	public function  setSessionValue($strName,$mixValue,$boolGlobal=false) {
		return $this->_objSessionHandler->setDataValue($strName,$mixValue,$boolGlobal);
	}
	
	/*
	* Access user data
	*
	* @param str value name
	* @param bool global value
	* @return mix data
	*/
	public function getUserValue($strName,$boolGlobal=false) {
		return $this->_objUser->getDataValue($strName,$boolGlobal);
	}
	
	/*
	* Set a user value
	* 
	* @param str name
	* @param mix data
	* @param bool global value
	*/
	public function  setUserValue($strName,$mixValue,$boolGlobal=false) {
		return $this->_objUser->setDataValue($strName,$mixValue,$boolGlobal);
	}
	
	/*
	* Set a cookie value
	*
	* @param str name
	* @param mix data
	* @param int expire
	* @param bool global value
	* @param bool domain only
	*/
	public function setCookieValue($strName,$mixValue,$boolGlobal=false,$intExpire=0,$boolSite=false) {
		return $this->_objCookieManager->setDataValue($strName,$mixValue,$boolGlobal,$intExpire,$boolSite);
	}
	
	/*
	* Get cookie value
	*
	* @param str value name
	* @param bool global value
	* @return mix data
	*/
	public function getCookieValue($strName,$boolGlobal=false) {
		return $this->_objCookieManager->getDataValue($strName,$boolGlobal);
	}
	
	/*
	* Set the master template
	*
	* @param str master real template file path
	*/
	public function setMasterTemplate($strTemplatePath) {
		$this->_strMasterTemplatePath = $strTemplatePath;
	}
	
	/*
	* Get value from cached data
	* 
	* @param str value name
	* @param str package value belongs to
	* @return mix cached value
	*/
	public function getCacheDataValue($strName,$strPkg=null) {
		
		$data = $this->_objCacheHandler->getDataValue($strName,$strPkg);
		
		/*
		* All we really care about it the value, nothing more. So lets
		* eliminate a step of logic by just returning the value for now. This
		* seems most practical as I can't think of a case when the whole
		* row is really needed. 
		*/
		return $data !== null?$data['cache_value']:$data;
		
	}
	
	/*
	* Set value for cached data
	* 
	* @param str value name
	* @param mix data
	* @param str package value belongs to
	* @return bool sucess/failure
	*/
	public function setCacheDataValue($strName,$mixValue,$strPkg=null) {
		return $this->_objCacheHandler->setDataValue($strName,$mixValue,$strPkg);
	}
	
	/*
	* Expire cached data resource
	* 
	* @param str cached name
	* @param str package
	* @return bool success/failure
	*/
	public function expireCacheDataValue($strName,$strPkg) {
		return $this->_objCacheHandler->expireDataValue($strName,$strPkg);
	}
	
	/*
	* Add image to cache 
	* 
	* @param array base image data
	* @param array cache image data
	* @param array options
	* @return cached images id
	*/
	public function setCacheImage($arrBaseImage,$arrImage,$arrOptions) {
		return $this->_objCacheHandler->cacheImage($arrBaseImage,$arrImage,$arrOptions);
	}
	
	/*
	* Fetch cached image 
	* 
	* @param array image data
	* @param array cached options
	* @return array cached image data
	*/
	public function getCacheImage($arrImage,$arrOptions) {
		return $this->_objCacheHandler->fetchImage($arrImage,$arrOptions);
	}
	
	/*
	* Get the master template
	* 
	* @return str master template file path
	*/
	public function getMasterTemplate() {
		return $this->_strMasterTemplatePath;
	}
	
	/*
	* Get the master template for plain text email
	* 
	* @return str master template file path
	*/
	public function getEmailPlainTextMasterTemplate() {
		return $this->_strEmailPlainTextMasterTemplatePath;
	}
	
	/*
	* Get the master template for HTML email
	* 
	* @return str master template file path
	*/
	public function getEmailHTMLMasterTemplate() {
		return $this->_strEmailHTMLMasterTemplatePath;
	}
	
	/*
	* Captures string to dumped at end of request. Can be used to debug
	* without interupting the rest of the application. 
	* 
	* @param function callback
	*/
	public function capture($func) {
		$this->_arrCapture[] = $func;
	}
	
	/*
	* An error message should be issued when an attempted action
	* was unsucessful. There are two types of messages. The normal
	* message is displayed to all users, the debug is displayed 
	* when in development/debug mode. So a generic message may be used
	* for all users but a more concise, technical may be used for the
	* second.
	* 
	* @param str error message to display to all users (non-technical)
	* @param str error message to display for developers (smart, concise message)
	*/
	public function addSystemErrorMessage($strNormal,$strDev=null) {
		
		$this->_arrSystemMessages['error'][] = array(
			'normal'=>$strNormal
			,'dev'=>$strDev
		);
		
	}
	
	/*
	* A warning message should be issued when an attempted action
	* was sucessful with some type of non-critical error. There are two types of messages. The normal
	* message is displayed to all users, the debug is displayed 
	* when in development/debug mode. So a generic message may be used
	* for all users but a more concise, technical may be used for the
	* second.
	* 
	* @param str warning message to display to all users (non-technical)
	* @param str warning message to display for developers (smart, concise message)
	*/
	public function addSystemWarningMessage($strNormal,$strDev=null) {
		
		$this->_arrSystemMessages['warning'][] = array(
			'normal'=>$strNormal
			,'dev'=>$strDev
		);
		
	}
	
	/*
	* A status message should be issued when an attempted action
	* was sucessful. There are two types of messages. The normal
	* message is displayed to all users, the debug is displayed 
	* when in development/debug mode. So a generic message may be used
	* for all users but a more concise, technical may be used for the
	* second.
	* 
	* @param str warning message to display to all users (non-technical)
	* @param str warning message to display for developers (smart, concise message)
	*/
	public function addSystemStatusMessage($strNormal,$strDev=null) {
		
		$this->_arrSystemMessages['status'][] = array(
			'normal'=>$strNormal
			,'dev'=>$strDev
		);
		
	}
	
	/*
	* Get all system messages
	* 
	* @return array system messages
	*/
	public function getSystemMessages() {
		return $this->_arrSystemMessages;
	}
        
        /*
        * Add new breadcrumb
        * 
        * @param Common.Field.Link config array   
        */
        public function addBreadcrumb($arrLink) {
            $this->_arrBreadcrumbs[] = $arrLink;
        }
        
        /*
        * Get all breadcrumbs
        * 
        * @return array breadcrumbs  
        */
        public function getBreadcrumbs() {
            return $this->_arrBreadcrumbs;
        }
        
        /*
        * Add CSS File 
        * 
        * @param css 
        */
        public function addCss($arrCss) {
            $this->_arrAssets['css'][] = $arrCss;
        }
        
        /*
        * Add JS file 
        * 
        * @param js 
        */
        public function addJs($arrJs) {
            $this->_arrAssets['js'][] = $arrJs;
        }
        
        /*
        * Get JS files 
        */
        public function getJs() {
            return $this->_arrAssets['js'];
        }
        
        /*
        * Add debug message 
        * 
        * IMPORTANT: 
        * 
        * Unlike system messages HTML entities will not be
        * converted. This is done so that var_dump and print_r
        * formating can be retained. Therefore, HTML can be invalid
        * but it is more important to inspect actual contents of variables
        * that have valid HTML during back-end development phase.
        *
        * @param str/array/obj debug message 
         *@param array extra options (ie. class, line, format:[var dump, echo, print_r, etc] etc)
        */
        public function debug($mixData,$arrOpt=array()) {
            
            /*
            * For the time being use print_r for all object and
            * array dumps. However, in the future add options as necessary
            * for different formats such as; var_dump instead of print_r.
            */
            if(is_array($mixData) || is_object($mixData)) {
                $strMsg = '<pre>'.print_r($mixData,true).'</pre>';
            } else {
                $strMsg = '<p>'.$mixData.'</p>';
            }
            
            /*
            * Push final message onto debug stack. At this point all the template
            * layer should need to do is print the msg no post processing
            * should occur in the template layer. All post processing of converting
            * a variable to a string should happen before placing the message into the
            * debug message array below. 
            */
            $this->_arrDebugMessages[] = array(
                'msg'=>$strMsg
            );
        }
        
        /*
        * Get array of debug messages.
        *
        * @return array debug messages. 
        */
        public function getDebugMessages() {
            return $this->_arrDebugMessages;
        }
        
        /*
        * Add Meta data
        *
        * @param str name
        * @param str value
        * @param optional attributes 
        */
        public function setMetaData($name,$value,$attr=array()) {
            $this->_arrMetaData[$name] = array(
                'value'=>$value
                ,'attr'=>$attr
            );
        }
        
        /*
        * Get meta data associated with page request.
        *
        * @return arr meta data 
        */
        public function getMetaData() {
            return $this->_arrMetaData;
        }
	
	/*
	* Add dynamic field data to entity of specified type and row 
	* 
	* @param array row data
	* @param str entity type
	* @param int pk of row to decorate
	* @param str entity id
	* @return array decorated row
	*/
	public function addFields($arrData,$intRowsId,$strEntityType,$intEntitiesId=null) {
		
		/*
		* This process requires the Field Component 
		*/
		$objDAOField = $this->getInstance('Component.Field.DAO.DAOField',array($this));
		
		/*
		* fetch entity field values 
		*/
		$arrFields = $objDAOField->fetchFieldValues($intRowsId,$strEntityType,$intEntitiesId);
		
		/*
		* Add the data 
		*/
		foreach($arrFields as $arrField) {
			
			if(!isset($arrField['field_name'])) continue;
			
			$arrData[$arrField['field_name']] = $arrField['field_value'];
			
			/*
			* relational row data for fields storing foreign key. Fields not storing
			* a foreign key relationship will be NULL.
			*/
			//$arrData["{$arrField['field_name']}:relation"] = $arrField['field_value_relation'];
			
			//$arrData["{$arrField['field_name']}:field_values_id"] = $arrField['field_values_id'];
			
		}
		
		// echo '<pre>',print_r($arrData),'</pre>';
		
		return $arrData;
		
	}
	
	/*
	* Save dynamic fields for node entity of specified type w/ row id
	* 
	* @param array fields data
	* @param int rows id
	* @param str entity type
	* @param int entities id
	* @return int affected rows
	*/
	public function saveFieldValues($arrFields,$intRowsId,$strEntityType,$intEntitiesId=null) {
		
		/*
		* This process requires the Field Component 
		*/
		$objDAOField = $this->getInstance('Component.Field.DAO.DAOField',array($this));
		
		/*
		* Save the field data 
		*/
		return $objDAOField->saveFieldValues($arrFields,$intRowsId,$strEntityType,$intEntitiesId);
		
	}
        
        /*
        * This method is required to be called upon deletion of any any entity. This
        * will properly clean-up any field values or fields that need to be deleted
        * when an entity of the given type is deleted.   
        */
        public function doDeleteEntity() {
            
            /*
            * Hand off process to the field DAO
            */
            $objDAOField = $this->getInstance('Component.Field.DAO.FieldDAO',array($this));
            
            /*
            * Do delete/clean-up procedure 
            */
            return $objDAOField->doDeleteEntity();
            
        }
	
	/*
	* reCaptcha valid?
	* 
	* @param add system error message if invalid
	* @return bool
	*/
	public function recaptchaValid($boolAddSystemErrorMessage=true) {
		return true;
		
		require_once(ROOT.'/App/Lib/reCaptcha/v1.11/recaptchalib.php');	
					
  		$resp = recaptcha_check_answer(
  			$this->getConfigValue('recaptcha_privatekey')
  			,$this->_objRequest->getServerData('REMOTE_ADDR')
  			,$this->getPost('recaptcha_challenge_field')
  			,$this->getPost('recaptcha_response_field')
  		);
  		
  		if($boolAddSystemErrorMessage === true && !$resp->is_valid) {
  			echo 'invalid';
  			$this->addSystemErrorMessage(
  				"The reCAPTCHA wasn't entered correctly. Go back and try it again."
  			);
  		}
  		
		return $resp->is_valid;	
		
	}
	
	/*
	* reCaptcha HTML
	* 
	* @return str reCaptcha HTML string
	*/
	public function recaptchaDraw() {
		return null;
		
		require_once(ROOT.'/App/Lib/reCaptcha/v1.11/recaptchalib.php');
		return recaptcha_get_html( $this->getConfigValue('recaptcha_publickey') );
	}
	
	public function __destruct() {
            
                // Set CSS and JS when accessing site via main access point
                if(strcmp(basename($_SERVER['SCRIPT_NAME']),'index.php') === 0) {
                    $this->setSessionValue(MCP::SESSION_ASSET_KEY,$this->_arrAssets);
                }
		
		// write session data before closing database connection
		session_write_close();
                
                // debug info
                $debug = $this->executeComponent('Component.Util.Module.SystemMessage.Debug',array());
		
		// dump request
		echo $debug.$this->_strRequest;
		
		// commit user data
		$this->_objUser->saveUserData();
		
		// Disconnect from database
		$this->_objDB->disconnect();
		
	}

}
?>