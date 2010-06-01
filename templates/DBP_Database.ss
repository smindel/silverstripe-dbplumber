	<h1><span class="small">Database</span> $Database.Name</h1>
	<div id="tabs">
		<ul>
			<li><a class='tabtabs' title="info-tab">Info</a></li>
			<li><a class='tabtabs' title="sql-tab">SQL</a></li>
		</ul>
		<div id="info-tab" class='tabbody'>
			<table>
				<tr class='odd'>
					<td>Database Type</td>
					<td>$Database.Type</td>
				</tr>
				<tr class='even'>
					<td>Database Version</td>
					<td>$Database.Version</td>
				</tr>
				<tr class='odd'>
					<td>Database Adapter</td>
					<td>$Database.Adapter</td>
				</tr>
			</table>
		</div>
		<div id="sql-tab" class='tabbody'>
			<% include DatabaseBrowser_right_sql %>
		</div>
	</div>