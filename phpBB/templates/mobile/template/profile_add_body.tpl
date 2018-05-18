<!-- INCLUDE overall_header.tpl -->

<form action="{S_PROFILE_ACTION}" {S_FORM_ENCTYPE} method="post">

{ERROR_BOX}
<!-- BEGIN switch_cpl_menu -->
{CPL_MENU_OUTPUT}
<!-- END switch_cpl_menu -->
{IMG_THL}{IMG_THC}<span class="forumlink">{L_CPL_NAV}</span>{IMG_THR}<table class="forumlinenb">
<!-- BEGIN switch_cpl_reg_info -->
<tr><td class="row-header" colspan="2"><span>{L_REGISTRATION_INFO}</span></td></tr>
<tr><td class="row1" colspan="2"><span class="gensmall">{L_ITEMS_REQUIRED}</span></td></tr>
<!-- BEGIN switch_namechange_disallowed -->
<tr>
	<td class="row1" width="38%"><span class="gen">{L_USERNAME}: *</span></td>
	<td class="row2"><input type="hidden" name="username" value="{USERNAME}" /><span class="gen"><b>{USERNAME}</b></span></td>
</tr>
<!-- END switch_namechange_disallowed -->
<!-- BEGIN switch_namechange_allowed -->
<tr>
	<td class="row1" width="38%"><span class="gen">{L_USERNAME}: *</span></td>
	<td class="row2"><input type="text" class="post" style="width: 200px;" name="username" size="25" maxlength="40" value="{USERNAME}" {VERIFY_UN_JS} /><div id="pseudobox"></div></td>
</tr>
<!-- END switch_namechange_allowed -->
<tr>
	<td class="row1"><span class="gen">{L_EMAIL_ADDRESS}: *</span></td>
	<td class="row2"><input type="text" class="post" style="width: 200px;" name="email" size="25" maxlength="255" value="{EMAIL}" {VERIFY_EMAIL_JS} /><div id="emailbox"></div></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_CONFIRM_EMAIL}: *</span></td>
	<td class="row2"><input type="text" class="post" style="width: 200px;" name="email_confirm" size="25" maxlength="255" value="{EMAIL_CONFIRM}" /></td>
</tr>
<!-- BEGIN switch_edit_profile -->
<tr>
	<td class="row1"><span class="gen">{L_CURRENT_PASSWORD}: *</span><br />
		<span class="gensmall">{L_CONFIRM_PASSWORD_EXPLAIN}</span></td>
	<td class="row2"><input type="password" class="post" style="width: 200px" name="cur_password" size="25" maxlength="100" value="{CUR_PASSWORD}" /></td>
</tr>
<!-- END switch_edit_profile -->
<tr>
	<td class="row1">
		<span class="gen">{L_NEW_PASSWORD}: *</span><br />
		<span class="gensmall">{L_PASSWORD_IF_CHANGED}&nbsp;</span>
	</td>
	<td class="row2"><input type="password" class="post" style="width: 200px" name="new_password" size="25" maxlength="100" value="{NEW_PASSWORD}" /></td>
</tr>
<tr>
	<td class="row1">
		<span class="gen">{L_CONFIRM_PASSWORD}: * </span><br />
		<span class="gensmall">{L_PASSWORD_CONFIRM_IF_CHANGED}&nbsp;</span>
	</td>
	<td class="row2"><input type="password" class="post" style="width: 200px" name="password_confirm" size="25" maxlength="100" value="{PASSWORD_CONFIRM}" /></td>
</tr>
<!-- BEGIN switch_confirm -->
<tr>
	<td class="row1" colspan="2" align="center"><span class="gensmall">{L_CONFIRM_CODE_IMPAIRED}</span><br /><br />{CONFIRM_IMG}<br /><br /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_CONFIRM_CODE}: * </span><br /><span class="gensmall">{L_CONFIRM_CODE_EXPLAIN}</span></td>
	<td class="row2"><input type="text" class="post" style="width: 200px" name="confirm_code" size="6" maxlength="6" value="" /></td>
</tr>
<!-- END switch_confirm -->

