<?php

/*
* Create field 
*/
if($allow_create) {
	echo $this->ui('Common.Field.Link',array(
		'url'=>$create_link
		,'label'=>'New Field'
	));
}

/*
* Build out table of fields 
*/
echo $this->ui('Common.Listing.Table',array(
	'data'=>$fields
	,'headers'=>$headers
	,'form'=>true
	,'form_legend'=>'Fields'
));
?>