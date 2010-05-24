				<form id='sql_form' method='post' action='{$Link}execute'>
					<textarea name='query'>$Sql.Query</textarea><br />
					<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false" type="submit"><span class="ui-button-text">go</span></button>
				</form>
				<% if Sql %>
					<% control Sql %>
						<% if Error %><p class='ui-state-error ui-corner-all'>$Error</p><% end_if %>
						<% include DatabaseBrowser_right_data %>
					<% end_control %>
				<% end_if %>
