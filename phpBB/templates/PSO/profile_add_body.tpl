<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left"><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
	</tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr><form action="{S_PROFILE_ACTION}" enctype="multipart/form-data" method="post">
		<td class="tablebg"><table border="0" cellpadding="3" cellspacing="1" width="100%">
			<tr>
				<th class="secondary" colspan="2">&nbsp;<b>{L_REGISTRATION_INFO}</b> <span class="gensmall">[{L_ITEMS_REQUIRED}]</span>&nbsp;</th>
			</tr>
			<tr>
				<td class="row1" width="38%"><span class="gen">{L_USERNAME}: *</b><br>{L_USER_UNIQ}</td>
				<td class="row2"><input type="text" name="username" size="35" maxlength="40" value="{USERNAME}"></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_EMAIL_ADDRESS}: *</b></td>
				<td class="row2"><input type="text" name="email" size="35" maxlength="255" value="{EMAIL}"></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_PASSWORD}: *</b></span><br /><span class="gensmall">{L_PASSWORD_IF_CHANGED}</span></td>
				<td class="row2"><input type="password" name="password" size="35" maxlength="100" value="{PASSWORD}"></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_CONFIRM} {L_PASSWORD}: * </b></span><br /><span class="gensmall">{L_PASSWORD_CONFIRM_IF_CHANGED}</span></td>
				<td class="row2"><input type="password" name="password_confirm" size="35" maxlength="100" value="{PASSWORD_CONFIRM}"></td>
			</tr>
			<tr>
				<th class="secondary" colspan="2">&nbsp;<b>{L_PROFILE_INFO}</b> <span class="gensmall">[{L_PROFILE_INFO_NOTICE}]</span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_ICQ_NUMBER}:</span></td>
				<td class="row2"><input type="text" name="icq" size="10" maxlength="15" value="{ICQ}"></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_AIM}:</span></td>
				<td class="row2"><input type="text" name="aim" size="20" maxlength="255" value="{AIM}"></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_MESSENGER}:</span></td>
				<td class="row2"><input type="text" name="msn" size="20" maxlength="255" value="{MSN}"></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_YAHOO}:</span></td>
				<td class="row2"><input type="text" name="yim" size="20" maxlength="255" value="{YIM}"></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_WEBSITE}:</span></td>
				<td class="row2"><input type="text" name="website" size="35" maxlength="255" value="{WEBSITE}"></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_LOCATION}:</span></td>
				<td class="row2"><input type="text" name="location" size="35" maxlength="100" value="{LOCATION}"></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_OCCUPATION}:</span></td>
				<td class="row2"><input type="text" name="occupation" size="35" maxlength="100" value="{OCCUPATION}"></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_INTERESTS}:</span></td>
				<td class="row2"><input type="text" name="interests" size="35" maxlength="150" value="{INTERESTS}"></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_SIGNATURE}:</b></span><br /><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{L_SIGNATURE_EXPLAIN}</span></td>
				<td class="row2"><textarea name="signature" rows="6" cols="45">{SIGNATURE}</textarea></td>
			</tr>
			<tr>
				<th class="secondary" colspan="2">&nbsp;<b>{L_PREFERENCES}</b></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_PUBLIC_VIEW_EMAIL}:</span></td>
				<td class="row2"><input type="radio" name="viewemail" value="1" {VIEW_EMAIL_YES}> <span class="gen">{L_YES}</span>&nbsp;&nbsp;<input type="radio" name="viewemail" value="0" {VIEW_EMAIL_NO}> <span class="gen">{L_NO}</span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_HIDE_USER}:</span></td>
				<td class="row2"><input type="radio" name="hideonline" value="1" {HIDE_USER_YES}> <span class="gen">{L_YES}</span>&nbsp;&nbsp;<input type="radio" name="hideonline" value="0" {HIDE_USER_NO}> <span class="gen">{L_NO}</span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_NOTIFY_ON_PRIVMSG}:</span></td>
				<td class="row2"><input type="radio" name="notifypm" value="1" {NOTIFY_PM_YES}> <span class="gen">{L_YES}</span>&nbsp;&nbsp;<input type="radio" name="notifypm" value="0" {NOTIFY_PM_NO}> <span class="gen">{L_NO}</span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_ALWAYS_ADD_SIGNATURE}:</span></td>
				<td class="row2"><input type="radio" name="attachsig" value="1" {ALWAYS_ADD_SIGNATURE_YES}> <span class="gen">{L_YES}</span>&nbsp;&nbsp;<input type="radio" name="attachsig" value="0" {ALWAYS_ADD_SIGNATURE_NO}> <span class="gen">{L_NO}</span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_ALWAYS_ALLOW_BBCODE}:</span></td>
				<td class="row2"><input type="radio" name="allowbbcode" value="1" {ALWAYS_ALLOW_BBCODE_YES}> <span class="gen">{L_YES}</span>&nbsp;&nbsp;<input type="radio" name="allowbbcode" value="0" {ALWAYS_ALLOW_BBCODE_NO}> <span class="gen">{L_NO}</span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_ALWAYS_ALLOW_HTML}:</span></td>
				<td class="row2"><input type="radio" name="allowhtml" value="1" {ALWAYS_ALLOW_HTML_YES}> <span class="gen">{L_YES}</span>&nbsp;&nbsp;<input type="radio" name="allowhtml" value="0" {ALWAYS_ALLOW_HTML_NO}> <span class="gen">{L_NO}</span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_ALWAYS_ALLOW_SMILIES}:</span></td>
				<td class="row2"><input type="radio" name="allowsmilies" value="1" {ALWAYS_ALLOW_SMILIES_YES}> <span class="gen">{L_YES}</span>&nbsp;&nbsp;<input type="radio" name="allowsmilies" value="0" {ALWAYS_ALLOW_SMILIES_NO}> <span class="gen">{L_NO}</span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_BOARD_LANGUAGE}:</span></td>
				<td class="row2">{LANGUAGE_SELECT}</td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_BOARD_THEME}:</span></td>
				<td class="row2">{THEME_SELECT}</td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_BOARD_TEMPLATE}:</span></td>
				<td class="row2">{TEMPLATE_SELECT}</td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_TIMEZONE}:</span></td>
				<td class="row2">{TIMEZONE_SELECT}</td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_DATE_FORMAT}:</span><br /><span class="gensmall">{L_DATE_FORMAT_EXPLAIN}</span></td>
				<td class="row2"><input type="text" name="dateformat" value="{DATE_FORMAT}" maxlength="16"></td>
			</tr>
			<!-- BEGIN avatarblock -->
			<tr>
				<th class="secondary" colspan="2">&nbsp;<b>{L_AVATAR_PANEL}</b></td>
			</tr>
			<tr>
				<td class="row1" colspan="2" align="center"><table width="70%" cellspacing="2" cellpadding="0" border="0">
					<tr>
						<td width="65%"><span class="gen"><span class="gensmall">{L_AVATAR_EXPLAIN}</span></td>
						<td align="center"><span class="gensmall">{L_CURRENT_IMAGE}</span><br>{AVATAR}<br><input type="checkbox" name="avatardel">&nbsp;<span class="gensmall">{L_DELETE_AVATAR}</span></td>
					</tr>
				</table></td>
			</tr>
			<!-- BEGIN avatarupload -->
			<tr>
				<td class="row1"><span class="gen">{L_UPLOAD_AVATAR_FILE}:</span></td>
				<td class="row2"><input type="hidden" name="MAX_FILE_SIZE" value="{AVATAR_SIZE}"><input type="file" name="avatar"></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_UPLOAD_AVATAR_URL}:</span><br><span class="gensmall">{L_UPLOAD_AVATAR_URL_EXPLAIN}</span></td>
				<td class="row2"><input type="text" name="avatarurl" size="40"></td>
			</tr>
			<!-- END avatarupload -->
			<!-- BEGIN avatarremote -->
			<tr>
				<td class="row1"><span class="gen">{L_LINK_REMOTE_AVATAR}:</span><br><span class="gensmall">{L_LINK_REMOTE_AVATAR_EXPLAIN}</span></td>
				<td class="row2"><input type="text" name="avatarremoteurl" size="40"></td>
			</tr>
			<!-- END avatarremote -->
			<!-- BEGIN avatargallery -->
			<tr>
				<td class="row1"><span class="gen">{L_AVATAR_GALLERY}:</span></td>
				<td class="row2"><input type="submit" name="avatargallery" value="{L_SHOW_GALLERY}"></td>
			</tr>
			<!-- END avatargallery -->
			<!-- END avatarblock -->
			<tr>
			   	<td class="cat" colspan="2" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SUBMIT}">&nbsp;&nbsp;<input type="reset" value="{L_RESET}"></td>
			</tr>
		</table></td>
	</form></tr>
</table>

<!-- IF $S_ALLOW_AVATAR_UPLOAD eq TRUE || $S_ALLOW_AVATAR_LOCAL eq TRUE || $S_ALLOW_AVATAR_REMOTE eq TRUE -->
<!-- IF $S_ALLOW_AVATAR_LOCAL eq TRUE -->
<!-- IF $S_ALLOW_AVATAR_REMOTE eq TRUE -->
<!-- IF $S_ALLOW_AVATAR_UPLOAD eq TRUE -->
