<?php
/*
* Build out table of terms
*/
echo $this->ui('Common.Listing.Table',array(
	'data'=>$terms
	,'headers'=>$headers
	,'form'=>true
	,'form_legend'=>'Terms'
	,'form_action'=>$frm_action
	,'form_method'=>$frm_method
	,'form_name'=>$frm_name
));
?>