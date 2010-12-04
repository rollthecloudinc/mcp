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
		
		// Images result in foreign key reference - always use db_int for value
		if($this->_arrFrmValues['cfg_image'] == 1) {
			$this->_arrFrmValues['db_value'] = 'int';
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
		
		// Image foreign key reference
		if($arrValues['cfg_image'] == 1) {
			$arrValues['db_ref_table'] = 'MCP_MEDIA_IMAGES';
			$arrValues['db_ref_col'] = 'images_id';
		}
		
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
		/*$perm = $this->_objMCP->getPermission('MCP_FIELD',$intFieldsId,array(
			'entity'=>$this->_strEntityPreselect
		));
		if(!$perm->allowed()) {
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