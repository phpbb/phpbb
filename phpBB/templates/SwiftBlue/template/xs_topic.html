<!-- INCLUDE overall_header.tpl -->

<script type="text/javascript" src="{FULL_SITE_PATH}{T_COMMON_TPL_PATH}js/bbcb_mg.js"></script>
<script type="text/javascript">
// <![CDATA[
function openAllSmiles()
{
	smiles = window.open('{U_SMILEYS_MORE}', '_xs_smileys', 'width=600,height=400,resizable=yes,scrollbars=yes');
	smiles.focus();
	return false;
}
// ]]>
</script>

<!-- BEGIN postrow -->
<?php

// check if quick reply is enabled
global $user, $config, $forum_id, $topic_id, $is_auth, $forum_topic_data, $lang, $images;

$can_reply = $user->data['session_logged_in'] ? true : false;
//$can_reply = true;

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

// Start Check Locked Status
$lock = ($forum_topic_data['topic_status'] == TOPIC_LOCKED) ? true : false;
$unlock = ($forum_topic_data['topic_status'] == TOPIC_UNLOCKED) ? true : false;
if (($lock || $unlock) && $is_auth['auth_mod'])
{
	if ($forum_topic_data['topic_status'] == TOPIC_LOCKED)
	{
		$this->assign_block_vars('switch_unlock_topic', array(
			'L_UNLOCK_TOPIC' => $lang['Unlock_topic'],
			'S_UNLOCK_CHECKED' => ($unlock) ? 'checked="checked"' : ''
			)
		);
	}
	elseif ($forum_topic_data['topic_status'] == TOPIC_UNLOCKED)
	{
		$this->assign_block_vars('switch_lock_topic', array(
			'L_LOCK_TOPIC' => $lang['Lock_topic'],
			'S_LOCK_CHECKED' => ($lock) ? 'checked="checked"' : ''
			)
		);
	}
}
// End check locked status

