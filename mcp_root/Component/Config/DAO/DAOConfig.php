<?php
/*
* Config data access layer 
*/
$this->import('App.Core.DAO');
class MCPDAOConfig extends MCPDAO {
	
	private
	
	/*
	* Global XML config 
	*/
	$_arrConfig
	
	/*
	* Dynamic field values 
	*/
	,$_arrDynamic
	
	/*
	* Cached complete config 
	*/
	,$arrCachedConfig;
	
	public function __construct(MCP $objMCP) {
		parent::__construct($objMCP);
		$this->_init();
	}
	
	private function _init() {
		
		// Read in global configuration setings
		$objXML = simplexml_load_file(ROOT.DS.'App'.DS.'Config'.DS.'Config.xml');
		
		// Parse config XML object into associative array
		foreach($objXML as $strName=>$strValue) {
			$this->_arrConfig[$strName] = (string) $strValue->value;
		}

		/*
		* Assign dynamic config values 
		* 
		* Dynamic config values are for internal use only. As far as the application
		* is concerned these values are part of the concrete config.
		*/
		$this->_arrDynamic = $this->_objMCP->addFields(array(),0,'MCP_CONFIG');
		
		/*
		* Create a cached version of the config 
		*/
		$this->_arrCachedConfig = $this->fetchEntireConfig();
		
	}
	
	/*
	* Pull down a fresh copy of the dynamic config values
	*/
	private function _reloadDynamicConfigValues() {
		$this->_arrDynamic = $this->_objMCP->addFields(array(),0,'MCP_CONFIG');
	}
	
	/*
	* Determine whether a config value is dynamic
	* 
	* @param str name
	* @return bool
	*/
	private function _isDynamicConfigValue($strName) {
		return isset($this->_arrDynamic[$strName]);
	}
	
	/*
	* Get a dynamic config value 
	* 
	* @param str name
	* @return mix config value
	*/
	private function _getDynamicConfigValue($strName) {
		return $this->_arrDynamic[$strName];
	}
	
	/*
	* Determines whether a config name is part of config - concrete or dynamic
	* 
	* @param str name
	* @return bool
	*/
	private function _isConfigValue($strName) {
		return !isset($this->_arrConfig[$strName]) && !$this->_isDynamicConfigValue($strName)?false:true;
	}
	
	/*
	* Save dynamic field values
	* 
	* @param array key/value pairs
	*/
	private function _saveDynamicFieldValues($arrValues) {
		
		// save the values
		$return = $this->_objMCP->saveFieldValues($arrValues,0,'MCP_CONFIG');
		
		// reload the dynamic values
		$this->_reloadDynamicConfigValues();
		
		return $return;
	}
	
	/*
	* Get value for configuration setting
	* 
	* @param str config setting name
	* @return str config value
	*/
	public function fetchConfigValueByName($strName) {
		
		/*
		* Use the request cached config value when it exists
		* to eliminate a most likely unnecessary query. 
		*/
		if( isset($this->_arrCachedConfig[$strName]) ) {
			return $this->_arrCachedConfig[$strName];
		}
		
		/*
		* Check for valid configuration setting name
		*/
		if(!$this->_isConfigValue($strName)) return null;
		
		/*
		* Check dynamic config first 
		*/
		if( $this->_isDynamicConfigValue($strName) ) {
			return $this->_getDynamicConfigValue($strName);
		}
		
		/*
		* Fetch site specific config value but default to global setting
		* when site specific setting doesn't exist. 
		*/
		/* $strSQL = sprintf(
			"SELECT config_value FROM MCP_CONFIG WHERE sites_id = %s AND config_name = '%s'"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($strName)
		);*/
		
		$arrRow = array_pop($this->_objMCP->query(
			'SELECT config_value FROM MCP_CONFIG WHERE sites_id = :sites_id AND config_name = :config_name'
			,array(
				':sites_id'=>(int) $this->_objMCP->getSitesId()
				,':config_name'=>(string) $strName
			)
		)); 
		
		
		return $arrRow === null?$this->_arrConfig[$strName]:$arrRow['config_value'];	
	}
	
	/*
	* Set config setting value
	* 
	* @param str config setting value name
	* @param str config setting value
	* @return bool success/failure
	*/
	public function setConfigValueByName($strName,$strValue) {	
		/*
		* Check for valid configuration setting name
		*/
		if(!$this->_isConfigValue($strName)) return false;
		
		/*
		* Check dynamic config first 
		*/
		if( $this->_isDynamicConfigValue($strName) ) {
			$this->_saveDynamicFieldValues(array($strName=>$strValue));
			return true;
		}
		
		/*$strSQL = sprintf(
			"INSERT IGNORE INTO MCP_CONFIG (sites_id,config_name,config_value) VALUES (%s,'%s','%s') ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($strName)
			,$this->_objMCP->escapeString($strValue)
		);*/
		
		$this->_objMCP->query(
			'INSERT IGNORE INTO MCP_CONFIG (sites_id,config_name,config_value) VALUES (:sites_id,:config_name,:config_value) ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)'
			,array(
				':sites_id'=>(int) $this->_objMCP->getSitesId()
				,':config_name'=>(string) $strName
				,':config_value'=>(string) $strValue
			)
		);
		
		return true;
	}
	
