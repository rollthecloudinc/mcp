<?php

/*
* Create new link under current menu
*/
if($allow_link_create) {
	echo $this->ui('Common.Field.Link',array(
		'url'=>$create_link
		,'label'=>"Create $create_label"
                ,'class'=>'btn link create'
	));
}

echo $this->ui('Common.Listing.Table',array(
	'data'=>$links
	,'tree'=>true // enable tree support
	,'child_key'=>'menu_links'
	,'headers'=>$headers
	,'form'=>true
	,'form_legend'=>$header
	,'form_action'=>$frm_action
	,'form_method'=>$frm_method
	,'form_name'=>$frm_name
));
?> 