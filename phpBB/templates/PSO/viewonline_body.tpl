<div align="center"><table width="98%" cellspacing="0" cellpadding="4" border="0">
	<tr>
		<td align="left"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><a href="/">{SITENAME}&nbsp;{L_INDEX}</a></font></td>
	</tr>
</table></div>

<div align="center"><table border="0" cellpadding="1" cellspacing="0" width="98%">
	<tr>
		<td bgcolor="{T_TH_COLOR1}"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
				<td colspan="3" bgcolor="{T_TH_COLOR2}" align="center"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{TOTAL_USERS_ONLINE}</b></font><br /><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">This data is based on users active over the past five minutes.</font></td>
			</tr>
			<tr>
				<td width="35%" bgcolor="{T_TH_COLOR3}" align="center">&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{L_USERNAME}</font>&nbsp;</td>
				<td width="25%" bgcolor="{T_TH_COLOR3}" align="center">&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{L_LAST_UPDATE}</font>&nbsp;</td>
				<td width="40%" bgcolor="{T_TH_COLOR3}" align="center">&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{L_LOCATION}</font>&nbsp;</td>
			</tr>
			<!-- BEGIN userrow -->
			<tr>
				<td bgcolor="{userrow.ROW_COLOR}" width="35%">&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><a href="{userrow.U_USER_PROFILE}">{userrow.USERNAME}</a></font>&nbsp;</td>
				<td bgcolor="{userrow.ROW_COLOR}" width="25%" align="center">&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{userrow.LASTUPDATE}</font>&nbsp;</td>
				<td bgcolor="{userrow.ROW_COLOR}" width="40%">&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><a href="{userrow.U_FORUM_LOCATION}">{userrow.LOCATION}</a></font>&nbsp;</td>
			</tr>
			<!-- END userrow -->
		</table></td>
	</tr>
</table></div>

<div align="center"><table cellspacing="2" border="0" width="98%">
	<tr>
		<td width="40%" valign="top"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}"><b>{S_TIMEZONE}</b></font></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table></div>