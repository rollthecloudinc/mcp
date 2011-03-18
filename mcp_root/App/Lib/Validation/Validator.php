<?php
class Validator {
	
	private
	
	/*
	* Overloaded dynamic validation rules 
	*/
	$_arrRules;
	
	public function __construct() {
		$this->_arrTypes = array();
	}
	
	/*
	* Add dynamic validation rules at runtime
	* 
	* NOTE: default types may not be overrided at this time
	* 
	* @param str type
	* @param array callback [object,method]
	* @return string 
	*/
	public function addRule($strType,$arrCallback) {
		$this->_arrRules[$strType] = $arrCallback;
	}

	public function validate($arrConfigs,$arrData) {
	
		/*
		* Reset errors array
		*/
		$arrErrors = array();
	
		foreach($arrConfigs as $strField=>$arrConfig) {
		
			$strValue = isset($arrData[$strField])?$arrData[$strField]:'';
			$strLabel = isset($arrConfig['label'])?$arrConfig['label']:$strField;
			
			if(is_array($strValue)) continue;
	
			if(strlen($strValue) == 0) {
				if(isset($arrConfig['required']) && !empty($arrConfig['required']) && strcasecmp($arrConfig['required'],'Y') == 0) {
					$arrErrors[$strField] = "$strLabel is required";
				}
				continue;
			}

			if(isset($arrConfig['min']) && !empty($arrConfig['min']) && strlen($strValue) < $arrConfig['min']) {
				$arrErrors[$strField] = "$strLabel must be a minimum of {$arrConfig['min']} characters";
				continue;
			}

			if(isset($arrConfig['max']) && !empty($arrConfig['max']) && strlen($strValue) > $arrConfig['max']) {
				$arrErrors[$strField] = "$strLabel is restricted to {$arrConfig['max']} characters";
				continue;
			}
		
			$strError = '';
			if(isset($arrConfig['type'])) {
				switch($arrConfig['type']) {

					case 'numeric':
						$strError = $this->validateNumeric($strValue,$strLabel);
						break;
		
					case 'aplha-numeric':
						$strError = $this->validateAlphaNumeric($strValue,$strLabel);
						break;
						
					case 'email':
						$strError = $this->validateEmail($strValue,$strLabel);
						break;
						
					case 'phone':
						$strError = $this->validatePhone($strValue,$strLabel);
						break;	

					case 'text':
						$strError = $this->validateText($strValue,$strLabel);
						break;
	
					default:
						if(isset($this->_arrRules[$arrConfig['type']])) {
							$strError = call_user_func_array($this->_arrRules[$arrConfig['type']],array($strValue,$strLabel));
						}
						
				}
			}
		
			if(!empty($strError)) {
				$arrErrors[$strField] = $strError;
				continue;
			}
			
			if(isset($arrConfig['values']) && count($arrConfig['values']) >= 1) {
				
				if(is_array($arrConfig['values'][0])) {
					
					/*
					* used to flatten tree based array of select options 
					*/
					$func = create_function(
						'$arrConfig,$func'
						,' if(!isset($arrConfig[\'values\']) || empty($arrConfig[\'values\'])) {
								return array();
							}
							
							$arrCollect = array();
							
							foreach($arrConfig[\'values\'] as $arrValue) {						
								$arrCollect[] = $arrValue[\'value\'];	
								$arrCollect = array_merge($arrCollect,call_user_func($func,$arrValue,$func));							
							}
							
							return $arrCollect;'
					);
					
					/*
					* Flatten array 
					*/
					$arrConfig['values'] = call_user_func($func,$arrConfig,$func);
					
				}
				
				if(!in_array($strValue,$arrConfig['values'])) {
					$arrErrors[$strField] = "$strLabel is restricted to one of the following values ".implode(',',$arrConfig['values']); 
				}
				
			}
			
		}
		
		return $arrErrors;

	}
	
	/*
	* Validate numerical value
	*
	* @param str value
	* @param label used to identify field
	* @return str error
	*/
	public function validateNumeric($strValue,$strLabel) {
		if(preg_match('/^[0-9]*?$/',$strValue)) {
			return '';
		} else {
			return "$strLabel may only contain numeric characters";
		}
	}
	
	/*
	* Validate alpha numerical value
	*
	* @param str value
	* @param label used to identify field
	* @return str error
	*/
	public function validateAlphaNumeric($strValue,$strLabel) {
	}
	
	/*
	* Validate email format
	*
	* @param str value
	* @param label used to identify field
	* @return str error
	*/
	public function validateEmail($strValue,$strLabel) {
		if(filter_var($strValue,FILTER_VALIDATE_EMAIL) !== false) {
			return '';
		} else {
			return "Supplied $strLabel is not valid";
		}
	}
	
	/*
	* Validate phone format
	*
	* @param str value
	* @param label used to identify field
	* @return str error
	*/
	public function validatePhone($strValue,$strLabel) {
		if(preg_match('/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/',$strValue)) {
			return '';
		} else {
			return "$strLabel must be in the format 111-222-3333";
		}
	}
	
	/*
	* Validate input as plain text 
	* 
	* @param str value
	* @param label used to identify field
	* @return str error
	*/
	public function validateText($strValue,$strLabel) {
		if(strip_tags($strValue) == $strValue) {
			return '';
		} else {
			return "$strLabel may only contain plain text";
		}
	}

}
?>