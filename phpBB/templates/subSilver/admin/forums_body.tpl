<p>Use this page to manage your forums and categories</p>


{S_FORMSTART}
<div align="center"><table width="98%" cellpadding="1" cellspacing="0" border="0">
	<tr>
		<td class="tablebg"><table width="100%" cellpadding="3" cellspacing="1" border="0">
			<!-- BEGIN catrow -->
			<tr>
				<td class="cat" colspan="4"><span class="cattitle"><b><a href="{catrow.U_VIEWCAT}">{catrow.CAT_DESC}</a></b></span>{catrow.S_ADDCAT}</span></td>
				<td class="cat" align="center" valign="middle"><span class="gen">{catrow.CAT_EDIT}</span></td>
				<td class="cat" align="center" valign="middle"><span class="gen">{catrow.CAT_DELETE}</span></td>
				<td class="cat" align="center" valign="middle"><span class="gen">{catrow.CAT_UP} {catrow.CAT_DOWN}</span></td>
				<td class="cat" align="center" valign="middle"><span class="gen">&nbsp</span></td>

			</tr>{catrow.S_ADDCAT_ENDFORM}
			<!-- BEGIN forumrow -->
			<tr>
				<td class="row1" align="center" valign="middle">{catrow.forumrow.FOLDER}</td>
				<td class="row2"><span class="gen">{catrow.forumrow.S_ADDFORUM}{catrow.forumrow.S_NEWFORUM}<a href="{catrow.forumrow.U_VIEWFORUM}" target="_new">{catrow.forumrow.FORUM_NAME}</a></span><br><span class="gensmall">{catrow.forumrow.FORUM_DESC}</span></td>
			{catrow.forumrow.S_ADDFORUM_ENDFORM}
				<td class="row1" align="center" valign="middle"><span class="gen">{catrow.forumrow.NUM_TOPICS}</span></td>
				<td class="row2" align="center" valign="middle"><span class="gen">{catrow.forumrow.NUM_POSTS}</span></td>
				<td class="row1" align="center" valign="middle"><span class="gen">{catrow.forumrow.FORUM_EDIT}</span></td>
				<td class="row2" align="center" valign="middle"><span class="gen">{catrow.forumrow.FORUM_DELETE}</span></td>
				<td class="row1" align="center" valign="middle"><span class="gen">{catrow.forumrow.FORUM_UP} {catrow.forumrow.FORUM_DOWN}</span></td>
				<td class="row2" align="center" valign="middle"><span class="gen">{catrow.forumrow.FORUM_SYNC}</span></td>
			</tr>
			<!-- END forumrow -->
			<!-- END catrow -->
		</table></td>
	</tr>
</table></div>
