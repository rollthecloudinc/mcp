<?php
namespace UI\Element\Common\Field;

class Content implements \UI\Element {
	
	public function settings() {
		return array(
			'content'=>array(
				'required'=>true
			)
			,'type'=>array(
				'required'=>false
                                ,'default'=>'text'
			)
		);
	}
	
	public function html($settings,\UI\Manager $ui) {
		
		extract($settings);
                $out = '';
                
		switch($type) {
			/*
			* PHP content 
			*/
			case 'php':
				eval('?>'.$content);
				break;
			
			/*
			* HTML content 
			*/
			case 'html':
				$out.= $content;
				break;
			
			/*
			* Textual content s
			*/
			case 'text':
			default:
				$out.= strip_tags($content);
		}
                
                return $out;
		
	}
	
}
?>