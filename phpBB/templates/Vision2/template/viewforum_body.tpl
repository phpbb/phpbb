
<form method="post" action="{S_POST_DAYS_ACTION}">
  <table width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr> 
	  <td align="left" valign="bottom" colspan="2"><a class="maintitle" href="{U_VIEW_FORUM}">{FORUM_NAME}</a><br /><span class="gensmall"><b>{L_MODERATOR}: {MODERATORS}<br /><br />{LOGGED_IN_USER_LIST}</b></span></td>
	</tr>

	<tr> 
	  <td align="left" valign="middle">
            <table cellspacing="0" cellpadding="0" border="0">
               <tr>
                   <td><img src="templates/Vision2/images/x1.gif" width="5" height="21" border="0"></td>
                   <td bgcolor="#dddddd"><a href="{U_POST_NEW_TOPIC}">{L_POST_NEW_TOPIC}</a></td>
                   <td><img src="templates/Vision2/images/x2.gif" width="5" height="21" border="0"></td>
               </tr>
             </table>

          </td>
	  <td align="right"><span class="gensmall"><b>{PAGINATION}</b></span></td>
	</tr>
	<tr> 
	  <td align="left"><span class="gensmall"><a href="{U_INDEX}" class="gensmall">{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}" class="gensmall">{FORUM_NAME}</a></span></td>
          <td align="right"><span class="gensmall"><a href="{U_MARK_READ}">{L_MARK_TOPICS_READ}</a></span></td>
	</tr>
  </table>

  <table border="0" cellpadding="4" cellspacing="1" width="100%">
	<tr> 
	  <td class="row3" colspan="2" align="center" height="25" nowrap="nowrap"><span class="fontrow3">{L_TOPICS}</span></td>
	  <td class="row3" width="50" align="center" nowrap="nowrap"><span class="fontrow3">{L_AUTHOR}</span></td>
	  <td class="row3" width="50" align="center" nowrap="nowrap"><span class="fontrow3">{L_REPLIES}</span></td>

	  <td class="row3" width="50" align="center" nowrap="nowrap"><span class="fontrow3">{L_VIEWS}</span></td>
	  <td class="row3" align="center" nowrap="nowrap"><span class="fontrow3">{L_LASTPOST}</span></td>
	</tr>
	<!-- BEGIN topicrow -->
	<tr> 
	  <td align="center" valign="middle" width="20"><img src="{topicrow.TOPIC_FOLDER_IMG}" width="19" height="18" alt="{topicrow.L_TOPIC_FOLDER_ALT}" title="{topicrow.L_TOPIC_FOLDER_ALT}" /></td>
	  <td width="100%"><span class="topictitle">{topicrow.NEWEST_POST_IMG}{topicrow.TOPIC_TYPE}<a href="{topicrow.U_VIEW_TOPIC}" class="topictitle">{topicrow.TOPIC_TITLE}</a></span><span class="gensmall"><br />
		{topicrow.GOTO_PAGE}</span></td>
	  <td align="center" valign="middle" nowrap="nowrap"><span class="name">{topicrow.TOPIC_AUTHOR}</span></td>
	  <td align="center" valign="middle" nowrap="nowrap"><span class="postdetails">{topicrow.REPLIES}</span></td>

	  <td align="center" valign="middle" nowrap="nowrap"><span class="postdetails">{topicrow.VIEWS}</span></td>
	  <td align="center" valign="middle" nowrap="nowrap"><span class="postdetails">{topicrow.LAST_POST_TIME}<br />{topicrow.LAST_POST_AUTHOR} {topicrow.LAST_POST_IMG}</span></td>
	</tr>
	<!-- END topicrow -->
	<!-- BEGIN switch_no_topics -->
	<tr> 
	  <td colspan="6" height="30" align="center" valign="middle"><span class="gen">{L_NO_TOPICS}</span></td>
	</tr>
	<!-- END switch_no_topics -->
	<tr> 
	  <td class="row3" align="center" valign="middle" colspan="6" height="28"><span class="genmed">{L_DISPLAY_TOPICS}:&nbsp;{S_SELECT_TOPIC_DAYS}&nbsp; 
		<input type="submit" class="liteoption" value="{L_GO}" name="submit" />
		</span></td>
	</tr>
  </table>
  <table width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr> 
	  <td align="left"><span class="gensmall"><a href="{U_INDEX}" class="gensmall">{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}" class="gensmall">{FORUM_NAME}</a></span></td>
          <td align="right"><span class="gensmall">{S_TIMEZONE}, {PAGE_NUMBER}</span></td>
	</tr>
	<tr> 
	  <td align="left" valign="middle">
            <table cellspacing="0" cellpadding="0" border="0">
               <tr>
                   <td><img src="templates/Vision2/images/x1.gif" width="5" height="21" border="0"></td>
                   <td bgcolor="#dddddd"><a href="{U_POST_NEW_TOPIC}">{L_POST_NEW_TOPIC}</a></td>
                   <td><img src="templates/Vision2/images/x2.gif" width="5" height="21" border="0"></td>
               </tr>
             </table>
          </td>
	  <td align="right"><span class="gensmall"><b>{PAGINATION}</b></span></td>
	</tr>
  </table>

</form>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
	<td align="right">{JUMPBOX}</td>
  </tr>
</table>
<table cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
<tr><td>
<table cellspacing="3" cellpadding="1" border="0" align="center" width="100%">
			<tr>
				<td class="gensmall"><img src="{FOLDER_NEW_IMG}" alt="{L_NEW_POSTS}" width="19" height="18" /></td>
				<td class="gensmall">{L_NEW_POSTS}</td>
				<td class="gensmall"><img src="{FOLDER_IMG}" alt="{L_NO_NEW_POSTS}" width="19" height="18" /></td>
				<td class="gensmall">{L_NO_NEW_POSTS}</td>



			</tr>
			<tr> 
				<td class="gensmall"><img src="{FOLDER_HOT_NEW_IMG}" alt="{L_NEW_POSTS_HOT}" width="19" height="18" /></td>
				<td class="gensmall">{L_NEW_POSTS_HOT}</td>
				<td class="gensmall"><img src="{FOLDER_HOT_IMG}" alt="{L_NO_NEW_POSTS_HOT}" width="19" height="18" /></td>
				<td class="gensmall">{L_NO_NEW_POSTS_HOT}</td>


			</tr>

			<tr> 
				<td class="gensmall"><img src="{FOLDER_LOCKED_NEW_IMG}" alt="{L_NEW_POSTS_LOCKED}" width="19" height="18" /></td>
				<td class="gensmall">{L_NEW_POSTS_LOCKED}</td>
				<td class="gensmall"><img src="{FOLDER_LOCKED_IMG}" alt="{L_NO_NEW_POSTS_LOCKED}" width="19" height="18" /></td>
				<td class="gensmall">{L_NO_NEW_POSTS_LOCKED}</td>
			</tr>
			<tr> 
				<td class="gensmall"><img src="{FOLDER_STICKY_IMG}" alt="{L_STICKY}" width="19" height="18" /></td>
				<td class="gensmall">{L_STICKY}</td>
				<td class="gensmall"><img src="{FOLDER_ANNOUNCE_IMG}" alt="{L_ANNOUNCEMENT}" width="19" height="18" /></td>
				<td class="gensmall">{L_ANNOUNCEMENT}</td>
			</tr>
</table>
</td><td>
<table width="100%" cellspacing="0" border="0" align="center" cellpadding="0">
 <tr><td align="right"><span class="gensmall">{S_AUTH_LIST}</span></td></tr>
</table>
</td></tr></table>