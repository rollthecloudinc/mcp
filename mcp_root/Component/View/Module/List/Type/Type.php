<?php 
/*
* List view entity types 
*/
class MCPViewListType extends MCPModule {
	
	private 
	
	/*
	* View data access layer 
	*/
	$_objDAOView
	
	/*
	* Alternate routing path 
	*/
	,$_strRequest;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		
		// Get view data access layer
		$this->_objDAOView = $this->_objMCP->getInstance('Component.View.DAO.DAOView',array($this->_objMCP));
		
	}
	
	/*
	* Get table configuration 
	*/
	private function _getHeaders() {
		
		$mcp = $this->_objMCP;
		$rownum = 0;
		$mod = $this;
		
		return array(
			array(
				'label'=>'&nbsp;'
				,'column'=>'label'
				,'mutation'=>function($value,$row) use (&$rownum) {
					return ++$rownum;
				}
			)
			,array(
				'label'=>'Human Name'
				,'column'=>'label'
				,'mutation'=>null
			)
			,array(
				'label'=>'System Name'
				,'column'=>'value'
				,'mutation'=>null
			)
			,array(
				'label'=>'Fields'
				,'column'=>'value'
				,'mutation'=>function($value,$row) use ($mcp,$mod) {
					return $mcp->ui('Common.Field.Link',array(
						'url'=>"{$mod->getBasePath(false)}/Fields/{$value}"
						,'label'=>'Fields'
					));
				}
			)
		);
		
	}
	
	public function execute($arrArgs) {
		
		// Alternate routing path
		$this->_strRequest = !empty($arrArgs) && in_array($arrArgs[0],array('Fields'))?array_shift($arrArgs):null;
		
		if($this->_strRequest === null) {
			// Assign view types to template varaiable
			$this->_arrTemplateData['types'] = $this->_objDAOView->fetchViewTypes();
		
			// Assign table headers to template variable
			$this->_arrTemplateData['headers'] = $this->_getHeaders();
		}
		
		// Load back link
		$this->_arrTemplateData['back_link'] = $this->getBasePath(false);
		
		/*
		* Primary template file and redirect content
		*/	
		$strTpl = 'Type';
		$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = '';
		
		// Fields redirect
		if(strcmp('Fields',$this->_strRequest) === 0) {
			$this->_arrTemplateData['TPL_REDIRECT_CONTENT'] = $this->_objMCP->executeComponent(
				'Component.View.Module.List.Field'
				,$arrArgs
				,null
				,array($this)
			);
			$strTpl = 'Redirect';
		}
		
		return "Type/$strTpl.php";
	}
	
	/*
	* Get base path to current module state
	* 
	* @return str base path
	*/
	public function getBasePath($redirect=true) {
		$strBasePath = parent::getBasePath();
		
		// append redirect flag
		if($redirect === true && $this->_strRequest !== null) {
			$strBasePath.= "/{$this->_strRequest}";
		}
		
		return $strBasePath;
	}
	
}
?>