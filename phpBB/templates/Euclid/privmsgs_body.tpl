
<script language="Javascript" type="text/javascript">
	//
	// Should really check the browser to stop this whining ...
	//
	function select_switch(status)
	{
		for (i = 0; i < document.privmsg_list.length; i++)
		{
			document.privmsg_list.elements[i].checked = status;
		}
	}
</script>

<form method="post" name="privmsg_list" action="{S_PRIVMSGS_ACTION}">

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

<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left" valign="bottom"><span class="gensmall"><a href="{U_INDEX}">{L_INDEX}</a></span></td>
		<td align="right" valign="bottom" nowrap><span class="gensmall">{L_DISPLAY_MESSAGES}: <select name="msgdays">{S_MSG_DAYS_OPTIONS}</select> <input class="outsidetable" type="submit" name="submit_msgdays" value="{L_GO}"></span></td>
	</tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="3" cellspacing="1" width="100%">
			<tr>
				<td class="cat" colspan="5"><table width="100%" cellspacing="0" cellpadding="2" border="0">
					<tr>
						<td valign="middle"><table cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td valign="top">{S_HIDDEN_FIELDS}<input class="liteoptiontable" type="submit" name="save" value="{L_SAVE_MARKED}" />&nbsp;<input class="liteoptiontable" type="submit" name="delete" value="{L_DELETE_MARKED}" />&nbsp;<input class="liteoptiontable" type="submit" name="deleteall" value="{L_DELETE_ALL}" /></td>
								<td>&nbsp;&nbsp;</td>
								<td valign="middle"><span class="gensmall">{PAGINATION}</span></td>
							</tr>
						</table></td>
						<td align="right" valign="middle"><table cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td valign="middle"><span class="gensmall">{PAGE_NUMBER}</td>
								<td>&nbsp;&nbsp;</td>
								<td valign="middle">{POST_PM_IMG}</td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<th width="5%" height="25">{L_FLAG}</th>
				<th width="20%">{L_FROM_OR_TO}</th>
				<th width="55%">{L_SUBJECT}</th>
				<th width="15%">{L_DATE}</th>
				<th width="5%">{L_MARK}</th>
			</tr>
			<!-- BEGIN listrow -->
			<tr bgcolor="{listrow.ROW_COLOR}">
				<td width="5%" align="center" valign="middle">{listrow.ICON_FLAG_IMG}</td>
				<td width="20%" valign="middle">&nbsp;<span class="gen"><a href="{listrow.U_FROM_USER_PROFILE}">{listrow.FROM}</a></span></td>
				<td width="55%" valign="middle">&nbsp;<span class="gen"><a href="{listrow.U_READ}">{listrow.SUBJECT}</a></span></td>
				<td width="15%" align="center" valign="middle"><span class="gensmall">{listrow.DATE}</span></td>
				<td width="5%" align="center" valign="middle"><input type="checkbox" name="mark[]" value="{listrow.S_MARK_ID}" /></td>
			</tr>
			<!-- END listrow -->
			<!-- BEGIN nomessages -->
			<tr>
				<td class="row1" colspan="5" align="center" valign="middle"><span class="gen">{L_NO_MESSAGES}</span></td>
			</tr>
			<!-- END nomessages -->
			<tr>
				<td class="cat" colspan="5"><table width="100%" cellspacing="0" cellpadding="2" border="0">
					<tr>
						<td valign="middle"><table cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td valign="middle">{POST_PM_IMG}</td>
								<td>&nbsp;&nbsp;</td>
								<td valign="middle"><span class="gensmall">{PAGE_NUMBER}</td>
							</tr>
						</table></td>
						<td align="right" valign="middle"><table cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td valign="middle"><span class="gensmall">{PAGINATION}</span></td>
								<td>&nbsp;&nbsp;</td>
								<td valign="top">{S_HIDDEN_FIELDS}<input class="liteoptiontable" type="submit" name="save" value="{L_SAVE_MARKED}" />&nbsp;<input class="liteoptiontable" type="submit" name="delete" value="{L_DELETE_MARKED}" />&nbsp;<input class="liteoptiontable" type="submit" name="deleteall" value="{L_DELETE_ALL}" /></td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table>

<table width="98%" cellspacing="0" cellpadding="2" border="0" align="center">
	<tr>
		<td colspan="5" align="right"><span class="gensmall"><a href="javascript:select_switch(true);">{L_MARK_ALL}</a> :: <a href="javascript:select_switch(false);">{L_UNMARK_ALL}</a></span></td>
	</tr>
</table></form>

<table width="98%" align="center" border="0" align="center">
	<tr>
		<td valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" nowrap>{JUMPBOX}</td>
	</tr>
</table>
