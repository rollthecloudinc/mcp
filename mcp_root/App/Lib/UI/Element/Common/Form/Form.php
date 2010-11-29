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
				for($i=0;$i<$loops;$i++) {
				
					/*
					* Print the field input, select, radio, checkbox, etc 
					*/
					if(isset($data['values'])) {
							
						$out.= $ui->draw('Common.Form.Select',array(
							'name'=>sprintf('%s[%s]%s',$name,$field,(isset($data['multi'])?'[]':''))
							,'id'=>$idbase.strtolower(str_replace('_','-',$field))
							,'data'=>$data
							,'value'=>$values[$field]
							,'size'=>isset($data['size'])?$data['size']:null
							,'disabled'=>$strDisabled?true:false
						));
							
						
						} else if(isset($data['textarea'])) {	

							$out.= $ui->draw('Common.Form.TextArea',array(
								'name'=>sprintf('%s[%s]%s',$name,$field,(isset($data['multi'])?'[]':''))
								,'id'=>$idbase.strtolower(str_replace('_','-',$field))
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
							* Override for file input 
							*/
							if(isset($data['image'])) {
								$input_type = 'file';
								
								// show image preview
								/*if(!is_array($values[$field]) && is_numeric($values[$field])) {
									printf(
										'<img src="%s/%u">'
										,'http://local.mcp4/img.php'
										,$values[$field]
									);
								}*/
								
							}
							
							$val = isset($data['multi'])?isset($values[$field][$i])?$values[$field][$i]:'':$values[$field];
							
							$out.= $ui->draw('Common.Form.Input',array(
								'type'=>$input_type
								,'name'=>sprintf('%s[%s]%s',$name,$field,(isset($data['multi'])?'[]':''))
								,'value'=>strcmp($input_type,'checkbox') == 0?'1':$val
								,'max'=>isset($data['max'])?$data['max']:null
								,'id'=>$idbase.strtolower(str_replace('_','-',$field))
								,'checked'=>strcmp($input_type,'checkbox') == 0 && $val?true:false
								,'disabled'=>$strDisabled?true:false
							));
					
						}
						
					}
				
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