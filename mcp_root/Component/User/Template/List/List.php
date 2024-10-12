<?php 
/*
* show pagination 
*/
echo $PAGINATION_TPL;

/*
* Link to view fields 
*/
echo $this->ui('Common.Field.Link',array(
	'url'=>$fields_link
	,'label'=>'Fields'
));

/*
* Build out table of users
*/
echo $this->ui('Common.Listing.Table',array(
	'data'=>$users
	,'headers'=>$headers
	,'form'=>true
	,'form_legend'=>'Members'
	,'form_action'=>$frm_action
	,'form_method'=>$frm_method
	,'form_name'=>$frm_name
));
?>