<br clear="all" />

<h1>{L_CONFIGURATION_TITLE}</h1>

<p>{L_CONFIGURATION_EXPLAIN}</p>

<form action="{S_CONFIG_ACTION}" method="POST"><table width="99%" cellpadding="4" cellspacing="1" border="0" align="center">
	<tr>
		<td class="cat" colspan="2"><span class="cattitle">{L_GENERAL_SETTINGS}</span></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">{L_SITE_NAME}:</span></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="sitename" value="{SITENAME}"></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">{L_ACCT_ACTIVATION}:</span></td>
		<td class="row2"><input type="radio" name="require_activation" value="{ACTIVATION_NONE}" {ACTIVATION_NONE_CHECKED}>{L_NO}ne&nbsp; &nbsp;<input type="radio" name="require_activation" value="{ACTIVATION_USER}" {ACTIVATION_USER_CHECKED}>User&nbsp; &nbsp;<input type="radio" name="require_activation" value="{ACTIVATION_ADMIN}" {ACTIVATION_ADMIN_CHECKED}>Admin</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Flood Interval:</span><br /><span class="gensmall">Number of seconds a user must wait between posts</span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="flood_interval" value="{FLOOD_INTERVAL}"></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Topics Per Page:</span></td>
		<td class="row2"><input type="text" name="topics_per_page" size="3" maxlength="4" value="{TOPICS_PER_PAGE}"></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Posts Per Page:</span></td>
		<td class="row2"><input type="text" name="posts_per_page" size="3" maxlength="4" value="{POSTS_PER_PAGE}"></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Hot Threshold:</span></td>
		<td class="row2"><input type="text" name="hot_threshold" size="3" maxlength="4" value="{HOT_TOPIC}"></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Default Style:</span></td>
		<td class="row2">{STYLE_SELECT}</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Override user style:</span><br /><span class="gensmall">Replaces users style with the default</span></td>
		<td class="row2"><input type="radio" name="override_user_style" value="1" {OVERRIDE_STYLE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="override_user_style" value="0" {OVERRIDE_STYLE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Default Language:</span></td>
		<td class="row2">{LANG_SELECT}</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Date Format:</span><br /><span class="gensmall">{L_DATE_FORMAT_EXPLAIN}</span></td>
		<td class="row2"><input type="text" maxlength="16" name="default_dateformat" value="{DEFAULT_DATEFORMAT}"></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">System Timezone:</span></td>
		<td class="row2">{TIMEZONE_SELECT}</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Enable GZip Compression:</span></td>
		<td class="row2"><input type="radio" name="gzip_compress" value="1" {GZIP_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="gzip_compress" value="0" {GZIP_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Enable Forum Pruning:</span></td>
		<td class="row2"><input type="radio" name="prune_enable" value="1" {PRUNE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="prune_enable" value="0" {PRUNE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="cat" colspan="2"><span class="cattitle">User/Forum Ability Settings</span></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Allow HTML:</span></td>
		<td class="row2"><input type="radio" name="allow_html" value="1" {HTML_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_html" value="0" {HTML_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Allow BBCode:</span></td>
		<td class="row2"><input type="radio" name="allow_bbcode" value="1" {BBCODE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_bbcode" value="0" {BBCODE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Allow Smilies:</span></td>
		<td class="row2"><input type="radio" name="allow_smilies" value="1" {SMILE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_smilies" value="0" {SMILE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Smilies Storage Path:</span><br /><span class="gensmall">Path under your phpBB root dir, e.g. images/smilies</span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="smilies_path" value="{SMILIES_PATH}"></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Allow Signatures:</span></td>
		<td class="row2"><input type="radio" name="allow_sig" value="1" {SIG_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_sig" value="0" {SIG_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Maximum signature length:</span><br /><span class="gensmall">Number of characters allowed</span></td>
		<td class="row2"><input type="text" size="5" maxlength="4" name="max_sig_chars" value="{SIG_SIZE}"></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Allow Name Change:</span></td>
		<td class="row2"><input type="radio" name="allow_namechange" value="1" {NAMECHANGE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_namechange" value="0" {NAMECHANGE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Inbox limit:</span></td>
		<td class="row2"><input type="text" size="5" maxlength="5" name="max_inbox_privmsgs" value="{INBOX_PRIVMSGS}"> messages</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Sentbox limit:</span></td>
		<td class="row2"><input type="text" size="5" maxlength="5" name="max_sentbox_privmsgs" value="{SENTBOX_PRIVMSGS}"> messages</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Savebox limit:</span></td>
		<td class="row2"><input type="text" size="5" maxlength="5" name="max_savebox_privmsgs" value="{SAVEBOX_PRIVMSGS}"> messages</td>
	</tr>
	<tr>
		<td class="cat" colspan="2"><span class="cattitle">Avatar Settings</span></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Allow local gallery avatars:</span></td>
		<td class="row2"><input type="radio" name="allow_avatar_local" value="1" {AVATARS_LOCAL_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_avatar_local" value="0" {AVATARS_LOCAL_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Allow remote avatars:</span><br /><span class="gensmall">Avatars linked from another website</span></td>
		<td class="row2"><input type="radio" name="allow_avatar_remote" value="1" {AVATARS_REMOTE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_avatar_remote" value="0" {AVATARS_REMOTE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Allow avatar uploading:</span></td>
		<td class="row2"><input type="radio" name="allow_avatar_upload" value="1" {AVATARS_UPLOAD_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_avatar_upload" value="0" {AVATARS_UPLOAD_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Max. Avatar File Size:</span><br /><span class="gensmall">For uploaded avatar files</span></td>
		<td class="row2"><input type="text" size="4" maxlength="10" name="avatar_filesize" value="{AVATAR_FILESIZE}"> Bytes</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Max. Avatar Size:</span><br /><span class="gensmall">(height x width)</span>
		</td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="avatar_max_height" value="{AVATAR_MAX_HEIGHT}"> x <input type="text" size="3" maxlength="4" name="avatar_max_width" value="{AVATAR_MAX_WIDTH}"></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Avatar Storage Path:</span><br /><span class="gensmall">Path under your phpBB root dir, e.g. images/avatars</span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="avatar_path" value="{AVATAR_PATH}"></td>
	</tr>
	<tr>
		<td class="cat" colspan="2"><span class="cattitle">Email Settings</span></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Admin Email Address:</span></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="board_email" value="{EMAIL_FROM}"></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Email Signature:</span><br /><span class="gensmall">This text will be attached to all emails the board sends</span></td>
		<td class="row2"><textarea name="board_email_sig" rows="5" cols="30">{EMAIL_SIG}</textarea></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Use SMTP for delivery:</span><br /><span class="gensmall">Say {L_YES} if you want or have to send email via a server instead of the local mail function</span></td>
		<td class="row2"><input type="radio" name="smtp_delivery" value="1" {SMTP_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="smtp_delivery" value="0" {SMTP_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">SMTP Server:</span></td>
		<td class="row2"><input type="text" name="smtp_host" value="{SMTP_HOST}" size="25" maxlength="50"></td>
	</tr>
	<tr>
		<td class="cat" colspan="2"><span class="cattitle">COPPA Settings</span></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">FAX Number:</span></td>
		<td class="row2"><input type="text" name="coppa_fax" value="{COPPA_FAX}" size="25" maxlength="50"></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">Mailing Address:</span><br /><span class="gensmall">Signed COPPA agreements will be mailed here</span></td>
		<td class="row2"><textarea name="coppa_mail" rows="5" cols="30">{COPPA_MAIL}</textarea></td>
	</tr>	
	<tr>
		<td class="cat" colspan="2" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="Save Settings">
		</td>
	</tr>
</table></form>

<br clear="all">
