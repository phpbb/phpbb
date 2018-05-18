<!-- BEGIN switch_user_logged_out -->
<form method="post" action="{S_LOGIN_ACTION}">
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="forumline">
    <tr> 
        <td class="catHead" height="28" colspan="2"><a name="login"></a><span class="cattitle">{L_LOGIN_LOGOUT}</span></td>
    </tr>
  <tr>
        <td class="row4" align="left"><img src="templates/Vision1/images/cellpic_shadow_left.gif" border="0"></td>
        <td class="row4" align="right" style="padding-right:10px"><span class="genwhite">{CURRENT_TIME}</span></td>
  </tr>
    <tr> 
        <td class="row1" align="center" valign="middle" height="28" colspan="2"><span class="gensmall">{L_USERNAME}: 
	    <input class="post" type="text" name="username" size="10" />&nbsp;&nbsp;&nbsp;{L_PASSWORD}: 
	    <input class="post" type="password" name="password" size="10" maxlength="32" />
	    <!-- BEGIN switch_allow_autologin -->
	    &nbsp;&nbsp; &nbsp;&nbsp;{L_AUTO_LOGIN} 
	    <input class="text" type="checkbox" name="autologin" />
	    <!-- END switch_allow_autologin -->
	    &nbsp;&nbsp;&nbsp; 
		                    &nbsp;&nbsp;&nbsp; 
		                    <input type="submit" class="mainoption" name="login" value="{L_LOGIN}" />
	    </span>
        </td>
    </tr>
  <tr>
      <td class="row3"><span class="nav"><a href="{U_INDEX}" class="nav">{L_INDEX}</a></span></td>
      <td class="row3" align="right"><a href="{U_SEARCH_UNANSWERED}" class="gensmall">{L_SEARCH_UNANSWERED}</a></td>
  </tr>
  <tr>
       <td class="shadow_bottom" align="left"><img src="templates/Vision1/images/shadow_bottom_left.gif" border="0" width="36" height="5"></td>
       <td height="5" class="shadow_bottom" align="right"><img src="templates/Vision1/images/shadow_bottom_right.gif" border="0" width="5" height="5"></td>
  </tr>
</table>
</form>
<!-- END switch_user_logged_out -->
<!-- BEGIN switch_user_logged_in -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="forumline">
    <tr> 
        <td class="catHead" height="28" colspan="2"><a name="login"></a><span class="cattitle"><a href="{U_INDEX}" class="nav">{L_INDEX}</a></span></td>
    </tr>
  <tr>
        <td class="row4" align="left"><img src="templates/Vision1/images/cellpic_shadow_left.gif" border="0" align="absmiddle"><span class="genwhite">{LAST_VISIT_DATE}</span></td>
        <td class="row4" align="right" style="padding-right:10px"><span class="genwhite">{CURRENT_TIME}</span></td>
  </tr>
    <tr> 
        <td class="row1" align="center" valign="middle"><a href="{U_SEARCH_NEW}" class="gensmall">{L_SEARCH_NEW}</a><br><a href="{U_SEARCH_SELF}" class="gensmall">{L_SEARCH_SELF}</a></td>
        <td class="row1" align="center"><a href="{U_MARK_READ}" class="gensmall">{L_MARK_FORUMS_READ}</a><br><a href="{U_SEARCH_UNANSWERED}" class="gensmall">{L_SEARCH_UNANSWERED}</a></td>
  </tr>
  <tr>
       <td class="shadow_bottom" align="left"><img src="templates/Vision1/images/shadow_bottom_left.gif" border="0" width="36" height="5"></td>
       <td height="5" class="shadow_bottom" align="right"><img src="templates/Vision1/images/shadow_bottom_right.gif" border="0" width="5" height="5"></td>
  </tr>
  <tr><td height="8" colspan="2"></td></tr>
