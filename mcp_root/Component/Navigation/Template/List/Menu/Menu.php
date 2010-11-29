<?php
/*
* Create new menu link 
*/
echo $this->ui('Common.Field.Link',array(
	'url'=>$create_link
	,'label'=>'Create Menu'
));

/*
* Build out table of vocabs 
*/
foreach($menus as $site) {

	echo $this->ui('Common.Listing.Table',array(
		'data'=>$site['menus']
		,'headers'=>$headers
		,'form'=>true
		,'form_legend'=>$site['site_name']
	));

}
?>