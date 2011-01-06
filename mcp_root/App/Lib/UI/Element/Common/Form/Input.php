<?php
namespace UI\Element\Common\Form;
/*
* Programmatic input element
*/
class Input implements \UI\Element {
	
	public function settings() {
		return array(
			'type'=>array(
				'required'=>true
			)
			,'name'=>array(
				'required'=>true
			)
			,'value'=>array(
				'required'=>true
			)
			,'id'=>array(
				'required'=>false
			)
			,'max'=>array(
				'default'=>null
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
			'<input type="%s" name="%s" value="%s"%sid="%s"%s%s>'
			,$type
			,$name
			,$value
			,$max !== null?' maxlength="'.$max.'"':''
			,$id
			,$disabled === true?' disabled="disabled"':''
			,$checked === true?' checked="checked"':''
		);
		
	}
	
}
?>