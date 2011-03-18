<?php
/*
* Dump pagination
*/
echo $PAGINATION_TPL;

/*
* Create role link 
*/
if($allow_create_role) {
	echo $this->ui('Common.Field.Link',array(
		'url'=>$create_link
		,'label'=>'Add Role'
	));
}

/*
* Build out table of roles 
*/
echo $this->ui('Common.Listing.Table',array(
	'data'=>$roles
	,'headers'=>$headers
	,'form'=>true
	,'form_legend'=>'Roles'
	,'form_action'=>$frm_action
	,'form_method'=>$frm_method
	,'form_name'=>$frm_name
));
?>