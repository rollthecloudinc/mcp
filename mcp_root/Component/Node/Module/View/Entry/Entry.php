<?php 
/*
* View  a single node entry 
*/
class MCPNodeViewEntry extends MCPModule {
	
	private
	
	/*
	* Node data access layer 
	*/
	$_objDAONode
	
	/*
	* Current node being viewed 
	*/
	,$_strNodesId
	
	/*
	* Determines if node is being edited 
	*/
	,$_boolEdit = false;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		// Fetch node DAO
		$this->_objDAONode = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP));
	}
	
	/*
	* Determine whether edit link will be displayed/accessible based on permission settings
	* 
	* @retrun bool yes/no
	*/
	private function _determineWhetherToDisplayEditLink() {
		
		$arrNode = $this->_arrTemplateData['node'];
		
		if($arrNode === null) return false;
		
		$boolShowLink = $this->_objMCP->hasPermission('LOGGED_IN',$this);
		
		/*if(!$boolShowLink) return $boolShowLink;
		
		$boolShowLink = $this->_objMCP->hasPermission('MCP_NODE_EDIT_ALL',$this);
		
		if($boolShowLink === false && $this->_objMCP->getUsersId() == $arrNode['authors_id']) {
			$boolShowLink = $this->_objMCP->hasPermission('MCP_NODE_EDIT_OWN',$this);
		} else if($boolEditAll === false) {
			$boolShowLink = $this->_objMCP->hasPermission('MCP_NODE_EDIT_ALL',$this);
		}*/

		return $boolShowLink;
	}
	
	public function execute($arrArgs) {
		
		$this->_strNodesId = !empty($arrArgs)?array_shift($arrArgs):null;
		
		/*
		* Edit node switch 
		*/
		$this->_boolEdit = !empty($arrArgs) && strcmp($arrArgs[0],'Edit') == 0 && array_shift($arrArgs)?true:false; 
		
		if($this->_strNodesId !== null) {
			$this->_arrTemplateData['node'] = $this->_objDAONode->fetchNodeByUrl($this->_strNodesId,$this->_objMCP->getSitesId());
		} else {
			$this->_arrTemplateData['node'] = null;
		}
		
		/*
		* Swap template for edit 
		*/
		$strEditTpl = '';
		if($this->_boolEdit === true) {
			$strEditTpl = $this->_objMCP->executeComponent(
				'Component.Node.Module.Form.Entry'
				,array($this->_arrTemplateData['node']['nodes_id'])
				,null
				,array($this)
				,array(array($this,'onNodeUpdate'),'NODE_UPDATE')
			);
			$strTpl = 'Entry/Edit.php';
		} else {
			$strTpl = 'Entry/Entry.php';
		}
		
		$this->_arrTemplateData['BASE_PATH'] = $this->getBasePath(false);
		$this->_arrTemplateData['EDIT_PATH'] = $this->getBasePath().'/Edit';
		$this->_arrTemplateData['EDIT_TPL'] = $strEditTpl;
		$this->_arrTemplateData['display_comments'] = $this->getConfigValue('display_comments');
		$this->_arrTemplateData['display_edit_link'] = $this->_determineWhetherToDisplayEditLink();
		
		return $strTpl;
	}
	
	public function getBasePath($boolEdit=true) {
		$strBasePath = parent::getBasePath();
		
		if($this->_strNodesId !== null) {
			$strBasePath.= "/{$this->_strNodesId}";
		}
		
		if($boolEdit === true && $this->_boolEdit === true) {
			$strBasePath.= '/Edit';
		}
		
		return $strBasePath;
	}
	
	/*
	* Event handler for when a node is updated
	*/
	public function onNodeUpdate($arrEvt) {
		$arrNode = $this->_objDAONode->fetchById($this->_arrTemplateData['node']['nodes_id']);
		$this->_strNodesId = $arrNode['node_url'];
		// $this->_objMCP->capture(get_class($arrEvt['target']).' fired '.$arrEvt['event']);
	}
	
}
?>