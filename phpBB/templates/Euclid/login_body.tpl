
<form action="{S_LOGIN_ACTION}" method="post">

<table width="80%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td><span class="gensmall"><a href="{U_INDEX}">{L_INDEX}</a></span></td>
	</tr>
</table>

<table width="80%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<td class="cat" colspan="2" height="30" align="center"><span class="cattitle">{L_ENTER_PASSWORD}</span></td>
			</tr>
			<tr>
				<td class="row1" width="45%" align="right"><span class="gen">{L_USERNAME}: </span></td>
				<td class="row1"><input type="text" name="username" size="25" maxlength="30" value="{USERNAME}" /></td>
			</tr>
			<tr>
				<td class="row2" align="right"><span class="gen">{L_PASSWORD}: </span></td>
				<td class="row2"><input type="password" name="password" size="25" maxlength="25" /></td>
			</tr>
			<tr>
				<td class="row1" colspan="2"><table width="100%" cellspacing="0" cellpadding="6" border="0">
					<tr>
						<td align="center"><span class="gen">{L_AUTO_LOGIN}</font>: <input type="checkbox" name="autologin" /></span></td>
					</tr>
					<tr>
						<td align="center">{S_HIDDEN_FIELDS}<input class="mainoptiontable" type="submit" name="login" value="{L_LOGIN}" /></td>
					</tr>
					<tr>
						<td align="center"><span class="gensmall"><a href="{U_SEND_PASSWORD}">{L_SEND_PASSWORD}</a></span></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<table width="80%" border="0" align="center">
	<tr>
		<td valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
	</tr>
</table>

<br clear="all" />