<!-- END switch_cpl_reg_info -->
<!-- BEGIN switch_cpl_profile_info -->
<tr><td class="row-header" colspan="2"><span>{L_PROFILE_INFO}</span></td></tr>
<tr><td class="row1" colspan="2"><span class="gensmall">{L_PROFILE_INFO_NOTICE}<br />{L_ITEMS_REQUIRED}</span></td></tr>
<tr>
	<td class="row1"><span class="gen">{L_USER_FIRST_NAME}:</span></td>
	<td class="row2"><input class="post" type="text" name="user_first_name" size="35" maxlength="180" value="{USER_FIRST_NAME}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_USER_LAST_NAME}:</span></td>
	<td class="row2"><input class="post" type="text" name="user_last_name" size="35" maxlength="180" value="{USER_LAST_NAME}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_PHONE}:</span></td>
	<td class="row2"><input type="text" name="phone" class="post" style="width: 100px;" size="10" maxlength="20" value="{PHONE}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_AIM}:</span></td>
	<td class="row2"><input type="text" class="post" style="width: 150px;" name="aim" size="20" maxlength="255" value="{AIM}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_FACEBOOK}:</span></td>
	<td class="row2"><input type="text" class="post" style="width: 150px;" name="facebook" size="20" maxlength="255" value="{FACEBOOK}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_ICQ_NUMBER}:</span></td>
	<td class="row2"><input type="text" name="icq" class="post" style="width: 100px;" size="10" maxlength="15" value="{ICQ}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_JABBER}:</span></td>
	<td class="row2"><input type="text" class="post" style="width: 150px;" name="jabber" size="20" maxlength="255" value="{JABBER}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_MESSENGER}:</span></td>
	<td class="row2"><input type="text" class="post" style="width: 150px;" name="msn" size="20" maxlength="255" value="{MSN}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_SKYPE}:</span></td>
	<td class="row2"><input type="text" class="post" style="width: 150px;" name="skype" size="20" maxlength="255" value="{SKYPE}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_TWITTER}:</span></td>
	<td class="row2"><input type="text" class="post" style="width: 150px;" name="twitter" size="20" maxlength="255" value="{TWITTER}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_YAHOO}:</span></td>
	<td class="row2"><input type="text" class="post" style="width: 150px;" name="yim" size="20" maxlength="255" value="{YIM}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_WEBSITE}:</span></td>
	<td class="row2"><input type="text" class="post" style="width: 200px;" name="website" size="25" maxlength="255" value="{WEBSITE}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_LOCATION}:</span></td>
	<td class="row2"><input type="text" class="post" style="width: 200px;" name="location" size="25" maxlength="100" value="{LOCATION}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_FLAG}:</span></td>
	<td class="row2">
		<table>
			<tr>
				<td width="40%" nowrap="nowrap">{FLAG_SELECT}&nbsp;&nbsp;</td>
				<td><img src="images/flags/{FLAG_START}" width="16" height="11" name="user_flag" alt="{L_FLAG}" /></td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_OCCUPATION}:</span></td>
	<td class="row2"><input type="text" class="post" style="width: 200px;" name="occupation" size="25" maxlength="100" value="{OCCUPATION}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_INTERESTS}:</span></td>
	<td class="row2"><input type="text" class="post" style="width: 200px;" name="interests" size="35" maxlength="150" value="{INTERESTS}" /></td>
</tr>
<tr>
	<td class="row1">
		<span class="gen">{L_EXTRA_PROFILE_INFO}:</span><br />
		<span class="gensmall">{L_EXTRA_PROFILE_INFO_EXPLAIN}</span>
	</td>
	<td class="row2"><textarea name="selfdes" style="width: 475px;" rows="5" cols="60" class="post">{SELFDES}</textarea></td>
</tr>

<tr>
	<td class="row1"><span class="gen">{L_GENDER}:{GENDER_REQUIRED}</span></td>
	<td class="row2">
		<input type="radio" {LOCK_GENDER} name="gender" value="0" {GENDER_NO_SPECIFY_CHECKED}/>
		<span class="gen">{L_GENDER_NOT_SPECIFY}</span>&nbsp;&nbsp;
		<input type="radio" name="gender" value="1" {GENDER_MALE_CHECKED}/>
		<span class="gen">{L_GENDER_MALE}</span>&nbsp;&nbsp;
		<input type="radio" name="gender" value="2" {GENDER_FEMALE_CHECKED}/>
		<span class="gen">{L_GENDER_FEMALE}</span>
	</td>
</tr>

<tr>
	<td class="row1"><span class="gen">{L_BIRTHDAY}:{BIRTHDAY_REQUIRED}</span></td>
	<td class="row2"><span class="gen">{S_BIRTHDAY}</span></td>
