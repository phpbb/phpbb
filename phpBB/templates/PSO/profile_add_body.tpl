<div align="center"><table width="98%" cellpadding="1" cellspacing="0" border="0">
	<tr><form action="{PHP_SELF}" method="POST">
		<td bgcolor="{T_TH_COLOR1}"><table border="0" cellpadding="3" cellspacing="1" width="100%">
			<tr>
				<td colspan="2" bgcolor="{T_TH_COLOR3}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">&nbsp;<b>{L_REGISTRATION_INFO}</b></font> <font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">[{L_ITEMS_REQUIRED}]</font>&nbsp;</td>
			</tr>
	      	<tr>
	           <td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_USERNAME}: *</b><br>{L_USER_UNIQ}</td>
	           <td bgcolor="{T_TD_COLOR2}"><input type="text" name="username" size="35" maxlength="40" value="{USERNAME}"></td>
            </tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_EMAIL_ADDRESS}: *</b></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="text" name="email" size="35" maxlength="255" value="{EMAIL}"></td>
			</tr>
            <tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_PASSWORD}: *</b></font><br /><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{L_PASSWORD_IF_CHANGED}</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="password" name="password" size="35" maxlength="100" value="{PASSWORD}"></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_CONFIRM} {L_PASSWORD}: * </b></font><br /><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{L_PASSWORD_CONFIRM_IF_CHANGED}</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="password" name="password_confirm" size="35" maxlength="100" value="{PASSWORD_CONFIRM}"></td>
			</tr>
			<tr>
				<td colspan="2" bgcolor="{T_TH_COLOR3}">&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{L_PROFILE_INFO}</b></font> <font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">[{L_PROFILE_INFO_NOTICE}]</font></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_ICQ_NUMBER}:</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="text" name="icq" size="10" maxlength="15" value="{ICQ}"></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_AIM}:</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="text" name="aim" size="20" maxlength="255" value="{AIM}"></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_MESSENGER}:</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="text" name="msn" size="20" maxlength="255" value="{MSN}"></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_YAHOO}:</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="text" name="yim" size="20" maxlength="255" value="{YIM}"></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_WEBSITE}:</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="text" name="website" size="35" maxlength="255" value="{WEBSITE}"></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_LOCATION}:</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="text" name="location" size="35" maxlength="100" value="{LOCATION}"></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_OCCUPATION}:</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="text" name="occupation" size="35" maxlength="100" value="{OCCUPATION}"></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_INTERESTS}:</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="text" name="interests" size="35" maxlength="150" value="{INTERESTS}"></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_SIGNATURE}:</b></font><br /><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{L_SIGNATURE_EXPLAIN}</font></td>
				<td bgcolor="{T_TD_COLOR2}"><textarea name="signature" rows="6" cols="45">{SIGNATURE}</textarea></td>
			</tr>
			<tr>
				<td colspan="2" bgcolor="{T_TH_COLOR3}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{L_PREFERENCES}</b></font></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_PUBLIC_VIEW_EMAIL}:</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="radio" name="viewemail" value="1" {VIEW_EMAIL_YES}> <font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_YES}</font>&nbsp;&nbsp;<input type="radio" name="viewemail" value="0" {VIEW_EMAIL_NO}> <font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_NO}</font></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_ALWAYS_ADD_SIGNATURE}:</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="radio" name="attachsig" value="1" {ALWAYS_ADD_SIGNATURE_YES}> <font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_YES}</font>&nbsp;&nbsp;<input type="radio" name="attachsig" value="0" {ALWAYS_ADD_SIGNATURE_NO}> <font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_NO}</font></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_ALWAYS_ALLOW_BBCODE}:</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="radio" name="allowbbcode" value="1" {ALWAYS_ALLOW_BBCODE_YES}> <font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_YES}</font>&nbsp;&nbsp;<input type="radio" name="allowbbcode" value="0" {ALWAYS_ALLOW_BBCODE_NO}> <font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_NO}</font></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_ALWAYS_ALLOW_HTML}:</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="radio" name="allowhtml" value="1" {ALWAYS_ALLOW_HTML_YES}> <font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_YES}</font>&nbsp;&nbsp;<input type="radio" name="allowhtml" value="0" {ALWAYS_ALLOW_HTML_NO}> <font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_NO}</font></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_ALWAYS_ALLOW_SMILIES}:</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="radio" name="allowsmilies" value="1" {ALWAYS_ALLOW_SMILIES_YES}> <font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_YES}</font>&nbsp;&nbsp;<input type="radio" name="allowsmilies" value="0" {ALWAYS_ALLOW_SMILIES_NO}> <font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_NO}</font></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_BOARD_LANGUAGE}:</font></td>
				<td bgcolor="{T_TD_COLOR2}">{LANGUAGE_SELECT}</td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_BOARD_THEME}:</font></td>
				<td bgcolor="{T_TD_COLOR2}">{THEME_SELECT}</td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_BOARD_TEMPLATE}:</font></td>
				<td bgcolor="{T_TD_COLOR2}">{TEMPLATE_SELECT}</td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_TIMEZONE}:</font></td>
				<td bgcolor="{T_TD_COLOR2}">{TIMEZONE_SELECT}</td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_DATE_FORMAT}:</font><br /><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{L_DATE_FORMAT_EXPLANATION}</font></td>
				<td bgcolor="{T_TD_COLOR2}"><input type="text" name="dateformat" value="{DATE_FORMAT}" maxlength="16"></td>
			</tr>
			<tr>
			   	<td colspan="2" bgcolor="{T_TH_COLOR3}" align="center"><<input type="hidden" name="user_id" value="{USER_ID}"><input type="hidden" name="mode" value="{MODE}"><input type="hidden" name="agreed" value="true"><input type="hidden" name="coppa" value="{COPPA}"><input type="submit" name="submit" value="{L_SUBMIT}"></td>
			</tr>
		</table></td>
	</form></tr>
</table></div>