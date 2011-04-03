<form action="" method="POST">
	<fieldset>
		<legend>View</legend>
	
<p>
Selecting the content to view is required to resolve dynamic fields properly. It will
not be possible to view multiple items unless creating unions between views.
</p>

<?php /*
<select>
	<option value="">--</option>
	<option value="users">Users</option>
	<option value="sites">Sites</option>
	<option value="config">Config</option>
	
	<!-- All menus -->
	<option value="menus">Menus</option>
	
	<!-- All vocabularies -->
	<option value="vocabularies">Vocabularies</option>
	
	<!-- All node types -->
	<option value="nodetypes">Node Types</option>
	
	<!-- terms in vocabulary 89 -->
	<option value="terms:89">Categories</option>
	
	<!-- nodes of node type 67 -->
	<option value="nodes:67">Products</option>
	
	<!-- All links in menu 67  -->
	<option value="links:67">Main Menu</option>
	
</select>
*/ ?>

<?php 
/*
* Type can not be changed after it has been created or for child displays - type
* must be constant. 
*/
echo $this->ui('Common.Form.Select',array(
	'name'=>'frmView[type]'
	,'id'=>'view-type'
	,'data'=>array('values'=>$config['type']['values'])
	,'value'=>$config['type']['value']
	,'disabled'=>true
)); 
?>

<?php /*<p>--------------------------------------------------------------------------</p>

<p>Once the type has been determined the available columns to start with
can be determined. So given a selection on the Products there will
be a look-up on all available columns and a select menu will be generated. The
menu will include dynamic fields available listed as if they are actually
part of the items concrete definition.</p>*/ ?>

<?php
/*
<select>
	<option>--</option>
	<option value="title">Title</option>
	<option value="subtitle">Subtitle</option>
	<option value="body">Body</option>
	<option value="creator">Creator</option>
	<option value="site">Site</option>
	
	<!-- example of dynamic fields proposal -->
	<option value="field/manufacturer">Manufacturer</option>
	<option value="field/product_images">Images</option>
	<option value="field/sale_price">Sale Price</option>
	<option value="field/msrp">MSRP</option>
	<option value="field/cost_price">Cost Price</option>
	
</select>*/
?>

<?php 
/*echo $this->ui('Common.Form.Select',array(
	'name'=>'frmView[fields][0][field][]'
	,'id'=>'view-field-0'
	,'data'=>array('values'=>$fields)
	,'value'=>''
	,'value_key'=>'path'
	,'label_key'=>'label'
)); */
?>

<?php /*<p>Ideally JavaScript is going to be used to bring up the next option
list if available. For example, when dealing with a field that is relation - references
another entity/table another select menu will be generated to possibly refine
the selection on that table. Here is an example of what this might look like
after selecting manufacturer. In the case where manufacturer is a 1:1 relation
that references a Term. It seems like in most cases when a relation exists a atomic
column selection will be required.</p>*/ ?>

