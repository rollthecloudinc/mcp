<?php 
namespace UI\Element\Common\Field;

class Heading implements \UI\Element {
	
	public function settings() {
		return array(
			'heading'=>array(
				'required'=>true
			)
			,'type'=>array(
				'required'=>true
			)
			,'id'=>array(
				'default'=>null
			)
			,'class'=>array(
				'default'=>null
			)
		);
	}
	
	public function html($settings,\UI\Manager $ui) {
		
		extract($settings);
		return sprintf(
			'<%s%s%s>%s</%s>'
			,$type
			,$id === null?'':' id="'.$id.'"'
			,$class === null?'':' class="'.$class.'"'
			,$heading
			,$type
		);
		
	}
	
}
?>