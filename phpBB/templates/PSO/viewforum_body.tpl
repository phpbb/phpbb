<form method="post" action="{S_POST_DAYS_ACTION}"><table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left" valign="bottom" nowrap="nowrap"><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a> -> {FORUM_NAME}</span></td>
		<td align="right" valign="bottom"  nowrap="nowrap"><span class="gensmall">{L_DISPLAY_TOPICS}:&nbsp;{S_SELECT_TOPIC_DAYS}&nbsp;<input type="submit"  value="{L_GO}" /></span></td>
	</tr>
</table>

<table border="0" cellpadding="1" cellspacing="0" width="98%" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
				<td class="cat" colspan="6"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td><span class="cattitle">{FORUM_NAME}</span><br /><span class="gensmall">{L_MODERATOR}: {MODERATORS}</span></td>
						<td align="right"><a href="{U_POST_NEW_TOPIC}"><img src="{IMG_POST}" border="1" alt="{L_NEW_TOPIC}" /></a></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<th width="4%">&nbsp;</th>
				<th>&nbsp;{L_TOPICS}&nbsp;</th>
				<th width="8%">&nbsp;{L_REPLIES}&nbsp;</th>
				<th width="20%">&nbsp;{L_AUTHOR}&nbsp;</th>
				<th width="6%">&nbsp;{L_VIEWS}&nbsp;</th>
				<th width="17%">&nbsp;{L_LASTPOST}&nbsp;</th>
			</tr>
			<!-- BEGIN topicrow -->
			<tr>
				<td class="row1" align="center" valign="middle">&nbsp;{topicrow.FOLDER}&nbsp;</td>
				<td class="row2">&nbsp;<span class="gensmall">{topicrow.TOPIC_TYPE}<a href="{topicrow.U_VIEW_TOPIC}">{topicrow.TOPIC_TITLE}</a>&nbsp;{topicrow.GOTO_PAGE}</span></td>
				<td class="row1" align="center" valign="middle"><span class="gen">{topicrow.REPLIES}</span></td>
				<td class="row2" align="center" valign="middle"><span class="gen"><a href="{topicrow.U_TOPIC_POSTER_PROFILE}">{topicrow.TOPIC_POSTER}</a></span></td>
				<td class="row1" align="center" valign="middle"><span class="gen">{topicrow.VIEWS}</span></td>
				<td class="row2" align="center" valign="middle"><span class="gensmall">{topicrow.LAST_POST}</span></td>
			</tr>
			<!-- END topicrow -->
			<!-- BEGIN notopicsrow -->
			<tr>
				<td class="row1" colspan="6" height="30" align="center" valign="middle"><span class="gen">{L_NO_TOPICS}</span></td>
			</tr>
			<!-- END notopicsrow -->
			<tr>
				<td class="cat" colspan="6"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td width="5" align="left" valign="middle"><a href="{U_POST_NEW_TOPIC}"><img src="{IMG_POST}" border="1" alt="{L_NEW_TOPIC}" /></a></td>
						<td align="left" valign="middle">&nbsp;&nbsp;&nbsp;<span class="gen">{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></span>&nbsp;</td>
						<td align="right" valign="middle"><span class="gen">{PAGINATION}&nbsp;</span></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<table width="98%" cellspacing="2" border="0" align="center">
	<tr>
		<td align="left" valign="top"><table cellspacing="0" border="0">
			<tr>
				<td width="20" align="center"><img src="images/folder_new.gif" alt="{L_NEWPOSTS}" /></td>
				<td><span class="gensmall">{L_NEWPOSTS}</span></td>
				<td>&nbsp;&nbsp;</td>
				<td width="20" align="center"><img src="images/folder.gif" alt="{L_NONEWPOSTS}" /></td>
				<td><span class="gensmall">{L_NONEWPOSTS}</span></td>
				<td>&nbsp;&nbsp;</td>
				<td width="20" align="center"><img src="images/folder_lock.gif" alt="{L_FORUM_LOCKED}" /></td>
				<td><span class="gensmall">{L_TOPIC_IS_LOCKED}</span></td>
			</tr>
			<tr>
				<td colspan="8"><br /><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
			</tr>
		</table></td>
		<td align="right" valign="top" nowrap="nowrap">{JUMPBOX}<span class="gensmall">{S_AUTH_LIST}</span></td>
	</tr>
</table>
