<?php 
/*
* Create and edit vocabulary 
*/
class MCPTaxonomyFormVocabulary extends MCPModule {
	
	protected
	
	/*
	* Validation object 
	*/
	$_objValidator
	
	/*
	* Taxonomy data acess layer 
	*/
	,$_objDAOTaxonomy
	
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
	* Current vocabulary 
	*/
	,$_arrVocabulary;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	protected function _init() {
		// Get Taxonomy DAO
		$this->_objDAOTaxonomy = $this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP));
	
		// Get validator
		$this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
		
		// Add custom validation routines
		$this->_objValidator->addRule('vocabulary_system_name',array($this,'validateVocabularySystemName'));
		$this->_objValidator->addRule('vocabulary_human_name',array($this,'validateVocabularyHumanName'));
		
		// Set post
		$this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
		
		// Reset form errors and values
		$this->_arrFrmValues = array();
		$this->_arrFrmErrors = array();
	}
	
	/*
	* Process form 
	*/
	protected function _frmHandle() {
		
		/*
		* Set form values 
		*/
		$this->_setFrmValues();
		
		/*
		* Validate 
		*/
		if($this->_arrFrmPost !== null) {
			$this->_arrFrmErrors = $this->_objValidator->validate($this->_getFrmConfig(),$this->_arrFrmValues);
		}
		
		/*
		* Save data to database 
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
		} else if($this->_getVocabulary() !== null) {
			$this->_setFrmEdit();
		} else {
			$this->_setFrmCreate();
		}
	}
	
	/*
	* Set form values as posted 
	*/
	protected function _setFrmSaved() {
		
		/*
		* get form configuration 
		*/
		$arrConfig = $this->_getFrmConfig();
		
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
					$this->_arrFrmValues[$strField] = isset($this->_arrFrmPost[$strField])?$this->_arrFrmPost[$strField]:'';
			}
		}
		
	}
	
	/*
	* Set form values from current vocabulary 
	*/
	protected function _setFrmEdit() {
		
		/*
		* Get the current vocabulary 
		*/
		$arrVocabulary = $this->_getVocabulary();
		
		/*
		* Set form fields as values for current vocabulary 
		*/
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				default:
					$this->_arrFrmValues[$strField] = isset($arrVocabulary[$strField])?$arrVocabulary[$strField]:'';
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
	* Save form data 
	*/
	protected function _frmSave() {
		
		/*
		* Get form values 
		*/
		$arrSave = $this->_arrFrmValues;
		
		/*
		* Get current vocabulary 
		*/
		$arrVocabulary = $this->_getVocabulary();
		
		/*
		* Add id of user and site creating new vocabulary 
		*/
		if($arrVocabulary === null) {
			$arrSave['creators_id'] = $this->_objMCP->getUsersId();
			$arrSave['sites_id'] = $this->_objMCP->getSitesId();
		} else {
			$arrSave['vocabulary_id'] = $arrVocabulary['vocabulary_id'];
		}
		
		/*
		* Save vocabulary to database 
		*/
		$this->_objDAOTaxonomy->saveVocabulary($arrSave);
		
		
	}
	
	/*
	* Get form configuration
	* 
	* @return array configuration
	*/
	protected function _getFrmConfig() {
		return $this->_objMCP->getFrmConfig($this->getPkg(),'frm',true,array('entity_type'=>'MCP_VOCABULARY'));
	}
	
	/*
	* Get form name
	* 
	* @return str form name
	*/
	protected function _getFrmName() {
		return 'frmVocabulary';
	}
	
	/*
	* Get form fields
	* 
	* @return array form fields
	*/
	protected function _getFrmFields() {
		return array_keys($this->_getFrmConfig());
	}
	
	/*
	* Get current vocabularies data
	* 
	* @return array current vocabularies data
	*/
	protected function _getVocabulary() {
		return $this->_arrVocabulary;
	}
	
	/*
	* Set the current vocabulary
	* 
	* @param array vocabularies data
	*/
	protected function _setVocabulary($arrVocabulary) {
		$this->_arrVocabulary = $arrVocabulary;
	}
	
	public function execute($arrArgs) {
		
		/*
		* Extract vocabulary for edit 
		*/
		$intVocabulariesId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		if($intVocabulariesId) {
			$this->_setVocabulary($this->_objDAOTaxonomy->fetchVocabularyById($intVocabulariesId));
		}
		
		/*
		* Check permissions 
		* Can person edit or add vocabulary?
		*/
		$perm = $this->_objMCP->getPermission(($intVocabulariesId === null?MCP::ADD:MCP::EDIT),'Vocabulary',$intVocabulariesId);
		// echo '<pre>',var_dump($perm),'</pre>';
		
		if(!$perm['allow']) {
			throw new MCPPermissionException($perm);
		}
		
		/*
		* Process form 
		*/
		$this->_frmHandle();
		
		/*
		* Load template 
		*/
		$this->_arrTemplateData['name'] = $this->_getFrmName();
		$this->_arrTemplateData['action'] = $this->getBasePath();
		$this->_arrTemplateData['method'] = 'POST';
		$this->_arrTemplateData['config'] = $this->_getFrmConfig();
		$this->_arrTemplateData['values'] = $this->_arrFrmValues;
		$this->_arrTemplateData['errors'] = $this->_arrFrmErrors;
		$this->_arrTemplateData['legend'] = 'Vocabulary';
		
		return 'Vocabulary/Vocabulary.php';
	}
	
	/*
	* get base path to this modules state 
	* 
	* @return str base path
	*/
	public function getBasePath() {
		$strBasePath = parent::getBasePath();
		
		/*
		* Add vocabularies id for edit 
		*/
		$arrVocabulary = $this->_getVocabulary();
		if($arrVocabulary !== null) {
			$strBasePath.= "/{$arrVocabulary['vocabulary_id']}";
		}
		
		return $strBasePath;
		
	}
	
	/*
	* Checks vocabulary system name, pkg and site uniqueness
	* 
	* @param mix value
	* @param str field label
	* @return str error 
	*/
	public function validateVocabularySystemName($mixValue,$strLabel) {
		
		$arrVocabulary = $this->_getVocabulary();
		
		/*
		* Check system name conforms to standard convention 
		*/
		if(!preg_match('/^[a-z0-9_]*?$/',$mixValue)) {
			return "$strLabel may only contain numbers, underscores and lower alphabetic characters.";
		}
		
		/*
		* Build filter to see if vocabulary already exists 
		*/
		$strFilter = sprintf(
			"v.deleted = 0 AND v.sites_id = %s AND v.system_name = '%s' AND v.pkg %s %s"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($mixValue)
			
			// edit edge case
			,$arrVocabulary !== null?" AND v.vocabulary_id <> {$this->_objMCP->escapeString($arrVocabulary['vocabulary_id'])}":''
			,empty($this->_arrFrmValues['pkg'])?"= ''":"= '{$this->_objMCP->escapeString($this->_arrFrmValues['pkg'])}'"
		);
		
		/*
		* Check site, system name and pkg uniqueness 
		*/
		if(array_pop($this->_objDAOTaxonomy->listVocabulary('v.vocabulary_id',$strFilter)) !== null) {
			return "$strLabel $mixValue already exists".(empty($this->_arrFrmValues['pkg'])?'.':" for package {$this->_arrFrmValues['pkg']}.");
		}
		
		return '';
	}
	
	/*
	* Check vocabulary human name, sites_id and pkg uniqueness
	* 
	* @param mix value
	* @param str field label
	* @return str error
	*/
	public function validateVocabularyHumanName($mixValue,$strLabel) {
		
		$arrVocabulary = $this->_getVocabulary();
		
		/*
		* Build filter to see if vocabulary already exists 
		*/
		$strFilter = sprintf(
			"v.deleted = 0 AND v.sites_id = %s AND v.human_name = '%s' AND v.pkg %s"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($mixValue)
			
			// edit edge case
			,$arrVocabulary !== null?" AND v.vocabulary_id <> {$this->_objMCP->escapeString($arrVocabulary['vocabulary_id'])}":''
			,empty($this->_arrFrmValues['pkg'])?"= ''":"= '{$this->_objMCP->escapeString($this->_arrFrmValues['pkg'])}'"
		);
		
		/*
		* Check site, system name and pkg uniqueness 
		*/
		if(array_pop($this->_objDAOTaxonomy->listVocabulary('v.vocabulary_id',$strFilter)) !== null) {
			return "$strLabel $mixValue already exists".(empty($this->_arrFrmValues['pkg'])?'.':" for package {$this->_arrFrmValues['pkg']}.");
		}
		
		return '';
		
	}
	
}
?>