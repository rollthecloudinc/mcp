<?php 
/*
* Back link 
*/
/*echo $this->ui('Common.Field.Link',array(
	'label'=>'Back To View Types'
	,'url'=>$back_link
));*/

$this->_objMCP->addBreadcrumb(array('url'=>$back_link,'label'=>'Schemas'));

/*
* Redirect content 
*/
echo $TPL_REDIRECT_CONTENT; 
?>