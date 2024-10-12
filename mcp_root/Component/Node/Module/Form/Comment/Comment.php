<?php 
/*
* Create and edit node comments 
*/
class MCPNodeFormComment extends MCPModule {
	
	private
	
	/*
	* Node data access layer 
	*/
	$_objDAONode
	
	/*
	* Form validator 
	*/
	,$_objValidator
	
	/*
	* Current comment 
	*/
	,$_arrComment
	
	/*
	* Node to comment on
	*/
	,$_arrNode
	
	/*
	* Form values 
	*/
	,$_arrFrmValues
	
	/*
	* Form errors 
	*/
	,$_arrFrmErrors;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		// Fetch node DAO
		$this->_objDAONode = $this->_objMCP->getInstance('Component.Node.DAO.DAONode',array($this->_objMCP));
	
		// Get validator
		$this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
		
		// Set-up values and errors arrays
		$this->_arrFrmErrors = array();
		$this->_srrFrmValues = array();
		
		// Fetch post  form post data
		$this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
	}
	
	/*
	* Process form 
	*/
	private function _process() {
		
		// Set form values
		$this->_setFrmValues();
		
		// validate form
		if($this->_arrFrmPost !== null) {
			$this->_arrFrmErrors = $this->_objValidator->validate($this->_getFrmConfig(),$this->_arrFrmValues);
		}
		
		// save form data to database
		if($this->_arrFrmPost !== null && empty($this->_arrFrmErrors)) {
			$this->_frmSave();
		}
		
	}
	
	/*
	* Route posted, edit or create 
	*/
	private function _setFrmValues() {
		if($this->_arrFrmPost !== null) {
			$this->_setFrmSaved();
		} else if($this->_getComment() !== null) {
			$this->_setFrmEdit();
		} else {
			$this->_setFrmCreate();
		}
	}
	
	/*
	* Set form values as posted
	*/
	private function _setFrmSaved() {
		
		/*
		* loop through form fields and set values and perform
		* abstractions as necessary.
		*/		
		foreach($this->_getFrmFields() as $strField) {
			$this->_arrFrmValues[$strField] = $this->_arrFrmPost[$strField];
		}
	}
	
	/*
	* Set form values as current comments
	*/
	private function _setFrmEdit() {
		
		// get current comments data
		$arrComment = $this->_getComment();
		
		/*
		* loop through form fields and set values and perform
		* abstractions as necessary.
		*/	
		foreach($this->_getFrmFields() as $strField) {
			$this->_arrFrmValues[$strField] = $arrComment[$strField];
		}
	}
	
	/*
	* Set form values as empty
	*/
	private function _setFrmCreate() {
		
		/*
		* loop through form fields and set default
		* values as necessary. 
		*/
		foreach($this->_getFrmFields() as $strField) {
			$this->_arrFrmValues[$strField] = '';
		}
		
	}
	
	/*
	* Get name of form
	* 
	* @return str form name
	*/
	private function _getFrmName() {
		return 'frmNodeComment';
	}
	
	/*
	* Get form configuration
	* 
	* @return array configuration
	*/
	private function _getFrmConfig() {
		
		/*
		* Get base form config 
		*/
		$arrConfig = $this->_objMCP->getFrmConfig($this->getPkg());
		
		/*
		* Remove fields reserved for unidentifiable users (those not logged in)
		*/
		if($this->_getComment() !== null || $this->_objMCP->getUsersId()) {
			unset($arrConfig['commenter_first_name'],$arrConfig['commenter_last_name'],$arrConfig['commenter_email']);
		}
		
		return $arrConfig;
		
	}
	
	/* Get form fields to be processed 
	* 
	* @return array form fields
	*/
	private function _getFrmFields() {		
		return array_keys($this->_getFrmConfig());
	}
	
	/*
	* Save form data to database 
	*/
	private function _frmSave() {
		
		// copy values array
		$arrValues = $this->_arrFrmValues;
		
		// get node and comment
		$arrNode = $this->_getNode();
		$arrComment = $this->_getComment();
		
		// add internal data
		if($arrComment === null) {
			
			// set the type of comment to node
			$arrValues['comment_type'] = 'node';
			
			// set the types id
			$arrValues['comment_types_id'] = $arrNode['nodes_id'];
			
			// set the site
			$arrValues['sites_id'] = $this->_objMCP->getSitesId();
			
			// set the commentors id
			if($this->_objMCP->getUsersId()) {
				$arrValues['commenter_id'] = $this->_objMCP->getUsersId();
			}
			
		} else {
			
			// set the primary key to enact duplicate key update
			$arrValues['comments_id'] = $arrComment['comments_id'];
			
		}
		
		$this->_objDAONode->saveNodeComment($arrValues);
		
	}
	
	/*
	* Get the current comment to edit
	* 
	* @return array comment data
	*/
	private function _getComment() {
		return $this->_arrComment;
	}
	
	/*
	* Node to comment on 
	* 
	* @return array node data
	*/
	private function _getNode() {
		return $this->_arrNode;
	}
	
	public function execute($arrArgs) {
		
		// determine whether to edit or create
		$boolNew = !empty($arrArgs) && strcmp('Node',$arrArgs[0]) == 0 && array_shift($arrArgs)?true:false;
		
		// extract id
		$intId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		// fetch edit comment or node to comment on
		if($intId !== null) {
			if($boolNew === true) {
				/*
				* Important: use cached node version - only thing we really care about
				* here is the id anyway. 
				*/
				$this->_arrNode = $this->_objDAONode->fetchById($intId);
			} else {
				$this->_arrComment = $this->_objDAONode->fetchCommentById($intId);
			}
		}	
		
		/*
		* Check permissions 
		* Can person edit or add comment - based on node?
		* - person may be restricted to creating or editing comments based on node
		*/
		/*$perm = $this->_objMCP->getPermission('MCP_COMMENT',($boolNew === true?null:$intId),array(
			 'node'=>'MCP_NODE'
			,'nodes_id'=>($boolNew === true?$intId:null)
		));
		if(!$perm->allowed()) {
			throw new MCPPermissionException($perm);
		}*/
		
		// process form
		$this->_process();
		
		// set template data
		$this->_arrTemplateData['action'] = $this->getBasePath();
		$this->_arrTemplateData['name'] = $this->_getFrmName();
		$this->_arrTemplateData['method'] = 'POST';
		$this->_arrTemplateData['config'] = $this->_getFrmConfig();
		$this->_arrTemplateData['values'] = $this->_arrFrmValues;
		$this->_arrTemplateData['errors'] = $this->_arrFrmErrors;
		$this->_arrTemplateData['legend'] = 'Comment';
		
		return 'Comment/Comment.php';
	}
	
	/*
	* Override base path method
	* 
	* @reurn str base path
	*/
	public function getBasePath() {
		$strBasePath = parent::getBasePath();
		
		// get comment and blog
		$arrComment = $this->_getComment();
		$arrNode = $this->_getNode();
		
		// rebuild base path to include state
		if($arrComment !== null) {
			$strBasePath.= "/{$arrComment['comments_id']}";
		} else if($arrNode !== null) {
			$strBasePath.= "/Node/{$arrNode['nodes_id']}";
		}
		
		return $strBasePath;
	}
	
}
?>