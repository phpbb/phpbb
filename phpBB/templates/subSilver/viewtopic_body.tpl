 
<table width="100%" cellspacing="2" cellpadding="2" border="0">
  <tr> 
	<td align="left" valign="bottom" colspan="2"><span class="maintitle">{TOPIC_TITLE}</span><br />
	  <span class="gensmall"><b>{PAGINATION}</b><br />
	  &nbsp; </span></td>
  </tr>
</table>
<table width="100%" cellspacing="2" cellpadding="2" border="0">
  <tr> 
	<td align="left" valign="bottom" nowrap="nowrap"><span class="nav"><a href="{U_POST_NEW_TOPIC}"><img src="{IMG_POST}" border="0" alt="{L_TOPIC_POST}" align="middle" width="82" height="25" /></a>&nbsp;&nbsp;&nbsp;<a href="{U_POST_REPLY_TOPIC}"><img src="{IMG_REPLY}" border="0" alt="{L_TOPIC_REPLY}" align="middle" width="82" height="25" /></a></span></td>
	<td align="left" valign="middle" width="100%"><span class="nav">&nbsp;&nbsp;&nbsp;<a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a> 
	  -> <a href="{U_VIEW_FORUM}" class="nav">{FORUM_NAME}</a></span></td>
  </tr>
</table>
<table border="0" cellpadding="3" cellspacing="1" width="100%" class="forumline">
  <tr align="right"> 
	<td class="catHead" colspan="2" height="28"><span class="nav"><a href="{U_VIEW_OLDER_TOPIC}" class="nav">{L_VIEW_PREVIOUS_TOPIC}</a> 
	  :: <a href="{U_VIEW_NEWER_TOPIC}" class="nav">{L_VIEW_NEXT_TOPIC}</a> &nbsp;</span></td>
  </tr>
  {POLL_DISPLAY} 
  <tr> 
	<th class="thLeft" width="22%" height="26">{L_AUTHOR}</th>
	<th class="thRight">{L_MESSAGE}</th>
  </tr>
  <!-- BEGIN postrow -->
  <tr> 
	<td width="22%" align="left" valign="top" class="{postrow.ROW_CLASS}" rowspan="2"><span class="name"><a name="{postrow.U_POST_ID}"></a><b>{postrow.POSTER_NAME}</b></span><br />
	  <span class="postdetails">{postrow.POSTER_RANK}<br />
	  {postrow.RANK_IMAGE}{postrow.POSTER_AVATAR}<br />
	  <br />
	  {postrow.POSTER_JOINED}<br />
	  {postrow.POSTER_POSTS}<br />
	  {postrow.POSTER_FROM}</span><br />
	</td>
	<td class="{postrow.ROW_CLASS}" height="28"> 
	  <table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr> 
		  <td>{postrow.MINI_POST_IMG}<span class="postdetails">{L_POSTED}: {postrow.POST_DATE}<span class="gen">&nbsp;</span>&nbsp;&nbsp;&nbsp;{L_POST_SUBJECT}: 
			{postrow.POST_SUBJECT}</span></td>
		  <td nowrap="nowrap" valign="top" align="right">{postrow.IP_IMG} {postrow.QUOTE_IMG} 
			{postrow.EDIT_IMG}</td>
		</tr>
	  </table>
	</td>
  </tr>
  <tr> 
	<td valign="top" class="{postrow.ROW_CLASS}"><span class="postbody" style="line-height: 150%">{postrow.MESSAGE}</span></td>
  </tr>
  <tr> 
	<td width="22%" align="left" valign="middle" class="{postrow.ROW_CLASS}"><span class="nav"><a href="#top" class="nav">Back 
	  to top</a></span></td>
	<td width="78%" height="28" class="{postrow.ROW_CLASS}" nowrap="nowrap" valign="bottom"> 
	  <table cellspacing="0" cellpadding="0" border="0" height="18">
		<tr> 
		  <td valign="middle" nowrap="nowrap">{postrow.PROFILE_IMG} {postrow.PM_IMG} {postrow.EMAIL_IMG} 
			{postrow.WWW_IMG} {postrow.AIM_IMG} {postrow.YIM_IMG} {postrow.MSN_IMG}&nbsp;</td>
		  <td valign="top" align="left" nowrap="nowrap">{postrow.ICQ_ADD_IMG}</td>
		</tr>
	  </table>
	</td>
  </tr>
  <tr> 
	<td colspan="2" height="1" class="spaceRow"><img src="templates/subSilver/images/spacer.gif" alt="" width="1" height="1" /></td>
  </tr>
  <!-- END postrow -->
  <tr align="center"> 
	<td class="catBottom" colspan="2" height="28"> 
	  <table border="0" cellspacing="0" cellpadding="0">
		<tr>
		  <form method="post" action="{S_POST_DAYS_ACTION}">
			<td align="center"><span class="gensmall">{L_DISPLAY_POSTS}:&nbsp;{S_SELECT_POST_DAYS}&nbsp;{S_SELECT_POST_ORDER}&nbsp; 
			  <input type="submit" value="{L_GO}" class="liteoption" name="submit" />
			  </span></td>
		  </form>
		</tr>
	  </table>
	</td>
  </tr>
</table>
<table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
  <tr> 
	<td align="left" valign="middle" nowrap="nowrap"><a href="{U_POST_NEW_TOPIC}"><img src="{IMG_POST}" border="0" alt="{L_TOPIC_POST}" align="middle" width="82" height="25" /></a>&nbsp;&nbsp;&nbsp;<a href="{U_POST_REPLY_TOPIC}"><img src="{IMG_REPLY}" border="0" alt="{L_TOPIC_REPLY}" align="middle" width="82" height="25" /></a><span class="nav"></span></td>
	<td align="left" valign="middle" width="100%"><span class="nav">&nbsp;&nbsp;&nbsp;{L_PAGE} 
	  <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></span></td>
	<td align="right" valign="top" nowrap="nowrap"><span class="nav">{PAGINATION}</span> 
	  <span class="gensmall"><br />
	  {S_TIMEZONE}</span></td>
  </tr>
</table>
<table width="100%" cellspacing="2" border="0" align="center">
  <tr> 
	<td width="40%" valign="top" nowrap="nowrap" align="left"><span class="gensmall">{S_WATCH_TOPIC}</span><br />
	  &nbsp;<br />
	  {S_TOPIC_ADMIN}</td>
	<td align="right" valign="top" nowrap="nowrap">{JUMPBOX}<span class="gensmall">{S_AUTH_LIST}</span></td>
  </tr>
</table>
