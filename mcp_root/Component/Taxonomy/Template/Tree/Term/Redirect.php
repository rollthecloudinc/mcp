<?php 
/*
* Back link 
*/
/*echo $this->ui('Common.Field.Link',array(
	'label'=>'Back To Terms'
	,'url'=>$back_link
));*/

$this->_objMCP->addBreadcrumb(array('url'=>$back_link,'label'=>'Terms'));

/*
* Redirect template contents 
*/
echo $TPL_REDIRECT;
?>