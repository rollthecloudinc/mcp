<?php 
namespace UI\Element\Common\Field;

class Thumbnail implements \UI\Element {
	
	public function settings() {
		return array(
			'src'=>array(
				'required'=>true
			)
			,'alt'=>array(
				'default'=>null
			)
			,'title'=>array(
				'default'=>null
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
			'<img src="%s"%s%s%s%s>'
			,$src
			,$alt !== null?' alt="'.$alt.'"':''
			,$title !== null?' title="'.$title.'"':''
			,$id !== null?' id="'.$id.'"':''
			,$class !== null?' class="'.$class.'"':''
		);
		
	}
	
}
?>