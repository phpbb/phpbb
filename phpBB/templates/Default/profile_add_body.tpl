<tr>
  <td><form action="profile.{PHPEX}" method="POST">
   <table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
    <tr>
      <td>
         <table border="0" width="100%" cellpadding="3" cellspacing="1">
         	<tr class="tableheader">
         		<td colspan="2"><b>{L_REGINFO}</b> ({L_ITEMSREQ})</td>
         	</tr>
	      	<tr class="tablebody">
	           <td bgcolor="#DDDDDD"><b>{L_USERNAME}: *</b><br>{L_USERUNIQ}</td>
	           <td bgcolor="#CCCCCC"><input type="text" name="username" size="35" maxlenght="40" value="{USERNAME}"></td>
            </tr>
            <tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_PASSWORD}: *</b></td>
					<td bgcolor="#CCCCCC"><input type="password" name="password" size="35" maxlenght="100" value="{PASSWORD}"></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_CONFIRM} {L_PASSWORD}: *</b></td>
					<td bgcolor="#CCCCCC"><input type="password" name="password_confirm" size="35" maxlenght="100" value="{PASSWORD_CONFIRM}"></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_EMAILADDRESS}: *</b></td>
					<td bgcolor="#CCCCCC"><input type="text" name="email" size="35" maxlength="255" value="{EMAIL}"></td>
				</tr>
				<tr class="tableheader">
					<td colspan="2"><b><b>{L_PROFILEINFO}</b></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_ICQNUMBER}:</b></td>
					<td bgcolor="#CCCCCC"><input type="text" name="icq" size="10" maxlength="15" value="{ICQ}"></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_AIM}:</b></td>
					<td bgcolor="#CCCCCC"><input type="text" name="aim" size="20" maxlength="255" value="{AIM}"></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_MESSENGER}:</b></td>
					<td bgcolor="#CCCCCC"><input type="text" name="msn" size="20" maxlength="255" value="{MSN}"></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_YAHOO}:</b></td>
					<td bgcolor="#CCCCCC"><input type="text" name="yim" size="20" maxlength="255" value="{YIM}"></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_WEBSITE}:</b></td>
					<td bgcolor="#CCCCCC"><input type="text" name="website" size="35" maxlength="255" value="{WEBSITE}"></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_FROM}:</b></td>
					<td bgcolor="#CCCCCC"><input type="text" name="from" size="35" maxlength="100" value="{FROM}"></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_OCC}:</b></td>
					<td bgcolor="#CCCCCC"><input type="text" name="occ" size="35" maxlength="100" value="{OCC}"></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_INTERESTS}:</b></td>
					<td bgcolor="#CCCCCC"><input type="text" name="interests" size="35" maxlength="150" value="{INTERESTS}"></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_SIGNATURE}:</b><br><font style="{font-size: 8pt;}">{L_SIGEXPLAIN}</font></td>
					<td bgcolor="#CCCCCC"><textarea name="sig" rows="6" cols="45">{SIG}</textarea></td>
				</tr>
				<tr class="tableheader">
					<td colspan="2"><b>{L_PREFERENCES}</b></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_PUBLICMAIL}:</b></td>
					<td bgcolor="#CCCCCC"><input type="radio" name="viewemail" value="1" {VIEWEMAIL_YES}> {L_YES}&nbsp;&nbsp;
												 <input type="radio" name="viewemail" value="0" {VIEWEMAIL_NO}> {L_NO}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_STORECOOKIE}:</b></td>
					<td bgcolor="#CCCCCC"><input type="radio" name="storeusername" value="1" {STOREUSERNAME_YES}> {L_YES}&nbsp;&nbsp;
												 <input type="radio" name="storeusername" value="0" {STOREUSERNAME_NO}> {L_NO}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_ALWAYSSIG}:</b></td>
					<td bgcolor="#CCCCCC"><input type="radio" name="alwayssig" value="1" {ALWAYSSIG_YES}> {L_YES}&nbsp;&nbsp;
												 <input type="radio" name="alwayssig" value="0" {ALWAYSSIG_NO}> {L_NO}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_ALWAYSBBCODE}:</b></td>
					<td bgcolor="#CCCCCC"><input type="radio" name="alwaysbbcode" value="1" {ALWAYSBBCODE_YES}> {L_YES}&nbsp;&nbsp;
												 <input type="radio" name="alwaysbbcode" value="0" {ALWAYSBBCODE_NO}> {L_NO}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_ALWAYSHTML}:</b></td>
					<td bgcolor="#CCCCCC"><input type="radio" name="alwayshtml" value="1" {ALWAYSHTML_YES}> {L_YES}&nbsp;&nbsp;
												 <input type="radio" name="alwayshtml" value="0" {ALWAYSHTML_NO}> {L_NO}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_ALWAYSSMILE}:</b></td>
					<td bgcolor="#CCCCCC"><input type="radio" name="alwayssmile" value="1" {ALWAYSSMILE_YES}> {L_YES}&nbsp;&nbsp;
												 <input type="radio" name="alwayssmile" value="0" {ALWAYSSMILE_NO}> {L_NO}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_BOARDLANG}:</b></td>
					<td bgcolor="#CCCCCC">{LANGUAGE_SELECT}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_BOARDTHEME}:</b></td>
					<td bgcolor="#CCCCCC">{THEME_SELECT}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_BOARDTEMPLATE}:</b></td>
					<td bgcolor="#CCCCCC">{TEMPLATE_SELECT}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_TIMEZONE}:</b></td>
					<td bgcolor="#CCCCCC">{TIMEZONE_SELECT}</td>
				</tr>
			<tr class="tableheader">
		   	<td align="center" colspan="2">
					<input type="hidden" name="mode" value="{MODE}">
					<input type="hidden" name="agreed" value="true">
					<input type="hidden" name="coppa" value="{COPPA}">
					<input type="submit" name="submit" value="{L_SUBMIT}">&nbsp;
			</tr>
			</table>
		</td>
	</tr>
</table>
</form>
</td>
</tr>
