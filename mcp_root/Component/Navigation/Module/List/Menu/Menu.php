<?php 
/*
* List navigation menus 
*/
class MCPNavigationListMenu extends MCPModule {
	
	private
	
	/*
	* Navigation data access layer
	*/
	$_objDAONavigation
	
	/*
	* Internal redirect flag 
	*/
	,$_strRedirect;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		// Get navigation DAO
		$this->_objDAONavigation = $this->_objMCP->getInstance('Component.Navigation.DAO.DAONavigation',array($this->_objMCP));
	}
	
	/*
	* Get menu data
	* 
	* @return array menu data
	*/
	private function _getMenus() {
		
		/*
		* Don't do this when internal redirect is being used 
		*/
		if($this->_strRedirect !== null) {
			return array();
		}
		
		$arrRows = $this->_objDAONavigation->listAllNavs('n.*,u.username creator,s.site_name',"n.sites_id = {$this->_objMCP->escapeString($this->_objMCP->getSitesId())}",null);
		$arrMenus = array();
		
		/*
		* Collect menu ids 
		*/
		$ids = array();
		foreach($arrRows as $arrMenu) {
			$ids[] = $arrMenu['navigation_id'];
		}
		
		/*
		* Get menu edit and add link permissions
		*/
		$perms = $this->_objMCP->getPermission(MCP::EDIT,'Navigation',$ids);
		$addPerms = $this->_objMCP->getPermission(MCP::ADD,'NavigationLink',$ids);
		
		/*
		* Add edit permission flag 
		*/
		foreach($arrRows as &$arrRow) {
			$arrRow['allow_edit'] = $perms[$arrRow['navigation_id']]['allow'];
			$arrRow['allow_add'] = $addPerms[$arrRow['navigation_id']]['allow'];
		}
		
		/*
		* Group each locations menus
		*/
		foreach($arrRows as $arrRow) {
			$arrMenus[$arrRow['menu_location']]['site_name'] = ucwords($arrRow['menu_location']).' Menus';
			$arrMenus[$arrRow['menu_location']]['menus'][] = $arrRow;
		}
		
		return $arrMenus;
		
	}
	
	/*
	* Get table headers 
	* 
	* @return array table headers
	*/
	private function _getHeaders() {
		
		$mod = $this;
		$mcp = $this->_objMCP;
		
		return array(
			array(
				'label'=>'Title'
				,'column'=>'menu_title'
				,'mutation'=>function($value,$row) use ($mod,$mcp) {
					
					return $mcp->ui('Common.Field.Link',array(
						'url'=>"{$mod->getBasePath(false)}/Links/{$row['navigation_id']}/Nav"
						,'label'=>$value
					));
					
				}
			)
			
			,array(
				'label'=>'Creator'
				,'column'=>'creator'
				,'mutation'=>null
			)
			
			,array(
				'label'=>'Display Title'
				,'column'=>'display_title'
				,'mutation'=>function($value,$row) {
					return $value == 1?'<abbr class="yes">Y</abbr>':'<abbr class="no">N</abbr>';
				}
			)
			
			,array(
				'label'=>'Position'
				,'column'=>'menu_location'
				,'mutation'=>function($value,$row) {
					return sprintf(
						'<span class="%s">%s</span>'
						,$value
						,ucwords($value)
					);
				}
			)
			
			,array(
				'label'=>'Created'
				,'column'=>'created_on_timestamp'
				,'mutation'=>function($value,$row) use ($mcp) {
					return $mcp->ui('Common.Field.Date',array(
						'date'=>$value
						,'type'=>'timestamp'
					));
				}
			)
			
			,array(
				'label'=>'Last Modified'
				,'column'=>'updated_on_timestamp'
				,'mutation'=>function($value,$row) use($mcp) {
					return $mcp->ui('Common.Field.Date',array(
						'date'=>$value
						,'type'=>'timestamp'
					));
				}
			)
			
			,array(
				'label'=>'&nbsp;'
				,'column'=>'navigation_id'
				,'mutation'=>function($value,$row) use ($mod,$mcp) {
					
					if(!$row['allow_edit']) {
						return 'Edit';
					}
					
					return $mcp->ui('Common.Field.Link',array(
						'url'=>"{$mod->getBasePath(false)}/Edit/{$value}"
						,'label'=>'Edit'
					));
					
				}
			)
			
			,array(
				'label'=>'&nbsp;'
				,'column'=>'navigation_id'
				,'mutation'=>function($value,$row) use ($mod,$mcp) {
					
					if(!$row['allow_add']) {
						return '+';
					}
					
					return $mcp->ui('Common.Field.Link',array(
						'url'=>"{$mod->getBasePath(false)}/Links/{$value}/Nav/Edit-Link/Nav/{$value}/"
						,'label'=>'+'
					));
					
				}
			)
			
		);
		
	}
	
	public function execute($arrArgs) {
		
		// Set internal redirect trigger when present
		$this->_strRedirect = !empty($arrArgs) && in_array($arrArgs[0],array('Edit','Links','Create'))?array_shift($arrArgs):null;
		
		// Get path to go back when internal redirect is triggered
		$this->_arrTemplateData['back_link'] = $this->getBasePath(false);
		
		// Get url path to create new menu
		$this->_arrTemplateData['create_link'] = "{$this->getBasePath(false)}/Create";
		
		// Get menu data
		$this->_arrTemplateData['menus'] = $this->_getMenus();
		
		// load menu table headers
		$this->_arrTemplateData['headers'] = $this->_getHeaders();
		
		// Internal redirect hndling
		$strTpl = 'Menu';
		$this->_arrTemplateData['REDIRECT_TPL'] = '';
		
		// Edit or create a menu
		if(strcmp('Edit',$this->_strRedirect) === 0 || strcmp('Create',$this->_strRedirect) === 0) {
			$this->_arrTemplateData['REDIRECT_TPL'] = $this->_objMCP->executeComponent(
				'Component.Navigation.Module.Form.Menu'
				,$arrArgs
				,null
				,array($this)
			);
			
			// change the template
			$strTpl = 'Redirect';
		
		// View menus links
		} else if(strcmp('Links',$this->_strRedirect) === 0) {
			
			$this->_arrTemplateData['REDIRECT_TPL'] = $this->_objMCP->executeComponent(
				'Component.Navigation.Module.List.Link'
				,$arrArgs
				,null
				,array($this)
			);

			// change the template
			$strTpl = 'Redirect';
			
		}	
		
		return "Menu/$strTpl.php";
		
	}
	
	/*
	* Base path to modules capturable state 
	* 
	* @param bool redirect flag
	* @return str base path to module state
	*/
	public function getBasePath($redirect=true) {
		$strBasePath = parent::getBasePath();
		
		// Append redirect flag
		if($redirect === true && $this->_strRedirect !== null) {
			$strBasePath.= "/{$this->_strRedirect}";
		}
		
		return $strBasePath;
	}
	
}
?>