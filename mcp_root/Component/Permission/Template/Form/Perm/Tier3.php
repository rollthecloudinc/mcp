<?php
echo $PAGINATION_TPL;
echo $this->ui('Common.Listing.Table',array(
	 'data'=>$items
	,'headers'=>$headers
	,'form'=>true
	,'form_legend'=>$legend
	,'form_action'=>$action
	,'form_method'=>$method
	,'form_name'=>$name
        ,'form_submit'=>true
));
?>
