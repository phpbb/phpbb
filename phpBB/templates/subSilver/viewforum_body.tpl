
<form method="post" action="{S_POST_DAYS_ACTION}">
  <table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr> 
	  <td align="left" valign="bottom" colspan="3"><span class="maintitle">{FORUM_NAME}</span><br />
		<b><span class="gensmall">{L_MODERATOR}: {MODERATORS}<br />
		{PAGINATION}<br />
		&nbsp;</span></b></td>
	</tr>
	<tr> 
	  <td align="left" valign="middle" width="50"><a href="{U_POST_NEW_TOPIC}"><img src="{IMG_POST}" border="0" alt="{L_NEW_TOPIC}" width="82" height="25" /></a></td>
	  <td align="left" valign="middle" class="nav" width="100%"><span class="nav">&nbsp;&nbsp;&nbsp;<a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a> -> {FORUM_NAME}</span></td>
	  <td align="right" valign="bottom" class="nav" nowrap="nowrap"><span class="gensmall"><a href="{U_MARK_READ}">{L_MARK_TOPICS_READ}</a></span></td>
	</tr>
  </table>
  <table border="0" cellpadding="4" cellspacing="1" width="100%" class="forumline">
	<tr> 
	  <th colspan="2" align="center" height="25" class="thCornerL">&nbsp;{L_TOPICS}&nbsp;</th>
	  <th width="50" align="center" class="thTop">&nbsp;{L_REPLIES}&nbsp;</th>
	  <th width="100" align="center" class="thTop">&nbsp;&nbsp;{L_AUTHOR}&nbsp;&nbsp;</th>
	  <th width="50" align="center" class="thTop">&nbsp;{L_VIEWS}&nbsp;</th>
	  <th align="center"  nowrap="nowrap" class="thCornerR">&nbsp;{L_LASTPOST}&nbsp;</th>
	</tr>
	<!-- BEGIN topicrow -->
	<tr> 
	  <td class="row1" align="center" valign="middle" width="20">{topicrow.FOLDER}</td>
	  <td class="row1" width="100%"><span class="topictitle">{topicrow.NEWEST_POST_IMG}{topicrow.TOPIC_TYPE}<a href="{topicrow.U_VIEW_TOPIC}" class="topictitle">{topicrow.TOPIC_TITLE}</a></span><span class="gensmall"><br />
		{topicrow.GOTO_PAGE}</span></td>
	  <td class="row2" align="center" valign="middle"><span class="postdetails">{topicrow.REPLIES}</span></td>
	  <td class="row3" align="center" valign="middle"><span class="name"><a href="{topicrow.U_TOPIC_POSTER_PROFILE}" class="name">{topicrow.TOPIC_POSTER}</a></span></td>
	  <td class="row2" align="center" valign="middle"><span class="postdetails">{topicrow.VIEWS}</span></td>
	  <td class="row3Right" align="center" valign="middle" nowrap="nowrap"><span class="postdetails">{topicrow.LAST_POST}</span></td>
	</tr>
	<!-- END topicrow -->
	<!-- BEGIN notopicsrow -->
	<tr> 
	  <td class="row1" colspan="6" height="30" align="center" valign="middle"><span class="gen">{L_NO_TOPICS}</span></td>
	</tr>
	<!-- END notopicsrow -->
	<tr> 
	  <td class="catBottom" align="center" valign="middle" colspan="6" height="28"><span class="genmed">{L_DISPLAY_TOPICS}:&nbsp;{S_SELECT_TOPIC_DAYS}&nbsp; 
		<input type="submit" class="liteoption" value="{L_GO}" name="submit" />
		</span></td>
	</tr>
  </table>
  <table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
	<tr> 
	  <td align="left" valign="middle" width="50"><a href="{U_POST_NEW_TOPIC}"><img src="{IMG_POST}" border="0" alt="{L_NEW_TOPIC}" width="82" height="25" /></a></td>
	  <td align="left" valign="middle" width="100%"><span class="nav">&nbsp;&nbsp;&nbsp;<a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a> -> {FORUM_NAME}</span></td>
	  <td align="right" valign="top" nowrap="nowrap"><span class="nav">{L_PAGE} 
		<b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b>&nbsp;&nbsp;::&nbsp;{PAGINATION}</span> 
		<span class="gensmall"><br />
		{S_TIMEZONE}</span></td>
	</tr>
  </table>
</form>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
	<td align="right">{JUMPBOX}</td>
  </tr>
</table>
<table cellspacing="3" border="0" align="center" cellpadding="0">
  <tr> 
	<td width="20" align="center"><img src="templates/subSilver/images/folder_new.gif" alt="{L_NEWPOSTS}" width="19" height="18" /></td>
	<td class="gensmall">New posts</td>
	<td>&nbsp;&nbsp;</td>
	<td width="20" align="center"><img src="templates/subSilver/images/folder.gif" alt="{L_NONEWPOSTS}" width="19" height="18" /></td>
	<td class="gensmall">Old topic</td>
	<td>&nbsp;&nbsp;</td>
	<td width="20" align="center"><img src="templates/subSilver/images/folder_announce.gif" alt="No new posts" width="19" height="18" /></td>
	<td class="gensmall">Announement</td>
	<td class="gensmall">&nbsp;</td>
	<td class="gensmall"><img src="templates/subSilver/images/folder_lock.gif" alt="{L_NEWPOSTS}" width="19" height="18" /></td>
	<td class="gensmall">Topic is locked</td>
  </tr>
  <tr> 
	<td width="20" align="center"><img src="templates/subSilver/images/folder_new_hot.gif" alt="{L_NEWPOSTS}" width="19" height="18" /></td>
	<td class="gensmall">New hot topic</td>
	<td>&nbsp;&nbsp;</td>
	<td width="20" align="center"><img src="templates/subSilver/images/folder_hot.gif" alt="{L_NEWPOSTS}" width="19" height="18" /></td>
	<td class="gensmall">Hot topic</td>
	<td>&nbsp;&nbsp;</td>
	<td width="20" align="center"><img src="templates/subSilver/images/folder_sticky.gif" alt="No new posts" width="19" height="18" /></td>
	<td class="gensmall">Sticky post</td>
	<td class="gensmall">&nbsp;&nbsp;</td>
	<td class="gensmall">&nbsp;</td>
	<td class="gensmall">&nbsp;</td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
	<td align="right"><span class="gensmall">{S_AUTH_LIST}</span></td>
  </tr>
</table>
