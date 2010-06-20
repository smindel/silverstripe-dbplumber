	<h1><span class="small">Table</span> $Name</h1>
	<div id="tabs">
		<ul>
			<li><a class='tabtabs' title="browse-tab">Browse</a></li>
			<li><a class='tabtabs' title="structure-tab">Structure</a></li>
			<li><a class='tabtabs' title="form-tab">Form</a></li>
		</ul>
		<div id="browse-tab" class='tabbody'>
			<% include DBP_Table_index %>
		</div>
		<div id="structure-tab" class='tabbody'>
			<table>
				<thead>
					<tr>
						<td>Field Name</td>
						<td>Field Spec</td>
					</tr>
				</thead>
				<tbody>
					<% control Fields %>
						<tr class='<% if Even %>even<% else %>odd<% end_if %>'>
							<td>$Label</td>
							<td>$Spec</td>
						</tr>
					<% end_control %>
				</tbody>
			</table>
		</div>
		<div id="form-tab" class='tabbody'>
			<% control Record %>
				<% include DBP_Record_form %>
			<% end_control %>
		</div>
	</div>
