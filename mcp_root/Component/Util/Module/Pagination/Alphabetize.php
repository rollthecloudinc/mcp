<?php 
/*
* Alphabetization pagination interface 
*/
class MCPUtilPaginationAlphabetize extends MCPModule {
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
	}
	
	public function execute($arrArgs) {
		
		/*
		* Extract the current letter
		*/
		$strLetter = !empty($arrArgs) && in_array($arrArgs[0],range('A','Z'))?array_shift($arrArgs):null;
		
		/*
		* Alphabetize module 
		*/
		$this->_objParentModule->alphabetize($strLetter);
		
		/*
		* Assign template data 
		*/
		$this->_arrTemplateData['letter'] = $strLetter;
		$this->_arrTemplateData['alphabet'] = range('A','Z');
		$this->_arrTemplateData['base_path'] = $this->_objParentModule->getBasePath(false);
		
		/*
		* Return the aphabetization template 
		*/
		return 'Pagination/Alphabetize.php';
	}
	
}
?>