<?php 
namespace UI\Element\Common\Form;

class TextArea implements \UI\Element {
	
	public function settings() {
		return array(
			'name'=>array(
				'required'=>true
			)
			,'id'=>array(
				'required'=>true
			)
			,'value'=>array(
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
			'<textarea name="%s" id="%s"%s>%s</textarea>'
			,$name
			,$id
			,$disabled === true?' disabled="disabled"':''
			,$value
		);
		
	}
	
	public function xhtml($settings,\UI\Manager $ui) {
		return $this->html($settings,$ui);
	}
	
}
?>