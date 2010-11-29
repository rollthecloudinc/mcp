<?php 
/*
* Show back link 
*/
echo $this->ui('Common.Field.Link',array(
	'label'=>'Back To Vocabularies'
	,'url'=>$back_link
));

/*
* Use terms template 
*/
echo $REDIRECT_TPL;
?>