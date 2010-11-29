<?php 
class MCPUtilPagination extends MCPModule {
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
	}
	
	public function execute($arrArgs) {
		
		/*
		* Extract number of items to show per page
		*/
		$intLimit = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):10;
		
		/*
		* Extract the current page number
		*/
		$intPage = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):1;
		
		/*
		* Calculate page offset 
		*/
		$intOffset = ($intPage-1)*$intLimit;
		
		/*
		* Paginate and get number of found rows 
		*/
		$intFoundRows = $this->_objParentModule->paginate($intOffset,$intLimit);
		
		/*
		* Assign template data 
		*/
		$this->_arrTemplateData['limit'] = $intLimit;
		$this->_arrTemplateData['page'] = $intPage;
		$this->_arrTemplateData['offset'] = $intOffset;
		$this->_arrTemplateData['found_rows'] = $intFoundRows;
		$this->_arrTemplateData['total_pages'] = $intFoundRows < $intLimit?1:ceil($intFoundRows/$intLimit);
		$this->_arrTemplateData['visible_pages'] = $this->getConfigValue('visible_pages');
		$this->_arrTemplateData['base_path'] = $this->_objParentModule->getBasePath(false);
		$this->_arrTemplateData['label'] = $intFoundRows == 1?$this->getConfigValue('items_label_singular'):$this->getConfigValue('items_label_plural');
		
		/*
		* Return the pagination template 
		*/
		return 'Pagination/Pagination.php';
		
	}
	
}
?>