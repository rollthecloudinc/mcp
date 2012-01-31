<?php

/*
* Create new link under current menu
*/
if($allow_link_create) {
	echo $this->ui('Common.Field.Link',array(
		'url'=>$create_link
		,'label'=>"Create $create_label"
                ,'class'=>'btn link create'
	));
}

echo $this->ui('Common.Listing.Table',array(
	'data'=>$links
	,'tree'=>true // enable tree support
	,'child_key'=>'menu_links'
	,'headers'=>$headers
));
?> 