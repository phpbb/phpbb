
<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left"><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
	</tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellpadding="3" cellspacing="1" border="0">
			<tr>
				<th>&nbsp;</td>
				<th>&nbsp;{L_FORUM}&nbsp;</td>
				<th>&nbsp;{L_TOPICS}&nbsp;</td>
				<th>&nbsp;{L_POSTS}&nbsp;</td>
				<th>&nbsp;{L_LASTPOST}&nbsp;</td>
				<th>&nbsp;{L_MODERATOR}&nbsp;</td>
			</tr>
			<!-- BEGIN catrow -->
			<tr>
				<td class="cat" colspan="6"><span class="cattitle"><b><a href="{catrow.U_VIEWCAT}">{catrow.CAT_DESC}</a></b>&nbsp;</span></td>
			</tr>
			<!-- BEGIN forumrow -->
			<tr>
				<td class="row1" align="center" valign="middle" width="7%">{catrow.forumrow.FOLDER}</td>
				<td class="row2"><span class="gen"><a href="{catrow.forumrow.U_VIEWFORUM}">{catrow.forumrow.FORUM_NAME}</a></span><br><span class="gensmall">{catrow.forumrow.FORUM_DESC}</span></td>
				<td class="row1" width="5%" align="center" valign="middle"><span class="gen">{catrow.forumrow.TOPICS}</span></td>
				<td class="row2" width="5%" align="center" valign="middle"><span class="gen">{catrow.forumrow.POSTS}</span></td>
				<td class="row1" width="15%" align="center" valign="middle"><span class="gensmall">{catrow.forumrow.LAST_POST}</span></td>
				<td class="row2" width="5%" align="center" valign="middle"><span class="gensmall">{catrow.forumrow.MODERATORS}</span></td></tr>
			</tr>
			<!-- END forumrow -->
			<!-- END catrow -->
			<tr>
				<td class="cat" colspan="6"><span class="cattitle"><b><a href="{U_PRIVATEMSGS}">{L_PRIVATEMSGS}</a></b></span></td>
			</tr>
			<tr>
				<td class="row1" width="7%" align="center" valign="middle"><img src="images/mailbox.gif"></td>
				<td class="row2" colspan="5" align="left"><span class="gen">{L_PRIVATE_MESSAGE_INFO}</span></td>
			</tr>
			<tr>
				<td class="cat" colspan="6"><span class="cattitle"><b><a href="{U_VIEWONLINE}">{L_WHO_IS_ONLINE}</a></b></span></td>
			</tr>
			<tr>
				<td class="row1" width="7%" align="center" valign="middle"><img src="images/whosonline.gif"></td>
				<td class="row2" colspan="5" align="left"><span class="gensmall">{TOTAL_USERS_ONLINE}</span><br><span class="gen">{LOGGED_IN_USER_LIST}</span></td>
			</tr>
		</table></td>
	</tr>
</table>

<table width="98%" cellspacing="2" border="0" align="center">
	<tr>
		<td align="left" valign="top"><table cellspacing="0" border="0">
			<tr>
				<td width="30" align="center"><img src="images/folder_new.gif"></td>
				<td><span class="gensmall">{L_NEWPOSTS}</span></td>
				<td>&nbsp;&nbsp;</td>
				<td width="30" align="center"><img src="images/folder.gif"></td>
				<td><span class="gensmall">{L_NONEWPOSTS}</span></td>
				<td>&nbsp;&nbsp;</td>
				<td width="30" align="center"><img src="images/folder_lock.gif"></td>
				<td><span class="gensmall">{L_FORUM_LOCKED}</span></td>
			</tr>
		</table></td>
		<td align="right" valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
	</tr>
</table>

<!-- div align="center"><table width="98%" cellpadding="1" cellspacing="0" border="0">
	<tr>
		<td bgcolor="{T_TH_COLOR1}"><table border="0" cellpadding="3" cellspacing="1" width="100%">
		</table></td>
	</tr>
</table></div -->
