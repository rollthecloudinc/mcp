<?php 
/*
* Create/edit terms 
*/
class MCPTaxonomyFormTerm extends MCPModule {
	
	protected
	
	/*
	* Term dat access layer 
	*/
	$_objDAOTaxonomy
	
	/*
	* Validation object 
	*/
	,$_objValidator
	
	/*
	* Post data 
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
	* Current term 
	*/
	,$_arrTerm
	
	/*
	* Terms parent id 
	*/
	,$_intParentId
	
	/*
	* Terms parent type [vocabulary,term] 
	*/
	,$_strParentType
	
	/*
	* Proxy for term option menu 
	*/
	,$_arrTermOptions;
	
	public function __construct(MCP $objMCP,MCPModule $objModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objModule,$arrConfig);
		$this->_init();
	}	
	
	protected function _init() {
		// Get Term DAO
		$this->_objDAOTaxonomy = $this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP));
		
		// Get validator
		$this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
		
		// Add custom validation routines
		$this->_objValidator->addRule('term_system_name',array($this,'validateTermSystemName'));
		$this->_objValidator->addRule('term_human_name',array($this,'validateTermHumanName'));
		
		// get post
		$this->_arrFrmPost = $this->_objMCP->getPost($this->_getFrmName());
		
		// reset form values and errors
		$this->_arrFrmValues = array();
		$this->_arrFrmErrors = array();
	}
	
	/*
	* Process form
	*/
	protected function _frmHandle() {
		
		/*
		* Select correct vocabulary when editing term
		*/
		$arrTerm = $this->_getTerm();	
		if($arrTerm !== null) {
			
			$this->_intParentId = $arrTerm['parent_id'];
			$this->_strParentType = $arrTerm['parent_type'];
			
			if(strcmp('term',$this->_strParentType) == 0) {
                            
                          
                                
				$arrVocabulary = $this->_objDAOTaxonomy->fetchTermsVocabulary($this->_intParentId);
                                
                               
				
				// reset the parent to display correct vocabulary terms in drop down
				$this->_strParentType = 'vocabulary';
				$this->_intParentId = $arrVocabulary['vocabulary_id'];
			}
			
		}
                
                
		
		/*
		* Set form values 
		*/
		$this->_setFrmValues();
		
		/*
		* Run form validation 
		*/
		if($this->_arrFrmPost !== null) {
			$this->_arrFrmErrors = $this->_objValidator->validate($this->_getFrmConfig(),$this->_arrFrmValues);
		}
		
		/*
		* Save term data 
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
		} else if($this->_getTerm() !== null) {
			$this->_setFrmEdit();
		} else {
			$this->_setFrmCreate();
		}
		
	}
	
	/*
	* Process submit 
	*/
	protected function _setFrmSaved() {
		
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
	* Process term edit 
	*/
	protected function _setFrmEdit() {
		
		/*
		* get the current term 
		*/
		$arrTerm = $this->_getTerm();
		
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				
				case 'parent_id':
					/*
					* Reformat parent_id to include type 
					*/
					$this->_arrFrmValues[$strField] = "{$arrTerm['parent_type']}-{$arrTerm['parent_id']}";
					break;
				
				default:
					$this->_arrFrmValues[$strField] = isset($arrTerm[$strField])?$arrTerm[$strField]:'';
			}
		}
		
	}
	
	/*
	* Process new term entry 
	*/
	protected function _setFrmCreate() {
		
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				
				case 'parent_id':
					/*
					* Preselect vocabulary or term 
					*/
					$this->_arrFrmValues[$strField] = "{$this->_strParentType}-{$this->_intParentId}";
					break;
				
				default:
					$this->_arrFrmValues[$strField] = '';
				
			}
		}
		
	}
	
	/*
	* Save term data to database 
	*/
	protected function _frmSave() {
		
		/*
		*Copy form values 
		*/
		$arrSave = $this->_arrFrmValues;
		
		/*
		* Get current term 
		*/
		$arrTerm = $this->_getTerm();
		
		if($arrTerm === null) {
			$arrSave['creators_id'] = $this->_objMCP->getUsersId();
		} else {
			$arrSave['terms_id'] = $arrTerm['terms_id'];
		}
		
		/*
                * @TODO 
		* Split parent id into parent id (left) and parent type (right)
		*/
                //if(isset($arrSave['parent_id'])) {
                list($strParentType,$intParentId) = explode('-',$arrSave['parent_id'],2);
                /*} else {
                    $strParentType = 'vocabulary';
                    $intParentId = $arrTerm['vocabulary_id'];
                }*/
                
                if(strcmp('vocabulary',$strParentType) === 0) {
                    $arrSave['parent_id'] = null;
                    $arrSave['vocabulary_id'] = $intParentId;
                } else {
                    $arrVocab = $this->_objDAOTaxonomy->fetchTermsVocabulary($intParentId);
                    $arrSave['parent_id'] = $intParentId;
                    $arrSave['vocabulary_id'] = $arrVocab['vocabulary_id'];
                }
                
                //$arrSave['vocabulary_id'] = $intParentId;
                //$arrSave['parent_id'] = $intParentId;
                
                // $this->_objMCP->debug($strParentType);
		
		//if(strcasecmp($strParentType,'vocabulary') !== 0) {
			
			// Get the terms vocabulary
			//$arrVocab = $this->_objDAOTaxonomy->fetchTermsVocabulary($intParentId);
			
			//$arrSave['vocabulary_id'] = $arrVocab['vocabulary_id'];
			//$arrSave['parent_id'] = $intParentId;
			
		//} else {
                        // $this->_objMCP->debug($arrTerm);
			//$arrSave['vocabulary_id'] = $arrTerm['vocabulary_id'];
			//unset($arrSave['parent_id']);
		//}
                
                $this->_objMCP->debug($arrSave);
                
                //return;
		
		/*
		* Save term
		*/
		try {
			
			$intId = $this->_objDAOTaxonomy->saveTerm($arrSave);
			
			/*
			* Fire update event using this as the target
			*/
			$this->_objMCP->fire($this,'TERM_UPDATE');
		
			/*
			* Add success message 
			*/
			$this->_objMCP->addSystemStatusMessage( $this->_getSaveSuccessMessage() );
                        
                        /*
                        * Refresh data 
                        */
                        if($arrTerm !== null) {
                            $this->_setTerm($this->_objDAOTaxonomy->fetchTermById($arrTerm['terms_id']));
                        } else {
                            $this->_setTerm($this->_objDAOTaxonomy->fetchTermById($intId));
                        }
                        
                        /*
                        * Reload data
                        */
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
	* Message to be shown to user upon sucessful save of term
	* 
	* @return str message
	*/
	protected function _getSaveSuccessMessage() {
		return 'Term '.($this->_getTerm() !== null?'Updated':'Created' ).'!';
	}

	/*
	* Message to be shown to user when error occurs saving of term
	* 
	* @return str message
	*/
	protected function _getSaveErrorMessage() {
		return 'An internal issue has prevented the term from being '.($this->_getTerm() !== null?'updated':'created' );
	}
	
	/*
	* Get form config
	* 
	* @return array form config
	*/
	protected function _getFrmConfig() {
		
		/*
		* get current term 
		*/
		$arrTerm = $this->_getTerm();
		
		$entity_id = null;
		if($arrTerm !== null) {           
                    $entity_id = $arrTerm['vocabulary_id'];
		} else {
			if(strcmp('vocabulary',$this->_strParentType) === 0 && $this->_intParentId !== null) {
				$entity_id = $this->_intParentId;
			} else {
				// get the vocabulary of the parent being assigned
				$vocab = $this->_objDAOTaxonomy->fetchTermsVocabulary($this->_intParentId);
				if($vocab !== null) {
					$entity_id = $vocab['vocabulary_id'];
				}
			}
		}
		
		
		$arrConfig = $this->_objMCP->getFrmConfig($this->getPkg(),'frm',true,array('entity_type'=>'MCP_VOCABULARY','entities_id'=>$entity_id));
		
                
                
                
		/*
		* Proxy in term options due to overhead of recursion used to build hierarchy
		*/
		if($this->_arrTermOptions === null) {
			
			/*
			* Default options 
			*/
			$arrOptions = array(
				'select'=> 't.terms_id,t.system_name label,CONCAT(\'term-\',t.terms_id) value'
				,'children'=>'values'
			);
			
			/*
			* When a term is being edited it needs to be ommited so some
			* moron doesn't attempt to make a term a child of itself. 
			*/
			$arrTerm = $this->_getTerm();
			
			/*
			* Add filter to ommitt items that have been soft deleted 
			*/
			$arrOptions['filter'] = ' t.deleted = 0 ';
			
			/*
			* Add a filter to ommit the term being edited
			*/
			if($arrTerm !== null) {
				$arrOptions['filter'].= " AND t.terms_id <> {$this->_objMCP->escapeString($arrTerm['terms_id'])} ";
			}
			
			$this->_arrTermOptions = array(
				array(
					'label'=>'ROOT'
					,'value'=>"vocabulary-{$this->_intParentId}"
					,'values'=>$this->_objDAOTaxonomy->fetchTerms(
						$entity_id //$this->_intParentId
						,'vocabulary'
						,true
						,$arrOptions
					)
				)		
			);
                                        
		}
                
                // $this->_objMCP->debug($this->_arrTermOptions);
		
		/*
		* Assign term options hierarchy as values 
		*/
		$arrConfig['parent_id']['values'] = $this->_arrTermOptions;
		
		return $arrConfig;
		
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
	* Get form name
	* 
	* @return str form name
	*/
	protected function _getFrmName() {
		return 'frmTerm';
	}
	
	/*
	* Get data for the term being edited 
	*/
	protected function _getTerm() {
		return $this->_arrTerm;
	}
	
	/*
	* Set current term
	* 
	* @param array term data
	*/
	protected function _setTerm($arrTerm) {
		$this->_arrTerm = $arrTerm;
	}
	
	public function execute($arrArgs) {
		
		// term to edit
		$intTermId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;		
		
		// parent of new term either vocabulary or term
		$this->_strParentType = !empty($arrArgs) && in_array($arrArgs[0],array('Vocabulary','Term'))?strtolower(array_shift($arrArgs)):null;
		
		// parent id of new link
		$this->_intParentId = $this->_strParentType !== null && !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		// set curret link or parent
		if($intTermId !== null) {			
			// fetch current term data
			$this->_setTerm($arrTerm = $this->_objDAOTaxonomy->fetchTermById($intTermId));			
		}
		
		/*
		* Check permissions 
		* Can person edit or add term - based on vocabulary?
		* - When adding new term person may be restricted to adding to a selected vocabulary.
		*/
		/*$perm = $this->_objMCP->getPermission('MCP_TERM',$intTermId,array(
		 	'vocabulary'=>strcasecmp($this->_strParentType,'Vocabulary') === 0?'MCP_VOCABULARY':null
		 	,'vocabularies_id'=>$this->_intParentId
		 ));
		if(!$perm->allowed()) {
			throw new MCPPermissionException($perm);
		}*/
		
		/*
		* process form 
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
		$this->_arrTemplateData['legend'] = 'Term';
		
		return 'Term/Term.php';
	}
	
	/*
	* Get base path to modules current state
	* 
	* @return str base path
	*/
	public function getBasePath() {
		$strBasePath = parent::getBasePath();
		
		$arrTerm = $this->_getTerm();
		
		if($arrTerm === null) {
			if($this->_strParentType !== null) {
				$strBasePath.= "/".ucfirst($this->_strParentType);
			}
		
			if($this->_intParentId !== null) {
				$strBasePath.= "/{$this->_intParentId}";
			}
		} else if($arrTerm !== null) {
			$strBasePath.= "/{$arrTerm['terms_id']}";
		}
		
		return $strBasePath;
	}
	
	/*
	* Check system_name, parent_type and parent id uniquess
	* 
	* @param str mix value
	* @param str field label
	* @return  str error
	*/
	public function validateTermSystemName($mixValue,$strLabel) {
		
		$arrTerm = $this->_getTerm();
		
		/*
		* make sure system name follows conventional naming 
		*/
		if(!preg_match('/^[a-z0-9_]*?$/',$mixValue)) {
			return "$strLabel may only contain numbers, underscores and lowercase alphabetic characters.";
		}
		
		/*
		* Build filter to check uniqueness 
		*/
		$strFilter = sprintf(
			"t.deleted = 0 AND ( (t.parent_id IS NOT NULL AND CONCAT('term','-',t.parent_id) = '%s' ) OR (t.parent_id IS NULL AND CONCAT('vocabulary','-',t.vocabulary_id) = '%s' ) ) AND t.system_name = '%s' %s"
			,$this->_objMCP->escapeString($this->_arrFrmValues['parent_id'])
			,$this->_objMCP->escapeString($this->_arrFrmValues['parent_id'])
			,$this->_objMCP->escapeString($mixValue)
			
			// edit edge case
			,$arrTerm !== null?" AND t.terms_id <> {$this->_objMCP->escapeString($arrTerm['terms_id'])}":null
		);
		
		if(array_pop($this->_objDAOTaxonomy->listTerms('t.terms_id',$strFilter)) !== null) {
			return "$strLabel $mixValue already exists.";
		}
		
		return '';
	}
	
	/*
	* Check human_name, parent_type and parent id uniqueness
	* 
	* @param str mix value
	* @param str field label
	* @return str error
	*/
	public function validateTermHumanName($mixValue,$strLabel) {
		
		$arrTerm = $this->_getTerm();
		
		/*
		* Build filter to check uniqueness 
		*/
		$strFilter = sprintf(
			"t.deleted = 0 AND ( (t.parent_id IS NOT NULL AND CONCAT('term','-',t.parent_id) = '%s' ) OR (t.parent_id IS NULL AND CONCAT('vocabulary','-',t.vocabulary_id) = '%s' ) ) AND t.human_name = '%s' %s"
			,$this->_objMCP->escapeString($this->_arrFrmValues['parent_id'])
			,$this->_objMCP->escapeString($this->_arrFrmValues['parent_id'])
			,$this->_objMCP->escapeString($mixValue)
			
			// edit edge case
			,$arrTerm !== null?" AND t.terms_id <> {$this->_objMCP->escapeString($arrTerm['terms_id'])}":null
		);
		
		if(array_pop($this->_objDAOTaxonomy->listTerms('t.terms_id',$strFilter)) !== null) {
			return "$strLabel $mixValue already exists.";
		}
		
		return '';
		
	}
	
}
?>