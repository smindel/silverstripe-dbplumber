<form id="recordform" method="post" action="{$DBPLink}record/form/{$Table}<% if ID %>.$ID<% end_if %>">
	<input type="hidden" id="oldid" name="oldid" value="{$Table}<% if ID %>.$ID<% end_if %>">
	<% control Cells %>
		<div id="div_$Column.Label">
			<label for="update_$Column.Label">$Column.Label</label>
			<% if Column.isText %>
				<div class="input"><textarea class='$Column.type' name="update_$Column.Label" id="update_$Column.Label">$Value</textarea></div>
			<% else_if Column.isBool %>
				<div class="input">
					<input class='$Column.type' name="update_$Column.Label" value="0" id="update_{$Column.Label}_0" type="radio"<% if Value != 1 %> checked="checked"<% end_if %>/> false
					<input class='$Column.type' name="update_$Column.Label" value="1" id="update_{$Column.Label}_1" type="radio"<% if Value %> checked="checked"<% end_if %>/> true
				</div>
			<% else %>
				<div class="input"><input class='$Column.type' name="update_$Column.Label" value="$Value" id="update_$Column.Label" type="text" /></div>
			<% end_if %>
		</div>
	<% end_control %>
	<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only saverecord" aria-disabled="false"><a href='{$DBPLink}record/save/{$Table}.{$ID}' onclick='return false'><% if ID %>update<% else %>insert<% end_if %></a></button>
</form>