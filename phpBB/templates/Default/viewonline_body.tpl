<tr>
	<td><table border="0" align="right" width="30%" bgcolor="#000000" cellpadding="0" cellspacing="1">
		<tr>
			<td><table border="0" width="100%" bgcolor="#CCCCCC" cellpadding="1" cellspacing="1">
				<tr>
					<td align="right" style="{font-size: 8pt;}">{L_POSTEDTOTAL} -<b>{TOTAL_POSTS}</b>- {L_MESSAGES}.<br> {L_WEHAVE} <b>{TOTAL_USERS}</b> {L_REGUSERS}.<br>{L_NEWESTUSER} <b><a href="{U_NEWEST_USER_PROFILE}">{NEWEST_USER}</a></b></td>
				</tr>
			</table></td>
		</tr>
	</table></td>
</tr>
<tr>
	<td><table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
		<tr>
			<td><table border="0" width="100%" cellpadding="3" cellspacing="1">
				<tr class="tableheader">
					<td colspan="4" align="center"><b>There are {ACTIVE_USERS} logged in users and {GUEST_USERS} guest users browsing this board.</b><br />This data is based on users active over the past five minutes.</td>
				</tr>
				<tr class="catheader">
					<td width="30%" align="center">&nbsp;{L_USERNAME}&nbsp;</td>
					<td width="30%" align="center">&nbsp;{L_LASTUPDATE}&nbsp;</td>
					<td width="40%" align="center">&nbsp;{L_LOCATION}&nbsp;</td>
				</tr>
				<!-- BEGIN userrow -->
				<tr bgcolor="{userrow.ROW_COLOR}" class="tablebody">
					<td width="30%">&nbsp;{userrow.USERNAME}&nbsp;</td>
					<td width="30%" align="center">&nbsp;{userrow.LASTUPDATE}&nbsp;</td>
					<td width="40%"><a href="{userguestrow.LOCATION_URL}">&nbsp;{userrow.LOCATION}&nbsp;</a></td>
				</tr>
				<!-- END userrow -->
			</table></td>
		</tr>
	</table></td>
</tr>
<tr>
	<td align="center"><table border="0" width="100%" cellpadding="0" cellspacing="1">
		<tr>
			<td style="{font-size: 8pt;}" align="left" valign="top"><b>{S_TIMEZONE}</b></td>
			<td style="{font-size: 8pt;}" align="right" ><table cellpadding="0" cellspacing="1" border="0" bgcolor="#000000">
				<tr>
					<td bgcolor="#CCCCCC"><table width="100%" cellpadding="1" cellspacing="1" border="0">
						<tr>
							<td style="{font-size:8pt; height:55px;}" align="right">{JUMPBOX}</td>
						</tr>
					</table></td>
				</tr>
			</table></td>
		</tr>
	</table></td>
</tr>