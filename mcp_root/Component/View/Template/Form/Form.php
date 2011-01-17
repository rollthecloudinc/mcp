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
								,'disabled'=>true
							));
							echo $this->ui('Common.Form.Submit',array(
								'name'=>''
								,'label'=>'Override'
								,'disabled'=>true
							));
						?>
						
						<ul>
							<?php foreach($field as $path) { ?>
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
							</li>*/ ?>
							
							
						</ul>
					</fieldset>
				<?php } ?>
			<?php } ?>
			
	</fieldset>
	<?php } ?>
	

<p>--------------------------------------------------------------------------- </p>

	</fieldset>
</form>

