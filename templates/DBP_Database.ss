	<h1><span class="small">Database</span> $Name</h1>
	<div id="tabs">
		<ul>
			<li><a class='tabtabs' title="info-tab">Info</a></li>
			<li><a class='tabtabs' title="sql-tab">SQL</a></li>
		</ul>
		<div id="info-tab" class='tabbody'>
			<table>
				<tr class='odd'>
					<td>Database Type</td>
					<td>$Type</td>
				</tr>
				<tr class='even'>
					<td>Database Version</td>
					<td>$Version</td>
				</tr>
				<tr class='odd'>
					<td>Database Adapter</td>
					<td>$Adapter</td>
				</tr>
			</table>
		</div>
		<div id="sql-tab" class='tabbody'>
			<% include DBP_Database_sql %>
		</div>
	</div>