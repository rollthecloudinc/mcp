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
	,$_strNodeTypeSelect
	
	/*
	* Cached config - proxy it in 
	*/
	,$_arrCachedFrmConfig
	
	/*
	* Allows modules that extend this one to bypass permission check 
	*/
	,$_boolBypassPermissionCheck = false;
	
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
		
		// echo '<pre>',print_r($this->_arrFrmPost),'</pre>';
		//echo '<pre>',print_r($_POST),'</pre>';
		
	}
	
	/*
	* Begin processing form data 
	*/
	protected function _process() {
		
		if($this->_arrFrmPost !== null && isset($this->_arrFrmPost['action'])) {
			
			$func = function($value,$func,$action=array()) {
				$item = is_array($value) ? array_pop($value) : $value;
				
				if( is_array($item) ) {
					$action[] = array_pop( array_keys($item) );
				}
				
				if( !is_array($item) ) {
					$action[] = $item;
				}				
				
				return is_array($item) ? $func($item,$func,$action) : $action	;	
			};
			
			$item = $func($this->_arrFrmPost['action'],$func);
			//echo '<pre>',print_r($item),'</pre>';
			
		}
		
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
		* NOTE: supports recaptcha
		*/
		if($this->_arrFrmPost !== null && empty($this->_arrFrmErrors) && ( $this->_useRecaptcha() === false || $this->_objMCP->recaptchaValid() ) ) {
			$this->_frmSave();
		
		} else if( $this->_arrFrmPost !== null && $this->_useRecaptcha() === false) {
			
			// add system message for errors
			$this->_objMCP->addSystemErrorMessage('Some errors were found that prevented form from saving.');
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
                
                // $this->_objMCP->debug($this->_arrFrmPost);
		
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
					if( !empty($arrNodeType['pkg']) ) {
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
		* Proxy config 
		*/
		if( $this->_arrCachedFrmConfig !== null ) {
			return $this->_arrCachedFrmConfig;
		}
		
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
		* 
		* Using $this->getPkg() causes issues when extending this module. The path MUST be hard-coded. This
		* is probably a change that needs to be made to others also.
		*/
		$config = $this->_objMCP->getFrmConfig('Component.Node.Module.Form.Entry'/*$this->getPkg()*/,'frm',true,array('entity_type'=>'MCP_NODE_TYPES','entities_id'=>$entity_id));
		
		/*
		* Before dynamic fields and views were introduced the node tyoe could change with affecting anything. However, with
		* the creation of Fields and Views changing the node type os not possible since other things may be affected. This
		* is something that can be looked into in the futue but for nowthe simplest solution is to not allow changing the node
		* type once it has been created.
		*/
		if( $arrNode !== null) {
			unset($config['node_types_id']);
		} else {
			/*
			* This just makes more sense since user will be creating a node of certain type. There is no need to
			* allow them to select the node type. 
			*/
			$config['node_types_id']['static'] = 'Y';
			$config['node_types_id']['default'] = $this->_strNodeTypeSelect;
		}
		
		/*
		* Assign config to proxy property 
		*/
		$this->_arrCachedFrmConfig = $config;
                
                // $this->_objMCP->debug($config);
		
		return $this->_arrCachedFrmConfig;
	
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
                
                // $this->_objMCP->debug($arrValues);
		
		//echo '<pre>',print_r($_POST),'</pre>';
		
		//echo '<pre>',print_r($this->_arrFrmPost),'</pre>';
		//return;
		
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
			$arrValues['authors_id'] = $this->_getAuthorsId();
		}
		
		/*
		* Break up node typre string into pkg and system name 
		* 
		* Node type may not be changed once created
		*/
		if( $arrNode === null ) {
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
				,empty($nodeTypePkg)?"= ''":"= '{$this->_objMCP->escapeString($nodeTypePkg)}'"
			);
			
			/*
			* Locate primary key of node type 
			*/
			$arrNodeType = array_pop($this->_objDAONode->fetchNodeTypes('t.node_types_id',$strFilter));
			
			/*
			* Add node type 
			*/
			$arrValues['node_types_id'] = $arrNodeType['node_types_id'];
		
		} else {
			$arrValues['node_types_id'] = $arrNode['node_types_id'];		
		}
		
		/*
		* Save node to database 
		*/
		try {
			
			$intId = $this->_objDAONode->saveNode($arrValues);
			
			/*
			* Fire update event using this as the target
			*/
			$this->_objMCP->fire($this,'NODE_UPDATE');
		
			/*
			* Add success message 
			*/
			$this->_objMCP->addSystemStatusMessage( $this->_getSaveSuccessMessage() );
                        
                        /*
                        * Refresh node and compile data 
                        */
                        if($arrNode !== null) {
                            $this->_arrNode = $this->_objDAONode->fetchById($arrNode['nodes_id']);
                        } else {
                            $this->_arrNode = $this->_objDAONode->fetchById($intId);
                        }
                        
                        $this->_arrFrmValues = array();
                        $this->_setFrmEdit();
			
		} catch(MCPDAOException $e) {
			
			$this->_objMCP->addSystemErrorMessage(
				$this->_getSaveErrorMessage()
				,$e->getMessage()
			);
			
			return false;
			
		}
		
		return true;
		
	}
	
	/*
	* Get current node
	* 
	* @return array node
	*/
	protected function _getNode() {
		return $this->_arrNode;
	}
	
	/*
	* Get form legend 
	* 
	* @return str form legend (based on node context)
	*/
	protected function _getLegend() {
		
		// Get node
		$arrNode = $this->_getNode();
		
		if( $arrNode !== null ) {
			
			$arrNodeType = $this->_objDAONode->fetchNodeTypeById($arrNode['node_types_id']);
			return "Edit {$arrNodeType['human_name']}"; //.(!empty($arrNodeType['pkg'])?" ({$arrNodeType['pkg']})":'');
			
		} else {
			
			$name = $this->_strNodeTypeSelect;
			
			// node types belonging to package need have the URL argument reversed to use the fetchNodeTypeByName method
			if(strpos($name,'::') !== false) {
				$tmp = explode('::',$name);
				$name = "{$tmp[1]}::{$tmp[0]}";
			}
			
			$arrNodeType = $this->_objDAONode->fetchNodeTypeByName($name);
			return "Create {$arrNodeType['human_name']}"; //.(!empty($arrNodeType['pkg'])?" ({$arrNodeType['pkg']})":'');
			
		}
		
	}
	
	/*
	* When creating a new node get the authors ID. This is needed because
	* in some cases users may not be logged in when creating nodes. Take for instance
	* email subscriptions where the user will not likely be logged in but needs
	* to be able to create a node. In that case its best to override this and
	* author the node under the site creator. A creator is always required. 
	* 
	* @return int users id
	*/
	protected function _getAuthorsId() {
		return $this->_objMCP->getUsersId();
	}
	
	/*
	* Message to be shown to user upon sucessful save of node
	* 
	* @param 
	*/
	protected function _getSaveSuccessMessage() {
		return 'Content '.($this->_getNode() !== null?'Updated':'Created' ).'!';
	}
	
	protected function _getSaveErrorMessage() {
		return 'An internal issue has prevented the content from being '.($this->_getNode() !== null?'updated':'created' );
	}
	
	/*
	* Get layout to use for form
	* 
	* @return str layout file
	*/
	protected function _getLayout() {
		
		// Get node
		$arrNode = $this->_getNode();
		
		if( $arrNode !== null ) {
			
			$arrNodeType = $this->_objDAONode->fetchNodeTypeById($arrNode['node_types_id']);
			
		} else {
			
			$name = $this->_strNodeTypeSelect;
			
			// node types belonging to package need have the URL argument reversed to use the fetchNodeTypeByName method
			if(strpos($name,'::') !== false) {
				$tmp = explode('::',$name);
				$name = "{$tmp[1]}::{$tmp[0]}";
			}
			
			$arrNodeType = $this->_objDAONode->fetchNodeTypeByName($name);
			
		}
			
		if( $arrNodeType['form_tpl'] !== null ) {
			return ROOT.str_replace('*',$this->_objMCP->getSite(),$arrNodeType['form_tpl']);
		} else {
			return null;
		}
		
	}
	
	/*
	* Determine whether form will use a recapctha 
	*/
	protected function _useRecaptcha() {
		
		/*
		* always use recaptcha for unauthenticated users 
		*/
		if( $this->_objMCP->getUsersId() === null ) {
			return true;
		}
		
		/*
		* Otherwise make it optional 
		*/
		return (bool) $this->getConfigValue('recaptcha');
		
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
                        
                        // $this->_objMCP->debug($this->_arrNode);
                        
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
		if( !$this->_boolBypassPermissionCheck ) {
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
		}
		
		/*
		* Process form 
		*/
		$this->_process();
		
		
		// echo '<pre>',print_r($this->_arrFrmValues),'</pre>';
		
		// echo '<pre>',print_r( $this->_getFrmConfig() ),'</pre>';
		//echo '<pre>',print_r($this->_getNode()),'</pre>';
		//exit;
		
		$this->_arrTemplateData['name'] = $this->_getFrmName();
		$this->_arrTemplateData['action'] = $this->getBasePath();
		$this->_arrTemplateData['method'] = 'POST';
		$this->_arrTemplateData['config'] = $this->_getFrmConfig();
		$this->_arrTemplateData['values'] = $this->_arrFrmValues;
		$this->_arrTemplateData['errors'] = $this->_arrFrmErrors;
		$this->_arrTemplateData['legend'] = $this->_getLegend();
		$this->_arrTemplateData['layout'] = $this->_getLayout();
		
		// recaptcha integration
		$this->_arrTemplateData['recaptcha'] = $this->_useRecaptcha() === true?$this->_objMCP->recaptchaDraw():null;
		
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
			"t.sites_id = %s AND t.system_name = '%s' AND t.pkg = %s"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($nodeTypeName)
			,empty($nodeTypePkg)?"''":"'{$this->_objMCP->escapeString($nodeTypePkg)}'"
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