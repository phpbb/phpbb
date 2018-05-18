<table width="100%" cellspacing="2" cellpadding="2" border="0">
  <tr> 
	<td align="left" valign="bottom" colspan="2"><a class="maintitle" href="{U_VIEW_TOPIC}">{TOPIC_TITLE}</a><br />
	  <span class="gensmall"><b>{PAGINATION}</b></span></td>
  </tr>
</table>

<table width="100%" cellspacing="2" cellpadding="2" border="0">
  <tr> 
	<td align="left" nowrap="nowrap">
            <table cellspacing="0" cellpadding="0" border="0">
               <tr>
                   <td><img src="templates/Vision2/images/x1.gif" width="5" height="21" border="0"></td>
                   <td bgcolor="#dddddd"><a href="{U_POST_NEW_TOPIC}">{L_POST_NEW_TOPIC}</a></td>
                   <td><img src="templates/Vision2/images/x2.gif" width="5" height="21" border="0"></td>
                   <td>&nbsp;&nbsp;</td>
                   <td><img src="templates/Vision2/images/x1.gif" width="5" height="21" border="0"></td>
                   <td bgcolor="#dddddd"><a href="{U_POST_REPLY_TOPIC}">{L_POST_REPLY_TOPIC}</a></td>
                   <td><img src="templates/Vision2/images/x2.gif" width="5" height="21" border="0"></td>
               </tr>
             </table>
</td>
</tr>
<tr>
    <td>
        <span class="gensmall"><a href="{U_INDEX}" class="gensmall">{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}"  class="gensmall">{FORUM_NAME}</a></span></td>
	<td align="right" colspan="2" height="28"><span class="gensmall"><a href="{U_VIEW_OLDER_TOPIC}" class="gensmall">{L_VIEW_PREVIOUS_TOPIC}</a> :: <a href="{U_VIEW_NEWER_TOPIC}" class="gensmall">{L_VIEW_NEXT_TOPIC}</a> &nbsp;</span></td>
    </tr>
</table>

<table class="forumline" width="100%" cellspacing="0" cellpadding="3" border="0">
    {POLL_DISPLAY} 

    <!-- BEGIN postrow -->
    <tr><td colspan="3" class="row3" height="7"></td></tr>
    <tr> 
        <td class="row1" width="33%" align="center">
            <span class="name"><a name="{postrow.U_POST_ID}"></a>
            <b>{postrow.POSTER_NAME}</b></span><br /><span class="postdetails">{postrow.POSTER_RANK}<br />{postrow.RANK_IMAGE}</span>
        </td>
        <td class="row1" width="34%" align="center">
            <span class="gensmall">{postrow.POSTER_AVATAR}</span>
        </td>
        <td class="row1" width="33%"><span class="gensmall">
            {postrow.POSTER_JOINED}<br />{postrow.POSTER_POSTS}<br /><span class="gensmall">{postrow.POST_DATE}</span><br />{postrow.POSTER_FROM}</span>
        </td>
    </tr>
    <tr>
        <td colspan="3" width="100%" valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			<tr>
				<td width="100%"><b>{postrow.POST_SUBJECT}</b></td>
				<td nowrap="nowrap">{postrow.QUOTE_IMG} {postrow.EDIT_IMG} {postrow.DELETE_IMG} {postrow.IP_IMG}</td>
			</tr>
			<tr>
				<td colspan="2"><span class="postbody">{postrow.MESSAGE}{postrow.SIGNATURE}</span><span class="gensmall">{postrow.EDITED_MESSAGE}</span></td>
			</tr>
		</table>
        </td>
    </tr>
    <tr><td colspan="3" class="row2" height="5"></td></tr>
    <tr>
    <td valign="middle" nowrap="nowrap">{postrow.PROFILE_IMG} {postrow.PM_IMG} {postrow.EMAIL_IMG} {postrow.WWW_IMG} {postrow.AIM_IMG} {postrow.YIM_IMG} {postrow.MSN_IMG} {postrow.ICQ_IMG}</td>
    <td align="center"></td>
    <td align="right" colspan="2"><a href="#top" class="nav">{L_BACK_TO_TOP}</a></span></td>
    </tr>

	<!-- END postrow -->
	<tr align="center"> 
		<td class="row3" colspan="3" height="28"><table cellspacing="0" cellpadding="0" border="0">
			<tr><form method="post" action="{S_POST_DAYS_ACTION}">
				<td align="center"><span class="gensmall">{L_DISPLAY_POSTS}: {S_SELECT_POST_DAYS}&nbsp;{S_SELECT_POST_ORDER}&nbsp;<input type="submit" value="{L_GO}" class="liteoption" name="submit" /></span></td>
			</form></tr>
		</table></td>
	</tr>
</table>

<table width="100%" cellspacing="0" cellpadding="2" border="0">

<tr>
    <td>
        <span class="gensmall"><a href="{U_INDEX}" class="gensmall">{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}"  class="gensmall">{FORUM_NAME}</a></span></td>
	<td align="right" height="28"><span class="gensmall">{PAGE_NUMBER}, {S_TIMEZONE}</span></td>
    </tr>
  <tr> 
	<td align="left" nowrap="nowrap">
            <table cellspacing="0" cellpadding="0" border="0">
               <tr>
                   <td><img src="templates/Vision2/images/x1.gif" width="5" height="21" border="0"></td>
                   <td bgcolor="#dddddd"><a href="{U_POST_NEW_TOPIC}">{L_POST_NEW_TOPIC}</a></td>
                   <td><img src="templates/Vision2/images/x2.gif" width="5" height="21" border="0"></td>
                   <td>&nbsp;&nbsp;</td>
                   <td><img src="templates/Vision2/images/x1.gif" width="5" height="21" border="0"></td>
                   <td bgcolor="#dddddd"><a href="{U_POST_REPLY_TOPIC}">{L_POST_REPLY_TOPIC}</a></td>
                   <td><img src="templates/Vision2/images/x2.gif" width="5" height="21" border="0"></td>
               </tr>
             </table>

        </td>
        <td align="right"><span class="nav">{PAGINATION}</span> </td>
</tr>
</table>


<table width="100%" cellspacing="0" cellpadding="2" border="0" align="center">
  <tr> 
	<td width="40%" valign="top" nowrap="nowrap" align="left"><span class="gensmall">{S_WATCH_TOPIC}</span><br />
	  &nbsp;<br />
	  {S_TOPIC_ADMIN}</td>
	<td align="right" valign="top" nowrap="nowrap">{JUMPBOX}<span class="gensmall">{S_AUTH_LIST}</span></td>
  </tr>
</table>
