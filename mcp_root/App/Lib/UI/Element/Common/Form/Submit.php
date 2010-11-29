<?php 
namespace UI\Element\Common\Form;

class Submit implements \UI\Element {
	
	public function settings() {
		return array(
			'label'=>array(
				'default'=>'Submit'
			)
			,'name'=>array(
				'required'=>true
			)
			,'disabled'=>array(
				'default'=>false
			)
		);
	}
	
	public function html($settings,\UI\Manager $ui) {
		
		extract($settings);
		return sprintf(
			'<input type="submit" name="%s" value="%s"%s>'
			,$name
			,$label
			,$disabled === true?' disabled="disabled"':''
		);
		
	}
	
}
?>