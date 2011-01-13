<?php 
if(isset($_POST['frmView'])) echo '<pre>',print_r($_POST['frmView']),'</pre>';
?>
<form method="POST" name="frmView" id="frmView" action="<?php echo $objModule->getBasePath(); ?>">
	<fieldset>
		<legend>View</legend>
		
		<fieldset>
			<legend>Type</legend>
			
			<select>
				<option>--</option>
				<option>Node Type 1</option>
				<option>Node Type 2</option>
				<option>Node Type 3</option>
				<option>Node Type 4</option>
				<option>Members</option>
				<option>Vocabulary 1</option>
				<option>Vocabulary 3</option>
				<option>Vocabulary 4</option>
				<option>Sites</option>
			</select>
			
		</fieldset>
		
		<fieldset>
			<legend>Naming</legend>
			<ul>
				<li>
					<label for="">System Name</label>
					<input type="text" value="products">
				</li>
				<li>
					<label for="">Human Name</label>
					<input type="text" value="Products List">
				</li>
			</ul>	
		</fieldset>
		
		<fieldset>
			<legend>General</legend>

				<ul>
					<li>
						<label for="">Items Per Page</label>
						<input type="text" value="20">
					</li>
					<li>
						<label for="">Page Heading</label>
						<input type="text" value="Products {Search String 2}">
					</li>
				</ul>
		
				<ul>
					<li>
						<input type="checkbox" value="1">
						<label for="">Paginate</label>
					</li>
				</ul>
		
		</fieldset>

		<fieldset>
			<legend>Arguments</legend>
			
			<p>Arguments supplied from URL, function, GET or POST array. Use this list to
			define arguments that are made available to view queries for filtering, sorting, etc
			based on dynamic arguments made available to views via query string, post, ect.</p>
			
			<p>To prevent breaking views arguments may not be removed until all argument
			references for the argument have been removed for filters, sorting, etc.</p>
			
			<fieldset>
				<input type="hidden" value="90" name="frmView[arguments][0][id]" disabled="disabled">
				
				<input type="text" value="Search String 1" disabled="disabled" name="frmView[arguments][0][name]">
				<select disabled="disabled" name="frmView[arguments][0][location]">
					<option>Args</option> <!-- arguments from page, relative to current page -->
					<option selected="selected">POST</option>
					<option>GET</option>
					<option>Function</option>
					<option>DAO</option>
					<option>PHP</option>
				</select>
				<input type="text" value="search_string" disabled="disabled" name="frmView[arguments][0][value]">
				
				<input type="submit" value="-" disabled="disabled" style="float: right;margin-left: 1em;" name="frmView[action][remove][arguments][0]">
				<input type="submit" value="Override" style="float: right;" name="frmView[action][override][arguments][0]">
			
			</fieldset>
			
			<fieldset>
				<input type="hidden" value="0" name="frmView[arguments][1][id]"> <!-- id of 0 means the field is new - yes to be saved -->
				
				<input type="text" value="Search String 2" name="frmView[arguments][1][name]">
				<select name="frmView[arguments][1][location]">
					<option selected="selected">Args</option>
					<option>POST</option>
					<option>GET</option>
					<option>Function</option>
					<option>DAO</option>
					<option>PHP</option>
				</select>
				<input type="text" value="1" name="frmView[arguments][1][value]">
				
				<input type="submit" value="-" style="float: right;margin-left: 1em;" disabled="disabled" name="frmView[action][remove][arguments][1]">
				<input type="submit" value="Use Default" style="float: right;" name="frmView[action][use_default][arguments][1]">
			
			</fieldset>
			
			<p><input type="submit" value="+" name="frmView[action][add][argument]"></p>
			
		</fieldset>
		
		<fieldset>
			<legend>Fields</legend>
			
			<p>Fields that will be shown to the end-user.</p>
			
			<fieldset>
			
				<input type="submit" value="-" disabled="disabled" style="float: right;margin-left: 1em;">
				<input type="submit" value="Override" style="float: right;">
				
				<ul>
					<li style="display: inline-block">
						<select disabled="disabled">
							<option>--</option>
							<option>id</option>
							<option>site</option>
							<option>author</option>
							<option>classification</option>
							<option>Published</option>
							<option selected="selected">Title</option>
							<option>SubTitle</option>
							<option>Teaser</option>
							<option>Body</option>
							<option>Updated</option>
							<option>Created</option>
							<!-- dynamic fields - mixed in as if actually part of item -->
							<option>Manufacturer</option>
							<option>Main Image</option>
							<option>Categories</option> <!-- possible multidimensional field - m:n -->				
						</select>
					</li>
					<li style="display: inline-block">
						<input type="checkbox" checked="checked" disabled="disabled">
						<label for="">Sortable</label>
					</li>
					<li style="display: inline-block">
						<input type="checkbox" checked="checked" disabled="disabled">
						<label for="">Editable</label>
					</li>
				</ul>
			</fieldset>
			<fieldset>
				<input type="hidden" value="56" name="frmView[fields][0][id]">
			
				<input type="submit" value="-" style="float: right;margin-left: 1em;">
				<input type="submit" value="Use Default" style="float: right;">
				
				<ul>
					<li style="display: inline-block">
						<select name="frmView[fields][0][field][]">
							<option>--</option>
							<option>id</option>
							<option>site</option>
							<option>author</option>
							<option>classification</option>
							<option>Published</option>
							<option>Title</option>
							<option>SubTitle</option>
							<option>Teaser</option>
							<option>Body</option>
							<option>Updated</option>
							<option>Created</option>
							<!-- dynamic fields - mixed in as if actually part of item -->
							<option selected="selected">field</option>				
						</select>
					</li>
					<li style="display: inline-block">
						<select name="frmView[fields][0][field][]">
							<option selected="selected">main image</option>				
						</select>
					</li>
					<li style="display: inline-block">
						<select name="frmView[fields][0][field][]">
							<option>--</option>
							<option selected="selected">Image</option> <!--the actual image itself -->
							<option>id</option>
							<option>width</option>
							<option>height</option>
							<option>size</option>
							<option>created</option>			
						</select>
					</li>
				</ul>
				
				<ul>
					<li>
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
					</li>
					<li>
						<select name="frmView[fields][0][option][1][type]">
							<option>Width</option>
							<option>Height</option>
							<option selected="selected">Grayscale</option>
							<option>B/W</option>
						</select>
						<input type="text" value="true" disabled="disabled" name="frmView[fields][0][option][1][value]">
						<select disabled="disabled" name="frmView[fields][0][option][1][location]">
							<option selected="selected">value</option>
							<option>arg</option>
						</select>
					</li>
				</ul>
			</fieldset>
			<fieldset>
				<input type="hidden" value="221" name="frmView[fields][1][id]">
			
				<input type="submit" value="-" style="float: right;margin-left: 1em;">
				<input type="submit" value="Use Default" style="float: right;">
				
				<ul>
					<li style="display: inline-block">
						<select name="frmView[fields][1][field][]">
							<option>--</option>
							<option>id</option>
							<option>site</option>
							<option>author</option>
							<option>classification</option>
							<option>Published</option>
							<option>Title</option>
							<option>SubTitle</option>
							<option>Teaser</option>
							<option>Body</option>
							<option>Updated</option>
							<option>Created</option>
							<!-- dynamic fields - mixed in as if actually part of item -->
							<option selected="selected">field</option>				
						</select>
					</li>
					<li style="display: inline-block">
						<select name="frmView[fields][1][field][]">
							<option selected="selected">msrp</option>				
						</select>
					</li>
					<li style="display: inline-block">
						<input type="checkbox" checked="checked" value="1" name="frmView[fields][1][sortable]">
						<label for="">Sortable</label>
					</li>
					<li style="display: inline-block">
						<input type="checkbox" checked="checked" value="1" name="frmView[fields][1][editable]">
						<label for="">Editable</label>
					</li>
				</ul>
			</fieldset>
			
			<p><input type="submit" value="+"></p>
		
		<p style="display: none;">* When non atomic fields are shown provide next drop down for selecting field of relation</p>
		
		<ul style="display: none;">
			<li>
				<label for="">Node</label>
				<select>
					<option>--</option>
					<option>id</option>
					<option>site</option>
					<option>author</option>
					<option>classification</option>
					<option>Published</option>
					<option>Title</option>
					<option>SubTitle</option>
					<option>Teaser</option>
					<option>Body</option>
					<option>Updated</option>
					<option>Created</option>
					<!-- dynamic fields - mixed in as if actually part of item -->
					<option>Manufacturer</option>
					<option>Main Image</option>
					<option>Categories</option> <!-- possible multidimensional field - m:n -->
				</select>
			</li>
			<li>
				<label for="">User</label>
				<select>
					<option>--</option>
					<option>id</option>
					<option>site</option>
					<option>username</option>
					<option>email</option>
					<!-- pwd and uid will be forbidden for obvious reasons -->
					<option>Updated</option>
					<option>Created</option>
					<option>Last Login</option>
					<option>Banned</option>
					<!-- any dynamic fields -->
					<option>Profile</option>
				</select>
			</li>
			<li>
				<label for="">Site</label>
				<select>
					<option>--</option>
					<option>id</option>
					<option>creator</option>
					<option>name</option>
					<option>directory</option>
					<option>prefix</option>
					<option>created</option>
					<option>updated</option>
					<option>domain</option> <!-- mixed in field -->
				</select>
			</li>
			<li>
				<label for="">Node Type</label>
				<select>
					<option>--</option>
					<option>id</option>
					<option>site</option>
					<option>creator</option>
					<option>pkg</option>
					<option>system name</option>
					<option>human name</option>
					<option>description</option>
					<option>Created</option>
					<option>Updated</option>
				</select>
			</li>
			<li>
				<label for="">Media</label>
				<select>
					<option>--</option>
					<option>Image</option> <!--the actual image itself -->
					<option>id</option>
					<option>width</option>
					<option>height</option>
					<option>size</option>
					<option>created</option>
				</select>
				<ul>
					<li>
						<label for="">Width</label>
						<input type="text" name="">
					</li>
					<li>
						<label for="">Height</label>
						<input type="text" name="">
					</li>
					<li>
						<label for="">Transformations</label>
						<ul>
							<li>
								<select>
									<option>--</option>
									<option>Grayscale</option>
									<option>Black and White</option>
								</select>
							</li>
						</ul>
					</li>
				</ul>
			</li>
			<li>
				<label for="">Term</label>
				<select>
					<option>--</option>
					<option>id</option>
					<option>creator</option>
					<option>system name</option>
					<option>human name</option>
					<option>description</option>
					<option>weight</option>
					<option>created</option>
					<option>updated</option>
					<!-- dynamic reference to vocabulary -->
					<option>vocabulary</option>
					<!-- dynamic reference to parent -->
					<option>parent</option> <!-- handle case of root and child of term -->
					<!-- any dynamic fields -->
					<option>Main Image</option>
				</select>
			</li>
			<li>
				<label for="">Vocabulary</label>
				<select>
					<option>--</option>
					<option>id</option>
					<option>Site</option>
					<option>human name</option>
					<option>system name</option>
					<option>Pkg</option>
					<option>Description</option>
					<option>Weight</option>
					<option>created</option>
					<option>updated</option>
					<!-- speciavalue for bulding name w/ pkg ie. ProjectPad::blah -->
					<option>Unique Name</option>
				</select>
			</li>
		</ul>
		
		</fieldset>
		
		<fieldset>
			<legend>Filters</legend>
			
			<p>I don't believe choosing a column to filter on is going to much different than selecting from one but
			we will see. For now I'll assume it has the same interface. Though some
			fields, such as Image won't be a filter. Each set of filters will be separated by AND.</p>
			
			<fieldset>
			
				<input type="submit" value="-" style="float: right;margin-left: 1em;" disabled="disabled">
				<input type="submit" value="Override" style="float: right;">
				
				<ul>
					<li style="display: inline-block;">
						<select disabled="disabled">
							<option>node type</option>
						</select>
					</li>
					<li style="display: inline-block;">
						<select disabled="disabled">
							<option>system name</option>
						</select>
					</li>
					
					<li style="display: inline-block;">
						<select disabled="disabled">
							<option selected="selected">=</option>
							<option>Contains</option> <!-- like alias -->
							<option>RegExp</option>
						</select>
					</li>
					
				</ul>
				
				<ul>
					<li style="display: inline-block;">
						<input disabled="disabled" type="radio" name="frmView[filter][0][delimiter]" checked="checked">
						<label for="">One Of</label>
					</li>
					<li style="display: inline-block;">
						<input disabled="disabled" type="radio" name="frmView[filter][0][delimiter]">
						<label for="">None Of</label>
					</li>
					<li style="display: inline-block;">
						<input disabled="disabled" type="radio" name="frmView[filter][0][delimiter]">
						<label for="">All Of</label>
					</li>
				</ul>
				
				<ol>
					<li>
						<input type="text" value="product" disabled="disabled">
						<select disabled="disabled">
							<option selected="selected">value</option>
							<option>arg</option>
							<option>view</option>
						</select>
					</li>
				</ol>
			
			</fieldset>
			
			<fieldset>

				<input type="submit" value="-" style="float: right;margin-left: 1em;">
				<input type="submit" value="Use Default" style="float: right;">

				<ul>
					<li style="display: inline-block;">
						<select>
							<option>body</option>
						</select>
					</li>
					
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
					</li>
					
				</ul>
				
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
				
				<ol>
					<li>
						<select>
							<option selected="selected">Search String 1</option>
							<option>Search String 2</option>
							<option>Search String 3</option>
						</select>
						<select>
							<option>value</option>
							<option selected="selected">arg</option>
							<option>view</option>
						</select>
						<button>Override %s%</button>
					</li>
				</ol>
			
			</fieldset>
			
			<fieldset>

				<input type="submit" value="-" style="float: right;margin-left: 1em;">
				<input type="submit" value="Use Default" style="float: right;">

				<ul>
					<li style="display: inline-block;">
						<select>
							<option>field</option>
						</select>
					</li>
					
					<li style="display: inline-block;">
						<select>
							<option>manufacturer</option>
						</select>
					</li>
					
					<li style="display: inline-block;">
						<select>
							<option>system name</option>
						</select>
					</li>
					
					<li style="display: inline-block;">
						<select>
							<option selected="selected">=</option>
							<option>Contains</option> <!-- like alias -->
							<option>RegExp</option>
						</select>
					</li>
					
				</ul>
				
				<ul>
					<li style="display: inline-block;">
						<input type="radio" name="frmView[filter][2][delimiter]" checked="checked">
						<label for="">One Of</label>
					</li>
					<li style="display: inline-block;">
						<input type="radio" name="frmView[filter][2][delimiter]">
						<label for="">None Of</label>
					</li>
					<li style="display: inline-block;">
						<input type="radio" name="frmView[filter][2][delimiter]">
						<label for="">All Of</label>
					</li>
				</ul>
				
				<ol>
					<li>
						<select>
							<option>Search String 1</option>
							<option selected="selected">Search String 2</option>
							<option>Search String 3</option>
						</select>
						<select>
							<option>value</option>
							<option selected="selected">arg</option>
							<option>view</option>
						</select>
					</li>
				</ol>
			
			</fieldset>
			
			<fieldset>

				<input type="submit" value="-" style="float: right;margin-left: 1em;">
				<input type="submit" value="Use Default" style="float: right;">

				<ul>
					<li style="display: inline-block;">
						<select>
							<option>published</option>
						</select>
					</li>
					<li style="display: inline-block;">
						<select>
							<option selected="selected">yes</option>
							<option>no</option>
						</select>
					</li>
				</ul>
			
			</fieldset>
			
			<p><input type="submit" value="+"></p>
			
		</fieldset>
		
		<fieldset>
			<legend>Sorting</legend>
		
			<p>In contrast to fields that are sortable the fields defined below will
			always exist in the order by clause.</p>
		
			<fieldset>
			
				<input type="submit" value="-" style="float: right;margin-left: 1em;" disabled="disabled">
				<input type="submit" value="Override" style="float: right;">
				
				<ul>
					<li style="display: inline-block;">
						<select disabled="disabled">
							<option>published</option>
						</select>
					</li>
					<li style="display: inline-block;">
						<select disabled="disabled">
							<option>Increase</option>
							<option selected="selected">Decrease</option>
						</select>
					</li>
				</ul>
				
				<fieldset>
				
					<div>
						<input type="checkbox" checked="checked" disabled="disabled">
						<label for="">Priority</label>
					</div>
					
					<ol>
						<li>
							<input type="text" value="1" disabled="disabled">
							<select disabled="disabled">
								<option selected="selected">value</option>
								<option>arg</option>
								<option>view</option>
							</select>
						</li>
						<li>
							<input type="text" value="0" disabled="disabled">
							<select disabled="disabled">
								<option selected="selected">value</option>
								<option>arg</option>
								<option>view</option>
							</select>
						</li>
					</ol>
				</fieldset>
				
			</fieldset>
			
			<p><input type="submit" value="+"></p>
		
		</fieldset>
		
		<fieldset>
			<legend>Layout</legend>
			
			<p>This may be an attempt to create a graphical UI with JavaScript for laying out
			the fields being shown. Not sure about this yet, though would be cool.</p>
		
		</fieldset>
		
		<fieldset>
			<legend>Analytics</legend>
		
			<p>Query generation analytics</p>
		
		</fieldset>
		
	</fieldset>
</form>
