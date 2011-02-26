<?php 
namespace UI\Element\Common\Form;

/*
* This is a conceptually complex UI element. It is complex
* because it makes use of other UI Form elements such as; select, radio, input, etc. 
*/
class Form implements \UI\Element {
	
	public function settings() {
		return array(
			'name'=>array(
				'default'=>''
			)
			,'action'=>array(
				'default'=>''
			)
			,'config'=>array(
				'default'=>array()
			)
			,'values'=>array(
				'default'=>array()
			)
			,'errors'=>array(
				'default'=>array()
			)
			,'legend'=>array(
				'default'=>''
			)
			,'idbase'=>array(
				'default'=>'frm-'
			)
			,'submit'=>array(
				'default'=>'Save'
			)
			,'clear'=>array(
				'default'=>null
			)
			,'method'=>array(
				'default'=>'POST'
			)
			,'image_path'=>array(
				'default'=>null
			)
			,'layout'=>array(
				'default'=>null
			)
		);
	}
	
	public function html($settings,\UI\Manager $ui) {
		
		// buffered elements
		$elements = array();
		
		// buffered form
		$form = '';
		
		/*$name = isset($frm['name'])?$frm['name']:'';
		$action = isset($frm['action'])?$frm['action']:'';
		$config = isset($frm['config'])?$frm['config']:array();
		$values = isset($frm['values'])?$frm['values']:array();
		$errors = isset($frm['errors'])?$frm['errors']:array();
		$legend = isset($frm['legend'])?$frm['legend']:'';
		$idbase = isset($frm['idbase'])?$frm['idbase']:'frm-';
		$submit = isset($frm['submit'])?$frm['submit']:'Save';*/
		
		// clear button
		//$clear =  isset($frm['clear'])?$frm['clear']:'';
		
		extract($settings);
		$out='';
		
		$form.= sprintf(
			'<form name="%s" id="%s" action="%s" method="%s" enctype="multipart/form-data">'
			,$name
			,$name
			,$action
			,$method
		);
		
		$form.= '<fieldset>';
		
		$form.= "<legend>$legend</legend>";
			
		
		if(!empty($config)) {
			
			$form.= '<ul>';
				
			foreach($config as $field=>$data) {
				
				// current buffered element
				$element = '';
				
				/*
				* Dynamic field reference 
				*/
				$dynamic_field = isset($data['dynamic_field']);
				
				/*
				* Display widget 
				*/
				$widget = isset($data['widget'])?$data['widget']:null;
					
				/*
				* Skip static fields 
				*/
				if(isset($data['static']) && strcmp('Y',$data['static']) == 0) continue;
					
				/*
				* Disabled attribute 
				*/
				$strDisabled = isset($data['disabled']) && $data['disabled'] == 'Y'?' disabled="disabled"':'';
				
				$form.= '<li><?php echo $'.$field.'; ?>';
					
				$element.= $ui->draw('Common.Form.Label',array(
					'for'=>$idbase.strtolower(str_replace('_','-',$field))
					,'label'=>$data['label']
					,'required'=>isset($data['required']) && $data['required'] == 'Y'?true:false
				));

				// Multi-values with checkbox group only require a single loop, same for multi-select
				$loops = isset($data['multi']) && !in_array($widget,array('checkbox_group','multi_select'))?$data['multi']:1;	

				// display multiple values as list for now
				// not needed for special multi_select and checkbox_group cases
				if( isset($data['multi']) && !in_array($widget,array('multi_select','checkbox_group')) ) {
					
					// create add new field submit/button
					$element.= $ui->draw('Common.Form.Input',array(
						'type'=>'submit'
						,'name'=>"{$name}[action][add][{$field}]"
						,'value'=>'+'
						,'id'=>$idbase.strtolower(str_replace('_','-',$field)).'-add'
					));				
					
					$element.= sprintf(
						'<ol id="%s">'
						,$idbase.strtolower(str_replace('_','-',$field)) // for multiple values label references container
					);
				}
				
				for($i=0;$i<$loops;$i++) {
					
					// multi_select and checkbox_group don't need/support delete control
					if( isset($data['multi']) && !in_array($widget,array('multi_select','checkbox_group')) ) {
						$out.= '<li>';
						
						// create control to delete value
						$element.= $ui->draw('Common.Form.Input',array(
							'type'=>'submit'
							,'name'=>"{$name}[action][delete][{$field}][{$i}]"
							,'value'=>'-'
							,'id'=>$idbase.strtolower(str_replace('_','-',$field)).'-'.($i+1).'-delete'
						));
						
					}
				
					/*
					* Print the field input, select, radio, checkbox, etc 
					*/
					if(isset($data['values'])) {
							
						switch( $widget ) {
							
							/*
							* Collection of checkboxes
							* 
							* @todo: hierarchy support
							*/
							case 'checkbox_group':
								
								// checkbox group only compatible with scalar fields
								if( isset($data['multi']) ) {
									
									// Render as checkbox group in a list
									$out.= $ui->draw('Common.Listing.Tree',array(
										'data'=>$data['values']
										,'mutation'=>function($val,$checkbox,$index) use(&$field,&$values,&$name,&$idbase,&$strDisabled,&$element,$ui) {
										
											// label
											$element = $ui->draw('Common.Form.Label',array(
												'for'=>$idbase.strtolower( str_replace('_','-',$field) ).'-'.($index+1)
												,'label'=>$checkbox['label']
											));
										
											// checkbox
											$element.= $ui->draw('Common.Form.Input',array(
												'type'=>'checkbox'
												,'id'=>$idbase.strtolower( str_replace('_','-',$field) ).'-'.($index+1)
												,'name'=>"{$name}[$field][$index][value]"
												,'value'=>$checkbox['value']
												,'checked'=>in_array($checkbox['value'],$values[$field])
												,'disabled'=>$strDisabled?true:false
											));
											
											return $out;
										
										}
										
									));
									
									break;
								}
								
							/*
							* Multiple select 
							*/
							case 'multi_select':
								$element.= $ui->draw('Common.Form.Select',array(
						
									// Elements that are dynamic fields and contain multiple values are placed in value key
									'name'=>"{$name}[$field][][value]"
						
									,'id'=>$idbase.strtolower(str_replace('_','-',$field)).'-'.($i+1)
									,'data'=>$data
									,'value'=>$values[$field]
									,'size'=>isset($data['size'])?$data['size']:7
									,'disabled'=>$strDisabled?true:false
									,'multiple'=>true
								));
								break;
								
							/*
							* Auto complete field 
							*/
							case 'autocomplete':
								break;
								
							/*
							* External look-up - pop-up window with options and return - good for large data sets 
							*/
							case 'lookup':
								break;
						
							/*
							* Single select menu
							*/
							case 'select':
							default:
								$element.= $ui->draw('Common.Form.Select',array(
						
									// Elements that are dynamic fields and contain multiple values are placed in value key
									'name'=>sprintf('%s[%s]%s',$name,$field,(isset($data['multi'])?$dynamic_field?"[$i][value]":"[$i]":''))
						
									,'id'=>$idbase.strtolower(str_replace('_','-',$field)).( isset($data['multi'])?'-'.($i+1):'' )
									,'data'=>$data
									,'value'=>isset($data['multi'])?isset($values[$field][$i])?$values[$field][$i]:'':$values[$field]
									,'size'=>isset($data['size'])?$data['size']:null
									,'disabled'=>$strDisabled?true:false
								));
						
						}
							
						
					} else if(isset($data['textarea'])) {	

						$element.= $ui->draw('Common.Form.TextArea',array(
							'name'=>sprintf('%s[%s]%s',$name,$field,(isset($data['multi'])?$dynamic_field?"[$i][value]":"[$i]":''))
							,'id'=>$idbase.strtolower(str_replace('_','-',$field)).( isset($data['multi'])?'-'.($i+1):'' )
							,'disabled'=>$strDisabled?true:false
							,'value'=>isset($data['multi'])?isset($values[$field][$i])?$values[$field][$i]:'':$values[$field]								
						));
						
					} else {
					
						switch(isset($data['type'])?$data['type']:'') {
							case 'bool':
								$input_type = 'checkbox';
								break;
							
							default:
								$input_type = 'text';
						}
							
						/*
						* Functions with serialization for now only 
						*/
						$val = isset($data['multi'])?isset($values[$field][$i])?$values[$field][$i]:'':$values[$field];
							
						/*
						* Override for file input 
						*/
						if(isset($data['media'])) {
							$input_type = 'file';
						}
							
						$element.= $ui->draw('Common.Form.Input',array(
							'type'=>$input_type
							,'name'=>sprintf('%s[%s]%s',$name,$field,(isset($data['multi'])?$dynamic_field && $input_type !== 'file'?"[$i][value]":"[$i]":''))
							,'value'=>strcmp($input_type,'checkbox') == 0?'1':$val
							,'max'=>isset($data['max'])?$data['max']:null
							,'id'=>$idbase.strtolower(str_replace('_','-',$field)).( isset($data['multi'])?'-'.($i+1):'')
							,'checked'=>strcmp($input_type,'checkbox') == 0 && ((string) $val)?true:false
							,'disabled'=>$strDisabled?true:false
						));
							
						/*
						* For images show thumbnail 
						*/
						if(isset($data['media']) && $val) {
								
							/*
							* Images will now show after form is submitted 
							*/
							$intImagesId = is_array($val) && isset($val['id'])?$val['id']:$val;
								
							$element.= $ui->draw('Common.Field.Thumbnail',array(
								'src'=>( $image_path !== null?sprintf($image_path,(string) $intImagesId):$intImagesId )
							));
								
						}
					
					}
						
					// close multiple value list element
					if( isset($data['multi']) ) {
							
						/*
						* When using a collapsed widget such as; checkbox group or
						* multi-select each id needs to be dumped out in a look because
						* the outer loop will only occur once. 
						*/					
						if(isset($data['values']) && in_array($widget,array('checkbox_group','multi_select'))) {
							/*
							* This logic is necessary to support multi-selects and check box groups or any other
							* item that allows multiple selections without separate physical fields.
							* 
							* @todo: hierarchy support
							*/
							foreach($data['values'] as $index=>$value) {
								
								foreach($values[$field] as $mcpfield) {
									if( $value['value'] == $mcpfield ) {
										$element.= $ui->draw('Common.Form.Input',array(
											'type'=>'hidden'
											,'name'=>"{$name}[$field][$index][id]"
											,'value'=>$mcpfield->getId()
										));										
										break;
									}
								}	
								
							}
						} else {
							/*
							* IMPORTANT: For dynamic fields the field values primary key is needed
							* to update any scalar dynamic field. 
							*/
							if($values[$field][$i] instanceof \MCPField) {
								$element.= $ui->draw('Common.Form.Input',array(
									'type'=>'hidden'
									,'name'=>"{$name}[$field][$i][id]"
									,'value'=>$values[$field][$i]->getId()
								));
							}							
						}
						
						// multi_select and checkbox don't need/support sorting controls
						if( !in_array($widget,array('multi_select','checkbox_group')) ) {
							
							// Create controls to sort multiple values - render as a list / tree
							$element.= $ui->draw('Common.Listing.Tree',array(
								'value_key'=>'control'
								,'data'=>array(
									array(
										'control'=>$ui->draw('Common.Form.Input',array(
											'type'=>'submit'
											,'value'=>'up'
											,'id'=>$idbase.strtolower(str_replace('_','-',$field)).'-'.($i+1).'-up'
											,'name'=>"{$name}[action][up][{$field}][{$i}]"
										))
									)
									,array(
										'control'=>$ui->draw('Common.Form.Input',array(
											'type'=>'submit'
											,'value'=>'down'
											,'id'=>$idbase.strtolower(str_replace('_','-',$field)).'-'.($i+1).'-down'
											,'name'=>"{$name}[action][down][{$field}][{$i}]"
										))
									)
								)
							));	
							
							$element.= '</li>';
						
						}
							
					}
					
				}
					
				// close multiple value list
				if( isset($data['multi']) ) $element.= '</ol>';
				
				/*
				* Print field errors 
				*/
				if(isset($errors[$field])) $element.= sprintf('<p>%s</p>',$errors[$field]);
				
				$form.= '</li>';
				
				$elements[$field] = $element;
				
			} 
			
			/*
			* Submit button 
			*/
			$submit = sprintf('<input type="submit" name="%s[save]" value="%s" id="%s%s">',$name,$submit,$idbase,'save');
			$form.= "<li class=\"save\">$submit</li>";
			
				
			/*
			* Clear button 
			*/
			if(strlen($clear) !== 0) {
				$form.= sprintf('<li class="save"><input type="submit" name="%s[clear]" value="%s" id="%s%s"></li>',$name,$clear,$idbase,'save');
			}
				
			$form.= '</ul>';
		} else {
			$form.= '<p>No form available</p>';
		}
		
		$form.= '</fieldset></form>';
		
		// where the magic happens!
		
		extract($elements);
		$rendered = '';
		
		ob_start();
		
		$layout === null?eval('?>'.$form):include($layout);
		
		$rendered = ob_get_contents();
		ob_end_clean();
		
		return $rendered;
		
	}
	
}
?>