<?php
echo $this->ui('Common.Listing.Table',array(
	'data'=>$terms
	,'tree'=>true // enable tree support
	,'child_key'=>'terms'
	,'headers'=>$headers
));
?>