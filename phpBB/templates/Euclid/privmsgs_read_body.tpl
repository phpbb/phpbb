
<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td><span class="cattitle">{INBOX_IMG} {INBOX_LINK} &nbsp; {SENTBOX_IMG} {SENTBOX_LINK} &nbsp; {OUTBOX_IMG} {OUTBOX_LINK} &nbsp; {SAVEBOX_IMG} {SAVEBOX_LINK}</span></td>
		<!-- BEGIN box_size_notice -->
		<td><table width="200" cellspacing="0" cellpadding="0" border="0" align="right">
			<tr>
				<td colspan="3" width="100%"><span class="gensmall">{BOX_SIZE_STATUS}</span></td>
			</tr>
			<tr>
				<td colspan="3" width="100%" class="row1"><table cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td class="cat"><img src="images/spacer.gif" width="{INBOX_LIMIT_IMG_WIDTH}" height="20" alt="{INBOX_LIMIT_PERCENT}" /></td>
				</table></td>
			</tr>
			<tr>
				<td width="33%"><span class="gensmall">0%</span></td>
				<td width="34%" align="center"><span class="gensmall">50%</span></td>
				<td width="33%" align="right"><span class="gensmall">100%</span></td>
			</tr>
		</table></td>
		<!-- END box_size_notice -->
	</tr>
</table>

<br clear="all" />

<form method="post" action="{S_PRIVMSGS_ACTION}">

<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left"><span class="gensmall"><a href="{U_INDEX}">{L_INDEX}</a></span></td>
	</tr>
</table>

<table width="98%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
		        <td class="cat" height="30" align="right">{QUOTE_PM_IMG} {REPLY_PM_IMG} {EDIT_PM_IMG}</td>
			</tr>
			<tr>
				<th height="25">{BOX_NAME} :: {L_MESSAGE}</th>
			</tr>
		</table></td>
	</tr>
	<tr>
		<td class="cat" height="2"><img src="images/spacer.gif" height="2"></td>
	</tr>
	<tr>
		<td class="tablebg"><table width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<td class="row2" align="left"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td width="10%" align="left"><span class="gen"><b>{L_SUBJECT}:</b></span></td>
						<td align="left"><span class="gen">{POST_SUBJECT}</span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td class="row1" align="left"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td width="10%" align="left"><span class="gen"><b>{L_POSTED}:</b></span></td>
						<td align="left"><span class="gen">{POST_DATE}</span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td class="row2" align="left"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td width="10%" align="left"><span class="gen"><b>{L_FROM}:</b></span></td>
						<td align="left"><span class="gen">{MESSAGE_FROM}</span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td class="row1" align="left"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td width="10%" align="left"><span class="gen"><b>{L_TO}:</b></span></td>
						<td align="left"><span class="gen">{MESSAGE_TO}</span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td class="row2"><span class="gen">{MESSAGE}</span></td>
			</tr>
			<tr>
				<td class="row2" height="20" valign="middle">{PROFILE_IMG} {EMAIL_IMG} {SEARCH_IMG} {WWW_IMG} {ICQ_ADD_IMG} {AIM_IMG} {YIM_IMG} {MSN_IMG}</td>
			</tr>
		</table></td>
	</tr>
	<tr>
		<td class="cat" height="2"><img src="images/spacer.gif" height="2"></td>
	</tr>
	<tr>
		<td class="tablebg"><table width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<td class="cat" height="30"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td>{QUOTE_PM_IMG} {REPLY_PM_IMG} {EDIT_PM_IMG}</td>
						<td align="right" valign="middle">{S_HIDDEN_FIELDS}<input class="liteoptiontable" type="submit" name="save" value="{L_SAVE_MSG}">&nbsp;<input class="liteoptiontable" type="submit" name="delete" value="{L_DELETE_MSG}"></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<table width="98%" cellspacing="2" border="0" align="center">
	<tr>
		<td width="40%" valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table>
