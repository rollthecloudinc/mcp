<?php 
echo '<div class="alert-message info"><p class="message note"><strong>Note:</strong>&nbsp;Blank fields fallback to global defaults</p></div>';
echo $this->ui('Common.Form.Form',array(
	'name'=>$name
	,'action'=>$action
	,'config'=>$config
	,'values'=>$values
	,'errors'=>$errors
	,'legend'=>$legend
	,'idbase'=>'site-config-'
	,'image_path'=>$this->_objMCP->getBaseUrl(false).'/img.php/%s/w/75'
)); ?>