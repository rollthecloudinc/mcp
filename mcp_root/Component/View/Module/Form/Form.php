<?php
class MCPViewForm extends MCPModule {
	
	protected 
	
	/*
	* View data access layer 
	*/
	$_objDAOView
	
	/*
	* View 
	*/
	,$_objView;
	
	public function __construct(MCP $objMCP,MCPModule $objParentModule=null,$arrConfig=null) {
		parent::__construct($objMCP,$objParentModule,$arrConfig);
		$this->_init();
	}
	
	protected function _init() {
		
		// Get view data access layer
		$this->_objDAOView = $this->_objMCP->getInstance('Component.View.DAO.DAOView',array($this->_objMCP));
		
	}
	
	/*
	* Get view being edited
	* 
	* @return obj StdClass view
	*/
	protected function _getView() {
		return $this->_objView;
	}
	
	/*
	* Set the view for edit
	* 
	* @param obj view
	*/
	protected function _setView($objView) {
		$this->_objView = $objView;
	}
	
	public function execute($arrArgs) {
		
		// Get the form configuration
		$arrConfig = $this->_objMCP->getFrmConfig($this->getPkg());
		
		// test select menu for field
		// $fields = $this->_objDAOView->fetchFieldsByViewType('Node:6');
		
		//$fields2 = $this->_objDAOView->fetchFieldsByViewPath('Node:6/manufacturer');
		
		// $field = $this->_objDAOView->fetchFieldsByViewPath('Node:6/site');
		
		// echo '<pre>',print_r($field),'</pre>'; exit;
		
		// echo '<pre>',print_r($field),'</pre>';
		
		//echo '<pre>',print_r($fields2),'</pre>';
		
		// echo '<pre>',print_r($fields),'</pre>';
		
		// echo '<pre>',print_r($arrConfig),'</pre>';
		
		/*
		* Extract view for editing 
		*/
		$intViewsId = !empty($arrArgs) && is_numeric($arrArgs[0])?array_shift($arrArgs):null;
		
		if( $intViewsId !== null ) {
			$this->_setView( $this->_objDAOView->fetchViewById($intViewsId) );
		}
		
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
		
		$view = $this->_getView();
		
		$arrConfig['type']['value'] = $view->base_path;
		
		// echo '<pre>',print_r($view),'</pre>';
		
		$types = array();
		foreach($view->fields as $item) {
			$types['fields'][] = $item;
		}
		
		foreach($view->filters as $item) {
			$types['filters'][] = $item;
		}
		
		foreach($view->sorting as $item) {
			$types['sorting'][] = $item;
		}
		
		// ------------------------------------------------------------------------------------------------
		
		$current = '';
		
		foreach($types as $type=>$paths) {
			
			foreach($paths as $index=>$item) {
				
				$path = $item['path'];
				
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
				$arrConfig[$type][$index]['override'] = true;
				
				// able to remove item
				// $arrConfig[$type][$index]['remove'] = true;
				
				// extra -----------------------------------------------------
				
				if(strcmp('filters',$type) === 0) {
					
					// One of the major edge case is boolean types - 
					// in that case only yes and no should be supplied.
					$arrConfig[$type][$index]['comparision'] = $item['comparision'];
					$arrConfig[$type][$index]['comparisions']['values'] = array(
						array('label'=>'=','value'=>'=')
						,array('label'=>'RegExp','value'=>'regex')
						,array('label'=>'FullText','value'=>'fulltext')
						,array('label'=>'Contains','value'=>'like')
					);
					
					// possible wildcard - mock for now testing (only compatible with contains in reality)
					$arrConfig[$type][$index]['wildcard'] =  $item['wildcard'] ; // '%s';
					$arrConfig[$type][$index]['wildcards']['values'] = array(
						array('label'=>'%s','value'=>'%s')
						,array('label'=>'s%','value'=>'s%')
						,array('label'=>'%s%','value'=>'%s%')
					);
					
					// possible regex definitoon - testing now - only compatible with regex comparision in reality
					$arrConfig[$type][$index]['regex'] = $item['regex']; //'/^[0-9]$/';
					
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
					$arrConfig[$type][$index]['operator'] = $item['conditional'];
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
					foreach( $item['values'] as $index2=>&$value ) {
						
						$arrConfig[$type][$index]['values'][$index2]['value'] = $value['value'];
						$arrConfig[$type][$index]['values'][$index2]['type'] = $value['type'];
						
						// type selection drop down list ie. argument, static or view reference
						$arrConfig[$type][$index]['values'][$index2]['types'] = array(
							 array('value'=>'static','label'=>'value')
							,array('value'=>'argument','label'=>'arg')
							,array('value'=>'view','label'=>'view')
						);
						
						// Build argument list for creating a drop-down of available arguments for argument type
						// Note: when switching between types this field will need to change.
						// Also when adding arguments this will need to be updated using js perhaps.
						// Additionally, it might be better to always dump this and swap using js. I will think about it.
						$arrConfig[$type][$index]['values'][$index2]['arguments'] = array();
						foreach( $view->arguments as &$argument) {
							$arrConfig[$type][$index]['values'][$index2]['arguments'][] = array(
								'value'=>$argument['id'],'label'=>$argument['human_name']
							);
						}
						
						// wildcard used for like comparision - override of the default for the filter if not null
						$arrConfig[$type][$index]['values'][$index2]['wildcard'] = $value['wildcard'];
						
						// regular expression used for regex - override of the default for the filter if not null
						$arrConfig[$type][$index]['values'][$index2]['regex'] = $value['regex'];
					}
					
				} else if(strcmp('sorting',$type) === 0) {
					
					// Add ordering - determine whether sorting will occur in ascending, descending or magical random order
					// This will obviously need a dao method
					// Its probably a good idea to change the label based on the type. When ordering
					// a numerical column use increase and decrease but when ordering a none-numeric, string
					// column such as title use A-Z,Z-A for a better user experience, understanding of how
					// string ordering is handled.
					$arrConfig[$type][$index]['ordering'] = $item['ordering'];
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
					
					$arrConfig[$type][$index]['sortable'] = $item['sortable'];
					$arrConfig[$type][$index]['editable'] = $item['editable'];
					
					// add possible options for the field. For example an image will
					// have options to make it greyscale or change its size. Could
					// also add a way to apply a SQL translation or after translation
					// in php. Need to think on this though.
					// NOTE: these are not options to be rendered as a select menu
					// but options for the field selection that exist as separate
					// groupings.
					
					// Kinda sucks it takes another call but for now it will work fine
					$field = $this->_objDAOView->fetchFieldByViewPath( $path );	
					if($field && $field['options']) {
						
						foreach($field['options'] as $index2=>$option) {
							
							$arrConfig[$type][$index]['options'][$index2]['name'] = $option['name'];
							
							/*
							* Name, type and value of the option if it has been implemented by field
						 	*/
							$arrConfig[$type][$index]['options'][$index2]['value'] = '';
							$arrConfig[$type][$index]['options'][$index2]['type'] = '';
							
							/*
							* Build list of option value types
							*/
							$arrConfig[$type][$index]['options'][$index2]['types'] = array(
								 array('value'=>'static','label'=>'value')
								,array('value'=>'argument','label'=>'arg')
								,array('value'=>'view','label'=>'view')
							);
							
							/*
							* Find the actual values for the option as defined by field if
							* the field has implemented (has a value) for the option. 
							*/
							foreach($item['options'] as $opt_value) {
								if( strcmp($opt_value['name'],$option['name']) === 0 ) {
									$arrConfig[$type][$index]['options'][$index2]['value'] = $opt_value['value'];
									$arrConfig[$type][$index]['options'][$index2]['type'] = $opt_value['type'];
									break;
								}
							}
							
							// Build argument list for creating a drop-down of available arguments for argument type
							// Note: when switching between types this field will need to change.
							// Also when adding arguments this will need to be updated using js perhaps.
							// Additionally, it might be better to always dump this and swap using js. I will think about it.
							$arrConfig[$type][$index]['options'][$index2]['arguments'] = array();
							foreach( $view->arguments as &$argument) {
								$arrConfig[$type][$index]['options'][$index2]['arguments'][] = array(
									'value'=>$argument['id'],'label'=>$argument['human_name']
								);
							}
							
						}
						
					}
					
				}
				
				// ----------------------------------------------------------
			
			}
			
		}
		
		// echo '<pre>',print_r($view),'<pre>';
		
		// ---------------------------------------------------------------------------------------------
		
		// View Arguments
		/*
		* Build separate set of elements for each view argument. People will be able
		* to set the human name, system name, origin and type. The system name may not
		* changed once it has been set and must be unique within the the view. The label
		* may change and will be the item displayed/exposed for filters, field options, etc. 
		*/
		$arrConfig['arguments'] = array();
		foreach( $view->arguments as $index => &$argument ) {
			
			/*
			* The system name is derived form the name supplied and may not be changed. The label
			* may be changed any time.
			*/
			$arrConfig['arguments'][$index]['system_name'] = $argument['system_name'];
			
			/*
			* The system name is derived form the name supplied and may not be changed. The label
			* may be changed any time.
			*/
			$arrConfig['arguments'][$index]['human_name'] = $argument['human_name'];
			
			/*
			* Value displayed. For static values this will be true value. For others such as; class, function
			* ,ect it will be the name of a function or foreign key depending on the context. 
			*/
			$arrConfig['arguments'][$index]['value'] = $argument['value'];
			
			/*
			*Arguments context 
			*/
			$arrConfig['arguments'][$index]['context'] = $argument['context'];
			
		}
		
		
		// ---------------------------------------------------------------------------------------------
		
		//echo '<pre>',print_r($select),'</pre>';
		
		
		$this->_arrTemplateData['config'] = $arrConfig; 
		
		// $this->_arrTemplateData['fields'] = $fields;
		
		// $this->_arrTemplateData['select'] = $select;
		
		return 'Form/Form.php';
	}
	
} 
?>