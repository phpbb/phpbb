
<form action="{S_PROFILE_ACTION}" method="post">

<table width="98%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr>
		<td align="left"><span class="gensmall"><a href="{U_INDEX}">{L_INDEX}</a></span></td>
	</tr>
</table>

<table width="98%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="3" cellspacing="1" width="100%">
			<tr>
				<td class="cat" colspan="{S_COLSPAN}" align="center"><span class="cattitle">{L_AVATAR_GALLERY}</span></td>
			</tr>
			<tr>
				<th colspan="6">{L_CATEGORY}:&nbsp;<select name="avatarcategory">{S_OPTIONS_CATEGORIES}</select>&nbsp;<input type="submit" class="liteoption" value="{L_GO}" name="avatargallery" /></td>
			</tr>
			<!-- BEGIN avatar_row -->
			<tr> 
			<!-- BEGIN avatar_column -->
				<td class="row1" align="center"><img src="{avatar_row.avatar_column.AVATAR_IMAGE}" /></td>
			<!-- END avatar_column -->
			</tr>
			<tr>
			<!-- BEGIN avatar_option_column -->
				<td class="row2" align="center"><input type="radio" name="avatarselect" value="{avatar_row.avatar_option_column.S_OPTIONS_AVATAR}" /></td>
			<!-- END avatar_option_column -->
			</tr>
			<!-- END avatar_row -->
			<tr> 
				<td class="cat" colspan="{S_COLSPAN}" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submitavatar" value="{L_SELECT_AVATAR}" class="mainoptiontable" />&nbsp;&nbsp;<input type="submit" name="cancelavatar" value="{L_RETURN_PROFILE}" class="liteoptiontable" /></td>
			</tr>
		</table></td>
	</tr>
</table></form>
