
<form method="post" action="{S_POST_DAYS_ACTION}">
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr> 
	  <td colspan="3">
          <a class="maintitle" href="{U_VIEW_FORUM}">{FORUM_NAME}</a>
          </td>
	</tr>
	<tr> 
	  <td align="left" valign="bottom" colspan="2"><span class="gensmall"><b>{L_MODERATOR}: {MODERATORS}<br /><br />{LOGGED_IN_USER_LIST}</b></span></td>
	  <td align="right" valign="bottom" nowrap="nowrap"></td>
	</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr> 
	  <td class="catBottom" valign="middle" colspan="3" height="28">
          <span class="nav"><a href="{U_INDEX}" class="nav">{L_INDEX}</a> -> <a class="nav" href="{U_VIEW_FORUM}">{FORUM_NAME}</a></span>
          </td>
	</tr>
  <tr>
       <td class="shadow_bottom" align="left"><img src="templates/Vision1/images/shadow_bottom_left.gif" border="0" width="36" height="5"></td>
       <td height="5" class="shadow_bottom"></td>
       <td height="5" class="shadow_bottom" align="right"><img src="templates/Vision1/images/shadow_bottom_right.gif" border="0" width="5" height="5"></td>
  </tr>
</table>
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr> 
	  <td align="left" valign="top" width="50"><a href="{U_POST_NEW_TOPIC}"><img src="{POST_IMG}" border="0" alt="{L_POST_NEW_TOPIC}" /></a></td>
	  <td align="right" valign="top" class="nav" width="100%" style="padding-right:10px"><span class="gensmall"><b>{PAGINATION}</b></span></td>
	  <td align="right" valign="top" class="nav" nowrap="nowrap"><span class="gensmall"><a href="{U_MARK_READ}">{L_MARK_TOPICS_READ}</a></span></td>
	</tr>

  </table>

  <table border="0" cellpadding="0" cellspacing="0" width="100%" class="forumline">
	<tr> 
	  <th colspan="2" align="center" height="25" class="thCornerL" nowrap="nowrap">&nbsp;{L_TOPICS}&nbsp;</th>
	  <th width="50" align="center" class="thTop" nowrap="nowrap">&nbsp;{L_REPLIES}&nbsp;</th>
	  <th width="100" align="center" class="thTop" nowrap="nowrap">&nbsp;{L_AUTHOR}&nbsp;</th>
	  <th width="50" align="center" class="thTop" nowrap="nowrap">&nbsp;{L_VIEWS}&nbsp;</th>
	  <th align="center" class="thCornerR" nowrap="nowrap">&nbsp;{L_LASTPOST}&nbsp;</th>
	</tr>
  <tr>
        <td class="row4" align="left" colspan="3"><img src="templates/Vision1/images/cellpic_shadow_left.gif" border="0"></td>
        <td class="row4" align="right"  colspan="3" style="padding-right:10px"><span class="genwhite">{PAGE_NUMBER} - {S_TIMEZONE}</span></td>
  </tr>
	<!-- BEGIN topicrow -->
	<tr> 
	  <td class="row1" align="center" valign="middle" width="20"><img src="{topicrow.TOPIC_FOLDER_IMG}" width="19" height="18" alt="{topicrow.L_TOPIC_FOLDER_ALT}" title="{topicrow.L_TOPIC_FOLDER_ALT}" /></td>
	  <td class="row1" width="100%"><span class="topictitle">{topicrow.NEWEST_POST_IMG}{topicrow.TOPIC_TYPE}<a href="{topicrow.U_VIEW_TOPIC}" class="topictitle">{topicrow.TOPIC_TITLE}</a></span><span class="gensmall"><br />
		{topicrow.GOTO_PAGE}</span></td>
	  <td class="row2" align="center" valign="middle"><span class="postdetails">{topicrow.REPLIES}</span></td>
	  <td class="row3" align="center" valign="middle"><span class="name">{topicrow.TOPIC_AUTHOR}</span></td>
	  <td class="row2" align="center" valign="middle"><span class="postdetails">{topicrow.VIEWS}</span></td>
	  <td class="row3Right" align="center" valign="middle" nowrap="nowrap"><span class="postdetails">{topicrow.LAST_POST_TIME}<br />{topicrow.LAST_POST_AUTHOR} {topicrow.LAST_POST_IMG}</span></td>
	</tr>
        <tr><td bgcolor="#f4f4f4" height="1" colspan="6"></td></tr>
	<!-- END topicrow -->
	<!-- BEGIN switch_no_topics -->
	<tr> 
	  <td class="row1" colspan="6" height="30" align="center" valign="middle"><span class="gen">{L_NO_TOPICS}</span></td>
	</tr>
	<!-- END switch_no_topics -->
  </table>
