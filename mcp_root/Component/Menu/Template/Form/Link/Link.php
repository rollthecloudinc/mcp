<script type="text/javascript">
$(document).ready(function() {

	$('input[id="menu-link-datasource"]').change( (function(evt) {

		var checked = false;
		$('input[id="menu-link-datasource"]:checked').each(function() {
			checked = true;
			return false;
		});

		$('input[id^="menu-link-datasource-"]').each(function() {
			this.disabled = checked?"":"disabled";
			checked?$(this).closest('li').show():$(this).closest('li').hide();
		});

		return arguments.callee;
		
	})() );

	$('input[id="menu-link-target"]').change( (function() {

		return arguments.callee;
		
	})() );
	
});
</script>

<?php echo $this->ui('Common.Form.Form',array(
	'name'=>$name
	,'action'=>$action
	,'config'=>$config
	,'values'=>$values
	,'errors'=>$errors
	,'legend'=>$legend
	,'idbase'=>'menu-link-'
	,'image_path'=>$this->_objMCP->getBaseUrl(false).'/img.php/%s/w/75'
	
	// just for testing - make a node type column - specific form overrride
	,'layout'=>$layout
        ,'layout_vars'=>$mod_form
)); ?>

<?php 

/*
* Form for configuration of module options when linking to a module. 
*/
echo $module_form; 

?>