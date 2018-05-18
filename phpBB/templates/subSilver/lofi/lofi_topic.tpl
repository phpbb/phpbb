<!-- INCLUDE ../common/lofi/lofi_header.tpl -->

<?php
// check if quick reply is enabled
global $user, $config, $topic_id, $is_auth, $forum_topic_data, $lang;

$can_reply = $user->data['session_logged_in'] ? true : false;
if($can_reply)
{
	$is_auth_type = 'auth_reply';
	if(!$is_auth[$is_auth_type])
	{
		$can_reply = false;
	}
	elseif ((($forum_topic_data['forum_status'] == FORUM_LOCKED) || ($forum_topic_data['topic_status'] == TOPIC_LOCKED)) && !$is_auth['auth_mod'])
	{
		$can_reply = false;
	}
}
if($can_reply)
{
	$this->assign_block_vars('xs_quick_reply', array());
}

$postrow_count = (isset($this->_tpldata['postrow.'])) ? count($this->_tpldata['postrow.']) : 0;
for ($postrow_i = 0; $postrow_i < $postrow_count; $postrow_i++)
{
	$postrow_item = &$this->_tpldata['postrow.'][$postrow_i];
	// set profile link and search button
	if(!empty($postrow_item['PROFILE']) && strpos($postrow_item['POSTER_NAME'], '<') === false)
	{
		$postrow_item['SEARCH_IMG2'] = str_replace('%s', htmlspecialchars($postrow_item['POSTER_NAME']), $postrow_item['SEARCH_IMG']);
		$search = array($lang['Read_profile'], '<a ');
		$replace = array($postrow_item['POSTER_NAME'], '<a class="post-name" ');
		$postrow_item['POSTER_NAME'] = str_replace($search, $replace, $postrow_item['PROFILE']);
	}
}

// show quick reply
if($can_reply)
{
	// quick reply button
		$this->vars['LOFI_QUICK_REPLY'] = '<a href="javascript:showQuickEditor();" class="nav">' . $lang['quick_lofi'] . '</a>';
	// quick reply form
	ob_start();
?>
<div id="quick_reply" style="display: none; position: relative;">
	<form action="<?php echo append_sid('posting.' . PHP_EXT); ?>" method="post" name="post" style="display: inline;">
	{S_HIDDEN_FIELDS}
	<table class="forumline">
	<tr><th colspan="2"><span><?php echo $lang['quick_lofi']; ?></span></th></tr>
	<tr>
		<td class="row1" align="left" width="200" nowrap="nowrap"><span class="gen"><b><?php echo $lang['Subject']; ?>:</b></span></td>
		<td class="row2" align="left" width="100%"><input name="subject" class="post" type="text" size="45" maxlength="120" style="width: 98%;" tabindex="2" value="" /></td>
	</tr>
	<tr>
		<td class="row1" align="left" width="200" nowrap="nowrap"><span class="gen"><b><?php echo $lang['Message_body']; ?>:<br /><img src="{T_TEMPLATE_PATH}/images/spacer.gif" width="200" height="1" alt="" /></b></span></td>
		<td class="row2" align="left" width="100%"><textarea name="message" rows="15" cols="35" style="width: 98%;" tabindex="3" class="post"></textarea></td>
	</tr>

	<tr>
		<td class="catBottom" colspan="2">
			<input type="hidden" name="mode" value="reply" />
			<input type="hidden" name="t" value="<?php echo $topic_id; ?>" />
			<input type="hidden" name="sid" value="<?php echo $user->data['session_id']; ?>" />
			<input type="submit" accesskey="s" tabindex="6" name="post" class="mainoption" value="<?php echo $lang['Submit']; ?>" />&nbsp;
			<input type="submit" tabindex="5" name="preview" class="mainoption" value="<?php echo $lang['Preview']; ?>" />
		</td>
	</tr>
	</table>
	</form>
</div>
<?php
	$str = ob_get_contents();
	ob_end_clean();
	$this->vars['LOFI_QUICK_REPLY_FORM'] = $str;
}

