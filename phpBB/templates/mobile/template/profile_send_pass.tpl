<!-- INCLUDE overall_header.tpl -->

<form action="{S_PROFILE_ACTION}" method="post">
{ERROR_BOX}
{IMG_THL}{IMG_THC}<span class="forumlink">{L_SEND_PASSWORD}</span>{IMG_THR}<table class="forumlinenb">
<tr><td class="row-header" colspan="2"><span>{L_SEND_PASSWORD}</span></td></tr>
<tr><th colspan="2"><span class="gensmall">{L_ITEMS_REQUIRED}</span></th></tr>
<tr>
	<td class="row1" width="38%"><span class="gen">{L_USERNAME}:&nbsp;*</span></td>
	<td class="row2"><input type="text" class="post" style="width: 200px" name="username" size="25" maxlength="40" value="{USERNAME}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_EMAIL_ADDRESS}:&nbsp;*</span></td>
	<td class="row2"><input type="text" class="post" style="width: 200px" name="email" size="25" maxlength="255" value="{EMAIL}" /></td>
</tr>
<tr>
	<td class="catBottom" colspan="2">{S_HIDDEN_FIELDS}
		<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;
		<input type="reset" value="{L_RESET}" name="reset" class="liteoption" />
	</td>
</tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
</form>

<!-- INCLUDE overall_footer.tpl -->