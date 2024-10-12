<?php 
$this->import('App.Core.DAO');
/*
* Site data access layer 
*/
class MCPDAOSite extends MCPDAO {
	
	/*
	* Fetch site data by sites id 
	* 
	* @param int site id
	* @param str select fields
	* @return array site data
	*/
	public function fetchById($intId,$strSelect='*') {
		$strSQL = sprintf(
			'SELECT %s FROM MCP_SITES WHERE sites_id = %s'
			,$strSelect
			,$this->_objMCP->escapeString($intId)
		);		
		return array_pop($this->_objMCP->query($strSQL));
	}
	
}
?>