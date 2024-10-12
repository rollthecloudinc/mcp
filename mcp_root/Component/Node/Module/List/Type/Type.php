<?php 
/*
* List node types 
*/
class MCPNodeListType extends MCPModule {
	
	protected
	
	/*
	* Node data access layer 
	*/
	$_objDAONode
	
	/*
	* Current letter being paginated on 
	*/
	,$_strLetter
	
	/*
	* Alternate routing path 
	*/
	,$_strRequest
	
	/*
	* Node type id to perform action on 
	*/
	,$_intActionsId;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		// Get node data access layer
		$this->_objDAONode = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP));
		
		// set-up delete event handler
		$id =& $this->_intActionsId;
		$dao = $this->_objDAONode;
                $mcp = $this->_objMCP;
		
		$this->_objMCP->subscribe($this,'NODE_TYPE_DELETE',function() use(&$id,$dao,$mcp)  {
                    
                        // delete the node type
                        try {
                            
                            $dao->deleteNodeTypes($id);
                            $mcp->addSystemStatusMessage(
                                 'Content type has been successfully deleted.'   
                            );
                            
                        } catch(MCPDAOException $e) {
                            $mcp->addSystemErrorMessage(
                                    'An error has occurred attempting to delete content type. Please try again and contact an administrator if error continues.'
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
		$arrPost = $this->_objMCP->getPost('frmNodeTypeList');
		
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
			$this->_objMCP->fire($this,"NODE_TYPE_".strtoupper($strAction));
		}
		
	}
	
	/*
	* Alphabetiation callback
	* 
	* @param str letter
	* @return int found rows
	*/
	public function alphabetize($strLetter) {
		
		/*
		* Only run main query when module isn't being redirected 
		*/
		if($this->_strRequest !== null) return 0;
		
		// Fetch node types
		$this->_arrTemplateData['node_types'] = $this->_arrTemplateData['node_types'] = $this->_objDAONode->fetchNodeTypes('t.*',$this->_getFilter($strLetter),$this->_getSort());
		
		// mixin permissions to edit and delete content to node type
		$ids = array();
		foreach($this->_arrTemplateData['node_types'] as $nodeType) {
			$ids[] = $nodeType['node_types_id'];
		}
		
		if(!empty($ids)) {
			$editPerms = $this->_objMCP->getPermission(MCP::EDIT,'NodeType',$ids);
			$deletePerms = $this->_objMCP->getPermission(MCP::DELETE,'NodeType',$ids);
		}
		
		foreach($this->_arrTemplateData['node_types'] as &$nodeType) {
			$nodeType['allow_edit'] = $editPerms[$nodeType['node_types_id']]['allow'];
			$nodeType['allow_delete'] = $deletePerms[$nodeType['node_types_id']]['allow'];
		}
		
	}
	
	/*
	* SQL filter
	* 
	* @param str letter to paginate on
	* @return str SQL where clause
	*/
	protected function _getFilter($strLetter=null) {
		
		// Get node types that belong to current site
		return sprintf(
			't.deleted = 0 AND t.sites_id = %s %s'
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$strLetter !== null?" AND t.system_name LIKE '{$this->_objMCP->escapeString($strLetter)}%'":''
		);
		
	}
	
	/*
	* SQL sort
	* 
	* @return str SQL order by clause
	*/
	protected function _getSort() {
		return null;
	}
	
	/*
	* get header configuration array for table display 
	*/
	protected function _getHeaders() {
		
		$mcp = $this->_objMCP;
		return array(
			array(
				'label'=>'Name'
				,'column'=>'node_types_id'
				,'mutation'=>array($this,'DisplayNodeTypeName')
			)	
			
			,array(
				'label'=>'&nbsp;'
				,'column'=>'node_types_id'
				,'mutation'=>array($this,'displayNodeTypeDynamicFieldLink')
			)
			
			,array(
				'label'=>'&nbsp;'
				,'column'=>'node_types_id'
				,'mutation'=>array($this,'displayNodeContentLink')
			)
			,array(
				'label'=>'&nbsp;'
				,'column'=>'node_types_id'
				,'mutation'=>function($value,$row) use ($mcp) {
					return $mcp->ui('Common.Form.Input',array(
						'value'=>'Delete'
						,'name'=>"frmNodeTypeList[action][delete][$value]"
						,'disabled'=>!$row['allow_delete']
                                                ,'type'=>'Submit'
                                                ,'class'=>'btn danger'
					));
				}
			)
		);
	}
	
	public function execute($arrArgs) {
		
		/*
		* Current page number 
		*/
		$this->_strLetter = !empty($arrArgs) && in_array($arrArgs[0],range('A','Z'))?array_shift($arrArgs):null;
		
		/*
		* Alternate internal redirect route
		*/
		$this->_strRequest = !empty($arrArgs) && in_array($arrArgs[0],array('Edit','Type','Fields','Add'))?array_shift($arrArgs):null;
				
		/*
		* Handle form submit  
		*/
		$this->_handleFrm();
		
		/*
		* Number of items per page 
		*/
		$intLimit = 10;
		
		// Paginate module (aphabetization)
		//$this->_arrTemplateData['PAGINATION_TPL'] = $this->_objMCP->executeComponent('Component.Util.Module.Pagination',array($intLimit,$this->_intPage),'Component.Util.Template',array($this));
		$this->_arrTemplateData['PAGINATION_TPL'] = $this->_objMCP->executeComponent('Component.Util.Module.Pagination.Alphabetize',array($this->_strLetter),'Component.Util.Template',array($this));
		
		/*
		* Set the display headers 
		*/
		$this->_arrTemplateData['headers'] = $this->_getHeaders();
		
		/*
		* Add the back link for internal redirects 
		*/
		$this->_arrTemplateData['back_link'] = $this->getBasePath(true,false);
		
		/*
		* Create node type link 
		*/
		$this->_arrTemplateData['create_link'] = "{$this->getBasePath(true,false)}/Add";
		
		/*
		* Form action
		*/
		$this->_arrTemplateData['frm_action'] = $this->getBasePath();
		
		/*
		* Form name 
		*/
		$this->_arrTemplateData['frm_name'] = 'frmNodeTypeList';
		
		/*
		* Form method 
		*/
		$this->_arrTemplateData['frm_method'] = 'POST';
		
		/*
		* set flag whether user is allowed to create node types 
		*/
		$create_perm = $this->_objMCP->getPermission(MCP::ADD,'NodeType',null);
		$this->_arrTemplateData['allow_node_type_create'] = $create_perm['allow'];
		
		/*
		* Primary template file and redirect content
		*/	
		$strTpl = 'Type';
		$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = '';
		
		// Edit node type
		if(strcmp('Edit',$this->_strRequest) === 0 || strcmp('Add',$this->_strRequest) === 0) {
			$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = $this->_objMCP->executeComponent(
				'Component.Node.Module.Form.Type'
				,$arrArgs
				,null
				,array($this)
			);
			$strTpl = 'Redirect';
			
		// Entries redirect (content of the specified type)
		} else if(strcmp('Type',$this->_strRequest) === 0) {
			$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = $this->_objMCP->executeComponent(
				'Component.Node.Module.List.Entry'
				,$arrArgs
				,null
				,array($this)
			);
			$strTpl = 'Redirect';		
		
		// Node types fields
		} else if(strcmp('Fields',$this->_strRequest) === 0) {
			$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = $this->_objMCP->executeComponent(
				'Component.Field.Module.List'
				,$arrArgs
				,null
				,array($this)
			);
			$strTpl = 'Redirect';			
		}
		
		
		/*
		* handle module redirection 
		*/
		
		return "Type/$strTpl.php";
	}
	
	/*
	* Get base path to current module state
	* 
	* @return str base path
	*/
	public function getBasePath($letter=true,$redirect=true) {
		$strBasePath = parent::getBasePath();
		
		// Add the current page letter
		if($letter === true && $this->_strLetter !== null) {
			$strBasePath.= "/{$this->_strLetter}";
		}
		
		// append redirect flag
		if($redirect === true && $this->_strRequest !== null) {
			$strBasePath.= "/{$this->_strRequest}";
		}
		
		return $strBasePath;
	}
	
	/*
	* Display callback for showing node types name
	* 
	* @param mix value
	* @param array node type data
	* @return string to echo
	*/
	public function displayNodeTypeName($value,$row) {
		
		$name = $row['system_name'];
		
		// node types with a package get it pre-pended
		if( !empty($row['pkg']) ) {
			$name = "{$row['pkg']}::$name";
		}
		
		if(!$row['allow_edit']) {
			return $name;
		}
		
		// create edit link with name
		return sprintf(
			'<a href="%s/Edit/%u">%s</a>'
			,$this->getBasePath()
			,$row['node_types_id']
			,$name
		);
	}
	
	/*
	* Display callback to view content of specific node type
	* 
	* @param mix value
	* @param array node type data
	* @return string to echo
	*/
	public function displayNodeContentLink($value,$row) {
		
		$name = $row['system_name'];
		
		// node types with a package get it pre-pended
		if( !empty($row['pkg']) ) {
			$name = "{$row['pkg']}::$name";
		}
		
		// create edit link with name
		return sprintf(
			'<a href="%s/Type/%s">Entries</a>'
			,$this->getBasePath()
			,$name
		);
		
	}
	
	/*
	* Display callback to view content type dynamic fields
	* 
	* @param mix value
	* @param array node type data
	* @return str output string
	*/
	public function displayNodeTypeDynamicFieldLink($value,$row) {
		return sprintf(
			'<a href="%s/Fields/MCP_NODE_TYPES/%u">Fields</a>'
			,$this->getBasePath()
			,$value
		);
	}
	
}
?>