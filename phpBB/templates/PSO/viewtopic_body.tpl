<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left"><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}">{FORUM_NAME}</a> -> {TOPIC_TITLE}</span></td>
		<td align="right" valign="bottom" nowrap="nowrap"><span class="gen"> <a href="{U_VIEW_OLDER_TOPIC}">{L_VIEW_PREVIOUS_TOPIC}</a> :: <a href="{U_VIEW_NEWER_TOPIC}">{L_VIEW_NEXT_TOPIC}</a> </span></td>
	</tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
		        <td class="cat" colspan="2"><table width="100%" cellspacing="0" cellpadding="0" border="0"> 
	                <tr>
               			<td><span class="cattitle"><b>{TOPIC_TITLE}</b></span></td> 
               			<td align="right" valign="middle"><a href="{U_POST_NEW_TOPIC}"><img src="{IMG_POST}" border="0" alt="{L_TOPIC_POST}" /></a>&nbsp;&nbsp;<a href="{U_POST_REPLY_TOPIC}"><img src="{IMG_REPLY}" border="0" alt="{L_TOPIC_REPLY}" /></a></td>  
	               </tr>
      			</table></td>
			</tr>
{POLL_DISPLAY}
			<tr>
				<th><b>{L_AUTHOR}</b></th>
				<th><b>{L_MESSAGE}</b></th>
			</tr>
	        <!-- BEGIN postrow -->
			<tr bgcolor="{postrow.ROW_COLOR}">
				<td align="left" valign="top"><a name="{postrow.U_POST_ID}"></a><table width="170" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td valign="top"><span class="gen"><b>{postrow.POSTER_NAME}</b></span><br /><span class="gensmall">{postrow.POSTER_RANK}<br />{postrow.RANK_IMAGE}{postrow.POSTER_AVATAR}<br /><br />{postrow.POSTER_JOINED}<br />{postrow.POSTER_POSTS}<br />{postrow.POSTER_FROM}</span><br /><br /></td>
					</tr>
				</table></td>
				<td width="100%" valign="top"><table width="100%" cellspacing="1" cellpadding="0" border="0">
					<tr>
						<td valign="top"><table width="100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td valign="middle"><img src="images/icon_minipost.gif" alt="Post image icon" /><span class="gensmall">{L_POSTED}: {postrow.POST_DATE}&nbsp;&nbsp;&nbsp;&nbsp;{L_POST_SUBJECT}: {postrow.POST_SUBJECT}</span></td>
								<td align="right" valign="middle" nowrap="nowrap">&nbsp; {postrow.EDIT_IMG} {postrow.QUOTE_IMG}&nbsp;</td>
							</tr>
							<tr>
								<td colspan="2"><hr /></td>
							</tr>
						</table></td>
					</tr>
					<tr>
						<td width="100%" height="100%" valign="top"><span class="gen">{postrow.MESSAGE}</span></td>
					</tr>
				</table></td>
			</tr>
			<tr bgcolor="{postrow.ROW_COLOR}">
				<td height="20" align="center" valign="middle"><span class="gensmall"><a href="#top">{L_RETURN_TO_TOP}</a></span></td>
				<td height="20"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td valign="middle">&nbsp;{postrow.PROFILE_IMG} {postrow.PM_IMG} {postrow.EMAIL_IMG} {postrow.WWW_IMG} {postrow.ICQ_STATUS_IMG} {postrow.ICQ_ADD_IMG} {postrow.AIM_IMG} {postrow.YIM_IMG} {postrow.MSN_IMG}&nbsp;</td>
						<td align="right" valign="middle">&nbsp;{postrow.IP_IMG}&nbsp;</td>
					</tr>
				</table></td>
			</tr>
			<!-- END postrow -->
			<tr>
				<td class="cat" colspan="2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td width="140" align="left" valign="middle" nowrap="nowrap"><a href="{U_POST_REPLY_TOPIC}"><img src="{IMG_REPLY}" border="0" alt="{L_TOPIC_REPLY}" /></a>&nbsp;&nbsp;<a href="{U_POST_NEW_TOPIC}"><img src="{IMG_POST}" border="0" alt="{L_TOPIC_POST}" /></a></td>
						<td align="left" valign="middle">&nbsp;<span class="gen">{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></span>&nbsp;</td>
						<td align="right" valign="middle"><span class="gen">{PAGINATION}</span></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table>

<br clear="all" />

<table width="98%" cellspacing="2" border="0" align="center">
	<tr>
		<td width="40%" valign="top" nowrap="nowrap"><form method="post" action="{S_POST_DAYS_ACTION}"><span class="gensmall">{L_DISPLAY_POSTS}:&nbsp;{S_SELECT_POST_DAYS}&nbsp;{S_SELECT_POST_ORDER}&nbsp;<input type="submit" value="{L_GO}" /></span></form><span class="gensmall"><b>{S_TIMEZONE}</b><br /><br />{S_WATCH_TOPIC}</span></td>
		<td align="right" valign="top" nowrap="nowrap">{JUMPBOX}<span class="gensmall">{S_AUTH_LIST}</span><br />{S_TOPIC_ADMIN}</td>
	</tr>
</table>
