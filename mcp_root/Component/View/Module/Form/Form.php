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
						$arrConfig[$type][$index]['paths'][($pos-1)]['value']  = $piece;
					}
					
					$arrConfig[$type][$index]['paths'][$pos]['values'] = array();
					
					foreach($this->_objDAOView->fetchFieldsByViewPath($current) as $arrField) {
						$arrConfig[$type][$index]['paths'][$pos]['values'][] = array(
							'label'=>$arrField['label']
							,'value'=>$arrField['path']
						);
					}
					
				}
				
				// determine if item is an override
				$arrConfig[$type][$index]['override'] = 0;
				
				// extra -----------------------------------------------------
				
				if(strcmp('filters',$type) === 0) {
					
					// One of the major edge case is boolean types - 
					// in that case only yes and no should be supplied.
					$arrConfig[$type][$index]['comparision'] = '=';
					$arrConfig[$type][$index]['comparisions']['values'] = array(
						array('label'=>'=','value'=>'=')
						,array('label'=>'RegExp','value'=>'regex')
						,array('label'=>'FullText','value'=>'fulltext')
						,array('label'=>'Contains','value'=>'like')
					);
					
					// possible wildcard - mock for now testing (only compatible with contains in reality)
					$arrConfig[$type][$index]['wildcard'] = '%s';
					$arrConfig[$type][$index]['wildcards']['values'] = array(
						array('label'=>'%s','value'=>'%s')
						,array('label'=>'s%','value'=>'s%')
						,array('label'=>'%s%','value'=>'%s%')
					);
					
					// possible regex definitoon - testing now - only compatible with regex comparision in reality
					$arrConfig[$type][$index]['regex'] = '/^[0-9]$/';
					
					// may add other for fulltext stuff but I think that is it for now
					
					// ----------------------------------------------------------------------------------
					
					// operators - I think all have operator except for the boolean values, I think.
					
					// These will need a DAO method just like some of the others most likely above
					// for now just getting the lay of the land so hard coding will do.
					
					// This is a bit of an edge case - needs to be rendered as radio group rather
					// than standard select menu. This may be something that needs to be added to the
					// Form UI render - perhaps a flag or something to render a group of values
					// as a radio group rather than the default select emnu sued now. For now though
					// we just do it manually to get the lay of the land.
					$arrConfig[$type][$index]['operators']['values'] = array(
						array('label'=>'One Of','value'=>'one')
						,array('label'=>'All Of','value'=>'all')
						,array('label'=>'None Of','value'=>'none')
					);
					
					// ---------------------------------------------------------------------------------
					
					// Add values - including static, view nesting and argument binding drop downs
					// NOTE: even though it is named values these ARE MOT drop down values
					// but the values for comparision. For now just do it statically to get an idea
					// how it should actually be handled.
					// $arrConfig[$type][$index]['value']['values'] = array();
					
				} else if(strcmp('sorting',$type) === 0) {
					
					// Add ordering - determine whether sorting will occur in ascending, descending or magical random order
					// This will obviously need a dao method
					// Its probably a good idea to change the label based on the type. When ordering
					// a numerical column use increase and decrease but when ordering a none-numeric, string
					// column such as title use A-Z,Z-A for a better user experience, understanding of how
					// string ordering is handled.
					$arrConfig[$type][$index]['ordering'] = 'desc';
					$arrConfig[$type][$index]['orderings']['values'] = array(
						array('label'=>'Increase','value'=>'asc')
						,array('label'=>'Decrease','value'=>'desc')
						,array('label'=>'Random','value'=>'rand')
					);
					
					// --------------------------------------------------------------------------------------
					
					// Add priorities - priority may be checked to order items in a specific order
					// using FIELD(). 
					
					// TODO
					
				} else {
					
					$arrConfig[$type][$index]['sortable'] = 0;
					$arrConfig[$type][$index]['editable'] = 0;
					
					// add possible options for the field. For example an image will
					// have options to make it greyscale or change its size. Could
					// also add a way to apply a SQL translation or after translation
					// in php. Need to think on this though.
					// NOTE: these are not options to be rendered as a select menu
					// but options for the field selection that exist as separate
					// groupings.
					
					// Kinda sucks it takes another call but for now it will work fine
					$field = $this->_objDAOView->fetchFieldByViewPath($path);				
					if($field && $field['options']) {
						
						foreach($field['options'] as $option) {
							$arrConfig[$type][$index]['options']['values'][] = array(
								'label'=>$option['name']
								,'value'=>$option['name']
							);
						}
						
					}
					
				}
				
				// ----------------------------------------------------------
			
			}
			
		}
		
		//echo '<pre>',print_r($arrConfig),'<pre>';
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