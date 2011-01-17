<?php 
namespace UI\Element\Common\Form;

class Select implements \UI\Element {
	
	public function settings() {
		return array(
			'name'=>array(
				'required'=>true
			)
			,'id'=>array(
				'required'=>true
			)
			,'data'=>array(
				'required'=>true
			)
			,'value'=>array(
				'required'=>true
			)
			,'disabled'=>array(
				'default'=>false
			)
			,'size'=>array(
				'default'=>null
			)
			,'label_key'=>array(
				'default'=>'label'
			)
			,'value_key'=>array(
				'default'=>'value'
			)
		);
	}
	
	public function html($settings,\UI\Manager $ui) {
		
		extract($settings);		
		$out='';
		
		$out.= sprintf(
			'<select name="%s" id="%s"%s%s>'
			,$name
			,$id
			,$disabled === true?' disabled="disabled"':''
			,$size !== null?' size="'.$size.'"':''
		);
						
		/*
		* This format can be used as a callback for recursive select menus 
		*/
		$func = function($func,$data,$runner=0) use($value,$value_key,$label_key) {					
			$out='';
			
			foreach($data['values'] as $option_value) {
			
				$out.= sprintf(
					'<option class="depth-%u" value="%s"%s>%s</option>'
					,$runner
					,$option_value[$value_key]
					,$value == $option_value[$value_key]?' selected="selected"':''
					,$option_value[$label_key]
				);	
									
				if(isset($option_value['values']) && !empty($option_value['values'])) {
					$out.= call_user_func($func,$func,$option_value,$value,($runner+1));
				}
							
			} 
			
			return $out;
		
		};
						
		/*
		* Build select menu 
		*/
		$out.= call_user_func($func,$func,$data);
		$out.= '</select>';
		
		return $out;
		
	}
	
}
?>