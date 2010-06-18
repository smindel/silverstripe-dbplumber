<form id="recordform" method="post" action="{$DBPLink}record/form">
	<input type="hidden" name="oldid" value="$ID">
	<% control Fields %>
		<div id="div_$Label">
			<label for="update_$Label">$Label</label>
			<% if type == text %>
				<div class="input"><textarea class='$type' name="update_$Label" id="update_$Label">$value</textarea></div>
			<% else_if type == mediumtext %>
				<div class="input"><textarea class='$type' name="update_$Label" id="update_$Label">$value</textarea></div>
			<% else_if type == bool %>
				<div class="input">
					<input class='$type' name="update_$Label" value="0" id="update_{$Label}_0" type="radio" /> false
					<input class='$type' name="update_$Label" value="1" id="update_{$Label}_1" type="radio"<% if value %> selected="selected"<% end_if %>/> true
				</div>
			<% else %>
				<div class="input"><input class='$type' name="update_$Label" value="$value" id="update_$Label" type="text" /></div>
			<% end_if %>
		</div>
	<% end_control %>
</form>