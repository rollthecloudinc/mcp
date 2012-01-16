<?php 
/*
* Show back link 
*/
/*echo $this->ui('Common.Field.Link',array(
	'url'=>$back_link
	,'label'=>'Back To Menus'
));*/

$this->_objMCP->addBreadcrumb(array('url'=>$back_link,'label'=>'Menus'));

/*
* Use terms template 
*/
echo $REDIRECT_TPL;
?>