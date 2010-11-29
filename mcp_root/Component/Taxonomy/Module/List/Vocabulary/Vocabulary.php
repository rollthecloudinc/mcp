<?php
/*
* List vocabularies
*/
class MCPTaxonomyListVocabulary extends MCPModule {
	
	private
	
	/*
	* Taxonomy data access layer 
	*/
	$_objDAOTaxonomy
	
	/*
	* Current page 
	*/
	,$_intPage = 1
	
	/*
	* Internal nested redirect
	*/
	,$_strRedirect = false;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
		
	}
	
	protected function _init() {
		// Get taxonomy data access layer
		$this->_objDAOTaxonomy = $this->_objMCP->getInstance('Component.Taxonomy.DAO.DAOTaxonomy',array($this->_objMCP));
	}
	
	/*
	* Pagination callback
	* 
	* @param int offset
	* @param int limit
	* @return int found rows
	*/
	public function paginate($intOffset,$intLimit) {
		
		// when viewing terms kill this (pagination isn't displayed anyway)
		if($this->_strRedirect !== null) return 0;
		
		// Fetch vocabulary data
		$data = $this->_objDAOTaxonomy->listVocabulary('v.*',$this->_getFilter(),$this->_getSort(),"{$this->_objMCP->escapeString($intOffset)},{$this->_objMCP->escapeString($intLimit)}");
		
		// Assign vocabularies to template var
		$this->_arrTemplateData['vocabularies'] = array_shift($data);
		
		// Determine whether user is allowed to edit each vocabulary and add term to each vocabulary
		$ids = array();
		foreach($this->_arrTemplateData['vocabularies'] as $vocab) {
			$ids[] = $vocab['vocabulary_id'];
		}
		if(!empty($ids)) {
			$perms = $this->_objMCP->getPermission(MCP::EDIT,'Vocabulary',$ids);
			$permsAddTerm = $this->_objMCP->getPermission(MCP::ADD,'Term',$ids);
		}
		foreach($this->_arrTemplateData['vocabularies'] as &$vocab) {
			$vocab['allow_edit'] = $perms[$vocab['vocabulary_id']]['allow'];
			$vocab['allow_add_term'] = $permsAddTerm[$vocab['vocabulary_id']]['allow'];
		}
		
		// return number of found rows
		return array_shift($data);
		
	}
	
	/*
	* Get SQL filter
	* 
	* @return str SQL where clause
	*/
	protected function _getFilter() {
		return "v.sites_id = {$this->_objMCP->escapeString($this->_objMCP->getSitesId())} AND v.deleted IS NULL";
	}
	
	/*
	* Get SQL sort
	* 
	* @return str SQL order by clause
	*/
	protected function _getSort() {
		return null;
	}
	
	/*
	* Get the display headers
	* 
	* @return array table headers
	*/
	protected function _getHeaders() {
		return array(
			array(
				'label'=>'Vocabulary'
				,'column'=>'system_name'
				,'mutation'=>array($this,'displayName')
			)
			,array(
				'label'=>'&nbsp;'
				,'column'=>'vocabulary_id'
				,'mutation'=>array($this,'displayDynamicFieldLink')
			)
			,array(
				'label'=>'Terms'
				,'column'=>'vocabulary_id'
				,'mutation'=>array($this,'displayLinkToTerms')
			)
			,array(
				'label'=>'&nbsp;'
				,'column'=>'vocabulary_id'
				,'mutation'=>array($this,'displayAddTermToVocabLink')
			)
		);
	}
	
	public function execute($arrArgs) {
		
		// get the current page
		$this->_intPage = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):1;
		
		// Terms switch
		$this->_strRedirect = !empty($arrArgs) && in_array($arrArgs[0],array('Terms','Edit','Add','Create','Fields'))?array_shift($arrArgs):null;
		
		// Number of items per page
		$intLimit = 10;
		
		// Paginate module
		$this->_arrTemplateData['PAGINATION_TPL'] = $this->_objMCP->executeComponent('Component.Util.Module.Pagination',array($intLimit,$this->_intPage),'Component.Util.Template',array($this));
		
		// Set the table headers
		$this->_arrTemplateData['headers'] = $this->_getHeaders();
		
		// Back to vocabularies link
		$this->_arrTemplateData['back_link'] = $this->getBasePath(false);
		
		// Create new vocabulary link
		$this->_arrTemplateData['create_link'] = "{$this->getBasePath(false)}/Create";
		
		// Determine whether use is allowed to create vocabulary
		$perm = $this->_objMCP->getPermission(MCP::ADD,'Vocabulary');
		$this->_arrTemplateData['allow_create_vocab'] = $perm['allow'];
		
		// template to send back
		$strTpl = 'Vocabulary';
		$this->_arrTemplateData['REDIRECT_TPL'] = '';
		
		// Internal terms redirect
		
		// view terms tree as nested module
		if(strcmp('Terms',$this->_strRedirect) == 0) {
			$this->_arrTemplateData['REDIRECT_TPL'] = $this->_objMCP->executeComponent(
				'Component.Taxonomy.Module.Tree.Term'
				,$arrArgs
				,null
				,array($this)
			);
			
			// change the template
			$strTpl = 'Redirect';
			
		// view edit form as nested module (create uses same module)
		} else if(strcmp('Edit',$this->_strRedirect) == 0 || strcmp('Create',$this->_strRedirect) == 0) {
			$this->_arrTemplateData['REDIRECT_TPL'] = $this->_objMCP->executeComponent(
				'Component.Taxonomy.Module.Form.Vocabulary'
				,$arrArgs
				,null
				,array($this)
			);
			
			// change the template
			$strTpl = 'Redirect';	

		// Add new term to vocabulary
		} else if(strcmp('Add',$this->_strRedirect) == 0) {		
			$this->_arrTemplateData['REDIRECT_TPL'] = $this->_objMCP->executeComponent(
				'Component.Taxonomy.Module.Form.Term'
				,$arrArgs
				,null
				,array($this)
			);
			
			// change the template
			$strTpl = 'Redirect';	

		// View vocabulary fields
		} else if(strcmp('Fields',$this->_strRedirect) == 0) {
			$this->_arrTemplateData['REDIRECT_TPL'] = $this->_objMCP->executeComponent(
				'Component.Field.Module.List'
				,$arrArgs
				,null
				,array($this)
			);
			
			// change the template
			$strTpl = 'Redirect';	
			
		}
		
		return "Vocabulary/$strTpl.php";
	}
	
	/*
	* Get path to module state
	* 
	* @param bool add redirect variable
	* @return str base path
	*/
	public function getBasePath($redirect=true) {
		
		$strBasePath = parent::getBasePath();
		
		// ad the page number
		 $strBasePath.= "/{$this->_intPage}";
		 
		 if($redirect === true && $this->_strRedirect) {
		 	$strBasePath.= "/{$this->_strRedirect}";
		 }
		 
		 return $strBasePath;
		
	}
	
	/*
	* Header callback for displaying name of vocabulary 
	* 
	* @param mix value
	* @param array vocabulary row
	* @return str string to echo
	*/
	public function displayName($value,$row) {
		
		$name = '';
		
		if($row['pkg'] === null) {
			$name = $row['system_name'];
		} else {
			$name = "{$row['pkg']}::{$row['system_name']}";
		}
		
		if(!$row['allow_edit']) {
			return $name;
		}
		
		/*
		* Build name with edit link 
		*/
		return sprintf(
			'<a href="%s/Edit/%u">%s</a>'
			,$this->getBasePath(false)
			,$row['vocabulary_id']
			,$name
		);
		
	}
	
	/*
	* header callback to display link to vocabulary fields 
	* 
	* @param mix value
	* @param array vocabulary data
	* @return str output
	*/
	public function displayDynamicFieldLink($value,$row) {
		
		return sprintf(
			'<a href="%s/Fields/MCP_VOCABULARY/%u">Fields</a>'
			,$this->getBasePath(false)
			,$value
		);
		
	}

	/*
	* Header callback for displaying link to terms
	* 
	* @param mix value
	* @param array vocabulary row
	* @return str string to echo
	*/
	public function displayLinkToTerms($value,$row) {
		
		$name = '';
		
		if($row['pkg'] === null) {
			$name = $row['system_name'];
		} else {
			$name = "{$row['pkg']}::{$row['system_name']}";
		}
		
		return sprintf(
			'<a href="%s/Terms/%s">Terms</a>'
			,$this->getBasePath(false)
			,$name
		);
		
	}
	
	/*
	* Header callback to display link to add term to vocabulary
	* 
	* @param mix value
	* @param array vocabulary data
	* @return str string to echo
	*/
	public function displayAddTermToVocabLink($value,$row) {
		
		if(!$row['allow_add_term']) {
			return '+';
		}

		return sprintf(
			'<a href="%s/Add/Vocabulary/%u">+</a>'
			,$this->getBasePath(false)
			,$row['vocabulary_id']
		);
		
	}
	
}
?>