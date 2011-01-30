<?php
/*
* Build out table of view types
*/
echo $this->ui('Common.Listing.Table',array(
	'data'=>$types
	,'headers'=>$headers
	,'form'=>false
));
?>