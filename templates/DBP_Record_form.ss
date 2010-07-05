<form id="recordform" method="post" action="{$DBPLink}record/save/{$Table}.{$ID}" onsubmit="return false">
	<fieldset>
		<input type="hidden" id="oldid" name="oldid" value="{$Table}<% if ID %>.$ID<% end_if %>" />
		<% control Cells %>
			<div id="div_$Column.Label">
				<label for="update_$Column.Label">$Column.Label</label>
				<% if Column.isText %>
					<div class="input"><textarea class='$Column.type' name="update_$Column.Label" rows="3" cols="40" id="update_$Column.Label">$Value.raw</textarea></div>
				<% else_if Column.isBool %>
					<div class="input">
						<input class='$Column.type' name="update_$Column.Label" value="0" id="update_{$Column.Label}_0" type="radio"<% if Value != 1 %> checked="checked"<% end_if %> /> false
						<input class='$Column.type' name="update_$Column.Label" value="1" id="update_{$Column.Label}_1" type="radio"<% if Value %> checked="checked"<% end_if %> /> true
					</div>
				<% else %>
					<div class="input"><input class='$Column.type' name="update_$Column.Label" value="$Value.raw" id="update_$Column.Label" type="text" /></div>
				<% end_if %>
			</div>
		<% end_control %>
		<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only saverecord" aria-disabled="false" type="submit"><span><% if ID %>update<% else %>insert<% end_if %></span></button>
	</fieldset>
</form>