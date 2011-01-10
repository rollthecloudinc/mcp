<?php 
/*
*  Content type data acess layer
*/
class MCPNodeFormType extends MCPModule {
	
	protected
	
	/*
	* Node data access layer 
	*/
	$_objDAONode
	
	/*
	* Validation object 
	*/
	,$_objValidator
	
	/*
	* Form post 
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
	* Node type for edit 
	*/
	,$_arrNodeType;
	
	public function __construct(MCP $objMCP,MCPModule $objModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objModule,$arrConfig);
		$this->_init();
	}
	
	protected function _init() {
		// Get node data access object
		$this->_objDAONode = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP));
	
		// Get validator
		$this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
		
		// Add custom validation handling
		$this->_objValidator->addRule('node_type_system_name',array($this,'validateSystemName'));
		$this->_objValidator->addRule('node_type_human_name',array($this,'validateHumanName'));
		
		// Get form post data
		$this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
		
		// Reset form values and errors
		$this->_arrFrmValues = array();
		$this->_arrFrmValues = array();
	}
	
	/*
	* Handle form processing 
	*/
	protected function _frmHandle() {
		
		/*
		* Set form values 
		*/
		$this->_setFrmValues();
		
		/*
		* Run validation 
		*/
		if($this->_arrFrmPost !== null) {
			$this->_arrFrmErrors = $this->_objValidator->validate($this->_getFrmConfig(),$this->_arrFrmValues);
		}
		
		/*
		* Save to db 
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
		} else if($this->_arrNodeType !== null) {
			$this->_setFrmEdit();
		} else {
			$this->_setFrmCreate();
		}
	}
	
	/*
	* Set form values as submitted 
	*/
	protected function _setFrmSaved() {
		
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				default:
					$this->_arrFrmValues[$strField] = isset($this->_arrFrmPost[$strField])?$this->_arrFrmPost[$strField]:'';
			}
		}
		
	}
	
	/*
	* Set form values from current content type 
	*/
	protected function _setFrmEdit() {
		
		/*
		* Get current node type being edited 
		*/
		$arrNodeType = $this->_getNodeType();
		
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				default:
					$this->_arrFrmValues[$strField] = isset($arrNodeType[$strField])?$arrNodeType[$strField]:'';
			}
		}
		
	}
	
	/*
	* Set form values as defaults
	*/
	protected function _setFrmCreate() {
		
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				default:
					$this->_arrFrmValues[$strField] = '';
			}
		}
		
	}
	
	/*
	* Save form data to db 
	*/
	protected function _frmSave() {
		
		/*
		* Copy values array 
		*/
		$arrValues = $this->_arrFrmValues;
		
		/*
		* Get current node type
		*/
		$arrNodeType = $this->_getNodeType();
		
		if($arrNodeType !== null) {
			$arrValues['node_types_id'] = $arrNodeType['node_types_id'];
		} else {
			$arrValues['sites_id'] = $this->_objMCP->getSitesId();
			$arrValues['creators_id'] = $this->_objMCP->getUsersId();
		}
		
		/*
		* Save node to database 
		*/
		$this->_objDAONode->saveNodeType($arrValues);
		
	}
	
	/*
	* Get form configuration
	* 
	* @return array form configuration
	*/
	protected function _getFrmConfig() {
		return $this->_objMCP->getFrmConfig($this->getPkg());
	}
	
	/*
	* Get form field names 
	* 
	* @return array form field names
	*/
	protected function _getFrmFields() {
		return array_keys($this->_getFrmConfig());
	}
	
	/*
	* Get the form name
	* 
	* @return str form name
	*/
	protected function _getFrmName() {
		return 'frmNodeType';
	}
	
	/*
	* Get current node type
	* 
	* @return array node type
	*/
	protected function _getNodeType() {
		return $this->_arrNodeType;
	}
	
	/*
	* Set current node type
	* 
	* @param array node type data
	*/
	protected function _setNodeType($arrNodeType) {
		$this->_arrNodeType = $arrNodeType;
	}
	
	public function execute($arrArgs) {
		
		/*
		* Get node type for edit 
		*/
		$intNodeTypeId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		/*
		* set node type for edit 
		*/
		if($intNodeTypeId !== null) {
			$this->_setNodeType($this->_objDAONode->fetchNodeTypeById($intNodeTypeId));
		}
		
		/*
		* Check permissions 
		* Can person edit or add node type?
		*/
		$perm = $this->_objMCP->getPermission(($intNodeTypeId === null?MCP::ADD:MCP::EDIT),'NodeType',$intNodeTypeId);
		if(!$perm['allow']) {
			throw new MCPPermissionException($perm);
		}
		
		/*
		* set form values, validate and save valid posted 
		*/
		$this->_frmHandle();
		
		/*
		* Load template with data 
		*/
		$this->_arrTemplateData['name'] = $this->_getFrmName();
		$this->_arrTemplateData['action'] = $this->getBasePath();
		$this->_arrTemplateData['method'] = 'POST';
		$this->_arrTemplateData['config'] = $this->_getFrmConfig();
		$this->_arrTemplateData['values'] = $this->_arrFrmValues;
		$this->_arrTemplateData['errors'] = $this->_arrFrmErrors;
		$this->_arrTemplateData['legend'] = 'Content Type';
		
		return 'Type/Type.php';
		
	}
	
	/*
	* Path to modeuls current state 
	*/
	public function getBasePath() {
		$strBasePath = parent::getBasePath();
		
		/*
	 	* Get node tyoe being edited
		*/
		$arrNodeType = $this->_getNodeType();
		
		if($arrNodeType !== null) {
			$strBasePath.= "/{$arrNodeType['node_types_id']}";
		}
		
		return $strBasePath;
	}
	
	/*
	* Validation callback for system name 
	* 
	* @param mix value
	* @param str label
	* @return str error
	*/
	public function validateSystemName($mixValue,$strLabel) {
		
		/*
	 	* get the node type ebeing edited (if one is being edited) 
		*/
		$arrNodeType = $this->_getNodeType();
		
		/*
		* lowercase letters, numbers and underscores only
		*/
		if(!preg_match('/^[a-z0-9_]*?$/',$mixValue)) {
			return "$strLabel may only contain numbers, underscores and lowercase letters";
		}
		
		/*
		* Build filter for unique site, system name and package combination 
		*/
		$strFilter = sprintf(
			"t.deleted = 0 AND t.sites_id = %s AND t.system_name = '%s' AND t.pkg %s %s"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($mixValue)
			,empty($this->_arrFrmValues['pkg'])?"= ''":"= '{$this->_objMCP->escapeString($this->_arrFrmValues['pkg'])}'"
			,$arrNodeType !== null?" AND t.node_types_id <> {$this->_objMCP->escapeString($arrNodeType['node_types_id'])}":''
		);
		
		/*
		* Check that node type doesn't already exist 
		*/
		if(array_pop($this->_objDAONode->fetchNodeTypes('t.node_types_id',$strFilter)) !== null) {
			return "$strLabel $mixValue already exists".(empty($this->_arrFrmValues['pkg'])?'.':" for package {$this->_arrFrmValues['pkg']}.");
		}
		
		return '';
		
	}
	
	/*
	* Validation callback for human name 
	* 
	* @param mix value
	* @param str label
	* @return str error
	*/
	public function validateHumanName($mixValue,$strLabel) {
		
		/*
	 	* get the node type ebeing edited (if one is being edited) 
		*/
		$arrNodeType = $this->_getNodeType();
		
		/*
		* Build filter for unique site, human name and package combination 
		*/
		$strFilter = sprintf(
			"t.deleted = 0 AND t.sites_id = %s AND t.human_name = '%s' AND t.pkg %s %s"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($mixValue)
			,empty($this->_arrFrmValues['pkg'])?"= ''":"= '{$this->_objMCP->escapeString($this->_arrFrmValues['pkg'])}'"
			,$arrNodeType !== null?" AND t.node_types_id <> {$this->_objMCP->escapeString($arrNodeType['node_types_id'])}":''
		);
		
		/*
		* Check that node type doesn't already exist 
		*/
		if(array_pop($this->_objDAONode->fetchNodeTypes('t.node_types_id',$strFilter)) !== null) {
			return "$strLabel $mixValue already exists".(empty($this->_arrFrmValues['pkg'])?'.':" for package {$this->_arrFrmValues['pkg']}.");
		}
		
		return '';
		
	}
	
}
?>