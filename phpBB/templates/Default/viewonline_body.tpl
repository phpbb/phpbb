<tr>
	<td><table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
		<tr>
			<td><table border="0" width="100%" cellpadding="3" cellspacing="1">
				<tr class="tableheader">
					<td colspan="4" align="center"><b>{L_WHOSONLINE}</b></td>
				</tr>
				<tr class="catheader">
					<td width="30%" align="center">&nbsp;{L_USERNAME}&nbsp;</td>
					<td width="20%" align="center">&nbsp;{L_LAST_UPDATE}&nbsp;</td>
					<td width="10%" align="center">&nbsp;{L_LOGGED_ON}&nbsp;</td>
					<td width="40%" align="center">&nbsp;{L_LOCATION}&nbsp;</td>
				</tr>
				<!-- BEGIN userrow -->
				<tr bgcolor="{userrow.ROW_COLOR}" class="tablebody">
					<td width="30%">&nbsp;<a href="profile.{PHPEX}?mode=view&{POST_USER_URL}={userrow.USER_ID}">{userrow.USERNAME}</a>&nbsp;</td>
					<td width="20%" align="center">&nbsp;{userrow.LASTUPDATE}&nbsp;</td>
					<td width="10%" align="center">&nbsp;{userrow.LOGGEDON}&nbsp;</td>
					<td width="40%">&nbsp;<a href="{userow.LOCATION_URL}">&nbsp;{userrow.LOCATION}</a>&nbsp;</td>
				</tr>
				<!-- END userrow -->
			</table></td>
		</tr>
	</table></td>
</tr>