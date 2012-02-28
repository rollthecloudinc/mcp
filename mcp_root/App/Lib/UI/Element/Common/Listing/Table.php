<?php 
namespace UI\Element\Common\Listing;

class Table implements \UI\Element {
	
	public function settings() {
		return array(
			'headers'=>array(
				'default'=>array()
			)
			,'data'=>array(
				'default'=>array()
			)
			,'caption'=>array(
				'default'=>null
			)
                        ,'class'=>array(
                            'default'=>null
                        )
			
			/*
			* Suppport for trees 
			*/
			,'tree'=>array(
				'default'=>false
			)
			,'child_key'=>array(
				'default'=>'children'
			)
			,'mutation'=>array(
				'default'=>null
			)
			
			/*
			* form wrapper 
			*/
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
			),
                        'form_submit'=>array( // include submit button in footer
                            'default'=>false
                        )
		);
	}
	
	public function html($settings,\UI\Manager $ui) {
		
		extract($settings);
		$out = '';
		
		if($form === true) {
			$out.= sprintf(
				'<form name="%s" action="%s" method="%s"%s><fieldset><legend>%s</legend>'
				,$form_name
				,$form_action
				,$form_method
                                ,$class !== null?' class="'.$class.'"':''
				,$form_legend
         
			);
		}
		
		$out.= '<table cellspacing="0" width="100%">';
		if($caption !== null) $out.= sprintf('<caption>%s</caption>',(string) $caption);
		$out.= '<thead>';
		foreach($headers as $header) {
			$out.= sprintf('<th>%s</th>',$header['label']);
		}
		$out.= '</thead>';
                
                if($form_submit === true) {
                    $out.= '<tfoot><tr><td colspan="'.count($headers).'">'.$ui->draw('Common.Form.Submit',array('name'=>$form_name.'[submit]','label'=>'Submit')).'</td></tr></tfoot>';
                }
                
		$out.= '<tbody>';
		
		// $cycle = false;
		
		if(!empty($data)) {
			/*foreach($data as $row) {
				$out.= '<tr'.($cycle =! $cycle?' class="odd"':'').'>';
				foreach($headers as $header) {
					
					$out.= sprintf(
						"<td>%s</td>"
						,$header['mutation'] === null?$row[$header['column']]:call_user_func_array($header['mutation'],array($row[$header['column']],$row))
					);
				}
				$out.= '</tr>';
			}*/
			
			$out.= $this->_renderRow(
				 $data
				,false
				,$headers
				,$settings
			);
			
		} else {
			$out.= sprintf(
				'<tr>
					<td colspan="%s">%s</td>
				 </tr>'
				 ,count($headers)
				 ,'No Items Available'
			);
		}
		
		$out.= '</tbody>';
		$out.= '</table>';
		
		if($form === true) {
			$out.= '</fieldset></form>';
		}
		
		return $out;
		
	}
	
	private function _renderRow($data,$cycle,$headers,$settings,$runner=0) {
		
		$out = '';
		
		if(empty($data)) {
			return $out;
		}
		
		foreach($data as $row) {
			
			$out.= '<tr'.($cycle =! $cycle?' class="odd"':'').'>';
			
			foreach($headers as $index=>$header) {		
				$out.= sprintf(
					"<td>%s%s</td>"
					,$index?'':str_repeat('&nbsp;',$runner * 3) // probably best to use CSS padding or margins instead - indent each level
					,$header['mutation'] === null?$row[$header['column']]:call_user_func_array($header['mutation'],array($row[$header['column']],$row,$header))
				);
			}
			
			$out.= '</tr>';
			
			// print children
			if($settings['tree'] === true) {
				$out.= $this->_renderRow(
					isset($row[$settings['child_key']])?$row[$settings['child_key']]:array()
					,$cycle
					,$headers
					,$settings
					,($runner + 1)
				);
			}
			
		}
		
		return $out;
		
	}
	
}
?>