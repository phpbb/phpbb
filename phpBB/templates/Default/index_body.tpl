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