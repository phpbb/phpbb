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

<!-- BEGIN box_size_notice -->
<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td><span class="cattitle">{INBOX_IMG} {INBOX_LINK} &nbsp; {SENTBOX_IMG} {SENTBOX_LINK} &nbsp; {OUTBOX_IMG} {OUTBOX_LINK} &nbsp; {SAVEBOX_IMG} {SAVEBOX_LINK}</span></td>
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
		</table>
	</tr>
</table>
<!-- END box_size_notice -->

<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left" valign="bottom"><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
		<td align="right" valign="bottom" nowrap><span class="gensmall">{L_DISPLAY_MESSAGES}: <select name="msgdays">{S_MSG_DAYS_OPTIONS}</select> <input class="button" type="submit" name="submit_msgdays" value="Go"></span></td>
	</tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="3" cellspacing="1" width="100%">
			<tr>
				<td class="cat" colspan="5"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td align="right">{POST_PM_IMG}</td>
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
				<td class="cat" colspan="5"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td width="5" align="left" valign="top">{POST_PM_IMG}</td>
						<td align="left" valign="top">&nbsp;&nbsp;&nbsp;<span class="gen">{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></span>&nbsp;</td>
						<td align="right" valign="top"><span class="gen">{PAGINATION}&nbsp;</span></td>
						<td align="right" valign="top">{S_HIDDEN_FIELDS}<input class="button" type="submit" name="save" value="Save Marked" />&nbsp;<input class="button" type="submit" name="delete" value="Delete Marked" />&nbsp;<input class="button" type="submit" name="deleteall" value="Delete All" /></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table>

<table width="98%" cellspacing="0" cellpadding="2" border="0" align="center">
	<tr>
		<td colspan="5" align="right"><span class="gensmall"><a href="javascript:select_switch(true);">Mark all</a> :: <a href="javascript:select_switch(false);">Unmark all</a></span></td>
	</tr>
</table></form>

<table width="98%" align="center" border="0" align="center">
	<tr>
		<td valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" nowrap>{JUMPBOX}</td>
	</tr>
</table>
