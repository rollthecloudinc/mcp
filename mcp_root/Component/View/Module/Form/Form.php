<?php
class MCPViewForm extends MCPModule {
	
	protected 
	
	/*
	* View data access layer 
	*/
	$_objDAOView;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	protected function _init() {
		
		// Get view data access layer
		$this->_objDAOView = $this->_objMCP->getInstance('Component.View.DAO.DAOView',array($this->_objMCP));
		
	}
	
	public function execute($arrArgs) {
		
		// Get the form configuration
		$arrConfig = $this->_objMCP->getFrmConfig($this->getPkg());
		
		// test select menu for field
		$fields = $this->_objDAOView->fetchFieldsByViewType('Node:6');
		
		//$fields2 = $this->_objDAOView->fetchFieldsByViewPath('Node:6/manufacturer');
		
		// $field = $this->_objDAOView->fetchFieldsByViewPath('Node:6/site');
		
		// echo '<pre>',print_r($field),'</pre>'; exit;
		
		// echo '<pre>',print_r($field),'</pre>';
		
		//echo '<pre>',print_r($fields2),'</pre>';
		
		// echo '<pre>',print_r($fields),'</pre>';
		
		// echo '<pre>',print_r($arrConfig),'</pre>';
		
		$view = $this->_objDAOView->fetchViewById(4);
		// echo '<pre>',print_r($view),'</pre>';
		
		// ------------------------------------------------------------------------------------------------
		
		/*$paths = array(
			'Node:6/manufacturer/system_name'
			,'Node:6/author/username'
			,'Node:6/node_title'
			,'Node:6/main_image/image_label'
			// ,'Node:6/site/id'
			,'Node:6/msrp'
			,'Node:6/sale_price'
		);*/
		
		$types = array();
		foreach($view->fields as $item) {
			$types['fields'][] = $item['path'];
		}
		
		foreach($view->filters as $item) {
			$types['filters'][] = $item['path'];
		}
		
		foreach($view->sorting as $item) {
			$types['sorting'][] = $item['path'];
		}
		
		// ------------------------------------------------------------------------------------------------
		
		$current = '';
		
		foreach($types as $type=>$paths) {
			
			foreach($paths as $index=>$path) {
				
				foreach(explode('/',$path) as $pos=>$piece) {
					$current = $pos == 0?$piece:"$current/$piece";
					
					if($pos != 0) {
						$arrConfig[$type][$index][($pos-1)]['value']  = $piece;
					}
					
					$arrConfig[$type][$index][$pos]['values'] = array();
					
					foreach($this->_objDAOView->fetchFieldsByViewPath($current) as $arrField) {
						$arrConfig[$type][$index][$pos]['values'][] = array(
							'label'=>$arrField['label']
							,'value'=>$arrField['path']
						);
					}
					
				}
			
			}
			
		}
		
		// echo '<pre>',print_r($arrConfig),'<pre>';
		// exit;
		
		// ---------------------------------------------------------------------------------------------
		
		
		// ---------------------------------------------------------------------------------------------
		
		//echo '<pre>',print_r($select),'</pre>';
		
		
		$this->_arrTemplateData['config'] = $arrConfig; 
		
		// $this->_arrTemplateData['fields'] = $fields;
		
		// $this->_arrTemplateData['select'] = $select;
		
		return 'Form/Form.php';
	}
	
} 
?>