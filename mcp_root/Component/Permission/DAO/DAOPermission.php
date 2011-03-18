<?php

/*
* Permission data acess layer 
*/
class MCPDAOPermission extends MCPDAO {
	
	/*
	* List roles
	* 
	* @param str select clause
	* @param str where clause
	* @param str orderby clause
	* @param str limit,offset
	*/
	public function listRoles($strSelect='r.*',$strFilter=null,$strSort=null,$strLimit=null) {
		
		/*
		* Build SQL 
		*/
		$strSQL = sprintf(
			'SELECT
			      %s %s
			   FROM
			      MCP_ROLES r
			      %s
			      %s
			      %s'
			,$strLimit === null?'':'SQL_CALC_FOUND_ROWS'
			,$strSelect
			,$strFilter !== null?"WHERE $strFilter":null
			,$strSort !== null?"WHERE $strSort":null
			,$strLimit !== null?"LIMIT $strLimit":''
		);
		
		/*
		* Query db 
		*/
		$arrRoles = $this->_objMCP->query($strSQL);
		
		/*
		* When without limit just return result set 
		*/
		if($strLimit === null) {
			return $arrRoles;
		}
		
		/*
		* Return bundle of data and number of total rows 
		*/
		return array(
			$arrRoles
			,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
		);
		
	}
	
}

?>