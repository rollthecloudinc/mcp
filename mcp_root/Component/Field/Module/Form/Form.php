<?php 
/*
* Add/Edit Dynamic field to application entity 
*/
class MCPFieldForm extends MCPModule {
	
	protected
	
	/*
	* Field data access layer 
	*/
	$objDAOField
	
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
	* Current field 
	*/
	,$_arrField
	
	/*
	* Preselect entity when creating new field (resolved via URL argument)
	*/
	,$_strEntityPreselect;	
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	protected function _init() {
		
		// Get field DAO
		$this->_objDAOField = $this->_objMCP->getInstance('Component.Field.DAO.DAOField',array($this->_objMCP));
	
		// Get validator
		$this->_objValidator = $this->_objMCP->getInstance('App.Lib.Validation.Validator',array());
		
		// Add custom validation routines
		$this->_objValidator->addRule('field_name',array($this,'validateFieldName'));
		
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
		} else if($this->_getField() !== null) {
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
		* Get the current field
		*/
		$arrField = $this->_getField();
		
		/*
		* Set form fields as values for current vocabulary 
		*/
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				
				case 'cfg_dao_args':
					$this->_arrFrmValues[$strField] = isset($arrField[$strField])?unserialize(base64_decode($arrField[$strField])):'';
					break;

				// logic necessary to mixin media with primitive db values interface wise
				case 'db_value':
					
					if( !empty($arrField['db_ref_context']) ) {
						
						$this->_arrFrmValues[$strField] = !empty($arrField['db_ref_context_id'])?"{$arrField['db_ref_context']}:{$arrField['db_ref_context_id']}":$arrField['db_ref_context'];
						break;
						
					}
					
					$this->_arrFrmValues[$strField] = $arrField !== null && $arrField['cfg_media'] !== null?$arrField['cfg_media']:$arrField['db_value'];
					break;
				
				// build expected entity value mask
				case 'entity':
					$this->_arrFrmValues[$strField] = $arrField['entities_id'] !== null?"{$arrField['entity_type']}-{$arrField['entities_id']}":$arrField['entity_type'];
					break;
				
				default:
					$this->_arrFrmValues[$strField] = isset($arrField[$strField])?$arrField[$strField]:'';
			}
		}
		
	}
	
	/*
	* Set form values as defaults 
	*/
	protected function _setFrmCreate() {
		
		foreach($this->_getFrmFields() as $strField) {
			switch($strField) {
				
				case 'entity':
					$this->_arrFrmValues[$strField] = $this->_strEntityPreselect !== null?$this->_strEntityPreselect:'';
					break;
				
				default:
					$this->_arrFrmValues[$strField] = '';
			}
		}
		
	}
	
	/*
	* Save form data 
	*/
	protected function _frmSave() {
		
		// copy form values
		$arrValues = $this->_arrFrmValues;
		
		// Resolve entity_type and entities_id
		$entity_type = null;
		$entities_id = null;
		
		if(strpos($arrValues['entity'],'-') !== false) {
			list($entity_type,$entities_id) = explode('-',$arrValues['entity'],2);
		} else {
			$entity_type = $arrValues['entity'];
		}
		
		// unset entity
		unset($arrValues['entity']);
		
		// Add the actual entity multi-part field
		$arrValues['entity_type'] = $entity_type;
		
		// Add the entity id if field bound to specific row id of entity
		if($entities_id !== null) {
			$arrValues['entities_id'] = $entities_id;
		}
		
		// ------------------------------------------------------------------------------------
		// Relational resolution
		// ------------------------------------------------------------------------------------
		$arrValues['db_ref_context'] = '';
		$arrValues['db_ref_context_id'] = '';
		/*$arrValues['cfg_dao_pkg'] = '';
		$arrValues['cfg_dao_method'] = '';
		$arrValues['cfg_dao_args'] = '';*/
			
		/*
		* Handle media type and entity reference conversions
		*/
		switch($arrValues['db_value']) {
			
			// start media references -------------------------
				
			case 'image':
				$arrValues['cfg_media'] = 'image';
				$arrValues['db_value'] = 'int';
				$arrValues['db_ref_table'] = 'MCP_MEDIA_IMAGES';
				$arrValues['db_ref_col'] = 'images_id';
				break;
				
			case 'audio':
				$arrValues['cfg_media'] = 'audio';
				$arrValues['db_value'] = 'int';
				$arrValues['db_ref_table'] = 'MCP_MEDIA_AUDIO';
				$arrValues['db_ref_col'] = 'audio_id';
				break;
				
			case 'video':
				$arrValues['cfg_media'] = 'video';
				$arrValues['db_value'] = 'int';
				$arrValues['db_ref_table'] = 'MCP_MEDIA_VIDEO';
				$arrValues['db_ref_col'] = 'video_id';
				break;
				
			case 'file':
				$arrValues['cfg_media'] = 'file';
				$arrValues['db_value'] = 'int';
				$arrValues['db_ref_table'] = 'MCP_MEDIA_FILES';
				$arrValues['db_ref_col'] = 'files_id';
				break;
				
			// end media start entity ---------------------------
				
			case 'vocabulary':
				$arrValues['db_value'] = 'int';
				$arrValues['db_ref_table'] = 'MCP_VOCABULARY';
				$arrValues['db_ref_col'] = 'vocabulary_id';
				$arrValues['db_ref_context'] = 'vocabulary';
				
				// values will be derived using a dao call
				$arrValues['cfg_dao_pkg'] = 'Component.Taxonomy.DAO.DAOTaxonomy';
				$arrValues['cfg_dao_method'] = 'listVocabulary';
				$arrValues['cfg_dao_args'] = array(
					 "v.vocabulary_id value,v.system_name label"
					,"v.deleted = 0 AND v.sites_id = SITES_ID"
					,"v.human_name ASC"
				);
				
				break;
				
			case 'nodetype':
				$arrValues['db_value'] = 'int';
				$arrValues['db_ref_table'] = 'MCP_NODE_TYPES';
				$arrValues['db_ref_col'] = 'node_types_id';
				$arrValues['db_ref_context'] = 'nodetype';
				
				// values will be derived using a dao call
				$arrValues['cfg_dao_pkg'] = 'Component.Node.DAO.DAONode';
				$arrValues['cfg_dao_method'] = 'fetchNodeTypes';
				$arrValues['cfg_dao_args'] = array(
					 "t.node_types_id value,t.system_name label"
					,"t.deleted = 0 AND t.sites_id = SITES_ID"
					,"t.human_name ASC"
				);
				
				break;
				
			default:
				
				// term and node handler in format: node:2 or vocaulary:4 ie. node:{nodetype} or term:{vocabulary}
				if(strpos($arrValues['db_value'],'term:') === 0 || strpos($arrValues['db_value'],'node:') === 0) {
					
					// separate the entity (node,term) from the nodetype or vocabulary id
					list($relation,$relation_id) = explode(':',$arrValues['db_value'],2);
					
					// Set the proper dao, dao method and dao arguments to generate the list
					if( strcmp('node',$relation) === 0 ) {
						
						$arrValues['db_value'] = 'int';
						$arrValues['db_ref_table'] = 'MCP_NODES';
						$arrValues['db_ref_col'] = 'nodes_id';
							
						$arrValues['db_ref_context'] = 'node';	
						$arrValues['db_ref_context_id'] = $relation_id;
						
						// values will be derived using a dao call
						$arrValues['cfg_dao_pkg'] = 'Component.Node.DAO.DAONode';
						$arrValues['cfg_dao_method'] = 'fetchNodes';
						$arrValues['cfg_dao_args'] = array(
							 "n.nodes_id value,n.node_title label"
							,"n.deleted = 0 AND n.sites_id = SITES_ID AND n.node_types_id = {$this->_objMCP->escapeString($relation_id)}"
							,"n.node_title ASC"
						);
						
					} else if( strcmp('term',$relation) === 0 ) {
						
						$arrValues['db_value'] = 'int';
						$arrValues['db_ref_table'] = 'MCP_TERMS';
						$arrValues['db_ref_col'] = 'terms_id';	
						
						$arrValues['db_ref_context'] = 'term';	
						$arrValues['db_ref_context_id'] = $relation_id;
						
						// values will be derived using a dao call
						$arrValues['cfg_dao_pkg'] = 'Component.Taxonomy.DAO.DAOTaxonomy';
						$arrValues['cfg_dao_method'] = 'fetchTerms';
						$arrValues['cfg_dao_args'] = array(
							$relation_id
							,"vocabulary"
							,true
							,array(
								 'select' => "t.terms_id value,t.human_name label"
								,'filter' => "t.deleted = 0"
								,'sort'   => "t.weight ASC"
							)
						);
									
					}
					
				}
				
		}
		
		// ----------------------------------------------------------------------------------------
		// End relational reference resolution
		// ----------------------------------------------------------------------------------------
		
		//echo '<pre>',print_r($arrValues),'</pre>';
		//return;
		
		// Get the current field
		$arrField = $this->_getField();
		
		// When creating a new node add the creators id, otherwise add the pk to trigger an update
		if($arrField !== null) {
			$arrValues['fields_id'] = $arrField['fields_id'];
		} else {
			$arrValues['creators_id'] = $this->_objMCP->getUsersId();
			$arrValues['sites_id'] = $this->_objMCP->getSitesId();
		}
		
		// Save the info
		$this->_objDAOField->saveField($arrValues);
		
	}
	
	/*
	* Get form configuration
	* 
	* @return array configuration
	*/
	protected function _getFrmConfig() {
		return $this->_objMCP->getFrmConfig($this->getPkg());
	}
	
	/*
	* Get form name
	* 
	* @return str form name
	*/
	protected function _getFrmName() {
		return 'frmField';
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
	* Get current field data
	* 
	* @return array current field data
	*/
	protected function _getField() {
		return $this->_arrField;
	}
	
	/*
	* Set the current field
	* 
	* @param array fields data
	*/
	protected function _setField($arrField) {
		$this->_arrField = $arrField;
	}
	
	public function execute($arrArgs) {
		
		/*
		* Extract fields for edit 
		*/
		$intFieldsId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		if($intFieldsId) {
			$this->_setField($this->_objDAOField->fetchFieldById($intFieldsId));
		}
		
		/*
		* Preselect entity 
		*/
		$this->_strEntityPreselect = !empty($arrArgs)?array_shift($arrArgs):null;
		
		/*
		* Check permissions 
		* Can user add/ edit field for entity?
		*/
		/*$field = $this->_getField();
		$perm = $this->_objMCP->getPermission(($field === null?MCP::ADD:MCP::EDIT),'Field', ( $field === null?$this->_strEntityPreselect:$field['fields_id'] ));
		if(!$perm['allow']) {
			throw new MCPPermissionException($perm);
		}*/
		
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
		$this->_arrTemplateData['legend'] = 'Field';
		
		return 'Form/Form.php';
	}
	
	/*
	* get base path to this modules state 
	* 
	* @return str base path
	*/
	public function getBasePath() {
		$strBasePath = parent::getBasePath();
		
		/*
		* Add fields id for edit 
		*/
		$arrField = $this->_getField();
		if($arrField !== null) {
			$strBasePath.= "/{$arrField['fields_id']}";
		}
		
		return $strBasePath;
		
	}
	
	/*
	* Validator callback rule to validate field name
	* 
	* @param mix value
	* @param str form label
	* @return str error feedback string
	*/
	public function validateFieldName($mixValue,$strLabel) {
		
		$arrField = $this->_getField();
		
		// Resolve entity_type and entities_id
		$entity_type = null;
		$entities_id = null;	
		if(strpos($this->_arrFrmValues['entity'],'-') !== false) {
			list($entity_type,$entities_id) = explode('-',$this->_arrFrmValues['entity'],2);
		} else {
			$entity_type = $this->_arrFrmValues['entity'];
		}
		
		/*
		* Check field name conforms to standard convention (lowercase alpha-numeric w/ underscores)
		*/
		if(!preg_match('/^[a-z0-9_]*?$/',$mixValue)) {
			return "$strLabel may only contain numbers, underscores and lower alphabetic characters.";
		}
		
		/*
		* Build filter to see if field with name already exists 
		*/
		$strFilter = sprintf(
			"f.sites_id = %s AND f.cfg_name = '%s' AND f.entity_type = '%s' AND f.entities_id %s %s"
			
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($mixValue)
			,$this->_objMCP->escapeString($entity_type)
			
			// edit edge case
			,$arrField !== null?" AND f.fields_id <> {$this->_objMCP->escapeString($arrField['fields_id'])}":''
			
			,$entities_id === null?'IS NULL':"= '{$this->_objMCP->escapeString($entities_id)}'"
		);
		
		/*
		* Check site, field name, entity type and entity id uniqeness
		*/
		if(array_pop($this->_objDAOField->listFields('f.fields_id',$strFilter)) !== null) {
			return "$strLabel $mixValue already exists".($entities_id === null?'.':" for entity id {$entities_id}.");
		}
		
		return '';
		
	}
	
}
?>