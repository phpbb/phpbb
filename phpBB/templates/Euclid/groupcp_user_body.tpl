
<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td><span class="gensmall"><a href="{U_INDEX}">{L_INDEX}</a></span></td>
	</tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<!-- BEGIN groups_joined -->
			<tr>
				<td class="cat" colspan="2" height="30"><span class="cattitle">{L_GROUP_MEMBERSHIP_DETAILS}</span></td>
			</tr>
			<!-- BEGIN groups_member -->
			<tr>
				<td class="row1"><span class="gen">{L_YOU_BELONG_GROUPS}</span></td>
				<td class="row2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr><form method="post" action="{S_USERGROUP_ACTION}">
						<td width="50%" align="center">&nbsp;{GROUP_MEMBER_SELECT}&nbsp;</td>
						<td width="50%" align="center">&nbsp;<input class="mainoptiontable" type="submit" name="viewinfo" value="{L_VIEW_INFORMATION}">&nbsp;</td>
					</form></tr>
				</table></td>
			</tr>
			<!-- END groups_member -->
			<!-- BEGIN groups_pending -->
			<tr>
				<td class="row1"><span class="gen">{L_PENDING_GROUPS}</span></td>
				<td class="row2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr><form method="post" action="{S_USERGROUP_ACTION}">
						<td width="40%" align="center">&nbsp;{GROUP_PENDING_SELECT}&nbsp;</td>
						<td width="30%" align="center">&nbsp;<input class="mainoptiontable" type="submit" name="viewinfo" value="{L_VIEW_INFORMATION}">&nbsp;</td>
					</form></tr>
				</table></td>
			</tr>
			<!-- END groups_pending -->
			<!-- END groups_joined -->
			<!-- BEGIN groups_remaining -->
			<tr>
				<td class="cat" colspan="2" height="30"><span class="cattitle"><b>{L_JOIN_A_GROUP}</b></span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_SELECT_A_GROUP}</span></td>
				<td class="row2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr><form method="post" action="{S_USERGROUP_ACTION}">
						<td width="40%" align="center">&nbsp;{GROUP_LIST_SELECT}&nbsp;</td>
						<td width="30%" align="center">&nbsp;<input class="mainoptiontable" type="submit" name="viewinfo" value="{L_VIEW_INFORMATION}">&nbsp;</td>
						<td width="30%" align="center">&nbsp;</td>
					</form></tr>
				</table></td>
			</tr>
			<!-- END groups_remaining -->
		</table></td>
	</tr>
</table>

<br clear="all" />
