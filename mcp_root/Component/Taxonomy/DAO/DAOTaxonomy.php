<?php 
/*
* Taxonomy data access layer 
*/
class MCPDAOTaxonomy extends MCPDAO {
	
	/*
	* List vocabulary
	* 
	* @param str columns
	* @param str where clause
	* @param str sort
	* @param str limit
	* @return array vocabulary
	*/
	public function listVocabulary($strSelect='v.*',$strFilter=null,$strSort=null,$strLimit=null) {
		
		/*
		* Build query 
		*/
		$strSQL = sprintf(
			'SELECT
			      %s
			      %s
			      ,v.vocabulary_id tmp_vocabs_id
			   FROM
			      MCP_VOCABULARY v
			     %s
			     %s
			     %s'
			,$strLimit === null?'':'SQL_CALC_FOUND_ROWS'
			,$strSelect
			,$strFilter === null?'':"WHERE $strFilter"
			,$strSort === null?'':"ORDER BY $strSort"
			,$strLimit === null?'':"LIMIT $strLimit"
		);
		
		/*
		* Query db 
		*/
		$arrVocabulary = $this->_objMCP->query($strSQL);
		
		/*
		* Add in dynamic fields - Internal columns used to add dynamic field data after removed
		*/
		foreach($arrVocabulary as &$arrVocab) {
			$arrVocab = $this->_objMCP->addFields($arrVocab,$arrVocab['tmp_vocabs_id'],'MCP_VOCABULARY');
			unset($arrVocab['tmp_vocabs_id']);
		}
		
		/*
		* When without limit just return result set 
		*/
		if($strLimit === null) {
			return $arrVocabulary;
		}
		
		/*
		* Return bundle of data and number of total rows 
		*/
		return array(
			$arrVocabulary
			,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
		);
		
	}
	
	/*
	* List terms
	* 
	* @param str columns
	* @param str where clause
	* @param str sort
	* @param str limit
	* @return array terms
	*/
	public function listTerms($strSelect='t.*',$strFilter=null,$strSort=null,$strLimit=null) {
		
		/*
		* Build query 
		*/
		$strSQL = sprintf(
			'SELECT
			      %s
			      %s
			      ,t.terms_id tmp_terms_id
			      ,t.parent_type tmp_parent_type
			      ,t.parent_id tmp_parent_id
			   FROM
			      MCP_TERMS t
			     %s
			     %s
			     %s'
			,$strLimit === null?'':'SQL_CALC_FOUND_ROWS'
			,$strSelect
			,$strFilter === null?'':"WHERE $strFilter"
			,$strSort === null?'':"ORDER BY $strSort"
			,$strLimit === null?'':"LIMIT $strLimit"
		);
		
		/*
		* Query db 
		*/
		$arrTerms = $this->_objMCP->query($strSQL);
		
		/*
		* Add in dynamic fields - Internal columns used to add dynamic field data after removed
		*/
		foreach($arrTerms as &$arrTerm) {
			
			if(strcmp('vocabulary',$arrTerm['tmp_parent_type']) === 0) {
				$entity_id = $arrTerm['tmp_parent_id'];
			} else {
				$vocab = $this->fetchTermsVocabulary($arrTerm['tmp_parent_id']);
				$entity_id = $vocab['vocabulary_id'];
			}
			
			$arrTerm = $this->_objMCP->addFields($arrTerm,$arrTerm['tmp_terms_id'],'MCP_VOCABULARY',$entity_id);
			unset($arrTerm['tmp_terms_id'],$arrTerm['tmp_parent_id'],$arrTerm['tmp_parent_type']);
		}
		
		/*
		* When without limit just return result set 
		*/
		if($strLimit === null) {
			return $arrTerms;
		}
		
		/*
		* Return bundle of data and number of total rows 
		*/
		return array(
			$arrTerms
			,array_pop(array_pop($this->_objMCP->query('SELECT FOUND_ROWS()')))
		);
		
	}
	
