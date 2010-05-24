<div class='main'>
	<% if Table %>
		<% include DatabaseBrowser_right_table %>
	<% else %>
		<% include DatabaseBrowser_right_db %>
	<% end_if %>
</div>
<div id='ajax_msg' class='ui-corner-all waiting'>msg</div>