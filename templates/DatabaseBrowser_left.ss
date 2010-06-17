<% control Database %>
	<h1 id='lefthead'><a href='$Link'>$Name</a></h1>
	<ul id='dbb_table_list'>
		<% control Tables %>
			<li<% if Selected %> class='selected'<% end_if %>><a href='{$DBPLink}table/show/$Name'>$Name</a></li>
		<% end_if %>
	</ul>
<% end_control %>