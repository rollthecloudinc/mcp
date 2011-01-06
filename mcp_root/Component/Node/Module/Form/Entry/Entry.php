<?php 
/*
* Create and edit node entry 
*/
class MCPNodeFormEntry extends MCPModule {
	
	protected
	
	/*
	* Node data access layer 
	*/
	$_objDAONode
	
	/*
	* Form validator object
	*/
	,$_objValidator
	
	/*
	* Current node
	*/
	,$_arrNode
	
	/*
	* Form post data 
	*/
	,$_arrFrmPost
	
	/*
	* Form values 
	*/
	,$_arrFrmValues
	
	/*
	* Form errors 
	*/
	,$_arrFrmErrors
	
	/*
	* Preselect node type when creating new content via argument passed through URL 
	*/
	,$_strNodeTypeSelect;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {		
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	protected function _init() {
		// fetch node DAO
		$this->_objDAONode = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP));
		
		// Get validator
		$this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
		
		// Add custom validation rule
		$this->_objValidator->addRule('node_title',array($this,'validateNodeTitle'));
		
		// form values and errors
		$this->_arrFrmValues = array();
		$this->_arrFrmErrors = array();
		
		// Assign form post data
		$this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
		
	}
	
	/*
	* Begin processing form data 
	*/
	protected function _process() {
		
		/*
		* Set form values 
		*/
		$this->_setFrmValues();
		
		/*
		* Validate form values 
		*/
		if($this->_arrFrmPost !== null) {
			$this->_arrFrmErrors = $this->_objValidator->validate($this->_getFrmConfig(),$this->_arrFrmValues);
		}
		
		/*
		* Save form data to database 
		*/
		if($this->_arrFrmPost !== null && empty($this->_arrFrmErrors)) {
			$this->_frmSave();
		}
		
	}
	
	/*
	* Set form values 
	*/
	protected function _setFrmValues() {
		
		if($this->_arrFrmPost !== null) {
			$this->_setFrmSaved();
		} else if($this->_getNode() !== null) {
			$this->_setFrmEdit();
		} else {
			$this->_setFrmCreate();
		}
		
	}
	
	/*
	* Set form values as submitted 
	*/
	protected function _setFrmSaved() {
		
		/*
		* Get current node 
		*/
		$arrNode = $this->_getNode();
		
		/*
		* Config used to set static values 
		*/
		$arrConfig = $this->_getFrmConfig();
		
		/*
		* Pull values directly from post array 
		*/
		foreach($this->_getFrmFields() as $strField) {
			
			/*
			* Fill in static fields 
			*/
			if(isset($arrConfig[$strField],$arrConfig[$strField]['static']) && strcmp('Y',$arrConfig[$strField]['static']) == 0) {
				$this->_arrFrmValues[$strField] = isset($arrConfig[$strField]['default'])?$arrConfig[$strField]['default']:'';
				continue;
			}
			
			switch($strField) {
				
				default:
					$this->_arrFrmValues[$strField] = $this->_arrFrmPost[$strField];
			}
		}
		
	}
	
	/*
	* Set form values for current node 
	*/
	protected function _setFrmEdit() {
		
		/*
		* Get current node
		*/
		$arrNode = $this->_getNode();
		
		/*
		* Set values as current blog values 
		*/
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				
				case 'node_types_id':
					$arrNodeType = $this->_objDAONode->fetchNodeTypeById($arrNode['node_types_id']);
					
					// construct the expected value
					if($arrNodeType['pkg'] !== null) {
						$this->_arrFrmValues[$strField] = "{$arrNodeType['system_name']}::{$arrNodeType['pkg']}";
					} else {
						$this->_arrFrmValues[$strField] = $arrNodeType['system_name'];
					}
					
					break;
				
				default:
					$this->_arrFrmValues[$strField] = $arrNode[$strField];
			}
		}
		
	}
	
	/*
	* Set form values as empty for new entry 
	*/
	protected function _setFrmCreate() {
		
		/*
		* Set all fields to empty 
		*/
		foreach($this->_getFrmFields() as $strField) {
			
			switch($strField) {
				
				case 'node_published':
					$this->_arrFrmValues[$strField] = 1;
					break;
					
				case 'node_types_id':
					$this->_arrFrmValues[$strField] = $this->_strNodeTypeSelect !== null?$this->_strNodeTypeSelect:'';
					break;
				
				default:
					$this->_arrFrmValues[$strField] = '';
			
			}
		}
		
	}
	
	/*
	* Get forms name
	* 
	* @return str form name
	*/
	protected function _getFrmName() {
		return 'frmNodeEntry';
	}
	
	/*
	* Get form configuration 
	* 
	* @return array configuration
	*/
	protected function _getFrmConfig() {
		
		/*
		* get current node 
		*/
		$arrNode = $this->_getNode();
		
		$entity_id = null;
		
		// edit node
		if($arrNode !== null) {
			
			$entity_id = $arrNode['node_types_id'];
			
		// create new node
		} else if($this->_strNodeTypeSelect !== null) {
			
			$name = $this->_strNodeTypeSelect;
			
			// node types belonging to package need have the URL argument reversed to use the fetchNodeTypeByName method
			if(strpos($name,'::') !== false) {
				$tmp = explode('::',$name);
				$name = "{$tmp[1]}::{$tmp[0]}";
			}
			
			$arrNodeType = $this->_objDAONode->fetchNodeTypeByName($name);
			if($arrNodeType !== null) {
				$entity_id = $arrNodeType['node_types_id'];
			}
		}
		
		
		/*
		* Get config from MCP 
		* 
		* NEW: Adds in dynamic fields
		*/
		return $this->_objMCP->getFrmConfig($this->getPkg(),'frm',true,array('entity_type'=>'MCP_NODE_TYPES','entities_id'=>$entity_id));
	
	}
	
	/*
	* Get form fields to be processed 
	* 
	* @return array form fields
	*/
	protected function _getFrmFields() {
		return array_keys($this->_getFrmConfig());
	}
	
	/*
	* Save form values to database 
	*/
	protected function _frmSave() {
		
		/*
		* Copy values array 
		*/
		$arrValues = $this->_arrFrmValues;
		
		//echo '<pre>',print_r($arrValues),'</pre>';
		
		/*
		* Get current node 
		*/
		$arrNode = $this->_getNode();
		
		/*
		* engineer safe url matching title as close as possible
		*/
		$arrValues['node_url'] = $this->_objDAONode->engineerNodeUrl($arrValues['node_title']);
		
		if($arrNode !== null) {
			$arrValues['nodes_id'] = $arrNode['nodes_id'];
		} else {
			$arrValues['sites_id'] = $this->_objMCP->getSitesId();
			$arrValues['authors_id'] = $this->_objMCP->getUsersId();
		}
		
		/*
		* Break up node typre string into pkg and system name 
		*/
		$nodeTypeName = null;
		$nodeTypePkg = null;
		
		if(strpos($arrValues['node_types_id'],'::') !== false) {
			list($nodeTypeName,$nodeTypePkg) = explode('::',$arrValues['node_types_id'],2);
		} else {
			$nodeTypeName = $arrValues['node_types_id'];
		}
		
		/*
		* Build filter to locate node type primary key based on package, site and name 
		*/
		$strFilter = sprintf(
			"t.sites_id = %s AND t.system_name = '%s' AND t.pkg %s"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($nodeTypeName)
			,empty($nodeTypePkg)?'IS NULL':"= '{$this->_objMCP->escapeString($nodeTypePkg)}'"
		);
		
		/*
		* Locate primary key of node type 
		*/
		$arrNodeType = array_pop($this->_objDAONode->fetchNodeTypes('t.node_types_id',$strFilter));
		
		/*
		* Add node type 
		*/
		$arrValues['node_types_id'] = $arrNodeType['node_types_id'];
		
		/*
		* Save node to database 
		*/
		$this->_objDAONode->saveNode($arrValues);
		
		/*
		* Fire update event using this as the target
		*/
		$this->_objMCP->fire($this,'NODE_UPDATE');
		
	}
	
	/*
	* Get current node
	* 
	* @return array node
	*/
	protected function _getNode() {
		return $this->_arrNode;
	}
	
	public function execute($arrArgs) {
		
		/*
		* Extract node to edit
		*/
		$intNodeId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		/*
		* Set current node data 
		*/
		if($intNodeId !== null) {
			$this->_arrNode = $this->_objDAONode->fetchById($intNodeId);
		}
		
		/*
		* Preselect node type 
		*/
		$this->_strNodeTypeSelect = !empty($arrArgs)?array_shift($arrArgs):null;
		
		/*
		* Check permissions 
		* Can person edit or add node - based on node type?
		* - person may be restricted to creating or editing nodes of type
		*/
		if($intNodeId !== null) {
			$perm = $this->_objMCP->getPermission(MCP::EDIT,'Node',$intNodeId);
			
			if(!$perm['allow']) throw new MCPPermissionException($perm);
		} else if($this->_strNodeTypeSelect !== null) {
			
			$name = $this->_strNodeTypeSelect;
			
			// node types belonging to package need have the URL argument reversed to use the fetchNodeTypeByName method
			if(strpos($name,'::') !== false) {
				$tmp = explode('::',$name);
				$name = "{$tmp[1]}::{$tmp[0]}";
			}
			
			$arrNodeType = $this->_objDAONode->fetchNodeTypeByName($name);
			if($arrNodeType !== null) {
				$perm = $this->_objMCP->getPermission(MCP::ADD,'Node',$arrNodeType['node_types_id']);
				if(!$perm['allow']) throw new MCPPermissionException($perm);
			}	

		}
		
		/*
		* Process form 
		*/
		$this->_process();
		
		// echo '<pre>',print_r( $this->_getFrmConfig() ),'</pre>';
		// echo '<pre>',print_r($this->_getNode()),'</pre>';
		
		$this->_arrTemplateData['name'] = $this->_getFrmName();
		$this->_arrTemplateData['action'] = $this->getBasePath();
		$this->_arrTemplateData['method'] = 'POST';
		$this->_arrTemplateData['config'] = $this->_getFrmConfig();
		$this->_arrTemplateData['values'] = $this->_arrFrmValues;
		$this->_arrTemplateData['errors'] = $this->_arrFrmErrors;
		$this->_arrTemplateData['legend'] = 'Content';
		
		return 'Entry/Entry.php';
	}
	
	/*
	* Override base path to append node reference for edit
	* 
	* @return str base path
	*/
	public function getBasePath() {
		
		$strPath = parent::getBasePath();
		$arrNode = $this->_getNode();
		
		if($arrNode !== null) {
			$strPath.= "/{$arrNode['nodes_id']}";
		}
		
		if($this->_strNodeTypeSelect !== null) {
			$strPath.= "/{$this->_strNodeTypeSelect}";
		}
		
		return $strPath;
	}
	
	/*
	* Restricts two node entries with the same node_url, site and deleted combination. This is 
	* a critical part of being able to use the title to reference the node for searche engine
	* friendly url creation.
	* 
	* @param mix form element value
	* @param str form element label
	* @return str error feeback string
	*/
	public function validateNodeTitle($mixValue,$strLabel) {
		
		$boolPassed = false;
		
		/*
		* Get the current node 
		*/
		$arrNode = $this->_getNode();
		
		/*
		* Engineer the url for this node 
		*/
		$strNodeUrl = $this->_objDAONode->engineerNodeUrl($mixValue);
		
		/*
		* Break up node type string into pkg and system name 
		*/
		$nodeTypeName = null;
		$nodeTypePkg = null;
		
		if(strpos($this->_arrFrmValues['node_types_id'],'::') !== false) {
			list($nodeTypeName,$nodeTypePkg) = explode('::',$this->_arrFrmValues['node_types_id'],2);
		} else {
			$nodeTypeName = $this->_arrFrmValues['node_types_id'];
		}
		
		/*
		* Build filter to locate node type primary key based on package, site and name 
		*/
		$strFilter = sprintf(
			"t.sites_id = %s AND t.system_name = '%s' AND t.pkg %s"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($nodeTypeName)
			,empty($nodeTypePkg)?'IS NULL':"= '{$this->_objMCP->escapeString($nodeTypePkg)}'"
		);
		
		/*
		* Locate primary key of node type 
		*/
		$arrNodeType = array_pop($this->_objDAONode->fetchNodeTypes('t.node_types_id',$strFilter));
		
		/*
		* Locate node with same url 
		*/
		$arrMatch = $this->_objDAONode->fetchNodeByUrl($strNodeUrl,$this->_objMCP->getSitesId(),$arrNodeType['node_types_id']);
		
		if($arrMatch !== null && $arrNode !== null && $arrMatch['nodes_id'] == $arrNode['nodes_id']) {
			$boolPassed = true;
		} else if($arrMatch === null) {
			$boolPassed = true;
		}
		
		/*
		* Add error message 
		*/
		if($boolPassed === false) {
			return "$strLabel found for existing content. Please choose a different $strLabel.";
		}
		
		return '';
		
	}
	
}
?>