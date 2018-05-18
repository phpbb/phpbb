<!-- BEGIN switch_privmsg -->
<!-- INCLUDE overall_header.tpl -->
<!-- END switch_privmsg -->

<!-- BEGIN switch_privmsg -->
{CPL_MENU_OUTPUT}

<div class="block">
	<div class="popup">
		<a href="javascript:void(0);" class="gradient">{L_INBOX}</a>
		<ul class="menu">
			<li>{INBOX}</li>
			<li>{SENTBOX}</li>
			<li>{OUTBOX}</li>
			<li>{SAVEBOX}</li>
		</ul>
	</div>
</div>

<!-- END switch_privmsg -->

<!-- BEGIN switch_prv_msg_review -->
<table class="forumline">
<tr><td class="row-header" colspan="2"><span>{switch_prv_msg_review.PRIVATE_MSG_TITLE}</span></td></tr>
<tr><td class="row2 tw65pct" colspan="2"><div class="post-text post-text-hide-flow">{switch_prv_msg_review.PRIVATE_MSG_REVIEW}</div></td></tr>
</table>
<!-- END switch_prv_msg_review -->
<form action="{S_POST_ACTION}" method="post" name="post" onsubmit="return checkForm(this);" {S_FORM_ENCTYPE}>
<!-- BEGIN save_draft_confirm -->
{IMG_THL}{IMG_THC}<span class="forumlink">{L_DRAFTS}</span>{IMG_THR}<table class="forumlinenb">
<tr><td class="row1g">{L_DRAFT_CONFIRM}<br /><br /><input type="submit" name="draft_confirm" value="{L_YES}" class="mainoption" /></td></tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
<br />
<!-- END save_draft_confirm -->
<input type="hidden" name="post_time" value="<?php echo time(); ?>" />
{POST_PREVIEW_BOX}
{ERROR_BOX}

<!-- IF S_FORUM_RULES -->
<div class="block">
	<!-- IF S_FORUM_RULES_TITLE --><h2>{L_FORUM_RULES}</h2><!-- ENDIF -->
	{FORUM_RULES}
</div>
<!-- ENDIF -->

<div class="block-empty">
	<h2>{L_POST_A}</h2>
<table class="forumlinenb">
<!-- BEGIN switch_username_select -->
<tr>
	<td class="row1"><span class="gen"><b>{L_USERNAME}</b></span></td>
	<td class="row2"><span class="genmed"><input type="text" class="post" tabindex="1" name="username" size="25" maxlength="25" value="{USERNAME}" /></span></td>
</tr>
<!-- END switch_username_select -->
<!-- BEGIN switch_privmsg -->
<tr>
	<td class="row1"><span class="gen"><b>{L_USERNAME}</b></span></td>
	<td class="row2"><span class="genmed"><input type="text" class="post" name="username" maxlength="25" size="25" tabindex="1" value="{USERNAME}" /></td>
</tr>
<tr id="pm_username_error_tbl" style="display: none;">
	<td class="row1">&nbsp;</td>
	<td class="row2"><span class="gen" id="pm_username_error_text">&nbsp;</span></td>
</tr>
<!-- END switch_privmsg -->
<tr>
	<td class="row1"><span class="gen"><b>{L_SUBJECT}</b></span></td>
	<td class="row2"><input type="text" name="subject" maxlength="120" style="width: 98%;" tabindex="2" class="post" value="{SUBJECT}" {S_AJAX_BLUR} /></td>
</tr>
<!-- BEGIN topic_description -->
<tr>
	<td class="row1"><span class="gen"><b>{L_TOPIC_DESCRIPTION}</b></span></td>
	<td class="row2"><span class="gen"><input type="text" name="topic_desc" maxlength="240" style="width: 98%;" tabindex="3" class="post" value="{TOPIC_DESCRIPTION}" /></span></td>
</tr>
<!-- END topic_description -->
<!-- BEGIN switch_type_toggle -->
<tr>
	<td class="row1"><span class="gen"><b>{L_TYPE_TOGGLE_TITLE}</b></span></td>
	<td class="row2"><span class="gen"><b>{S_TYPE_TOGGLE}</b></span></td>
</tr>
<!-- END switch_type_toggle -->
<!-- BEGIN switch_news_cat -->
<tr>
	<td class="row1"><span class="gen"><b>{switch_news_cat.L_NEWS_CATEGORY}</b></span></td>
	<td class="row2"><select name="{switch_news_cat.S_NAME}">{switch_news_cat.S_CATEGORY_BOX}</select></td>
