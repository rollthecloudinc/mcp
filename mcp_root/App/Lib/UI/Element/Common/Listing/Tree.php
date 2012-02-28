<?php
namespace UI\Element\Common\Listing;

class Tree implements \UI\Element {
	
	public function settings() {
		return array(
			'data'=>array(
				'default'=>array()
			)
			,'child_key'=>array(
				'default'=>'children'
			)
			,'value_key'=>array(
				'default'=>'value'
			)
			,'list_element'=>array(
				'default'=>'ul'
			)
			,'depth_class'=>array(
				'default'=>true
			)
			,'mutation'=>array(
				'default'=>null
			)
			,'cls'=>array(
				'default'=>null
			)
			,'form'=>array( // flag to wrap contents in form element
				'default'=>false
			)
			,'form_action'=>array( // form action, when form
				'default'=>''
			)
			,'form_name'=>array( // form name, when form
				'default'=>''
			)
			,'form_method'=>array( // form action, when form
				'default'=>''
			)
			,'form_legend'=>array( // form legend, when form
				'default'=>''
			)
		);
	}
	
	public function html($settings,\UI\Manager $ui) {
		
		return $this->_html($settings);
		
	}
	
	private function _html($settings,$runner=0) {
		
		extract($settings);
		$out = '';
		
		if(isset($form) && $form === true) {
			$out.= sprintf(
				'<form name="%s" action="%s" method="%s"><fieldset><legend>%s</legend>'
				,$form_name
				,$form_action
				,$form_method
				,$form_legend
			);
		}
		
		/*
		* Build out tree 
		*/
		if(!empty($data)) {
			$out.="<$list_element";
                        
                        if($cls !== null) {
                            $out.= ' class="'.$cls.'"';
                        }
                        
                        $out.= '>';
                        
			foreach($data as $index=>$item) {
				$out.= sprintf(
					'<li%s>%s%s</li>'
					,$depth_class === true?' class="depth-'.$runner.'"':''
					,$mutation !== null?call_user_func_array($mutation,array($item[$value_key],$item,$index)):$item[$value_key]
					,$this->_html(array(
						'data'=>isset($item[$child_key]) && !empty($item[$child_key])?$item[$child_key]:array()
						,'child_key'=>$child_key
						,'value_key'=>$value_key
						,'list_element'=>$list_element
						,'depth_class'=>$depth_class
						,'mutation'=>$mutation
					),($runner+1))
				);
			}
			$out.="</$list_element>";
		}
		
		if(isset($form) && $form === true) {
			$out.= '</fieldset></form>';
		}
		
		return $out;
		
	}
}
?>