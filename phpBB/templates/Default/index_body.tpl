<tr>
<td>
  <table border="0" align="right" width="30%" bgcolor="#000000" cellpadding="0" cellspacing="1">
  <tr>
	  <td>
	    <table border="0" width="100%" bgcolor="#CCCCCC" cellpadding="1" cellspacing="1">
        <tr>
	        <td align="right" style="{font-size: 8pt;}">{L_POSTEDTOTAL} -<b>{TOTAL_POSTS}</b>- {L_MESSAGES}.<br>
	          {L_WEHAVE} <b>{TOTAL_USERS}</b> {L_REGUSERS}.<br>
	          {L_NEWESTUSER} <b><a href="{U_NEWEST_USER_PROFILE}">{NEWEST_USER}</a></b><br>
	          {L_ARECURRENTLY} <a href="{U_VIEWONLINE}"><b>{USERS_BROWSING}</b> {L_BROWSING}</a> {L_THEFORUMS}<br>
	        </td>
	      </tr>
	    </table>
	  </td>
	</tr>
	</table>
 </td>
</tr>
<tr>
	<td bgcolor="#000000" align="center"><table width="100%" cellpadding="0" cellspacing="1"  border="0">
		<tr>
			<td><table width="100%" cellpadding="3" cellspacing="1" border="0">
				<tr class="tableheader">
					<td width="5%">&nbsp;</td>
					<td>{L_FORUM}</td>
					<td align="center" width="5%">{L_TOPICS}</td>
					<td align="center" width="5%">{L_POSTS}</td>
					<td align="center" width="15%">{L_LASTPOST}</td>
					<td align="center" width="5%">{L_MODERATOR}</td>
				</tr>
				<!-- BEGIN catrow -->
				<tr class="catheader">
					<td colspan="6"><a href="{catrow.U_VIEWCAT}">{catrow.CAT_DESC}</a></td>
				</tr>
				<!-- BEGIN forumrow -->
				<tr bgcolor="{catrow.forumrow.ROW_COLOR}" class="tablebody">
					<td width="5%" align="center" valign="middle">{catrow.forumrow.FOLDER}</td>
					<td><a href="{catrow.forumrow.U_VIEWFORUM}">{catrow.forumrow.FORUM_NAME}</a><br>{catrow.forumrow.FORUM_DESC}</td>
					<td width="5%" align="center" valign="middle">{catrow.forumrow.TOPICS}</td>
					<td width="5%" align="center" valign="middle">{catrow.forumrow.POSTS}</td>
					<td width="15%" align="center" valign="middle">{catrow.forumrow.LAST_POST}</td>
					<td width="5%" align="center" valign="middle">{catrow.forumrow.MODERATORS}</td>
				</tr>
				<!-- END forumrow -->
				<!-- END catrow -->
				<tr class="catheader">
					<td colspan="6">{TOTAL_USERS_ONLINE}<br/>{LOGGED_IN_USER_LIST}</td>
				</tr>
			</table></td>
		</tr>
	</table></td>
</tr>
<tr>
	<td align="center"><table border="0" width="100%" cellpadding="0" cellspacing="1">
		<tr>
			<td style="{font-size: 8pt;}"><img src="images/folder.gif"> = {L_NEWPOSTS}</td>
			<td style="{font-size: 8pt;}" align="right" valign="top"><b>{S_TIMEZONE}</b></td>
		</tr>
		<tr>
			<td style="{font-size: 8pt;}"><img src="images/red_folder.gif"> = {L_NONEWPOSTS}</td>
		</tr>
	</table></td>
</tr>