	/*
	* Fetch vocabulary by id
	* 
	* @param int vocabularies id
	* @param str select columns
	* @return array vocabulary data
	*/
	public function fetchVocabularyById($intVocabularyId,$strSelect='v.*') {
		$arrVocab = array_pop($this->_objMCP->query(sprintf(
			'SELECT
			      %s
			   FROM
			      MCP_VOCABULARY v
			  WHERE
			      v.vocabulary_id = %s'
			,$strSelect
			,$this->_objMCP->escapeString($intVocabularyId)
		)));
		
		// decorate node with dynamic field values
		$arrVocab = $this->_objMCP->addFields($arrVocab,$intVocabularyId,'MCP_VOCABULARY');
		
		return $arrVocab;
		
	}
	
	/*
	* Fetch term by id
	* 
	* @param int terms id
	* @param str select columns
	* @return array term data
	*/
	public function fetchTermById($intTermsId,$strSelect='t.*') {
		$arrTerm = array_pop($this->_objMCP->query(sprintf(
			'SELECT
			      %s
			      ,parent_id tmp_parent_id
			      ,parent_type tmp_parent_type
			   FROM
			      MCP_TERMS t
			  WHERE
			      t.terms_id = %s'
			,$strSelect
			,$this->_objMCP->escapeString($intTermsId)
		)));
		
		// dynamic field vocab resolution
		if(strcmp('vocabulary',$arrTerm['tmp_parent_type']) === 0) {
			$entity_id = $arrTerm['tmp_parent_id'];
		} else {
			$vocab = $this->fetchTermsVocabulary($arrTerm['tmp_parent_id']);
			$entity_id = $vocab['vocabulary_id'];
		}
		
		// decorate node with dynamic field values
		$arrTerm = $this->_objMCP->addFields($arrTerm,$intTermsId,'MCP_VOCABULARY',$entity_id);
		
		return $arrTerm;
		
		
	}
	
	/*
	* Fetch all terms recursive
	* 
	* @param int parent id
	* @param str parent type [vocabulary or term]
	* @param bool recursive
	* @param array option set for selecting specific columns, adding filters or changing default sort order
	* @return array terms
	*/
	public function fetchTerms($intParentId,$strParentType='vocabulary',$boolR=true,$arrOptions=null) {
		
		/*
		* Build SQL 
		*/
		$strSQL = sprintf(
			"SELECT
			      %s
			   FROM
			      MCP_TERMS t
			  WHERE
			      t.parent_type = '%s'
			    AND
			      t.parent_id = %s
			      %s
			      %s"
			,$arrOptions !== null && isset($arrOptions['select'])?$arrOptions['select']:'t.*'
			,$this->_objMCP->escapeString($strParentType)
			,$this->_objMCP->escapeString($intParentId)
			,$arrOptions !== null && isset($arrOptions['filter'])?"AND {$arrOptions['filter']}":''
			,$arrOptions !== null && isset($arrOptions['sort'])?"ORDER BY {$arrOptions['sort']}":''
		);
		
		/*
		* Fetch terms 
		*/
		$arrTerms = $this->_objMCP->query($strSQL);
		
		/*
		* Recure 
		*/
		if($boolR === true) {
			foreach($arrTerms as &$arrTerm) {
				$children = $arrOptions !== null && isset($arrOptions['children'])?$arrOptions['children']:'terms';
				$arrTerm[$children] = $this->fetchTerms($arrTerm['terms_id'],'term',$boolR,$arrOptions);
			}
		}
		
		return $arrTerms;	
		
	}
	
