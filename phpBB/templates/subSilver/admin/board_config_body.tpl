
<h1>{L_CONFIGURATION_TITLE}</h1>

<p>{L_CONFIGURATION_EXPLAIN}</p>

<form action="{S_CONFIG_ACTION}" method="POST"><table width="99%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
	<tr>
	  <th class="thHead" colspan="2">{L_GENERAL_SETTINGS}</th>
	</tr>
	<tr>
		<td class="row1">{L_SITE_NAME}:</td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="sitename" value="{SITENAME}"></td>
	</tr>
	<tr>
		<td class="row1">{L_SITE_DESCRIPTION}:</td>
		<td class="row2"><input type="text" size="40" maxlength="255" name="site_desc" value="{SITE_DESCRIPTION}"></td>
	</tr>
	<tr>
		<td class="row1">{L_ACCT_ACTIVATION}:</td>
		<td class="row2"><input type="radio" name="require_activation" value="{ACTIVATION_NONE}" {ACTIVATION_NONE_CHECKED}>{L_NO}ne&nbsp; &nbsp;<input type="radio" name="require_activation" value="{ACTIVATION_USER}" {ACTIVATION_USER_CHECKED}>User&nbsp; &nbsp;<input type="radio" name="require_activation" value="{ACTIVATION_ADMIN}" {ACTIVATION_ADMIN_CHECKED}>Admin</td>
	</tr>
	<tr>
		<td class="row1">Flood Interval: <br /><span class="gensmall">Number of seconds a user must wait between posts</span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="flood_interval" value="{FLOOD_INTERVAL}"></td>
	</tr>
	<tr>
		<td class="row1">Topics Per Page</td>
		<td class="row2"><input type="text" name="topics_per_page" size="3" maxlength="4" value="{TOPICS_PER_PAGE}"></td>
	</tr>
	<tr>
		<td class="row1">Posts Per Page</td>
		<td class="row2"><input type="text" name="posts_per_page" size="3" maxlength="4" value="{POSTS_PER_PAGE}"></td>
	</tr>
	<tr>
		<td class="row1">Hot Threshold</td>
		<td class="row2"><input type="text" name="hot_threshold" size="3" maxlength="4" value="{HOT_TOPIC}"></td>
	</tr>
	<tr>
		<td class="row1">Default Style:</td>
		<td class="row2">{STYLE_SELECT}</td>
	</tr>
	<tr>
		<td class="row1">Override user style:<br /><span class="gensmall">Replaces users style with the default</span></td>
		<td class="row2"><input type="radio" name="override_user_style" value="1" {OVERRIDE_STYLE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="override_user_style" value="0" {OVERRIDE_STYLE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">Default Language:</td>
		<td class="row2">{LANG_SELECT}</td>
	</tr>
	<tr>
		<td class="row1">Date Format:<br /><span class="gensmall">{L_DATE_FORMAT_EXPLAIN}</span></td>
		<td class="row2"><input type="text" maxlength="16" name="default_dateformat" value="{DEFAULT_DATEFORMAT}"></td>
	</tr>
	<tr>
		<td class="row1">System Timezone:</td>
		<td class="row2">{TIMEZONE_SELECT}</td>
	</tr>
	<tr>
		<td class="row1">Enable GZip Compression:</td>
		<td class="row2"><input type="radio" name="gzip_compress" value="1" {GZIP_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="gzip_compress" value="0" {GZIP_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">Enable Forum Pruning:</td>
		<td class="row2"><input type="radio" name="prune_enable" value="1" {PRUNE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="prune_enable" value="0" {PRUNE_NO}> {L_NO}</td>
	</tr>
	<tr>
	  <th class="thHead" colspan="2">User/Forum Ability Settings</th>
	</tr>
	<tr>
		<td class="row1">Allow HTML</td>
		<td class="row2"><input type="radio" name="allow_html" value="1" {HTML_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_html" value="0" {HTML_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">Allowed HTML tags<br /><span class="gensmall">Seperate tags with commas</span></td>
		<td class="row2"><input type="text" size="30" maxlength="255" name="allow_html_tags" value="{HTML_TAGS}"></td>
	</tr>
	<tr>
		<td class="row1">Allow BBCode</td>
		<td class="row2"><input type="radio" name="allow_bbcode" value="1" {BBCODE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_bbcode" value="0" {BBCODE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">Allow Smilies</td>
		<td class="row2"><input type="radio" name="allow_smilies" value="1" {SMILE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_smilies" value="0" {SMILE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">Smilies Storage Path <br /><span class="gensmall">Path under your phpBB root dir, e.g. images/smilies</span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="smilies_path" value="{SMILIES_PATH}"></td>
	</tr>
	<tr>
		<td class="row1">Allow Signatures</td>
		<td class="row2"><input type="radio" name="allow_sig" value="1" {SIG_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_sig" value="0" {SIG_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">Maximum signature length<br /><span class="gensmall">Number of characters allowed</span></td>
		<td class="row2"><input type="text" size="5" maxlength="4" name="max_sig_chars" value="{SIG_SIZE}"></td>
	</tr>
	<tr>
		<td class="row1">Allow Name Change</td>
		<td class="row2"><input type="radio" name="allow_namechange" value="1" {NAMECHANGE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_namechange" value="0" {NAMECHANGE_NO}> {L_NO}</td>
	</tr>
	<tr>
	  <th class="thHead" colspan="2">Avatar Settings</th>
	</tr>
	<tr>
		<td class="row1">Allow local gallery avatars</td>
		<td class="row2"><input type="radio" name="allow_avatar_local" value="1" {AVATARS_LOCAL_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_avatar_local" value="0" {AVATARS_LOCAL_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">Allow remote avatars <br /><span class="gensmall">Avatars linked from another website</span></td>
		<td class="row2"><input type="radio" name="allow_avatar_remote" value="1" {AVATARS_REMOTE_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_avatar_remote" value="0" {AVATARS_REMOTE_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">Allow avatar uploading</td>
		<td class="row2"><input type="radio" name="allow_avatar_upload" value="1" {AVATARS_UPLOAD_YES}> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_avatar_upload" value="0" {AVATARS_UPLOAD_NO}> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">Max. Avatar File Size<br /><span class="gensmall">For uploaded avatar files</span></td>
		<td class="row2"><input type="text" size="4" maxlength="10" name="avatar_filesize" value="{AVATAR_FILESIZE}"> Bytes</td>
	</tr>
	<tr>
		<td class="row1">Max. Avatar Size <br />
			<span class="gensmall">(height x width)</span>
		</td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="avatar_max_height" value="{AVATAR_MAX_HEIGHT}"> x <input type="text" size="3" maxlength="4" name="avatar_max_width" value="{AVATAR_MAX_WIDTH}"></td>
	</tr>
	<tr>
		<td class="row1">Avatar Storage Path <br /><span class="gensmall">Path under your phpBB root dir, e.g. images/avatars</span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="avatar_path" value="{AVATAR_PATH}" /></td>
	</tr>
	<tr>
		<td class="row1">Avatar Gallery Path <br /><span class="gensmall">Path under your phpBB root dir for pre-loaded images, e.g. images/avatars/gallery</span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="avatar_gallery_path" value="{AVATAR_GALLERY_PATH}" /></td>
	</tr>
	<tr>
	  <th class="thHead" colspan="2">Email Settings</th>
	</tr>
	<tr>
		<td class="row1">Admin Email Address</td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="board_email" value="{EMAIL_FROM}" /></td>
	</tr>
	<tr>
		<td class="row1">Email Signature<br /><span class="gensmall">This text will be attached to all emails the board sends</span></td>
		<td class="row2"><textarea name="board_email_sig" rows="5" cols="30">{EMAIL_SIG}</textarea></td>
	</tr>
	<tr>
		<td class="row1">Use SMTP for delivery<br /><span class="gensmall">Say {L_YES} if you want or have to send email via a server instead of the local mail function</span></td>
		<td class="row2"><input type="radio" name="smtp_delivery" value="1" {SMTP_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="smtp_delivery" value="0" {SMTP_NO} /> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1">SMTP Server</td>
		<td class="row2"><input type="text" name="smtp_host" value="{SMTP_HOST}" size="25" maxlength="50" /></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="Save Settings" class="mainoption" />
		</td>
	</tr>
</table></form>

<br clear="all">
