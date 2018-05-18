<!-- INCLUDE overall_header.tpl -->

<form action="{S_PROFILE_ACTION}" method="post">
{CPL_MENU_OUTPUT}
{IMG_THL}{IMG_THC}<span class="forumlink">{L_AVATAR_GALLERY}</span>{IMG_THR}<table class="forumlinenb">
	<tr><th colspan="{S_COLSPAN}">{L_CATEGORY}:&nbsp;{S_CATEGORY_SELECT}&nbsp;<input type="submit" class="liteoption" value="{L_GO}" name="avatargallery" /></th></tr>
	<!-- BEGIN avatar_row -->
	<tr>
		<!-- BEGIN avatar_column -->
		<td class="row1 row-center">
			<img src="{avatar_row.avatar_column.AVATAR_IMAGE}" alt="{avatar_row.avatar_column.AVATAR_NAME}" title="{avatar_row.avatar_column.AVATAR_NAME}" />
		</td>
		<!-- END avatar_column -->
	</tr>
	<tr>
		<!-- BEGIN avatar_option_column -->
		<td class="row2 row-center"><input type="radio" name="avatarselect" value="{avatar_row.avatar_option_column.S_OPTIONS_AVATAR}" /></td>
		<!-- END avatar_option_column -->
	</tr>
	<tr><td class="spaceRow" colspan="{S_COLSPAN}"><img src="{SPACER}" width="1" height="3" alt="" /></td></tr>
	<!-- END avatar_row -->
	<tr>
		<td class="cat" colspan="{S_COLSPAN}">{S_HIDDEN_FIELDS}
			<input type="submit" name="submitavatar" value="{L_SELECT_AVATAR}" class="mainoption" />
			&nbsp;&nbsp;
			<input type="button" name="cancelavatar" value="{L_RETURN_PROFILE}" class="liteoption" />
		</td>
	</tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
	</td>
	</tr>
</table>
</form>

<!-- INCLUDE overall_footer.tpl -->