
<br clear="all" />

<h1>{L_AUTH_TITLE}</h1>

<p>{L_AUTH_EXPLAIN}</p>

<h2>{L_FORUM}: {FORUM_NAME}</h2>

<table cellspacing="1" cellpadding="4" border="0" align="center">
	<tr><form method="post" action="{S_FORUMAUTH_ACTION}">
		<!-- BEGIN forum_auth_titles -->
		<th>{forum_auth_titles.CELL_TITLE}</th>
		<!-- END forum_auth_titles -->
	</tr>
	<tr>
		<!-- BEGIN forum_auth_data -->
		<td class="row1" align="center">{forum_auth_data.S_AUTH_LEVELS_SELECT}</td>
		<!-- END forum_auth_data -->
	</tr>
	<tr>
		<td colspan="{S_COLUMN_SPAN}"><table width="100%" cellspacing="0" cellpadding="4" border="0">
			<tr>
				<td align="center"><span class="gensmall">{U_SWITCH_MODE}</span></td>
			</tr>
			<tr>
				<td align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SUBMIT_CHANGES}">&nbsp;&nbsp;<input type="reset" value="{L_RESET_CHANGES}"></td>
			</tr>
		</table></td>
	</form></tr>
</table>

<br clear="all" />
