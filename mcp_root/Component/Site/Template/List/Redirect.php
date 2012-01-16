<?php

/*
* Back link 
*/
/*echo $this->ui('Common.Field.Link',array(
	'label'=>'Back To Site'
	,'url'=>$back_link
));*/

$this->_objMCP->addBreadcrumb(array('url'=>$back_link,'label'=>'Sites'));

/*
* Dump the redirect content 
*/
echo $TPL_REDIRECT_CONTENT;
?>