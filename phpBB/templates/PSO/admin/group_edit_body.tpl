
<form action="{S_GROUP_ACTION}" method="post"><table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left"><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
	</tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="3" cellspacing="1" width="100%">
			<tr>
				<td class="cat" colspan="2"><span class="cattitle"><b>{L_GROUP_INFO}</b></span><br /><span class="gensmall">{L_ITEMS_REQUIRED}</span></td>
			</tr>
			<tr>
				<td class="row1" width="38%"><span class="gen">{L_GROUP_NAME}:</span></td>
				<td class="row2"><input type="text" name="group_name" size="35" maxlength="40" value="{S_GROUP_NAME}" /></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_GROUP_DESCRIPTION}:</span></td>
				<td class="row2"><textarea name="group_description" rows=5 cols=51>{S_GROUP_DESCRIPTION}</textarea></td>
			</tr>
			<tr>
				<td class="row1" width="38%"><span class="gen">{L_GROUP_MODERATOR}:</span></td>
				<td class="row2">{S_GROUP_MODERATOR}</td>
			</tr>
			<tr>
				<td class="row1" width="38%"><span class="gen">{L_DELETE_MODERATOR}:</span>
				<br />
				<span class="gensmall">{L_DELETE_MODERATOR_EXPLAIN}</span></td>
				<td class="row2"><input type="checkbox" name="delete_old_moderator" value="1" />&nbsp;&nbsp;{L_YES}</td>
			</tr>
			<tr>
				<td class="row1" width="38%"><span class="gen">{L_GROUP_STATUS}:</span></td>
				<td class="row2"><input type="radio" name="group_type" value="1" {S_GROUP_OPEN_CHECKED} />{L_GROUP_OPEN} &nbsp;&nbsp;<input type="radio" name="group_type" value="0" {S_GROUP_CLOSED_CHECKED} /> {L_GROUP_CLOSED}</td>
			</tr>
			<tr>
				<td class="row1" width="38%"><span class="gen">{L_GROUP_DELETE}:</span></td>
				<td class="row2"><input type="checkbox" name="deletegroup" value="1">{L_GROUP_DELETE_CHECK}</td>
			</tr>
			<tr>
				<td class="cat" colspan="2" align="center"><span class="cattitle"><input type="submit" name="submit" value="{L_SUBMIT}" />&nbsp;&nbsp;<input type="reset" value="{L_RESET}" /></span></td>
			</tr>
		</table></td>
	</tr>

<input type="hidden" name="mode" value="{S_GROUP_MODE}" />
<input type="hidden" name="updategroup" value="update" />
<input type="hidden" name="group_id" value="{GROUP_ID}" />
</table></form>