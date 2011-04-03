<?php 
/*
* Utility data access layer 
*/
$this->import('App.Core.DAO');
class MCPDAOUtil extends MCPDAO {
	
	/*
	* List states
	* 
	* @param str select clause
	* @param str where clause
	* @param str order by clause
	* @param str limit statement
	* @return array states
	*/
	public function listAllStates($strSelect='s.*',$strFilter=null,$strSort=null,$strLimit=null) {
		
		/*
		* Build SQL 
		*/
		$strSQL = sprintf(
			"SELECT
                 %s
                 %s
			  FROM
			     MCP_TERMS s
			 INNER
			  JOIN
			     MCP_TERMS c
			    ON
			     c.parent_type = 'vocabulary'
			   AND
			     s.parent_id = c.terms_id
			   AND
			     c.system_name = 'United States'
			 INNER
			  JOIN
			     MCP_VOCABULARY v
			    ON
			     c.parent_id = v.vocabulary_id
			 WHERE
			     v.pkg = 'Component.Util'
			   AND
			     v.system_name = 'countries'
			   AND
			     v.sites_id = 0
			     %s
	             %s
	             %s"
			,empty($strLimit)?'':'SQL_CALC_FOUND_ROWS'
			,$strSelect
			,empty($strFilter)?'':"AND $strFilter"
			,empty($strSort)?'':"ORDER BY $strSort"
			,empty($strLimit)?'':"LIMIT $strLimit"
		);
		
		$arrStates = $this->_objMCP->query($strSQL);
		
		if(empty($strLimit)) return $arrStates;
		
		/*
		* When results are limited include number of found rows 
		*/
		return array(
			$arrStates
			,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
		);
		
	}
	
	/*
	* Get the term associated with terms for states
	* 
	* @param str select columns
	* @return str states
	*/
	public function fetchStateTerm($strSelect='c.*') {
		return array_pop($this->_objMCP->query(
		  "SELECT
		         $strSelect
			 FROM
			     MCP_TERMS c
			 INNER
			  JOIN
			     MCP_VOCABULARY v
			    ON
			     c.parent_id = v.vocabulary_id
			   AND
			     v.pkg = 'Component.Util'
			   AND
			     v.system_name = 'countries'
			   AND
			     v.sites_id = 0
			 WHERE
			     c.parent_type = 'vocabulary'
			   AND
			     c.system_name = 'United States'
			 LIMIT 1"
		));
	}
	
	/*
	* Fetch the vocabulary that stores country data
	* 
	* @param str select columns
	* @return array vocabulary
	*/
	public function fetchCountryVocabulary($strSelect='*') {
		return array_pop($this->_objMCP->query(
			"SELECT
			      $strSelect
			   FROM
			      MCP_VOCABULARY
			  WHERE
			      pkg = 'Component.Util'
			    AND
			      system_name = 'countries'
			   AND
			      sites_id = 0"
		));
	}
	
	/*
	* List all content types
	* 
	* @param str select columns
	* @param str where clause
	* @param str order by clause
	* @param str limit clause
	* @return array content types
	*/
	public function listContentTypes($strSelect='ct.*',$strWhere=null,$strSort=null,$strLimit=null) {
		
		$strSQL = sprintf(
			'SELECT 
			      %s %s 
			   FROM 
			      MCP_ENUM_CONTENT_TYPES ct
			      %s 
			      %s 
			      %s'
			,$strLimit === null?'':'SQL_CALC_FOUND_ROWS'
			,$strSelect
			,$strWhere === null?'':"WHERE $strWhere"
			,$strSort === null?'':"ORDER BY $strSort"
			,$strLimit === null?'':"LIMIT $strLimit"
		);
		
		$arrRows = $this->_objMCP->query($strSQL);
		
		if($strLimit === null) {
			return $arrRows;
		} else {
			return array(
				$arrRows
				,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
			);
		}
	}
	
}
?>