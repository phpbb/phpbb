<?php
/***************************************************************************
 *                                posting.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

// TODO for 2.2:
// 
// * topic review additions -> quoting from previous posts ?
// * check for reply since started posting upon submission and display of 'between-posts' to allow re-defining of post
// * hidden form element containing sid to prevent remote posting - Edwin van Vliet
// * Attachments
// * bbcode parsing -> see functions_posting.php
// * lock topic option within posting
// * multichoice polls
// * permission defined ability for user to add poll options
// * Spellcheck? aspell? or some such?
// * Posting approval

// Temp Function - strtolower (will have a look at iconv later) - borrowed from php.net
function phpbb_strtolower($string)
{
	$new_string = '';

	for ($i = 0; $i < strlen($string); $i++) 
	{
		// Not sure about the offset, where is my ASCII Table ???
		if (ord(substr($string, $i, 1)) > 0xa0) 
		{
			$new_string .= strtolower(substr($string, $i, 2));
			$i++;
		} 
		else 
		{
			$new_string .= strtolower(substr($string, $i, 1));
		}
	}

	return $new_string;
}

define('IN_PHPBB', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_posting.'.$phpEx);
include($phpbb_root_path . 'includes/message_parser.'.$phpEx);

// Start session management
$user->start();
$user->setup();
$auth->acl($user->data);

// Grab only parameters needed here
$mode = (!empty($_REQUEST['mode'])) ? strval($_REQUEST['mode']) : '';
$post_id = (!empty($_REQUEST['p'])) ? intval($_REQUEST['p']) : false;
$topic_id = (!empty($_REQUEST['t'])) ? intval($_REQUEST['t']) : false;
$forum_id = (!empty($_REQUEST['f'])) ? intval($_REQUEST['f']) : false;
$lastclick = (isset($_POST['lastclick'])) ? intval($_POST['lastclick']) : 0;

$submit = (isset($_POST['post'])) ? true : false;
$preview = (isset($_POST['preview'])) ? true : false;
$save = (isset($_POST['save'])) ? true : false;
$cancel = (isset($_POST['cancel'])) ? true : false;
$confirm = (isset($_POST['confirm'])) ? true : false;
$delete = (isset($_POST['delete'])) ? true : false;

if (($delete) && (!$preview) && ($submit))
{
	$mode = 'delete';
}

// Was cancel pressed? If so then redirect to the appropriate page
if ( ($cancel) || ((time() - $lastclick) < 2) )
{
	$redirect = ($post_id) ? "viewtopic.$phpEx$SID&p=" . $post_id . "#" . $post_id : (($topic_id) ? "viewtopic.$phpEx$SID&t=" . $topic_id : (($forum_id) ? "viewforum.$phpEx$SID&f=" . $forum_id : "index.$phpEx$SID"));
	redirect($redirect);
}

// What is all this following SQL for? Well, we need to know
// some basic information in all cases before we do anything.
$forum_validate = false;
$topic_validate = false;
$post_validate = false;

$forum_fields = array('f.forum_id', 'f.forum_name', 'f.parent_id', 'f.forum_parents', 'f.forum_status', 'f.forum_postable', 'f.enable_icons', 'f.enable_post_count', 'f.enable_moderate');
$topic_fields = array('t.topic_id', 't.topic_status', 't.topic_first_post_id', 't.topic_last_post_id', 't.topic_type', 't.topic_title', 't.poll_last_vote', 't.poll_start', 't.poll_title', 't.poll_length');
$post_fields = array('p.post_id', 'p.post_time', 'p.poster_id', 'p.post_username', 'p.post_text', 'p.post_subject', 'p.post_checksum', 'p.bbcode_uid', 'p.enable_magic_url', 'p.enable_sig', 'p.enable_smilies', 'p.enable_bbcode');

switch ($mode)
{
	case 'post':
		if (!$forum_id)
		{
			trigger_error($user->lang['NO_FORUM']);
		}

		$sql = "SELECT " . implode(',', $forum_fields) . "
			FROM " . FORUMS_TABLE . " f
			WHERE forum_id = " . $forum_id;

		$forum_validate = true;
		break;

	case 'reply':
		if (!$topic_id)
		{
			trigger_error($user->lang['NO_TOPIC']);
		}

		$sql = "SELECT " . implode(',', $topic_fields) . ", " . implode(',', $forum_fields) . "
		FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f
			WHERE t.topic_id = " . $topic_id . "
				AND f.forum_id = t.forum_id";

		$forum_validate = true;
		$topic_validate = true;
		break;
		
	case 'quote':
	case 'edit':
	case 'delete':
		if (!$post_id)
		{
			trigger_error($user->lang['NO_POST']);
		}

		$sql = "SELECT " . implode(',', $post_fields) . ", " . implode(',', $topic_fields) . ", " . implode(',', $forum_fields) . ", u.username
			FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f, " . USERS_TABLE . " u
			WHERE p.post_id = " . $post_id . "
				AND t.topic_id = p.topic_id
				AND u.user_id = p.poster_id
				AND f.forum_id = t.forum_id";
		$forum_validate = true;
		$topic_validate = true;
		$post_validate = true;
		break;

	case 'topicreview':
		if (!$topic_id)
		{
			trigger_error($user->lang['NO_TOPIC']);
		}

		topic_review($topic_id, false);
		break;

	case 'smilies':
		generate_smilies('window');
		break;

	default:
		trigger_error($user->lang['NO_MODE']);
}

if ($sql != '')
{
	$result = $db->sql_query($sql);

	// This will overwrite parameter passed id's
	extract($db->sql_fetchrow($result));
	$db->sql_freeresult($result);

	$forum_id = intval($forum_id);
	$parent_id = ($forum_validate) ? intval($parent_id) : false;
	$forum_parents = ($forum_validate) ? trim($forum_parents) : '';
	$forum_name = ($forum_validate) ? trim($forum_name) : '';
	$forum_status = ($forum_validate) ? intval($forum_status) : false;
	$forum_postable = ($forum_validate) ? intval($forum_postable) : false;
	$enable_post_count = ($forum_validate) ? intval($enable_post_count) : false;
	$enable_moderate = ($forum_validate) ? intval($enable_moderate) : false;
	$enable_icons = ($forum_validate) ? intval($enable_icons) : false;

	$topic_id = intval($topic_id);
	$topic_status = ($topic_validate) ? intval($topic_status) : false;
	$topic_first_post_id = ($topic_validate) ? intval($topic_first_post_id) : false;
	$topic_last_post_id = ($topic_validate) ? intval($topic_last_post_id) : false;
	$topic_type = ($topic_validate) ? intval($topic_type) : false;
	$topic_title = ($topic_validate) ? trim($topic_title) : '';
	$poll_last_vote = ($topic_validate) ? intval($poll_last_vote) : false;
	$poll_start = ($topic_validate) ? intval($poll_start) : false;
	$poll_title = ($topic_validate) ? trim($poll_title) : false;
	$poll_length = ($topic_validate) ? (intval($poll_length)/3600) : false;
	$poll_options = array();

	// Get Poll Data
	if ($poll_start)
	{
		$sql = "SELECT poll_option_text 
		FROM " . POLL_OPTIONS_TABLE . "
		WHERE topic_id = " . $topic_id . "
		ORDER BY poll_option_id";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$poll_options[] = trim($row['poll_option_text']);
		}
		$db->sql_freeresult($result);
	}

	$post_id = intval($post_id);
	$post_time = ($post_validate) ? intval($post_time) : false;
	$poster_id = ($post_validate) ? intval($poster_id) : false;
	
	if (($poster_id == ANONYMOUS) || (!$poster_id))
	{
		$username = ($post_validate) ? trim($post_username) : '';
	}
	else
	{
		$username = ($post_validate) ? trim($username) : '';
	}

	$post_text = ($post_validate) ? trim($post_text) : '';
	$post_checksum = ($post_validate) ? trim($post_checksum) : '';
	$post_subject = ($post_validate) ? trim($post_subject) : $topic_title;
	$bbcode_uid = ($post_validate) ? trim($bbcode_uid) : '';
	$enable_urls = ($post_validate) ? intval($enable_magic_url) : true;
	$enable_sig = ($post_validate) ? intval($enable_sig) : ((intval($config['allow_sig']) && $user->data['user_attachsig']) ? true : false);
	$enable_smilies = ($post_validate) ? intval($enable_smilies) : ((intval($config['allow_smilies']) && $user->data['user_allowsmile']) ? true : false);
	$enable_bbcode = ($post_validate) ? intval($enable_bbcode) : ((intval($config['allow_bbcode']) && $user->data['user_allowbbcode']) ? true : false);
	$enable_magic_url = false;
}

// Notify user checkbox
if ($mode != 'post' && $user->data['user_id'] != ANONYMOUS)
{
	$sql = "SELECT topic_id
	FROM " . TOPICS_WATCH_TABLE . "
	WHERE topic_id = " . $topic_id . "
	AND user_id = " . $user->data['user_id'];
	$result = $db->sql_query($sql);

	$notify_set = ($db->sql_fetchrow($result)) ? true : false;
	$db->sql_freeresult($result);
}

// Collect general Permissions to be used within the complete page
$perm = array(
	'm_lock' => $auth->acl_gets('m_lock', 'a_', $forum_id),
	'm_edit' => $auth->acl_gets('m_edit', 'a_', $forum_id),
	'm_delete' => $auth->acl_gets('m_delete', 'a_', $forum_id),

	'u_delete' => $auth->acl_get('f_delete', $forum_id),

	'f_news' => $auth->acl_gets('f_news', 'm_', 'a_', $forum_id),
	'f_announce' => $auth->acl_gets('f_announce', 'm_', 'a_', $forum_id),
	'f_sticky' => $auth->acl_gets('f_sticky', 'm_', 'a_', $forum_id),
	'f_ignoreflood' => $auth->acl_gets('f_ignoreflood', 'm_', 'a_', $forum_id),
	'f_sigs' => $auth->acl_gets('f_sigs', 'm_', 'a_', $forum_id),
	'f_save' => $auth->acl_gets('f_save', 'm_', 'a_', $forum_id)
);

// DEBUG - Show Permissions
//debug_print_permissions($perm);
// DEBUG - Show Permissions

if ( (!$auth->acl_gets('f_' . $mode, 'm_', 'a_', $forum_id)) && ($forum_postable) )
{
	trigger_error($user->lang['USER_CANNOT_' . strtoupper($mode)]);
}

// Forum/Topic locked?
if ( ($forum_status == ITEM_LOCKED || $topic_status == ITEM_LOCKED) && !$perm['m_edit'])
{
	$message = ($forum_status == ITEM_LOCKED) ? 'FORUM_LOCKED' : 'TOPIC_LOCKED';
	trigger_error($user->lang[$message]);
}

// Can we edit this post?
if ( ($mode == 'edit' || $mode == 'delete') && !empty($config['edit_time']) && $post_time < time() - intval($config['edit_time']) && !$perm['m_edit'])
{
	trigger_error($user->lang['CANNOT_EDIT_TIME']);
}

// Do we want to edit our post ?
if ( ($mode == 'edit') && (!$perm['m_edit']) && ($user->data['user_id'] != $poster_id))
{
	trigger_error($user->lang['USER_CANNOT_EDIT']);
}

$message_parser = new parse_message(0); // <- TODO: add constant (MSG_POST/MSG_PM)

// Delete triggered ?
if ( ($mode == 'delete') && ((($poster_id == $user->data['user_id']) && ($user->data['user_id'] != ANONYMOUS) && ($perm['u_delete']) && ($post_id == $topic_last_post_id)) || ($perm['m_delete'])) )
{
	// Do we need to confirm ?
	if ($confirm)
	{
		$post_data = array(
			'topic_first_post_id' => $topic_first_post_id,
			'topic_last_post_id' => $topic_last_post_id,
			'enable_post_count' => $enable_post_count,
			'user_id' => $poster_id
		);

		$msg = delete_post($mode, $post_id, $topic_id, $forum_id, $post_data);
		
		// We have a problem... 
		trigger_error($msg);
	}
	else
	{
		$s_hidden_fields = '<input type="hidden" name="p" value="' . $post_id . '" /><input type="hidden" name="mode" value="delete" />';

		$page_title = $user->lang['DELETE_MESSAGE'];
		include($phpbb_root_path . 'includes/page_header.' . $phpEx);

		$template->set_filenames(array(
			'body' => 'confirm_body.html')
		);

		$template->assign_vars(array(
			'MESSAGE_TITLE' => $user->lang['DELETE_MESSAGE'],
			'MESSAGE_TEXT' => $user->lang['CONFIRM_DELETE'],

			'L_YES' => $user->lang['YES'],
			'L_NO' => $user->lang['NO'],

			'S_CONFIRM_ACTION' => $phpbb_root_path . 'posting.' . $phpEx . $SID,
			'S_HIDDEN_FIELDS' => $s_hidden_fields)
		);
		
		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
}

if ( ($mode == 'delete') && ( ($poster_id != $user->data['user_id']) && (!$perm['u_delete'])) )
{
	trigger_error($user->lang['DELETE_OWN_POSTS']);
}

if ( ($mode == 'delete') && ( ($poster_id == $user->data['user_id']) && ($perm['u_delete'])) && ($post_id != $topic_last_post_id))
{
	trigger_error($user->lang['CANNOT_DELETE_REPLIED']);
}

if ($mode == 'delete')
{
	trigger_error('USER_CANNOT_DELETE');
}

if (($submit) || ($preview))
{
	$topic_cur_post_id	= (isset($_POST['topic_cur_post_id'])) ? intval($_POST['topic_cur_post_id']) : false;
	$subject			= (!empty($_POST['subject'])) ? trim(htmlspecialchars(strip_tags($_POST['subject']))) : '';

	if ((strcmp($subject, strtoupper($subject)) == 0) && ($subject != ''))
	{
		$subject = phpbb_strtolower($subject);
	}
	
	$message			= (!empty($_POST['message'])) ? trim($_POST['message']) : '';
	$username			= (!empty($_POST['username'])) ? trim($_POST['username']) : '';
	$topic_type			= (!empty($_POST['topic_type'])) ? intval($_POST['topic_type']) : POST_NORMAL;
	$icon_id			= (!empty($_POST['icon'])) ? intval($_POST['icon']) : 0;

	$enable_html 		= (!intval($config['allow_html'])) ? 0 : ((!empty($_POST['disable_html'])) ? 0 : 1);
	$enable_bbcode 		= (!intval($config['allow_bbcode'])) ? 0 : ((!empty($_POST['disable_bbcode'])) ? 0 : 1);
	$enable_smilies		= (!intval($config['allow_smilies'])) ? 0 : ((!empty($_POST['disable_smilies'])) ? 0 : 1);
	$enable_urls 		= (isset($_POST['disable_magic_url'])) ? 0 : 1;
	$enable_sig			= (!intval($config['allow_sig'])) ? false : ((!empty($_POST['attach_sig'])) ? true : false);
	$notify				= (!empty($_POST['notify'])) ? true : false;

	$poll_delete		= (isset($_POST['poll_delete'])) ? true : false;

	if ( ($poll_delete) && ($mode == 'edit' && !empty($poll_options) && ((empty($poll_last_vote) && $poster_id == $user->data['user_id'] && $perm['u_delete']) || $perm['m_delete'])) )
	{
		delete_poll($topic_id);

		$poll_title = '';
		$poll_length = '';
		$poll_option_text = '';
	}
	else
	{
		$poll_title			= (!empty($_POST['poll_title'])) ? trim($_POST['poll_title']) : '';
		$poll_length		= (!empty($_POST['poll_length'])) ? $_POST['poll_length'] : '';
		$poll_option_text	= (!empty($_POST['poll_option_text'])) ? $_POST['poll_option_text'] : '';
	}

	$err_msg = '';
	$current_time = time();

	// If replying/quoting and last post id has changed
	// give user option of continuing submit or return to post
	// notify and show user the post made between his request and the final submit
	if ( ($mode == 'reply' || $mode == 'quote') && ($topic_cur_post_id != $topic_last_post_id) )
	{
	
	}

	// Grab md5 'checksum' of new message
	$message_md5 = md5($message);

	// Check checksum ... don't re-parse message if the same
	if ($mode != 'edit' || $message_md5 != $post_checksum)
	{
		// Parse message
		if (($result = $message_parser->parse($message, $enable_html, $enable_bbcode, $bbcode_uid, $enable_urls, $enable_smilies)) != '')
		{
			$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $result;
		}
	}

	if (($mode != 'edit') && (!$preview))
	{
		// Flood check
		$where_sql = ($user->data['user_id'] == ANONYMOUS) ? "poster_ip = '$user->ip'" : 'poster_id = ' . $user->data['user_id'];
		$sql = "SELECT MAX(post_time) AS last_post_time
			FROM " . POSTS_TABLE . "
			WHERE $where_sql";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			if (intval($row['last_post_time']) && ($current_time - intval($row['last_post_time'])) < intval($config['flood_interval']) && !$perm['f_ignoreflood'])
			{
				$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $user->lang['FLOOD_ERROR'];
			}
		}
	}

	// Validate username
	if (($username != '' && $user->data['user_id'] == ANONYMOUS) || ($mode == 'edit' && $post_username != ''))
	{
		$userdata = new userdata();
		$username = strip_tags(htmlspecialchars($username));
		if (($result = $userdata->validate_username($username)) != false)
		{
			$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $result;
		}
	}

	// Parse subject
	if ( ($subject == '') && ($mode == 'post' || ($mode == 'edit' && $topic_first_post_id == $post_id)))
	{
		$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $user->lang['EMPTY_SUBJECT'];
	}
	
	$poll_data = array(
		'poll_title'		=> $poll_title,
		'poll_length'		=> $poll_length,
		'poll_option_text'	=> $poll_option_text,
		'poll_start'		=> $poll_start,
		'poll_last_vote'	=> $poll_last_vote,
		'enable_html'		=> $enable_html,
		'enable_bbcode'		=> $enable_bbcode,
		'bbcode_uid'		=> $bbcode_uid,
		'enable_urls'		=> $enable_urls,
		'enable_smilies'	=> $enable_smilies
	);

	$poll = array();
	if (($result = $message_parser->parse_poll($poll, $poll_data)) != '')
	{
		$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $result;
	}

	$poll_options = $poll['poll_options'];
	$poll_title = $poll['poll_title'];

	// Check topic type
	if ($topic_type != POST_NORMAL)
	{
		$auth_option = '';
		switch ($topic_type)
		{
			case POST_NEWS:
				$auth_option = 'news';
				break;
			case POST_ANNOUNCE:
				$auth_option = 'announce';
				break;
			case POST_STICKY:
				$auth_option = 'sticky';
				break;
		}

		if (!$perm['f_' . $auth_option])
		{
			$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $user->lang['CANNOT_POST_' . strtoupper($auth_option)];
		}
	}

	// Store message, sync counters
	if (($err_msg == '') && ($submit))
	{
		$post_data = array(
			'topic_first_post_id'	=> $topic_first_post_id,
			'post_id'				=> $post_id,
			'topic_id'				=> $topic_id,
			'forum_id'				=> $forum_id,
			'enable_moderate'		=> $enable_moderate,
			'icon_id'				=> $icon_id,
			'poster_id'				=> $poster_id,
			'enable_sig'			=> $enable_sig,
			'enable_bbcode'			=> $enable_bbcode,
			'enable_html' 			=> $enable_html,
			'enable_smilies'		=> $enable_smilies,
			'enable_urls'			=> $enable_urls,
			'enable_post_count'		=> $enable_post_count,
			'message_md5'			=> $message_md5,
			'post_checksum'			=> $post_checksum,
			'forum_parents'			=> $forum_parents,
			'notify'				=> $notify,
			'notify_set'			=> $notify_set
		);
		
		submit_post($mode, $message, $subject, $username, $topic_type, $bbcode_uid, $poll, $post_data);
	}	

	$post_text = stripslashes($message);
	$post_subject = $topic_title = stripslashes($subject);
}

if ($err_msg)
{
	$preview = false;
}

if ($preview)
{
	if (empty($censors))
	{
		$censors = array();
		obtain_word_list($censors);
	}

	$post_time = $current_time;
	$preview_message = format_display(stripslashes($message), $enable_html, $enable_bbcode, $bbcode_uid, $enable_urls, $enable_smilies, $enable_sig);

	$preview_subject = (sizeof($censors)) ? preg_replace($censors['match'], $censors['replace'], $subject) : $subject;

	// Poll Preview
	if ( ( ($mode == 'post') || ( ($mode == 'edit') && ($post_id == $topic_first_post_id) && (empty($poll_last_vote)) )) && ( ($auth->acl_get('f_poll', $forum_id)) || ($auth->acl_gets('m_edit', 'a_', $forum_id)) ))
	{
		decode_text($poll_title);
		$preview_poll_title = format_display(stripslashes($poll_title), $enable_html, $enable_bbcode, $bbcode_uid, $enable_urls, $enable_smilies, false, false);

		$template->assign_vars(array(
			'S_HAS_POLL_OPTIONS' => (sizeof($poll_options)) ? true : false,
			'POLL_QUESTION' => $preview_poll_title)
		);

		foreach ($poll_options as $option)
		{
			$template->assign_block_vars('poll_option', array(
				'POLL_OPTION_CAPTION' => format_display(stripslashes($option), $enable_html, $enable_bbcode, $bbcode_uid, $enable_urls, $enable_smilies, false, false))
			);
		}
	}
}

// Decode text for message display
decode_text($post_text);
decode_text($subject);

// Save us some processing time. ;)
$poll_options_tmp = implode("\n", $poll_options);
decode_text($poll_options_tmp);
$poll_options = explode("\n", $poll_options_tmp);

if (($mode == 'quote') && (!$preview))
{
	quote_text($post_text, $username);
}

if ( (($mode == 'reply') || ($mode == 'quote')) && (!$preview) )
{
	$post_subject = ( ( !preg_match('/^Re:/', $post_subject) ) ? 'Re: ' : '' ) . $post_subject;
}

// MAIN POSTING PAGE BEGINS HERE

// Forum moderators?
get_moderators($moderators, $forum_id);

// Generate smilies and topic icon listings
generate_smilies('inline');

// Generate Topic icons
$s_topic_icons = generate_topic_icons($mode, $enable_icons);

// Topic type selection ... only for first post in topic.
$topic_type_toggle = '';
if ( ($mode == 'post') || (($mode == 'edit') && ($post_id == $topic_first_post_id)) )
{
	$topic_types = array(
		'sticky' => array('const' => POST_STICKY, 'lang' => 'POST_STICKY'),
		'announce' => array('const' => POST_ANNOUNCE, 'lang' => 'POST_ANNOUNCEMENT')
//		'global_announce' => array('const' => POST_GLOBAL_ANNOUNCE, 'lang' => 'POST_GLOBAL_ANNOUNCE')
	);
	
	@reset($topic_types);
	while (list($auth_key, $topic_value) = each($topic_types))
	{
		if ($perm['f_' . $auth_key])
		{
			$topic_type_toggle .= '<input type="radio" name="topic_type" value="' . $topic_value['const'] . '"';
			if ($topic_type == $topic_value['const'])
			{
				$topic_type_toggle .= ' checked="checked"';
			}
			$topic_type_toggle .= ' /> ' . $user->lang[$topic_value['lang']] . '&nbsp;&nbsp;';
		}
	}

	if ($topic_type_toggle != '')
	{
		$topic_type_toggle = (($mode == 'edit') ? $user->lang['CHANGE_TOPIC_TO'] : $user->lang['POST_TOPIC_AS']) . ': <input type="radio" name="topic_type" value="' . POST_NORMAL . '"' . (($topic_type == POST_NORMAL) ? ' checked="checked"' : '') . ' /> ' . $user->lang['POST_NORMAL'] . '&nbsp;&nbsp;' . $topic_type_toggle;
	}
}

// HTML, BBCode, Smilies, Images and Flash status
$html_status = (intval($config['allow_html']) && $auth->acl_get('f_html', $forum_id)) ? true : false;
$bbcode_status = (intval($config['allow_bbcode']) && $auth->acl_get('f_bbcode', $forum_id)) ? true : false;
$smilies_status = (intval($config['allow_smilies']) && $auth->acl_get('f_smilies', $forum_id)) ? true : false;
$img_status = (intval($config['allow_img']) && $auth->acl_get('f_img', $forum_id)) ? true : false;
$flash_status = (intval($config['allow_flash']) && $auth->acl_get('f_flash', $forum_id)) ? true : false;

$html_checked = (isset($enable_html)) ? !$enable_html : ((intval($config['allow_html'])) ? !$user->data['user_allowhtml'] : 1);
$bbcode_checked = (isset($enable_bbcode)) ? !$enable_bbcode : ((intval($config['allow_bbcode'])) ? !$user->data['user_allowbbcode'] : 1);
$smilies_checked = (isset($enable_smilies)) ? !$enable_smilies : ((intval($config['allow_smilies'])) ? !$user->data['user_allowsmile'] : 1);
$urls_checked = (isset($enable_urls)) ? !$enable_urls : 0;
$sig_checked = $enable_sig;
$notify_checked = (isset($notify_set)) ? $notify_set : (($user->data['user_id'] != ANONYMOUS) ? $user->data['user_notify'] : 0);
$lock_topic_checked = (isset($topic_lock)) ? $topic_lock : (($topic_status == ITEM_LOCKED) ? 1 : 0);

// Page title & action URL, include session_id for security purpose
$s_action = "posting.$phpEx?sid=" . $user->session_id . "&amp;mode=$mode&amp;f=" . $forum_id;
$s_action .= ($topic_id) ? '&amp;t=' . $topic_id : '';
$s_action .= ($post_id) ? '&amp;p=' . $post_id : '';

switch ($mode)
{
	case 'post':
		$page_title = $user->lang['POST_TOPIC'];
		break;

	case 'quote':
	case 'reply':
		$page_title = $user->lang['POST_REPLY'];
		break;

	case 'delete':
	case 'edit':
		$page_title = $user->lang['EDIT_POST'];
}

// Build navigation links
$forum_data = array(
	'parent_id' => $parent_id,
	'forum_parents' => $forum_parents,
	'forum_name' => $forum_name,
	'forum_id' => $forum_id,
	'forum_desc' => ''
);
generate_forum_nav($forum_data);

$s_hidden_fields = ($mode == 'reply' || $mode == 'quote') ? '<input type="hidden" name="topic_cur_post_id" value="' . $topic_last_post_id . '" />' : '';
$s_hidden_fields .= '<input type="hidden" name="lastclick" value="' . time() . '" />';


// Start assigning vars for main posting page ...
$template->assign_vars(array(
	'L_POST_A'				=> $page_title,
	'L_ICON'				=> ($mode == 'reply' || $mode == 'quote') ? $user->lang['POST_ICON'] : $user->lang['TOPIC_ICON'], 
	'L_MESSAGE_BODY_EXPLAIN'=> (intval($config['max_post_chars'])) ? sprintf($user->lang['MESSAGE_BODY_EXPLAIN'], intval($config['max_post_chars'])) : '',

	'FORUM_NAME' 			=> $forum_name,
	'FORUM_DESC'			=> (!empty($forum_desc)) ? strip_tags($forum_desc) : '',
	'TOPIC_TITLE' 			=> $topic_title,
	'MODERATORS' 			=> (sizeof($moderators)) ? implode(', ', $moderators[$forum_id]) : $user->lang['NONE'],
	'USERNAME'				=> (((!$preview) && ($mode != 'quote')) || ($preview)) ? stripslashes($username) : '',
	'SUBJECT'				=> $post_subject,
	'PREVIEW_SUBJECT'		=> ($preview) ? $preview_subject : '',
	'MESSAGE'				=> trim($post_text),
	'PREVIEW_MESSAGE'		=> ($preview) ? $preview_message : '',
	'HTML_STATUS'			=> ($html_status) ? $user->lang['HTML_IS_ON'] : $user->lang['HTML_IS_OFF'],
	'BBCODE_STATUS'			=> ($bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . "faq.$phpEx$SID&amp;mode=bbcode" . '" target="_phpbbcode">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . "faq.$phpEx$SID&amp;mode=bbcode" . '" target="_phpbbcode">', '</a>'),
	'IMG_STATUS'			=> ($img_status) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
	'FLASH_STATUS'			=> ($flash_status) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
	'SMILIES_STATUS'		=> ($smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
	'MINI_POST_IMG'			=> $user->img('goto_post', $user->lang['POST']),
	'POST_DATE'				=> ($post_time) ? $user->format_date($post_time) : '',
	'ERROR_MESSAGE'			=> $err_msg,

	'U_VIEW_FORUM' 			=> "viewforum.$phpEx$SID&amp;f=" . $forum_id,
	'U_VIEWTOPIC' 			=> ($mode != 'post') ? "viewtopic.$phpEx$SID&amp;" . $forum_id . "&amp;t=" . $topic_id : '',
	'U_REVIEW_TOPIC'		=> ($mode != 'post') ? "posting.$phpEx$SID&amp;mode=topicreview&amp;f=" . $forum_id . "&amp;t=" . $topic_id : '',

	'S_DISPLAY_PREVIEW'		=> ($preview),
	'S_DISPLAY_REVIEW'		=> ($mode == 'reply' || $mode == 'quote') ? true : false,
	'S_DISPLAY_USERNAME'	=> ($user->data['user_id'] == ANONYMOUS || ($mode == 'edit' && $post_username)) ? true : false,
	'S_SHOW_TOPIC_ICONS'	=> $s_topic_icons,
	'S_DELETE_ALLOWED' 		=> ($mode == 'edit' && ( ($post_id == $topic_last_post_id && $poster_id == $user->data['user_id'] && $perm['u_delete']) || ($perm['m_delete']))) ? true : false,
	'S_HTML_ALLOWED'		=> $html_status,
	'S_HTML_CHECKED' 		=> ($html_checked) ? 'checked="checked"' : '',
	'S_BBCODE_ALLOWED'		=> $bbcode_status,
	'S_BBCODE_CHECKED' 		=> ($bbcode_checked) ? 'checked="checked"' : '',
	'S_SMILIES_ALLOWED'		=> $smilies_status,
	'S_SMILIES_CHECKED' 	=> ($smilies_checked) ? 'checked="checked"' : '',
	'S_SIG_ALLOWED'			=> ( ($perm['f_sigs']) && ($config['allow_sig']) ) ? true : false,
	'S_SIGNATURE_CHECKED' 	=> ($sig_checked) ? 'checked="checked"' : '',
	'S_NOTIFY_ALLOWED'		=> ($user->data['user_id'] != ANONYMOUS) ? true : false,
	'S_NOTIFY_CHECKED' 		=> ($notify_checked) ? 'checked="checked"' : '',
	'S_LOCK_TOPIC_ALLOWED'	=> ( ($mode == 'edit' || $mode == 'reply' || $mode == 'quote') && ($perm['m_lock']) ) ? true : false,
	'S_LOCK_TOPIC_CHECKED'	=> ($lock_topic_checked) ? 'checked="checked"' : '',
	'S_MAGIC_URL_CHECKED' 	=> ($urls_checked) ? 'checked="checked"' : '',
	'S_TYPE_TOGGLE'			=> $topic_type_toggle,
	'S_SAVE_ALLOWED'		=> ($perm['f_save']) ? true : false,
	
	'S_POST_ACTION' 		=> $s_action,
	'S_HIDDEN_FIELDS'		=> $s_hidden_fields)
);

// Poll entry
if ( ( ($mode == 'post') || ( ($mode == 'edit') && ($post_id == $topic_first_post_id) && (empty($poll_last_vote)) )) && ( ($auth->acl_get('f_poll', $forum_id)) || ($auth->acl_gets('m_edit', 'a_', $forum_id)) ))
{
	$template->assign_vars(array(
		'S_SHOW_POLL_BOX' 	=> true,
		'S_POLL_DELETE' 	=> ($mode == 'edit' && !empty($poll_options) && ((empty($poll_last_vote) && $poster_id == $user->data['user_id'] && $perm['u_delete']) || $perm['m_delete'])) ? true : false,

		'L_POLL_OPTIONS_EXPLAIN'=> sprintf($user->lang['POLL_OPTIONS_EXPLAIN'], $config['max_poll_options']),

		'POLL_TITLE' 	=> $poll_title,
		'POLL_OPTIONS'	=> (!empty($poll_options)) ? implode("\n", $poll_options) : '',
		'POLL_LENGTH' 	=> $poll_length)
	);
}

// Output page ...
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => 'posting_body.html')
);

make_jumpbox('viewforum.'.$phpEx);

// Topic review
if ($mode == 'reply' || $mode == 'quote')
{
	topic_review($topic_id, true);
}

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

function debug_print_permissions($perm)
{
	global $forum_id;

	@reset($perm);
	echo '<span class="gensmall">Permission Settings -> Forum ID ' . $forum_id . ': <br />';

	while (list($perm_key, $authed) = each($perm))
	{
		echo $perm_key . ' -> ' . (($authed) ? 'yes' : 'no') . '<br />';
	}

	echo '</span>';
}

?>