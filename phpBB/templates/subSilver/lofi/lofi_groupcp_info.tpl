<!-- INCLUDE ../common/lofi/lofi_header.tpl -->

<div class="index">
	<form action="{S_GROUPCP_ACTION}" name="group_info" method="post">
		<table class="s2px p2px">
		<tr><td class="nav"><a href="{U_INDEX}" class="nav">{L_INDEX}</a></td></tr>
		</table>
		<table class="s2px p2px">
			<tr><th colspan="7" height="25">{L_GROUP_INFORMATION}</th></tr>
			<tr>
				<td width="20%"><span class="gen">{L_GROUP_NAME}:</span></td>
				<td><span class="gen"><b>{GROUP_NAME}</b></span></td>
			</tr>
			<tr>
				<td width="20%"><span class="gen">{L_GROUP_DESC}:</span></td>
				<td><span class="gen">{GROUP_DESC}</span></td>
			</tr>
			<tr>
				<td class="row1 tw20pct"><span class="gen">{L_GROUP_MEMBERSHIP}:</span></td>
				<td class="row2">
					<span class="gen">{GROUP_DETAILS} &nbsp;&nbsp;
					<!-- BEGIN switch_subscribe_group_input -->
					<input class="mainoption" type="submit" name="joingroup" value="{L_JOIN_GROUP}" />
					<!-- END switch_subscribe_group_input -->
					<!-- BEGIN switch_unsubscribe_group_input -->
					<input class="mainoption" type="submit" name="unsub" value="{L_UNSUBSCRIBE_GROUP}" />
					<!-- END switch_unsubscribe_group_input -->
					</span>
				</td>
			</tr>
			<!-- BEGIN switch_mod_option -->
			<tr>
				<td class="row1 tw20pct"><span class="gen">{L_GROUP_TYPE}:</span></td>
				<td class="row2">
					<span class="gen">
						<input type="radio" name="group_type" value="{S_GROUP_OPEN_TYPE}" {S_GROUP_OPEN_CHECKED} /> {L_GROUP_OPEN} &nbsp;&nbsp;
						<input type="radio" name="group_type" value="{S_GROUP_CLOSED_TYPE}" {S_GROUP_CLOSED_CHECKED} />	{L_GROUP_CLOSED} &nbsp;&nbsp;
						<input type="radio" name="group_type" value="{S_GROUP_HIDDEN_TYPE}" {S_GROUP_HIDDEN_CHECKED} />	{L_GROUP_HIDDEN} &nbsp;&nbsp;
						<input class="mainoption" type="submit" name="groupstatus" value="{L_UPDATE}" />
					</span>
				</td>
			</tr>
			<!-- END switch_mod_option -->
		</table>
		{S_HIDDEN_FIELDS}
	</form>

	<form action="{S_GROUPCP_ACTION}" method="post" name="post">
		<table class="forumline">
		<tr>
			<th>{L_PM}</th>
			<th>{L_USERNAME}</th>
			<th>{L_POSTS}</th>
			<th>{L_FROM}</th>
			<th>{L_EMAIL}</th>
			<th>{L_WEBSITE}</th>
			<th>{L_SELECT}</th>
		</tr>
		<tr><td class="catSides" colspan="8" height="28"><span class="cattitle"><b>{L_GROUP_MODERATOR}</b></span></td></tr>
		<tr>
			<td class="row1 row-center"><span class="gen"> {MOD_PM} </td>
			<td class="row1 row-center"><span class="gen"><a href="{U_MOD_VIEWPROFILE}" class="gen">{MOD_USERNAME}</a></span></td>
			<td class="row1 row-center tvalignm"><span class="gen">{MOD_POSTS}</span></td>
			<td class="row1 row-center tvalignm"><span class="gen">{MOD_FROM}</span></td>
			<td class="row1 row-center tvalignm"><span class="gen">{MOD_EMAIL}</span></td>
			<td class="row1 row-center">{MOD_WWW}</td>
			<td class="row1 row-center"> &nbsp; </td>
		</tr>
		<tr><td class="catSides" colspan="8" height="28"><span class="cattitle"><b>{L_GROUP_MEMBERS}</b></span></td></tr>
		<!-- BEGIN member_row -->
		<tr>
			<td class="{member_row.ROW_CLASS}" align="center"> {member_row.PM} </td>
			<td class="{member_row.ROW_CLASS}" align="center"><span class="gen"><a href="{member_row.U_VIEWPROFILE}" class="gen">{member_row.USERNAME}</a></span></td>
			<td class="{member_row.ROW_CLASS}" align="center"><span class="gen">{member_row.POSTS}</span></td>
			<td class="{member_row.ROW_CLASS}" align="center"><span class="gen"> {member_row.FROM}</span></td>
			<td class="{member_row.ROW_CLASS}" align="center" valign="middle"><span class="gen">{member_row.EMAIL}</span></td>
			<td class="{member_row.ROW_CLASS}" align="center"> {member_row.WWW}</td>
			<td class="{member_row.ROW_CLASS}" align="center">
				<!-- BEGIN switch_mod_option -->
				<input type="checkbox" name="members[]" value="{member_row.USER_ID}" />
				<!-- END switch_mod_option -->
			</td>
		</tr>
		<!-- END member_row -->

		<!-- BEGIN switch_no_members -->
		<tr><td class="row1" colspan="7" align="center"><span class="gen">{L_NO_MEMBERS}</span></td></tr>
		<!-- END switch_no_members -->

		<!-- BEGIN switch_hidden_group -->
		<tr><td class="row1" colspan="7" align="center"><span class="gen">{L_HIDDEN_MEMBERS}</span></td></tr>
		<!-- END switch_hidden_group -->

		<!-- BEGIN switch_mod_option -->
		<tr>
			<td class="catBottom" colspan="8" align="right"><span class="cattitle"><input type="submit" name="remove" value="{L_REMOVE_SELECTED}" class="mainoption" /></td>
		</tr>
		<!-- END switch_mod_option -->
		</table>

		<table class="s2px p2px">
		<tr>
			<td>
				<!-- BEGIN switch_mod_option -->
				<span class="genmed">
					<input type="text"  class="post" name="username" maxlength="50" size="20" />&nbsp;
					<input type="submit" name="add" value="{L_ADD_MEMBER}" class="mainoption" />&nbsp;
					<input type="submit" name="usersubmit" value="{L_FIND_USERNAME}" class="liteoption" onclick="window.open('{U_SEARCH_USER}', '_search', 'width=400,height=250,resizable=yes');return false;" />
				</span>
				<br /><br />
				<!-- END switch_mod_option -->
				<span class="gensmall">{PAGE_NUMBER}</span>
			</td>
			<td class="tdalignr"><span class="gensmall">{S_TIMEZONE}</span><br /><span class="pagination">{PAGINATION}</span></td>
		</tr>
		</table>

		{PENDING_USER_BOX}

		{S_HIDDEN_FIELDS}
	</form>
	<table class="s2px p2px"><tr><td class="tdalignr">{JUMPBOX}</td></tr></table>
</div>
<br />

<!-- INCLUDE ../common/lofi/lofi_footer.tpl -->