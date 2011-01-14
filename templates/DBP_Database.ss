	<h1><span class="small"><% _t('DATABASE_HEADER', 'Database') %></span> $Name</h1>
	<div id="tabs">
		<ul>
			<li><a class='tabtabs' title="info-tab"><% _t('INFO_TAB', 'Info') %></a></li>
			<li><a class='tabtabs' title="sql-tab"><% _t('SQL_TAB', 'SQL') %></a></li>
			<li><a class='tabtabs' title="port-tab"><% _t('IMPORT_EXPORT_TAB', 'Import / Export') %></a></li>
			<li><a class='tabtabs' title="artefact-tab"><% _t('ARTEFACTS_TAB', 'Artefacts') %></a></li>
		</ul>
		<div id="info-tab" class='tabbody'>
			<table>
				<tr class='even'>
					<td><% _t('DB_TYPE', 'Database Type') %></td>
					<td>$Type</td>
				</tr>
				<tr class='odd'>
					<td><% _t('DB_VERSION', 'Database Version') %></td>
					<td>$Version</td>
				</tr>
				<tr class='even'>
					<td><% _t('DB_TRANSACTIONS', 'Transactions') %></td>
					<td><% if Transactions %><% _t('DB_TRANACTIONS_SUPPORTED', 'supported') %><% else %><% _t('DB_TRANSACTIONS_NOT_SUPPORTED', 'not supported') %><% end_if %></td>
				</tr>
				<% if ExposeConfig %>
				<% control ExposeConfig %>
				<tr class='$EvenOdd'>
					<td>$key</td>
					<td>$val</td>
				</tr>
				<% end_control %>
				<% end_if %>
			</table>
		</div>
		<div id="sql-tab" class='tabbody'>
			<% include DBP_Database_sql %>
		</div>
		<div id="port-tab" class='tabbody'>
			<div id='exportformdiv' class='importexportdiv'>
				<% include DBP_Database_export %>
			</div>
			<div id='importformdiv' class='importexportdiv'>
				<% include DBP_Database_import %>
			</div>
		</div>
		<div id="artefact-tab" class='tabbody'>
			<% _t('COMPUTING_ARTEFACTS', 'Computing Artefacts...') %>
		</div>
	</div>