$prev_id = false;
$postrow_count = (isset($this->_tpldata['postrow.'])) ? count($this->_tpldata['postrow.']) : 0;
for ($postrow_i = 0; $postrow_i < $postrow_count; $postrow_i++)
{
	$postrow_item = &$this->_tpldata['postrow.'][$postrow_i];
	// set profile link and search button

	// check for new post
	$new_post = strpos($postrow_item['MINI_POST_IMG'], '_new') > 0 ? true : false;
	$postrow_item['LINK_CLASS'] = $new_post ? '-new' : '';
	// fix text
	$search = array('  ', "\t", "\n ");
	$replace = array('&nbsp;&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;', "\n&nbsp;");
	$postrow_item['MESSAGE'] = str_replace($search, $replace, $postrow_item['MESSAGE']);
	// set prev/next post links
	$next_id = $postrow_i == ($postrow_count - 1) ? false : $this->_tpldata['postrow.'][$postrow_i + 1]['U_POST_ID'];
	if(($next_id !== false) || ($prev_id !== false))
	{
		$str = '';
		if($prev_id)
		{
			$str .= '<a href="#p' . $prev_id . '"><img src="' . $images['arrow_alt_up'] . '" alt="" /></a>';
			//$str .= '<br /><img src="' . $images['spacer'] . '" width="9" height="2" alt="" />';
		}
		else
		{
			$str .= '<img src="' . $images['spacer'] . '" width="9" height="2" alt="" />';
		}
		$str .= '&nbsp;';
		if($next_id)
		{
			//$str .= '<img src="' . $images['spacer'] . '" width="9" height="2" alt="" /><br />';
			$str .= '<a href="#p' . $next_id . '"><img src="' . $images['arrow_alt_down'] . '" alt="" /></a>';
		}
		else
		{
			$str .= '<img src="' . $images['spacer'] . '" width="9" height="2" alt="" />';
		}
		$str .= '';
		$postrow_item['ARROWS'] = $str;
	}
	$prev_id = $postrow_item['U_POST_ID'];
	ob_start();
?>
{postrow.ATTACHMENTS}
<?php
$postrow_item['ATTACHMENTS'] = ob_get_contents();
ob_end_clean();
}
?>
<!-- END postrow -->
<?php
// show quick reply
if($can_reply)
{
	// quick reply button
	global $images;
	$this->vars['CA_QUICK_REPLY_BUTTON'] = '<a href="javascript:showQuickEditor();" title="' . $lang['Post_a_reply'] . '"><img src="' . $images['quick_reply'] . '" alt="' . $lang['Quick_Reply'] . '" title="' . $lang['Quick_Reply'] . '" /></a>';
	// quick reply form
	ob_start();
?>
<div id="quick_reply" style="display: none; position: relative;">
<a id="quick"></a>
<form action="<?php echo append_sid(CMS_PAGE_POSTING); ?>" method="post" name="post" style="display: inline;">
{S_HIDDEN_FIELDS}
<input type="hidden" name="post_time" value="<?php echo time(); ?>" />
{IMG_THL}{IMG_THC}<span class="forumlink"><?php echo $lang['Post_a_reply']; ?></span>{IMG_THR}<table class="forumlinenb">
<tr>
	<td class="row1 tw200px tdnw"><span class="gen"><b><?php echo $lang['Subject']; ?>:</b></span></td>
	<td class="row2 tw100pct"><input type="text" name="subject" size="45" maxlength="120" style="width: 98%;" tabindex="2" class="post" value="{L_REPLY_PREFIX}{TOPIC_TITLE_PLAIN}" /></td>
</tr>
<tr>
	<td class="row1 tw200px tdnw"><span class="gen"><b><?php echo $lang['Message_body']; ?>:<br /><img src="{T_TEMPLATE_PATH}/images/spacer.gif" width="200" height="1" alt="" /></b></span></td>
	<td class="row2 tw100pct"><div class="message-box"><textarea name="message" rows="15" cols="35" tabindex="3"></textarea></div></td>
</tr>
<tr>
	<td class="row1 tw200px tdnw"><span class="gen"><b>{L_SMILEYS}:</b></span></td>
	<td class="row2 row-center tw100pct">
		<table>
		<tr>
			<td class="tdalignc">
				<!-- BEGIN smilies -->
				<img src="{smilies.URL}" onmouseover="this.style.cursor='pointer';" onclick="emoticon('{smilies.CODE}');" alt="{smilies.DESC}" title="{smilies.DESC}" />
				<!-- END smilies -->
			</td>
			<td class="tdalignc tvalignm">&nbsp;<input type="button" class="liteoption" name="SmilesButt" value="{L_SMILEYS_MORE}" onclick="openAllSmiles();" /></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td class="row1 tdnw"><span class="gen"><b><?php echo $lang['Options']; ?>:</b></span></td>
	<td class="row2">
	<?php
		$user_sig = ($user->data['user_sig'] != '' && $config['allow_sig']) ? $user->data['user_sig'] : '';
		$html_on = $config['allow_html'] ? $user->data['user_allowhtml'] : 1;
		$bbcode_on = $config['allow_bbcode'] ? $user->data['user_allowbbcode'] : 0;
		$smilies_on = $config['allow_smilies'] ? $user->data['user_allowsmile'] : 0;
	?>
	<label><input type="checkbox" name="disable_acro_auto" />&nbsp;<span class="genmed"><?php echo $lang['Disable_ACRO_AUTO_post']; ?></span></label><br />
	<?php if($config['allow_html'] || (($user->data['user_level'] == ADMIN) && $config['allow_html_only_for_admins'])) { ?>
	<label><input type="checkbox" name="disable_html" <?php echo ($html_on ? '' : 'checked="checked"'); ?> />&nbsp;<span class="genmed"><?php echo $lang['Disable_HTML_post']; ?></span></label><br />
	<?php } else { ?><input type="hidden" name="disable_html" value="checked" /><?php } ?>
	<?php if($config['allow_bbcode']) { ?>
	<label><input type="checkbox" name="disable_bbcode" <?php echo ($bbcode_on ? '' : 'checked="checked"'); ?> />&nbsp;<span class="genmed"><?php echo $lang['Disable_BBCode_post']; ?></span></label><br />
	<?php } else { ?><input type="hidden" name="disable_bbcode" value="checked" /><?php } ?>
	<?php if($config['allow_smilies']) { ?>
	<label><input type="checkbox" name="disable_smilies" <?php echo ($smilies_on ? '' : 'checked="checked"'); ?> />&nbsp;<span class="genmed"><?php echo $lang['Disable_Smilies_post']; ?></span></label><br />
	<?php } else { ?><input type="hidden" name="disable_smilies" value="checked" /><?php } ?>
	<?php if($user_sig) { ?>
	<label><input type="checkbox" name="attach_sig" <?php echo ($user->data['user_attachsig'] ? 'checked="checked"' : ''); ?> />&nbsp;<span class="genmed"><?php echo $lang['Attach_signature']; ?></span></label><br />
	<?php } else { ?><input type="hidden" name="attach_sig" value="" /><?php } ?>
	<label><input type="checkbox" name="notify" <?php echo ($user->data['user_notify'] ? 'checked="checked"' : ''); ?> />&nbsp;<span class="genmed"><?php echo $lang['Notify']; ?></span></label><br />
	<!-- BEGIN switch_lock_topic -->
	<label><input type="checkbox" name="lock" {switch_lock_topic.S_LOCK_CHECKED} />&nbsp;<span>{switch_lock_topic.L_LOCK_TOPIC}</span></label><br />
	<!-- END switch_lock_topic -->
	<!-- BEGIN switch_unlock_topic -->
	<label><input type="checkbox" name="unlock" {switch_unlock_topic.S_UNLOCK_CHECKED} />&nbsp;<span>{switch_unlock_topic.L_UNLOCK_TOPIC}</span></label><br />
	<!-- END switch_unlock_topic -->
	</td>
</tr>
<tr>
	<td class="cat" colspan="2">
		<input type="hidden" name="mode" value="reply" />
		<input type="hidden" name="f" value="<?php echo $forum_id; ?>" />
		<input type="hidden" name="t" value="<?php echo $topic_id; ?>" />
		<input type="hidden" name="sid" value="<?php echo $user->data['session_id']; ?>" />
		<input type="submit" tabindex="5" name="preview" class="liteoption" value="<?php echo $lang['Preview']; ?>" />&nbsp;
		<input type="submit" accesskey="s" tabindex="6" name="post" class="mainoption" value="<?php echo $lang['Submit']; ?>" />
	</td>
</tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
</form>
</div>
<?php
	$str = ob_get_contents();
	ob_end_clean();
	$this->vars['CA_QUICK_REPLY_FORM'] = $str;
}
?>
<!-- IF IS_KB_MODE -->
<!-- INCLUDE viewtopic_kb_body.tpl -->
<!-- ELSE -->
<!-- INCLUDE viewtopic_body.tpl -->
<!-- ENDIF -->

<!-- INCLUDE overall_footer.tpl -->