?>

<div class="nav"><a href="{U_INDEX}">{L_INDEX}</a>{NAV_CAT_DESC}</div><br />

<h2><a href="{U_VIEW_TOPIC_BASE}" style="text-decoration: none;">{TOPIC_TITLE}</a></h2>

<!-- IF S_TOPIC_TAGS and TOPIC_TAGS --><div><span class="gensmall"><b>{L_TOPIC_TAGS}</b>:&nbsp;{TOPIC_TAGS}</span></div><br /><!-- ENDIF -->

<div class="index">
	<div class="bottom">
		<div class="bottom-left"><a href="{U_POST_REPLY_TOPIC}" class="nav">{L_POST_REPLY_TOPIC}</a> {LOFI_QUICK_REPLY}</div>
		<span class="navigation">{S_WATCH_TOPIC}</span><br />
		<span class="pagination">{PAGINATION}</span>
	</div>
	<br />
	{POLL_DISPLAY}
	{REG_DISPLAY}
	<!-- BEGIN postrow -->
	<div class="postwrapper">
		<div class="posttopbar">
			<div class="postname">{postrow.POSTER_NAME}<br /></div>
			<div class="postedit"><!-- IF IS_UPI2DB --><!-- IF postrow.UPI2DB_MARK_UNREAD --><a href="{postrow.UPI2DB_MARK_UNREAD_URL}" title="{postrow.L_UPI2DB_MARK_UNREAD}">{L_upi2db_u}</a>&nbsp;<!-- ENDIF --><!-- IF postrow.UPI2DB_MARK_POST --><a href="{postrow.UPI2DB_MARK_POST_URL}" title="{postrow.L_UPI2DB_MARK_POST}">{L_upi2db_m}</a>&nbsp;<!-- ENDIF --><!-- IF postrow.UPI2DB_UNMARK_POST --><a href="{postrow.UPI2DB_UNMARK_POST_URL}" title="{postrow.L_UPI2DB_UNMARK_POST}">{L_upi2db_p}</a>&nbsp;<!-- ENDIF --><!-- ENDIF -->{postrow.QUOTE} {postrow.EDIT} {postrow.DELETE} {postrow.IP}</div>
			<div class="postinfo">{postrow.POSTER_POSTS} {postrow.POSTER_FROM}<br /></div>
			<div class="postdate">{postrow.POST_DATE}</div>
		</div>
		<a id="p{postrow.U_POST_ID}"></a>
		<span class="desc">{L_SUBJECT}: {postrow.POST_SUBJECT}</span>
		<div class="postcontent">{postrow.MESSAGE}</div>
		<br />
		<!-- IF postrow.EDITED_MESSAGE --><span class="signature">{postrow.EDITED_MESSAGE}</span><!-- ENDIF -->
		{postrow.ATTACHMENTS}
		<div class="posttopbar">
			<span class="desc">{postrow.PROFILE} {postrow.PM} {postrow.EMAIL} {postrow.WWW} {postrow.AIM} {postrow.YIM} {postrow.MSN} {postrow.SKYPE} {postrow.ICQ}</span>
		</div>
	</div>
	<!-- END postrow -->

	{LOFI_QUICK_REPLY_FORM}
	<div class="bottom">
		<div class="bottom-left"><a href="{U_POST_REPLY_TOPIC}" class="nav">{L_POST_REPLY_TOPIC}</a> {LOFI_QUICK_REPLY}</div>
		<span class="pagination">{PAGINATION}</span>
	</div>

	<br />
	<div class="bottom">
		<div class="bottom-left">{PAGE_NUMBER}<br />{S_TOPIC_ADMIN}<br /><br />{JUMPBOX}</div>
		{S_AUTH_LIST}
	</div>
</div>
<br />

<!-- INCLUDE ../common/lofi/lofi_footer.tpl -->