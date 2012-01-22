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
                        /*
                        * Additional variables to pass to custom
                        * layout template. 
                        */
                        ,'layout_vars'=>array(
                                'default'=>array()
                        )
                        /*
                        * Flag can be set to nest forms within one another. This
                        * will effectively remove the form element and any submit
                        * buttons allowing the outer form to handle the submission
                        * process.  
                        */
                        ,'nested'=>array(
                            'default'=>false
                        )
			,'recaptcha'=>array(
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
		
                if(!$nested) {
                    $form.= sprintf(
                            '<form name="%s" id="%s" action="%s" method="%s" enctype="multipart/form-data">'
                            ,$name
                            ,$name
                            ,$action
                            ,$method
                    );
                }
		
		$form.= '<fieldset>';
		
		$form.= "<legend>$legend</legend>";
			
		
		if(!empty($config)) {
			
			//$form.= '<ul>';
				
			foreach($config as $field=>$data) {
				
				// echo '<pre>',print_r($data),'</pre>';
				
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
				if(isset($data['static']) && strcmp('Y',$data['static']) == 0) {
                                    continue;
                                }
					
				/*
				* Disabled attribute 
				*/
				$strDisabled = isset($data['disabled']) && $data['disabled'] == 'Y'?' disabled="disabled"':'';
				
				$form.= '<div class="clearfix widget-'.($widget?str_replace('_','-',$widget):'null').' '.(isset($data['multi'])?'many':'one').' '.($errors[$field]?'error':'').'"><?php echo $'.$field.'; ?>';
					
				$element.= $ui->draw('Common.Form.Label',array(
					'for'=>$idbase.strtolower(str_replace('_','-',$field))
					,'label'=>$data['label']
					,'required'=>isset($data['required']) && $data['required'] == 'Y'?true:false
				));
                                
                                $element.= '<div class="input">';

				// Multi-values with checkbox group only require a single loop, same for multi-select
				$loops = isset($data['multi']) && !in_array($widget,array('checkbox_group','multi_select')) && isset($values[$field]) && is_array($values[$field])?count($values[$field]):1;	

                                // default to a single loop
                                if($loops === 0) {
                                    $loops = 1;
                                }
                                
				// display multiple values as list for now
				// not needed for special multi_select and checkbox_group cases
				if( isset($data['multi']) && !in_array($widget,array('multi_select','checkbox_group')) ) {
					
					// create add new field submit/button
					$element.= $ui->draw('Common.Form.Input',array(
						'type'=>'submit'
						,'name'=>"{$name}[action][add][{$field}]"
						,'value'=>'+'
						,'id'=>$idbase.strtolower(str_replace('_','-',$field)).'-add'
                                                ,'class'=>'btn info'
					));				
					
					$element.= sprintf(
						'<ol id="%s" class="ui-widget-multi unstyled">'
						,$idbase.strtolower(str_replace('_','-',$field)) // for multiple values label references container
					);
				}
				
				for($i=0;$i<$loops;$i++) {
					
					// multi_select and checkbox_group don't need/support delete control
					if( isset($data['multi']) && !in_array($widget,array('multi_select','checkbox_group')) ) {
						$element.= '<li>';
						
						// create control to delete value
						$element.= $ui->draw('Common.Form.Input',array(
							'type'=>'submit'
							,'name'=>"{$name}[action][delete][{$field}][{$i}]"
							,'value'=>'-'
							,'id'=>$idbase.strtolower(str_replace('_','-',$field)).'-'.($i+1).'-delete'
                                                        ,'class'=>'btn danger'
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
							* @note: 
                                                        * 
                                                        * checkbox group does not support a hierarchy of items just a single level. This
                                                        * is a limitation that should be introduced into the form to select a widget for a field. 
							*/
							case 'checkbox_group': // @todo modify this like others
								
								// checkbox group only compatible with scalar fields
								if( isset($data['multi']) ) {
									
									// echo '<pre>',print_r($data['values']),'</pre>';
									
									// rebuild values without any blank values
									$rebuild = array();
                                                                        
									foreach($data['values'] as $option) {
										if(strlen($option['value']) === 0) continue;
										$rebuild[] = $option;
									}
                                                                        
                                                                        //echo '<pre>'; var_dump($values[$field]); echo '</pre>';
                                                                        //echo '<pre>'; var_dump($rebuild); echo '</pre>';
									
									// Render as checkbox group in a list
									$element.= $ui->draw('Common.Listing.Tree',array(
										'data'=>$rebuild
										,'mutation'=>function($val,$checkbox,$index) use(&$field,&$values,&$name,&$idbase,&$strDisabled,&$element,$ui) {
											
											$out = '';
                                                                                        $checked = false;
                                                                                        
                                                                                        // in_array is not correct because mcp instances need to be casted to strings before comparision
                                                                                        if(!empty($values[$field])) {
                                                                                            foreach($values[$field] as $mixVal) {
                                                                                                //var_dump($mixVal);
                                                                                                if( (is_array($mixVal) && isset($mixVal['value']) && $checkbox['value'] === $mixVal['value']) || ((string) $mixVal) === $checkbox['value']) {
                                                                                                    //echo "<p>{$mixVal} = {$checkbox['value']}</p>";;
                                                                                                    $checked = true;
                                                                                                    break;
                                                                                                }
                                                                                            }
                                                                                        }
											
											// label
											$out.= $ui->draw('Common.Form.Label',array(
												'for'=>$idbase.strtolower( str_replace('_','-',$field) ).'-'.($index+1)
												,'label'=>$checkbox['label']
											));
										
											// checkbox
											$out.= $ui->draw('Common.Form.Input',array(
												'type'=>'checkbox'
												,'id'=>$idbase.strtolower( str_replace('_','-',$field) ).'-'.($index+1)
												,'name'=>"{$name}[$field][][value]"
												,'value'=>$checkbox['value']
												,'checked'=>$checked
												,'disabled'=>$strDisabled?true:false
											));
											
											return $out;
										
										}
										
									));
                             
									
									break;
								}
								
							/*
							* Multiple select 
                                                        * 
                                                        * @note: supports hierarchy of items
							*/
							case 'multi_select':
                                                            
                                                                $rebuildValues = array();
                                                                
                                                                if(isset($values[$field]) && is_array($values[$field])) {
                                                                    foreach($values[$field] as $mixVal) {
                                                                        if(is_array($mixVal)) {
                                                                            $rebuildValues[] = $mixVal['value'];
                                                                        } else {
                                                                            $rebuildValues[] = $mixVal;
                                                                        }
                                                                    }
                                                                }
                                                            
								$element.= $ui->draw('Common.Form.Select',array(
						
									// Elements that are dynamic fields and contain multiple values are placed in value key
									'name'=>"{$name}[$field][][value]"
						
									,'id'=>$idbase.strtolower(str_replace('_','-',$field)).'-'.($i+1)
									,'data'=>$data
									,'value'=>$rebuildValues
									,'size'=>isset($data['size'])?$data['size']:10
									,'disabled'=>$strDisabled?true:false
									,'multiple'=>true
								));
                                                                        
                               
								break;
                                                                
                                                                
                                                        /*
                                                        * Linked list @todo
                                                        */
                                                        case 'linkedlist':
                                                            $element.= $ui->draw('Common.Form.LinkedList',array(
                                                                 'data'=>$data
                                                                ,'value'=>isset($data['multi'])?isset($values[$field][$i])?$values[$field][$i]:'':$values[$field]
                                                            ));
                                                            break;
                                                                
								
							/*
							* Auto complete field @todo
							*/
							case 'autocomplete':
								break;
								
							/*
							* External look-up - pop-up window with options and return - good for large data sets 
							*/
							case 'lookup': // @todo
								break;
						
							/*
							* Single select menu
                                                        *
                                                        * @note:
                                                        * 
                                                        * This widget is compatible with a hierarchy of items.   
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
							
						
					} else if(isset($data['textarea']) || strcasecmp($widget,'textarea') === 0) {	

						$element.= $ui->draw('Common.Form.TextArea',array(
							'name'=>sprintf('%s[%s]%s',$name,$field,(isset($data['multi'])?$dynamic_field?"[$i][value]":"[$i]":''))
							,'id'=>$idbase.strtolower(str_replace('_','-',$field)).( isset($data['multi'])?'-'.($i+1):'' )
							,'disabled'=>$strDisabled?true:false
							,'value'=>isset($data['multi'])?isset($values[$field][$i])?$values[$field][$i]:'':$values[$field]								
						));
                                                
                                                
                                        } else if(isset($data['media']) && strcasecmp($data['media'],'video') === 0) {
						
                                            
                                                $element.= $ui->draw('Common.Form.Video',array(
                                                    'base_name'=>sprintf('%s[%s]%s',$name,$field,(isset($data['multi'])?"[$i]":''))
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
							,'value'=>strcmp($input_type,'checkbox') !== 0?is_array($val)?$val['value']:$val:'1'
							,'max'=>isset($data['max'])?$data['max']:null
							,'id'=>$idbase.strtolower(str_replace('_','-',$field)).( isset($data['multi'])?'-'.($i+1):'')
							,'checked'=>strcmp($input_type,'checkbox') == 0 && ((string) $val)?true:false
							,'disabled'=>$strDisabled?true:false
							,'class'=>!empty($widget)?"ui-widget-$widget":null
						));
                                                
							
						/*
						* For images show thumbnail (@todo this will affect movies and audio I believe)
						*/
						if(isset($data['media']) && $val) {
								
							/*
							* Images will now show after form is submitted 
							*/
							$mixMediaId = is_array($val) && isset($val['value'])?$val['value']:$val;
							
                                                        /*
                                                        * Image preview 
                                                        */
                                                        if(strcasecmp('image',$data['media']) === 0) {
                                                            if(!is_array($mixMediaId)) {
                                                                /*$element.= $ui->draw('Common.Field.Thumbnail',array(
								'src'=>( $image_path !== null?sprintf($image_path,(string) $mixMediaId):$mixMediaId )
                                                                ));*/
                                                                $element.= '<div class="preview" style="background-image: url('.( $image_path !== null?sprintf($image_path,(string) $mixMediaId):$mixMediaId ).');"></div>';
                                                            }
                                                        }
                                                        
                                                        /*
                                                        * Generic File cue
                                                        */
                                                        else if(strcasecmp('file',$data['media']) === 0) {
                                                            if(is_object($val)) {
                                                                $element.= '<p>'.htmlentities($val['file_label']).'</p>';
                                                            }
                                                        }
                                                        
                                                        /*
                                                        * When mutiple items are allowed for media field drop
                                                        * the value so that the image meta data can still be updated
                                                        * regardless of new image upload.  
                                                        * 
                                                        * Do the same for single image field so that ssociated meta
                                                        * data can be easily updated.  
                                                        */
                                                        if(!is_array($mixMediaId)) {
                                                            $element.= $ui->draw('Common.Form.Input',array(
                                                                'type'=>'hidden'
                                                                ,'name'=>isset($data['multi'])?"{$name}[$field][$i][value]":"{$name}[$field][value]"
                                                                ,'value'=>$mixMediaId
                                                            ));
                                                        }
                                                        
                                                        unset($intMediaId);
                                                        
								
						}
					
					}
                                        
                                        
                                        /*
                                        * Add meta data fields for image alt and caption. 
                                        */
                                        if(isset($data['media']) && strcasecmp($data['media'],'image') === 0) {
                                            
                                            // alt
                                            $element.= $ui->draw('Common.Form.Label',array(
                                                'label'=>'Alt',
                                                'for'=>''
                                            ));
                                            $element.= $ui->draw('Common.Form.Input',array(
                                                'type'=>'text',
                                                'id'=>'test-alt-1',
                                                'value'=>($val && isset($val['image_alt'])?$val['image_alt']:''),
                                                'name'=>(isset($data['multi'])?"{$name}[$field][$i][image_alt]":"{$name}[$field][image_alt]")
                                            ));    
                                            
                                            // caption
                                            $element.= $ui->draw('Common.Form.Label',array(
                                                'label'=>'Caption',
                                                'for'=>''
                                            ));                                                       
                                            $element.= $ui->draw('Common.Form.TextArea',array(
                                                'id'=>'test-caption-1',
                                                'value'=>($val && isset($val['image_caption'])?$val['image_caption']:''),
                                                'name'=>(isset($data['multi'])?"{$name}[$field][$i][image_caption]":"{$name}[$field][image_caption]")
                                            ));
                                                            
                                            // echo '<pre>'; var_dump($val['image_caption']); echo '</pre>';
                                            
                                        }
						
					// close multiple value list element
					if( isset($data['multi']) ) {
                                           
							
						/*
						* When using a collapsed widget such as; checkbox group or
						* multi-select each id needs to be dumped out in a loop because
						* the outer loop will only occur once. 
						*/					
						if(isset($data['values']) && in_array($widget,array('checkbox_group','multi_select'))) {
                                               
                                           
                                                        $rebuildValues = array();

                                                        if(isset($values[$field]) && is_array($values[$field])) {
                                                            foreach($values[$field] as $mixVal) {
                                                                if(is_array($mixVal)) {
                                                                    if(isset($mixVal['id'])) {
                                                                        $rebuildValues[] = $mixVal['id'];
                                                                    }
                                                                } else {
                                                                    $rebuildValues[] = $mixVal->getId();
                                                                }
                                                            }
                                                        }
                                                    
							/*
							* This logic is necessary to support multi-selects and check box groups or any other
							* item that allows multiple selections without separate physical fields.
							* 
							*/
								
                                                        foreach($rebuildValues as $index=>$strVal) {
                                                            $element.= $ui->draw('Common.Form.Input',array(
                                                                'type'=>'hidden'
                                                                ,'name'=>"{$name}[$field][$index][id]"
                                                                ,'value'=>$strVal
                                                            ));										
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
								//echo "<p>{$name}[$field][$i][id]</p>";
                                                                        
                                                        // necessary for submissions
							} else if(is_array($values[$field][$i]) && isset($values[$field][$i]['id'])) {
								$element.= $ui->draw('Common.Form.Input',array(
									'type'=>'hidden'
									,'name'=>"{$name}[$field][$i][id]"
									,'value'=>$values[$field][$i]['id']
								));                                                            
                                                        }							
						}
						
						// multi_select and checkbox don't need/support sorting controls
						if( !in_array($widget,array('multi_select','checkbox_group')) ) {
							
							if( !isset($data['sortable']) || $data['sortable'] != 0 ) {
							
								// Create controls to sort multiple values - render as a list / tree
								/*$element.= $ui->draw('Common.Listing.Tree',array(
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
								));*/
							
							}
							
							$element.= '</li>';
						
						}
							
					}
					
				}
					
				// close multiple value list
				if( isset($data['multi']) ) $element.= '</ol>';
				
				/*
				* Print field errors 
				*/
				if(isset($errors[$field])) $element.= sprintf('<span class="help-inline">%s</span>',$errors[$field]);
				
				//$form.= '</li>';
                                
                                
                                $form.= '</div>';
				
				$elements[$field] = $element.'</div>';
				
			}
			
			if( $recaptcha !== null ) {
				$elements['recaptcha'] = $recaptcha;
				$form.= "<li>$recaptcha</li>";
                        }
                        
                        if(!$nested) {
                            
                            /*
                            * Submit button 
                            */
                            $submit = sprintf('<input type="submit" name="%s[save]" class="btn primary" value="%s" id="%s%s">',$name,$submit,$idbase,'save');
                            $form.= "<div class=\"save actions\">$submit</div>";
			
				
                            /*
                            * Clear button 
                            */
                            if(strlen($clear) !== 0) {
				$form.= sprintf('<li class="save"><input type="submit" name="%s[clear]" value="%s" id="%s%s"></li>',$name,$clear,$idbase,'save');
                            }
                            
                        }
				
			// $form.= '</ul>';
			
		} else {
			$form.= '<p>No form available</p>';
		}
		

		
		$form.= '</fieldset>';
                
                if(!$nested) {
                    $form.= '</form>';
                }
		
		// where the magic happens!
		
		extract($elements);
		$rendered = '';
                
                if(isset($settings['layout_vars'])) {
                    $layout_vars = $settings['layout_vars'];
                } else {
                    $layout_vars = null;
                }
		
		ob_start();
		
		$layout === null?eval('?>'.$form):include($layout);
		
		$rendered = ob_get_contents();
		ob_end_clean();
		
		return $rendered;
		
	}
	
}
?>