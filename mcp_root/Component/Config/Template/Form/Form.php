<?php 
echo '<p class="message note"><strong>Note:</strong>&nbsp;Blank fields fallback to global defaults</p>';
/*
* Config update success/failure message
*/
if($success === true) {
	echo '<p class="message success">Config updated</p>';
} else if($success === false) {
	echo '<p class="message error">Error updating config</p>';
}
?>
<?php echo $this->ui('Common.Form.Form',array(
	'name'=>$name
	,'action'=>$action
	,'config'=>$config
	,'values'=>$values
	,'errors'=>$errors
	,'legend'=>$legend
	,'idbase'=>'site-config-'
)); ?>