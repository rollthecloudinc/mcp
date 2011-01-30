<?php
/*
* Build out table of view type fields
*/
echo $this->ui('Common.Listing.Table',array(
	'data'=>$fields
	,'headers'=>$headers
	,'form'=>false
));
?>