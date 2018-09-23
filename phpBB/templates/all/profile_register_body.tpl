<!-- INCLUDE overall_header.tpl -->

<!-- INCLUDE profile_ajax_js.tpl -->

<form action="{S_PROFILE_ACTION}" {S_FORM_ENCTYPE} method="post">

{ERROR_BOX}

<!-- IF S_REGISTER_MESSAGE -->{IMG_TBL}<table class="forumline"><tr><td class="row1"><div class="post-text">{L_REGISTER_MESSAGE}</div></td></tr></table>{IMG_TBR}<!-- ENDIF -->

{IMG_THL}{IMG_THC}<span class="forumlink">{L_REGISTRATION_INFO}</span>{IMG_THR}<table class="forumlinenb">
<!-- <tr><th class="tvalignm" colspan="2">{L_REGISTRATION_INFO}</th></tr> -->
<!-- IF SOCIAL_CONNECT -->
<tr>
	<td class="row1" colspan="2">
		<img style="float: left; margin-right: 10px;" src="{U_PROFILE_PHOTO}" alt="{USER_REAL_NAME}" />
		<span class="genmed">
			{L_SOCIAL_CONNECT_REGISTER_INFO}<br />
			<img src="{U_SOCIAL_NETWORK_ICON}" alt="{SOCIAL_NETWORK_NAME}" title="{SOCIAL_NETWORK_NAME}" />&nbsp;<b>{USER_REAL_NAME}</b><br/>
			<a href="{U_PROFILE_LINK}" target="_blank">{U_PROFILE_LINK}</a>
		</span>
	</td>
</tr>
<tr><th class="tvalignm" colspan="2">{L_REGISTRATION_INFO}</th></tr>
<!-- ENDIF -->
<tr><td class="row2" colspan="2"><span class="gensmall">{L_ITEMS_REQUIRED}</span></td></tr>
<tr>
	<td class="row1" width="38%"><span class="gen">{L_USERNAME}:&nbsp;*</span></td>
	<td class="row2"><input type="text" class="post" style="width:200px" name="username" size="25" maxlength="40" value="{USERNAME}" {VERIFY_UN_JS} /><div id="pseudobox"></div></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_EMAIL_ADDRESS}:&nbsp;*</span></td>
	<td class="row2"><input type="text" class="post" style="width:200px" name="email" size="25" maxlength="255" value="{EMAIL}" {VERIFY_EMAIL_JS} /><div id="emailbox"></div></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_CONFIRM_EMAIL}:&nbsp;*</span></td>
	<td class="row2"><input type="text" class="post" style="width:200px" name="email_confirm" size="25" maxlength="255" value="{EMAIL_CONFIRM}" /></td>
</tr>
<tr>
	<td class="row1">
		<span class="gen">{L_NEW_PASSWORD}:&nbsp;*</span><br />
		<span class="gensmall">{L_PASSWORD_IF_CHANGED}&nbsp;</span>
	</td>
	<td class="row2"><input type="password" class="post" style="width: 200px" name="new_password" size="25" maxlength="100" value="{PASSWORD}" /></td>
</tr>
<tr>
	<td class="row1">
		<span class="gen">{L_CONFIRM_PASSWORD}:&nbsp;*</span><br />
		<span class="gensmall">{L_PASSWORD_CONFIRM_IF_CHANGED}&nbsp;</span>
	</td>
	<td class="row2"><input type="password" class="post" style="width: 200px" name="password_confirm" size="25" maxlength="100" value="{PASSWORD_CONFIRM}" /></td>
</tr>
<!-- BEGIN switch_confirm -->
<tr><td class="row1 row-center" colspan="2"><span class="gensmall">{L_CONFIRM_CODE_IMPAIRED}</span><br /><br />{CONFIRM_IMG}<br /><br /></td></tr>
<tr>
	<td class="row1">
		<span class="gen">{L_CONFIRM_CODE}:&nbsp;*</span><br />
		<span class="gensmall">{L_CONFIRM_CODE_EXPLAIN}</span>
	</td>
	<td class="row2"><input type="text" class="post" style="width: 200px" name="confirm_code" size="6" maxlength="6" value="" /></td>
</tr>
<!-- END switch_confirm -->
<tr>
	<td class="row1"><span class="gen">{L_BIRTHDAY}:{BIRTHDAY_REQUIRED}</span></td>
	<td class="row2"><span class="gen">{S_BIRTHDAY}</span></td>
