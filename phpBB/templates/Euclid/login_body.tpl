<div align="center"><table width="60%" cellspacing="0" cellpadding="4" border="0">
	<tr>
		<td align="left"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><a href="/">{SITENAME}&nbsp;{L_INDEX}</a></font></td>
	</tr>
</table></div>

<div align="center"><table border="0" cellpadding="1" cellspacing="0" width="60%">
	<tr><form action="{S_LOGIN_ACTION}" method="post">
		<td bgcolor="{T_TH_COLOR1}"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
				<th colspan="2" bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}"><p><b>Please enter your username and password to login</b></p></font></th>
			</tr>
			<tr>
				<td width="45%" bgcolor="{T_TD_COLOR1}" align="right"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR2}">{L_USERNAME}:&nbsp;</font></td><td bgcolor="{T_TD_COLOR2}"><input type="text" name="username" size="25" maxlength="40" value="{USERNAME}"></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}" align="right"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR2}">{L_PASSWORD}:&nbsp;</font></td><td bgcolor="{T_TD_COLOR2}"><input type="password" name="password" size="25" maxlength="25"></td>
			</tr>
			<tr>
				<td colspan="2" bgcolor="{T_TH_COLOR2}"><table width="100%" cellspacing="0" cellpadding="6" border="0">
					<tr>
						<td align="center"><font face="{T_FONTFACE1}" size="2" color="{T_FONTCOLOR2}">{L_AUTO_LOGIN}</font>:&nbsp;<input type="checkbox" name="autologin"></td>
					</tr>
					<tr>
						<td align="center"><input type="hidden" name="login" value="login"><input type="hidden" name="forward_page" value="{FORWARD_PAGE}"><input type="submit" name="submit" value="{L_LOGIN}"></td>
					</tr>
					<tr>
						<td align="center"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><a href="{U_SEND_PASSWORD}">{L_SEND_PASSWORD}</a></font></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</form></tr>
</table></div>

<div align="center"><table align="center" border="0" width="60%">
		<td align="right"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}"><b>{S_TIMEZONE}</b></font></td>
</table>
