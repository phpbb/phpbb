<div align="center"><table width="98%" cellspacing="0" cellpadding="4" border="0">
	<tr><form method="post" action="{S_MSG_DAYS_ACTION}">
		<td align="left" valign="bottom"><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
		<td align="right" valign="bottom" nowrap><span class="gensmall">{L_DISPLAY_MESSAGES}: {S_SELECT_MSG_DAYS} <input type="submit" value="Go"></span></td>
	</form></tr>
</table></div>

<div align="center"><table width="98%" cellpadding="1" cellspacing="0" border="0">
	<tr><form method="post" action="{S_PRIVMSGS_ACTION}">
		<td class="tablebg"><table border="0" cellpadding="3" cellspacing="1" width="100%">
			<tr>
				<td colspan="5" bgcolor="{T_TH_COLOR2}"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td>&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE3}">{INBOX}&nbsp;&nbsp;&nbsp;{SENTBOX}&nbsp;&nbsp;&nbsp;{OUTBOX}&nbsp;&nbsp;&nbsp;{SAVEBOX}</font></td>
						<td align="right">{S_POST_NEW_MSG}</td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td width="5%" bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE2}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><b>{L_FLAG}</b></font></td>
				<td width="20%" bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE2}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><b>{L_FROM_OR_TO}</b></font></td>
				<td width="55%" bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE2}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><b>{L_SUBJECT}</b></font></td>
				<td width="15%" bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE2}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><b>{L_DATE}</b></font></td>
				<td width="5%" bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE2}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><b>{L_MARK}</b></td>
			</tr>
			<!-- BEGIN listrow -->
			<tr>
				<td width="5%"bgcolor="{listrow.ROW_COLOR}" align="center" valign="middle">{listrow.ICON_FLAG_IMG}</td>
				<td width="20%" bgcolor="{listrow.ROW_COLOR}" valign="middle">&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><a href="{listrow.U_FROM_USER_PROFILE}">{listrow.FROM}</a></font></td>
				<td width="55%" bgcolor="{listrow.ROW_COLOR}" valign="middle">&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><a href="{listrow.U_READ}">{listrow.SUBJECT}</a></font></td>
				<td width="15%" bgcolor="{listrow.ROW_COLOR}" align="center" valign="middle"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{listrow.DATE}</font></td>
				<td width="5%" bgcolor="{listrow.ROW_COLOR}" align="center" valign="middle">{listrow.S_DEL_CHECKBOX}</td>
			</tr>
			<!-- END listrow -->
			<tr>
				<td colspan="5" bgcolor="{T_TH_COLOR2}"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td width="5" align="left" valign="middle">{S_POST_NEW_MSG}</td>
						<td align="left" valign="middle">&nbsp;&nbsp;&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></font>&nbsp;</td>
						<td align="right" valign="middle"><font face="{T_FONTFACE2}" size="{T_FONTSIZE2}">{PAGINATION}&nbsp;</font></td>
						<td align="right" valign="middle">{S_HIDDEN_FIELDS}<input type="submit" name="save" value="Save Marked">&nbsp;<input type="submit" name="delete" value="Delete Marked">&nbsp;<input type="submit" name="deleteall" value="Delete All"></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</form></tr>
</table></div>

<div align="center"><table align="center" border="0" width="98%">
	<tr>
		<td valign="top"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}"><b>{S_TIMEZONE}</b></font></td>
		<td align="right" nowrap>{JUMPBOX}</td>
	</tr>
</table>