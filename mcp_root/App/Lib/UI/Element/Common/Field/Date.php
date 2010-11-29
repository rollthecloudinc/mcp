<?php
namespace UI\Element\Common\Field;

class Date implements \UI\Element {
	
	public function settings() {
		return array(
			'date'=>array(
				'required'=>true
			)
			,'format'=>array(
				'default'=>'M d,Y g:ia'
			)
			,'type'=>array(
				'default'=>'timestamp'
			)
		);
	}
	
	public function html($settings,\UI\Manager $ui) {
		
		extract($settings);	
		return date($format,strtotime($date));
		
	}
	
}
?>