				<form id='sql_form' method='post' action='admin/dbplumber/database/execute' return='return false'>
					<textarea name='query' class='expand50-250'>$Query</textarea><br />
					<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" aria-disabled="false" type="submit"><a href='admin/dbplumber/database/execute' class="ui-button-text">go</a></button>
					<input type="checkbox" name="indent" id="indent"> indent SQL commands for improved readability
				</form>
				<% if Query %>
					<% if Records %>

						<table class='kike'>
							<colgroup><% control Fields %>
								<col /><% end_control %>
							</colgroup>

							<thead>
								<tr>
									<% control Fields %>
										<td>$Label</td>
									<% end_control %>
								</tr>
							</thead>
							<tbody>
								<% control Records %>
									<tr class='<% if Even %>even<% else %>odd<% end_if %>'>
										<% control Cells %>
											<td class='$Column.type'>$Value.truncated</td>
										<% end_control %>
									</tr>
								<% end_control %>
							</tbody>
						</table>
					<% else %>
						<% if Message %>
							<% control Message %>
								<div class='ui-state-$type ui-corner-all'><p>$text</p></div>
							<% end_control %>
						<% end_if %>
					<% end_if %>
				<% end_if %>
