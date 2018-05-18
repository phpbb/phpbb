<!-- INCLUDE overall_header.tpl -->

<!-- INCLUDE profile_cpl_menu_inc_start.tpl -->

<form method="post" action="{S_ACTION}">
{IMG_THL}{IMG_THC}<span class="forumlink">{L_MOD_NAME}</span>{IMG_THR}<table class="forumlinenb">
<tr><td class="row-header" colspan="2"><span>{L_MOD_NAME}</span></td></tr>
<!-- BEGIN field -->
<tr>
	<td class="row1 tw50pct"><div class="gen">{field.L_NAME}</div><!-- IF field.L_EXPLAIN --><div class="gensmall">{field.L_EXPLAIN}</div><!-- ENDIF --></td>
	<td class="row2" width="50%" nowrap="nowrap"><div class="gen">{field.INPUT}</div></td>
</tr>
<!-- END field -->
<tr>
	<td class="cat" colspan="2">{S_HIDDEN_FIELDS}
		<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;
		<input type="reset" value="{L_RESET}" class="liteoption" />
	</td>
</tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
</form>

<!-- INCLUDE profile_cpl_menu_inc_end.tpl -->

<!-- INCLUDE overall_footer.tpl -->