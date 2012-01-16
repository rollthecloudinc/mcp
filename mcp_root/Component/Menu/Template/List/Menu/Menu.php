<?php
/*
* Create new menu link 
*/
echo $this->ui('Common.Field.Link',array(
	'url'=>$create_link
	,'label'=>'Create Menu'
        ,'class'=>'menu create'
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
		,'form_action'=>$frm_action
		,'form_method'=>$frm_method
		,'form_name'=>$frm_name
	));

}
?>