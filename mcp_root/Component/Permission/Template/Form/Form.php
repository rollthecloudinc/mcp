<form>

	<fieldset>
	
		<select id="type">
			<option value="">--</option>
			<option value="user">By User</option>
			<option value="role">By Role</option>
		</select>
	
		<select id="user">
			<option value="">--</option>
			<option value="username_1">username_1</option>
			<option value="username_2">username_2</option>
			<option value="username_3">username_3</option>
			<option value="username_4">username_4</option>
			<option value="username_5">username_5</option>
			<option value="username_6">username_6</option>
			<option value="username_7">username_7</option>
		</select>
		
		<select id="role">
			<option value="">--</option>
			<option value="role_1">role_1</option>
			<option value="role_2">role_2</option>
			<option value="role_3">role_3</option>
		</select>
		
		<select>
			<option value="">--</option>
			<option value="">Term</option>
			<option value="">Vocabulary</option>
			<option value="">Node</option>
			<option value="">NodeType</option>
			<option value="">Config</option>
			<option value="">Routing</option>
			<option value="">Site</option>
			<option value="">View</option>
		</select>
	
		<p>PlatForm.RealEstate::listing</p>
		
		<ul style="display: none;">
			<li>	
				<input type="checkbox" name="" value="1" />
				<label for="">Read</label>
				<em>Read the listing type definition?</em>
			</li>
			<li>
				<input type="checkbox" name="" value="1" />
				<label for="">Edit</label>
				<em>Edit the listing type definition?</em>
			</li>
			<li>
				<input type="checkbox" name="" value="1" />
				<label for="">Delete</label>
				<em>Delete the listing type definition?</em>
			</li>
		</ul>
		
		<ul>
			<li>
                                <label for="">Create New Site</label>
				<input type="checkbox" name="" disabled />
		
			</li>
			<li>
                                <label for="">Read All Sites</label>
				<select>
                                    <option value="">--</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                    <option value="2">Owner</option>
                                </select>
	
			</li>
			<li>
                                <label for="">Edit All Sites</label>
				<select>
                                    <option value="">--</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                    <option value="2">Owner</option>
                                </select>
				
			</li>
			<li>
                                <label for="">Delete All Sites</label>
				<select>
                                    <option value="">--</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                    <option value="2">Owner</option>
                                </select>
			</li>
		</ul>

<table width="100%">
        <caption>Sites</caption>
	<thead>
		<tr>
			<th>Site</th>
			<th>Read</th>
			<th>Update</th>
			<th>Delete</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Site 1</td>
			<td>
				<input type="checkbox" checked disabled> <!-- current state -->
				<select>
					<option value="" selected>Default</option>
					<option value="1">Yes</option>
					<option value="0">No</option>
				</select>
			</td>
			<td>
				<input type="checkbox" checked disabled> <!-- current state -->
				<select>
					<option value="">Default</option>
					<option value="1" selected>Yes</option>
					<option value="0">No</option>
				</select>
			</td>
			<td>
				<input type="checkbox" disabled> <!-- current state -->
				<select>
					<option value="">Default</option>
					<option value="1">Yes</option>
					<option value="0" selected>No</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Site 2</td>
			<td>
				<input type="checkbox" disabled> <!-- current state -->
				<select>
					<option value="">Default</option>
					<option value="1">Yes</option>
					<option value="0" selected>No</option>
				</select>
			</td>
			<td>
				<input type="checkbox" checked disabled> <!-- current state -->
				<select>
					<option value="">Default</option>
					<option value="1" selected>Yes</option>
					<option value="0">No</option>
				</select>
			</td>
			<td>
				<input type="checkbox" disabled> <!-- current state -->
				<select>
					<option value="">Default</option>
					<option value="1">Yes</option>
					<option value="0" selected>No</option>
				</select>
			</td>
		</tr>
	</tbody>
</table>
                
                
                
                	
	</fieldset>

</form>