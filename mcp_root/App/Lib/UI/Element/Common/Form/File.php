<?php 
namespace UI\Element\Common\Form;

class File implements \UI\Element {
	
	public function settings() {
		return array(
			'name'=>array(
				'required'=>true
			)
			,'id'=>array(
				'required'=>true
			)
			,'disabled'=>array(
				'default'=>false
			)
			,'class'=>array(
				'default'=>null
			)
		);
	}
	
	public function html($settings,\UI\Manager $ui) {
		
		extract($settings);
		return sprintf(
			'<input type="file" name="%s" id="%s"%s>'
			,$name
			,$id
			,$disabled === true?' disabled="disabled"':''
		);
		
	}
	
}
?>