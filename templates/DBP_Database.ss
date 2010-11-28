	<h1><span class="small">Database</span> $Name</h1>
	<div id="tabs">
		<ul>
			<li><a class='tabtabs' title="info-tab">Info</a></li>
			<li><a class='tabtabs' title="sql-tab">SQL</a></li>
			<li><a class='tabtabs' title="port-tab">Import / Export</a></li>
			<li><a class='tabtabs' title="artefact-tab">Artefacts</a></li>
		</ul>
		<div id="info-tab" class='tabbody'>
			<table>
				<tr class='even'>
					<td>Database Type</td>
					<td>$Type</td>
				</tr>
				<tr class='odd'>
					<td>Database Version</td>
					<td>$Version</td>
				</tr>
				<tr class='even'>
					<td>Transactions</td>
					<td><% if Transactions %>supported<% else %>not supported<% end_if %></td>
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
				<form action='admin/dbplumber/database/export' method="post">
					<div>
						<label for="tablenames">Tables to export</label>
						<div class="input">
							<select id="tablenames" name="tables[]" size="10" MULTIPLE>
								<% control Tables %>
									<option value="$Name" selected="selected">$Name</option>
								<% end_control %>
							</select>
						</div>
					</div>
					<div>
						<label>Export type</label>
						<div class="input"><input id='exporttype_backup' name='exporttype' value='backup' type="radio" checked="checked"> Backup (SQL DELETEs and INSERTs)</div>
						<% if HasZlibSupport %><div class="input"><input id='exporttype_compressed' name='exporttype' value='compressed' type="radio"> Backup compressed using ZLIB</div><% end_if %>
					</div>
					<div>
						<label>SQL Dialect (experimental)</label>
						<% control Adapters %>
							<div class="input"<% if Available == 0 %> style="color:grey"<% end_if %>><input id='SqlDialect_{$Name}' name='SqlDialect' value='{$Name}' type="radio"<% if Available == 0 %> disabled="disabled"<% end_if %><% if Selected %> checked="checked"<% end_if %>> $Name</div>
						<% end_control %>
					</div>
					<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only export" aria-disabled="false" type="submit"><a href='#'>export</a></button>
				</form>
			</div>
			<div id='importformdiv' class='importexportdiv'>
				<form action='admin/dbplumber/database/import' method="post" enctype="multipart/form-data">
					<input type="hidden" name="MAX_FILE_SIZE" value="$MaxFileSize" />
					<div>
						<label for="importfile">Import SQL File (max file size $MaxFileSize)</label>
						<div class="input"><input id='importfile' name='importfile' type="file"></div>
					</div>
					<div>
						<label>Import type</label>
						<div class="input"><input id='importtype_rawsql' name='importtype' value='rawsql' type="radio"> SQL commands</div>
						<% if HasZlibSupport %><div class="input"><input id='importtype_compressedsql' name='importtype' value='compressedsql' type="radio"> ZLIB compressed SQL commands</div><% end_if %>
						<div class="input"><input id='importtype_auto' name='importtype' value='auto' type="radio" checked="checked"> auto detect by file name extension</div>
					</div>
					<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only import" aria-disabled="false" type="submit"><span>import</span></button>
					
					<div class="ui-state-highlight ui-corner-all" style="width:170px; padding-left:5px;">
						<p>
							<span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>
							<a href="importexport" class="DBP_HELPER"><strong>HOWTO:</strong> Import/Export</a>
						</p>
					</div>
					
					<div id="importexport" class='DBP_HELP' title='andy'>
						<h3>HOWTO Export/Import</h3>
						<p>The short answer is: click the export button on the left form to create a backup or upload a backup file in the right form of this tab to restore a beckup.</p>
						<p>What it does: It only exports the data of your database. The file contains raw SQL statements, one delete statement per table to delete all current records and one insert statement per backed up record to restore it.</p>
						<p>What it doesn't do: It doesn't contain the structure of your database. <b>That said you have to have run dev/build on the code that you're importing your backup to create the schema. If you are migrating databases the project's PHP code has to be identical.</b></p>
						<p>In general it should be possible to copy data between different servers and DBMS. You could for example export your MySQL production database and import it to your SQLite development server, provided both run the same code. If you are migrating from on adapter to another pick the appropriate target SQL dialect on export. (There is one limitation to cross adapter migration as of now: migrations to SQL Server do not work because there is no way to determine identity columns.)</p>
						<p>If your import fails, make sure your backup doesn't exceed the maximum file size. Use Zlib compression to reduce the size of your dump on export. To bump up the file size limit increase post_max_size and upload_max_filesize in your php.ini</p>
					</div>
				</form>
			</div>
		</div>
		<div id="artefact-tab" class='tabbody'>
			Computing Artefacts...
		</div>
	</div>