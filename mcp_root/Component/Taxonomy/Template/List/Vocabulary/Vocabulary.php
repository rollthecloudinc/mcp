<?php
/*
* Dump pagination
*/
echo $PAGINATION_TPL;

/*
* Create vocabulary link 
*/
if($allow_create_vocab) {
	echo $this->ui('Common.Field.Link',array(
		'url'=>$create_link
		,'label'=>'Add Vocabulary'
                ,'class'=>'vocab create'
	));
}

/*
* Build out table of vocabs 
*/
echo $this->ui('Common.Listing.Table',array(
	'data'=>$vocabularies
	,'headers'=>$headers
	,'form'=>true
	,'form_legend'=>'Vocabularies'
	,'form_action'=>$frm_action
	,'form_method'=>$frm_method
	,'form_name'=>$frm_name
));
?>