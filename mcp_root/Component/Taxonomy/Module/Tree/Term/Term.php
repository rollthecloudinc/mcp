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
	,$_strRedirect
	
	/*
	* Term id to perform action on 
	*/
	,$_intActionsId;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
		
	}
	
	/*
	* Handle form submit 
	*/
	private function _handleFrm() {
		
		/*
		* Get posted form data 
		*/
		$arrPost = $this->_objMCP->getPost('frmTermList');
		
		/*
		* Route action 
		*/
		if($arrPost && isset($arrPost['action']) && !empty($arrPost['action'])) {
			
			/*
			* Get action 
			*/
			$strAction = array_pop(array_keys($arrPost['action']));
			
			/*
			* Get terms id 
			*/
			$this->_intActionsId = array_pop(array_keys(array_pop($arrPost['action'])));
			
			/*
			* Fire event 
			*/
			$this->_objMCP->fire($this,"TERM_".strtoupper($strAction));
		}
		
	}
	
	protected function _init() {
		// Get taxonomy data access layer
		$this->_objDAOTaxonomy = $this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP));
	
		// set-up delete event handler
		$id =& $this->_intActionsId;
		$dao = $this->_objDAOTaxonomy;
		
		$this->_objMCP->subscribe($this,'TERM_DELETE',function() use(&$id,$dao)  {
			// delete the term
			$dao->deleteTerm($id);
		});
		
		$this->_objMCP->subscribe($this,'TERM_REMOVE',function() use(&$id,$dao)  {
			// remove the term
			$dao->removeTerm($id);
		});
	
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
				"v.system_name = '%s' AND v.sites_id = %s AND v.pkg %s AND v.deleted = 0"
				,$this->_objMCP->escapeString($system_name)
				,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
				,$pkg === null?" = ''":"= '{$this->_objMCP->escapeString($pkg)}'"
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
		$this->_strRedirect = !empty($arrArgs) && in_array($arrArgs[0],array('Edit','Add'))?array_shift($arrArgs):null;
		
		/*
		* Handle form submit  
		*/
		$this->_handleFrm();
		
		/*
		* Map vocabulary name to vocabulary id
		*/
		$arrVocabulary = $this->_getVocabulary($this->_strVocabulary);
		
		/*
		* Assign vocabylary data 
		*/
		$this->_arrTemplateData['vocabulary'] = $arrVocabulary;
		
		/*
		* Only do this when viewing terms tree 
		*/
		if( !in_array($this->_strRedirect,array('Edit','Add')) ) {
		
			/*
			* Assign terms in vocabulary 
			*/
			$this->_arrTemplateData['terms'] = $this->_objDAOTaxonomy->fetchTerms(($arrVocabulary === null?0:$arrVocabulary['vocabulary_id']),'Vocabulary',true,array(
				// hide deleted items
				'filter'=>'t.deleted = 0'
			));
			
			/*
			* Mixin term permissions -------------------------------------------------
			*/
			$mcp = $this->_objMCP;
			
			$func = function($term,$func) use ($mcp) {
			
				if(!isset($term['terms']) || empty($term['terms'])) return array();
				
				foreach($term['terms'] as &$item) {			
					$item['terms'] = $func($item,$func);			
				}
				
				$ids = array();
				foreach($term['terms'] as $item) {
					$ids[] = $item['terms_id'];
				}
			
				if(!empty($ids)) {
					$deletePerms = $mcp->getPermission(MCP::DELETE,'Term',$ids);
					$addPerms = $mcp->getPermission(MCP::ADD,'Term',$ids);
				}
			
				foreach($term['terms'] as &$item) {
					$item['allow_delete'] = $deletePerms[$item['terms_id']]['allow'];
				
					// @TODO: resolve this properly
					$item['allow_add'] = true; // $addPerms[$term['terms_id']]['allow'];
				}
				
				return $term['terms'];
			
			};
			
			$this->_arrTemplateData['terms'] = $func(array('terms'=>$this->_arrTemplateData['terms']),$func);
		
		}
		
		/* ----------------------------------------------------------------------- */
		
		/*
		* Mutation for each term 
		*/
		$this->_arrTemplateData['mutation'] = array($this,'displayTermAsLink');
		
		/*
		* Back link for nested redirects 
		*/
		$this->_arrTemplateData['back_link'] = $this->getBasePath(false);
		
		/*
		* Form action
		*/
		$this->_arrTemplateData['frm_action'] = $this->getBasePath();
		
		/*
		* Form name 
		*/
		$this->_arrTemplateData['frm_name'] = 'frmTermList';
		
		/*
		* Form method 
		*/
		$this->_arrTemplateData['frm_method'] = 'POST';
		
		/*
		* Resolve redirect if requested 
		*/
		$strTpl = 'Term';
		$this->_arrTemplateData['TPL_REDIRECT'] = '';
		
		/*
		* Edit existing term or create new term as a child (quick link) 
		*/
		if(strcmp('Edit',$this->_strRedirect) == 0 || strcmp('Add',$this->_strRedirect) == 0) {
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
		
		// Create new child term quick link
		if( $arrTerm['allow_add'] ) {
			$add = $this->_objMCP->ui('Common.Field.Link',array(
				'url'=>"{$this->getBasePath(false)}/Add/Term/{$arrTerm['terms_id']}"
				,'label'=>'+'
			));
		} else {
			$add = '+';
		}
		
		// Delete term action
		$delete = $this->_objMCP->ui('Common.Form.Submit',array(
			'label'=>'Delete'
			,'name'=>"frmTermList[action][delete][{$arrTerm['terms_id']}]"
			,'disabled'=>!$arrTerm['allow_delete']
		));
		
		// Remove term action
		$remove = $this->_objMCP->ui('Common.Form.Submit',array(
			'label'=>'Remove'
			,'name'=>"frmTermList[action][remove][{$arrTerm['terms_id']}]"
			,'disabled'=>!$arrTerm['allow_delete']
		));
		
		// Place actions into a list
		$actions = $this->_objMCP->ui('Common.Listing.Tree',array(
			'data'=>array(
				array('value'=>$add)
				,array('value'=>$delete)
				,array('value'=>$remove)
			)
		));
		
		// link to edit term
		return $this->_objMCP->ui('Common.Field.Link',array(
			'url'=>"{$this->getBasePath(false)}/Edit/{$arrTerm['terms_id']}"
			,'label'=>$value
		)).$actions;
	}
	
}
?>