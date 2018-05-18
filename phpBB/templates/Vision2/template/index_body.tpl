<table width="100%" cellpadding="3" cellspacing="1" border="0" class="forumline">
    <!-- BEGIN catrow -->
    <tr> 
        <td height="28" colspan="2"><span class="cattitle"><a href="{catrow.U_VIEWCAT}" class="cattitle">{catrow.CAT_DESC}</a></span></td>
    </tr>
    <!-- BEGIN forumrow -->
    <tr>
	    <td width="70%" class="backquote"><span class="forumlink"> <a href="{catrow.forumrow.U_VIEWFORUM}" class="forumlink">{catrow.forumrow.FORUM_NAME}</a> </span><span class="gensmall">({catrow.forumrow.TOPICS}/{catrow.forumrow.POSTS})</span>&nbsp;
            <img src="{catrow.forumrow.FORUM_FOLDER_IMG}" width="9" height="7" alt="{catrow.forumrow.L_FORUM_FOLDER_ALT}" title="{catrow.forumrow.L_FORUM_FOLDER_ALT}" /><br><span class="genmed">{catrow.forumrow.FORUM_DESC}</span></td>
        <td width="30%" align="center" class="row2" rowspan="2"><span class="gensmall">{catrow.forumrow.LAST_POST}</span></td>
    </tr>
    <tr>
        <td class="backquote"><span class="gensmall">{catrow.forumrow.L_MODERATOR} {catrow.forumrow.MODERATORS}</span></td>
    </tr>
    <tr>
        <td height="1" style="padding-left:25px"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td background="templates/Vision2/images/line_cols_2.gif" style="padding:0px"></td></tr></table></td>
        <td></td>
    </tr>
    <!-- END forumrow -->
    <!-- END catrow -->
</table>

<table width="100%" cellpadding="3" cellspacing="1" border="0" class="forumline">
  <tr> 
	<td height="28"><span class="cattitle"><a href="{U_VIEWONLINE}" class="cattitle">{L_WHO_IS_ONLINE}</a></span></td>
  </tr>
<tr>
	<td align="left" valign="top" class="backquote"><span class="gensmall">{CURRENT_TIME}, <span class="gensmall">{S_TIMEZONE}</span>  	
<!-- BEGIN switch_user_logged_in -->
 		<span class="gensmall">[ <a href="{U_MARK_READ}" class="gensmall">{L_MARK_FORUMS_READ}</a> ]</span>
 	<!-- END switch_user_logged_in --><br>{L_ONLINE_EXPLAIN}</span></td>
</tr>
  <tr> 
	<td align="left" width="100%" class="backquote"><span class="gensmall">{TOTAL_POSTS},&nbsp;{TOTAL_USERS}<br />{NEWEST_USER}</span>
	</td>
  </tr>
  <tr> 
	<td align="left" class="backquote"><span class="gensmall">{TOTAL_USERS_ONLINE} <br> {RECORD_USERS} [ {L_WHOSONLINE_ADMIN} ]&nbsp;[ {L_WHOSONLINE_MOD} ] <br />{LOGGED_IN_USER_LIST} </span></td>
  </tr>
</table>


<br clear="all" />

<table cellspacing="3" border="0" align="center" cellpadding="0">
  <tr> 
	<td width="20" align="center"><img src="templates/Vision2/images/folder_new_big.gif" alt="{L_NEW_POSTS}"/></td>
	<td><span class="gensmall">{L_NEW_POSTS}</span></td>
	<td>&nbsp;&nbsp;</td>
	<td width="20" align="center"><img src="templates/Vision2/images/folder_big.gif" alt="{L_NO_NEW_POSTS}" /></td>
	<td><span class="gensmall">{L_NO_NEW_POSTS}</span></td>
	<td>&nbsp;&nbsp;</td>
	<td width="20" align="center"><img src="templates/Vision2/images/folder_locked_big.gif" alt="{L_FORUM_LOCKED}" /></td>
	<td><span class="gensmall">{L_FORUM_LOCKED}</span></td>
  </tr>
</table>
