<tr>
	<td><table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
		<tr>
			<td><table border="0" width="100%" cellpadding="3" cellspacing="1">
				<tr class="tableheader">
					<td colspan="4" align="center"><b>{L_REGISTERED_ONLINE}</b></td>
				</tr>
				<tr class="catheader">
					<td width="30%" align="center">&nbsp;{L_USERNAME}&nbsp;</td>
					<td width="20%" align="center">&nbsp;{L_LASTUPDATE}&nbsp;</td>
					<td width="10%" align="center">&nbsp;{L_LOGGED_ON}&nbsp;</td>
					<td width="40%" align="center">&nbsp;{L_LOCATION}&nbsp;</td>
				</tr>
				<!-- BEGIN userregrow -->
				<tr bgcolor="{userregrow.ROW_COLOR}" class="tablebody">
					<td width="30%">&nbsp;<a href="profile.{PHPEX}?mode=view&{POST_USER_URL}={userregrow.USER_ID}">{userregrow.USERNAME}</a>&nbsp;</td>
					<td width="20%" align="center">&nbsp;{userregrow.LASTUPDATE}&nbsp;</td>
					<td width="10%" align="center">&nbsp;{userregrow.LOGGEDON}&nbsp;</td>
					<td width="40%">&nbsp;<a href="{userregrow.LOCATION_URL}">{userregrow.LOCATION}</a>&nbsp;</td>
				</tr>
				<!-- END userregrow -->
			</table></td>
		</tr>
	</table></td>
</tr>