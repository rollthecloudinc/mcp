<script type="text/javascript">
	$(document).ready(function() {
		var current = 'general';
		$('#menu-link-tabs a').click(function(evt) {

			evt.preventDefault();

			if( current !== null && current === evt.target.className) {
				return true;
			}

			$('form fieldset.' + evt.target.className).css({display:'block'});

			if(current !== null) {
				$('form fieldset.' + current).css({display:'none'});
			}

			current = evt.target.className;
			
		});
	});
</script>

<form name="<?php echo $name;?>" action="<?php echo $action;?>" id="<?php echo $name;?>" method="<?php echo $method;?>">

	<fieldset>
		<legend><?php echo $legend?></legend>
		
		<ul id="menu-link-tabs">
			<li style="display: inline-block;"><a class="general" href="#">General</a></li>
			<li style="display: inline-block;"><a class="target" href="#">Target</a></li>
			<li style="display: inline-block;"><a class="header" href="#">Header</a></li>
			<li style="display: inline-block;"><a class="footer" href="#">Footer</a></li>
			<li style="display: inline-block;"><a class="datasource" href="#">Datasource</a></li>
		</ul>
		
		<fieldset class="general">
			<legend>General</legend>
			<ul>
				<li><?php echo $display_title; ?></li>
				<li><?php echo $path; ?></li>
				<li><?php echo $browser_title; ?></li>
				<li><?php echo $page_title; ?></li>
			</ul>
			
			<div><?php echo $parent_id; ?></div>
		</fieldset>
		
		<fieldset class="target" style="display: none;">
			<legend>Target</legend>

			<div><?php echo $target; ?></div>
			
			<ul>
				<li><?php echo $absolute_url; ?></li>
			</ul>
			
			<ul>
				<li><?php echo $mod_path; ?></li>
				<li><?php echo $mod_tpl; ?></li>
				<li><?php echo $mod_args; ?></li>
			</ul>
                        
                        <?php if(isset($layout_vars)) {
                            echo $ui->draw('Common.Form.Form',$layout_vars);
                        } ?>

		</fieldset>
		
		<fieldset class="header" style="display: none;">
			<legend>Header</legend>
			<div><?php echo $content_header; ?></div>
			<div><?php echo $content_header_type; ?></div>
		</fieldset>
		
		<fieldset class="footer" style="display: none;">
			<legend>Footer</legend>
			<div><?php echo $content_footer; ?></div>
			<div><?php echo $content_footer_type; ?></div>
		</fieldset>
		
		<fieldset class="datasource" style="display: none;">
			<legend>Datasource</legend>
			<div><?php echo $datasource; ?></div>
			<ul>
				<li><?php echo $datasource_dao; ?></li>
				<li><?php echo $datasource_method; ?></li>
				<li><?php echo $datasource_args; ?></li>
			</ul>
		</fieldset>
		
		<?php echo $submit; ?>
		
	</fieldset>

</form>