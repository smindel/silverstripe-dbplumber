			<% if Records %>
				<% if Pagination.total %>
					<p class='table-tools'>
						Showing records $Pagination.start - $Pagination.end ($Pagination.total total)

						<% if Records.MoreThanOnePage %>
							<% if Records.PrevLink %>
								<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pagination first-page" aria-disabled="false"><span><a href='{$DBPLink}table/index/$Name?{$Pagination.firstlink}&orderby={$Pagination.orderby}&orderdir={$Pagination.orderdir}'>first</a></span></button>
								<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pagination prev-page"  aria-disabled="false"><span><a href='{$DBPLink}table/index/$Name?{$Pagination.prevlink}&orderby={$Pagination.orderby}&orderdir={$Pagination.orderdir}'>prev</a></span></button>
							<% end_if %>

							<% if Records.NextLink %>
								<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pagination next-page" aria-disabled="false"><span><a href='{$DBPLink}table/index/$Name?{$Pagination.nextlink}&orderby={$Pagination.orderby}&orderdir={$Pagination.orderdir}'>next</a></span></button>
								<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pagination last-page" aria-disabled="false"><span><a href='{$DBPLink}table/index/$Name?{$Pagination.lastlink}&orderby={$Pagination.orderby}&orderdir={$Pagination.orderdir}'>last</a></span></button>
							<% end_if %>
						<% end_if %>

						<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only crud-records delete-records" disabled="disabled"><span><a href='{$DBPLink}record/delete'>delete</a></span></button>
					</p>
				<% end_if %>
				<form action="#">
					<fieldset>
						<input type='hidden' id='url' name='url' value='admin/dbplumber/table/index/$Name' />
						<input type='hidden' id='table' name='table' value='$Name' />
						<input type='hidden' id='start' name='start' value='$Pagination.start' />
						<input type='hidden' id='orderby' name='orderby' value='$Pagination.orderby' />
						<input type='hidden' id='orderdir' name='orderdir' value='$Pagination.orderdir' />
					</fieldset>
				</form>
				<table class='kike'>
					<colgroup><% control Fields %>
						<col /><% end_control %>
					</colgroup>

					<thead>
						<tr>
							<% control Fields %>
								<% if Table %>
									<td class='fieldname<% if Ordered %> order$Ordered<% end_if %>'><a href='{$Table.DBPLink}table/index/{$Table.Name}?start=$Table.requestVar(start)&orderby=$Label&orderdir=<% if Ordered == ASC %>DESC<% else %>ASC<% end_if %>'>$Label</a></td>
								<% else %>
									<td>$Label</td>
								<% end_if %>
							<% end_control %>
						</tr>
					</thead>
					<tbody>
						<% control Records %>
							<tr class='<% if Even %>even<% else %>odd<% end_if %>'<% if Table %> id='{$Table}.{$ID}'<% end_if %>>
								<% control Cells %>
									<td class='$Column.type'>$Value.truncated</td>
								<% end_control %>
							</tr>
						<% end_control %>
					</tbody>
				</table>
			<% else %>
				<p>No records</p>
			<% end_if %>