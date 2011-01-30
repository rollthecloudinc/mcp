<?php
/*
* List available fields for a view type 
*/
class MCPViewListField extends MCPModule {
	
	private 
	
	/*
	* View data access layer 
	*/
	$_objDAOView
	
	/*
	* View type 
	*/
	,$_strViewType;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	private function _init() {
		
		// Get view data access layer
		$this->_objDAOView = $this->_objMCP->getInstance('Component.View.DAO.DAOView',array($this->_objMCP));
		
	}
	
	private function _getHeaders() {
		
		return array(
			array(
				'label'=>'Label'
				,'column'=>'label'
				,'mutation'=>function($value,$row) {
					return $row['primary']?"$value <em>(primary key)</em>":$value;
				}
			)
			,array(
				'label'=>'Path'
				,'column'=>'path'
				,'mutation'=>null
			)
			,array(
				'label'=>'Column'
				,'column'=>'column'
				,'mutation'=>null
			)
			,array(
				'label'=>'Data Type'
				,'column'=>'type'
				,'mutation'=>null
			)
			,array(
				'label'=>'Dynamic'
				,'column'=>'dynamic'
				,'mutation'=>function($value,$row) {
					return $value?'Y':'--';
				}
			)
			,array(
				'label'=>'relation'
				,'column'=>'relation'
				,'mutation'=>function($value,$row) {
					return $value?$row['relation_type']:'--';
				}
			)
			,array(
				'label'=>'Entity'
				,'column'=>'relation'
				,'mutation'=>function($value,$row) {
					return $value?$value:'--';
				}
			)
		);
		
	}
	
	public function execute($arrArgs) {
		
		// Extract view type from url
		$this->_strViewType = !empty($arrArgs) && is_string($arrArgs[0])?array_shift($arrArgs):null;
		
		// Assign view types fields to template variable
		$this->_arrTemplateData['fields'] = $this->_objDAOView->fetchFieldsByViewType( $this->_strViewType );
		
		// echo '<pre>',print_r( $this->_arrTemplateData['fields'] ),'</pre>';
		
		// Assign headers to template variable
		$this->_arrTemplateData['headers'] = $this->_getHeaders();
		
		return 'Field/Field.php';
	}
	
}
?>