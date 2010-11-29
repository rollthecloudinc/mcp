<?php 
namespace UI\Element\Common\Form;

class Label implements \UI\Element {
	
	public function settings() {
		return array(
			'for'=>array(
				'required'=>true
				,'type'=>'string'
			)
			,'label'=>array(
				'required'=>true
				,'type'=>'string'
			)
			,'required'=>array(
				'default'=>false
			)
		);
	}
	
	public function html($settings,\UI\Manager $ui) {
		
		extract($settings);
		
		return sprintf(
			'<label for="%s">%s%s</label>'
			,$for
			,$label
			,$required === true?'&nbsp<span class="required">*</span>':''
		);
		
	}
	
}
?>