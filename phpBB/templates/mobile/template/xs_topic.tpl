<!-- INCLUDE overall_header.tpl -->

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

<!-- IF IS_KB_MODE -->
<!-- INCLUDE viewtopic_kb_body.tpl -->
<!-- ELSE -->
<!-- INCLUDE viewtopic_body.tpl -->
<!-- ENDIF -->

<!-- INCLUDE overall_footer.tpl -->