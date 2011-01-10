<?php 
/*
* List users 
*/
class MCPUserList extends MCPModule {
	
	protected
	
	/*
	* User data access layer 
	*/
	$_objDAOUser
	
	/*
	* Current page number 
	*/
	,$_intPage = 1
	
	/*
	* Internal redirect 
	*/
	,$_strRequest;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	protected function _init() {
		// Fetch user data access layer
		$this->_objDAOUser = $this->_objMCP->getInstance('Component.User.DAO.DAOUser',array($this->_objMCP)); 
	}
	
	/*
	* Standard pagination callback 
	* 
	* @param int SQL offset
	* @param int SQL limit
	* @return int found rows
	*/
	public function paginate($intOffset,$intLimit) {
		
		// Kill query when redirecting to another module
		if($this->_strRequest !== null) return 0;
		
		// Fetch users
		$data = $this->_objDAOUser->listAll('*',$this->_getFilter(),$this->_getSort(),"{$this->_objMCP->escapeString($intOffset)},{$this->_objMCP->escapeString($intLimit)}");
		
		// Assign users to template variable
		$this->_arrTemplateData['users'] = array_shift($data);
		
		// Send back totalnumber of found users to pagination module
		return array_shift($data);
		
	}
	
	/*
	* Get SQL where clause
	* 
	* @return str SQL where clause
	*/
	protected function _getFilter() {
		return sprintf(
			"sites_id = %s AND deleted = 0"
			
			// Users that belong to the current site
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
		);
	}
	
	/*
	* Get SQL order by clause
	* 
	* @return str SQL order by clause
	*/
	protected function _getSort() {
		return null;
	}
	
	/*
	* Get display table headers
	* 
	* @return array table headers
	*/
	protected function _getHeaders() {
		return array(
			array(
				'label'=>'Username'
				,'column'=>'username'
				,'mutation'=>null
			)
			,array(
				'label'=>'Email'
				,'column'=>'email_address'
				,'mutation'=>null
			)
			,array(
				'label'=>'&nbsp;'
				,'column'=>'users_id'
				,'mutation'=>array($this,'displayEditLink')
			)
		);
	}
	
	public function execute($arrArgs) {
		
		// Get the current page number
		$this->_intPage = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):1;
		
		// Resolve possible internal redirect
		$this->_strRequest = !empty($arrArgs) && in_array($arrArgs[0],array('Edit','Fields'))?array_shift($arrArgs):null;
		
		// Set the number of users per page
		$intLimit = 10;
		
		// Paginate the module
		$this->_arrTemplateData['PAGINATION_TPL'] = $this->_objMCP->executeComponent('Component.Util.Module.Pagination',array($intLimit,$this->_intPage),'Component.Util.Template',array($this));
		
		// Get table headers
		$this->_arrTemplateData['headers'] = $this->_getHeaders();
		
		// Redirect back link
		$this->_arrTemplateData['back_link'] = $this->getBasePath(false);
		
		// View fields link
		$this->_arrTemplateData['fields_link'] = "{$this->getBasePath(false)}/Fields/MCP_SITES/{$this->_objMCP->getSitesId()}";
		
		// handle internal redirect
		$strTpl = 'List';
		$this->_arrTemplateData['REDIRECT_TPL'] = '';
		
		if(strcmp($this->_strRequest,'Edit') === 0) {
			$this->_arrTemplateData['REDIRECT_TPL'] = $this->_objMCP->executeComponent(
				'Component.User.Module.Registration.Form'
				,$arrArgs
				,null
				,array($this)
			);
			
			// change the template
			$strTpl = 'Redirect';
		
		// User fields
		} else if(strcmp($this->_strRequest,'Fields') === 0) {
			$this->_arrTemplateData['REDIRECT_TPL'] = $this->_objMCP->executeComponent(
				'Component.Field.Module.List'
				,$arrArgs
				,null
				,array($this)
			);
			
			// change the template
			$strTpl = 'Redirect';
		}
		
		return "List/$strTpl.php";
	}
	
	/*
	* Get path to module current state
	* 
	* @param bool apply redirect
	* @return str base path
	*/
	public function getBasePath($redirect=true) {
		$strBasePath = parent::getBasePath();
		
		// Add current page number
		if($this->_intPage !== null) {
			$strBasePath.= "/{$this->_intPage}";
		}
		
		// Add redirect
		if($redirect === true && $this->_strRequest !== null) {
			$strBasePath.= "/{$this->_strRequest}";
		}
		
		return $strBasePath;
	}
	
	/*
	* Header callback to display edit link
	* 
	* @param mix value
	* @param array user data
	* @return str output
	*/
	public function displayEditLink($value,$row) {
		
		return sprintf(
			'<a href="%s/Edit/%u">Edit</>'
			,$this->getBasePath(false)
			,$value
		);
		
	}
	
}
?>