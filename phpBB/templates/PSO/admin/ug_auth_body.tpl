<br clear="all" />

<h1>{L_USER_OR_GROUP} Authorisation Control</h1>

<form method="post" action="{S_USER_AUTH_ACTION}">

<h2>{L_USER_OR_GROUPNAME}: {USERNAME}</h2>

<p>{USER_GROUP_MEMBERSHIPS}</p>

<h2>Access to Forums</h2>

<p>Remember that there are two possible places for controlling access to forums, user and group auth control. Removing access rights from a user will not affect any rights granted via group membership. You will be warned if you remove access rights from a user (or group) but access is still granted via membership of a group (or via individual user rights)</p>

<table cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th width="30%">Forum Name</th>
		<!-- BEGIN acltype -->
		<th>{acltype.L_UG_ACL_TYPE}</th>
		<!-- END acltype -->
		<th>Moderator</th>
	</tr>
	<!-- BEGIN forums -->
	<tr>
		<td class="{forums.ROW_CLASS}" align="center"><a href="{forums.U_FORUM_AUTH}" onClick="open_new_window('{forums.U_FORUM_AUTH}');return false" target="_new">{forums.FORUM_NAME}</a></td>
		<!-- BEGIN aclvalues -->
		<td class="{forums.ROW_CLASS}" align="center">{forums.aclvalues.S_ACL_SELECT}</td>
		<!-- END aclvalues -->
		<td class="{forums.ROW_CLASS}" align="center">{forums.S_MOD_SELECT}</td>
	</tr>
	<!-- END forums -->
	<tr>
		<td colspan="{S_COLUMN_SPAN}"><table width="100%" cellspacing="0" cellpadding="4" border="0">
			<tr>
				<td align="center"><span class="gensmall">{U_SWITCH_MODE}</span></td>
			</tr>
			<tr>
				<td align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="Request Update">&nbsp;&nbsp;&nbsp;<input type="reset" value="Reset Changes"></td>
			</tr>
		</table></td>
	</tr>
</table>

</form>
