<?php 

// tab menu
echo $this->ui('Common.Listing.Tree',$tabs);

// form
echo $this->ui('Common.Form.Form',array(
	'name'=>$name
	,'action'=>$action
	,'config'=>$config
	,'values'=>$values
	,'errors'=>$errors
	,'legend'=>$legend
	,'idbase'=>'role-'
	,'image_path'=>$this->_objMCP->getBaseUrl(false).'/img.php/%s/w/75'
)); 

?>
