<?php
$this->_objMCP->addJs(array(
    'inline'=>'$(document).ready(function() {
	$("#node-node-content,#node-intro-content").ckeditor();
    });'
));
?>

<?php echo $this->ui('Common.Form.Form',array(
	'name'=>$name
	,'action'=>$action
	,'config'=>$config
	,'values'=>$values
	,'errors'=>$errors
	,'legend'=>$legend
	,'idbase'=>'node-'
	,'image_path'=>$this->_objMCP->getBaseUrl(false).'/img.php/%s/w/75'
	
	// just for testing - make a node type column - specific form overrride
	,'layout'=>$layout
	
	,'recaptcha'=>$recaptcha
)); ?>
