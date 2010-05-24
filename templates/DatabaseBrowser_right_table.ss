	<h1><span class="small">Table</span> $Table.Name</h1>
	<div id="tabs">
		<% control Table %>
			<ul>
				<li><a class='tabtabs' title="browse-tab">Browse</a></li>
				<li><a class='tabtabs' title="structure-tab">Structure</a></li>
			</ul>
			<div id="browse-tab" class='tabbody'>
				<% include DatabaseBrowser_right_data %>
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
								<td>$Name</td>
								<td>$Spec</td>
							</tr>
						<% end_control %>
					</tbody>
				</table>
			</div>
		<% end_control %>
	</div>
