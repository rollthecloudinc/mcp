<?php
/*
* Create a site link
*/
if($allow_create) {
	echo $this->ui('Common.Field.Link',array(
		'label'=>'Create Site'
		,'url'=>$create_link
	));
}

/*
* Build out table of sites
*/
echo $this->ui('Common.Listing.Table',array(
	'data'=>$sites
	,'headers'=>$headers
	,'form'=>true
	,'form_legend'=>'Sites'
));
?>