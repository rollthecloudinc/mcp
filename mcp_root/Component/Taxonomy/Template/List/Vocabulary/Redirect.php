<?php 
/*
* Show back link 
*/
/*echo $this->ui('Common.Field.Link',array(
	'label'=>'Back To Vocabularies'
	,'url'=>$back_link
));*/

$this->_objMCP->addBreadcrumb(array('url'=>$back_link,'label'=>'Vocabularies'));

/*
* Use terms template 
*/
echo $REDIRECT_TPL;
?>