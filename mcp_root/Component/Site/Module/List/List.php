<?php
/*
* View all available sites 
*/ 
class MCPSiteList extends MCPModule {
	
	protected
	
	/*
	* Site data access layer 
	*/
	$_objDAOSite
	
	/*
	* Internal redirect keyword/flag 
	*/
	,$_strRequest;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	protected function _init() {
		// Get site data access layer
		$this->_objDAOSite = $this->_objMCP->getInstance('Component.Site.DAO.DAOSite',array($this->_objMCP));
	}
	
	/*
	* Get table display headers 
	* 
	* @return array display headers
	*/
	protected function _getHeaders() {
		
		$mcp = $this->_objMCP;
		return array(
			array(
				'label'=>'Name'
				,'column'=>'site_name'
				,'mutation'=>null
			)
			,array(
				'label'=>'Domain'
				,'column'=>'site_domain'
				,'mutation'=>null
			)
			,array(
				'label'=>'Folder'
				,'column'=>'site_directory'
				,'mutation'=>null
			)
			,array(
				'label'=>'Module Prefix'
				,'column'=>'site_module_prefix'
				,'mutation'=>null
			)
			,array(
				'label'=>'Created'
				,'column'=>'created_on_timestamp'
				,'mutation'=>function($value,$row) use ($mcp) {
					return $mcp->ui('Common.Field.Date',array(
						'date'=>$value
					));
				}
			)
			,array(
				'label'=>'&nbsp;'
				,'column'=>'sites_id'
				,'mutation'=>array($this,'displaySiteEditLink')
			)
			
			,array(
				'label'=>'&nbsp;'
				,'column'=>'sites_id'
				,'mutation'=>function($value,$row) use ($mcp) {
					return $mcp->ui('Common.Form.Submit',array(
						'label'=>'Delete'
						,'name'=>"frmSite[action][delete][{$row['sites_id']}]"
						,'disabled'=>!$row['allow_delete']
					));
				}
			)
			
			/*,array(
				'label'=>'&nbsp;'
				,'column'=>'sites_id'
				,'mutation'=>function($value,$row) use ($mcp) {
					return $mcp->ui('Common.Form.Checkbox',array(
						'id'=>'blah-'.$row['sites_id']
						,'value'=>1
						,'name'=>"frmSite[action][checked][{$row['sites_id']}]"
					));
				}
			)*/
			
		);
	}
	
	public function execute($arrArgs) {
		
		/*
		* Determine whether to redirect request 
		*/
		$this->_strRequest = !empty($arrArgs) && in_array($arrArgs[0],array('Edit','Add'))?array_shift($arrArgs):null;
		
		/*
		* List all sites when no redirect not present
		*/
		if($this->_strRequest === null) {
			$this->_arrTemplateData['sites'] = $this->_objDAOSite->listAll('s.*','s.deleted = 0','s.site_name ASC');
			
			// Get delete and edit permissions (may also want to add in read)
			$ids = array();
			foreach($this->_arrTemplateData['sites'] as $site) {
				$ids[] = $site['sites_id'];
			}
			
			$permsEdit = $this->_objMCP->getPermission(MCP::EDIT,'Site',$ids);
			$permsDelete = $this->_objMCP->getPermission(MCP::DELETE,'Site',$ids);
			
			// add in flags to determine whether user has permission to edit or delete site
			foreach($this->_arrTemplateData['sites'] as &$site) {
				$site['allow_edit'] = $permsEdit[$site['sites_id']]['allow'];
				$site['allow_delete'] = $permsDelete[$site['sites_id']]['allow'];
			}
		}
		
		/*
		* Get table headers 
		*/
		$this->_arrTemplateData['headers'] = $this->_getHeaders();
		
		/*
		* Resolve redirect content 
		*/
		$strTpl = 'List';
		$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = '';
		
		/*
		* Build back link 
		*/
		$this->_arrTemplateData['back_link'] = $this->getBasePath(false);
		
		/*
		* Build Create Site Link 
		*/
		$this->_arrTemplateData['create_link'] = "{$this->getBasePath(false)}/Add";
		
		/*
		* Determine whether user is allowed to create a new site 
		*/
		$perm = $this->_objMCP->getPermission(MCP::ADD,'Site');
		$this->_arrTemplateData['allow_create'] = $perm['allow'];
		
		// Edit or Add Site
		if(strcmp('Edit',$this->_strRequest) === 0 || strcmp('Add',$this->_strRequest) === 0) {	
			$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = $this->_objMCP->executeComponent(
				'Component.Site.Module.Form'
				,$arrArgs
				,null
				,array($this)
			);
			$strTpl = 'Redirect';
		}
		
		return "List/$strTpl.php";
	}
	
	/*
	* Get path to current modules state
	* 
	* @param bool append redirect keyword/flag when present
	* @return str base path
	*/
	public function getBasePath($redirect=true) {
		$strBasePath = parent::getBasePath();
		
		if($redirect === true && $this->_strRequest !== null) {
			$strBasePath.= "/{$this->_strRequest}";
		}
		
		return $strBasePath;
	}
	
	/*
	* Header display callback to display link to edit site row
	* 
	* @param mix column data
	* @param array row data
	* @return str output
	*/
	public function displaySiteEditLink($value,$row) {
		
		if(!$row['allow_edit']) {
			return 'Edit';
		}
		
		return sprintf(
			'<a href="%s/Edit/%u">Edit</a>'
			,$this->getBasePath(false)
			,$value
		);
		
	}
	
}
?>