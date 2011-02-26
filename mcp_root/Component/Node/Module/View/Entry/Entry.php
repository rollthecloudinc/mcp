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
	
	public function execute($arrArgs) {
		
		$this->_strNodesId = !empty($arrArgs)?array_shift($arrArgs):null;
		
		/*
		* Edit node switch 
		*/
		$this->_boolEdit = !empty($arrArgs) && strcmp($arrArgs[0],'Edit') == 0 && array_shift($arrArgs)?true:false; 

		if($this->_strNodesId !== null) {
			
			if( is_numeric($this->_strNodesId) ) {
				$this->_arrTemplateData['node'] = $this->_objDAONode->fetchById((int) $this->_strNodesId,'*',true);
				
				$this->_replacePlaceholders($this->_arrTemplateData['node']);
			} else {
				/*
				* @TODO: Need to pass the node type also 
				*/
				// $this->_arrTemplateData['node'] = $this->_objDAONode->fetchNodeByUrl($this->_strNodesId,$this->_objMCP->getSitesId());
			}
			
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
				/*,array(array($this,'onNodeUpdate'),'NODE_UPDATE')*/
			);
			$strTpl = 'Entry/Edit.php';
		} else {
			$strTpl = 'Entry/Entry.php';
			
			// Get the theme template
			if($this->_arrTemplateData['node'] !== null) {
				// Get the node type data to resolve theme
				$arrNodeType = $this->_objDAONode->fetchNodeTypeById($this->_arrTemplateData['node']['node_types_id']);
				
				// Set the node type theme template
				$strTpl = $arrNodeType['theme_tpl'] === null?$strTpl:$arrNodeType['theme_tpl'];
			}
			
		}
		
		$this->_arrTemplateData['BASE_PATH'] = $this->getBasePath(false);
		$this->_arrTemplateData['EDIT_PATH'] = $this->getBasePath().'/Edit';
		$this->_arrTemplateData['EDIT_TPL'] = $strEditTpl;
		$this->_arrTemplateData['display_comments'] = $this->getConfigValue('display_comments');
		
		// allow edit?
		$perm =  $this->_objMCP->getPermission(MCP::EDIT,'Node',(int) $this->_strNodesId);
		$this->_arrTemplateData['display_edit_link'] = $perm['allow'];
		
		// comment form
		$this->_arrTemplateData['comment'] = $this->_objMCP->executeComponent(
			'Component.Node.Module.Form.Comment'
			,array('Node', $this->_arrTemplateData['node']['nodes_id'] )
			,null
			,array($this)
		);
		
		// comments list module
		$this->_arrTemplateData['comments'] = $this->_objMCP->executeComponent(
			'Component.Node.Module.List.Comment'
			,array($this->_arrTemplateData['node']['nodes_id'])
			,null
			,array($this)
		);
		
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
	/*public function onNodeUpdate($arrEvt) {
		$arrNode = $this->_objDAONode->fetchById($this->_arrTemplateData['node']['nodes_id']);
		$this->_strNodesId = $arrNode['node_url'];
		// $this->_objMCP->capture(get_class($arrEvt['target']).' fired '.$arrEvt['event']);
	}*/
	
	protected function _replacePlaceholders($arrNode) {
		
		$matches = array();
		
		preg_match_all('/<mcp.*?>/xsm',$arrNode['node_content'],$matches);
		
		//echo '<pre>',print_r($matches),'</pre>';
		
	}
	
}
?>