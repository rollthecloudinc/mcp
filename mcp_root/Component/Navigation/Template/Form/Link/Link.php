<form id="<?php echo $name; ?>" name="<?php echo $name; ?>" action="<?php echo $action; ?>" method="<?php echo $method;?>">
	<fieldset>
		<legend>Menu Link</legend>
		<fieldset id="navigation-link-display-information">
			<legend>Display</legend>
			<ul>
				<li class="navigation-links-title">
					<label for="navigation-links-title"><?php echo $config['link_title']['label']; ?><?php if($config['link_title']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
					<input type="text" name="<?php echo $name; ?>[link_title]" value="<?php echo $values['link_title']; ?>" maxlength="<?php echo $config['link_title']['max']; ?>" id="navigation-links-title"<?php $this->close(); ?>
					<?php if(isset($errors['link_title'])) printf('<p>%s</p>',$errors['link_title']); ?>
				</li>
				<li class="navigation-links-browser-title">
					<label for="navigation-links-browser-title"><?php echo $config['browser_title']['label']; ?><?php if($config['browser_title']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
					<input type="text" name="<?php echo $name; ?>[browser_title]" value="<?php echo $values['browser_title']; ?>" maxlength="<?php echo $config['browser_title']['max']; ?>" id="navigation-links-browser-title"<?php $this->close(); ?>
					<?php if(isset($errors['browser_title'])) printf('<p>%s</p>',$errors['browser_title']); ?>
				</li>
				<li class="navigation-links-page-heading">
					<label for="navigation-links-page-heading"><?php echo $config['page_heading']['label']; ?><?php if($config['page_heading']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
					<input type="text" name="<?php echo $name; ?>[page_heading]" value="<?php echo $values['page_heading']; ?>" maxlength="<?php echo $config['page_heading']['max']; ?>" id="navigation-links-page-heading"<?php $this->close(); ?>
					<?php if(isset($errors['page_heading'])) printf('<p>%s</p>',$errors['page_heading']); ?>
				</li>
				<li class="navigation-links-target-window">
					<label for="navigation-links-target-window"><?php echo $config['target_window']['label']; ?></label>
					<select name="<?php echo $name; ?>[target_window]" id="navigation-links-target-window">
						<?php echo $this->html_options($target_windows); ?>
					</select>
					<?php if(isset($errors['target_window'])) printf('<p>%s</p>',$errors['target_window']); ?>
				</li>
			</ul>
		</fieldset>
		<fieldset id="navigation-links-parent-information">
			<legend>Parent</legend>
			
			<?php if(isset($errors['parent_id'])) { ?><p>Please choose a parent for the link</p><?php } ?>
			
			<?php 
				/*
				* Print menu hierachies and select current links parent 
				*/
				echo $objModule->printParentFieldset($values['parent_id']); 
				?>
		</fieldset>
		<fieldset>
			<legend>Content</legend>
			<fieldset>
				<legend>External Link</legend>
				<ul>
					<li class="navigation-links-url">
						<label for="navigation-links-url"><?php echo $config['link_url']['label']; ?><?php if($config['link_url']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
						<input type="text" name="<?php echo $name; ?>[link_url]" value="<?php echo $values['link_url']; ?>" maxlength="<?php echo $config['link_url']['max']; ?>" id="navigation-links-url"<?php $this->close(); ?>
						<?php if(isset($errors['link_url'])) printf('<p>%s</p>',$errors['link_url']); ?>
					</li>
				</ul>
			</fieldset>
			<fieldset>
				<legend>Module/Component</legend>
				<ul>
					<li class="navigation-links-sites-internal-url">
						<label for="navigation-links-sites-internal-url"><?php echo $config['sites_internal_url']['label']; ?><?php if($config['sites_internal_url']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
						<input type="text" name="<?php echo $name; ?>[sites_internal_url]" value="<?php echo $values['sites_internal_url']; ?>" maxlength="<?php echo $config['sites_internal_url']['max']; ?>" id="navigation-links-sites-internal-url"<?php $this->close(); ?>
						<?php if(isset($errors['sites_internal_url'])) printf('<p>%s</p>',$errors['sites_internal_url']); ?>
					</li>
					<li class="navigation-links-target-module">
						<label for="navigation-links-target-module"><?php echo $config['target_module']['label']; ?><?php if($config['target_module']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
						<input type="text" name="<?php echo $name; ?>[target_module]" value="<?php echo $values['target_module']; ?>" maxlength="<?php echo $config['target_module']['max']; ?>" id="navigation-links-target-module"<?php $this->close(); ?>
						<?php if(isset($errors['target_module'])) printf('<p>%s</p>',$errors['target_module']); ?>
					</li>
					<li class="navigation-links-target-template">
						<label for="navigation-links-target-template"><?php echo $config['target_template']['label']; ?><?php if($config['target_template']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
						<input type="text" name="<?php echo $name; ?>[target_template]" value="<?php echo $values['target_template']; ?>" maxlength="<?php echo $config['target_template']['max']; ?>" id="navigation-links-target-template"<?php $this->close(); ?>
						<?php if(isset($errors['target_template'])) printf('<p>%s</p>',$errors['target_template']); ?>
					</li>
				</ul>	
				<fieldset>
					<legend>Module Args</legend>
					<?php if(!empty($values['target_module_args'])) { ?>
					<ol class="navigation-links-target-module-args">
						<?php foreach($values['target_module_args'] as $index=>$arg) { ?>
						<li class="navigation-links-target-module-args-<?php echo $index; ?>">
							<label for="navigation-links-target-module-args-<?php echo $index; ?>">Arg <?php echo ($index+1);?></label>
							<input type="text" name="<?php echo $name; ?>[target_module_args][]" value="<?php echo $arg; ?>" maxlength="255" name="navigation-links-target-module-args-<?php echo $index; ?>"<?php $this->close(); ?>
						</li>
						<?php } ?>
					</ol>
					<?php } else { ?>
						<p>No Module Arguments Available</p>
					<?php } ?>
				</fieldset>
				<?php echo $MODULE_CONFIG_TPL; ?>	
			</fieldset>
			<fieldset>
				<legend>Custom Page</legend>
				<ul class="navigation-links-body-content">
					<li class="navigation-links-body-content-type">
						<label for="navigation-links-body-content-type""><?php echo $config['body_content_type']['label']; ?><?php if($config['body_content_type']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
						<select name="<?php echo $name; ?>[body_content_type]" id="navigation-links-body-content-type">
							<?php echo $this->html_options($body_content_types); ?>
						</select>
					</li>
					<li class="navigation-links-body-content">
						<label for="navigation-links-body-content"><?php echo $config['body_content']['label']; ?><?php if($config['body_content']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
						<textarea name="<?php echo $name; ?>[body_content]" id="navigation-links-body-content"><?php echo $values['body_content']; ?></textarea>
					</li>
				</ul>	
			</fieldset>
		</fieldset>	
		<fieldset>
			<legend>Header &amp; Footer</legend>
			<ul class="navigation-links-header-content">
				<li class="navigation-links-header-content-type">
					<label for="navigation-links-header-content-type""><?php echo $config['header_content_type']['label']; ?><?php if($config['header_content_type']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
					<select name="<?php echo $name; ?>[header_content_type]" id="navigation-links-header-content-type">
						<?php echo $this->html_options($header_content_types); ?>
					</select>
				</li>
				<li class="navigation-links-header-content">
					<label for="navigation-links-header-content"><?php echo $config['header_content']['label']; ?><?php if($config['header_content']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
					<textarea name="<?php echo $name; ?>[header_content]" id="navigation-links-header-content"><?php echo $values['header_content']; ?></textarea>
				</li>
			</ul>
			<ul class="navigation-links-footer-content-type">
				<li class="navigation-links-footer-content-type">
					<label for="navigation-links-footer-content-type""><?php echo $config['footer_content_type']['label']; ?><?php if($config['footer_content_type']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
					<select name="<?php echo $name; ?>[footer_content_type]" id="navigation-links-footer-content-type">
						<?php echo $this->html_options($footer_content_types); ?>
					</select>
				</li>
				<li class="navigation-links-footer-content">
					<label for="navigation-links-footer-content"><?php echo $config['footer_content']['label']; ?><?php if($config['footer_content']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
					<textarea name="<?php echo $name; ?>[footer_content]" id="navigation-links-footer-content"><?php echo $values['footer_content']; ?></textarea>
				</li>
			</ul>
		</fieldset>
		<fieldset>
			<legend>DataSource</legend>
			<fieldset>
				<legend>Query</legend>
				<ul>
					<li class="navigation-links-datasource-query">
						<label for="navigation-links-datasource-query"><?php echo $config['datasource_query']['label']; ?><?php if($config['datasource_query']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
						<textarea name="<?php echo $name; ?>[datasource_query]" id="navigation-links-datasource_query"><?php echo $values['datasource_query']; ?></textarea>
					</li>
				</ul>
			</fieldset>
			<fieldset>
				<legend>DAO</legend>
				<ul>
					<li class="navigation-links-datasource-dao">
						<label for="navigation-links-datasource-dao"><?php echo $config['datasource_dao']['label']; ?><?php if($config['datasource_dao']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
						<input type="text" name="<?php echo $name; ?>[datasource_dao]" value="<?php echo $values['datasource_dao']; ?>" maxlength="<?php echo $config['datasource_dao']['max']; ?>" id="navigation-links-datasource-dao"<?php $this->close(); ?>
						<?php if(isset($errors['datasource_dao'])) printf('<p>%s</p>',$errors['datasource_dao']); ?>
					</li>
					<li class="navigation-links-datasource-dao-method">
					
						<label for="navigation-links-datasource-dao-method"><?php echo $config['datasource_dao_method']['label']; ?><?php if($config['datasource_dao_method']['required'] == 'Y') { ?>&nbsp;<span class="required">*</span><?php } ?></label>
						<input type="text" name="<?php echo $name; ?>[datasource_dao_method]" value="<?php echo $values['datasource_dao_method']; ?>" maxlength="<?php echo $config['datasource_dao_method']['max']; ?>" id="navigation-links-datasource-dao-method"<?php $this->close(); ?>
						<?php if(isset($errors['datasource_dao_method'])) printf('<p>%s</p>',$errors['datasource_dao_method']); ?>
						
						<ol class="navigation-links-datasource-dao-args">
							<?php foreach($values['datasource_dao_args'] as $index=>$dao_arg) { ?>
							<li class="navigation-links-datasource-dao-args-<?php echo $index; ?>">
								<label for="navigation-links-datasource-dao-args-<?php echo $index; ?>">Arg <?php echo ($index+1); ?></label>
								<input type="text" name="<?php echo $name; ?>[datasource_dao_args][]" value="<?php echo $dao_arg; ?>" maxlength="255" id="navigation-links-datasource-dao-args-<?php echo $index; ?>"<?php $this->close(); ?>
							</li>
							<?php } ?>
						</ol>
						
					</li>
				</ul>
			</fieldset>
		</fieldset>
		
		<input type="submit" name="<?php echo $name; ?>[submit]" value="Save"<?php $this->close(); ?>
		
	</fieldset>
</form>