<!-- INCLUDE overall_header.tpl -->

<script type="text/javascript">
<!--
function add_username(selected_username)
{
	if (document.forms['post'].add.value == '')
	{
		document.forms['post'].add.value = selected_username;
	}
	else
	{
		document.forms['post'].add.value = document.forms['post'].add.value + "\n" + selected_username;
	}
}
//-->
</script>

<!-- INCLUDE profile_cpl_menu_inc_start.tpl -->

<form action="{S_PROFILE_ACTION}" name="post" method="post">
{IMG_THL}{IMG_THC}<span class="forumlink">{L_FRIENDS}</span>{IMG_THR}<table class="forumlinenb">
<!-- <tr><th colspan="2"><span class="genmed"><b>{L_FRIENDS}</b></span></th></tr> -->
<tr>
	<td class="row1" width="40%"><b class="genmed">{L_YOUR_FRIENDS}:</b><br /><span class="gensmall">{L_YOUR_FRIENDS_EXPLAIN}</span></td>
	<td class="row2 row-center">
		<!-- IF S_USERNAME_OPTIONS -->
		<select name="usernames[]" multiple="multiple" size="5">{S_USERNAME_OPTIONS}</select>
		<!-- ELSE -->
		<b class="genmed">{L_NO_FRIENDS}</b>
		<!-- ENDIF -->
	</td>
</tr>
<tr>
	<td class="row1">
		<b class="genmed">{L_ADD_FRIENDS}:</b><br /><span class="gensmall">{L_ADD_FRIENDS_EXPLAIN}</span>
	</td>
	<td class="row2 row-center">
		<input type="text" class="post" name="username" id="username" maxlength="50" size="20" {S_AJAX_USER_CHECK} />&nbsp;
		<span id="username_list" style="display: none;">&nbsp;<span id="username_select">&nbsp;</span></span>
		<input type="button" value="{L_ADD_MEMBER}" class="mainoption" onclick="add_username(this.form.username.value);return false;" />&nbsp;
		<input type="submit" name="usersubmit" value="{L_FIND_USERNAME}" class="liteoption" onclick="window.open('{U_SEARCH_USER}', '_search', 'width=400,height=250,resizable=yes'); return false;" /><br /><br />
		<textarea name="add" rows="5" cols="30">{USERNAMES}</textarea><br />
	</td>
</tr>
<tr>
	<td class="cat" colspan="2">
		{S_HIDDEN_FIELDS}
		<input type="submit" name="submit" class="mainoption" value="{L_SUBMIT}" />&nbsp;&nbsp;
		<input type="submit" name="reset" class="liteoption" value="{L_RESET}" />
	</td>
</tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
</form>

<!-- INCLUDE profile_cpl_menu_inc_end.tpl -->

<!-- INCLUDE overall_footer.tpl -->