
<script language="javascript" type="text/javascript">
<!--
function refresh_username(selected_username)
{
	opener.document.forms['post'].username.value = selected_username;
}
//-->
</script>

<form method="post" name="search" action="{S_SEARCH_ACTION}"><table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" border="0" cellspacing="1" cellpadding="3">
			<tr>
				<th>{L_SEARCH_USERNAME}</th>
			</tr>
			<tr>
				<td valign="top" class="row1"><span class="gensmall"><br /><input type="text" name="search_author" value="{AUTHOR}" class="post" />&nbsp;<input class="mainoptiontable" type="submit" name="search" value="{L_SEARCH}" class="liteoption" /></span><br /><span class="gensmall">{L_SEARCH_EXPLAIN}</span><br />
				<!-- BEGIN switch_select_name -->
				<span class="gensmall">{L_UPDATE_USERNAME}<br /><select name="author_list">{S_AUTHOR_OPTIONS}</select>&nbsp;<input class="liteoptiontable" type="submit" class="liteoption" onClick="refresh_username(this.form.author_list.options[this.form.author_list.selectedIndex].value);return false;" name="use" value="{L_SELECT}" /></span><br />
				<!-- END switch_select_name -->
				<br /><span class="gensmall"><a href="javascript:window.close();" class="genmed">{L_CLOSE_WINDOW}</a></span></td>
			</tr>
		</table></td>
	</tr>
</table></form>
