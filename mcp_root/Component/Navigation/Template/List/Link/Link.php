<?php echo $BREADCRUMB_TPL; ?>
<form name="<?php echo $name; ?>" id="<?php echo $name; ?>" action="<?php echo "$action#$name"; ?>" method="<?php echo $method; ?>">
	<fieldset>
		<legend><?php echo $legend; ?> Links</legend>
		<table cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>&nbsp;</th>
					<th>Title</th>
					<th>Browser</th>
					<th>Page</th>
					<th>Window</th>
					<th colspan="7">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php if(!empty($links)) {
					$cycle = false;
					foreach($links as $index=>$link) {
						printf(
							'<tr%s>
								<td>%u</td>
								
								<td><a href="%s/%9$s">%s</a></td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								
								<td><input type="submit" name="%8$s[action][up][%9$s]" value="Up"></td>
								<td><input type="submit" name="%8$s[action][down][%9$s]" value="Down"></td>
								
								<td><input type="submit" name="%8$s[action][delete][%9$s]" value="Delete"'.($link['datasources_id'] === null?'':' disabled=""disabled').'></td>
								<td><input type="submit" name="%8$s[action][remove][%9$s]" value="Remove"'.($link['datasources_id'] === null?'':' disabled=""disabled').'></td>
								
								<td>
									<select name="%8$s[parent_id]"%12$s>
					  				     %11$s
									</select>
								</td>
								
								<td>'.($link['allow_edit']?'<a href="%10$s/Edit-Link/%9$s/">Edit</a>':'<span style="display:none;">%10$s%9$s</span>Edit').'</td>
								<td>'.($link['allow_add']?'<a href="%10$s/Edit-Link/Link/%9$s/">+</a>':'<span style="display:none;">%10$s%9$s</span>+').'</td>
								
							 </tr>'
							 ,$cycle =! $cycle?' class="odd"':''
							 ,($index+1)
							 ,$edit_path
							 ,$this->out($link['link_title'])
							 ,$link['browser_title'] === null?'--':$this->out($link['browser_title'])
							 ,$link['page_heading'] === null?'--':$this->out($link['page_heading'])
							 ,$this->out($link['target_window'])
							 ,$name
							 ,$this->out($link['navigation_links_id'])
							 ,$action
							 ,$this->html_options($ancestory)
							 ,strcmp('nav',$link['parent_type']) == 0?' disabled="disabled"':''
						);
					}
				} else { ?>
					<tr class="empty">
						<td colspan="12">No Links Available</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</fieldset>
</form>