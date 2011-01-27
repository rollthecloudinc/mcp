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
echo $this->ui('Common.Form.Select',array(
	'name'=>'frmView[type]'
	,'id'=>'view-type'
	,'data'=>array('values'=>$config['type']['values'])
	,'value'=>''
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
							// delete and override controls (replaces commented controls above)
							echo $this->ui('Common.Form.Submit',array(
								'name'=>''
								,'label'=>'-'
								,'disabled'=>!$field['override']
							));
							echo $this->ui('Common.Form.Submit',array(
								'name'=>''
								,'label'=>$field['override']?'Use Default':'Override'
								,'disabled'=>false
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
						
							if(isset($field['options']) && !empty($field['options'])) {
								echo '<ul>';
								echo '<li>';
								echo $this->ui('Common.Form.Select',array(
									'name'=>''
									,'id'=>''
									,'data'=>array('values'=>$field['options']['values'])
									,'value'=>''
								)); 
								echo '</li>';
								echo '</ul>';
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

