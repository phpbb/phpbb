<!-- INCLUDE ../common/lofi/lofi_header.tpl -->

<div class="index">
	<table border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
	<tr>
		<td class="tw100pct tdalignc">
			<table height="40" cellspacing="2" cellpadding="2" border="0">
			<tr valign="middle">
				<td><span class="cattitle">{INBOX} &nbsp;</span></td>
				<td><span class="cattitle">{SENTBOX} &nbsp;</span></td>
				<td><span class="cattitle">{OUTBOX} &nbsp;</span></td>
				<td><span class="cattitle">{SAVEBOX} &nbsp;</span></td>
			</tr>
			</table>
		</td>
		<td class="tdalignr">
			<!-- BEGIN switch_box_size_notice -->
			<table width="175" cellspacing="1" cellpadding="2" border="0" class="bodyline">
			<tr>
				<td colspan="3" width="175" class="row1 tdnw"><span class="gensmall">{BOX_SIZE_STATUS}</span></td>
			</tr>
			<tr>
				<td colspan="3" width="175" class="row2">
					<table cellspacing="0" cellpadding="1" border="0">
						<tr><td bgcolor="#555555"><img src="{SPACER}" width="{INBOX_LIMIT_IMG_WIDTH}" height="8" alt="{INBOX_LIMIT_PERCENT}" /></td></tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="tw33pct row1"><span class="gensmall">0%</span></td>
				<td width="34%" align="center" class="row1"><span class="gensmall">50%</span></td>
				<td width="33%" align="right" class="row1"><span class="gensmall">100%</span></td>
			</tr>
			</table>
			<!-- END switch_box_size_notice -->
		</td>
	</tr>
	</table>

	<br class="clear" />

	<form method="post" name="privmsg_list" action="{S_PRIVMSGS_ACTION}">
		<table class="s2px p2px">
		<tr>
			<td class="tvalignm">{POST_PM}</td>
			<td class="tdalignr tdnw">
				<span class="gensmall">
					{L_DISPLAY_MESSAGES}:
					<select name="msgdays">{S_SELECT_MSG_DAYS}</select>
					<input type="submit" value="{L_GO}" name="submit_msgdays" class="liteoption" />
				</span>
			</td>
		</tr>
		</table>

		<table class="forumline">
		<tr>
			<th width="5%" height="25" nowrap="nowrap">&nbsp;{L_FLAG}&nbsp;</th>
			<th width="55%" nowrap="nowrap">&nbsp;{L_SUBJECT}&nbsp;</th>
			<th width="20%" nowrap="nowrap">&nbsp;{L_FROM_OR_TO}&nbsp;</th>
			<th class="tw15pct tdnw">&nbsp;{L_DATE}&nbsp;</th>
			<th class="tw5pct tdnw">&nbsp;{L_MARK}&nbsp;</th>
		</tr>
		<!-- BEGIN listrow -->
		<tr>
			<td class="{listrow.ROW_CLASS}" width="5%" align="center" valign="middle">{listrow.L_PRIVMSG_FOLDER_ALT}</td>
			<td width="55%" valign="middle" class="{listrow.ROW_CLASS}">&nbsp;<b><a href="{listrow.U_READ}">{listrow.SUBJECT}</a></b></td>
			<td width="20%" valign="middle" align="center" class="{listrow.ROW_CLASS}"><span class="name">&nbsp;<a href="{listrow.U_FROM_USER_PROFILE}" class="name">{listrow.FROM}</a></span></td>
			<td width="15%" align="center" valign="middle" class="{listrow.ROW_CLASS}"><span class="postdetails">{listrow.DATE}</span></td>
			<td width="5%" align="center" valign="middle" class="{listrow.ROW_CLASS}"><span class="postdetails">
			<input type="checkbox" name="mark[]" value="{listrow.S_MARK_ID}" />
			</span></td>
		</tr>
		<!-- END listrow -->
		<!-- BEGIN switch_no_messages -->
		<tr><td class="row1" colspan="5" align="center" valign="middle"><span class="gen">{L_NO_MESSAGES}</span></td></tr>
		<!-- END switch_no_messages -->
		<tr>
			<td class="catBottom" colspan="5" height="28" align="right">
				{S_HIDDEN_FIELDS}
				<input type="submit" name="save" value="{L_SAVE_MARKED}" class="mainoption" />&nbsp;
				<input type="submit" name="delete" value="{L_DELETE_MARKED}" class="liteoption" />&nbsp;
				<input type="submit" name="deleteall" value="{L_DELETE_ALL}" class="liteoption" />
			</td>
		</tr>
		</table>

		<table class="s2px p2px">
		<tr>
			<td class="tvalignm tdnw">{POST_PM}<br /></td>
			<td class="tw100pct tvalignm"><span class="nav"></span></td>
			<td class="tdalignr tdnw"><a href="#" onclick="setCheckboxes('privmsg_list', 'mark[]', true); return false;" class="gensmall">{L_MARK_ALL}</a>&nbsp;&bull;&nbsp;<a href="#" onclick="setCheckboxes('privmsg_list', 'mark[]', false); return false;" class="gensmall">{L_UNMARK_ALL}</a><br class="mb5" /><span class="pagination">{PAGINATION}<br /></span><span class="gensmall">{S_TIMEZONE}</span></td>
		</tr>
		</table>
		{PAGE_NUMBER}
	</form>

	<table><tr><td class="tdalignr">{JUMPBOX}</td></tr></table>
</div>
<br />

<!-- INCLUDE ../common/lofi/lofi_footer.tpl -->