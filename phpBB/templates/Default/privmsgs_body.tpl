<tr>
	<td> 
	<table width="100%" cellpadding="1" cellspacing="0" border="0">
	<tr>
		<td bgcolor="{T_TH_COLOR1}"><table border="0" cellpadding="0" cellspacing="1" width="100%">
			<tr>
				<td colspan="4" bgcolor="{T_TH_COLOR2}"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr class="catheader">
						<td>&nbsp;{INBOX_FOLDER}&nbsp;&nbsp;&nbsp;{SENT_FOLDER}</td>
						<td align="right"><a href="{U_POST_NEW_MSG}"><img src="templates/PSO/images/post.gif" border="0"></a></td>
					</tr>
				</table></td>
			</tr>
			<tr class="tableheader">
				<td width="5%"></td>
				<td width="20%" align="center"><b>{L_FROM_OR_TO}</b></td>
				<td width="60%" align="center"><b>{L_SUBJECT}</b></td>
				<td width="15%" align="center"><b>{L_DATE}</b></td>
			</tr>
			<!-- BEGIN listrow -->
			<tr class="tablebody">
				<td bgcolor="{listrow.ROW_COLOR}" align="center" valign="middle">{listrow.ICON_FLAG_IMG}</td>
				<td bgcolor="{listrow.ROW_COLOR}" width="5%" valign="middle">&nbsp;<a href="{listrow.U_FROM_USER_PROFILE}">{listrow.FROM}</a></td>
				<td bgcolor="{listrow.ROW_COLOR}" width="5%" valign="middle">&nbsp;<a href="{listrow.U_READ}">{listrow.SUBJECT}</a></td>
				<td bgcolor="{listrow.ROW_COLOR}" width="15%" align="center" valign="middle">{listrow.DATE}</td>
			</tr>
			<!-- END listrow -->
			<tr>
				<td colspan="6" bgcolor="{T_TH_COLOR2}"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr class="catheader">
						<td width="5" align="left" valign="middle"><a href="{U_POST_NEW_TOPIC}"><img src="templates/PSO/images/post.gif" border="0"></a></td>
						<td align="left" valign="middle">&nbsp;&nbsp;&nbsp;{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b>&nbsp;</td>
						<td align="right" valign="middle">{L_GOTO_PAGE}:&nbsp;{PAGINATION}&nbsp;</td>
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
</td>
</tr>