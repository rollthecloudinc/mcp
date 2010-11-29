<?php
/*
* All data access classes should
* extends this class. 
*/
abstract class MCPDAO extends MCPResource {
	
	/*
	* Generic save method used to insert and update individual tables. 
	* 
	* NOTE: Don't be afraid to use your own save method. This is merely here
	* due to the countless methods in the system that use to replicate this code. However
	* , if you need something more specific use your own. This is only here to eliminate
	* repeition and provide a means organization for the core.
	* 
	* Also, note that this method is not publicly accessible. This is because it exposes database
	* level operations. Thus, it should only be used as a means to eliminate repeition from a self 
	* declared save method. Its purpose is not to skip a custom save method and call directly from
	* within module due to required data layer paramaters that the data layer should only be responsible
	* for managing.
	* 
	* @param array data to save
	* @param str full table name to save data to
	* @param str primary key field name (presence used to trigger update)
	* @param array list of fields that are stored as strings IE. need '' placed around them.
	* @param str created on timestamp field. Use null or '' for table without this field.
	* @param array fields that are serialized
	* @return mix insert: insert id | update: affected rows
	*/
	protected function _save($arrData,$strTable,$strPrimaryKey,$arrStrings,$strCreated='created_on_timestamp',$arrSerialized=null) {
		
		$boolUpdate = false;
		$arrUpdate = array();
		
		$arrValues = array();
		$arrColumns = array();
		
		$arrSerialized = empty($arrSerialized)?array():$arrSerialized;
		
		foreach($arrData as $strField=>$mixValue) {
			
			if(strcmp($strPrimaryKey,$strField) == 0) {
				$boolUpdate = true;
			} else {
				$arrUpdate[] = "$strField = VALUES($strField)";
			}
			
			if(!is_array($mixValue) && strlen($mixValue) == 0) {
				$mixValue = 'NULL';
			} else if(in_array($strField,$arrStrings)) {
				$mixValue = "'".$this->_objMCP->escapeString($mixValue)."'";
			} else if(in_array($strField,$arrSerialized)) {
				$mixValue = "'".base64_encode(serialize($mixValue))."'";
			} else {
				$mixValue = $this->_objMCP->escapeString($mixValue);
			}
			
			$arrColumns[] = $strField;  
			$arrValues[] = $mixValue;
			
		}
		
		if(!empty($strCreated) && !array_key_exists($strCreated,$arrColumns)) {
			$arrColumns[] = $strCreated;
			$arrValues[] = 'NOW()';
		}
		
		$strSQL = sprintf(
			'INSERT INTO %s (%s) VALUES (%s) %s'
			,$strTable
			,implode(',',$arrColumns)
			,implode(',',$arrValues)
			,$boolUpdate === true?' ON DUPLICATE KEY UPDATE '.implode(',',$arrUpdate):''
		);
		
		return $this->_objMCP->query($strSQL);
		
	}
	
	/*
	* Check user edit permissions for item
	* - Allow child classes to define implementation
	* 
	* @param array item data
	* [@param] item entity type such as; Node, Navigation Item, Navigation, ect.
	* 
	* @return bool true/false
	*/
	public function canEdit($arrItem,$strEntity=null) {
		return true;
	}
	
	/*
	* Check user delete permissions for item
	* - Allow child classes to define implementation
	* 
	* @param array item data
	* [@param] item entity type such as; Node, Navigation Item, Navigation, ect.
	* 
	* @return bool true/false
	*/
	public function canDelete($arrItem,$strEntity=null) {
		return true;
	}
	
	/*
	* Check user create permissions for item
	* - Allow child classes to define implementation
	* 
	* @param array item data
	* [@param] item entity type such as; Node, Navigation Item, Navigation, ect.
	* 
	* @return bool true/false
	*/
	public function canAdd($arrItem,$strEntity=null) {
		return true;
	}
	
}
?>