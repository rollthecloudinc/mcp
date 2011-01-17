<?php
class MCPTaxonomyListTerm extends MCPModule {
	
	protected 
	
	/*
	* Taxonomy data access layer 
	*/
	$_objDAOTaxonomy
	
	/*
	* ID context: vocabulary or term 
	*/
	,$_strParentType
	
	/*
	* Parent ID of terms to display - parent type determines id context either: vocabulary or term
	*/
	,$_intParentId
	
	/*
	* Redirection keyword 
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
	* Get the table headers 
	*/
	protected function _getHeaders() {
		
		$mcp = $this->_objMCP;
		$dao = $this->_objDAOTaxonomy;
		$mod = $this;
		
		return array(
		
			array(
				'label'=>'Name'
				,'column'=>'human_name'
				,'mutation'=>function($value,$row) use($mcp,$mod) {
					// link to edit term
					return $mcp->ui('Common.Field.Link',array(
						'url'=>"{$mod->getBasePath(false)}/Edit/{$row['terms_id']}"
						,'label'=>$value
					));
				}
			)
			
			,array(
				'label'=>'&nbsp;'
				,'column'=>'terms_id'
				,'mutation'=>function($value,$row) use ($mcp) {
					return $mcp->ui('Common.Field.Link',array(
						'label'=>'+'
						,'url'=>'#'
					));
				}
			)
			
			,array(
				'label'=>'&nbsp;'
				,'column'=>'terms_id'
				,'mutation'=>function($valye,$row) use ($mcp) {
					return $mcp->ui('Common.Form.Submit',array(
						'label'=>'Delete'
						,'name'=>"frmTermList[action][delete][$value]"
						//,'disabled'=>!$row['allow_delete']
					));
				}
			)
			
			,array(
				'label'=>'&nbsp;'
				,'column'=>'terms_id'
				,'mutation'=>function($valye,$row) use ($mcp) {
					return $mcp->ui('Common.Form.Submit',array(
						'label'=>'Remove'
						,'name'=>"frmTermList[action][remove][$value]"
						//,'disabled'=>!$row['allow_delete']
					));
				}
			)
			
		);
		
	}
	
	public function execute($arrArgs) {
		
		// Get the parent type: default to term context
		$this->_strParentType = !empty($arrArgs) && in_array($arrArgs[0],array('Vocabulary','Term'))?array_shift($arrArgs):'Term';
		
		// Get the parent id: 0 is used to avoid conditional below
		$this->_intParentId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		// internal redirect flag
		$this->_strRedirect = !empty($arrArgs) && in_array($arrArgs[0],array('Edit','Add'))?array_shift($arrArgs):null;
		
		// Assign term data to template variable
		$this->_arrTemplateData['terms'] = $this->_objDAOTaxonomy->fetchTerms( ($this->_intParentId?$this->_intParentId:0) ,$this->_strParentType,false,array(
			'filter'=>'t.deleted = 0'
		));
		
		// Get the table headers
		$this->_arrTemplateData['headers'] = $this->_getHeaders();
		
		// form info
		$this->_arrTemplateData['frm_action'] = $this->getBasePath();
		$this->_arrTemplateData['frm_method'] = 'POST';
		$this->_arrTemplateData['frm_name'] = 'frmTermList';
		
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
	* Get path to current modules state 
	*/
	public function getBasePath($redirect=true) {
		
		$strBasePath = parent::getBasePath();
		 
		 if($this->_intParentId !== null) {
		 	$strBasePath.= "/{$this->_strParentType}/{$this->_intParentId}";
		 }
		 
		 if($redirect === true && $this->_strRedirect) {
		 	$strBasePath.= "/{$this->_strRedirect}";
		 }
		 
		 return $strBasePath;
		
	}
	
}
?>