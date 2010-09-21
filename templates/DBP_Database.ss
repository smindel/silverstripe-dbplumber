	<h1><span class="small">Database</span> $Name</h1>
	<div id="tabs">
		<ul>
			<li><a class='tabtabs' title="info-tab">Info</a></li>
			<li><a class='tabtabs' title="sql-tab">SQL</a></li>
			<li><a class='tabtabs' title="port-tab">Import / Export</a></li>
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
				<tr class='odd'>
					<td>Transactions</td>
					<td><% if Transactions %>supported<% else %>not supported<% end_if %></td>
				</tr>
			</table>
		</div>
		<div id="sql-tab" class='tabbody'>
			<% include DBP_Database_sql %>
		</div>
		<div id="port-tab" class='tabbody'>
			<div id='exportformdiv' class='importexportdiv'>
				<form action='admin/dbplumber/database/export' method="post">
					<div>
						<label for="tablenames">Tables to export</label>
						<div class="input">
							<select id='tablenames' name="tables[]" size="10" MULTIPLE>
								<% control Tables %>
									<option value="$Name" selected="selected">$Name</option>
								<% end_control %>
							</select>
						</div>
					</div>
					<div>
						<label for="exporttype">Export type</label>
						<div class="input"><input id='exporttype' name='exporttype' value='backup' type="radio" checked="checked"> Backup (SQL DELETEs and INSERTs)</div>
					</div>
					<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only export" aria-disabled="false" type="submit"><a href='#'>export</a></button>
				</form>
			</div>
			<div id='importformdiv' class='importexportdiv'>
				<form action='admin/dbplumber/database/import' method="post" enctype="multipart/form-data">
					<input type="hidden" name="MAX_FILE_SIZE" value="16777216" />
					<div>
						<label for="importfile">Import SQL File</label>
						<div class="input"><input id='importfile' name='importfile' type="file"></div>
					</div>
					<div>
						<label for="exporttype">Import type</label>
						<div class="input"><input id='importtype' name='importtype' value='rawsql' type="radio" checked="checked"> SQL commands</div>
					</div>
					<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only import" aria-disabled="false" type="submit"><span>import</span></button>
				</form>
			</div>
		</div>
	</div>