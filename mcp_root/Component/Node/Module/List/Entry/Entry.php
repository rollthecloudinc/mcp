<?php 
/*
* Lists nodes with pagination 
*/
class MCPNodeListEntry extends MCPModule {
	
	protected
	
	/*
	* node data access layer 
	*/
	$_objDAONode
	
	/*
	* The current page being viewed 
	*/
	,$_intPage
	
	/*
	* The current node type being viewed / filtered 
	*/
	,$_arrNodeType
	
	/*
	* The current nodes id being viewed 
	*/
	,$_intNodeId
	
	/*
	* Internal redirect
	*/
	,$_strRequest
	
	/*
	* Node id to perform action on 
	*/
	,$_intActionsId;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	protected function _init() {
		// Fetch node DAO
		$this->_objDAONode = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP));
	
		// set-up delete event handler
		$id =& $this->_intActionsId;
		$dao = $this->_objDAONode;
                $mcp = $this->_objMCP;
		
		$this->_objMCP->subscribe($this,'NODE_DELETE',function() use(&$id,$dao,$mcp)  {
                    
			// delete the node type
                        try {                 
                            $dao->deleteNodes($id);
                            $mcp->addSystemStatusMessage(
                                 'Content has been successfully deleted.'   
                            );
                        } catch(MCPDAOException $e) {
                            $mcp->_objMCP->addSystemErrorMessage(
                                    'An error has occurred attempting to delete node. Please try again and contact an administrator if error continues.'
                                    ,$e->getMessage()
                            );
                        }
                        
		});
		
	}
	
	/*
	* Handle form submit 
	*/
	private function _handleFrm() {
		
		/*
		* Get posted form data 
		*/
		$arrPost = $this->_objMCP->getPost('frmNodeList');
		
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
			$this->_objMCP->fire($this,"NODE_".strtoupper($strAction));
		}
		
	}
	
	/*
	* Paginates nodes (called by pagination component)
	* 
	* @param int SQL offset
	* @param int SQL limit
	* @return int found rows
	*/
	public function paginate($intOffset,$intLimit) {
		
		/*
		* Fetch nodes 
		*/
		$arrResult = $this->_objDAONode->listAll('n.*,u.username',$this->_getFilter(),$this->_getSort(),"$intOffset,$intLimit");
		
		/*
		* Assign nodes to display 
		*/
		$this->_arrTemplateData['nodes'] = array_shift($arrResult);
		
		/*
		* Add flag to allow edit of node 
		*/
		$ids = array();
		foreach($this->_arrTemplateData['nodes'] as $node) $ids[] = $node['nodes_id'];
		
		if(!empty($ids)) {
			$editPerms = $this->_objMCP->getPermission(MCP::EDIT,'Node',$ids);
			$deletePerms = $this->_objMCP->getPermission(MCP::DELETE,'Node',$ids);
		}
		
		
		foreach($this->_arrTemplateData['nodes'] as &$node) {
			$node['allow_edit'] = $editPerms[$node['nodes_id']]['allow'];
			$node['allow_delete'] = $deletePerms[$node['nodes_id']]['allow'];
		}
		
		/*
		* Return control to pagination module and send back number of nodes
		*/
		return array_shift($arrResult);
	}
	
	/*
	* Filter for nodes
	* 
	* @return str where clause
	*/
	protected function _getFilter() {
		
		/*
		* Get the current node type 
		*/
		$arrNodeType = $this->_getNodeType();
		
		$filter = sprintf(
			"%s n.deleted = 0"
			
			// view node of the specified type
			,$arrNodeType !== null?"t.node_types_id = {$this->_objMCP->escapeString($arrNodeType['node_types_id'])} AND ":''
		);
		
		return $filter;
	}
	
	/*
	* Sort order for nodes 
	* 
	* @return str order by clause
	*/
	protected function _getSort() {
		return $this->getConfigValue('nodes_sort_order');
	}
	
	/*
	* Nodes of specified type will be shown
	* 
	* @param array node type data
	*/
	protected function _setNodeType($arrNodeType) {
		$this->_arrNodeType = $arrNodeType;
	}
	
	/*
	* Get the current node type
	* 
	* @return array node type data
	*/
	protected function _getNodeType() {
		return $this->_arrNodeType;
	}
	
	/*
	* Get table headers for displaying info 
	* 
	* @return array headers
	*/
	protected function _getHeaders() {
		
		$mod = $this;
		$mcp = $this->_objMCP;
		
		return array(
			array(
				'label'=>'Title'
				,'column'=>'node_title'
				,'mutation'=>function($title,$row) use ($mod,$mcp) {
					
					return $mcp->ui(
						'Common.Field.Link'
						,array(
							'url'=>"{$mod->getBasePath()}/View/{$row['nodes_id']}"
							,'label'=>$title
						)
					);
					
				}
			)
			,array(
				'label'=>'Published'
				,'column'=>'node_published'
				,'mutation'=>function($value,$row) {
					return $value?'Y':'N';
				}
			)
			,array(
				'label'=>'&nbsp;'
				,'column'=>'nodes_id'
				,'mutation'=>array($this,'displayNodeEditLink')
			)
			,array(
				'label'=>'&nbsp;'
				,'column'=>'nodes_id'
				,'mutation'=>function($value,$row) use ($mcp) {
					return $mcp->ui('Common.Form.Input',array(
						'value'=>'Delete'
						,'name'=>"frmNodeList[action][delete][$value]"
						,'disabled'=>!$row['allow_delete']
                                                ,'type'=>'submit'
                                                ,'class'=>'btn danger'
					));
				}
			)
		);
	}
	
	public function execute($arrArgs) {
		
		/*
		* Type is required
		*/
		$strNodeType = array_shift($arrArgs);
		
		/*
		* Page the page number
		*/
		$this->_intPage = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):1;
		
		/*
		* Set current node type
		*/
		$this->_setNodeType($this->_objDAONode->fetchNodeTypeByName($strNodeType));
		
		/*
		* Get redirect
		*/
		$this->_strRequest = !empty($arrArgs) && in_array($arrArgs[0],array('Create','Edit','View'))?array_shift($arrArgs):null;
		
		/*
		* Handle form submit  
		*/
		$this->_handleFrm();
		
		/*
		* Number of nodes per page 
		*/
		$intLimit = $this->getConfigValue('nodes_per_page');
		
		/*
		* Handle internal redirect 
		*/
		$strTpl = 'Entry';
		$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = '';
		
		// add or edit existing node
		if(strcmp('Create',$this->_strRequest) === 0 || strcmp('Edit',$this->_strRequest) === 0) {
			$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = $this->_objMCP->executeComponent(
				'Component.Node.Module.Form.Entry'
				,$arrArgs
				,null
				,array($this)
			);
			$strTpl = 'Redirect';
			
		} else if(strcmp('View',$this->_strRequest) === 0) {
			$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = $this->_objMCP->executeComponent(
				'Component.Node.Module.View.Entry'
				,$arrArgs
				,null
				,array($this)
			);
			$strTpl = 'Redirect';
			
		}
		
		/*
		* Set template data 
		*/
		
		// Get current node type 
		$arrNodeType = $this->_getNodeType();
		
		// Table headers
		$this->_arrTemplateData['headers'] = $this->_getHeaders();
		
		// Page heading 
		$this->_arrTemplateData['header'] = $arrNodeType !== null?$arrNodeType['human_name']:'Content';
		
		// Create label 
		$this->_arrTemplateData['create_label'] = $arrNodeType !== null?$arrNodeType['human_name']:'Content';
		
		// Create a new node of specified type link 
		$this->_arrTemplateData['create_link'] = "{$this->getBasePath(false)}/{$this->_intPage}/Create/".($arrNodeType !== null?( !empty($arrNodeType['pkg']) ?"{$arrNodeType['system_name']}::{$arrNodeType['pkg']}":$arrNodeType['system_name']):'');
		
		// Back label 
		$this->_arrTemplateData['back_label'] = $arrNodeType !== null?$arrNodeType['human_name']:'Content';
		
		// Redirect back link
		$this->_arrTemplateData['back_link'] = "{$this->getBasePath(false)}/{$this->_intPage}";
		
		// Allow creation of new content under specified node type?
		if($arrNodeType !== null) {
			$perm = $this->_objMCP->getPermission(MCP::ADD,'Node',$arrNodeType['node_types_id']);
			$this->_arrTemplateData['allow_node_create'] = $perm['allow'];
		} else {
			$this->_arrTemplateData['allow_node_create'] = false;
		}
		
		/*
		* Form action
		*/
		$this->_arrTemplateData['frm_action'] = $this->getBasePath();
		
		/*
		* Form name 
		*/
		$this->_arrTemplateData['frm_name'] = 'frmNodeList';
		
		/*
		* Form method 
		*/
		$this->_arrTemplateData['frm_method'] = 'POST';
		
		// Execute pagination module
		$this->_arrTemplateData['PAGINATION_TPL'] = $this->_objMCP->executeComponent('Component.Util.Module.Pagination',array($intLimit,$this->_intPage),'Component.Util.Template',array($this));
		
		//$this->_arrTemplateData['BASE_PATH'] = $this->getBasePath();
		//$this->_arrTemplateData['VIEW_NODE_PATH'] = $this->getBasePath(); //."/{$this->_getNodeEntrySwitch()}";
		//$this->_arrTemplateData['node'] = null; //$this->_intNodeId;
		//$this->_arrTemplateData['VIEW_TPL'] = ''; //$strNestedTpl;
		//$this->_arrTemplateData['edit_label'] = 'Node'; //$this->_getEditLabel();
		$this->_arrTemplateData['display_username'] = $this->getConfigValue('display_username');
		$this->_arrTemplateData['display_created_on_timestamp'] = $this->getConfigValue('display_created_on_timestamp');
		$this->_arrTemplateData['display_pagination'] = $this->getConfigValue('display_pagination');
		
		return "Entry/$strTpl.php";
		
	}
	
	/*
	* manufacturer new base path from arguments supplied in request
	* 
	* @return str base path
	*/
	public function getBasePath($redirect=true) {
		$strBasePath = parent::getBasePath();
		
		/*
		* Get the current node type 
		*/
		$arrNodeType = $this->_getNodeType();
		
		// add node type name
		if($arrNodeType !== null) {
			$strBasePath.= "/{$this->_objDAONode->getNodeTypeName($arrNodeType)}";
		}
		
		// add redirect flag
		if($redirect === true && $this->_strRequest !== null) {
			$strBasePath.= "/{$this->_strRequest}";
		}
		
		return $strBasePath;
	}
	
	/*
	* Header callback for displaying node edit link
	* 
	* @param mix bound column value
	* @param array node row data
	* @return str HTML to output
	*/
	public function displayNodeEditLink($value,$row) {
		
		if(!$row['allow_edit']) {
			return 'Edit';
		}
		
		return sprintf(
			'<a href="%s/Edit/%s" title="Edit Content">Edit</a>'
			,$this->getBasePath(false)
			,$row['nodes_id']
		);
		
	}
	
	/*
	* Event handler for when the node changes 
	*/
	public function onNodeChange() {
	}
	
}
?>