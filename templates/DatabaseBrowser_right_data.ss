			<% if Rows %>
				<% if Stats.total %>
					<p>
						Showing rows $Stats.start - $Stats.end ($Stats.total total)
					</p>
					<p>
						<% if Stats.firstlink %>
							<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pagination first-page" aria-disabled="false"><a href='{$Link}?{$Stats.firstlink}&{$Stats.orderlink}'>&nbsp;</a></button>
							<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pagination prev-page"  aria-disabled="false"><a href='{$Link}?{$Stats.prevlink}&{$Stats.orderlink}'>&nbsp;</a></button>
						<% end_if %>
						<% if Stats.lastlink %>
							<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pagination next-page" aria-disabled="false"><a href='{$Link}?{$Stats.nextlink}&{$Stats.orderlink}'>&nbsp;</a></button>
							<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pagination last-page" aria-disabled="false"><a href='{$Link}?{$Stats.lastlink}&{$Stats.orderlink}'>&nbsp;</a></button>
						<% end_if %>
					</p>
				<% end_if %>
				<table class='kike'>
						<colgroup><% control Fields %>
							<col /><% end_control %>
						</colgroup>

					<thead>
						<tr>
							<% control Fields %>
								<% if Table %>
									<td class='fieldname<% if Ordered %> order$Ordered<% end_if %>'><a href='$Table.Link?start=$Table.requestVar(start)&orderby=$Name&orderdir=<% if Ordered == ASC %>DESC<% else %>ASC<% end_if %>'>$Name</a></td>
								<% else %>
									<td>$Name</td>
								<% end_if %>
							<% end_control %>
						</tr>
					</thead>
					<tbody>
						<% control Rows %>
							<tr class='<% if Even %>even<% else %>odd<% end_if %>'>
								<% control Cells %>
									<td class='$Type' rel='$Context'>$Val</td>
								<% end_control %>
							</tr>
						<% end_control %>
					</tbody>
				</table>
			<% else %>
				<p>No records</p>
			<% end_if %>