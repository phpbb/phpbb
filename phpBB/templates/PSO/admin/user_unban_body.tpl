
<br clear="all" />

<h1>{L_BAN_TITLE}</h1>

<p>{L_BAN_EXPLAIN}</p>

<form method="post" action="{S_BAN_ACTION}"><table width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<td class="cat" colspan="2" align="center">{L_BAN_USER}</td>
	</tr>
	<tr>
		<td class="row1">{L_USERNAME}:&nbsp;<br /><span class="gensmall">{L_BAN_USER_EXPLAIN}</span></td>
		<td class="row2">{S_USERLIST_SELECT}</td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center">{L_BAN_IP}</td>
	</tr>
	<tr>
		<td class="row1">{L_IP_OR_HOSTNAME}:&nbsp;<br /><span class="gensmall">{L_BAN_IP_EXPLAIN}</span></td>
		<td class="row2">{S_IPLIST_SELECT}</td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center">{L_BAN_EMAIL}</td>
	</tr>
	<tr>
		<td class="row1">{L_EMAIL_ADDRESS}:&nbsp;<br /><span class="gensmall">{L_BAN_EMAIL_EXPLAIN}</span></td>
		<td class="row2">{S_EMAILLIST_SELECT}</td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SUBMIT}" />&nbsp;&nbsp;<input type="reset" value="{L_RESET}" /></td>
	</tr>
</table></form>

<br	clear="all" />
