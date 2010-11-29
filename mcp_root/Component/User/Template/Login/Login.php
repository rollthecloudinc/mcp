<form id="login-<?php echo $instance_num; ?>" name="<?php echo $frm_name; ?>" action="<?php echo $frm_action; ?>" method="<?php echo $frm_method; ?>">
	<?php $objModule->paintHeaderMessages(); ?>
	<fieldset>
		<legend>Login</legend>
		<ul>
			<li class="username">
				<label for="login-<?php echo $instance_num; ?>-username"><?php echo $frm_config['username']['label']; ?><?php if($frm_config['username']['required'] === 'Y') { ?><span class="required">*</span><?php } ?></label>
				<input type="text" name="<?php echo $frm_name; ?>[username]" value="<?php echo $frm_values['username']; ?>" maxlength="<?php echo $frm_config['username']['max']; ?>" id="login-<?php echo $instance_num; ?>-username" class="">
				<?php if(isset($frm_errors['username'])) { ?><p><?php echo $frm_errors['username']; ?></p><?php } ?>
			</li>
			<li class="password">
				<label for="login-<?php echo $instance_num; ?>-password"><?php echo $frm_config['password']['label']; ?><?php if($frm_config['password']['required'] === 'Y') { ?><span class="required">*</span><?php } ?></label>
				<input type="password" name="<?php echo $frm_name; ?>[password]" value="<?php echo $frm_values['password']; ?>" maxlength="<?php echo $frm_config['password']['max']; ?>" id="login-<?php echo $instance_num; ?>-password" class="">
				<?php if(isset($frm_errors['password'])) { ?><p><?php echo $frm_errors['password']; ?></p><?php } ?>
			</li>
			<li>
				<input type="checkbox" name="<?php echo $frm_name; ?>[remember]" value="1" id="login-<?php echo $instance_num; ?>-remember"<?php if($frm_values['remember'] == '1') { echo ' checked="checked"'; }?>>
				<label for=""><?php echo $frm_config['remember']['label']; ?></label>
			</li>
			<li class="login">
				<input type="submit" name="<?php echo $frm_name; ?>[login]" value="Login">
			</li>
		</ul>
	</fieldset>
</form>