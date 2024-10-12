<?php echo $this->ui('Common.Form.Form',array(
	 'name'=>$name
	,'action'=>$action
	,'config'=>$config
	,'values'=>$values
	,'errors'=>$errors
	,'legend'=>'Login'
	,'idbase'=>'login-'.$instance_num.'-'
	,'image_path'=>$this->_objMCP->getBaseUrl(false).'/img.php/%s/w/75'
)); ?>