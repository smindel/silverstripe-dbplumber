<form action='admin/dbplumber/database/export' method="post">
	<div>
		<label for="tablenames"><% _t('TABLES_TO_EXPORT', 'Tables to export') %></label>
		<div class="input">
			<select id="tablenames" name="tables[]" size="10" MULTIPLE>
				<% control Tables %>
					<option value="$Name" selected="selected">$Name</option>
				<% end_control %>
			</select>
		</div>
	</div>
	<div>
		<label><% _t('EXPORT_FORMAT', 'Export Format') %></label>
		<div class="input"><input id='exporttype_backup' name='exporttype' value='backup' type="radio" checked="checked"> <% _t('EXPORT_FORMAT_BACKUP', 'Backup (SQL DELETEs and INSERTs)') %></div>
		<% if HasZlibSupport %><div class="input"><input id='exporttype_compressed' name='exporttype' value='compressed' type="radio"> <% _t('EXPORT_FORMAT_BACKUP_ZIPPED', 'Backup compressed using ZLIB') %></div><% end_if %>
		<!-- <div class="input"><input id='exporttype_open_office' name='exporttype' value='openoffice' type="radio"> <% _t('EXPORT_FORMAT_BACKUP_OPENOFFICE', 'Open Office Spreadsheet (MS Excel, iWorks Numbers, OpenOffice Calc compatible)') %></div> -->
	</div>
	<div>
		<label><% _t('SQL_DIALECT', 'SQL Dialect') %></label>
		<% control Adapters %>
			<div class="input"<% if Available == 0 %> style="color:grey"<% end_if %>><input id='SqlDialect_{$Name}' name='SqlDialect' value='{$Name}' type="radio"<% if Available == 0 %> disabled="disabled"<% end_if %><% if Selected %> checked="checked"<% end_if %>> $Name</div>
		<% end_control %>
	</div>
	<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only export" aria-disabled="false" type="submit"><a href='#'><% _t('EXPORT_BUTTON', 'export') %></a></button>
</form>
