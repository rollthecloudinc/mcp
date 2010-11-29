<?php 
$this->import('App.Core.DAO');
/*
* Site data access layer 
*/
class MCPDAOSite extends MCPDAO {
	
	/*
	* List all sites
	* 
	* @param str select fields
	* @param str where clause
	* @param order by clause
	* @param limit clause
	* @return array users
	*/
	public function listAll($strSelect='s.*',$strFilter=null,$strSort=null,$strLimit=null) {
		
		/*
		* Build SQL 
		*/
		$strSQL = sprintf(
			'SELECT
			      %s %s
			   FROM
			      MCP_SITES s
			      %s
			      %s
			      %s'
			,$strLimit === null?'':'SQL_CALC_FOUND_ROWS'
			,$strSelect
			,$strFilter === null?'':"WHERE $strFilter"
			,$strSort === null?'':"ORDER BY $strSort"
			,$strLimit === null?'':"LIMIT $strLimit"
		);
		
		$arrSites = $this->_objMCP->query($strSQL);
		
		if($strLimit === null) {
			return $arrSites;
		}
		
		return array(
			$arrSites
			,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
		);
		
	}
	
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