</tr>
<tr>
	<td class="row1"><span class="gen">{L_GENDER}:{GENDER_REQUIRED}</span></td>
	<td class="row2">
		<input type="radio" {LOCK_GENDER} name="gender" value="0" {GENDER_NO_SPECIFY_CHECKED} />
		<span class="gen">{L_GENDER_NOT_SPECIFY}</span>
			&nbsp;&nbsp;<input type="radio" name="gender" value="1" {GENDER_MALE_CHECKED} />
		<span class="gen">{L_GENDER_MALE}</span>
			&nbsp;&nbsp;<input type="radio" name="gender" value="2" {GENDER_FEMALE_CHECKED} />
		<span class="gen">{L_GENDER_FEMALE}</span>
	</td>
</tr>

<!-- BEGIN switch_cpl_profile_info -->
<!-- BEGIN switch_custom_fields -->
<tr><td class="row3" colspan="2"><span class="gensmall">{switch_custom_fields.L_CUSTOM_FIELD_NOTICE}</span></td></tr>
<!-- END switch_custom_fields -->
<!-- BEGIN custom_fields -->
<tr>
	<td class="row1">
		<span class="gen">{custom_fields.NAME}:{custom_fields.REQUIRED}</span>
		<!-- BEGIN switch_description -->
		<br /><span class="gensmall">{custom_fields.switch_description.DESCRIPTION}</span>
		<!-- END switch_description -->
	</td>
	<td class="row2">{custom_fields.FIELD}</td>
</tr>
<!-- END custom_fields -->
<!-- END switch_cpl_profile_info -->
<tr><th class="tvalignm" colspan="2">{L_PREFERENCES}</th></tr>
<tr>
	<td class="row1"><span class="gen">{L_BOARD_LANGUAGE}:</span></td>
	<td class="row2"><span class="gensmall">{LANGUAGE_SELECT}</span></td>
</tr>
<tr>
	<td class="row1">
		<span class="gen">{L_TIME_MODE}:</span><br />
		<span class="gensmall">{L_TIME_MODE_TEXT}</span>
	</td>
	<td class="row2">
		<!--
		<span class="gen">{L_TIME_MODE_AUTO}</span><br />
		<input type="radio" name="time_mode" value="6" {TIME_MODE_FULL_PC_CHECKED}/>
		<span class="gen">{L_TIME_MODE_FULL_PC}</span>&nbsp;&nbsp;<br />
		<input type="radio" name="time_mode" value="4" {TIME_MODE_SERVER_PC_CHECKED}/>
		<span class="gen">{L_TIME_MODE_SERVER_PC}</span>&nbsp;&nbsp;<br />
		<input type="radio" name="time_mode" value="3" {TIME_MODE_FULL_SERVER_CHECKED}/>
		<span class="gen">{L_TIME_MODE_FULL_SERVER}</span>
		<br /><br />
		-->
		<span class="gen">{L_TIME_MODE_MANUAL}</span><br />
		<span class="gen">&nbsp;&nbsp;{L_TIME_MODE_DST}:</span>
		<input type="radio" name="time_mode" value="1" {TIME_MODE_MANUAL_DST_CHECKED} />
		<span class="gen">{L_YES}{L_TIME_MODE_DST_ON}</span>
		&nbsp;<input type="radio" name="time_mode" value="0" {TIME_MODE_MANUAL_CHECKED} />
		<span class="gen">{L_NO}{L_TIME_MODE_DST_OFF}</span>
		&nbsp;<input type="radio" name="time_mode" value="2" {TIME_MODE_SERVER_SWITCH_CHECKED} />
		<span class="gen">{L_TIME_MODE_DST_SERVER}</span><br />
		<span class="gen">&nbsp;&nbsp;{L_TIME_MODE_DST_TIME_LAG}:</span>
		<input type="text" name="dst_time_lag" value="{DST_TIME_LAG}" maxlength="3" size="3" class="post" />
		<span class="gen">{L_TIME_MODE_DST_MN}</span><br />
		<span class="gen">&nbsp;&nbsp;{L_TIME_MODE_TIMEZONE}:</span>
		<span class="gensmall">{TIMEZONE_SELECT}</span>
	</td>
</tr>
<tr>
	<td class="catBottom" colspan="2">{S_HIDDEN_FIELDS}
		<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;
		<input type="reset" value="{L_RESET}" name="reset" class="liteoption" />
	</td>
</tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
</form>

<!-- INCLUDE overall_footer.tpl -->