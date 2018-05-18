<!-- INCLUDE ../common/lofi/lofi_header.tpl -->

<table class="s2px p2px">
<tr><td><span class="nav"><a href="{U_INDEX}" class="nav">{L_INDEX}</a></span></td></tr>
</table>
<div class="index">
<table class="forumline">
	<!-- BEGIN switch_groups_joined -->
	<tr><th colspan="2" class="thHead">{L_GROUP_MEMBERSHIP_DETAILS}</th></tr>
	<!-- BEGIN switch_groups_member -->
	<tr>
		<td class="row1"><span class="gen">{L_YOU_BELONG_GROUPS}</span></td>
		<td class="row2 tdalignr">
			<form method="get" action="{S_USERGROUP_ACTION}">
			<table class="tw90pct">
				<tr>
					<td width="40%"><span class="gensmall">{GROUP_MEMBER_SELECT}</span></td>
					<td align="center" width="30%"><input type="submit" value="{L_VIEW_INFORMATION}" class="liteoption" />{S_HIDDEN_FIELDS}</td>
				</tr>
			</table>
			</form>
		</td>
	</tr>
	<!-- END switch_groups_member -->
	<!-- BEGIN switch_groups_pending -->
	<tr>
	<td class="row1"><span class="gen">{L_PENDING_GROUPS}</span></td>
	<td class="row2 tdalignr">
		<form method="get" action="{S_USERGROUP_ACTION}">
		<table class="tw90pct">
			<tr>
				<td width="40%"><span class="gensmall">{GROUP_PENDING_SELECT}</span></td>
				<td align="center" width="30%"><input type="submit" value="{L_VIEW_INFORMATION}" class="liteoption" />{S_HIDDEN_FIELDS}</td>
			</tr>
		</table>
		</form>
	</td>
	</tr>
	<!-- END switch_groups_pending -->
	<!-- END switch_groups_joined -->
	<!-- BEGIN switch_groups_remaining -->
	<tr><th colspan="2" class="thHead">{L_JOIN_A_GROUP}</th></tr>
	<tr>
	<td class="row1"><span class="gen">{L_SELECT_A_GROUP}</span></td>
	<td class="row2 tdalignr">
		<form method="get" action="{S_USERGROUP_ACTION}">
		<table class="tw90pct">
		<tr>
			<td width="40%"><span class="gensmall">{GROUP_LIST_SELECT}</span></td>
			<td align="center" width="30%"><input type="submit" value="{L_VIEW_INFORMATION}" class="liteoption" />{S_HIDDEN_FIELDS}</td>
		</tr>
		</table>
		</form>
	</td>
	</tr>
	<!-- END switch_groups_remaining -->
</table>

<table class="s2px p2px">
<tr><td class="tdalignr"><span class="gensmall">{S_TIMEZONE}</span></td></tr>
</table>

<br class="clear" />

<table class="s2px p2px">
<tr><td class="tdalignr">{JUMPBOX}</td></tr>
</table>
</div>
</div>
<br />

<!-- INCLUDE ../common/lofi/lofi_footer.tpl -->