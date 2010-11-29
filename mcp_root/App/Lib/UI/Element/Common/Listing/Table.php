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
		
		extract($settings);
		$out = '';	
		
		if($form === true) {
			$out.= sprintf(
				'<form name="%s" action="%s" method="%s"><fieldset><legend>%s</legend>'
				,$form_name
				,$form_action
				,$form_method
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
		$out.= '<tbody>';
		
		if(!empty($data)) {
			foreach($data as $row) {
				$out.= '<tr>';
				foreach($headers as $header) {
					
					$out.= sprintf(
						"<td>%s</td>"
						,$header['mutation'] === null?$row[$header['column']]:call_user_func_array($header['mutation'],array($row[$header['column']],$row))
					);
				}
				$out.= '</tr>';
			}
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
	
}
?>