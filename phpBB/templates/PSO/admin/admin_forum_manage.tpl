<h1>Manage Forums</h1>
<p>
	With the form below you can get a quick overview of your forums, change their ordering, modify their settings, lock forums, and delete unwanted forums.
	To modify forum permissions use the <u>Permissions</u> link on the left hand side.
</p>
<br />

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellpadding="3" cellspacing="1" border="0">
			<tr>
				<th>&nbsp;{L_FORUM}&nbsp;</th>
				<th>&nbsp;{L_MODERATOR}&nbsp;</th>
				<th>&nbsp;{L_ORDER}&nbsp;</th>
				<th>&nbsp;{L_ACTION}&nbsp;</th>
			</tr>
			<!-- BEGIN catrow -->
			<tr>
				<td class="cat" colspan="6"><span class="cattitle"><b>{catrow.CAT_DESC}</b>&nbsp;</span></td>
			</tr>
			<!-- BEGIN forumrow -->
			<form action="{S_MANAGE_ACTION}"><input type="hidden" name="mode" value="manage">
			<tr>
				<td class="row1"><span class="gen"><a href="{catrow.forumrow.U_VIEWFORUM}">{catrow.forumrow.FORUM_NAME}</a></span><br /><span class="gensmall">{catrow.forumrow.FORUM_DESC}</span></td>
				<td class="row2" width="5%" align="center" valign="middle"><span class="gensmall">{catrow.forumrow.MODERATORS}</span></td>
				<td class="row2" width="5%" align="center" valign="middle"><input type="text" size="2" maxlength="4" value="{catrow.forumrow.FORUM_ORDER}" name="order"></td>
				<td class="row2">
					<input type="hidden" name="{POST_FORUM_URL}" value="{catrow.forumrow.FORUM_ID}">
					<input type="submit" name="reorder" value="{L_UPDATE_ORDER}">&nbsp;
					<input type="submit" name="lock" value="{L_LOCK}">&nbsp;
					<input type="submit" name="edit" value="{L_EDIT}">&nbsp;
					<input type="submit" name="delete" value="{L_REMOVE}">
				</td>
			</tr>
			</form>
			<!-- END forumrow -->
			<!-- END catrow -->
		</table></td>
	</tr>
</table>