</tr>
<!-- END switch_news_cat -->
<tr>
	<td class="row1 tw22pct"><span class="gen"><b>{L_MESSAGE_BODY}</b></span></td>
	<td class="row2 tw78pct">
		<div class="message-box"><textarea id="message" name="message" style="width: 98%; height: 200px;" tabindex="4">{MESSAGE}</textarea></div>
	</td>
</tr>
<!-- BEGIN switch_edit -->
<tr>
	<td class="row1"><span class="gen"><b>{L_EDIT_NOTES}</b></span></td>
	<td class="row2"><input type="text" name="notes" maxlength="60" style="width: 98%;" tabindex="5" class="post" value="{switch_edit.NOTES}" /></td>
</tr>
<!-- END switch_edit -->
<!-- IF S_POSTING_TOPIC -->
<!-- IF S_ADMIN or S_MOD -->
<!--
<tr>
	<td class="row1"><span class="gen"><b>{L_CLEAN_NAME}</b></span><br /><span class="gensmall">{L_CLEAN_NAME_EXPLAIN}</span></td>
	<td class="row2"><span class="gen"><input type="text" name="topic_title_clean" maxlength="240" style="width: 98%;" tabindex="6" class="post" value="{TOPIC_TITLE_CLEAN}" /></span></td>
</tr>
-->
<!-- ENDIF -->
<!-- IF S_TOPIC_TAGS -->
<tr>
	<td class="row1"><span class="gen"><b>{L_TOPIC_TAGS}</b></span><br /><span class="gensmall">{L_TOPIC_TAGS_EXPLAIN}</span></td>
	<td class="row2"><span class="gen"><input type="text" name="topic_tags" maxlength="240" style="width: 98%;" tabindex="7" class="post" value="{TOPIC_TAGS}" /></span></td>
</tr>
<!-- ENDIF -->
<!-- ENDIF -->
<tr>
	<td class="catBottom" colspan="2">
		<input type="submit" tabindex="9" name="preview" class="liteoption" value="{L_PREVIEW}" />&nbsp;
		<!-- BEGIN allow_drafts -->
		<input type="submit" tabindex="10" name="draft" class="altoption" value="{L_DRAFT_SAVE}" />&nbsp;
		<!-- END allow_drafts -->
		<input type="submit" accesskey="s" tabindex="11" name="post" class="mainoption" value="{L_SUBMIT}" />
	</td>
</tr>
<tr>
	<td class="row1"><span class="gen"><b>{L_OPTIONS}</b></span><br /><span class="gensmall">{HTML_STATUS}<br />{BBCODE_STATUS}<br />{SMILIES_STATUS}</span></td>
	<td class="row2">
		<div class="genmed">
			<!-- BEGIN switch_show_portal -->
			<label><input type="checkbox" name="topic_show_portal" {S_TOPIC_SHOW_PORTAL} />&nbsp;{L_SHOW_PORTAL}</label><br />
			<!-- END switch_show_portal -->
			<!-- BEGIN switch_html_checkbox -->
			<label><input type="checkbox" name="disable_html" {S_HTML_CHECKED} />&nbsp;{L_DISABLE_HTML}</label><br />
			<!-- END switch_html_checkbox -->
			<label><input type="checkbox" name="disable_acro_auto" {S_ACRO_AUTO_CHECKED} />&nbsp;{L_DISABLE_ACRO_AUTO}</label><br />
			<!-- BEGIN switch_bbcode_checkbox -->
			<label><input type="checkbox" name="disable_bbcode" {S_BBCODE_CHECKED} />&nbsp;{L_DISABLE_BBCODE}</label><br />
			<!-- END switch_bbcode_checkbox -->
			<!-- BEGIN switch_smilies_checkbox -->
			<label><input type="checkbox" name="disable_smilies" {S_SMILIES_CHECKED} />&nbsp;{L_DISABLE_SMILIES}</label><br />
			<!-- END switch_smilies_checkbox -->
			<!-- BEGIN switch_signature_checkbox -->
			<label><input type="checkbox" name="attach_sig" {S_SIGNATURE_CHECKED} />&nbsp;{L_ATTACH_SIGNATURE}</label><br />
			<!-- END switch_signature_checkbox -->
			<!-- BEGIN switch_bookmark_checkbox -->
			<label><input type="checkbox" name="setbm" {S_SETBM_CHECKED} />&nbsp;{L_SET_BOOKMARK}</label><br />
			<!-- END switch_bookmark_checkbox -->
			<!-- BEGIN switch_notify_checkbox -->
			<label><input type="checkbox" name="notify" {S_NOTIFY_CHECKED} />&nbsp;{L_NOTIFY_ON_REPLY}</label><br />
			<!-- END switch_notify_checkbox -->
			<!-- BEGIN switch_mark_edit_checkbox -->
			<label><input type="checkbox" name="mark_edit" {S_MARK_EDIT_CHECKED} />&nbsp;{L_MARK_EDIT}</label><br />
			<!-- END switch_mark_edit_checkbox -->
			<!-- BEGIN switch_delete_checkbox -->
			<label><input type="checkbox" name="delete" />&nbsp;{L_DELETE_POST}</label><br />
			<!-- END switch_delete_checkbox -->
			<!-- BEGIN switch_lock_topic -->
			<label><input type="checkbox" name="lock" {S_LOCK_CHECKED} />&nbsp;{L_LOCK_TOPIC}</label><br />
			<!-- END switch_lock_topic -->
			<!-- BEGIN switch_unlock_topic -->
			<label><input type="checkbox" name="unlock" {S_UNLOCK_CHECKED} />&nbsp;{L_UNLOCK_TOPIC}</label><br />
			<!-- END switch_unlock_topic -->
		</div>
	</td>