<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr> 
	  <td class="catBottom" align="center" valign="middle" colspan="3" height="28"><span class="genmed">{L_DISPLAY_TOPICS}:&nbsp;{S_SELECT_TOPIC_DAYS}&nbsp; 
		<input type="submit" class="liteoption" value="{L_GO}" name="submit" />
		</span></td>
	</tr>
  <tr>
       <td class="shadow_bottom" align="left"><img src="templates/Vision1/images/shadow_bottom_left.gif" border="0" width="36" height="5"></td>
       <td height="5" class="shadow_bottom"></td>
       <td height="5" class="shadow_bottom" align="right"><img src="templates/Vision1/images/shadow_bottom_right.gif" border="0" width="5" height="5"></td>
  </tr>
</table>


<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr><td height="5" colspan="2"></td></tr>
  <tr>
      <td align="left" valign="top" width="50"><a href="{U_POST_NEW_TOPIC}"><img src="{POST_IMG}" border="0" alt="{L_POST_NEW_TOPIC}" /></a></td>
	  <td align="right" valign="top" class="nav" nowrap="nowrap">{JUMPBOX}</td>
  </tr>
  <tr><td align="right" valign="top" colspan="2"><span class="gensmall"><b>{PAGINATION}</b></span></td></tr>
  <tr><td height="5" colspan="2"></td></tr>
</table>

<table width="100%" cellspacing="0" border="0" align="center" cellpadding="0">
	<tr>
		<td align="left" valign="top"><table cellspacing="3" cellpadding="0" border="0">
			<tr>
				<td width="20" align="left"><img src="{FOLDER_NEW_IMG}" alt="{L_NEW_POSTS}" width="19" height="18" /></td>
				<td class="gensmall">{L_NEW_POSTS}</td>
				<td>&nbsp;&nbsp;</td>
				<td width="20" align="center"><img src="{FOLDER_IMG}" alt="{L_NO_NEW_POSTS}" width="19" height="18" /></td>
				<td class="gensmall">{L_NO_NEW_POSTS}</td>
				<td>&nbsp;&nbsp;</td>
				<td width="20" align="center"><img src="{FOLDER_ANNOUNCE_IMG}" alt="{L_ANNOUNCEMENT}" width="19" height="18" /></td>
				<td class="gensmall">{L_ANNOUNCEMENT}</td>
			</tr>
			<tr> 
				<td width="20" align="center"><img src="{FOLDER_HOT_NEW_IMG}" alt="{L_NEW_POSTS_HOT}" width="19" height="18" /></td>
				<td class="gensmall">{L_NEW_POSTS_HOT}</td>
				<td>&nbsp;&nbsp;</td>
				<td width="20" align="center"><img src="{FOLDER_HOT_IMG}" alt="{L_NO_NEW_POSTS_HOT}" width="19" height="18" /></td>
				<td class="gensmall">{L_NO_NEW_POSTS_HOT}</td>
				<td>&nbsp;&nbsp;</td>
				<td width="20" align="center"><img src="{FOLDER_STICKY_IMG}" alt="{L_STICKY}" width="19" height="18" /></td>
				<td class="gensmall">{L_STICKY}</td>
			</tr>
			<tr>
				<td class="gensmall"><img src="{FOLDER_LOCKED_NEW_IMG}" alt="{L_NEW_POSTS_LOCKED}" width="19" height="18" /></td>
				<td class="gensmall">{L_NEW_POSTS_LOCKED}</td>
				<td>&nbsp;&nbsp;</td>
				<td class="gensmall"><img src="{FOLDER_LOCKED_IMG}" alt="{L_NO_NEW_POSTS_LOCKED}" width="19" height="18" /></td>
				<td class="gensmall">{L_NO_NEW_POSTS_LOCKED}</td>
			</tr>
		</table></td>
		<td align="right"><span class="gensmall">{S_AUTH_LIST}</span></td>
	</tr>
</table>
</form>