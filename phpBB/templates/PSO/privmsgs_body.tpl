<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr><form method="post" action="{S_MSG_DAYS_ACTION}">
		<td align="left" valign="bottom"><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
		<td align="right" valign="bottom" nowrap><span class="gensmall">{L_DISPLAY_MESSAGES}: {S_SELECT_MSG_DAYS} <input type="submit" value="Go"></span></td>
	</form></tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr><form method="post" action="{S_PRIVMSGS_ACTION}">
		<td class="tablebg"><table border="0" cellpadding="3" cellspacing="1" width="100%">
			<tr>
				<td class="cat" colspan="5"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td>&nbsp;<span class="cattitle">{INBOX}&nbsp;&nbsp;&nbsp;{SENTBOX}&nbsp;&nbsp;&nbsp;{OUTBOX}&nbsp;&nbsp;&nbsp;{SAVEBOX}</span></td>
						<td align="right">{S_POST_NEW_MSG}</td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<th width="5%">&nbsp;{L_FLAG}&nbsp;</th>
				<th width="20%">&nbsp;{L_FROM_OR_TO}&nbsp;</th>
				<th width="55%">&nbsp;{L_SUBJECT}&nbsp;</th>
				<th width="15%">&nbsp;{L_DATE}&nbsp;</th>
				<th width="5%">&nbsp;{L_MARK}&nbsp;</th>
			</tr>
			<!-- BEGIN listrow -->
			<tr bgcolor="{listrow.ROW_COLOR}">
				<td width="5%" align="center" valign="middle">{listrow.ICON_FLAG_IMG}</td>
				<td width="20%" valign="middle">&nbsp;<span class="gen"><a href="{listrow.U_FROM_USER_PROFILE}">{listrow.FROM}</a></span></td>
				<td width="55%" valign="middle">&nbsp;<span class="gen"><a href="{listrow.U_READ}">{listrow.SUBJECT}</a></span></td>
				<td width="15%" align="center" valign="middle"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{listrow.DATE}</span></td>
				<td width="5%" align="center" valign="middle">{listrow.S_DEL_CHECKBOX}</td>
			</tr>
			<!-- END listrow -->
			<tr>
				<td class="cat" colspan="5"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td width="5" align="left" valign="middle">{S_POST_NEW_MSG}</td>
						<td align="left" valign="middle">&nbsp;&nbsp;&nbsp;<span class="gen">{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></span>&nbsp;</td>
						<td align="right" valign="middle"><span class="gen">{PAGINATION}&nbsp;</span></td>
						<td align="right" valign="middle">{S_HIDDEN_FIELDS}<input type="submit" name="save" value="Save Marked">&nbsp;<input type="submit" name="delete" value="Delete Marked">&nbsp;<input type="submit" name="deleteall" value="Delete All"></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</form></tr>
</table>

<table align="center" border="0" width="98%" align="center">
	<tr>
		<td valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" nowrap>{JUMPBOX}</td>
	</tr>
</table>