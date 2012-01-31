<?php 
class MCPMenuListLink extends MCPModule {
	
	protected
	
	/*
	* Menu Data access object 
	*/
	$_objDAOMenu
	
	/*
	* Internal redirect
	*/
	,$_strRequest
	
	/*
	* The current menu for links being viewed 
	*/
	,$_arrMenu;
	
	public function __construct(MCP $objMCP,MCPModule $objParent=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParent,$arrConfg);
		$this->_init();
	}
	
	protected function _init() {
		
		// Get menu data access object
		$this->_objDAOMenu = $this->_objMCP->getInstance('Component.Menu.DAO.DAOMenu',array($this->_objMCP));
		
	}
	
	/*
	* Get menu links are being viewed for
	* 
	* @return array menu data
	*/
	protected function _getMenu() {
		return $this->_arrMenu;
	}
	
	/*
	* Set the menu to view links for
	* 
	* @param array menu data
	*/
	protected function _setMenu($arrMenu) {
		$this->_arrMenu = $arrMenu;
	}
	
	/*
	* Get headers for listing links in table
	* 
	* @return array table headers
	*/
	protected function _getHeaders() {
		
		$mcp = $this->_objMCP;
		$mod = $this;
		
		return array(
			array(	
				'label'=>'Title'
				,'column'=>'display_title'
				,'mutation'=>null
			)
			
			// datasource?
			,array(
				'label'=>'Datasource'
				,'column'=>'datasource'
				,'mutation'=>function($value,$row) {
					return $value?'Y':'N';
				}
			)
			
			// dynamic?
			,array(
				'label'=>'Dynamic'
				,'column'=>'dynamic'
				,'mutation'=>function($value,$row) {
					return $value?'Y':'N';
				}
			)
			
			// add child link
			,array(
				'label'=>'&nbsp;'
				,'column'=>'menu_links_id'
				,'mutation'=>function($value,$row) use ($mcp,$mod,$menu) {
					
					// dynamically derived links can have children added to them via the UI
					if(!$row['allow_add']) {
						return '<a href="#" class="btn create disabled">+</a>';
					}
					
					return $mcp->ui('Common.Field.Link',array(
						'label'=>'+'
						,'url'=>$mod->getBasePath().'/Create/Link/'.$value
                                                ,'class'=>'btn create'
					));	
					
				}
			)
			
			// edit link
			,array(
				'label'=>'&nbsp;'
				,'column'=>'menu_links_id'
				,'mutation'=>function($value,$row) use ($mcp,$mod) {
					
					// dynamically derived links can not be edited
					if(!$row['allow_edit']) {
						return 'Edit';
					}
					
					return $mcp->ui('Common.Field.Link',array(
						'label'=>'Edit'
						,'url'=>$mod->getBasePath().'/Edit/'.$value
					));				
						
				}
			)
			
			// delete link
			,array(
				'label'=>'&nbsp;'
				,'column'=>'menu_link_id'
				,'mutation'=>function($value,$row) use ($mcp) {	
					return $mcp->ui('Common.Form.Input',array(
						'value'=>'Delete'
						,'name'=>"frmMenuLink[$row]"
						,'type'=>'submit'
						,'disabled'=>!$row['allow_delete']
                                                ,'class'=>'btn delete'
					));
				}
			)
		);
		
	}
	
	public function execute($arrArgs) {
		
		// Get id of menu to view links for
		$intMenuId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		if($intMenuId !== null) {
			// fetch and set menu data
			$this->_setMenu($this->_objDAOMenu->fetchMenuById($intMenuId));
		}
		
		// Determine if the request is a redirect
		$this->_strRequest = !empty($arrArgs) && in_array($arrArgs[0],array('Create','Edit','View'))?array_shift($arrArgs):null;
		
		// Handle internal redirect 
		$strTpl = 'Link';
		$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = '';
		
		// add or edit existing link
		if(strcmp('Create',$this->_strRequest) === 0 || strcmp('Edit',$this->_strRequest) === 0) {
			$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = $this->_objMCP->executeComponent(
				'Component.Menu.Module.Form.Link'
				,$arrArgs
				,null
				,array($this)
			);
			$strTpl = 'Redirect';
			
		} /* else if(strcmp('View',$this->_strRequest) === 0) { none existent at this moment
			$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = $this->_objMCP->executeComponent(
				'Component.Node.Module.View.Entry'
				,$arrArgs
				,null
				,array($this)
			);
			$strTpl = 'Redirect';
			
		}*/ else { // normal request - only get data when its needed - not internally redirecting
		
			// Get the menu
			$arrMenu = $this->_getMenu();
		
			if($arrMenu === null) {
				throw new Exception('Requested menu does not exist.');
			}
			
			// Get menu links
			$this->_arrTemplateData['links'] = $this->_objDAOMenu->fetchMenu($arrMenu['menus_id'],array('dynamic_links'=>true,'include_perms'=>true));
		
			// Assign table headers
			$this->_arrTemplateData['headers'] = $this->_getHeaders();
		
		}
		
		// Back label 
		$this->_arrTemplateData['back_label'] = 'Links';
		
		// Redirect back link
		$this->_arrTemplateData['back_link'] = $this->getBasePath(false);
		
		// Create label 
		$this->_arrTemplateData['create_label'] = 'Link';
		
		// Create a new node of specified type link 
		$arrMenu = $this->_getMenu();
		$this->_arrTemplateData['create_link'] = "{$this->getBasePath(false)}/Create/Menu/{$arrMenu['menus_id']}";
		
		// create new link permissions
		$perm = $this->_objMCP->getPermission(MCP::ADD,'MenuLink',$arrMenu['menus_id']);
		$this->_arrTemplateData['allow_link_create'] = $perm['allow'];
		
		return "Link/$strTpl.php";
		
	}
	
	public function getBasePath($redirect=true) {
		$strBasePath = parent::getBasePath();
		
		/*
		* Get the current menu being viewed
		*/
		$arrMenu = $this->_getMenu();
		
		// add menu id
		if($arrMenu !== null) {
			$strBasePath.= "/{$arrMenu['menus_id']}";
		}
		
		// add redirect flag
		if($redirect === true && $this->_strRequest !== null) {
			$strBasePath.= "/{$this->_strRequest}";
		}
		
		return $strBasePath;
	}
	
}
?>