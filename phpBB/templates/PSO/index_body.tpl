<div align="center"><table width="98%" cellspacing="0" cellpadding="4" border="0">
	<tr>
		<td align="left"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></font></td>
	</tr>
</table></div>

<div align="center"><table width="98%" cellpadding="1" cellspacing="0" border="0">
	<tr>
		<td bgcolor="{T_TH_COLOR1}"><table border="0" cellpadding="3" cellspacing="1" width="100%">
			<tr>
				<td bgcolor="{T_TH_COLOR3}">&nbsp;</td>
				<td bgcolor="{T_TH_COLOR3}"><font face="{T_FONTFACE2}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}">&nbsp;<b>{L_FORUM}</b>&nbsp;</font></td>
				<td bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE2}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}">&nbsp;<b>{L_TOPICS}</b>&nbsp;</font></td>
				<td bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE2}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}">&nbsp;<b>{L_POSTS}</b>&nbsp;</font></td>
				<td bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE2}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}">&nbsp;<b>{L_LASTPOST}</b>&nbsp;</font></td>
				<td bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE2}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}">&nbsp;<b>{L_MODERATOR}</b>&nbsp;</font></td>
			</tr>
			<!-- BEGIN catrow -->
			<tr>
				<td colspan="6" bgcolor="{T_TH_COLOR2}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE3}"><b><a href="{catrow.U_VIEWCAT}">{catrow.CAT_DESC}</a></b>&nbsp;</font></td>
			</tr>
			<!-- BEGIN forumrow -->
			<tr>
				<td bgcolor="{T_TD_COLOR1}" align="center" valign="middle" width="7%">{catrow.forumrow.FOLDER}</td>
				<td bgcolor="{T_TD_COLOR2}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><a href="{catrow.forumrow.U_VIEWFORUM}">{catrow.forumrow.FORUM_NAME}</a></font><br><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{catrow.forumrow.FORUM_DESC}</font></td>
				<td bgcolor="{T_TD_COLOR1}" width="5%" align="center" valign="middle"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{catrow.forumrow.TOPICS}</font></td>
				<td bgcolor="{T_TD_COLOR2}" width="5%" align="center" valign="middle"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{catrow.forumrow.POSTS}</font></td>
				<td bgcolor="{T_TD_COLOR1}" width="15%" align="center" valign="middle"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{catrow.forumrow.LAST_POST}</font></td>
				<td bgcolor="{T_TD_COLOR2}" width="5%" align="center" valign="middle"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{catrow.forumrow.MODERATORS}</td></tr>
			</tr>
			<!-- END forumrow -->
			<!-- END catrow -->
			<tr>
				<td colspan="6" bgcolor="{T_TH_COLOR2}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE3}"><b><a href="{U_VIEWONLINE}">Who is Online</a></b>&nbsp;</font></td>
			</tr>
			<tr>
				<td colspan="6" bgcolor="{T_TD_COLOR1}" align="left"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{TOTAL_USERS_ONLINE}</font><br><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{LOGGED_IN_USER_LIST}</font></td>
			</tr>
		</table></td>
	</tr>
</table></div>

<div align="center"><table align="center" border="0" width="98%">
	<tr>
		<td width="20" align="center"><img src="images/folder.gif"></td>
		<td><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{L_NEWPOSTS}</font></td>
		<td align="right"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}"><b>{S_TIMEZONE}</b></font></td>
	</tr>
	<tr>
		<td align="center"><img src="images/red_folder.gif"></td>
		<td><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{L_NONEWPOSTS}</font></td>
	</tr>
</table>