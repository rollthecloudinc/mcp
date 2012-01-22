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
        * get headers for each table column
        *
        * @return array table headers   
        */
        protected function _getHeaders() {
            
            $mod = $this;
            $mcp = $this->_objMCP;
            
            return array(
                array(
                    'label'=>'Term'
                    ,'column'=>'human_name'
                    ,'mutation'=>null
                )
                ,array(
                    'label'=>'Edit'
                    ,'column'=>'terms_id'
                    ,'mutation'=>function($mixValue,$arrTerm) use ($mcp,$mod) {
                        if($arrTerm['allow_edit']) {
                            return $mcp->ui('Common.Field.Link',array(
                                'label'=>'Edit'
                                ,'url'=>"{$mod->getBasePath(false)}/Edit/{$arrTerm['terms_id']}"
                            ));
                        } else {
                            return 'Edit';
                        }
                    }
                )
                ,array(
                    'label'=>'Add Child'
                    ,'column'=>'terms_id'
                    ,'mutation'=>function($mixValue,$arrTerm) use ($mcp,$mod) {
                
                        if( $arrTerm['allow_add'] ) {
                            return $mcp->ui('Common.Field.Link',array(
				'url'=>"{$mod->getBasePath(false)}/Add/Term/{$arrTerm['terms_id']}"
				,'label'=>'+'
                                ,'class'=>'btn info'
                            ));
                        } else {
                            return '<a class="btn info" href="#">+</a>';
                        }
                        
                    }
                )
                ,array(
                    'label'=>'Delete'
                    ,'column'=>'terms_id'
                    ,'mutation'=>function($mixValue,$arrTerm) use ($mcp) {
           		return $mcp->ui('Common.Form.Input',array(
                            'value'=>'Delete'
                            ,'name'=>"frmTermList[action][delete][{$arrTerm['terms_id']}]"
                            ,'disabled'=>!$arrTerm['allow_delete']
                            ,'type'=>'submit'
                            ,'class'=>'btn danger'
                        ));         
                    }
                )
                ,array(
                    'label'=>'Remove'
                    ,'column'=>'terms_id'
                    ,'mutation'=>function($mixValue,$arrTerm) use ($mcp) {
           		return $mcp->ui('Common.Form.Input',array(
                            'value'=>'Remove'
                            ,'name'=>"frmTermList[action][remove][{$arrTerm['terms_id']}]"
                            ,'disabled'=>!$arrTerm['allow_delete']
                            ,'type'=>'submit'
                            ,'class'=>'btn danger'
                        ));         
                    }
                )
            );
            
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
                        
                        // add permission based on vocabulary
                        $arrVocab = $this->_getVocabulary($this->_strVocabulary);
                        $arrAdd = $mcp->getPermission(MCP::ADD,'Term',$arrVocab['vocabulary_id']);
			
                        //$this->debug($this->_arrTemplateData['terms']);
                        
			$func = function($term,$func) use ($mcp,$arrAdd) {
			
				if(!isset($term['terms']) || empty($term['terms'])) return array();
				
				foreach($term['terms'] as &$item) {			
					$item['terms'] = $func($item,$func);			
				}
				
				$ids = array();
				foreach($term['terms'] as &$item) {
					$ids[] = $item['terms_id'];
				}
			
				if(!empty($ids)) {
					$deletePerms = $mcp->getPermission(MCP::DELETE,'Term',$ids);
                                        $editPerms = $mcp->getPermission(MCP::EDIT,'Term',$ids);
				}
			
				foreach($term['terms'] as &$item) {
					$item['allow_delete'] = $deletePerms[$item['terms_id']]['allow'];
                                        $item['allow_edit'] = $editPerms[$item['terms_id']]['allow'];
				
					// Add is based on whether someone is allowed to add terms to vocabulary
					$item['allow_add'] = $arrAdd['allow'];
				}
				
				return $term['terms'];
			
			};
			
			$this->_arrTemplateData['terms'] = $func(array('terms'=>$this->_arrTemplateData['terms']),$func);
		
		}
		
		/* ----------------------------------------------------------------------- */
		
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
                * Table headers 
                */
                $this->_arrTemplateData['headers'] = $this->_getHeaders();
		
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
                
                // $this->debug($this->_arrTemplateData['terms']);
		
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
	
}
?>