<?php
$this->import('App.Core.Resource');
$this->import('App.Core.DB');
class MCPMySQL extends MCPResource implements MCPDB {

	private 
	
	/*
	* Connection resource identifier
	*/
	$_objLink;
	
	/*
	* Connects to database
	*
	* @param str host name
	* @param str user name
	* @param str user password
	* @param str database name
	*/
	public function connect($strHost,$strUser,$strPwd,$strDb) {
		$this->_objLink = mysql_connect($strHost,$strUser,$strPwd);
		
		if(!$this->_objLink) {
			$this->_objMCP->triggerError('Unable to establish database connection');
		}
			
		if(!mysql_select_db($strDb,$this->_objLink)) {
			$this->_objMCP->triggerError('Unable to select database');
		}
	}

	/*
	* Disconnects from database
	*/
	public function disconnect() {
		if(!mysql_close($this->_objLink)) {
			$this->_objMCP->triggerError('Unable to close database connection');
		}
	}
	
	/*
	* Queries database returning result set as associative array
	*
	* @param str query
	* @param array bound arguments (necessary for consistent interface for PDO and MySQLi adapters)
	* @return array result set associative array
	*/
	public function query($strSQL,$arrBind=array()) {

		/*
		* Fake/replicate binding 
		*/
		if(!empty($arrBind)) {
			$strSQL = $this->_rewriteQuery($strSQL,$arrBind);
			// echo "<p>$strSQL</p>";
		}
		
		$objResult = mysql_query($strSQL,$this->_objLink);
		
		if( mysql_error($this->_objLink) ) {
			throw new MCPDBException('Query failed');
		}
		
		$arrRows = array();
		if(strpos($strSQL,'SELECT') === 0 || strpos($strSQL,'DESCRIBE') === 0 || strpos($strSQL,'SHOW') === 0) {
			while($arrRow = mysql_fetch_assoc($objResult)) {
				$arrRows[] = $arrRow;
			}
		} else if(strpos($strSQL,'INSERT') === 0) {		
			return $this->lastInsertId();		
		} else if(strpos($strSQL,'UPDATE') === 0) {		
			return $this->affectedRows();			
		}
		
		return $arrRows;		
	}
	
	/*
	* Number of rows affected by the last query
	*
	* @return int number of affected rows
	*/
	public function affectedRows() {
	
		$affectedRows = mysql_affected_rows($this->_objLink);
		
		if($affectedRows < 0) {
			$this->_objMCP->triggerError('Last query failed so number of affected rows could not be determined');
		}
		
		return $affectedRows;
	
	}
	
	/*
	* Insert id of last query
	*
	* @return mix last insert id
	*/
	public function lastInsertId() {
		return mysql_insert_id($this->_objLink);	
	}
	
	/*
	* Escapes string for proper input into query statement
	*
	* @param str string to escape
	* @return escaped string value
	*/
	public function escapeString($strValue) {
		$strEscapedValue = mysql_real_escape_string($strValue,$this->_objLink);
		
		if($strEscapedValue === false) {
			$this->_objMCP->triggerError('String escape failed');
		}
		
		return $strEscapedValue;
		
	}
	
	/*
	* Begin a transaction 
	*/
	public function beginTransaction() {
		
		/*
		* Turn off auto commit 
		*/
		mysql_query('SET autocommit=0;',$this->_objLink);
		
		/*
		* Start the transaction 
		*/
		mysql_query('START TRANSACTION;',$this->_objLink);
		
	}
	
	/*
	* Rollback a transaction 
	*/
	public function rollback() {
		
		/*
		* Rollback the transaction
		*/
		mysql_query('ROLLBACK;',$this->_objLink);
		
		/*
		* Turn on auto commit 
		*/
		mysql_query('SET autocommit=1;',$this->_objLink);
		
	}
	
	/*
	* Commit a transaction 
	*/
	public function commit() {
		
		/*
		* Commit the transaction
		*/
		mysql_query('COMMIT;',$this->_objLink);
		
		/*
		* Turn on auto commit 
		*/
		mysql_query('SET autocommit=1;',$this->_objLink);
		
	}
	
	/*
	* Has database connection been established?
	* @return bool
	*/
	public function isConnected() {
		return $this->_objLink === null?false:true;
	}
	
	/*
	* Internal method used to rewrite supplied queries with
	* question mark or named placeholders. Since, default MySQL
	* adapter doesn't support bound arguments the the behvior
	* must be "faked" to keep a consistent interface across
	* adapters that do actually supply this capability such as; PDO
	* and MySQLi.
	* 
	* IMPORTANT: strings and integers MUST be properly type casts. When a string
	* '0' is supplied that value is treated as a string, not an integer. Similarly 
	* the value 0 that is meant to be a string will be treated as a integer if not
	* a true string.
	* 
	* @param str SQL w/ placeholders 
	* @param array bindings
	* @return str rewritten SQL
	*/
	protected function _rewriteQuery($strSQL,$arrBind) {
		
		/*
		* Determines whether matching will be name or ? placeholder based 
		*/
		$named = 'false';
		
		/*
		* Pttern to match against; defaults to ? placeholder 
		*/
		$pattern = '\?';
		
		/*
		* Name based placeholder named and pattern change
		*/
		if(strpos($strSQL,'?') === false) {
			
			/*
			* This is a bit of an issue when using binding for a multiple insert.
			* The common convention is name_1. So name_13 will match name_1
			* when name_1 is prior to name_13. To get around that the array is 
			* reversed do that name_13 comes before name_1. This seems to prevent
			* the issue when using named placeholders.
			*/
			$pattern = '('.implode('|', array_reverse(array_keys($arrBind))).')';
			
			$named = 'true';
		}
		
		/*
		* begin building replacement function 
		*/
		$func = 'static $i=0;$values = array();$named = '.$named.';';

		foreach($arrBind as $mixIndex=>$mixValue) {

			/*
			* NULL, integer and string mutations 
			*/
			if($mixValue === null) {
				$mixValue = '\'NULL\'';
			} else if(is_int($mixValue)) {
				$mixValue = $this->escapeString($mixValue);
			} else {
				$mixValue = "\"'".$this->escapeString($mixValue)."'\"";
			}
	
			/*
			* Push onto replacement function values array 
			*/
			$func.= '$values[\''.$mixIndex.'\'] = '.$mixValue.';';

		};

		$func.= 'return $named?$values[$matches[0]]:$values[$i++];';

		/*
		* End building replacement function; rebuild SQL. 
		*/
		return preg_replace_callback("/$pattern/",create_function('$matches',$func),$strSQL);
		
	}

}
?>