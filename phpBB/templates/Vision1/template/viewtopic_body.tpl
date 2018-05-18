
<table width="100%" cellspacing="2" cellpadding="2" border="0">
  <tr> 
	<td align="left" valign="bottom" colspan="2" style="padding-top:10px;padding-bottom:10px"><a class="maintitle" href="{U_VIEW_TOPIC}">{TOPIC_TITLE}</a></td>
  </tr>
</table>



<table width="100%" cellpadding="0" cellspacing="0" border="0" class="forumline">
    <tr> 
        <td class="catHead" height="28" colspan="2"><span class="nav"><a href="{U_INDEX}" class="nav">{L_INDEX}</a> 
	  -> <a href="{U_VIEW_FORUM}" class="nav">{FORUM_NAME}</a></span></td>
    </tr>
  <tr>
        <td class="row4" align="left"><img src="templates/Vision1/images/cellpic_shadow_left.gif" border="0"></td>
        <td class="row4" align="right" style="padding-right:10px"><span class="genwhite">{PAGE_NUMBER} - {S_TIMEZONE}</span></td>
  </tr>
  <tr>
       <td class="row1" align="left" height="20"><span class="gensmall"><b>{PAGINATION}</b></span></td>
       <td class="row1" align="right"><span class="gensmall"><a href="{U_VIEW_OLDER_TOPIC}" class="gensmall">{L_VIEW_PREVIOUS_TOPIC}</a> :: <a href="{U_VIEW_NEWER_TOPIC}" class="gensmall">{L_VIEW_NEXT_TOPIC}</a></span></td>
  </tr>
  <tr>
       <td class="shadow_bottom" align="left"><img src="templates/Vision1/images/shadow_bottom_left.gif" border="0" width="36" height="5"></td>
       <td height="5" class="shadow_bottom" align="right"><img src="templates/Vision1/images/shadow_bottom_right.gif" border="0" width="5" height="5"></td>
  </tr>

</table>
<table width="100%" cellspacing="2" cellpadding="2" border="0">
  <tr> 
	<td align="left" valign="bottom" nowrap="nowrap"><span class="nav"><a href="{U_POST_NEW_TOPIC}"><img src="{POST_IMG}" border="0" alt="{L_POST_NEW_TOPIC}" align="middle" /></a>&nbsp;&nbsp;&nbsp;<a href="{U_POST_REPLY_TOPIC}"><img src="{REPLY_IMG}" border="0" alt="{L_POST_REPLY_TOPIC}" align="middle" /></a></span></td>
	<td align="left" valign="middle" width="100%"></td>
  </tr>
</table>
{POLL_DISPLAY} 
<table class="forumline" width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<th class="thLeft" width="150" height="26" nowrap="nowrap" colspan="2">{L_AUTHOR}</th>
		<th class="thRight" nowrap="nowrap">{L_MESSAGE}</th>
	</tr>

	<!-- BEGIN postrow -->

	<tr> 
		<td width="150" align="left" valign="top" class="row1"><span class="name"><a name="{postrow.U_POST_ID}"></a><b>{postrow.POSTER_NAME}</b></span><br /><span class="postdetails">{postrow.POSTER_RANK}<br />{postrow.RANK_IMAGE}{postrow.POSTER_AVATAR}<br /><br />{postrow.POSTER_JOINED}<br />{postrow.POSTER_POSTS}<br />{postrow.POSTER_FROM}</span><br /></td>
                <td width="1"></td>
		<td class="row1" width="100%" height="28" valign="top">
                        <table width="100%" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<td width="100%"><span class="postdetails"><!--{L_POST_SUBJECT}: --><b>{postrow.POST_SUBJECT}</b></span></td>
				<td valign="top" nowrap="nowrap">{postrow.QUOTE_IMG} {postrow.EDIT_IMG} {postrow.DELETE_IMG} {postrow.IP_IMG}</td>
			</tr>
			<tr> 
				<td colspan="2"><hr></td>
			</tr>
			<tr>
				<td colspan="2"><span class="postbody">{postrow.MESSAGE}{postrow.SIGNATURE}</span><span class="gensmall">{postrow.EDITED_MESSAGE}</span></td>
			</tr>
		</table></td>
	</tr>

	<tr> 
		<td class="row2" width="150" align="left" valign="middle" colspan="2"><span class="nav"><a href="#top" class="nav">{L_BACK_TO_TOP}</a></span></td>
		<td class="row2" width="100%" height="28" valign="bottom" nowrap="nowrap">
                    <table cellspacing="0" cellpadding="0" border="0" height="18" width="100%">
			<tr> 
				<td valign="middle" nowrap="nowrap">{postrow.PROFILE_IMG} {postrow.PM_IMG} {postrow.EMAIL_IMG} {postrow.WWW_IMG} {postrow.AIM_IMG} {postrow.YIM_IMG} {postrow.MSN_IMG} {postrow.ICQ_IMG}</td>
                                <td align="right" nowrap="nowrap"><span class="postdetails"><i>{L_POSTED}: {postrow.POST_DATE}</i></span></td>
			</tr>
		</table></td>
	</tr>

  <tr>
       <td class="shadow_bottom" align="left" colspan="2"><img src="templates/Vision1/images/shadow_bottom_left.gif" border="0" width="36" height="5"></td>
       <td height="5" class="shadow_bottom" align="right"><img src="templates/Vision1/images/shadow_bottom_right.gif" border="0" width="5" height="5"></td>
  </tr>
  <tr>
       <td colspan="3" height="8"></td>
  </tr>
	<!-- END postrow -->

	<tr align="center"> 
		<td class="catBottom" colspan="3" height="28"><table cellspacing="0" cellpadding="0" border="0">
			<tr><form method="post" action="{S_POST_DAYS_ACTION}">
				<td align="center"><span class="gensmall">{L_DISPLAY_POSTS}: {S_SELECT_POST_DAYS}&nbsp;{S_SELECT_POST_ORDER}&nbsp;<input type="submit" value="{L_GO}" class="liteoption" name="submit" /></span></td>
			</form></tr>
		</table></td>
	</tr>
  <tr>
       <td class="shadow_bottom" align="left" colspan="2"><img src="templates/Vision1/images/shadow_bottom_left.gif" border="0" width="36" height="5"></td>
       <td height="5" class="shadow_bottom" align="right"><img src="templates/Vision1/images/shadow_bottom_right.gif" border="0" width="5" height="5"></td>
  </tr>
</table>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
  <tr><td colspan="2" height="5"></td></tr>
  <tr> 
	<td align="left" valign="top" nowrap="nowrap"><span class="nav"><a href="{U_POST_NEW_TOPIC}"><img src="{POST_IMG}" border="0" alt="{L_POST_NEW_TOPIC}" align="middle" /></a>&nbsp;&nbsp;&nbsp;<a href="{U_POST_REPLY_TOPIC}"><img src="{REPLY_IMG}" border="0" alt="{L_POST_REPLY_TOPIC}" align="middle" /></a></span></td>
	<td align="right" valign="top">{JUMPBOX}</td>
  </tr>
  <tr><td colspan="2" align="right"><span class="gensmall"><b>{PAGINATION}</b></span></td></tr>
</table>
<table width="100%" cellspacing="2" cellpadding="3" border="0" align="center">
  <tr> 
	<td width="40%" valign="top" nowrap="nowrap" align="left"><span class="gensmall">{S_WATCH_TOPIC}</span><br />
	  &nbsp;<br />
	  {S_TOPIC_ADMIN}</td>
	<td align="right" valign="top" nowrap="nowrap"><span class="gensmall">{S_AUTH_LIST}</span></td>
  </tr>
</table>
