<?php 
$this->import('App.Core.Resource');
$this->import('App.Core.DB');
/*
* MySQLi database adapter 
*/
class MCPMySQLi extends MCPResource implements MCPDB {
	
	/*
	* Connect to database
	* 
	* @param str host
	* @param str user name
	* @param str database name
	*/
	public function connect($strHost,$strUser,$strPwd,$strDb) {
		
		
	}
	
	/*
	* Disconnect from database 
	*/
	public function disconnect() {
		
	}
	
	/*
	* Run SQL query
	* 
	* @param str SQL
	* @param array bound paramters
	* @return mix (dependent on query type)
 	*/
	public function query($strSQL,$arrBind=array()) {
		
	}
	
	/*
	* Get the number of affected rows of last query
	* 
	* @return int affected rows
	*/
	public function affectedRows() {
		
	}
	
	/*
	* Get the last insert id 
	* 
	* @return int last insert id
	*/
	public function lastInsertId() {
		
	}
	
	/*
	* Escape string for safe embed in query 
	* 
	* @param mix value
	* @return str query safe, escaped string
	*/
	public function escapeString($strValue) {
		
	}
	
	/*
	* Determine whether database connection has been estalished
	* 
	* @return bool yes/no
	*/
	public function isConnected() {
		
	}
	
}
?>