</tr>

{ATTACHBOX}
{POLLBOX}
{REGBOX}
<!-- BEGIN switch_type_cal -->
<tr><th colspan="2">{L_CALENDAR_TITLE}</th></tr>
<tr>
	<td class="row1"><span class="gen"><b>{L_CALENDAR_TITLE}</b></span></td>
	<td class="row1">
		<table cellpadding="2" cellspacing="0" width="100%" border="0">
			<tr>
				<td class="tdalignr tdnw"><span><b>{L_CALENDAR_TITLE}:&nbsp;</b></span></td>
				<td class="tw100pct">
					<span class="genmed">
						{S_CALENDAR_DAY}{S_CALENDAR_MONTH}{S_CALENDAR_YEAR}&nbsp;
						<a href="#" class="genmed" onclick="document.post.topic_calendar_day.value='{TODAY_DAY}';document.post.topic_calendar_month.value='{TODAY_MONTH}';document.post.topic_calendar_year.value='{TODAY_YEAR}';">{L_TODAY}</a>
					</span>
				</td>
			</tr>
			<tr>
				<td class="tdalignr tdnw"><span><b>{L_TIME}:&nbsp;</b></span></td>
				<td class="tw100pct">
					<span class="genmed">
						<input name="topic_calendar_hour" type="text" maxlength="2" size="3" value="{CALENDAR_HOUR}" class="post" />&nbsp;{L_HOURS}&nbsp;&nbsp;
						<input name="topic_calendar_min" type="text" maxlength="2" size="3" value="{CALENDAR_MIN}" class="post" />&nbsp;{L_MINUTES}
					</span>
				</td>
			</tr>
			<tr>
				<td class="tdalignr tdnw"><span><b>{L_CALENDAR_DURATION}:&nbsp;</b></span></td>
				<td class="tw100pct">
					<span class="genmed">
						<input name="topic_calendar_duration_day" type="text" maxlength="5" size="3" value="{CALENDAR_DURATION_DAY}" class="post" />&nbsp;{L_DAYS}&nbsp;&nbsp;
						<input name="topic_calendar_duration_hour" type="text" maxlength="5" size="3" value="{CALENDAR_DURATION_HOUR}" class="post" />&nbsp;{L_HOURS}&nbsp;&nbsp;
						<input name="topic_calendar_duration_min" type="text" maxlength="5" size="3" value="{CALENDAR_DURATION_MIN}" class="post" />&nbsp;{L_MINUTES}
					</span>
				</td>
			</tr>
		</table>
	</td>
</tr>
<!-- END switch_type_cal -->

<!-- BEGIN switch_confirm -->
<tr><td class="row1 row-center" colspan="2"><br /><br />{CONFIRM_IMAGE}<br /><br /></td></tr>
<tr>
	<td class="row2 row-center" colspan="2">
		<span class="gen"><b>{L_CT_CONFIRM}</b></span><br />
		<span class="gensmall">{L_CT_CONFIRM_E}</span><br /><br />
		<input type="text" class="post" style="width: 200px" name="confirm_code" size="6" value="" />
		{S_HIDDEN_FIELDS}
	</td>
</tr>
<!-- END switch_confirm -->

<tr>
	<td class="cat" colspan="2">
		{S_HIDDEN_FORM_FIELDS}
		<input type="submit" tabindex="12" name="preview" class="liteoption" value="{L_PREVIEW}" />&nbsp;
		<input type="submit" accesskey="s" tabindex="13" name="post" class="mainoption" value="{L_SUBMIT}" />
	</td>
</tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
</form>
<!-- BEGIN switch_privmsg -->
	</td>
</tr>
</table>
<!-- END switch_privmsg -->
<div align="right">{JUMPBOX}</div>

<!-- INCLUDE overall_footer.tpl -->