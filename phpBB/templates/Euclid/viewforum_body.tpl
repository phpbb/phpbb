
<form method="post" action="{S_POST_DAYS_ACTION}"><table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left" valign="bottom" nowrap="nowrap"><span class="gensmall"><a href="{U_INDEX}">{L_INDEX}</a> -> {FORUM_NAME}</span></td>
		<td align="right" valign="bottom"  nowrap="nowrap"><span class="gensmall">{L_DISPLAY_TOPICS}:&nbsp;{S_SELECT_TOPIC_DAYS}&nbsp;<input class="outsidetable" type="submit" value="{L_GO}" /></span></td>
	</tr>
</table>

<table border="0" cellpadding="0" cellspacing="0" width="98%" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
				<td class="cat" colspan="6"><table width="100%" cellspacing="0" cellpadding="2" border="0">
					<tr>
						<td><span class="cattitle">{FORUM_NAME}</span> &nbsp; <span class="gensmall">{PAGINATION}&nbsp;</span><br /><span class="gensmall">{L_MODERATOR}: {MODERATORS}</span></td>
						<td align="right" valign="top"><table cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td><span class="gensmall">{PAGE_NUMBER}</span></td>
								<td>&nbsp;&nbsp;</td>
								<td align="right" valign="middle"><a href="{U_POST_NEW_TOPIC}"><img src="{IMG_POST}" alt="{L_NEW_TOPIC}" title="{L_NEW_TOPIC}" border="0" /></a></td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<th width="4%" height="25">&nbsp;</th>
				<th height="25">{L_TOPICS}</th>
				<th width="20%" height="25">{L_AUTHOR}</th>
				<th width="8%" height="25">{L_REPLIES}</th>
				<th width="6%" height="25">{L_VIEWS}</th>
				<th width="17%" height="25">{L_LASTPOST}</th>
			</tr>
			<!-- BEGIN topicrow -->
			<tr>
				<td class="row1" align="center" valign="middle">&nbsp;{topicrow.FOLDER}&nbsp;</td>
				<td class="row2">&nbsp;<span class="gensmall">{topicrow.NEWEST_POST_IMG}{topicrow.TOPIC_TYPE}<a href="{topicrow.U_VIEW_TOPIC}">{topicrow.TOPIC_TITLE}</a>&nbsp;{topicrow.GOTO_PAGE}</span></td>
				<td class="row1" align="center" valign="middle"><span class="gen"><a href="{topicrow.U_TOPIC_POSTER_PROFILE}">{topicrow.TOPIC_POSTER}</a></span></td>
				<td class="row2" align="center" valign="middle"><span class="gen">{topicrow.REPLIES}</span></td>
				<td class="row1" align="center" valign="middle"><span class="gen">{topicrow.VIEWS}</span></td>
				<td class="row2" align="center" valign="middle" NOWRAP><span class="gensmall">{topicrow.LAST_POST}</span></td>
			</tr>
			<!-- END topicrow -->
			<!-- BEGIN notopicsrow -->
			<tr>
				<td class="row1" colspan="6" height="30" align="center" valign="middle"><span class="gen">{L_NO_TOPICS}</span></td>
			</tr>
			<!-- END notopicsrow -->
			<tr>
				<td class="cat" colspan="6"><table width="100%" cellspacing="0" cellpadding="2" border="0">
					<tr>
						<td valign="middle"><table cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td><a href="{U_POST_NEW_TOPIC}"><img src="{IMG_POST}" alt="{L_NEW_TOPIC}" title="{L_NEW_TOPIC}" border="0" /></a></td>
								<td>&nbsp;&nbsp;</td>
								<td valign="middle"><span class="gensmall">{PAGE_NUMBER}</span></td>
							</tr>
						</table></td>
						<td align="right" valign="middle"><span class="gensmall">{PAGINATION}</span></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table></form>


<table width="98%" cellspacing="0" cellpadding="2" border="0" align="center">
	<tr>
		<td align="left" valign="top"><table cellspacing="0" cellpadding="1" border="0">
			<tr>
				<td colspan="8"><span class="gensmall"><a href="{U_MARK_READ}">{L_MARK_TOPICS_READ}</a></span><br /><br /></td>
			</tr>
			<tr>
				<td width="20"><img src="{FOLDER_NEW_IMG}" alt="{L_NEW_POSTS}" /></td>
				<td><span class="gensmall">{L_NEW_POSTS}</span></td>
				<td>&nbsp;&nbsp;</td>
				<td width="20"><img src="{FOLDER_IMG}" alt="{L_NO_NEW_POSTS}" /></td>
				<td><span class="gensmall">{L_NO_NEW_POSTS}</span></td>
				<td>&nbsp;&nbsp;</td>
				<td width="20"><img src="{FOLDER_ANNOUNCE_IMG}" alt="{L_ANNOUNCEMENT}" /></td>
				<td><span class="gensmall">{L_ANNOUNCEMENT}</span></td>
			</tr>
			<tr> 
				<td width="20"><img src="{FOLDER_HOT_NEW_IMG}" alt="{L_NEW_POSTS_HOT}" /></td>
				<td><span class="gensmall">{L_NEW_POSTS_HOT}</span></td>
				<td>&nbsp;&nbsp;</td>
				<td width="20"><img src="{FOLDER_HOT_IMG}" alt="{L_NO_NEW_POSTS_HOT}" /></td>
				<td><span class="gensmall">{L_NO_NEW_POSTS_HOT}</span></td>
				<td>&nbsp;&nbsp;</td>
				<td width="20"><img src="{FOLDER_STICKY_IMG}" alt="{L_STICKY}" /></td>
				<td><span class="gensmall">{L_STICKY}</span></td>
			</tr>
			<tr>
				<td width="20"><img src="{FOLDER_LOCKED_NEW_IMG}" alt="{L_NEW_POSTS_TOPIC_LOCKED}" /></td>
				<td><span class="gensmall">{L_NEW_POSTS_LOCKED}</span></td>
				<td>&nbsp;&nbsp;</td>
				<td width="20"><img src="{FOLDER_LOCKED_IMG}" alt="{L_NO_NEW_POSTS_TOPIC_LOCKED}" /></td>
				<td><span class="gensmall">{L_NO_NEW_POSTS_LOCKED}</span></td>
			</tr>
			<tr>
				<td colspan="8"><br /><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
			</tr>
		</table></td>
		<td align="right" valign="top" nowrap="nowrap">{JUMPBOX}<span class="gensmall">{S_AUTH_LIST}</span></td>
	</tr>
</table>
