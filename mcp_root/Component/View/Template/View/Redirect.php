<?php 
/*
* Back to content entries link 
*/
/*echo $this->ui('Common.Field.Link',array(
	'url'=>$back_link
	,'label'=>$back_label
));*/

$this->_objMCP->addBreadcrumb(array('url'=>$back_link,'label'=>$back_label));

/*
* pagination controls 
*/
echo $pager;

/*
* Dump the redirect content 
*/
echo $TPL_REDIRECT_CONTENT;
?>