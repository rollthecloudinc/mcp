<?php 
/*
* View  a single node entry 
*/
class MCPNodeViewEntry extends MCPModule {
	
	protected
	
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
	,$_boolEdit = false
        
        /*
        * Allow module to be overridden 
        */
        ,$_boolAllowOverride = true;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		// Fetch node DAO
		$this->_objDAONode = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP));
	}
	
	public function execute($arrArgs) {
            
                $arrArgsCopy = $arrArgs;
		
		$this->_strNodesId = !empty($arrArgs)?array_shift($arrArgs):null;
		
		/*
		* Edit node switch 
		*/
		$this->_boolEdit = !empty($arrArgs) && strcmp($arrArgs[0],'Edit') == 0 && array_shift($arrArgs)?true:false; 

		if($this->_strNodesId !== null) {
			
			if( is_numeric($this->_strNodesId) ) {
				$this->_arrTemplateData['node'] = $this->_objDAONode->fetchById((int) $this->_strNodesId);
				
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
                * Make the default title of the page the node title. This
                * can be overriden in the template to use a more specific title
                * using the same call considering the mcp is available in the template.
                */
                if( isset($this->_arrTemplateData['node']) ) {
                    $this->_objMCP->setMetaData('title',$this->_arrTemplateData['node']['node_title']);
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
				
                                /*
                                * Experimental - seems like it may cause more issues than it solves
                                *  
                                * Check for module override and execute that if it exists. This
                                * makes it possible to completely override the view implemention
                                * of this module. This can be used for sectioning off content into tabs
                                * or just about anything else. 
                                */
                                /*if($this->_boolAllowOverride === true && isset($arrNodeType['view_mod'])) {
                                    
                                    // path to edit node
                                    $this->_arrTemplateData['EDIT_PATH'] = $this->getBasePath().'/Edit';
                                    
                                    // allow edit?
                                    $perm =  $this->_objMCP->getPermission(MCP::EDIT,'Node',(int) $this->_strNodesId);
                                    $this->_arrTemplateData['display_edit_link'] = $perm['allow'];
                                    
                                    $this->_arrTemplateData['TPL_OVERRIDE_CONTENT'] = $this->_objMCP->executeComponent(
                                            $arrNodeType['view_mod']
                                            ,$arrArgsCopy
                                            ,null
                                            ,array($this->_objParentModule)
                                    );
                                    return 'Entry/Override.php';
                                }*/
                                
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
                
                /*
                * Testing view type stuff 
                */
                $objDAOView = $this->_objMCP->getInstance('Component.View.DAO.DAOView',array($this->_objMCP));
                $arrFields = $objDAOView->fetchFieldsByViewPath("Node:{$this->_arrTemplateData['node']['node_types_id']}");
                $this->_arrTemplateData['schema'] = $arrFields;
		
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