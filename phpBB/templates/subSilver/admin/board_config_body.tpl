
<h1>{L_CONFIGURATION_TITLE}</h1>

<p>{L_CONFIGURATION_EXPLAIN}</p>

<form action="{S_CONFIG_ACTION}" method="POST"><table width="99%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
	<tr>
	  <th class="thHead" colspan="2">{L_GENERAL_SETTINGS}</th>
	</tr>
	<tr>
		<td class="row1">{L_SITE_NAME}</td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="sitename" value="{SITENAME}"></td>
	</tr>
	<tr>
		<td class="row1">{L_SITE_DESCRIPTION}</td>
		<td class="row2"><input type="text" size="40" maxlength="255" name="site_desc" value="{SITE_DESCRIPTION}"></td>
	</tr>
	<tr>
		<td class="row1">{L_ACCT_ACTIVATION}</td>
		<td class="row2"><input type="radio" name="require_activation" value="{ACTIVATION_NONE}" {ACTIVATION_NONE_CHECKED}>{L_NO}ne&nbsp; &nbsp;<input type="radio" name="require_activation" value="{ACTIVATION_USER}" {ACTIVATION_USER_CHECKED}>User&nbsp; &nbsp;<input type="radio" name="require_activation" value="{ACTIVATION_ADMIN}" {ACTIVATION_ADMIN_CHECKED}>Admin</td>
	</tr>
	<tr>
		<td class="row1">{L_FLOOD_INTERVAL} <br /><span class="gensmall">{L_FLOOD_INTERVAL_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="flood_interval" value="{FLOOD_INTERVAL}"></td>
	</tr>
	<tr>
		<td class="row1">{L_TOPICS_PER_PAGE}</td>
		<td class="row2"><input type="text" name="topics_per_page" size="3" maxlength="4" value="{TOPICS_PER_PAGE}"></td>
	</tr>
	<tr>
		<td class="row1">{L_POSTS_PER_PAGE}</td>
		<td class="row2"><input type="text" name="posts_per_page" size="3" maxlength="4" value="{POSTS_PER_PAGE}"></td>
	</tr>
	<tr>
		<td class="row1">{L_HOT_THRESHOLD}</td>
		<td class="row2"><input type="text" name="hot_threshold" size="3" maxlength="4" value="{HOT_TOPIC}"></td>
	</tr>
	<tr>
		<td class="row1">{L_DEFAULT_STYLE}</td>
		<td class="row2">{STYLE_SELECT}</td>
	</tr>
	<tr>
		<td class="row1">{L_OVERRIDE_STYLE}<br /><span class="gensmall">{L_OVERRIDE_STYLE_EXPLAIN}</span></td>
		<td class="row2"><input type="radio" name="override_user_style" value="1" {OVERRIDE_STYLE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="override_user_style" value="0" {OVERRIDE_STYLE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">{L_DEFAULT_LANGUAGE}</td>
		<td class="row2">{LANG_SELECT}</td>
	</tr>
	<tr>
		<td class="row1">{L_DATE_FORMAT}<br /><span class="gensmall">{L_DATE_FORMAT_EXPLAIN}</span></td>
		<td class="row2"><input type="text" maxlength="16" name="default_dateformat" value="{DEFAULT_DATEFORMAT}"></td>
	</tr>
	<tr>
		<td class="row1">{L_SYSTEM_TIMEZONE}</td>
		<td class="row2">{TIMEZONE_SELECT}</td>
	</tr>
	<tr>
		<td class="row1">{L_ENABLE_GZIP}</td>
		<td class="row2"><input type="radio" name="gzip_compress" value="1" {GZIP_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="gzip_compress" value="0" {GZIP_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">{L_ENABLE_PRUNE}</td>
		<td class="row2"><input type="radio" name="prune_enable" value="1" {PRUNE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="prune_enable" value="0" {PRUNE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<th class="thHead" colspan="2">{L_PRIVATE_MESSAGING}</th>
	</tr>
	<tr>
		<td class="row1">{L_DISABLE_PRIVATE_MESSAGING}:</td>
		<td class="row2"><input type="radio" name="privmsg_disable" value="0" {S_PRIVMSG_ENABLED}>{L_ENABLED}&nbsp; &nbsp;<input type="radio" name="privmsg_disable" value="1" {S_PRIVMSG_DISABLED}>{L_DISABLED}</td>
	</tr>
	<tr>
		<td class="row1">{L_INBOX_LIMIT}</span></td>
		<td class="row2"><input type="text" maxlength="4" size="4" name="max_inbox_privmsgs" value="{INBOX_LIMIT}"></td>
	</tr>
	<tr>
		<td class="row1">{L_SENTBOX_LIMIT}</span></td>
		<td class="row2"><input type="text" maxlength="4" size="4" name="max_sentbox_privmsgs" value="{SENTBOX_LIMIT}"></td>
	</tr>
	<tr>
		<td class="row1">{L_SAVEBOX_LIMIT}</span></td>
		<td class="row2"><input type="text" maxlength="4" size="4" name="max_savebox_privmsgs" value="{SAVEBOX_LIMIT}"></td>
	</tr>
	<tr>
	  <th class="thHead" colspan="2">{L_ABILITIES_SETTINGS}</th>
	</tr>
	<tr>
		<td class="row1">{L_ALLOW_HTML}</td>
		<td class="row2"><input type="radio" name="allow_html" value="1" {HTML_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_html" value="0" {HTML_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">{L_ALLOWED_TAGS}<br /><span class="gensmall">{L_ALLOWED_TAGS_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="30" maxlength="255" name="allow_html_tags" value="{HTML_TAGS}"></td>
	</tr>
	<tr>
		<td class="row1">{L_ALLOW_BBCODE}</td>
		<td class="row2"><input type="radio" name="allow_bbcode" value="1" {BBCODE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_bbcode" value="0" {BBCODE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">{L_ALLOW_SMILIES}</td>
		<td class="row2"><input type="radio" name="allow_smilies" value="1" {SMILE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_smilies" value="0" {SMILE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">{L_SMILIES_PATH} <br /><span class="gensmall">{L_SMILIES_PATH_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="smilies_path" value="{SMILIES_PATH}"></td>
	</tr>
	<tr>
		<td class="row1">{L_ALLOW_SIG}</td>
		<td class="row2"><input type="radio" name="allow_sig" value="1" {SIG_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_sig" value="0" {SIG_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">{L_MAX_SIG_LENGTH}<br /><span class="gensmall">{L_MAX_SIG_LENGTH_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="5" maxlength="4" name="max_sig_chars" value="{SIG_SIZE}"></td>
	</tr>
	<tr>
		<td class="row1">{L_ALLOW_NAME_CHANGE}</td>
		<td class="row2"><input type="radio" name="allow_namechange" value="1" {NAMECHANGE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_namechange" value="0" {NAMECHANGE_NO}> {L_NO}</td>
	</tr>
	<tr>
	  <th class="thHead" colspan="2">{L_AVATAR_SETTINGS}</th>
	</tr>
	<tr>
		<td class="row1">{L_ALLOW_LOCAL}</td>
		<td class="row2"><input type="radio" name="allow_avatar_local" value="1" {AVATARS_LOCAL_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_avatar_local" value="0" {AVATARS_LOCAL_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">{L_ALLOW_REMOTE} <br /><span class="gensmall">{L_ALLOW_REMOTE_EXPLAIN}</span></td>
		<td class="row2"><input type="radio" name="allow_avatar_remote" value="1" {AVATARS_REMOTE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_avatar_remote" value="0" {AVATARS_REMOTE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">{L_ALLOW_UPLOAD}</td>
		<td class="row2"><input type="radio" name="allow_avatar_upload" value="1" {AVATARS_UPLOAD_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_avatar_upload" value="0" {AVATARS_UPLOAD_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">{L_MAX_FILESIZE}<br /><span class="gensmall">{L_MAX_FILESIZE_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="4" maxlength="10" name="avatar_filesize" value="{AVATAR_FILESIZE}"> Bytes</td>
	</tr>
	<tr>
		<td class="row1">{L_MAX_AVATAR_SIZE} <br />
			<span class="gensmall">{L_MAX_AVATAR_SIZE_EXPLAIN}</span>
		</td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="avatar_max_height" value="{AVATAR_MAX_HEIGHT}"> x <input type="text" size="3" maxlength="4" name="avatar_max_width" value="{AVATAR_MAX_WIDTH}"></td>
	</tr>
	<tr>
		<td class="row1">{L_AVATAR_STORAGE_PATH} <br /><span class="gensmall">{L_AVATAR_STORAGE_PATH_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="avatar_path" value="{AVATAR_PATH}" /></td>
	</tr>
	<tr>
		<td class="row1">{L_AVATAR_GALLERY_PATH} <br /><span class="gensmall">{L_AVATAR_GALLERY_PATH_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="avatar_gallery_path" value="{AVATAR_GALLERY_PATH}" /></td>
	</tr>
	<tr>
	  <th class="thHead" colspan="2">{L_COPPA_SETTINGS}</th>
	</tr>
	<tr>
		<td class="row1">{L_COPPA_FAX}</td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="coppa_fax" value="{COPPA_FAX}" /></td>
	</tr>
	<tr>
		<td class="row1">{L_COPPA_MAIL}<br /><span class="gensmall">{L_COPPA_MAIL_EXPLAIN}</span></td>
		<td class="row2"><textarea name="coppa_mail" rows="5" cols="30">{COPPA_MAIL}</textarea></td>
	</tr>

	<tr>
	  <th class="thHead" colspan="2">{L_EMAIL_SETTINGS}</th>
	</tr>
	<tr>
		<td class="row1">{L_ADMIN_EMAIL}</td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="board_email" value="{EMAIL_FROM}" /></td>
	</tr>
	<tr>
		<td class="row1">{L_EMAIL_SIG}<br /><span class="gensmall">{L_EMAIL_SIG_EXPLAIN}</span></td>
		<td class="row2"><textarea name="board_email_sig" rows="5" cols="30">{EMAIL_SIG}</textarea></td>
	</tr>
	<tr>
		<td class="row1">{L_USE_SMTP}<br /><span class="gensmall">{L_USE_SMTP_EXPLAIN}</span></td>
		<td class="row2"><input type="radio" name="smtp_delivery" value="1" {SMTP_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="smtp_delivery" value="0" {SMTP_NO} /> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">{L_SMPT_SERVER}</td>
		<td class="row2"><input type="text" name="smtp_host" value="{SMTP_HOST}" size="25" maxlength="50" /></td>
	</tr>

	<tr>
		<td class="catBottom" colspan="2" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />
		</td>
	</tr>
</table></form>

<br clear="all">
