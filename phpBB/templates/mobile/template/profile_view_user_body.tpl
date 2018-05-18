<!-- INCLUDE overall_header.tpl -->

<!-- INCLUDE profile_cpl_menu_inc_start.tpl -->

{IMG_THL}{IMG_THC}<span class="forumlink">{L_PROFILE_VIEWED}</span>{IMG_THR}<table class="forumlinenb">
<tr>
	<th class="tw50pct tdnw">{L_VIEWER}</th>
	<th class="tw25pct tdnw">{L_STAMP}</th>
	<th class="tw25pct tdnw">{L_NUMBER}</th>
</tr>
<!-- BEGIN row -->
<tr>
	<td class="row1 row-center">{row.AVATAR}<br />{row.VIEW_BY}</td>
	<td class="row1 row-center">{row.STAMP}</td>
	<td class="row1 row-center">{row.NUMBER}</td>
</tr>
<!-- END row -->
<tr><td class="cat" colspan="3">&nbsp;</td></tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}

<table>
<tr><td class="tdalignr tdnw"><span class="pagination">{PAGINATION}</span><br /></td></tr>
</table>

<!-- INCLUDE profile_cpl_menu_inc_end.tpl -->

<!-- INCLUDE overall_footer.tpl -->