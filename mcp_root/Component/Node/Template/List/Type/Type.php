<?php
/*
* Show pagination 
*/
echo $PAGINATION_TPL;

/*
* Create new node type link 
*/
if($allow_node_type_create) {
	echo $this->ui('Common.Field.Link',array(
		'label'=>'Create Classification'
		,'url'=>$create_link
	));
}

/*
* Build out table of node types
*/
echo $this->ui('Common.Listing.Table',array(
	'data'=>$node_types
	,'headers'=>$headers
	,'form'=>true
	,'form_legend'=>'Content'
	,'form_action'=>$frm_action
	,'form_method'=>$frm_method
	,'form_name'=>$frm_name
));
?>