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
			<tr align="left" valign="top">
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
				<td colspan="6" bgcolor="{T_TH_COLOR2}" align="left"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{TOTAL_USERS_ONLINE}</font><br/><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{LOGGED_IN_USER_LIST}</font></td>
			</tr>
			<tr><form method="post" action="login.{PHPEX}">
				<td colspan="6" bgcolor="{T_TH_COLOR3}" align="right"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{L_USERNAME}: <input type="text" name="username">&nbsp;&nbsp;&nbsp;{L_PASSWORD}: <input type="password" name="password">&nbsp;&nbsp;&nbsp;{L_AUTO_LOGIN}</font>:&nbsp;<input type="checkbox" name="autologin">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="Login">&nbsp;</font></td>
			</form></tr>
		</table></td>
	</tr>
</table></div>