	/*
	* Get the vocabulary a term belongs to
	* 
	* @param int terms id
	* @return array vocabularies data
	*/
	public function fetchTermsVocabulary($intTermsId,$runner=0,$echo=false) {
		
		$strSQL = sprintf(
			'SELECT
			      t.terms_id
			      ,t.parent_id
			      ,t.parent_type
			   FROM
			      MCP_TERMS t
			  WHERE
			      t.terms_id = %s'
			,$this->_objMCP->escapeString($intTermsId)
		);
		
		$arrRow = array_pop($this->_objMCP->query($strSQL));
		
		if(strcmp($arrRow['parent_type'],'vocabulary') != 0) {
			return $this->fetchTermsVocabulary($arrRow['parent_id'],($runner+1),$echo);
		}
		
		return $this->fetchVocabularyById($arrRow['parent_id']);
		
	}
	
	/*
	* Get all terms children (every descendent) 
	* 
	* Good to use to build a filter for the entire term
	* taking into consideration child items at every depth.
	* 
	* @param int terms id
	* @return array child terms
	*/
	public function getAllSubTerms($intTermsId) {
		
		/*
		* Get terms hierarchy
		*/
		$arrTerms = $this->fetchTerms($intTermsId,'term');
		
		/*
		* Recursive function used to flatten hierarchy
		*/
		$func = create_function(
			'$term,$func'
			,'if(!isset($term[\'terms\']) || empty($term[\'terms\'])) {
				return array();
			}
			
			$children = array();
			
			foreach($term[\'terms\'] as $child) {
				$children[] = $child;
				$children = array_merge($children,call_user_func($func,$child,$func));
			}
			
			return $children;'
		);
		
		/*
		* Flatten hierarchy 
		*/
		return call_user_func($func,array('terms'=>$arrTerms),$func);
		
	}
	
	/*
	* Insert or update vocabulary
	*/
	public function saveVocabulary($arrVocabulary) {		
		
		/*
		* Get fields native to table
		*/
		$schema = $this->_objMCP->query('DESCRIBE MCP_VOCABULARY');
		
		$native = array();
		foreach($schema as $column) {
			$native[] = $column['Field'];
		}
		
		/*
		* Siphon dynamic fields
		*/
		$dynamic = array();
		
		foreach(array_keys($arrVocabulary) as $field) {
			if(!in_array($field,$native)) {
				$dynamic[$field] = $arrVocabulary[$field];
				unset($arrVocabulary[$field]);
			}
		}
		
		$intId = $this->_save(
			$arrVocabulary
			,'MCP_VOCABULARY'
			,'vocabulary_id'
			,array('system_name','human_name','pkg','description')
			,'created_on_timestamp'
		);	
		
		/*
		* Save dynamic fields 
		*/
		$this->_objMCP->saveFieldValues($dynamic,(isset($arrVocabulary['vocabulary_id'])?$arrVocabulary['vocabulary_id']:$intId),'MCP_VOCABULARY');
		
		return $intId;
		
	}
	
	/*
	* Insert or update term
	*/
	public function saveTerm($arrTerm) {	
		
		/*
		* Get fields native to table
		*/
		$schema = $this->_objMCP->query('DESCRIBE MCP_TERMS');
		
		$native = array();
		foreach($schema as $column) {
			$native[] = $column['Field'];
		}
		
		/*
		* Siphon dynamic fields
		*/
		$dynamic = array();
		
		foreach(array_keys($arrTerm) as $field) {
			if(!in_array($field,$native)) {
				$dynamic[$field] = $arrTerm[$field];
				unset($arrTerm[$field]);
			}
		}
		
		$intId = $this->_save(
			$arrTerm
			,'MCP_TERMS'
			,'terms_id'
			,array('system_name','human_name','description','parent_type')
			,'created_on_timestamp'
		);	
		
		/*
		* Resolve the vocabulary 
		*/
		$pk = isset($arrTerm['terms_id'])?$arrTerm['terms_id']:$intId;
		$vocab = $this->fetchTermsVocabulary($pk);
		$entity_id = $vocab['vocabulary_id'];

		/*
		* Save dynamic fields 
		*/
		$this->_objMCP->saveFieldValues($dynamic,$pk,'MCP_VOCABULARY',$entity_id);
		
		return $intId;
		
	}
	
}
?>