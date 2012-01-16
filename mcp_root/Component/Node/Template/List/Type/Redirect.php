<?php 
/*
* Back link 
*/
/*echo $this->ui('Common.Field.Link',array(
	'label'=>'Back To Classifications'
	,'url'=>$back_link
));*/

$this->_objMCP->addBreadcrumb(array('label'=>'Classifications','url'=>$back_link));

/*
* Redirect content 
*/
echo $TPL_REDIRECT_CONTENT; 
?>