<!-- INCLUDE ../common/lofi/bots/lofi_bots_header.tpl -->

<div class="forumline nav-div">
	<p class="nav-header">
		<a href="{U_INDEX}">{L_INDEX}</a>{NAV_SEP}<a href="{U_REFERERS}" class="nav-current">{L_REFERERS}</a>
	</p>
	<div class="nav-links">
		<div class="nav-links-left">{CURRENT_TIME}</div>
		&nbsp;
	</div>
</div>

<form method="post" action="{S_MODE_ACTION}" name="refersrow_values">
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="forumline">
<tr>
	<th class="tdnw">#</th>
	<th class="tdnw">{L_URL}</th>
	<th class="tdnw">{L_HITS}</th>
	<th class="tdnw">{L_FIRST}</th>
	<th class="tdnw">{L_LAST}</th>
	<!-- BEGIN switch_admin_or_mod -->
	<th class="tdnw">{L_IP}</th>
	<th class="tdnw">{L_SELECT}</th>
	<!-- END switch_admin_or_mod -->
</tr>
<!-- BEGIN refersrow -->
<tr>
	<td class="row1 row-center"><span class="gen">{refersrow.ID}</span></td>
	<td class="row1" ><span class="gen">{refersrow.URL}</span></td>
	<td class="row1 row-center"><span class="gen">{refersrow.HITS}</span></td>
	<td class="row1 row-center"><span class="gen">{refersrow.FIRST}</span></td>
	<td class="row1 row-center"><span class="gen">{refersrow.LAST}</span></td>
	<!-- BEGIN switch_admin_or_mod -->
	<td class="row1 row-center"><span class="gen">{refersrow.IP}</span></td>
	<td class="row1 row-center"><span class="gensmall"><input type="checkbox" name="delete_id_{refersrow.REFER_ID}"></span></td>
	<!-- END switch_admin_or_mod -->
</tr>
<!-- END refersrow -->
<tr>
	<td class="catBottom" colspan="6" height="28">&nbsp;</td>
	<!-- BEGIN switch_admin_or_mod -->
	<td class="catBottom tdalignc">
	<input type="submit" name="delete_sub" value="{L_DELETE}" class="liteoption"></td>
	<!-- END switch_admin_or_mod -->
</tr>
</table>
</form>
<form method="post" action="{S_MODE_ACTION}">
<table>
<tr>
	<td><span class="gen">{PAGE_NUMBER}</span></td>
	<td class="tdalignr">{JUMPBOX}</td>
</tr>
</table>
<table class="s2px p2px">
	<tr>
		<td class="tdalignr"><span class="nav">

		</td>
	</tr>
	</table>
	<table class="s2px p2px"><tr><td class="tdalignr"></td></tr></table>

<!-- INCLUDE ../common/lofi/bots/lofi_bots_footer.tpl -->