</tr>
<!-- BEGIN switch_custom_fields -->
<tr><td class="row3" colspan="2"><span class="gensmall">{switch_custom_fields.L_CUSTOM_FIELD_NOTICE}</span></td></tr>
<!-- END switch_custom_fields -->
<!-- BEGIN custom_fields -->
<tr>
	<td class="row1"><span class="gen">{custom_fields.NAME}:{custom_fields.REQUIRED}</span>
		<!-- BEGIN switch_description -->
		<br /><span class="gensmall">{custom_fields.switch_description.DESCRIPTION}</span>
		<!-- END switch_description -->
	</td>
	<td class="row2">{custom_fields.FIELD}</td>
</tr>
<!-- END custom_fields -->

<!-- END switch_cpl_profile_info -->

<!-- BEGIN switch_cpl_preferences -->
<tr><td class="row-header" colspan="2"><span>{L_PREFERENCES}</span></td></tr>
<tr>
	<td class="row1"><span class="gen">{L_HIDE_USER}:</span></td>
	<td class="row2">
		<label><input type="radio" name="hideonline" value="1" {HIDE_USER_YES} /><span class="gen">&nbsp;{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="hideonline" value="0" {HIDE_USER_NO} /><span class="gen">&nbsp;{L_NO}</span></label>
	</td>
</tr>
<tr>
	<td class="row1 tw50pct"><span class="gen">{L_PUBLIC_VIEW_EMAIL}:</span></td>
	<td class="row2">
		<label><input type="radio" name="viewemail" value="1" {VIEW_EMAIL_YES} /><span class="gen">&nbsp;{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="viewemail" value="0" {VIEW_EMAIL_NO} /><span class="gen">&nbsp;{L_NO}</span></label>
	</td>
</tr>
<tr>
	<td class="row1 tw50pct"><span class="gen">{L_MASS_EMAIL}:</span></td>
	<td class="row2">
		<label><input type="radio" name="allowmassemail" value="1" {ALLOW_MASS_EMAIL_YES} /><span class="gen">&nbsp;{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="allowmassemail" value="0" {ALLOW_MASS_EMAIL_NO} /><span class="gen">&nbsp;{L_NO}</span></label>
	</td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_NOTIFY_ON_REPLY}:</span><br />
		<span class="gensmall">{L_NOTIFY_ON_REPLY_EXPLAIN}</span></td>
	<td class="row2">
		<label><input type="radio" name="notifyreply" value="1" {NOTIFY_REPLY_YES} /><span class="gen">&nbsp;{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="notifyreply" value="0" {NOTIFY_REPLY_NO} /><span class="gen">&nbsp;{L_NO}</span></label>
	</td>
</tr>
<tr>
	<td class="row1 tw50pct"><span class="gen">{L_PM_IN}:</span><br />
		<span class="gensmall">{L_PM_IN_EXPLAIN}</span></td>
	<td class="row2">
		<label><input type="radio" name="allowpmin" value="1" {ALLOW_PM_IN_YES} /><span class="gen">&nbsp;{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="allowpmin" value="0" {ALLOW_PM_IN_NO} /><span class="gen">&nbsp;{L_NO}</span></label>
	</td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_NOTIFY_ON_PRIVMSG}:</span></td>
	<td class="row2">
		<label><input type="radio" name="notifypm" value="1" {NOTIFY_PM_YES} /><span class="gen">&nbsp;{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="notifypm" value="0" {NOTIFY_PM_NO} /><span class="gen">&nbsp;{L_NO}</span></label>
	</td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_POPUP_ON_PRIVMSG}:</span><br /><span class="gensmall">{L_POPUP_ON_PRIVMSG_EXPLAIN}</span></td>
	<td class="row2">
		<label><input type="radio" name="popup_pm" value="1" {POPUP_PM_YES} /><span class="gen">&nbsp;{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="popup_pm" value="0" {POPUP_PM_NO} /><span class="gen">&nbsp;{L_NO}</span></label>
	</td>
</tr>
<tr>
<td class="row1"><span class="gen">{L_PROFILE_VIEW_POPUP}:</span></td>
		<td class="row2">
		<label><input type="radio" name="profile_view_popup" value="1" {PROFILE_VIEW_POPUP_YES} /><span class="gen">&nbsp;{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="profile_view_popup" value="0" {PROFILE_VIEW_POPUP_NO} /><span class="gen">&nbsp;{L_NO}</span></label></td>
	</tr>
<tr>
	<td class="row1"><span class="gen">{L_ALWAYS_ADD_SIGNATURE}:</span></td>
	<td class="row2">
		<label><input type="radio" name="attachsig" value="1" {ALWAYS_ADD_SIGNATURE_YES} /><span class="gen">&nbsp;{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="attachsig" value="0" {ALWAYS_ADD_SIGNATURE_NO} /><span class="gen">&nbsp;{L_NO}</span></label>
	</td>

</tr>
	<tr>
		<td class="row1"><span class="gen">{L_ALWAYS_SET_BOOKMARK}:</span></td>
		<td class="row2">
		<input type="radio" name="setbm" value="1" {ALWAYS_SET_BOOKMARK_YES} />
		<span class="gen">{L_YES}</span>&nbsp;&nbsp;
		<input type="radio" name="setbm" value="0" {ALWAYS_SET_BOOKMARK_NO} />
		<span class="gen">{L_NO}</span></td>
	</tr>
<tr>
	<td class="row1"><span class="gen">{L_RETRO_SIG}:</span><br /><span class="gensmall">{L_RETRO_SIG_EXPLAIN}</span></td>
	<td class="row2">
		<input type="checkbox" name="retrosig" />&nbsp;
		<span class="gensmall">{L_RETRO_SIG_CHECKBOX}</span>
	</td>
</tr>

<tr>
	<td class="row1"><span class="gen">{L_ALWAYS_ALLOW_BBCODE}:</span></td>
	<td class="row2">
		<label><input type="radio" name="allowbbcode" value="1" {ALWAYS_ALLOW_BBCODE_YES} /><span class="gen">&nbsp;{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="allowbbcode" value="0" {ALWAYS_ALLOW_BBCODE_NO} /><span class="gen">&nbsp;{L_NO}</span></label>
	</td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_ALWAYS_ALLOW_HTML}:</span></td>
	<td class="row2">
		<label><input type="radio" name="allowhtml" value="1" {ALWAYS_ALLOW_HTML_YES} /><span class="gen">&nbsp;{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="allowhtml" value="0" {ALWAYS_ALLOW_HTML_NO} /><span class="gen">&nbsp;{L_NO}</span></label>
	</td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_ALWAYS_ALLOW_SMILIES}:</span></td>
	<td class="row2">
		<label><input type="radio" name="allowsmilies" value="1" {ALWAYS_ALLOW_SMILIES_YES} /><span class="gen">&nbsp;{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="allowsmilies" value="0" {ALWAYS_ALLOW_SMILIES_NO} /><span class="gen">&nbsp;{L_NO}</span></label>
	</td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_SHOW_AVATARS}:</span></td>
	<td class="row2">
		<label><input type="radio" name="showavatars" value="1" {SHOW_AVATARS_YES} /><span class="gen">&nbsp;{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="showavatars" value="0" {SHOW_AVATARS_NO} /><span class="gen">&nbsp;{L_NO}</span></label>
	</td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_SHOW_SIGNATURES}:</span></td>
	<td class="row2">
		<label><input type="radio" name="showsignatures" value="1" {SHOW_SIGNATURES_YES} /><span class="gen">&nbsp;{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="showsignatures" value="0" {SHOW_SIGNATURES_NO} /><span class="gen">&nbsp;{L_NO}</span></label>&nbsp;&nbsp;
	</td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_ALWAYS_ALLOW_SWEARYWORDS}:</span></td>
	<td class="row2">
		<label><input type="radio" name="allowswearywords" value="1" {ALWAYS_ALLOW_SWEARYWORDS_YES} />
		<span class="gen">{L_YES}</span></label>&nbsp;&nbsp;
		<label><input type="radio" name="allowswearywords" value="0" {ALWAYS_ALLOW_SWEARYWORDS_NO} />
		<span class="gen">{L_NO}</span></label></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_TOPICS_PER_PAGE}:</span></td>
	<td class="row2"><input class="post" type="text" name="user_topics_per_page" size="3" maxlength="4" value="{TOPICS_PER_PAGE}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_POSTS_PER_PAGE}:</span></td>
	<td class="row2"><input class="post" type="text" name="user_posts_per_page" size="3" maxlength="4" value="{POSTS_PER_PAGE}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_HOT_THRESHOLD}:</span></td>
	<td class="row2"><input class="post" type="text" name="user_hot_threshold" size="3" maxlength="4" value="{HOT_TOPIC}" /></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_VIEW_POSTS_DAYS}:</span></td>
	<td class="row2">{USER_POST_SHOW_DAYS_SELECT}</td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_VIEW_POSTS_KEY}:</span></td>
	<td class="row2">{USER_POST_SORTBY_TYPE_SELECT}</td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_VIEW_POSTS_DIR}:</span></td>
	<td class="row2">{USER_POST_SORTBY_DIR_SELECT}</td>
</tr>
<!-- BEGIN switch_upi2db_is_on -->
	<tr>
		<th class="thSides" colspan="2">{L_UPI2DB_SYSTEM}</th>
	</tr>
<!-- END switch_upi2db_is_on -->
<!-- BEGIN switch_upi2db_user_select -->
	<tr>
		<td class="row1"><span class="gen">{L_UPI2DB_WHICH_SYSTEM}</span><br />
		<span class="gensmall">{L_UPI2DB_WHICH_SYSTEM_EXPLAIN}</span></td>
		<td class="row2">
		<input type="radio" name="upi2db_which_system" value="0" {COOKIE_SYSTEM} /><span class="gen">{L_COOKIE_SYSTEM}</span>&nbsp;&nbsp;
		<input type="radio" name="upi2db_which_system" value="1" {UPI2DB_SYSTEM} /><span class="gen">{L_UPI2DB_SYSTEM}</span>
		</td>
	</tr>
<!-- END switch_upi2db_user_select -->
<!-- BEGIN switch_upi2db_words -->
	<tr>
		<td class="row1">
			<span class="gen">{L_UPI2DB_NEW_WORD}:</span><br />
			<span class="gensmall">{L_UPI2DB_NEW_WORD_EXPLAIN}</span>
		</td>
		<td class="row2">
			<input type="radio" name="upi2db_new_word" value="1" {UPI2DB_NEW_WORD_YES} />
			<span class="gen">{L_YES}</span>&nbsp;&nbsp;
			<input type="radio" name="upi2db_new_word" value="0" {UPI2DB_NEW_WORD_NO} />
			<span class="gen">{L_NO}</span>
		</td>
	</tr>
	<tr>
		<td class="row1">
			<span class="gen">{L_UPI2DB_EDIT_WORD}:</span><br />
			<span class="gensmall">{L_UPI2DB_EDIT_WORD_EXPLAIN}</span>
		</td>
		<td class="row2">
			<input type="radio" name="upi2db_edit_word" value="1" {UPI2DB_EDIT_WORD_YES} />
			<span class="gen">{L_YES}</span>&nbsp;&nbsp;
			<input type="radio" name="upi2db_edit_word" value="0" {UPI2DB_EDIT_WORD_NO} />
			<span class="gen">{L_NO}</span>
		</td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">{L_UPI2DB_UNREAD_COLOR}:</span></td>
		<td class="row2">
			<input type="radio" name="upi2db_unread_color" value="1" {UPI2DB_UNREAD_COLOR_YES} />
			<span class="gen">{L_YES}</span>&nbsp;&nbsp;
			<input type="radio" name="upi2db_unread_color" value="0" {UPI2DB_UNREAD_COLOR_NO} />
			<span class="gen">{L_NO}</span>
		</td>
	</tr>
<!-- END switch_upi2db_words -->
<!-- END switch_cpl_preferences -->

<!-- BEGIN switch_cpl_board_settings -->
	<tr>
		<td class="row-header" colspan="2"><span>{L_CPL_BOARD_SETTINGS}</span></td>
	</tr>
<tr>
	<td class="row1" width="38%"><span class="gen">{L_BOARD_LANGUAGE}:</span></td>
	<td class="row2"><span class="gensmall">{LANGUAGE_SELECT}</span></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_BOARD_STYLE}:</span></td>
	<td class="row2"><span class="gensmall">{STYLE_SELECT}</span></td>
</tr>

<tr>
	<td class="row1">
		<span class="gen">{L_TIME_MODE}:</span><br />
		<span class="gensmall">{L_TIME_MODE_TEXT}</span>
	</td>
	<td class="row2">
		<span class="gen">{L_TIME_MODE_MANUAL}</span><br />
		<span class="gen">&nbsp;&nbsp;{L_TIME_MODE_DST}:</span>
		<input type="radio" name="time_mode" value="1" {TIME_MODE_MANUAL_DST_CHECKED} /><span class="gen">{L_YES}{L_TIME_MODE_DST_ON}</span>&nbsp;
		<input type="radio" name="time_mode" value="0" {TIME_MODE_MANUAL_CHECKED} /><span class="gen">{L_NO}{L_TIME_MODE_DST_OFF}</span>&nbsp;
		<input type="radio" name="time_mode" value="2" {TIME_MODE_SERVER_SWITCH_CHECKED} /><span class="gen">{L_TIME_MODE_DST_SERVER}</span><br />
		<span class="gen">&nbsp;&nbsp;{L_TIME_MODE_DST_TIME_LAG}: </span><input type="text" name="dst_time_lag" value="{DST_TIME_LAG}" maxlength="3" size="3" class="post" /><span class="gen">{L_TIME_MODE_DST_MN}</span><br />
		<span class="gen">&nbsp;&nbsp;{L_TIME_MODE_TIMEZONE}: </span><span class="gensmall">{TIMEZONE_SELECT}</span>
	</td>
</tr>

<tr>
	<td class="row1"><span class="gen">{L_DATE_FORMAT}:</span></td>
	<td class="row2">{DATE_FORMAT}</td>
</tr>
<!-- END switch_cpl_board_settings -->

<!-- BEGIN switch_cpl_avatar -->
<!-- BEGIN switch_avatar_block -->
<tr>
	<td class="row-header" colspan="2"><span>{L_AVATAR_PANEL}</span></td>
</tr>
<tr>
	<td class="row1" colspan="2"><table class="talignc tw70pct">
	<tr>
		<td width="65%"><span class="gensmall">{L_AVATAR_EXPLAIN}</span></td>
		<td class="tdalignc"><span class="gensmall">{L_CURRENT_IMAGE}</span><br />{AVATAR}<br /><label><input type="checkbox" name="avatardel" />&nbsp;<span class="gensmall">{L_DELETE_AVATAR}</span></label></td>
	</tr>
	</table></td>
</tr>
<!-- BEGIN switch_avatar_local_upload -->
<tr>
	<td class="row1"><span class="gen">{L_UPLOAD_AVATAR_FILE}:</span></td>
	<td class="row2"><input type="hidden" name="MAX_FILE_SIZE" value="{AVATAR_SIZE}" /><input type="file" name="avatar" class="post" style="width: 200px;" /></td>
</tr>
<!-- END switch_avatar_local_upload -->
<!-- BEGIN switch_avatar_remote_upload -->
<tr>
	<td class="row1"><span class="gen">{L_UPLOAD_AVATAR_URL}:</span><br /><span class="gensmall">{L_UPLOAD_AVATAR_URL_EXPLAIN}</span></td>
	<td class="row2"><input type="text" name="avatarurl" size="40" class="post" style="width: 200px;" /></td>
</tr>
<!-- END switch_avatar_remote_upload -->
<!-- BEGIN switch_avatar_remote_link -->
<tr>
	<td class="row1"><span class="gen">{L_LINK_REMOTE_AVATAR}:</span><br /><span class="gensmall">{L_LINK_REMOTE_AVATAR_EXPLAIN}</span></td>
	<td class="row2"><input type="text" name="avatarremoteurl" size="40" class="post" style="width: 200px;" /></td>
</tr>
<!-- END switch_avatar_remote_link -->
<!-- BEGIN switch_gravatar -->
<tr>
	<td class="row1"><span class="gen">{L_GRAVATAR}:</span><br /><span class="gensmall">{L_GRAVATAR_EXPLAIN}</span></td>
	<td class="row2"><input type="text" name="gravatar" value="{GRAVATAR}" size="40" class="post" style="width: 200px;" /></td>
</tr>
<!-- END switch_gravatar -->
<!-- BEGIN switch_avatar_local_gallery -->
<tr>
	<td class="row1" width="38%"><span class="gen">{L_AVATAR_GALLERY}:</span></td>
	<td class="row2"><input type="submit" name="avatargallery" value="{L_SHOW_GALLERY}" class="liteoption" /></td>
</tr>
<!-- END switch_avatar_local_gallery -->
<!-- BEGIN switch_avatar_generator -->
<tr>
	<td class="row1"><span class="gen">{L_GENERATE_AVATAR}:</span></td>
	<td class="row2"><input type="submit" name="avatargenerator" value="{L_AVATAR_GENERATOR}" class="liteoption" /></td>
</tr>
<!-- END switch_avatar_generator -->
<!-- END switch_avatar_block -->

<!-- END switch_cpl_avatar -->
<tr>
	<td class="catBottom" colspan="2">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="{L_RESET}" name="reset" class="liteoption" /></td>
</tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
<!-- BEGIN switch_cpl_menu -->
	</td>
	</tr>
</table>
<!-- END switch_cpl_menu -->
</form>

<!-- INCLUDE overall_footer.tpl -->