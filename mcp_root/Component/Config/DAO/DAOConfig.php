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
	$_arrConfig;
	
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
	}
	
	/*
	* Get value for configuration setting
	* 
	* @param str config setting name
	* @return str config value
	*/
	public function fetchConfigValueByName($strName) {
		
		/*
		* Check for valid configuration setting name
		*/
		if(!isset($this->_arrConfig[$strName])) return null;
		
		/*
		* Fetch site specific config value but default to global setting
		* when site specific setting doesn't exist. 
		*/
		$strSQL = sprintf(
			"SELECT config_value FROM MCP_CONFIG WHERE sites_id = %s AND config_name = '%s'"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($strName)
		);
		
		$arrRow = array_pop($this->_objMCP->query($strSQL)); 
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
		if(!isset($this->_arrConfig[$strName])) return false;
		
		$strSQL = sprintf(
			"INSERT IGNORE INTO MCP_CONFIG (sites_id,config_name,config_value) VALUES (%s,'%s','%s') ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($strName)
			,$this->_objMCP->escapeString($strValue)
		);
		$this->_objMCP->query($strSQL);
		return true;
	}
	
	/*
	*  Save full config
	*  
	*  @param array config (key value pair)
	*/
	public function saveMultiConfig($arrConfig) {
		
		if(empty($arrConfig)) return;
		
		// insert statements
		$arrInsert = array();
		
		foreach($arrConfig as $strName=>$strValue) {
			
			// prevent non-config based data from being placed in db on accident
			if(!isset($this->_arrConfig[$strName])) continue;
			
			// build separate insert statements
			$arrInsert[] = sprintf(
				"(%s,'%s','%s')"
				,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
				,$this->_objMCP->escapeString($strName)
				,$this->_objMCP->escapeString($strValue)
			);
		}
		
		// create SQL w/ duplicate key update
		$strSQL = sprintf(
			'INSERT IGNORE INTO MCP_CONFIG (sites_id,config_name,config_value) VALUES %s ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)'
			,implode(',',$arrInsert)
		);
		
		// run query
		return $this->_objMCP->query($strSQL);
		
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
		$arrRows = $this->_objMCP->query(sprintf(
			'SELECT config_name,config_value FROM MCP_CONFIG WHERE sites_id = %s'
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
		));
		
		/*
		* Override base values 
		*/
		foreach($arrRows as $arrRow) {
			$arrConfig[$arrRow['config_name']] = $arrRow['config_value'];
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
		
		return $arrSchema;
		
	}
	
}
?>