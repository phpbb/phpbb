<div align="center"><table width="98%" cellspacing="0" cellpadding="4" border="0">
	<tr>
		<td align="left"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></font></td>
	</tr>
</table></div>

<div align="center"><table width="98%" cellpadding="1" cellspacing="0" border="0">
	<tr>
		<td bgcolor="{T_TH_COLOR1}"><table border="0" cellpadding="3" cellspacing="1" width="100%">
			<tr>
				<td colspan="4" bgcolor="{T_TH_COLOR2}"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td>&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE3}">{INBOX_FOLDER}&nbsp;&nbsp;&nbsp;{SENT_FOLDER}</font></td>
						<td align="right"><a href="{U_POST_NEW_MSG}"><img src="templates/PSO/images/post.gif" border="1"></a></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td width="5%" bgcolor="{T_TH_COLOR3}"></td>
				<td width="20%" bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE2}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><b>{L_FROM_OR_TO}</b></font></td>
				<td width="60%" bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE2}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><b>{L_SUBJECT}</b></font></td>
				<td width="15%" bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE2}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><b>{L_DATE}</b></font></td>
			</tr>
			<!-- BEGIN listrow -->
			<tr>
				<td bgcolor="{listrow.ROW_COLOR}" align="center" valign="middle">{listrow.ICON_FLAG_IMG}</td>
				<td bgcolor="{listrow.ROW_COLOR}" width="5%" valign="middle">&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><a href="{listrow.U_FROM_USER_PROFILE}">{listrow.FROM}</a></font></td>
				<td bgcolor="{listrow.ROW_COLOR}" width="5%" valign="middle">&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><a href="{listrow.U_READ}">{listrow.SUBJECT}</a></font></td>
				<td bgcolor="{listrow.ROW_COLOR}" width="15%" align="center" valign="middle"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{listrow.DATE}</font></td>
			</tr>
			<!-- END listrow -->
			<tr>
				<td colspan="6" bgcolor="{T_TH_COLOR2}"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td width="5" align="left" valign="middle"><a href="{U_POST_NEW_TOPIC}"><img src="templates/PSO/images/post.gif" border="1"></a></td>
						<td align="left" valign="middle">&nbsp;&nbsp;&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></font>&nbsp;</td>
						<td align="right" valign="middle"><font face="{T_FONTFACE2}" size="{T_FONTSIZE2}">{L_GOTO_PAGE}:&nbsp;{PAGINATION}&nbsp;</font></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table></div>

<div align="center"><table align="center" border="0" width="98%">
	<tr>
		<td valign="top"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}"><b>{S_TIMEZONE}</b></font></td>
		<td align="right" nowrap>{JUMPBOX}</td>
	</tr>
</table>