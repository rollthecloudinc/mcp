<?php
echo $this->ui('Common.Listing.Table',array(
	'data'=>$terms
	,'tree'=>true // enable tree support
	,'child_key'=>'terms'
	,'headers'=>$headers
	,'form'=>true
	,'form_legend'=>$header
	,'form_action'=>$frm_action
	,'form_method'=>$frm_method
	,'form_name'=>$frm_name
));
?>