<?php 
$this->import('App.Core.Resource');
$this->import('App.Core.DB');
/*
* PDO database adapter 
*/
class MCPPDO extends MCPResource implements MCPDB {
	
	private
	
	/*
	* PDO object 
	*/
	$_objPDO;
	
	public function __construct(MCP $objMCP) {
		parent::__construct($objMCP);
		
		/*
		* Check PDO driver installation 
		*/
		if(!class_exists('PDO')) {
			$this->_objMCP->triggerError('PDO driver not installed.');
		}
	}
	
	/*
	* Connect to database 
	* 
	* @param str host
	* @param str user name
	* @param str password
	* @param str database
	*/
	public function connect($strHost,$strUser,$strPwd,$strDb) {
		
		/*
		* Connect to database 
		*/
		try {
			
			$this->_objPDO = new PDO("mysql:dbname=$strDb;host=$strHost",$strUser,$strPwd);;
			
		} catch(PDOException $e) {
			/*
			* Error connecting 
			*/
			$this->_objMCP->triggerError('Unable to establish database connection');
		}
		
	}
	
	/*
	* Disconnect from database
	*/
	public function disconnect() {
		if($this->_objPDO !== null) {
			unset($this->_objPDO);
		}
	}
	
	/*
	* Query database
	* 
	* @param str SQL
	* @param bound arguments
	* @return mixed (dependent on query type)
	*/
	public function query($strSQL,$arrBind=array()) {
		
		if($this->_objPDO === null) {
			$this->_objMCP->triggerError('Unable to run query, database connection has not been established.');
		}
		
		/*
		* Run query 
		* 
		*/
		if(empty($arrBind)) {
			$objResult = $this->_objPDO->query($strSQL);
		} else {		
			try {
				$objResult = $this->_objPDO->prepare($strSQL);
			} catch(PDOException $e) {
				$this->_objMCP->triggerError('SQL Query uanble to be prepared.');
			}		
		}
		
		if(!$objResult) {
			$this->_objMCP->triggerError('SQL Query invalid');
		}
		
		/*
		* Execute query with bound arguments 
		*/
		if(!empty($arrBind)) {
			if($objResult->execute($arrBind) === false) {
				$this->_objMCP->triggerError('Unable to execute SQL query.');
			}
		}
		
		$arrRows = array();
		if(strpos($strSQL,'SELECT') === 0 || strpos($strSQL,'DESCRIBE') === 0 || strpos($strSQL,'SHOW') === 0) {
			while($arrRow = $objResult->fetch(PDO::FETCH_ASSOC)) {
				$arrRows[] = $arrRow;
			}
			
			/*
			* Close the cursor to allow execution of next query 
			*/
			$objResult->closeCursor();
			
			return $arrRows;
		} else if(strpos($strSQL,'INSERT') === 0) {
			
			/*
			* Close the cursor to allow execution of next query 
			*/
			$objResult->closeCursor();
			
			return $this->lastInsertId();
		} else if(strpos($strSQL,'UPDATE') === 0) {
			
			/*
			* Close the cursor to allow execution of next query 
			*/
			$objResult->closeCursor();
			
			return $this->affectedRows();
		}
		
	}
	
	/*
	* Get number of affected rows
	* 
	* @return int affected rows
	*/
	public function affectedRows() {
		return array_pop(array_pop($this->query('SELECT ROW_COUNT()')));
	}
	
	/*
	* get the last insert id
	* 
	* @return int last insert id
	*/
	public function lastInsertId() {
		
		if($this->_objPDO === null) {
			$this->_objMCP->triggerError('Unable to get last insert ID, no DB connection established.');
		}
		
		return $this->_objPDO->lastInsertId();
	}
	
	/*
	* Escape data
	* 
	* @param mix data
	* @returned escape placeholder
	*/
	public function escapeString($strValue) {
		
		if($this->_objPDO === null) {
			$this->_objMCP->triggerError('Unable to escape string. no DB connection established.');
		}
		
		/*
		* Escape the value 
		*/
		$strEscapedValue = $this->_objPDO->quote($strValue);
		
		/*
		* Check driver support 
		*/
		if($strEscapedValue === false) {
			$this->_objMCP->triggerError('PDO Driver doesn\'t support quote/escapeString method.');
		}
		
		$strEscapedValue = trim($strEscapedValue,"'");
		
		/*
		* Send back returned value 
		*/
		return $strEscapedValue;
		
	}
	
	/*
	* Begin a transaction 
	*/
	public function beginTransaction() {
		
	}
	
	/*
	* Rollback a transaction 
	*/
	public function rollback() {
		
	}
	
	/*
	* Commit a transaction 
	*/
	public function commit() {
		
	}
	
	/*
	* Determine whether connection to database has been established
	* 
	* @return bool yes/no
	*/
	public function isConnected() {
		return $this->_objPDO === null?false:true;
	}
	
}
?>