	<h1><span class="small"><% _t('TABLE', 'Table') %></span> $Name</h1>
	<div id="tabs">
		<ul>
			<li><a class='tabtabs' title="browse-tab"><% _t('BROWSE', 'Browse') %></a></li>
			<li><a class='tabtabs' title="structure-tab"><% _t('STRUCTURE', 'Structure') %></a></li>
			<li><a class='tabtabs' title="form-tab"><% _t('FORM', 'Form') %></a></li>
			<li><a class='tabtabs warn' id="empty_btn" title="empty-tab"><% _t('EMPTY', 'Empty') %></a></li>
			<li><a class='tabtabs WARN' id="drop_btn" title="drop-tab"><% _t('DROP', 'Drop') %></a></li>
		</ul>
		<div id="browse-tab" class='tabbody'>
			<% include DBP_Table_index %>
		</div>
		<div id="structure-tab" class='tabbody'>
			<table>
				<thead>
					<tr>
						<td><% _t('FIELD_NAME', 'Field Name') %></td>
						<td><% _t('FIELD_SPEC', 'Field Spec') %></td>
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
		<div id="empty-tab" class='tabbody'>
			<form id="empty_form" action="admin/dbplumber/table/truncate/$Name">
			</form>
		</div>
		<div id="drop-tab" class='tabbody'>
			<form id="drop_form" action="admin/dbplumber/database/drop/$Name">
			</form>
		</div>
	</div>
