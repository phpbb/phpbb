<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left"><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
	</tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<tr>
				<td class="cat" colspan="3" align="center"><span class="gen"><b>{TOTAL_USERS_ONLINE}</b></span><br /><span class="gensmall">This data is based on users active over the past five minutes</span></td>
			</tr>
			<tr>
				<th width="35%">&nbsp;{L_USERNAME}&nbsp;</th>
				<th width="25%">&nbsp;{L_LAST_UPDATE}&nbsp;</th>
				<th width="40%">&nbsp;{L_LOCATION}&nbsp;</th>
			</tr>
			<!-- BEGIN userrow -->
			<tr bgcolor="{userrow.ROW_COLOR}">
				<td width="35%">&nbsp;<span class="gen"><a href="{userrow.U_USER_PROFILE}">{userrow.USERNAME}</a></span>&nbsp;</td>
				<td width="25%" align="center">&nbsp;<span class="gen">{userrow.LASTUPDATE}</span>&nbsp;</td>
				<td width="40%">&nbsp;<span class="gen"><a href="{userrow.U_FORUM_LOCATION}">{userrow.LOCATION}</a></span>&nbsp;</td>
			</tr>
			<!-- END userrow -->
		</table></td>
	</tr>
</table>

<table cellspacing="2" border="0" width="98%" align="center">
	<tr>
		<td width="40%" valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table>
