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
		);
	}
	
	public function html($settings,\UI\Manager $ui) {
		
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
		
		$out.= sprintf(
			'<form name="%s" id="%s" action="%s" method="%s" enctype="multipart/form-data">'
			,$name
			,$name
			,$action
			,$method
		);
		
		$out.= '<fieldset>';
		
		$out.= "<legend>$legend</legend>";
			
		
		if(!empty($config)) {
			
			$out.= '<ul>';
				
			foreach($config as $field=>$data) {
					
				/*
				* Skip static fields 
				*/
				if(isset($data['static']) && strcmp('Y',$data['static']) == 0) continue;
					
				/*
				* Disabled attribute 
				*/
				$strDisabled = isset($data['disabled']) && $data['disabled'] == 'Y'?' disabled="disabled"':'';
					
				$out.= '<li>'.$ui->draw('Common.Form.Label',array(
					'for'=>$idbase.strtolower(str_replace('_','-',$field))
					,'label'=>$data['label']
					,'required'=>isset($data['required']) && $data['required'] == 'Y'?true:false
				));
					
				$loops = isset($data['multi'])?$data['multi']:1;	

				// display multiple values as list for now
				if( isset($data['multi']) ) {
					
					// create add new field submit/button
					$out.= $ui->draw('Common.Form.Input',array(
						'type'=>'submit'
						,'name'=>"{$name}[action][add][{$field}]"
						,'value'=>'+'
						,'id'=>$idbase.strtolower(str_replace('_','-',$field)).'-add'
					));				
					
					$out.= sprintf(
						'<ol id="%s">'
						,$idbase.strtolower(str_replace('_','-',$field)) // for multiple values label references container
					);
				}
				
				for($i=0;$i<$loops;$i++) {
					
					if( isset($data['multi']) ) {
						$out.= '<li>';
						
						// create control to delete value
						$out.= $ui->draw('Common.Form.Input',array(
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
							
						$out.= $ui->draw('Common.Form.Select',array(
							'name'=>sprintf('%s[%s]%s',$name,$field,(isset($data['multi'])?"[$i]":''))
							,'id'=>$idbase.strtolower(str_replace('_','-',$field)).( isset($data['multi'])?'-'.($i+1):'' )
							,'data'=>$data
							,'value'=>$values[$field]
							,'size'=>isset($data['size'])?$data['size']:null
							,'disabled'=>$strDisabled?true:false
						));
							
						
						} else if(isset($data['textarea'])) {	

							$out.= $ui->draw('Common.Form.TextArea',array(
								'name'=>sprintf('%s[%s]%s',$name,$field,(isset($data['multi'])?"[$i]":''))
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
							
							$out.= $ui->draw('Common.Form.Input',array(
								'type'=>$input_type
								,'name'=>sprintf('%s[%s]%s',$name,$field,(isset($data['multi'])?"[$i]":''))
								,'value'=>strcmp($input_type,'checkbox') == 0?'1':$val
								,'max'=>isset($data['max'])?$data['max']:null
								,'id'=>$idbase.strtolower(str_replace('_','-',$field)).( isset($data['multi'])?'-'.($i+1):'')
								,'checked'=>strcmp($input_type,'checkbox') == 0 && $val?true:false
								,'disabled'=>$strDisabled?true:false
							));
							
							/*
							* For images show thumbnail 
							*/
							if(isset($data['media']) && $val) {
								$out.= $ui->draw('Common.Field.Thumbnail',array(
									'src'=>( $image_path !== null?sprintf($image_path,(string) $val):$val )
								));
							}
					
						}
						
						// close multiple value list element
						if( isset($data['multi']) ) {
							
							/*
							* IMPORTANT: For dynamic fields the field values primary key is needed
							* to update any scalar dynamic field. 
							*/
							if($val instanceof \MCPField) {
								$out.= $ui->draw('Common.Form.Input',array(
									'type'=>'hidden'
									,'name'=>"{$name}[$field][$i][id]"
									,'value'=>$values[$field][$i]->getId()
								));
							}
							
							// Create controls to sort multiple values - render as a list / tree
							$out.= $ui->draw('Common.Listing.Tree',array(
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
							
							$out.= '</li>';
							
						}
						
					}
					
					// close multiple value list
					if( isset($data['multi']) ) $out.= '</ol>';
				
					/*
					* Print field errors 
					*/
					if(isset($errors[$field])) $out.= sprintf('<p>%s</p>',$errors[$field]);
				
					$out.= '</li>';
				
				} 
			
				/*
				* Submit button 
				*/
				$out.= sprintf('<li class="save"><input type="submit" name="%s[save]" value="%s" id="%s%s"></li>',$name,$submit,$idbase,'save');
				
				/*
				* Clear button 
				*/
				if(strlen($clear) !== 0) {
					$out.= sprintf('<li class="save"><input type="submit" name="%s[clear]" value="%s" id="%s%s"></li>',$name,$clear,$idbase,'save');
				}
				
				$out.= '</ul>';
			} else {
				$out.= '<p>No form available</p>';
			}
		
			$out.= '</fieldset></form>';
			return $out;
		
	}
	
}
?>