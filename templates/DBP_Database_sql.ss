				<form id='sql_form' method='post' action='admin/dbplumber/database/execute' onsubmit='return false;'>
					<textarea name='query'>$Query</textarea><br />
					<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false" type="submit"><span class="ui-button-text">go</span></button>
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
