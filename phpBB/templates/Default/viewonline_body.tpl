<tr>
	<td><table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
		<tr>
			<td><table border="0" width="100%" cellpadding="3" cellspacing="1">
				<tr class="tableheader">
					<td colspan="2" align="center"><b>{L_WHOSONLINE}</b></td>
				</tr>
				<tr class="catheader">
					<td align="center">{L_USERNAME}</td>
					<td align="center">{L_LOCATION}</td>
				</tr>
				<!-- BEGIN userrow -->
				<tr bgcolor="{userrow.ROW_COLOR}" class="tablebody">
					<td><a href="profile.{PHPEX}?mode=view&{POST_USER_URL}={userrow.USER_ID}">{userrow.USERNAME}</a></td>
					<td><a href="{userrow.LOCATION_URL}">{userrow.LOCATION}</a></td>
				</tr>
				<!-- END userrow -->
			</table></td>
		</tr>
	</table></td>
</tr>