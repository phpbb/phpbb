<tr>
	<td><br clear="all" /><br />
	
<table border="0" align="center" width="60%" bgcolor="#000000" cellpadding="0" cellspacing="1">
    <tr><form action="{S_LOGIN_ACTION}" method="post">
	    <td><table border="0" width="100%" cellpadding="3" cellspacing="1">
			<tr class="tableheader">
				<td colspan="2" align="center"><b>Please enter your username and password to login</b></td>
			</tr>
			<tr bgcolor="#DDDDDD" class="tablebody">
				<td width="45%" align="right">{L_USERNAME}:&nbsp;</td>
				<td><input type="text" name="username" size="25" maxlength="40" value="{USERNAME}"></td>
			</tr>
			<tr bgcolor="#DDDDDD" class="tablebody">
				<td align="right">{L_PASSWORD}:&nbsp;</td>
				<td><input type="password" name="password" size="25" maxlength="25"></td>
			</tr>
			<tr bgcolor="#DDDDDD" class="tablebody">
				<td colspan="2" align="center">{L_AUTO_LOGIN}:&nbsp;<input type="checkbox" name="autologin"></td>
			</tr>
			<tr bgcolor="#DDDDDD" class="tablebody">
				<td colspan="2"><table width="100%" cellspacing="0" cellpadding="6" border="0">
					<tr>
						<td align="center"><input type="hidden" name="forward_page" value="{FORWARD_PAGE}"><input type="submit" name="submit" value="{L_LOGIN}"></td>
					</tr>
					<tr>
						<td align="center"><a href="{U_SEND_PASSWORD}">{L_SEND_PASSWORD}</a></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</form></tr>
</table></td>

	</td>
</tr>