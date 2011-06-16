<?php
echo $this->ui('Common.Listing.Tree',array(
	'data'=>$links
	,'value_key'=>'display_title'
	,'child_key'=>'menu_links'
	,'list_element'=>'ul'
	,'mutation'=>$mutation
)); 
?>