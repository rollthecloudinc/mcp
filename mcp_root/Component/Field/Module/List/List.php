<?php 
/*
* List fields for specified entity 
*/
class MCPFieldList extends MCPModule {
	
	protected
	
	/*
	* Field data access layer 
	*/
	$_objDAOField
	
	/*
	* Type fo entity (module argument required) 
	*/
	,$_strEntityType
	
	/*
	* Entity id (optional module argument)
	*/
	,$_intEntitiesId
	
	/*
	* Internal redirect 
	*/
	,$_strRequest;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	protected function _init() {
		// fetch field data acess layer
		$this->_objDAOField = $this->_objMCP->getInstance('Component.Field.DAO.DAOField',array($this->_objMCP));
	}
	
	/*
	* Get SQL where clause 
	* 
	* @param str where clause
	*/
	protected function _getFilter() {
		return sprintf(
			"f.sites_id = %s AND f.entity_type = '%s' AND f.entities_id %s"
			,$this->_objMCP->escapeString($this->_objMCP->getSitesId())
			,$this->_objMCP->escapeString($this->_strEntityType)
			,$this->_intEntitiesId !== null?"= {$this->_objMCP->escapeString($this->_intEntitiesId)}":'IS NULL'
		);
	}
	
	/*
	* Get display table headers 
	* 
	* @return array headers
	*/
	protected function _getHeaders() {
		
		// Closure module reference
		$mod = $this;
		
		return array(
			array(
				'label'=>'Label'
				,'column'=>'cfg_label'
				,'mutation'=>null
			)
			,array(
				'label'=>'Name'
				,'column'=>'cfg_name'
				,'mutation'=>null
			)
			,array(
				'label'=>'&nbsp;'
				,'column'=>'fields_id'
				,'mutation'=>function($value,$row) use($mod) {
					return sprintf(
						'<a href="%s/Edit/%u">Edit</a>'
						,$mod->getBasePath(false)
						,$value
					);
				}
				
			)
		);
	}
	
	/*
	* Get SQL order by clause
	* 
	* @return str order by clause
	*/
	protected function _getSort() {
		return null;
	}
	
	public function execute($arrArgs) {
		
		/*
		* Entity type is required 
		*/
		$this->_strEntityType = !empty($arrArgs)?array_shift($arrArgs):null;
		
		/*
		* Entity id optional 
		*/
		$this->_intEntitiesId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null; 
		
		/*
		* Resolve internal redirect 
		*/
		$this->_strRequest = !empty($arrArgs) && in_array($arrArgs[0],array('New','Edit'))?array_shift($arrArgs):null;
		
		/*
		* fetch the fields 
		*/
		$this->_arrTemplateData['fields'] = $this->_objDAOField->listFields(
			'f.*'
			,$this->_getFilter()
			,$this->_getSort()
		);
		
		/*
		* Get the table display headers 
		*/
		$this->_arrTemplateData['headers'] = $this->_getHeaders();
		
		/*
		* Create new field link 
		*/
		$this->_arrTemplateData['create_link'] = "{$this->getBasePath(false)}/New/{$this->_strEntityType}".($this->_intEntitiesId !== null?"-{$this->_intEntitiesId}":'');
		
		/*
		* Redirection back link 
		*/
		$this->_arrTemplateData['back_link'] = $this->getBasePath(false);
		
		/*
		* Redirection back label 
		*/
		$this->_arrTemplateData['back_label'] = 'Back To Fields';
		
		/*
		* Handle internal module redirects 
		*/
		$strTpl = 'List';
		
		/*
		* Placeholder redirect content 
		*/
		$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = '';
		
		// create new field
		if(strcmp('New',$this->_strRequest) === 0 || strcmp('Edit',$this->_strRequest) === 0) {
			$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = $this->_objMCP->executeComponent(
				'Component.Field.Module.Form'
				,$arrArgs
				,null
				,array($this)
			);
			$strTpl = 'Redirect';
		}
		
		return "List/$strTpl.php";
	}
	
	/*
	* Get path to current modules state
	* 
	*  @param bool append redirect if exists?
	*  @return str path
	*/
	public function getBasePath($redirect=true) {
		$strBasePath = parent::getBasePath();
		
		// Add the entity type
		if($this->_strEntityType !== null) {
			$strBasePath.= "/{$this->_strEntityType}";
		}
		
		// Add the entities id
		if($this->_intEntitiesId !== null) {
			$strBasePath.= "/{$this->_intEntitiesId}";
		}
		
		// append redirect flag/keyword
		if($redirect === true && $this->_strRequest !== null) {
			$strBasePath.= "/{$this->_strRequest}";
		}
		
		return $strBasePath;
	}
	
}
?>