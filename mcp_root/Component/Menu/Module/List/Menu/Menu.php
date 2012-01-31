<?php 
/*
* List navigation menus 
*/
class MCPMenuListMenu extends MCPModule {
	
	private
	
	/*
	* menu data access layer
	*/
	$_objDAOMenu
	
	/*
	* Internal redirect flag 
	*/
	,$_strRedirect
	
	/*
	* Menu id to perform action on 
	*/
	,$_intActionsId;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		// Get menu DAO
		$this->_objDAOMenu = $this->_objMCP->getInstance('Component.Menu.DAO.DAOMenu',array($this->_objMCP));
	
		// set-up delete event handler
		/*$id =& $this->_intActionsId;
		$dao = $this->_objDAOMenu;
		
		$this->_objMCP->subscribe($this,'MENU_DELETE',function() use(&$id,$dao)  {
			// delete the menu
			$dao->deleteNavs($id);
		});*/
	
	}
	
	/*
	* Handle form submit 
	*/
	private function _handleFrm() {
		
		/*
		* Get posted form data 
		*/
		$arrPost = $this->_objMCP->getPost('frmMenuList');
		
		/*
		* Route action 
		*/
		if($arrPost && isset($arrPost['action']) && !empty($arrPost['action'])) {
			
			/*
			* Get action 
			*/
			$strAction = array_pop(array_keys($arrPost['action']));
			
			/*
			* Get node types id 
			*/
			$this->_intActionsId = array_pop(array_keys(array_pop($arrPost['action'])));
			
			/*
			* Fire event 
			*/
			$this->_objMCP->fire($this,"MENU_".strtoupper($strAction));
		}
		
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
		
		$arrRows = $this->_objDAOMenu->listAllMenus('m.*,u.username creator,s.site_name',"m.deleted = 0 AND m.sites_id = {$this->_objMCP->escapeString($this->_objMCP->getSitesId())}",null);
		$arrMenus = array();
		
		/*
		* Collect menu ids 
		*/
		$ids = array();
		foreach($arrRows as $arrMenu) {
			$ids[] = $arrMenu['menus_id'];
		}
		
		/*
		* Get menu edit and add link permissions
		*/
		if( !empty($arrRows) ) {
			$perms = $this->_objMCP->getPermission(MCP::EDIT,'Menu',$ids);
			$addPerms = $this->_objMCP->getPermission(MCP::ADD,'MenuLink',$ids);
			$deletePerms = $this->_objMCP->getPermission(MCP::DELETE,'Menu',$ids);
		}
		
		/*
		* Add edit permission flag 
		*/
		$arrRow = null;
		foreach($arrRows as &$arrRow) {
			$arrRow['allow_edit'] = $perms[$arrRow['menus_id']]['allow'];
			$arrRow['allow_add'] = $addPerms[$arrRow['menus_id']]['allow'];
			$arrRow['allow_delete'] = $deletePerms[$arrRow['menus_id']]['allow'];
		}
		
		unset($arrRow);
		
		/*
		* Group each locations menus
		*/
		foreach($arrRows as $arrRow) {
			$arrMenus[$arrRow['menu_location']]['site_name'] = ucwords($arrRow['menu_location']).' Menus';
			$arrMenus[$arrRow['menu_location']]['menus'][] = $arrRow;
		}
		
		// echo '<pre>',print_r($arrMenus),'</pre>';
		
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
						'url'=>"{$mod->getBasePath(false)}/Links/{$row['menus_id']}"
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
				,'column'=>'menus_id'
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
				,'column'=>'menus_id'
				,'mutation'=>function($value,$row) use ($mod,$mcp) {
					
					if(!$row['allow_add']) {
						return '<a href="#" class="btn create disabled">+</a>';
					}
					
					return $mcp->ui('Common.Field.Link',array(
						'url'=>"{$mod->getBasePath(false)}/Create-Link/Menu/{$value}/"
						,'label'=>'+'
                                                ,'class'=>'btn create'
					));
					
				}
			)
			
			,array(
				'label'=>'&nbsp;'
				,'column'=>'menus_id'
				,'mutation'=>function($value,$row) use ($mcp) {
					return $mcp->ui('Common.Form.Input',array(
						'value'=>'Delete'
                                                ,'type'=>'submit'
						,'name'=>"frmMenuList[action][delete][$value]"
						,'disabled'=>!$row['allow_delete']
                                                ,'class'=>'btn delete'
					));
				}
			)
			
		);
		
	}
	
	public function execute($arrArgs) {
		
		// Set internal redirect trigger when present
		$this->_strRedirect = !empty($arrArgs) && in_array($arrArgs[0],array('Edit','Links','Create','Create-Link'))?array_shift($arrArgs):null;
		
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
				'Component.Menu.Module.Form.Menu'
				,$arrArgs
				,null
				,array($this)
			);
			
			// change the template
			$strTpl = 'Redirect';
		
		// View menus links
		} else if(strcmp('Links',$this->_strRedirect) === 0) {
			
			$this->_arrTemplateData['REDIRECT_TPL'] = $this->_objMCP->executeComponent(
				'Component.Menu.Module.List.Link'
				,$arrArgs
				,null
				,array($this)
			);

			// change the template
			$strTpl = 'Redirect';
			
		} else if(strcmp('Create-Link',$this->_strRedirect) === 0) {

			$this->_arrTemplateData['REDIRECT_TPL'] = $this->_objMCP->executeComponent(
				'Component.Menu.Module.Form.Link'
				,$arrArgs
				,null
				,array($this)
			);

			// change the template
			$strTpl = 'Redirect';
			
		}
		
		/*
		* Handle form submit  
		*/
		// $this->_handleFrm();

		/*
		* Form action
		*/
		$this->_arrTemplateData['frm_action'] = $this->getBasePath();
		
		/*
		* Form name 
		*/
		$this->_arrTemplateData['frm_name'] = 'frmMenuList';
		
		/*
		* Form method 
		*/
		$this->_arrTemplateData['frm_method'] = 'POST';
		
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