</table>
<!-- END switch_user_logged_in -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="forumline">
  <!-- BEGIN catrow -->
  <tr> 
	<td class="catLeft" colspan="4" height="28"><span class="cattitle"><a href="{catrow.U_VIEWCAT}" class="cattitle">{catrow.CAT_DESC}</a></span></td>
        <td class="rowpic"></td>
  </tr>
  <tr>
        <td class="row4" align="left"><img src="templates/Vision1/images/cellpic_shadow_left.gif" border="0"></td>
        <td class="row4" nowrap="nowrap" align="center"><span class="genwhite">&nbsp;{L_FORUM}&nbsp;</span></td>
       	<td width="50" class="row4" nowrap="nowrap" align="center"><span class="genwhite">&nbsp;{L_TOPICS}&nbsp;</span></td>
	<td width="50" class="row4" nowrap="nowrap" align="center"><span class="genwhite">&nbsp;{L_POSTS}&nbsp;</span></td>
	<td nowrap="nowrap"  class="row4" align="center"><span class="genwhite">&nbsp;{L_LASTPOST}&nbsp;</span></td>
  </tr>
  <!-- BEGIN forumrow -->
  <tr> 
	<td class="row1" align="center" valign="middle" height="50"><img src="{catrow.forumrow.FORUM_FOLDER_IMG}" width="46" height="25" alt="{catrow.forumrow.L_FORUM_FOLDER_ALT}" title="{catrow.forumrow.L_FORUM_FOLDER_ALT}" /></td>
	<td class="row1" width="100%" height="50"><span class="forumlink"> <a href="{catrow.forumrow.U_VIEWFORUM}" class="forumlink">{catrow.forumrow.FORUM_NAME}</a><br />
	  </span> <span class="genmed">{catrow.forumrow.FORUM_DESC}<br />
	  </span><span class="gensmall">{catrow.forumrow.L_MODERATOR} {catrow.forumrow.MODERATORS}</span></td>
	<td class="row5" align="center" valign="middle" height="50"><span class="gensmall">{catrow.forumrow.TOPICS}</span></td>
	<td class="row1" align="center" valign="middle" height="50"><span class="gensmall">{catrow.forumrow.POSTS}</span></td>
	<td class="row6" align="center" valign="middle" height="50" nowrap="nowrap"> <span class="gensmall">{catrow.forumrow.LAST_POST}</span></td>
  </tr>
  <tr><td bgcolor="#f4f4f4" height="1" colspan="5"></td></tr>
  <!-- END forumrow -->
  <tr>
       <td class="shadow_bottom" align="left"><img src="templates/Vision1/images/shadow_bottom_left.gif" border="0" width="36" height="5"></td>
       <td colspan="3" height="5" class="shadow_bottom"></td>
       <td height="5" class="shadow_bottom" align="right"><img src="templates/Vision1/images/shadow_bottom_right.gif" border="0" width="5" height="5"></td>
  </tr>
  <tr><td height="8"></td></tr>
  <!-- END catrow -->
</table>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="forumline">
  <tr> 
	<td class="catHead" colspan="2" height="28"><span class="cattitle"><a href="{U_VIEWONLINE}" class="cattitle">{L_WHO_IS_ONLINE}</a></span></td>
  </tr>
  <tr>
        <td class="row4" align="left"><img src="templates/Vision1/images/cellpic_shadow_left.gif" border="0"></td>
        <td class="row4" align="right" style="padding-right:10px"><span class="genwhite">{L_ONLINE_EXPLAIN}, {S_TIMEZONE}</span></td>
  </tr>
  <tr> 
	<td class="row1" align="center" valign="top" rowspan="3"><img src="templates/Vision1/images/whosonline.png" alt="{L_WHO_IS_ONLINE}" /></td>
	<td class="row1" align="left" width="100%"><span class="gensmall">{TOTAL_POSTS}<br />{TOTAL_USERS}<br />{NEWEST_USER}</span></td>
  </tr>
  <tr><td height="1" bgcolor="#DEE3E7"></td></tr>
  <tr> 
	<td class="row1" align="left"><span class="gensmall">{TOTAL_USERS_ONLINE} &nbsp; [ {L_WHOSONLINE_ADMIN} ] &nbsp; [ {L_WHOSONLINE_MOD} ]<br />{RECORD_USERS}<br />{LOGGED_IN_USER_LIST}</span></td>
  </tr>
  <tr>
       <td class="shadow_bottom" align="left"><img src="templates/Vision1/images/shadow_bottom_left.gif" border="0" width="36" height="5"></td>
       <td height="5" class="shadow_bottom" align="right"><img src="templates/Vision1/images/shadow_bottom_right.gif" border="0" width="5" height="5"></td>
  </tr>
</table>

<br clear="all" />

<table cellspacing="3" border="0" align="center" cellpadding="0">
  <tr> 
	<td width="20" align="center"><img src="templates/Vision1/images/folder_new_big.gif" alt="{L_NEW_POSTS}"/></td>
	<td><span class="gensmall">{L_NEW_POSTS}</span></td>
	<td>&nbsp;&nbsp;</td>
	<td width="20" align="center"><img src="templates/Vision1/images/folder_big.gif" alt="{L_NO_NEW_POSTS}" /></td>
	<td><span class="gensmall">{L_NO_NEW_POSTS}</span></td>
	<td>&nbsp;&nbsp;</td>
	<td width="20" align="center"><img src="templates/Vision1/images/folder_locked_big.gif" alt="{L_FORUM_LOCKED}" /></td>
	<td><span class="gensmall">{L_FORUM_LOCKED}</span></td>
  </tr>

</table>
