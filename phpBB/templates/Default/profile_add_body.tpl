<tr>
  <td><form action="{PHP_SELF}" method="POST">
   <table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
    <tr>
      <td>
         <table border="0" width="100%" cellpadding="3" cellspacing="1">
         	<tr class="tableheader">
         		<td colspan="2"><b>{L_REGISTRATION_INFO}</b> ({L_ITEMS_REQUIRED})</td>
         	</tr>
	      	<tr class="tablebody">
	           <td bgcolor="#DDDDDD"><b>{L_USERNAME}: *</b><br>{L_USER_UNIQ}</td>
	           <td bgcolor="#CCCCCC"><input type="text" name="username" size="35" maxlength="40" value="{USERNAME}"></td>
            </tr>
			<tr class="tablebody">
				<td bgcolor="#DDDDDD"><b>{L_EMAIL_ADDRESS}: *</b></td>
				<td bgcolor="#CCCCCC"><input type="text" name="email" size="35" maxlength="255" value="{EMAIL}"></td>
			</tr>
            <tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_PASSWORD}: *</b><br /><font style="{font-size: 8pt;}">{L_PASSWORD_IF_CHANGED}</font></td>
					<td bgcolor="#CCCCCC"><input type="password" name="password" size="35" maxlength="100" value="{PASSWORD}"></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_CONFIRM} {L_PASSWORD}: *</b><br /><font style="{font-size: 8pt;}">{L_PASSWORD_CONFIRM_IF_CHANGED}</font></td>
					<td bgcolor="#CCCCCC"><input type="password" name="password_confirm" size="35" maxlength="100" value="{PASSWORD_CONFIRM}"></td>
				</tr>
				<tr class="tableheader">
					<td colspan="2"><b>{L_PROFILE_INFO}</b> ({L_PROFILE_INFO_NOTICE})</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_ICQ_NUMBER}:</b></td>
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
					<td bgcolor="#DDDDDD"><b>{L_LOCATION}:</b></td>
					<td bgcolor="#CCCCCC"><input type="text" name="location" size="35" maxlength="100" value="{LOCATION}"></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_OCCUPATION}:</b></td>
					<td bgcolor="#CCCCCC"><input type="text" name="occupation" size="35" maxlength="100" value="{OCCUPATION}"></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_INTERESTS}:</b></td>
					<td bgcolor="#CCCCCC"><input type="text" name="interests" size="35" maxlength="150" value="{INTERESTS}"></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_SIGNATURE}:</b><br><font style="{font-size: 8pt;}">{L_SIGNATURE_EXPLAIN}</font></td>
					<td bgcolor="#CCCCCC"><textarea name="signature" rows="6" cols="45">{SIGNATURE}</textarea></td>
				</tr>
				<tr class="tableheader">
					<td colspan="2"><b>{L_PREFERENCES}</b></td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_PUBLIC_VIEW_EMAIL}:</b></td>
					<td bgcolor="#CCCCCC"><input type="radio" name="viewemail" value="1" {VIEW_EMAIL_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="viewemail" value="0" {VIEW_EMAIL_NO}> {L_NO}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_ALWAYS_ADD_SIGNATURE}:</b></td>
					<td bgcolor="#CCCCCC"><input type="radio" name="attachsig" value="1" {ALWAYS_ADD_SIGNATURE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="attachsig" value="0" {ALWAYS_ADD_SIGNATURE_NO}> {L_NO}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_ALWAYS_ALLOW_BBCODE}:</b></td>
					<td bgcolor="#CCCCCC"><input type="radio" name="allowbbcode" value="1" {ALWAYS_ALLOW_BBCODE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allowbbcode" value="0" {ALWAYS_ALLOW_BBCODE_NO}> {L_NO}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_ALWAYS_ALLOW_HTML}:</b></td>
					<td bgcolor="#CCCCCC"><input type="radio" name="allowhtml" value="1" {ALWAYS_ALLOW_HTML_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allowhtml" value="0" {ALWAYS_ALLOW_HTML_NO}> {L_NO}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_ALWAYS_ALLOW_SMILIES}:</b></td>
					<td bgcolor="#CCCCCC"><input type="radio" name="allowsmilies" value="1" {ALWAYS_ALLOW_SMILIES_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allowsmilies" value="0" {ALWAYS_ALLOW_SMILIES_NO}> {L_NO}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_BOARD_LANGUAGE}:</b></td>
					<td bgcolor="#CCCCCC">{LANGUAGE_SELECT}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_BOARD_THEME}:</b></td>
					<td bgcolor="#CCCCCC">{THEME_SELECT}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_BOARD_TEMPLATE}:</b></td>
					<td bgcolor="#CCCCCC">{TEMPLATE_SELECT}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_TIMEZONE}:</b></td>
					<td bgcolor="#CCCCCC">{TIMEZONE_SELECT}</td>
				</tr>
				<tr class="tablebody">
					<td bgcolor="#DDDDDD"><b>{L_DATE_FORMAT}:</b><br /><font style="{font-size: 8pt;}">{L_DATE_FORMAT_EXPLANATION}</font></td>
					<td bgcolor="#CCCCCC"><input type="text" name="dateformat" value="{DATE_FORMAT}" maxlength="16"></td>
				</tr>
			<tr class="tableheader">
		   	<td align="center" colspan="2">
		   		<input type="hidden" name="user_id" value="{USER_ID}">
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
