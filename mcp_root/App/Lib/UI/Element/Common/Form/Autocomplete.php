<?php
namespace UI\Element\Common\Form;
/*
* Programmatic autocomplete element
*/
class Autocomplete implements \UI\Element {
	
	public function settings() {
		return array(
			'service'=>array(
                            'required'=>true
			)
                        ,'name'=>array(
                            'required'=>true
                        )
		);
	}
	
	public function html($settings,\UI\Manager $ui) {
		
		extract($settings);

                $out = '<input type="text" name="'.$name.'" data-ui-source="'.$service.'">';
                
                return $out;
                
		
	}
	
}
?>