	/*
	*  Save full config
	*  
	*  @param array config (key value pair)
	*/
	public function saveMultiConfig($arrConfig) {
		
		if(empty($arrConfig)) return;
		
		// dynamic fields to save
		$fields = array();
		
		// insert statements
		$arrInsert = array();
		$arrBind = array();
		
		$intCounter = 0; // used to build unique placehoolders for bind variables
		foreach($arrConfig as $strName=>$strValue) {
			
			// prevent non-config based data from being placed in db on accident
			if(!$this->_isConfigValue($strName)) {
				continue;
			}
			
			// See if its a dynamic field
			if( $this->_isDynamicConfigValue($strName) ) {
				$fields[$strName] = $strValue;
				continue;
			}
			
			// build separate insert statements
			/*$arrInsert[] = sprintf(
				"(%s,'%s','%s')"
				,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
				,$this->_objMCP->escapeString($strName)
				,$this->_objMCP->escapeString($strValue)
			);*/
			
			// Build out bind insert string
			$arrInsert[] = "(:sites_id_{$intCounter},:config_name_{$intCounter},:config_value_{$intCounter})";
			
			// add values to variable bind array
			$arrBind[":sites_id_{$intCounter}"] = (int) $this->_objMCP->getSitesId();
			$arrBind[":config_name_{$intCounter}"] = (string) $strName;
			$arrBind[":config_value_{$intCounter}"] = (string) $strValue;
			
			// increment unique bind variable counter
			$intCounter++;
			
		}
		
		// create SQL w/ duplicate key update
		$strSQL = sprintf(
			'INSERT IGNORE INTO MCP_CONFIG (sites_id,config_name,config_value) VALUES %s ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)'
			,implode(',',$arrInsert)
		);
		
		// save dynamic field values
		if(!empty($fields)) {
			$this->_saveDynamicFieldValues($fields);
		}
		
		// run query
		return $this->_objMCP->query($strSQL,$arrBind);
		
	}
	
	/*
	* Get all config values for current site
	* 
	* @return array config
	*/
	public function fetchEntireConfig() {
		
		/*
		* Duplicate base config 
		*/
		$arrConfig = $this->_arrConfig;
		
		/*
		* Select all config values that have been overrided 
		*/
		/*$arrRows = $this->_objMCP->query(sprintf(
			'SELECT config_name,config_value FROM MCP_CONFIG WHERE sites_id = %s'
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
		));*/
		
		$arrRows = $this->_objMCP->query(
			'SELECT config_name,config_value FROM MCP_CONFIG WHERE sites_id = :sites_id'
			,array(
				':sites_id'=>(int) $this->_objMCP->getSitesId()
			)
		);
		
		/*
		* Override base values 
		*/
		foreach($arrRows as $arrRow) {
			$arrConfig[$arrRow['config_name']] = $arrRow['config_value'];
		}
		
		/*
		* Add dynamic config values 
		*/
		foreach($this->_arrDynamic as $name=>$value) {
			$arrConfig[$name] = $value;
		}
		
		return $arrConfig;
		
	}
	
	/*
	* Get sites global configuration settings form definition
	* 
	* NOTE: This method is only needed to build the form for 
	* filling in site level configuration values.
	* 
	* @return array schema
	*/
	public function fetchConfigSchema() {
		
		// Read in global configuration setings
		$objXML = simplexml_load_file(ROOT.DS.'App'.DS.'Config'.DS.'Config.xml');
		
		$arrSchema = array();
		
		// parse xml for entire schema
		foreach($objXML as $strName=>$objName) {
			foreach($objName->children() as $objField) {
				
				/*
				* Atomic and scalar value handling 
				*/
				if(strcmp('values',$objField->getName()) != 0) {
					$arrSchema[$strName][$objField->getName()] = (string) $objField;
				} else {
					foreach($objField->children() as $objValue) {
						$arrSchema[$strName][$objField->getName()][] = array(
							'value'=>(string) $objValue->value
							,'label'=>(string) $objValue->label
						);
					}
				}
			}
		}
		
		// Add dynamic config value definitions
		$fields = $this->_objMCP->getFrmConfig('','frm',true,array('entity_type'=>'MCP_CONFIG'));
		
		foreach($fields as $name=>$field) {
			$arrSchema[$name] = $field;
		}
		
		return $arrSchema;
		
	}
	
}
?>