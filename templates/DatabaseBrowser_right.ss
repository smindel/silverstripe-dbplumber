<div class='main'>
	<% if requested(Table)  %>
		$Table
	<% else %>
		$Database
	<% end_if %>
</div>
<div id='ajax_msg' class='ui-corner-all waiting'>msg</div>