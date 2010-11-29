<fieldset>
	<legend>Module Config</legend>
	
	<?php
		if(!empty($module_config)) {
			
			print('<ul>');
			foreach($module_config as $field=>$data) {
				
				/*
				* Print the field label 
				*/
				printf(
					'<li><label for="navigation-items-module-config-%s">%s%s</label>'
					,strtolower(str_replace('_','-',$field))
					,$data['label']
					,$data['required'] == 'Y'?'&nbsp<span class="required">*</span>':''
				);
				
				/*
				* Print the field input, select, radio, checkbox, etc 
				*/
				if(isset($data['values'])) {
					
					printf(
						'<select name="%s[%s][%s]" id="navigation-items-module-config-%s">'
						,$name
						,'module_config'
						,$field
						,strtolower(str_replace('_','-',$field))
					);
					
					foreach($data['values'] as $option_value) {
						printf(
							'<option value="%s"%s>%s</option>'
							,$option_value['value']
							,$values['module_config'][$field] == $option_value['value']?' selected="selected"':''
							,$this->out($option_value['label'])
						);
					}
					
					print('</select>');
				
				} else {
					
					switch(isset($data['type'])?$data['type']:'') {
						case 'bool':
							$input_type = 'checkbox';
							break;
							
						default:
							$input_type = 'text';
					}
					
					printf(
						'<input type="%s" name="%s[%s][%s]" value="%s" id="navigation-items-module-config-%s"'.$this->close(false)
						,$input_type
						,$name
						,'module_config'
						,$field
						,$values['module_config'][$field]
						,strtolower(str_replace('_','-',$field))
					);
					
				}
				
				print('</li>');
				
			} 
			print('</ul>');
		} else {
			print('<p>No config available for target module</p>');
		}
	?>
	
</fieldset>