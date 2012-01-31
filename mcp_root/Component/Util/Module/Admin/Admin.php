<?php 
/*
* Provides administrative back-door to components using short name references in
* a protected namespace.
*/
class MCPUtilAdmin extends MCPModule {
	
	protected
	
	/*
	* Short Name to package mappings
	*/
	$_arrMappings = array(
	
		// Manage vocabularies
		'Vocabulary'			=>'Component.Taxonomy.Module.List.Vocabulary'
		
		// Manage menus
		,'Menu'					=>'Component.Menu.Module.List.Menu'
		
		// Manage global configuaration
		,'Config'				=>'Component.Config.Module.Form'
		
		// Manage content / nodes
		,'Content'				=>'Component.Node.Module.List.Type'
		
		// manage users
		,'Users'					=>'Component.User.Module.List'
		
		// manage sites
		,'Sites'						=>'Component.Site.Module.List'
		
		// manage roles
		,'Roles'						=>'Component.Permission.Module.List.Role'
		
		// manage views
		,'Schemas'						=>'Component.View.Module.List.Type'
	)
	
	/*
	* requested short name module 
	*/
	,$_strShortName;
	
	public function __construct(MCP $objMCP, MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
	}
	
	public function execute($arrArgs) {
		
		/*
		* Get the short name 
		*/
		$this->_strShortName = !empty($arrArgs) && isset($this->_arrMappings[$arrArgs[0]])?array_shift($arrArgs):null;
		
                /*
                * Add admin CSS and JS 
                */
                $this->_objMCP->addCss(array(
                    'path'=>'/theme/admin/default/css/admin.css'
                ));
                
		/*
		* Pass the request to requested component 
		*/
		$this->_arrTemplateData['TPL_ADMIN_CONTENT'] = '';
		if($this->_strShortName !== null) {
			$this->_arrTemplateData['TPL_ADMIN_CONTENT'] = $this->_objMCP->executeComponent(
				$this->_arrMappings[$this->_strShortName]
				,$arrArgs
				,null
				,array($this)
			);
		}
                
                /*
                * A single template exists to handle the admin area for now.
                */
                $this->_objMCP->setMasterTemplate('Component/Util/Template/Admin/Layout.php');
                
                /*
                * Admin area uses bootstrap for the time being to handle
                * just about all styling. Things are much simpler this way
                * and we can focus on functionality. Bootstrap will not be included
                * on the front end considering it is up to the front-end developer
                * to design the site as necessary for optimization, etc.  
                */
                $this->_objMCP->addCss(array(
                    'path'=>'/lib/bootstrap/v1.4.0/bootstrap.css'
                ));
		
		return 'Admin/Admin.php';
	}
	
	/*
	* Get the base path the modules state
	* 
	* @return str base path
	*/
	public function getBasePath() {
		$strBasePath = parent::getBasePath();
		
		// add the short name in
		if($this->_strShortName !== null) {
			$strBasePath.= "/{$this->_strShortName}";
		}
		
		return $strBasePath;
		
	}
	
}
?>