<form name="<?php echo $name;?>" action="<?php echo $action;?>" id="<?php echo $name;?>" method="<?php echo $method;?>">

	<fieldset>
		<legend><?php echo $legend?></legend>
		
		<ul class="tabs">
			<li><a href="#link-general">General</a></li>
			<li><a href="#link-target">Target</a></li>
			<li><a href="#link-header">Header</a></li>
			<li><a href="#link-footer">Footer</a></li>
			<li><a href="#link-datasource">Datasource</a></li>
                </ul>
		
		<fieldset id="link-target" class="tab">
			<legend>Target</legend>

			<?php echo $target; ?>
			<?php echo $absolute_url; ?>
			
                        <?php echo $mod_path; ?>
                        <?php echo $mod_tpl; ?>
                        <?php echo $mod_args; ?>
                        
                        <?php if(isset($layout_vars)) {
                            echo $ui->draw('Common.Form.Form',$layout_vars);
                        } ?>

		</fieldset>
		
		<fieldset id="link-header" class="tab">
			<legend>Header</legend>
			<?php echo $content_header; ?>
			<?php echo $content_header_type; ?>
		</fieldset>
		
		<fieldset id="link-footer" class="tab">
			<legend>Footer</legend>
			<?php echo $content_footer; ?>
			<?php echo $content_footer_type; ?>
		</fieldset>
		
		<fieldset id="link-datasource" class="tab">
			<legend>Datasource</legend>
			<?php echo $datasource; ?>
                        <?php echo $datasource_dao; ?>
                        <?php echo $datasource_method; ?>
                        <?php echo $datasource_args; ?>
		</fieldset>
                
                <fieldset id="link-general" class="last tab">
			<legend>General</legend>
                        <?php echo $display_title; ?>
                        <?php echo $path; ?>
                        <?php echo $browser_title; ?>
                        <?php echo $page_title; ?>			
			<?php echo $parent_id; ?>
		</fieldset>
		
		<?php echo $submit; ?>
		
	</fieldset>

</form>