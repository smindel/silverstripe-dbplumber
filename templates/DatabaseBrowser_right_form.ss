<form id="recordform" method="post" action="$Link">
	<input type="hidden" name="oldid" value="$ID">
	<% control Fields %>
		<div id="div_$Name">
			<label for="update_$Name">$Name</label>
			<% if type == text %>
				<div class="input"><textarea class='$type' name="update_$Name" id="update_$Name">$value</textarea></div>
			<% else_if type == mediumtext %>
				<div class="input"><textarea class='$type' name="update_$Name" id="update_$Name">$value</textarea></div>
			<% else_if type == bool %>
				<div class="input">
					<input class='$type' name="update_$Name" value="0" id="update_{$Name}_0" type="radio" /> false
					<input class='$type' name="update_$Name" value="1" id="update_{$Name}_1" type="radio"<% if value %> selected="selected"<% end_if %>/> true
				</div>
			<% else %>
				<div class="input"><input class='$type' name="update_$Name" value="$value" id="update_$Name" type="text" /></div>
			<% end_if %>
		</div>
	<% end_control %>
</form>