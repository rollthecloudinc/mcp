<?php
namespace UI\Element\Common\Form;

class LinkedList implements \UI\Element {
	
	public function settings() {
		return array(
			'data'=>array(
				'required'=>true
                         )
                         ,'value'=>array(
                                'required'=>true
                         )
		);
	}
	
	public function html($settings,\UI\Manager $ui) {
		
		extract($settings);
                
                $path = $this->_discoverPath($data,$value);
                
                // echo '<pre>'.print_r($path,true).'</pre>';
                
                $elements = $this->_htmlBuild($data,$path,$ui);
                
                //echo '<pre>'.print_r($elements,true).'</pre>';
                
                return implode('',$elements);
                
		
	}
        
        /*
        * Discover path to value. This is necessary so that
        * each level of the select can be built. 
        */
        protected function _discoverPath($data,$value) {
                
            if(!isset($data['values']) || empty($data['values'])) {
                return;
            }
                
            foreach($data['values'] as $val) {
                    
                if(is_array($value)) {
                    if(in_array($val['value'],$value)) {
                        return array($val['value']);
                    }
                } else {
                    if($val['value'] == $value) {
                        return array($val['value']);
                    }
                }
                    
                $return = $this->_discoverPath($val,$value);
                    
                if($return !== null) {
                    return array_merge(array($val['value']),$return);
                }
                    
            }
            
        }
        
        /*
        * Build linked list HTML 
        */
        protected function _htmlBuild($data,$path,$ui,$runner=0) {
            
            if(!isset($path[$runner])) {
                return array();
            }
            
            foreach($data['values'] as $val) {
                
                if($val['value'] != $path[$runner]) {
                    continue;
                }
                
                $copy = array_slice($data,0);
                foreach($copy['values'] as &$cp) {
                    if(isset($cp['values'])) {
                        unset($cp['values']);
                    }
                }
                
                if($runner !== 0) {
                    array_unshift($copy['values'],array('label'=>'--','value'=>''));
                }
                
                return array_merge(
                     array($ui->draw('Common.Form.Select',array(
                         'id'=>'x'
                         ,'name'=>'x'
                         ,'value'=>$val['value']
                         ,'data'=>$copy
                     )))
                    ,$this->_htmlBuild($val,$path,$ui,($runner + 1))
                );
                
            }
            
        }
	
}
?>