
<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left"><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
	</tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<tr>
				<td class="cat" colspan="3" align="center"><span class="gen"><b>{TOTAL_USERS_ONLINE}</b></span><br /><span class="gensmall">{L_ONLINE_EXPLAIN}</span></td>
			</tr>
			<tr>
				<th width="35%">&nbsp;{L_USERNAME}&nbsp;</th>
				<th width="25%">&nbsp;{L_LAST_UPDATE}&nbsp;</th>
				<th width="40%">&nbsp;{L_LOCATION}&nbsp;</th>
			</tr>
			<!-- BEGIN reguserrow -->
			<tr bgcolor="{reguserrow.ROW_COLOR}">
				<td width="35%">&nbsp;<span class="gen"><a href="{reguserrow.U_USER_PROFILE}">{reguserrow.USERNAME}</a></span>&nbsp;</td>
				<td width="25%" align="center">&nbsp;<span class="gen">{reguserrow.LASTUPDATE}</span>&nbsp;</td>
				<td width="40%">&nbsp;<span class="gen"><a href="{reguserrow.U_FORUM_LOCATION}">{reguserrow.LOCATION}</a></span>&nbsp;</td>
			</tr>
			<!-- END reguserrow -->
		</table></td>
	</tr>
</table>

<br clear="all" />

<table cellspacing="2" border="0" width="98%" align="center">
	<tr>
		<td width="40%" valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" valign="top" nowrap="nowrap">{JUMPBOX}</td>
	</tr>
</table>
