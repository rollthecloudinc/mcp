<?php
/*
* Dump pagination
*/
if($display_pagination == 1) echo $PAGINATION_TPL;

/*
* Create node of type link 
*/
if($allow_node_create) {
	echo $this->ui('Common.Field.Link',array(
		'url'=>$create_link
		,'label'=>"Create $create_label"
	));
}

/*
* Build out table of node content entries
*/
echo $this->ui('Common.Listing.Table',array(
	'data'=>$nodes
	,'headers'=>$headers
	,'form'=>true
	,'form_legend'=>$header
	,'form_action'=>$frm_action
	,'form_method'=>$frm_method
	,'form_name'=>$frm_name
));
?>