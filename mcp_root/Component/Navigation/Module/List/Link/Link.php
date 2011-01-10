<?php 
/*
* List menu links
*/
class MCPNavigationListLink extends MCPModule {
	
	private
	
	/*
	* Navigation data access layer 
	*/
	$_objDAONavigation
	
	/*
	* Parent id passed through URL 
	*/
	,$_strParentId
	
	/*
	* Parent type passed through URL 
	*/
	,$_strParentType
	
	/*
	* Editing view
	*/
	,$_boolEdit = false
	
	/*
	* Navigation links id to perform action on 
	*/
	,$_intActionsId;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		// Get Navigation DAO
		$this->_objDAONavigation = $this->_objMCP->getInstance('Component.Navigation.DAO.DAONavigation',array($this->_objMCP));
		
		// set-up event handlers
		$this->_objMCP->subscribe($this,'NAVIGATION_LINK_UP',array($this,'onUp'));
		$this->_objMCP->subscribe($this,'NAVIGATION_LINK_DOWN',array($this,'onDown'));
		$this->_objMCP->subscribe($this,'NAVIGATION_LINK_DELETE',array($this,'onDelete'));
		$this->_objMCP->subscribe($this,'NAVIGATION_LINK_REMOVE',array($this,'onRemove'));
		
	}
	
	/*
	* Move navigation link up
	*/
	public function onUp() {
		$this->_objDAONavigation->moveLinkUp($this->_intActionsId);	
	}
	
	/*
	* Move navigation link down
	*/
	public function onDown() {	
		$this->_objDAONavigation->moveLinkDown($this->_intActionsId);
	}
	
	/*
	* Delete navigation link
	*/
	public function onDelete() {
		$this->_objDAONavigation->deleteLink($this->_intActionsId);
	}
	
	/*
	* Remove navigation link
	*/
	public function onRemove() {
		$this->_objDAONavigation->removeLink($this->_intActionsId);
	}
	
	/*
	* Handle form submit 
	*/
	private function _handleFrm() {
		
		/*
		* Get posted form data 
		*/
		$arrPost = $this->_objMCP->getPost($this->_getFrmName());
		
		/*
		* Route action 
		*/
		if($arrPost && isset($arrPost['action']) && !empty($arrPost['action'])) {
			
			/*
			* Get action 
			*/
			$strAction = array_pop(array_keys($arrPost['action']));
			
			/*
			* Get links id 
			*/
			$this->_intActionsId = array_pop(array_keys(array_pop($arrPost['action'])));
			
			/*
			* Create hard link for dynamic link action
			*/
			if(!is_numeric($this->_intActionsId) && strpos($this->_intActionsId,'-') !== false) {
				$this->_intActionsId = call_user_func_array(array($this->_objDAONavigation,'createHardLinkFromDynamic'),explode('-',$this->_intActionsId));
			}
			
			/*
			* Fire event 
			*/
			$this->_objMCP->fire($this,"NAVIGATION_LINK_".strtoupper($strAction));
		}
		
	}
	
	/*
	* Get ancestory select menu
	* 
	* @param array ancestory menu
	*/
	private function _getAncestory() {
		
		/*
		* Get link ancestory
		*/
		$arrLinks = strcmp('nav',$this->_strParentType) == 0?array():$this->_objDAONavigation->fetchAncestory($this->_intParentId);
		
		/*
		* Get menu
		*/
		$arrNav = $this->_objDAONavigation->fetchNavById(isset($arrLinks[0])?$arrLinks[0]['parent_id']:$this->_intParentId);
		
		/*
		* Push menu onto beginning of array
		*/
		array_unshift($arrLinks,$arrNav);
		
		$arrAncestory = array('values'=>array(''),'output'=>array('--'),'selected'=>'');
		
		foreach($arrLinks as $intIndex=>$arrLink) {
			$boolNav = $intIndex == 0?true:false;
			
			$arrAncestory['values'][] = $boolNav === true?"nav-{$arrLink['navigation_id']}":$arrLink['navigation_links_id'];
			$arrAncestory['output'][] = $boolNav === true?'Root':$arrLink['link_title'];
		}
		
		return $arrAncestory;
		
	}
	
	/*
	* Get forms name
	* 
	* @return str form name
	*/
	private function _getFrmName() {
		return 'frmNavigationList';
	}
	
	public function execute($arrArgs) {
		
		/*
		* Get parent id 
		*/
		$this->_intParentId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null; 
		
		/*
		* Get parent type. The parent type will either be nav or link
		*/
		$this->_strParentType = !empty($arrArgs) && strcmp($arrArgs[0],'Nav') == 0 && array_shift($arrArgs)?'nav':'link';
		
		/*
		* Editor 
		*/
		$this->_boolEdit = !empty($arrArgs) && strcmp($arrArgs[0],'Edit-Link') == 0 && array_shift($arrArgs)?true:false;
		
		/*
		* Handle form submit  
		*/
		$this->_handleFrm();
		
		/*
		* inline edit and list switch 
		*/
		if($this->_boolEdit === false) {
			if($this->_intParentId !== null) {
				$this->_arrTemplateData['links'] = $this->_objDAONavigation->fetchMenu($this->_intParentId,$this->_strParentType,false);
			
				// Get permissions for first level of links
				$ids = array();
				$add = array();
				foreach($this->_arrTemplateData['links'] as &$link) {
					$ids[] = $link['navigation_links_id'];
				}
				
				if(!empty($ids)) {
					$addPerms = $this->_objMCP->getPermission(MCP::ADD,'NavigationLink',$ids);
					$editPerms = $this->_objMCP->getPermission(MCP::EDIT,'NavigationLink',$ids);
				}
				
				foreach($this->_arrTemplateData['links'] as &$link) {
					$link['allow_edit'] = $editPerms[$link['navigation_links_id']]['allow'];
					
					// @TODO: resolve this properly
					$link['allow_add'] = true; //$addPerms[$link['navigation_links_id']]['allow'];
				}
			
			} else {
				$this->_arrTemplateData['links'] = array();
			}
		} else {
			$this->_arrTemplateData['EDIT_TPL'] = $this->_objMCP->executeComponent('Component.Navigation.Module.Form.Link',$arrArgs,null,array($this));
		}
		
		/*
		* Load other template data 
		*/
		$this->_arrTemplateData['legend'] = 'Menu';
		$this->_arrTemplateData['name'] = $this->_getFrmName();
		$this->_arrTemplateData['action'] = $this->getBasePath();
		$this->_arrTemplateData['method'] = 'POST';
		$this->_arrTemplateData['edit_path'] = parent::getBasePath();
		$this->_arrTemplateData['ancestory'] = $this->_getAncestory();
		$this->_arrTemplateData['BREADCRUMB_TPL'] = $this->_objMCP->executeComponent('Component.Util.Module.Breadcrumb',array(),null,array($this));
		
		if($this->_boolEdit === true) {
			return 'Link/Edit.php';
		} else {
			return 'Link/Link.php';
		}
	}
	
	/*
	* Gte base path to current modules state 
	*/
	public function getBasePath() {
		$strBasePath = parent::getBasePath();
		
		/*
		* Add parent id passed through URL to maintain state 
		*/
		if($this->_intParentId !== null) {
			$strBasePath.= "/{$this->_intParentId}";
		}
		
		/*
		* Add parent type if at top of nav tree 
		*/
		if(strcmp(($this->_strParentType?$this->_strParentType:''),'nav') == 0) {
			$strBasePath.= "/Nav";
		}
		
		/*
		* Add editing flag 
		*/
		if($this->_boolEdit === true) {
			$strBasePath.= "/Edit-Link";
		}
		
		return $strBasePath;
	}
	
	/*
	* Override base breadcrumb method to provide breadcrumb data to breadcrumb utility
	* 
	* @return array breadcrumb data
	*/
	public function getBreadcrumbData() {
		
		/*
		* Get link ancestory
		*/
		$arrLinks = strcmp('nav',$this->_strParentType) == 0?array():$this->_objDAONavigation->fetchAncestory($this->_intParentId);
		
		/*
		* Get menu
		*/
		$arrNav = $this->_objDAONavigation->fetchNavById(isset($arrLinks[0])?$arrLinks[0]['parent_id']:$this->_intParentId);
		
		/*
		* Push menu onto beginning of array
		*/
		array_unshift($arrLinks,$arrNav);
		
		$arrBreadcrumbs = array();
		foreach($arrLinks as $intIndex=>$arrLink) {
			
			$boolNav = $intIndex == 0?true:false;
			
			$arrBreadcrumbs[] = array(
				'label'=>$boolNav === true?$arrLink['menu_title']:$arrLink['link_title']
				,'href'=>parent::getBasePath().'/'.($boolNav === true?"{$arrLink['navigation_id']}/Nav":$arrLink['navigation_links_id'])
			);
		}
		
		if($this->_objParentModule instanceof NavigationListMenu) {
			array_unshift($arrBreadcrumbs,array(
				'label'=>'Menus'
				,'href'=>$this->_objParentModule->getBasePath(false)
			));
		}
		
		return $arrBreadcrumbs;
		
	}
	
}
?>