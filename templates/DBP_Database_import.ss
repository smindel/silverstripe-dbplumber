<form action='admin/dbplumber/database/import' method="post" enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="$MaxFileSize" />
	<div>
		<label for="importfile"><% _t('MAX_FILE_SIZE', 'Import SQL File (max file size ') %>$MaxFileSize)</label>
		<div class="input"><input id='importfile' name='importfile' type="file"></div>
	</div>
	<div>
		<label><% _t('IMPORT_FORMAT', 'Import Format') %></label>
		<div class="input"><input id='importtype_rawsql' name='importtype' value='rawsql' type="radio"> <% _t('SQL_COMMANDS', 'SQL commands') %></div>
		<% if HasZlibSupport %><div class="input"><input id='importtype_compressedsql' name='importtype' value='compressedsql' type="radio"> <% _t('ZLIB_COMPRESSED', 'ZLIB compressed SQL commands') %></div><% end_if %>
		<div class="input"><input id='importtype_auto' name='importtype' value='auto' type="radio" checked="checked"> <% _t('AUTO_DETECT', 'auto detect by file name extension') %></div>
	</div>
	<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only import" aria-disabled="false" type="submit"><span><% _t('IMPORT_BUTTON', 'import') %></span></button>
	
	<div class="ui-state-highlight ui-corner-all" style="width:170px; padding-left:5px;">
		<p>
			<span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>
			<a href="importexport" class="DBP_HELPER"><strong><% _t('HOWTO', 'HOWTO') %>:</strong> <% _t('IMPORT_EXPORT', 'Import/Export') %></a>
		</p>
	</div>
	
	<div id="importexport" class='DBP_HELP' title='andy'>
		<h3><% _t('HOWTO', 'HOWTO') %> <% _t('IMPORT_EXPORT', 'Import/Export') %></h3>
		<p><% _t('HOWTO_LINE1', 'The short answer is: click the export button on the left form to create a backup or upload a backup file in the right form of this tab to restore a beckup.') %></p>
		<p><% _t('HOWTO_LINE2', 'What it does: It only exports the data of your database. The file contains raw SQL statements, one delete statement per table to delete all current records and one insert statement per backed up record to restore it.') %></p>
		<p><% _t('HOWTO_LINE3', 'What it does not do: It does not contain the structure of your database. <b>That said you have to have run dev/build on the code that you are importing your backup to create the schema. If you are migrating databases the projects PHP code has to be identical.</b>') %></p>
		<p><% _t('HOWTO_LINE4', 'In general it should be possible to copy data between different servers and DBMS. You could for example export your MySQL production database and import it to your SQLite development server, provided both run the same code. If you are migrating from on adapter to another pick the appropriate target SQL dialect on export. (There is one limitation to cross adapter migration as of now: migrations to SQL Server do not work because there is no way to determine identity columns.)') %></p>
		<p><% _t('HOWTO_LINE5', 'If your import fails, make sure your backup does not exceed the maximum file size. Use Zlib compression to reduce the size of your dump on export. To bump up the file size limit increase post_max_size and upload_max_filesize in your php.ini') %></p>
	</div>
</form>
