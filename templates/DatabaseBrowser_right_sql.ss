				<form id='sql_form' method='post' action='{$Link}execute'>
					<textarea name='query'>$Sql.Query</textarea><br />
					<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false" type="submit"><span class="ui-button-text">go</span></button>
				</form>
				<% if Sql %>
					<% control Sql %>
						<% if Message %>
							<div class='ui-state-$Message.type ui-corner-all'><p>$Message.text</p></div>
						<% else %>
							<% include DatabaseBrowser_right_data %>
						<% end_if %>
					<% end_control %>
				<% end_if %>
