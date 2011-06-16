<?php 
echo $this->ui('Common.Listing.Tree',array(
	'data'=>$links
	,'value_key'=>'display_title'
	,'child_key'=>'menu_links'
	,'list_element'=>'ul'
	,'mutation'=>$mutation
)); 

echo $this->ui('Common.Listing.Table',array(
	'data'=>$links
	,'tree'=>true
	,'child_key'=>'menu_links'
	,'headers'=>array(
		array(
			'label'=>'Title'
			,'column'=>'display_title'
			,'mutation'=>null
		)
		,array(
			'label'=>'&nbsp;'
			,'column'=>'menu_links_id'
			,'mutation'=>function($value,$row) {
				return $value;
			}
		)
		,array(
			'label'=>'&nbsp;'
			,'column'=>'menu_link_id'
			,'mutation'=>function($value,$row) {
				return 'delete';
			}
		)
	)
)); 
?>