<?php 
class MCPNavigationMenu extends MCPModule {
	
	private
	
	/*
	* Navigation data access layer 
	*/
	$_objDAONavigation
	
	/*
	* Navigation menu data 
	*/
	,$_arrMenu;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		// Fetch Navigation DAO
		$this->_objDAONavigation = $this->_objMCP->getInstance('Component.Navigation.DAO.DAONavigation',array($this->_objMCP));
	}
	
	public function execute($arrArgs) {
		
		$intMenuId = !empty($arrArgs)?array_shift($arrArgs):null;
		
		/*
		* Switch to fetch by id or location for current site 
		*/
		if($intMenuId !== null) {
			if(in_array($intMenuId,array('top','left','right','bottom'))) {
				$this->_arrMenu = $this->_objDAONavigation->fetchNavBySiteLocation($intMenuId,$this->_objMCP->getSitesId());
			} else {
				$this->_arrMenu = $this->_objDAONavigation->fetchNavById($intMenuId);
			}
		}
		
		if($this->_arrMenu !== null) {
			$this->_arrTemplateData['menu'] = $this->_objDAONavigation->fetchMenu($this->_arrMenu['navigation_id']);
		} else {
			$this->_arrTemplateData['menu'] = array();
		}
		
		$this->_arrTemplateData['nav'] = $this->_arrMenu;
		return 'Menu/Menu.php';
	}
	
	/*
	* Paint menu recersive 
	*/
	public function paintMenu($arrLinks,$intRunner=0) {
		
		if( $intRunner === 0 ) {
			$strReturn = '<ul id="nav-'.str_replace('_','-',$this->_arrMenu['system_name']).'">';
		} else {
			$strReturn = '<ul>';
		}
		
		foreach($arrLinks as $arrLink) {
			
			$strReturn.= sprintf(
				"<li class=\"navigation-link-{$arrLink['navigation_links_id']}\">%s%s%s%s"
				,$arrLink['link_url'] === null && $arrLink['sites_internal_url'] === null?'':sprintf(
					'<a href="%s" target="%s">'
					,$arrLink['link_url'] !== null?$arrLink['link_url']:sprintf(
						'%s/%s%s'
						,$this->_objMCP->getBaseUrl()
						,$arrLink['sites_internal_url']
						,isset($arrLink['dynamic_vars'])?'/'.implode('/',$arrLink['dynamic_vars']):''
					)
					,$arrLink['target_window']
				)
				,htmlentities($arrLink['link_title'])
				,$arrLink['link_url'] === null && $arrLink['sites_internal_url'] === null?'':'</a>'
				,empty($arrLink['navigation_links'])?'</li>':$this->paintMenu($arrLink['navigation_links'],($intRunner+1)).'</li>'
			);
			
		}
		$strReturn.= '</ul>';
		return $strReturn;
		
	}
	
}
?>