<?php 
namespace UI\Element\Common\Field;

class Link implements \UI\Element {
	
	public function settings() {
		return array(
			'url'=>array(
				'required'=>true
			)
			,'label'=>array(
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
			'<a href="%s"%s%s>%s</a>'
			,$url
			,$id === null?'':' id="'.$id.'"'
			,$class === null?'':' class="'.$class.'"'
			,$label
		);
		
	}
	
}
?>