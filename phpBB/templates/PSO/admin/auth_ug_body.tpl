
<br clear="all" />

<h1>{L_AUTH_TITLE}</h1>

<h2>{L_USER_OR_GROUPNAME}: {USERNAME}</h2>

<form method="post" action="{S_AUTH_ACTION}">

<p>{USER_GROUP_MEMBERSHIPS}</p>

<h2>{L_PERMISSIONS}</h2>

<p>{L_AUTH_EXPLAIN}</p>

<table cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th width="30%">{L_FORUM}</th>
		<!-- BEGIN acltype -->
		<th>{acltype.L_UG_ACL_TYPE}</th>
		<!-- END acltype -->
		<th>{L_MODERATOR_STATUS}</th>
	</tr>
	<!-- BEGIN forums -->
	<tr>
		<td class="{forums.ROW_CLASS}" align="center">{forums.FORUM_NAME}</td>
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
				<td align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SUBMIT_CHANGES}" />&nbsp;&nbsp;<input type="reset" value="{L_RESET_CHANGES}" /></td>
			</tr>
		</table></td>
	</tr>
</table></form>
