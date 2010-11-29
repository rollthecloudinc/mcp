<?php 
/*
* Show all terms in vocabulary as a tree 
*/
class MCPTaxonomyTreeTerm extends MCPModule {
	
	protected
	
	/*
	* Taxonomy data access layer 
	*/
	$_objDAOTaxonomy
	
	/*
	* Current vocabularies terms being viewed 
	*/
	,$_strVocabulary
	
	/*
	* Internal nested template redirect (Ex. edit clicked term)
	*/
	,$_strRedirect;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
		
	}
	
	protected function _init() {
		// Get taxonomy data access layer
		$this->_objDAOTaxonomy = $this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP));
	}
	
	/*
	* Resolve vocabulary name to vocabulary id (primary key)
	* 
	* @param str vocabulary name
	* @return array vocabulary
	*/
	private function _getVocabulary($strVocabulary) {
		
		if($strVocabulary === null) {
			return null;
		}
		
		$system_name = $strVocabulary;
		$pkg = null;
		
		/*
		* Vocabulary bound to package 
		*/
		if(strpos($strVocabulary,'::') !== false) {
			list($pkg,$system_name) = explode('::',$strVocabulary,2);
		}
		
		/*
		* Accounts for vocabs with and without packages 
		*/
		return array_pop($this->_objDAOTaxonomy->listVocabulary(
			'v.*'
			,sprintf(
				"v.system_name = '%s' AND v.sites_id = %s AND v.pkg %s"
				,$this->_objMCP->escapeString($system_name)
				,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
				,$pkg === null?' IS NULL':"= '{$this->_objMCP->escapeString($pkg)}'"
			)
		));
		
	}
	
	public function execute($arrArgs) {
		
		/*
		* Get the vocabulary name 
		*/
		$this->_strVocabulary = !empty($arrArgs)?array_shift($arrArgs):null;
		
		/*
		* Get redirect path if it exists
		*/
		$this->_strRedirect = !empty($arrArgs) && in_array($arrArgs[0],array('Edit'))?array_shift($arrArgs):null;
		
		/*
		* Map vocabulary name to vocabulary id
		*/
		$arrVocabulary = $this->_getVocabulary($this->_strVocabulary);
		
		/*
		* Assign vocabylary data 
		*/
		$this->_arrTemplateData['vocabulary'] = $arrVocabulary;
		
		/*
		* Assign terms in vocabulary 
		*/
		$this->_arrTemplateData['terms'] = $this->_objDAOTaxonomy->fetchTerms(($arrVocabulary === null?0:$arrVocabulary['vocabulary_id']),'Vocabulary');
		
		/*
		* Mutation for each term 
		*/
		$this->_arrTemplateData['mutation'] = array($this,'displayTermAsLink');
		
		/*
		* Back link for nested redirects 
		*/
		$this->_arrTemplateData['back_link'] = $this->getBasePath(false);
		
		/*
		* Resolve redirect if requested 
		*/
		$strTpl = 'Term';
		$this->_arrTemplateData['TPL_REDIRECT'] = '';
		
		if(strcmp('Edit',$this->_strRedirect) == 0) {
			$this->_arrTemplateData['TPL_REDIRECT'] = $this->_objMCP->executeComponent(
				'Component.Taxonomy.Module.Form.Term'
				,$arrArgs
				,null
				,array($this)
			);
			$strTpl = 'Redirect';
		}
		
		return "Term/$strTpl.php";
	}
	
	/*
	* Get path to modules state
	* 
	* @param bool include redirect flag
	* @return str base path
	*/
	public function getBasePath($redirect=true) {
		
		$strBasePath = parent::getBasePath();
		
		if($this->_strVocabulary !== null) {
			$strBasePath.= "/{$this->_strVocabulary}";
		}
		
		if($redirect === true && $this->_strRedirect !== null) {
			$strBasePath.= "/{$this->_strRedirect}";
		}
		
		return $strBasePath;
		
	}
	
	/*
	* Display each term as a link (tree builder callback) 
	* 
	* @param value that would be printed
	* @param array term data
	* @return str mutation
	*/
	public function displayTermAsLink($value,$arrTerm) {
		return sprintf(
			'<a href="%s/Edit/%u">%s</a>'
			,$this->getBasePath(false)
			,$arrTerm['terms_id']
			,$value
		);
	}
	
}
?>