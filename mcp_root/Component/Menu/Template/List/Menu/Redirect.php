<?php 
/*
* Show back link 
*/
echo $this->ui('Common.Field.Link',array(
	'url'=>$back_link
	,'label'=>'Back To Menus'
));

/*
* Use terms template 
*/
echo $REDIRECT_TPL;
?>