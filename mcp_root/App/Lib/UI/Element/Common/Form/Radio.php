<?php 
namespace UI\Element\Common\Form;

class Radio implements \UI\Element {
	
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
			,'checked'=>array(
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
			'<input type="radio" name="%s" value="%s" id="%s"%s%s>'
			,$name
			,$value
			,$id
			,$disabled === true?' disabled="disabled"':''
			,$checked === true?' checked="checked"':''
		);
		
	}
	
}
?>