<?php
/*
<select name="frmView[fields][0][]">
	<option value="field/manufacturer">Manufacturer</option>
</select>

<select name="frmView[fields][0][]">
	<option>--</option>
	<option value="system_name">System Name</option>
	<option value="human_name">Human Name</option>
	<option value="site">Site</option>
	<option value="creator">Creator</option>
	
	<!-- parent would be a reference to the parent term or vocabulary if at root level -->
	<option value="parent">Parent</option>
	
	<!-- vocabularu reference - rgearldess of depth -->
	<option value="vocabulary">Vocabulary</option>
</select>*/
?>


	<?php 
	// View arguments -------------------------------------------------------------------------------------------
	?>

	<fieldset>
		<legend>Arguments</legend>
			
		<?php foreach( $config['arguments'] as &$argument) { ?>
			<fieldset>
		
				<input type="hidden" value="90" name="frmView[arguments][0][id]" disabled="disabled">
				
				<?php
				// <input type="text" value="Search String 1" disabled="disabled" name="frmView[arguments][0][name]">
				echo $this->ui('Common.Form.Input',array(
					'value'=>$argument['human_name']
					,'name'=>''
					,'type'=>'text'
				));	
				?>
				
				<?php
				// system name that may not be changed after creation
				// <input type="text" value="Search String 1" disabled="disabled" name="frmView[arguments][0][name]">
				echo $this->ui('Common.Form.Input',array(
					'value'=>$argument['system_name']
					,'name'=>''
					,'type'=>'text'
					,'disabled'=>true
				));	
				?>
				
				<?php
				/*<select disabled="disabled" name="frmView[arguments][0][location]">
					<option>Args</option> <!-- arguments from page, relative to current page -->
					<option selected="selected">POST</option>
					<option>GET</option>
					<option>Function</option>
					<option>DAO</option>
					<option>PHP</option>
				</select>*/
				echo $this->ui('Common.Form.Select',array(
					'name'=>''
					,'id'=>''
					// Values will be returned via a DAO mehod inside View
					,'data'=>array('values'=>array(
						 array('value'=>'','label'=>'--')
						,array('value'=>'post','label'=>'$_POST')
						,array('value'=>'get','label'=>'$_GET')
						,array('value'=>'request','label'=>'$_REQUEST')
						,array('value'=>'int','label'=>'Number')
						,array('value'=>'text','label'=>'Text')
						,array('value'=>'bool','label'=>'True/False')
						,array('value'=>'float','label'=>'Float')
						,array('value'=>'global_arg','label'=>'Global Arg')
						,array('value'=>'module_arg','label'=>'Module Arg')
						,array('value'=>'dao','label'=>'DAO')
						,array('value'=>'function','label'=>'Function')
						,array('value'=>'class','label'=>'Method')
						,array('value'=>'view','label'=>'View')
						// add cookie, server, user pref, config ref?
					))
					,'value'=>$argument['context']
				)); 
				?>
				
				<?php
				// <input type="text" value="search_string" disabled="disabled" name="frmView[arguments][0][value]">
				echo $this->ui('Common.Form.Input',array(
					'value'=>$argument['value']
					,'name'=>''
					,'type'=>'text'
				));	
				?>
				
				<input type="submit" value="-" disabled="disabled" style="float: right;margin-left: 1em;" name="frmView[action][remove][arguments][0]">
				<input type="submit" value="Override" style="float: right;" name="frmView[action][override][arguments][0]">
			
			</fieldset>
		<?php } ?>
			
	</fieldset>


	<?php 
	// View fields, filters and sorting ------------------------------------------------------------------------
	?>
	<?php foreach(array('fields','filters','sorting') as $type) { ?>
	<fieldset>
		<legend><?php echo ucwords($type); ?></legend>
	
			<p>Fields that will be shown to the end-user.</p>

			<?php if(isset($config[$type]) && !empty($config[$type])) { ?>
				<?php foreach($config[$type] as $index=>$field) { ?>
					<fieldset>
		
						<!--  <input type="submit" value="-" disabled="disabled" style="float: right;margin-left: 1em;"> -->
						<!--  <input type="submit" value="Override" style="float: right;"> -->	
						
						<?php 
							echo $this->ui('Common.Form.Submit',array(
								'name'=>''
								,'label'=>'-'
								,'disabled'=>$field['override']
							));
							echo $this->ui('Common.Form.Submit',array(
								'name'=>''
								,'label'=>$field['override']?'Use Default':'Override'
								,'disabled'=>!$field['override']
							));
						?>
						
						<ul>
							<?php foreach($field['paths'] as $path) { ?>
								<?php if(empty($path['values'])) break; ?>
							
								<li style="display: inline-block">
									<?php 
										echo $this->ui('Common.Form.Select',array(
											'name'=>"frmView[fields][$index][field][]"
											,'id'=>'view-field-0'
											,'data'=>array('values'=>$path['values'])
											,'value'=>$path['value']
										)); 
									?>
								</li>
							<?php } ?>
							
							<?php
							/*<li style="display: inline-block">
								<input type="checkbox" checked="checked" disabled="disabled" value="1" name="<?php echo "frmView[fields][$index][sortable]"; ?>">
								<label for="">Sortable</label>
							</li>
							<li style="display: inline-block">
								<input type="checkbox" checked="checked" disabled="disabled" value="1" name="<?php echo "frmView[fields][$index][editable]"; ?>">
								<label for="">Editable</label>
							</li> */ 
							?>
							<?php 
							// extra field controls - sortable and editable at this time
							// need a way to control checking for items that have not been overriden but are sortable
							// and editable via parent display field.
							if(strcmp('fields',$type) === 0) {
								
								// sortable checkbox
								echo '<li style="display: inline-block">';
								echo $this->ui('Common.Form.Checkbox',array(
									'name'=>''
									,'id'=>''
									,'disabled'=>!$field['sortable']
								));	
								echo $this->ui('Common.Form.Label',array(
									'for'=>''
									,'label'=>'Sortable'
								));
								echo '</li>';
								
								// --------------------------------------------------------
								
								// editable checkbox
								echo '<li style="display: inline-block">';
								echo $this->ui('Common.Form.Checkbox',array(
									'name'=>''
									,'id'=>''
									,'disabled'=>!$field['editable']
								));	
								echo $this->ui('Common.Form.Label',array(
									'for'=>''
									,'label'=>'Editable'
								));
								echo '</li>';
							} ?>
							
							
							<?php /*
							<li style="display: inline-block;">
								<select>
									<option>=</option>
									<option selected="selected">Contains</option> <!-- like alias -->
									<option>RegExp</option>
								</select>
							</li>
							<li style="display: inline-block;">
								<select>
									<option selected="selected">%s%</option>
									<option>s%</option>
									<option>%s</option>
								</select>
							</li> */ ?>
							<?php 
							// filter comparision and possible wildcard (contains) or regexp
							if(strcmp('filters',$type) === 0) {
								
								// comparisions available based on data type
								// for example a ful text search can only happen
								// on a field compatiable. A regex or like can only
								// happen on a field that is text or varchar type.
								echo '<li style="display: inline-block;">';
								echo $this->ui('Common.Form.Select',array(
									'name'=>''
									,'id'=>''
									,'data'=>array('values'=>$field['comparisions']['values'])
									,'value'=>$field['comparision']
								)); 
								echo '</li>';
								
								// wildcard selection for contains comparision
								// This will determine how to handle the like comparision
								// The values will be %s, s% or %s% - s indicates where
								// the string will be placed.
								if(isset($field['wildcards'])) {
									echo '<li style="display: inline-block;">';
									echo $this->ui('Common.Form.Select',array(
										'name'=>''
										,'id'=>''
										,'data'=>array('values'=>$field['wildcards']['values'])
										,'value'=>$field['wildcard']
									)); 
									echo '</li>';									
								}
								
								// regex definition - only compatible with regular expression
								// comparision.
								if(isset($field['regex'])) {
									echo '<li style="display: inline-block;">';
									echo $this->ui('Common.Form.Input',array(
										'value'=>$field['regex']
										,'name'=>''
										,'type'=>'text'
									));
									echo '</li>';
								}
								
							} ?>
							
							<?php /*
								<li style="display: inline-block;">
									<select disabled="disabled">
										<option>Increase</option>
										<option selected="selected">Decrease</option>
									</select>
								</li>
							*/ ?>
							<?php if(strcmp('sorting',$type) === 0) {
								
								// ascending, descending and random ordering selecting for sorting field
								if(isset($field['orderings'])) {
									echo '<li style="display: inline-block;">';
									echo $this->ui('Common.Form.Select',array(
										'name'=>''
										,'id'=>''
										,'data'=>array('values'=>$field['orderings']['values'])
										,'value'=>$field['ordering']
									)); 
									echo '</li>';
								}
								
							} ?>
							
							
						</ul>
						
						<?php 
						/*
							<ul>
								<li style="display: inline-block;">
									<input type="radio" name="frmView[filter][1][delimiter]" checked="checked">
									<label for="">One Of</label>
								</li>
								<li style="display: inline-block;">
									<input type="radio" name="frmView[filter][1][delimiter]">
									<label for="">None Of</label>
								</li>
								<li style="display: inline-block;">
									<input type="radio" name="frmView[filter][1][delimiter]">
									<label for="">All Of</label>
								</li>
							</ul>
						*/
						// Filter operators - will exist as a radio group as per the design
						if(strcmp('filters',$type) === 0) {
							if(isset($field['operators'])) {
								echo '<ul>';
								foreach($field['operators']['values'] as $operator) {
									echo '<li style="display: inline-block;">';
									// the radio button
									echo $this->ui('Common.Form.Radio',array(
										'name'=>''
										,'id'=>''
										,'value'=>$operator['value']
										,'checked'=>strcmp($operator['value'],$field['operator']) === 0
									));
									// the label for the radio button
									echo $this->ui('Common.Form.Label',array(
										'for'=>''
										,'label'=>$operator['label']
									));
									echo '</li>';
								}
								echo '</ul>';
							}
						} ?>
						
						
						<?php /*------- options, values and priorities -------------- */ ?>
						
						
						<?php 
						if(strcmp('fields',$type) === 0) {
						
							if( !empty($field['options'])) {
								
								echo '<ul>';
								
								foreach( $field['options'] as $field_option) {
									
								/* <li>
									<select name="frmView[fields][0][option][0][type]">
										<option selected="selected">Width</option>
										<option>Height</option>
										<option>Grayscale</option>
										<option>B/W</option>
									</select>
									<input type="text" value="200" name="frmView[fields][0][option][0][value]">
									<select name="frmView[fields][0][option][0][location]">
										<option selected="selected">value</option>
										<option>arg</option>
									</select>
								</li>	*/	

									echo '<li>';
									
									
									/*
									* use the option name as the option values labels
									*/
									echo $this->ui('Common.Form.Label',array(
										'label'=>"{$field_option['name']}:"
										,'for'=>''
									));

									/*
									* When value is an argument create a select menu
									* of all arguments available. When argument is a view
									* or static value use a normal text field.
									*/
									switch( $field_option['type'] ) {
										case 'argument':
											echo $this->ui('Common.Form.Select',array(
												'name'=>''
												,'id'=>''
												,'data'=>array('values'=>$field_option['arguments'])
												,'value'=>$field_option['value']
											));
											break;
											
										case 'view':
										default:
											echo $this->ui('Common.Form.Input',array(
												'value'=>$field_option['value']
												,'name'=>''
												,'type'=>'text'
											));
											
									}
									
									/*
									* Type drop down ie. value (static), argument or view 
									* 
									* - the type will need to change the value ex. when one selects
									* the argument type the argument drop down that lists all arguments
									* will need to be shown in place of the default text field.
									*/
									echo $this->ui('Common.Form.Select',array(
										'name'=>''
										,'id'=>''
										,'data'=>array('values'=>$field_option['types'])
										,'value'=>$field_option['type']
									));
									
									echo '</li>';
									
								}
								
								echo '</ul>';
								
							}
							
						} else if( strcmp('filters',$type) === 0 ) { 
						
							if( !empty($field['values']) ) {
								
								echo '<ol>';
								
								foreach( $field['values'] as $filter_value ) {
									echo '<li>';
									
									/*
									* When value is an argument create a select menu
									* of all arguments available. When argument is a view
									* or static value use a normal text field.
									*/
									switch( $filter_value['type'] ) {
										case 'argument':
											echo $this->ui('Common.Form.Select',array(
												'name'=>''
												,'id'=>''
												,'data'=>array('values'=>$filter_value['arguments'])
												,'value'=>$filter_value['value']
											));
											break;
											
										case 'view':
										default:
											echo $this->ui('Common.Form.Input',array(
												'value'=>$filter_value['value']
												,'name'=>''
												,'type'=>'text'
											));
											
									}
									
									/*
									* Type drop down ie. value (static), argument or view 
									* 
									* - the type will need to change the value ex. when one selects
									* the argument type the argument drop down that lists all arguments
									* will need to be shown in place of the default text field.
									*/
									echo $this->ui('Common.Form.Select',array(
										'name'=>''
										,'id'=>''
										,'data'=>array('values'=>$filter_value['types'])
										,'value'=>$filter_value['type']
									)); 
									
									/*
									* Regular expression and like comparision field level overrides. These
									* make it possible to define a different like comparision or regular
									* expression than the default defined for the filter. These are only supported
									* when the filter uses a regular expression or like comparision condition. 
									*/
									if( strcmp($field['comparision'],'like' ) === 0) {
										// will need a override button and another text field
										echo $this->ui('Common.Form.Submit',array(
											'label'=>"Override"
											,'name'=>''
										));
										
										// I will cricle back around to this - need to create a view that use this
									}
									
									if( strcmp($field['comparision'],'regex' ) === 0 ) {
										// will need a override button and another text field
										echo $this->ui('Common.Form.Submit',array(
											'label'=>"Override"
											,'name'=>''
										));
										
										// I will cricle back around to this - need to create a view that use this
									}
									
									echo '</li>';
									
								}
								
								echo '</ol>';
								
							}
							
						} ?>
						
						
					</fieldset>
				<?php } ?>
			<?php } ?>
			
			<p><input type="submit" value="+"></p>
			
	</fieldset>
	<?php } ?>
	

<p>--------------------------------------------------------------------------- </p>

	</fieldset>
</form>

