
<table width="100%" cellspacing="0" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left" valign="bottom"><span class="nav"><a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
	<td align="right" class="gensmall"><a href="{U_SEARCH_NEW}" class="gensmall">{L_SEARCH_NEW}</a><br />
	  on {LAST_VISIT_DATE}</td>
  </tr>
</table>
<table width="100%" cellpadding="2" cellspacing="1" border="0" class="forumline">
  <tr> 
	<th colspan="2" class="thCornerL" height="25">&nbsp;{L_FORUM}&nbsp;</th>
	<th width="50" class="thTop">&nbsp;{L_TOPICS}&nbsp;</th>
	<th width="50" class="thTop">&nbsp;{L_POSTS}&nbsp;</th>
	<th class="thCornerR">&nbsp;{L_LASTPOST}&nbsp;</th>
  </tr>
  <!-- BEGIN catrow -->
  <tr> 
	<td class="catLeft" colspan="2" height="28"><span class="cattitle"><a href="{catrow.U_VIEWCAT}" class="cattitle">{catrow.CAT_DESC}</a></span></td>
	<td class="rowpic" colspan="3" align="right">&nbsp;</td>
  </tr>
  <!-- BEGIN forumrow -->
  <tr> 
	<td class="row1" align="center" valign="middle" height="50">{catrow.forumrow.FOLDER}</td>
	<td class="row1" width="100%" height="50"><span class="forumlink"> <a href="{catrow.forumrow.U_VIEWFORUM}" class="forumlink">{catrow.forumrow.FORUM_NAME}</a><br />
	  </span> <span class="genmed">{catrow.forumrow.FORUM_DESC}<br />
	  </span><span class="gensmall">{L_MODERATOR} {catrow.forumrow.MODERATORS}</span></td>
	<td class="row2" align="center" valign="middle" height="50"><span class="gensmall">{catrow.forumrow.TOPICS}</span></td>
	<td class="row2" align="center" valign="middle" height="50"><span class="gensmall">{catrow.forumrow.POSTS}</span></td>
	<td class="row2" align="center" valign="middle" height="50" nowrap="nowrap"> <span class="gensmall">{catrow.forumrow.LAST_POST}</span></td>
  </tr>
  <!-- END forumrow -->
  <!-- END catrow -->
</table>
<table width="100%" cellspacing="0" border="0" align="center" cellpadding="2">
  <tr> 
	<td align="left"><span class="gensmall"><a href="{U_MARK_READ}" class="gensmall">{L_MARK_FORUMS_READ}</a></span></td>
	<td align="right"><span class="gensmall">{S_TIMEZONE}</span></td>
  </tr>
</table>
<table width="100%" cellpadding="3" cellspacing="1" border="0" class="forumline">
  <tr> 
	<td class="catHead" colspan="2" height="28"><span class="cattitle"><a href="{U_VIEWONLINE}" class="cattitle">{L_WHO_IS_ONLINE}</a></span></td>
  </tr>
  <tr> 
	<td class="row1" align="center" valign="middle" rowspan="2"><img src="templates/subSilver/images/whosonline.gif" alt="{L_WHO_IS_ONLINE}" /></td>
	<td class="row1" align="left" width="100%"> 
	  <p><span class="gensmall">A total of <b>{TOTAL_POSTS}</b> posts have been 
		made.<br />
		We have <b>{TOTAL_USERS}</b> registered users.<br />
		Welcome to our newest member <b><a href="{U_NEWEST_USER_PROFILE}">{NEWEST_USER}</a></b>.</span></p>
	</td>
  </tr>
  <tr> 
	<td class="row1" align="left"><span class="gensmall">{TOTAL_USERS_ONLINE}.<br />
	  {LOGGED_IN_USER_LIST}</span></td>
  </tr>
</table>
<!-- BEGIN loginbox -->
<br />
<form method="post" action="{S_LOGIN_ACTION}">
  <table width="100%" cellpadding="3" cellspacing="1" border="0" class="forumline">
	<tr> 
	  <td class="catHead" height="28"><a name="login"></a><span class="cattitle">{L_LOGIN_LOGOUT}</span></td>
	</tr>
	<tr> 
	  <td class="row1" align="center" valign="middle" height="28"><span class="gensmall">{L_USERNAME}: 
		<input class="post" type="text" name="username" size="10" />
		&nbsp;&nbsp;&nbsp;{L_PASSWORD}: 
		<input class="post" type="password" name="password" size="10" />
		&nbsp;&nbsp; &nbsp;&nbsp;{L_AUTO_LOGIN} 
		<input class="text" type="checkbox" name="autologin" />
		&nbsp;&nbsp;&nbsp; 
		<input type="submit" class="mainoption" name="submit" value="{L_LOGIN}" />
		</span> </td>
	</tr>
  </table>
</form>

		<!-- END loginbox -->
&nbsp; <br clear="all" />
<table cellspacing="3" border="0" align="center" cellpadding="0">
  <tr> 
	<td width="20" align="center"><img src="templates/subSilver/images/folder_new.gif" alt="{L_NEWPOSTS}"/></td>
	<td class="gensmall"><span class="gensmall">{L_NEWPOSTS}</span></td>
	<td>&nbsp;&nbsp;</td>
	<td width="20" align="center"><img src="templates/subSilver/images/folder.gif" alt="{L_NONEWPOSTS}" /></td>
	<td class="gensmall">{L_NONEWPOSTS}</td>
	<td>&nbsp;&nbsp;</td>
	<td width="20" align="center"><img src="templates/subSilver/images/folder_lock.gif" alt="{L_FORUM_LOCKED}" /></td>
	<td class="gensmall">{L_FORUM_LOCKED}</td>
  </tr>
</table>