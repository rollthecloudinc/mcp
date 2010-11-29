<?php

/*
* Back link 
*/
echo $this->ui('Common.Field.Link',array(
	'label'=>'Back To Site'
	,'url'=>$back_link
));

/*
* Dump the redirect content 
*/
echo $TPL_REDIRECT_CONTENT;
?>