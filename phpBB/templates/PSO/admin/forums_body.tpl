{S_FORMSTART}
<div align="center"><table width="98%" cellpadding="1" cellspacing="0" border="0">
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
				<td class="cat" colspan="6"><span class="cattitle"><b><a href="{catrow.U_VIEWCAT}">{catrow.CAT_DESC}</a></b>&nbsp;</span>{catrow.S_NEWCAT}</td>
			</tr>
			<!-- BEGIN forumrow -->
			<tr>
				<td class="row1" align="center" valign="middle" width="7%">{catrow.forumrow.FOLDER}</td>
				<td class="row2"><span class="gen">{catrow.forumrow.S_NEWFORUM}<a href="{catrow.forumrow.U_VIEWFORUM}">{catrow.forumrow.FORUM_NAME}</a></span><br><span class="gensmall">{catrow.forumrow.FORUM_DESC}</span></td>
				<td class="row1" width="5%" align="center" valign="middle"><span class="gen">{catrow.forumrow.TOPICS}</span></td>
				<td class="row2" width="5%" align="center" valign="middle"><span class="gen">{catrow.forumrow.POSTS}</span></td>
				<td class="row1" width="15%" align="center" valign="middle"><span class="gensmall">{catrow.forumrow.LAST_POST}</span></td>
				<td class="row2" width="5%" align="center" valign="middle"><span class="gensmall">{catrow.forumrow.MODERATORS}</span></td></tr>
			</tr>
			<!-- END forumrow -->
			<!-- END catrow -->
		</table></td>
	</tr>
</table></div>

{S_FORMEND}
