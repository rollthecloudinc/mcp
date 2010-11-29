<?php 
/*
* Back link 
*/
echo $this->ui('Common.Field.Link',array(
	'label'=>'Back To Terms'
	,'url'=>$back_link
));

/*
* Redirect template contents 
*/
echo $TPL_REDIRECT;
?>