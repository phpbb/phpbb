<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
include($phpbb_root_path . 'includes/functions_posting.'.$phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.'.$phpEx);


// Start session management
$user->session_begin();
$auth->acl($user->data);


// Grab only parameters needed here
$post_id	= request_var('p', 0);
$topic_id	= request_var('t', 0);
$forum_id	= request_var('f', 0);
$draft_id	= request_var('d', 0);
$lastclick	= request_var('lastclick', 0);

$submit		= (isset($_POST['post']));
$preview	= (isset($_POST['preview']));
$save		= (isset($_POST['save']));
$load		= (isset($_POST['load']));
$cancel		= (isset($_POST['cancel']));
$confirm	= (isset($_POST['confirm']));
$delete		= (isset($_POST['delete']));

$refresh	= isset($_POST['add_file']) || isset($_POST['delete_file']) || isset($_POST['edit_comment']) || isset($_POST['cancel_unglobalise']) || $save || $load;

$mode		= ($delete && !$preview && !$refresh && $submit) ? 'delete' : request_var('mode', '');

$error = array();
$current_time = time();


// Was cancel pressed? If so then redirect to the appropriate page
if ($cancel || ($current_time - $lastclick < 2 && $submit))
{
	$redirect = ($post_id) ? "viewtopic.$phpEx$SID&p=$post_id#$post_id" : (($topic_id) ? "viewtopic.$phpEx$SID&t=$topic_id" : (($forum_id) ? "viewforum.$phpEx$SID&f=$forum_id" : "index.$phpEx$SID"));
	redirect($redirect);
}

if (in_array($mode, array('post', 'reply', 'quote', 'edit', 'delete', 'popup')) && !$forum_id)
{
	trigger_error('NO_FORUM');
}

// What is all this following SQL for? Well, we need to know
// some basic information in all cases before we do anything.
switch ($mode)
{
	case 'post':
		$sql = 'SELECT *
			FROM ' . FORUMS_TABLE . "
			WHERE forum_id = $forum_id";
		break;

	case 'bump':
	case 'reply':
		if (!$topic_id)
		{
			trigger_error('NO_TOPIC');
		}

		$sql = 'SELECT f.*, t.*
			FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
			WHERE t.topic_id = $topic_id
				AND (f.forum_id = t.forum_id
					OR f.forum_id = $forum_id)";
		break;

	case 'quote':
	case 'edit':
	case 'delete':
		if (!$post_id)
		{
			trigger_error('NO_POST');
		}

		$sql = 'SELECT f.*, t.*, p.*, u.username, u.user_sig, u.user_sig_bbcode_uid, u.user_sig_bbcode_bitfield
			FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f, ' . USERS_TABLE . " u
			WHERE p.post_id = $post_id
				AND t.topic_id = p.topic_id
				AND u.user_id = p.poster_id
				AND (f.forum_id = t.forum_id
					OR f.forum_id = $forum_id)";
		break;

	case 'smilies':
		$sql = '';
		generate_smilies('window', $forum_id);
		break;

	case 'popup':
		$sql = 'SELECT forum_style
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . $forum_id;
		break;

	default:
		$sql = '';
		trigger_error('NO_POST_MODE');
}

if ($sql)
{
	$result = $db->sql_query($sql);

	extract($db->sql_fetchrow($result));
	$db->sql_freeresult($result);

	if ($mode == 'popup')
	{
		upload_popup($forum_style);
		exit;
	}

	$quote_username = (isset($username)) ? $username : ((isset($post_username)) ? $post_username : '');

	$forum_id	= (int) $forum_id;
	$topic_id	= (int) $topic_id;
	$post_id	= (int) $post_id;

	// Global Topic? - Adjust forum id
	if (!$forum_id && $topic_type == POST_GLOBAL)
	{
		$forum_id = request_var('f', 0);
	}
	
	$post_edit_locked = (isset($post_edit_locked)) ? (int) $post_edit_locked : 0;

	$user->setup(array('posting', 'mcp', 'viewtopic'), $forum_style);

	if ($forum_password)
	{
		$forum_info = array(
			'forum_id'		=> $forum_id,
			'forum_password'=> $forum_password
		);

		login_forum_box($forum_info);
		unset($forum_info);
	}

	$post_subject = (in_array($mode, array('quote', 'edit', 'delete'))) ? $post_subject : ((isset($topic_title)) ? $topic_title : '');

	$topic_time_limit = (isset($topic_time_limit)) ? (($topic_time_limit) ? (int) $topic_time_limit / 86400 : (int) $topic_time_limit) : 0;

	$poll_length = (isset($poll_length)) ? (($poll_length) ? (int) $poll_length / 86400 : (int) $poll_length) : 0;
	$poll_start = (isset($poll_start)) ? (int) $poll_start : 0;
	$poll_options = array();
	
	if (!isset($icon_id) || in_array($mode, array('quote', 'reply')))
	{
		$icon_id = 0;
	}

	// Get Poll Data
	if ($poll_start)
	{
		$sql = 'SELECT poll_option_text
			FROM ' . POLL_OPTIONS_TABLE . "
			WHERE topic_id = $topic_id
			ORDER BY poll_option_id";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$poll_options[] = trim($row['poll_option_text']);
		}
		$db->sql_freeresult($result);
	}

	$orig_poll_options_size = sizeof($poll_options);
	$message_parser = new parse_message();

	if (isset($post_text))
	{
		$message_parser->message = $post_text;
		unset($post_text);
	}

	$message_parser->get_submitted_attachment_data();

	// Set uninitialized variables
	$uninit = array('post_attachment' => 0, 'poster_id' => 0, 'enable_magic_url' => 0, 'topic_status' => 0, 'topic_type' => POST_NORMAL, 'subject' => '', 'topic_title' => '', 'post_time' => 0, 'post_edit_reason' => '');
	foreach ($uninit as $var_name => $default_value)
	{
		if (!isset($$var_name))
		{
			$$var_name = $default_value;
		}
	}
	unset($uninit, $var_name, $default_value);

	if ($post_attachment && !$submit && !$refresh && !$preview && $mode == 'edit')
	{
		$sql = 'SELECT attach_id, physical_filename, comment, real_filename, extension, mimetype, filesize, filetime, thumbnail
			FROM ' . ATTACHMENTS_TABLE . "
			WHERE post_msg_id = $post_id
				AND in_message = 0
			ORDER BY filetime " . ((!$config['display_order']) ? 'DESC' : 'ASC');
		$result = $db->sql_query($sql);

		$message_parser->attachment_data = array_merge($message_parser->attachment_data, $db->sql_fetchrowset($result));

		$db->sql_freeresult($result);
	}

	if ($poster_id == ANONYMOUS || !$poster_id)
	{
		$username = (in_array($mode, array('quote', 'edit', 'delete'))) ? trim($post_username) : '';
	}
	else
	{
		$username = (in_array($mode, array('quote', 'edit', 'delete'))) ? trim($username) : '';
	}

	$enable_urls = $enable_magic_url;
	
	$enable_html = (isset($enable_html)) ? $enable_html : $config['allow_html'];

	if (!in_array($mode, array('quote', 'edit', 'delete')))
	{
		$enable_sig		= ($config['allow_sig'] && $user->optionget('attachsig'));
		$enable_smilies	= ($config['allow_smilies'] && $user->optionget('smilies'));
		$enable_bbcode	= ($config['allow_bbcode'] && $user->optionget('bbcode'));
		$enable_urls	= true;
	}

	$enable_magic_url = $drafts = false;

	// User own some drafts?
	if ($user->data['is_registered'] && $auth->acl_get('u_savedrafts') && $mode != 'delete')
	{
		$sql = 'SELECT draft_id
			FROM ' . DRAFTS_TABLE . '
			WHERE (forum_id = ' . $forum_id . (($topic_id) ? " OR topic_id = $topic_id" : '') . ')
				AND user_id = ' . $user->data['user_id'] .
				(($draft_id) ? " AND draft_id <> $draft_id" : '');
		$result = $db->sql_query_limit($sql, 1);

		if ($db->sql_fetchrow($result))
		{
			$drafts = true;
		}
		$db->sql_freeresult($result);
	}

	$check_value = (($enable_html+1) << 16) + (($enable_bbcode+1) << 8) + (($enable_smilies+1) << 4) + (($enable_urls+1) << 2) + (($enable_sig+1) << 1);
}

// Notify user checkbox
if ($mode != 'post' && $user->data['is_registered'])
{
	$sql = 'SELECT topic_id
		FROM ' . TOPICS_WATCH_TABLE . '
		WHERE topic_id = ' . $topic_id . '
			AND user_id = ' . $user->data['user_id'];
	$result = $db->sql_query_limit($sql, 1);
	$notify_set = ($db->sql_fetchrow($result)) ? 1 : 0;
	$db->sql_freeresult($result);
}
else
{
	$notify_set = 0;
}

if (!$auth->acl_get('f_' . $mode, $forum_id) && $forum_type == FORUM_POST)
{
	if ($user->data['is_registered'])
	{
		trigger_error('USER_CANNOT_' . strtoupper($mode));
	}

	login_box('', $user->lang['LOGIN_EXPLAIN_' . strtoupper($mode)]);
}


// Forum/Topic locked?
if (($forum_status == ITEM_LOCKED || $topic_status == ITEM_LOCKED) && !$auth->acl_get('m_edit', $forum_id))
{
	$message = ($forum_status == ITEM_LOCKED) ? 'FORUM_LOCKED' : 'TOPIC_LOCKED';
	trigger_error($message);
}

// Can we edit this post ... if we're a moderator with rights then always yes
// else it depends on editing times, lock status and if we're the correct user
// !$preview && !$refresh && !$submit &&
if ($mode == 'edit' && !$preview && !$refresh && !$submit && !$auth->acl_get('m_edit', $forum_id))
{
	if ($user->data['user_id'] != $poster_id)
	{
		trigger_error('USER_CANNOT_EDIT');
	}

	if (!($post_time > time() - $config['edit_time'] || !$config['edit_time']))
	{
		trigger_error('CANNOT_EDIT_TIME');
	}

	if ($post_edit_locked)
	{
		trigger_error('CANNOT_EDIT_POST_LOCKED');
	}
}

// Do we want to edit our post ?

if ($mode == 'edit')
{
	$message_parser->bbcode_uid = $bbcode_uid;
}


// Delete triggered ?
if ($mode == 'delete' && (($poster_id == $user->data['user_id'] && $user->data['is_registered'] && $auth->acl_get('f_delete', $forum_id) && $post_id == $topic_last_post_id) || $auth->acl_get('m_delete', $forum_id)))
{
	$s_hidden_fields = build_hidden_fields(array(
		'p'		=> $post_id,
		'f'		=> $forum_id,
		'mode'	=> 'delete')
	);

	if (confirm_box(true))
	{
		$data = array(
			'topic_first_post_id'	=> $topic_first_post_id,
			'topic_last_post_id'	=> $topic_last_post_id,
			'topic_approved'		=> $topic_approved,
			'topic_type'			=> $topic_type,
			'post_approved'			=> $post_approved,
			'post_time'				=> $post_time,
			'poster_id'				=> $poster_id
		);

		$next_post_id = delete_post($mode, $post_id, $topic_id, $forum_id, $data);

		if ($topic_first_post_id == $topic_last_post_id)
		{
			add_log('mod', $forum_id, $topic_id, 'LOG_DELETE_TOPIC', $topic_title);

			$meta_info = "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=$forum_id";
			$message = $user->lang['POST_DELETED'];
		}
		else
		{
			add_log('mod', $forum_id, $topic_id, 'LOG_DELETE_POST', $post_subject);

			$meta_info = "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;p=$next_post_id#$next_post_id";
			$message = $user->lang['POST_DELETED'] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], "<a href=\"{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;p=$next_post_id#$next_post_id\">", '</a>');
		}

		meta_refresh(3, $meta_info);
		$message .= '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], "<a href=\"{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=$forum_id\">", '</a>');
		trigger_error($message);
	}
	else
	{
		confirm_box(false, 'DELETE_MESSAGE', $s_hidden_fields);
	}
}


if ($mode == 'delete' && $poster_id != $user->data['user_id'] && !$auth->acl_get('f_delete', $forum_id))
{
	trigger_error('DELETE_OWN_POSTS');
}

if ($mode == 'delete' && $poster_id == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id) && $post_id != $topic_last_post_id)
{
	trigger_error('CANNOT_DELETE_REPLIED');
}

if ($mode == 'delete')
{
	trigger_error('USER_CANNOT_DELETE');
}


// HTML, BBCode, Smilies, Images and Flash status
$html_status	= ($config['allow_html'] && $auth->acl_get('f_html', $forum_id));
$bbcode_status	= ($config['allow_bbcode'] && $auth->acl_get('f_bbcode', $forum_id));
$smilies_status	= ($config['allow_smilies'] && $auth->acl_get('f_smilies', $forum_id));
$img_status		= ($auth->acl_get('f_img', $forum_id));
$flash_status	= ($auth->acl_get('f_flash', $forum_id));
$quote_status	= ($auth->acl_get('f_quote', $forum_id));

// Bump Topic
if ($mode == 'bump' && ($bump_time = bump_topic_allowed($forum_id, $topic_bumped, $topic_last_post_time, $topic_poster, $topic_last_poster_id)))
{
	$db->sql_transaction();

	$db->sql_query('UPDATE ' . POSTS_TABLE . "
		SET post_time = $current_time
		WHERE post_id = $topic_last_post_id
			AND topic_id = $topic_id");

	$db->sql_query('UPDATE ' . TOPICS_TABLE . "
		SET topic_last_post_time = $current_time,
			topic_bumped = 1,
			topic_bumper = " . $user->data['user_id'] . "
		WHERE topic_id = $topic_id");

	$db->sql_query('UPDATE ' . FORUMS_TABLE . '
		SET ' . implode(', ', update_last_post_information('forum', $forum_id)) . "
		WHERE forum_id = $forum_id");

	$db->sql_query('UPDATE ' . USERS_TABLE . "
		SET user_lastpost_time = $current_time
		WHERE user_id = " . $user->data['user_id']);

	$db->sql_transaction('commit');

	markread('post', $forum_id, $topic_id, $current_time);

	add_log('mod', $forum_id, $topic_id, sprintf($user->lang['LOGM_BUMP'], $topic_title));

	meta_refresh(3, "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;p=$topic_last_post_id#$topic_last_post_id");

	$message = $user->lang['TOPIC_BUMPED'] . '<br /><br />' . sprintf($user->lang['VIEW_MESSAGE'], '<a href="viewtopic.' . $phpEx . $SID . "&amp;f=$forum_id&amp;t=$topic_id&amp;p=$topic_last_post_id#$topic_last_post_id\">", '</a>') . '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="viewforum.' . $phpEx . $SID .'&amp;f=' . $forum_id . '">', '</a>');

	trigger_error($message);
}
else if ($mode == 'bump')
{
	trigger_error('BUMP_ERROR');
}

// Save Draft
if ($save && $user->data['is_registered'] && $auth->acl_get('u_savedrafts'))
{
	$subject = request_var('subject', '', true);
	$subject = (!$subject && $mode != 'post') ? $topic_title : $subject;
	$message = request_var('message', '', true);

	if ($subject && $message)
	{
		$sql = 'INSERT INTO ' . DRAFTS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'user_id'	=> $user->data['user_id'],
			'topic_id'	=> $topic_id,
			'forum_id'	=> $forum_id,
			'save_time'	=> $current_time,
			'draft_subject' => $subject,
			'draft_message' => $message));
		$db->sql_query($sql);

		$meta_info = ($mode == 'post') ? "viewforum.$phpEx$SID&amp;f=$forum_id" : "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id";

		meta_refresh(3, $meta_info);

		$message = $user->lang['DRAFT_SAVED'] . '<br /><br />';
		$message .= ($mode != 'post') ? sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $meta_info . '">', '</a>') . '<br /><br />' : '';
		$message .= sprintf($user->lang['RETURN_FORUM'], '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_id . '">', '</a>');

		trigger_error($message);
	}

	unset($subject);
	unset($message);
}

// Load Draft
if ($draft_id && $user->data['is_registered'] && $auth->acl_get('u_savedrafts'))
{
	$sql = 'SELECT draft_subject, draft_message
		FROM ' . DRAFTS_TABLE . "
		WHERE draft_id = $draft_id
			AND user_id = " . $user->data['user_id'];
	$result = $db->sql_query_limit($sql, 1);

	if ($row = $db->sql_fetchrow($result))
	{
		$_REQUEST['subject'] = strtr($row['draft_subject'], array_flip(get_html_translation_table(HTML_ENTITIES)));
		$_POST['message'] = strtr($row['draft_message'], array_flip(get_html_translation_table(HTML_ENTITIES)));
		$refresh = true;
		$template->assign_var('S_DRAFT_LOADED', true);
	}
	else
	{
		$draft_id = 0;
	}
}

// Load Drafts
if ($load && $drafts)
{
	load_drafts($topic_id, $forum_id);
}

if ($submit || $preview || $refresh)
{
	$topic_cur_post_id	= request_var('topic_cur_post_id', 0);
	$subject = request_var('subject', '', true);

	if (strcmp($subject, strtoupper($subject)) == 0 && $subject)
	{
		$subject = strtolower($subject);
	}

	$message_parser->message = request_var('message', '', true);

	$username			= (isset($_POST['username'])) ? request_var('username', '') : $username;
	$post_edit_reason	= (isset($_POST['edit_reason']) && !empty($_POST['edit_reason']) && $mode == 'edit' && $user->data['user_id'] != $poster_id) ? request_var('edit_reason', '') : '';

	$topic_type			= (isset($_POST['topic_type'])) ? (int) $_POST['topic_type'] : (($mode != 'post') ? $topic_type : POST_NORMAL);
	$topic_time_limit	= (isset($_POST['topic_time_limit'])) ? (int) $_POST['topic_time_limit'] : (($mode != 'post') ? $topic_time_limit : 0);
	$icon_id			= request_var('icon', 0);

	$enable_html 		= (!$html_status || isset($_POST['disable_html'])) ? false : true;
	$enable_bbcode 		= (!$bbcode_status || isset($_POST['disable_bbcode'])) ? false : true;
	$enable_smilies		= (!$smilies_status || isset($_POST['disable_smilies'])) ? false : true;
	$enable_urls 		= (isset($_POST['disable_magic_url'])) ? 0 : 1;
	$enable_sig			= (!$config['allow_sig']) ? false : ((isset($_POST['attach_sig']) && $user->data['is_registered']) ? true : false);

	$notify				= (isset($_POST['notify']));
	$topic_lock			= (isset($_POST['lock_topic']));
	$post_lock			= (isset($_POST['lock_post']));

	$poll_delete		= (isset($_POST['poll_delete']));

	if ($submit)
	{
		$status_switch	= (($enable_html+1) << 16) + (($enable_bbcode+1) << 8) + (($enable_smilies+1) << 4) + (($enable_urls+1) << 2) + (($enable_sig+1) << 1);
		$status_switch = ($status_switch != $check_value);
	}
	else
	{
		$status_switch = 1;
	}

	// Delete Poll
	if ($poll_delete && $mode == 'edit' && $poll_options && 
		((!$poll_last_vote && $poster_id == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id)) || $auth->acl_get('m_delete', $forum_id)))
	{
		switch (SQL_LAYER)
		{
			case 'mysql4':
			case 'mysqli':
				$sql = 'DELETE FROM ' . POLL_OPTIONS_TABLE . ', ' . POLL_VOTES_TABLE . "
					WHERE topic_id = $topic_id";
				$db->sql_query($sql);
				break;

			default:
				$sql = 'DELETE FROM ' . POLL_OPTIONS_TABLE . "
					WHERE topic_id = $topic_id";
				$db->sql_query($sql);

				$sql = 'DELETE FROM ' . POLL_VOTES_TABLE . "
					WHERE topic_id = $topic_id";
				$db->sql_query($sql);
		}
		
		$topic_sql = array(
			'poll_title'		=> '',
			'poll_start' 		=> 0,
			'poll_length'		=> 0,
			'poll_last_vote'	=> 0,
			'poll_max_options'	=> 0,
			'poll_vote_change'	=> 0
		);

		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $topic_sql) . "
			WHERE topic_id = $topic_id";
		$db->sql_query($sql);

		$poll_title = $poll_option_text = '';
		$poll_vote_change = $poll_max_options = $poll_length = 0;
	}
	else
	{
		$poll_title			= request_var('poll_title', '');
		$poll_length		= request_var('poll_length', 0);
		$poll_option_text	= request_var('poll_option_text', '');
		$poll_max_options	= request_var('poll_max_options', 1);
		$poll_vote_change	= ($auth->acl_get('f_votechg', $forum_id) && isset($_POST['poll_vote_change'])) ? 1 : 0;
	}

	// If replying/quoting and last post id has changed
	// give user option to continue submit or return to post
	// notify and show user the post made between his request and the final submit
	if (($mode == 'reply' || $mode == 'quote') && $topic_cur_post_id && $topic_cur_post_id != $topic_last_post_id)
	{
		if (topic_review($topic_id, $forum_id, 'post_review', $topic_cur_post_id))
		{
			$template->assign_var('S_POST_REVIEW', true);
		}
		$submit = false;
		$refresh = true;
	}

	// Parse Attachments - before checksum is calculated
	$message_parser->parse_attachments('fileupload', $mode, $forum_id, $submit, $preview, $refresh);

	// Grab md5 'checksum' of new message
	$message_md5 = md5($message_parser->message);

	// Check checksum ... don't re-parse message if the same
	$update_message = ($mode != 'edit' || $message_md5 != $post_checksum || $status_switch) ? true : false;
	
	// Parse message
	if ($update_message)
	{
		$message_parser->parse($enable_html, $enable_bbcode, $enable_urls, $enable_smilies, $img_status, $flash_status, $quote_status);
	}
	else
	{
		$message_parser->bbcode_bitfield = $bbcode_bitfield;
	}

	if ($mode != 'edit' && !$preview && !$refresh && $config['flood_interval'] && !$auth->acl_get('f_ignoreflood', $forum_id))
	{
		// Flood check
		$last_post_time = 0;

		if ($user->data['is_registered'])
		{
			$last_post_time = $user->data['user_lastpost_time'];
		}
		else
		{
			$sql = 'SELECT post_time AS last_post_time
				FROM ' . POSTS_TABLE . "
				WHERE poster_ip = '" . $user->ip . "'
					AND post_time > " . ($current_time - $config['flood_interval']);
			$result = $db->sql_query_limit($sql, 1);
			if ($row = $db->sql_fetchrow($result))
			{
				$last_post_time = $row['last_post_time'];
			}
			$db->sql_freeresult($result);
		}

		if ($last_post_time)
		{
			if ($last_post_time && ($current_time - $last_post_time) < intval($config['flood_interval']))
			{
				$error[] = $user->lang['FLOOD_ERROR'];
			}
		}
	}

	// Validate username
	if (($username && !$user->data['is_registered']) || ($mode == 'edit' && $post_username))
	{
		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

		if (($result = validate_username(($mode == 'edit' && $post_username) ? $post_username : $username)) != false)
		{
			$error[] = $result;
		}
	}

	// Parse subject
	if (!$subject && ($mode == 'post' || ($mode == 'edit' && $topic_first_post_id == $post_id)))
	{
		$error[] = $user->lang['EMPTY_SUBJECT'];
	}

	$poll_last_vote = (isset($poll_last_vote)) ? $poll_last_vote : 0;

	if ($poll_option_text && 
		($mode == 'post' || ($mode == 'edit' && $post_id == $topic_first_post_id && (!$poll_last_vote || $auth->acl_get('m_edit', $forum_id))))
		&& $auth->acl_get('f_poll', $forum_id))
	{
		$poll = array(
			'poll_title'		=> $poll_title,
			'poll_length'		=> $poll_length,
			'poll_max_options'	=> $poll_max_options,
			'poll_option_text'	=> $poll_option_text,
			'poll_start'		=> $poll_start,
			'poll_last_vote'	=> $poll_last_vote,
			'poll_vote_change'	=> $poll_vote_change,
			'enable_html'		=> $enable_html,
			'enable_bbcode'		=> $enable_bbcode,
			'enable_urls'		=> $enable_urls,
			'enable_smilies'	=> $enable_smilies,
			'img_status'		=> $img_status
		);

		$message_parser->parse_poll($poll);
	
		$poll_options = isset($poll['poll_options']) ? $poll['poll_options'] : '';
		$poll_title = isset($poll['poll_title']) ? $poll['poll_title'] : '';

		if ($poll_last_vote && ($poll['poll_options_size'] < $orig_poll_options_size))
		{
			$message_parser->warn_msg[] = $user->lang['NO_DELETE_POLL_OPTIONS'];
		}
	}
	else
	{
		$poll = array();
	}

	// Check topic type
	if ($topic_type != POST_NORMAL && ($mode == 'post' || ($mode == 'edit' && $topic_first_post_id == $post_id)))
	{
		switch ($topic_type)
		{
			case POST_GLOBAL:
			case POST_ANNOUNCE:
				$auth_option = 'f_announce';
				break;
			case POST_STICKY:
				$auth_option = 'f_sticky';
				break;
			default:
				$auth_option = '';
		}

		if (!$auth->acl_get($auth_option, $forum_id))
		{
			$error[] = $user->lang['CANNOT_POST_' . str_replace('F_', '', strtoupper($auth_option))];
		}
	}

	if (sizeof($message_parser->warn_msg))
	{
		$error[] = implode('<br />', $message_parser->warn_msg);
	}

	// Store message, sync counters
	if (!sizeof($error) && $submit)
	{
		// Check if we want to de-globalize the topic... and ask for new forum
		if ($topic_type != POST_GLOBAL)
		{
			$sql = 'SELECT topic_type, forum_id
				FROM ' . TOPICS_TABLE . "
				WHERE topic_id = $topic_id";
			$result = $db->sql_query_limit($sql, 1);

			$row = $db->sql_fetchrow($result);

			if ($row && !$row['forum_id'] && $row['topic_type'] == POST_GLOBAL)
			{
				$to_forum_id = request_var('to_forum_id', 0);

				if (!$to_forum_id)
				{
					$template->assign_vars(array(
						'S_FORUM_SELECT'	=> make_forum_select(false, false, false, true, true),
						'S_UNGLOBALISE'		=> true)
					);

					$submit = false;
					$refresh = true;
				}
				else
				{
					$forum_id = $to_forum_id;
				}
			}
		}

		if ($submit)
		{
			// Lock/Unlock Topic
			$change_topic_status = $topic_status;
			$perm_lock_unlock = ($auth->acl_get('m_lock', $forum_id) || ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && $user->data['user_id'] == $topic_poster));

			if ($topic_status == ITEM_LOCKED && !$topic_lock && $perm_lock_unlock)
			{
				$change_topic_status = ITEM_UNLOCKED;
			}
			else if ($topic_status == ITEM_UNLOCKED && $topic_lock && $perm_lock_unlock)
			{
				$change_topic_status = ITEM_LOCKED;
			}

			if ($change_topic_status != $topic_status)
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . "
					SET topic_status = $change_topic_status
					WHERE topic_id = $topic_id
						AND topic_moved_id = 0";
				$db->sql_query($sql);

				$user_lock = ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && $user->data['user_id'] == $topic_poster) ? 'USER_' : '';

				add_log('mod', $forum_id, $topic_id, 'LOG_' . $user_lock . (($change_topic_status == ITEM_LOCKED) ? 'LOCK' : 'UNLOCK'), $topic_title);
			}

			// Lock/Unlock Post Edit
			if ($mode == 'edit' && $post_edit_locked == ITEM_LOCKED && !$post_lock && $auth->acl_get('m_edit', $forum_id))
			{
				$post_edit_locked = ITEM_UNLOCKED;
			}
			else if ($mode == 'edit' && $post_edit_locked == ITEM_UNLOCKED && $post_lock && $auth->acl_get('m_edit', $forum_id))
			{
				$post_edit_locked = ITEM_LOCKED;
			}

			$post_data = array(
				'topic_title'			=> (!$topic_title) ? $subject : $topic_title,
				'topic_first_post_id'	=> (isset($topic_first_post_id)) ? (int) $topic_first_post_id : 0,
				'topic_last_post_id'	=> (isset($topic_last_post_id)) ? (int) $topic_last_post_id : 0,
				'topic_time_limit'		=> (int) $topic_time_limit,
				'post_id'				=> (int) $post_id,
				'topic_id'				=> (int) $topic_id,
				'forum_id'				=> (int) $forum_id,
				'icon_id'				=> (int) $icon_id,
				'poster_id'				=> (int) $poster_id,
				'enable_sig'			=> (bool) $enable_sig,
				'enable_bbcode'			=> (bool) $enable_bbcode,
				'enable_html' 			=> (bool) $enable_html,
				'enable_smilies'		=> (bool) $enable_smilies,
				'enable_urls'			=> (bool) $enable_urls,
				'enable_indexing'		=> (bool) $enable_indexing,
				'message_md5'			=> (string) $message_md5,
				'post_time'				=> (isset($post_time)) ? (int) $post_time : $current_time,
				'post_checksum'			=> (isset($post_checksum)) ? (string) $post_checksum : '',
				'post_edit_reason'		=> $post_edit_reason,
				'post_edit_user'		=> ($mode == 'edit') ? $user->data['user_id'] : ((isset($post_edit_user)) ? (int) $post_edit_user : 0),
				'forum_parents'			=> $forum_parents,
				'forum_name'			=> $forum_name,
				'notify'				=> $notify,
				'notify_set'			=> $notify_set,
				'poster_ip'				=> (isset($poster_ip)) ? (int) $poster_ip : $user->ip,
				'post_edit_locked'		=> (int) $post_edit_locked,
				'bbcode_bitfield'		=> (int) $message_parser->bbcode_bitfield,
				'bbcode_uid'			=> $message_parser->bbcode_uid,
				'message'				=> $message_parser->message,
				'attachment_data'		=> $message_parser->attachment_data,
				'filename_data'			=> $message_parser->filename_data
			);
			unset($message_parser);

			submit_post($mode, $subject, $username, $topic_type, $poll, $post_data, $update_message);
		}
	}

	$post_subject = stripslashes($subject);
}

// Preview
if (!sizeof($error) && $preview)
{
	$post_time = ($mode == 'edit') ? $post_time : $current_time;

	$preview_message = $message_parser->format_display($enable_html, $enable_bbcode, $enable_urls, $enable_smilies, false);

	$preview_signature = ($mode == 'edit') ? $user_sig : $user->data['user_sig'];
	$preview_signature_uid = ($mode == 'edit') ? $user_sig_bbcode_uid : $user->data['user_sig_bbcode_uid'];
	$preview_signature_bitfield = ($mode == 'edit') ? $user_sig_bbcode_bitfield : $user->data['user_sig_bbcode_bitfield'];

	// Signature
	if ($enable_sig && $config['allow_sig'] && $preview_signature && $auth->acl_get('f_sigs', $forum_id))
	{
		$parse_sig = new parse_message($preview_signature);
		$parse_sig->bbcode_uid = $preview_signature_uid;
		$parse_sig->bbcode_bitfield = $preview_signature_bitfield;

		// Not sure about parameters for bbcode/smilies/urls... in signatures
		$parse_sig->format_display($config['allow_html'], $config['allow_bbcode'], true, $config['allow_smilies']);
		$preview_signature = $parse_sig->message;
		unset($parse_sig);
	}
	else
	{
		$preview_signature = '';
	}
	
	$preview_subject = censor_text($subject);
	
	// Poll Preview
	if (($mode == 'post' || ($mode == 'edit' && $post_id == $topic_first_post_id && (!$poll_last_vote || $auth->acl_get('m_edit', $forum_id))))
	&& $auth->acl_get('f_poll', $forum_id))
	{
		$parse_poll = new parse_message($poll_title);
		$parse_poll->bbcode_uid = $message_parser->bbcode_uid;
		$parse_poll->bbcode_bitfield = $message_parser->bbcode_bitfield;

		$parse_poll->format_display($enable_html, $enable_bbcode, $enable_urls, $enable_smilies);
		
		$template->assign_vars(array(
			'S_HAS_POLL_OPTIONS'=> (sizeof($poll_options)),
			'S_IS_MULTI_CHOICE'	=> ($poll_max_options > 1) ? true : false,

			'POLL_QUESTION'		=> $parse_poll->message,
			
			'L_POLL_LENGTH'		=> ($poll_length) ? sprintf($user->lang['POLL_RUN_TILL'], $user->format_date($poll_length + $poll_start)) : '',
			'L_MAX_VOTES'		=> ($poll_max_options == 1) ? $user->lang['MAX_OPTION_SELECT'] : sprintf($user->lang['MAX_OPTIONS_SELECT'], $poll_max_options))
		);

		$parse_poll->message = implode("\n", $poll_options);
		$parse_poll->format_display($enable_html, $enable_bbcode, $enable_urls, $enable_smilies);
		$preview_poll_options = explode('<br />', $parse_poll->message);
		unset($parse_poll);

		foreach ($preview_poll_options as $option)
		{
			$template->assign_block_vars('poll_option', array('POLL_OPTION_CAPTION' => $option));
		}
		unset($preview_poll_options);
	}

	// Attachment Preview
	if (sizeof($message_parser->attachment_data))
	{
		$extensions = $update_count = array();

		$template->assign_var('S_HAS_ATTACHMENTS', true);

		$attachment_data = $message_parser->attachment_data;
		$unset_attachments = parse_inline_attachments($preview_message, $attachment_data, $update_count, $forum_id, true);

		foreach ($unset_attachments as $index)
		{
			unset($attachment_data[$index]);
		}

		foreach ($attachment_data as $i => $attachment)
		{
			$template->assign_block_vars('attachment', array(
				'DISPLAY_ATTACHMENT'	=> $attachment)
			);
		}
		unset($attachment_data, $attachment);
	}

	if (!sizeof($error))
	{
		$template->assign_vars(array(
			'PREVIEW_SUBJECT'		=> $preview_subject,
			'PREVIEW_MESSAGE'		=> $preview_message,
			'PREVIEW_SIGNATURE'		=> $preview_signature,

			'S_DISPLAY_PREVIEW'		=> true)
		);
	}

	unset($post_text);
}

// Decode text for message display
$bbcode_uid = ($mode == 'quote' && !$preview && !$refresh && !sizeof($error)) ? $bbcode_uid : $message_parser->bbcode_uid;
$message_parser->decode_message($bbcode_uid);

if ($mode == 'quote' && !$preview && !$refresh)
{
	$message_parser->message = '[quote="' . $quote_username . '"]' . censor_text(trim($message_parser->message)) . "[/quote]\n";
}

if (($mode == 'reply' || $mode == 'quote') && !$preview && !$refresh)
{
	$post_subject = ((!preg_match('/^Re:/', $post_subject)) ? 'Re: ' : '') . censor_text($post_subject);
}

$attachment_data = $message_parser->attachment_data;
$filename_data = $message_parser->filename_data;
$post_text = $message_parser->message;

if (sizeof($poll_options) && $poll_title)
{
	$message_parser->message = $poll_title;
	$message_parser->bbcode_uid = $bbcode_uid;

	$message_parser->decode_message();
	$poll_title = $message_parser->message;

	$message_parser->message = implode("\n", $poll_options);
	$message_parser->decode_message();
	$poll_options = explode("\n", $message_parser->message);
}
unset($message_parser);

// MAIN POSTING PAGE BEGINS HERE

// Forum moderators?
get_moderators($moderators, $forum_id);

// Generate smiley listing
generate_smilies('inline', $forum_id);

// Generate inline attachment select box
posting_gen_inline_attachments($attachment_data);


// Do show topic type selection only in first post.
$topic_type_toggle = false;

if ($mode == 'post' || ($mode == 'edit' && $post_id == $topic_first_post_id))
{
	$topic_type_toggle = posting_gen_topic_types($forum_id, $topic_type);
}

$s_topic_icons = false;
if ($enable_icons)
{
	$s_topic_icons = posting_gen_topic_icons($mode, $icon_id);
}

$html_checked		= (isset($enable_html)) ? !$enable_html : (($config['allow_html']) ? !$user->optionget('html') : 1);
$bbcode_checked		= (isset($enable_bbcode)) ? !$enable_bbcode : (($config['allow_bbcode']) ? !$user->optionget('bbcode') : 1);
$smilies_checked	= (isset($enable_smilies)) ? !$enable_smilies : (($config['allow_smilies']) ? !$user->optionget('smilies') : 1);
$urls_checked		= (isset($enable_urls)) ? !$enable_urls : 0;
$sig_checked		= $enable_sig;
$notify_checked		= (isset($notify)) ? $notify : ((!$notify_set) ? (($user->data['is_registered']) ? $user->data['user_notify'] : 0) : 1);
$lock_topic_checked	= (isset($topic_lock)) ? $topic_lock : (($topic_status == ITEM_LOCKED) ? 1 : 0);
$lock_post_checked	= (isset($post_lock)) ? $post_lock : $post_edit_locked;

// Page title & action URL, include session_id for security purpose
$s_action = "posting.$phpEx?sid=" . $user->session_id . "&amp;mode=$mode&amp;f=$forum_id";
$s_action .= ($topic_id) ? "&amp;t=$topic_id" : '';
$s_action .= ($post_id) ? "&amp;p=$post_id" : '';

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

$forum_data = array(
	'parent_id'		=> $parent_id,
	'left_id'		=> $left_id,
	'right_id'		=> $right_id,
	'forum_parents'	=> $forum_parents,
	'forum_name'	=> $forum_name,
	'forum_id'		=> $forum_id,
	'forum_type'	=> $forum_type,
	'forum_desc'	=> $forum_desc,
	'forum_rules'	=> $forum_rules,
	'forum_rules_flags' => $forum_rules_flags,
	'forum_rules_bbcode_uid' => $forum_rules_bbcode_uid,
	'forum_rules_bbcode_bitfield' => $forum_rules_bbcode_bitfield,
	'forum_rules_link' => $forum_rules_link
);

// Build Navigation Links
generate_forum_nav($forum_data);

// Build Forum Rules
generate_forum_rules($forum_data);

$s_hidden_fields = ($mode == 'reply' || $mode == 'quote') ? '<input type="hidden" name="topic_cur_post_id" value="' . $topic_last_post_id . '" />' : '';
$s_hidden_fields .= '<input type="hidden" name="lastclick" value="' . $current_time . '" />';
$s_hidden_fields .= ($draft_id || isset($_REQUEST['draft_loaded'])) ? '<input type="hidden" name="draft_loaded" value="' . ((isset($_REQUEST['draft_loaded'])) ? intval($_REQUEST['draft_loaded']) : $draft_id) . '" />' : '';

$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off' || @ini_get('file_uploads') == '0' || !$config['allow_attachments'] || !$auth->acl_gets('f_attach', 'u_attach', $forum_id)) ? '' : ' enctype="multipart/form-data"';

// Start assigning vars for main posting page ...
$template->assign_vars(array(
	'L_POST_A'				=> $page_title,
	'L_ICON'				=> ($mode == 'reply' || $mode == 'quote') ? $user->lang['POST_ICON'] : $user->lang['TOPIC_ICON'],
	'L_MESSAGE_BODY_EXPLAIN'=> (intval($config['max_post_chars'])) ? sprintf($user->lang['MESSAGE_BODY_EXPLAIN'], intval($config['max_post_chars'])) : '',

	'FORUM_NAME' 			=> $forum_name,
	'FORUM_DESC'			=> ($forum_desc) ? strip_tags($forum_desc) : '',
	'TOPIC_TITLE' 			=> $topic_title,
	'MODERATORS' 			=> (sizeof($moderators)) ? implode(', ', $moderators[$forum_id]) : '',
	'USERNAME'				=> ((!$preview && $mode != 'quote') || $preview) ? stripslashes($username) : '',
	'SUBJECT'				=> $post_subject,
	'MESSAGE'				=> $post_text,
	'HTML_STATUS'			=> ($html_status) ? $user->lang['HTML_IS_ON'] : $user->lang['HTML_IS_OFF'],
	'BBCODE_STATUS'			=> ($bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . "faq.$phpEx$SID&amp;mode=bbcode" . '" target="_phpbbcode">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . "faq.$phpEx$SID&amp;mode=bbcode" . '" target="_phpbbcode">', '</a>'),
	'IMG_STATUS'			=> ($img_status) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
	'FLASH_STATUS'			=> ($flash_status) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
	'SMILIES_STATUS'		=> ($smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
	'MINI_POST_IMG'			=> $user->img('icon_post', $user->lang['POST']),
	'POST_DATE'				=> ($post_time) ? $user->format_date($post_time) : '',
	'ERROR'					=> (sizeof($error)) ? implode('<br />', $error) : '',
	'TOPIC_TIME_LIMIT'		=> (int) $topic_time_limit,
	'EDIT_REASON'			=> $post_edit_reason,

	'U_VIEW_FORUM' 			=> "viewforum.$phpEx$SID&amp;f=" . $forum_id,
	'U_VIEWTOPIC' 			=> ($mode != 'post') ? "viewtopic.$phpEx$SID&amp;$forum_id&amp;t=$topic_id" : '',
	'U_PROGRESS_BAR'		=> "posting.$phpEx$SID&f=$forum_id&mode=popup", // do NOT replace & with &amp; here

	'S_PRIVMSGS'			=> false,
	'S_CLOSE_PROGRESS_WINDOW'	=> isset($_POST['add_file']),
	'S_EDIT_POST'			=> ($mode == 'edit'),
	'S_EDIT_REASON'			=> ($mode == 'edit' && $user->data['user_id'] != $poster_id),
	'S_DISPLAY_USERNAME'	=> (!$user->data['is_registered'] || ($mode == 'edit' && $post_username)),
	'S_SHOW_TOPIC_ICONS'	=> $s_topic_icons,
	'S_DELETE_ALLOWED' 		=> ($mode == 'edit' && (($post_id == $topic_last_post_id && $poster_id == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id)) || $auth->acl_get('m_delete', $forum_id))),
	'S_HTML_ALLOWED'		=> $html_status,
	'S_HTML_CHECKED' 		=> ($html_checked) ? ' checked="checked"' : '',
	'S_BBCODE_ALLOWED'		=> $bbcode_status,
	'S_BBCODE_CHECKED' 		=> ($bbcode_checked) ? ' checked="checked"' : '',
	'S_SMILIES_ALLOWED'		=> $smilies_status,
	'S_SMILIES_CHECKED' 	=> ($smilies_checked) ? ' checked="checked"' : '',
	'S_SIG_ALLOWED'			=> ($auth->acl_get('f_sigs', $forum_id) && $config['allow_sig'] && $user->data['is_registered']),
	'S_SIGNATURE_CHECKED' 	=> ($sig_checked) ? ' checked="checked"' : '',
	'S_NOTIFY_ALLOWED'		=> ($user->data['is_registered']),
	'S_NOTIFY_CHECKED' 		=> ($notify_checked) ? ' checked="checked"' : '',
	'S_LOCK_TOPIC_ALLOWED'	=> (($mode == 'edit' || $mode == 'reply' || $mode == 'quote') && ($auth->acl_get('m_lock', $forum_id) || ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && $user->data['user_id'] == $topic_poster))),
	'S_LOCK_TOPIC_CHECKED'	=> ($lock_topic_checked) ? ' checked="checked"' : '',
	'S_LOCK_POST_ALLOWED'	=> ($mode == 'edit' && $auth->acl_get('m_edit', $forum_id)),
	'S_LOCK_POST_CHECKED'	=> ($lock_post_checked) ? ' checked="checked"' : '',
	'S_MAGIC_URL_CHECKED' 	=> ($urls_checked) ? ' checked="checked"' : '',
	'S_TYPE_TOGGLE'			=> $topic_type_toggle,
	'S_SAVE_ALLOWED'		=> ($auth->acl_get('u_savedrafts') && $user->data['is_registered']),
	'S_HAS_DRAFTS'			=> ($auth->acl_get('u_savedrafts') && $user->data['is_registered'] && $drafts),
	'S_FORM_ENCTYPE'		=> $form_enctype,

	'S_POST_ACTION' 		=> $s_action,
	'S_HIDDEN_FIELDS'		=> $s_hidden_fields)
);

// Poll entry
if (($mode == 'post' || ($mode == 'edit' && $post_id == $topic_first_post_id && (!$poll_last_vote || $auth->acl_get('m_edit', $forum_id))))
	&& $auth->acl_get('f_poll', $forum_id))
{
	$template->assign_vars(array(
		'S_SHOW_POLL_BOX'		=> true,
		'S_POLL_VOTE_CHANGE'	=> ($auth->acl_get('f_votechg', $forum_id)),
		'S_POLL_DELETE'			=> ($mode == 'edit' && $poll_options && ((!$poll_last_vote && $poster_id == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id)) || $auth->acl_get('m_delete', $forum_id))),

		'L_POLL_OPTIONS_EXPLAIN'=> sprintf($user->lang['POLL_OPTIONS_EXPLAIN'], $config['max_poll_options']),

		'VOTE_CHANGE_CHECKED'	=> (isset($poll_vote_change) && $poll_vote_change) ? ' checked="checked"' : '',
		'POLL_TITLE' 			=> (isset($poll_title)) ? $poll_title : '',
		'POLL_OPTIONS'			=> (isset($poll_options) && $poll_options) ? implode("\n", $poll_options) : '',
		'POLL_MAX_OPTIONS'		=> (isset($poll_max_options)) ? (int) $poll_max_options : 1,
		'POLL_LENGTH' 			=> $poll_length)
	);
}

// Attachment entry
// Not using acl_gets here, because it is using OR logic
if ($auth->acl_get('f_attach', $forum_id) && $auth->acl_get('u_attach') && $config['allow_attachments'] && $form_enctype)
{
	posting_gen_attachment_entry($attachment_data, $filename_data);
}

// Output page ...
page_header($page_title);

$template->set_filenames(array(
	'body' => 'posting_body.html')
);

make_jumpbox('viewforum.'.$phpEx);

// Topic review
if ($mode == 'reply' || $mode == 'quote')
{
	if (topic_review($topic_id, $forum_id))
	{
		$template->assign_var('S_DISPLAY_REVIEW', true);
	}
}

page_footer();


/**
* Delete Post
*/
function delete_post($mode, $post_id, $topic_id, $forum_id, &$data)
{
	global $db, $user, $config, $auth, $phpEx, $SID;

	// Specify our post mode
	$post_mode = ($data['topic_first_post_id'] == $data['topic_last_post_id']) ? 'delete_topic' : (($data['topic_first_post_id'] == $post_id) ? 'delete_first_post' : (($data['topic_last_post_id'] == $post_id) ? 'delete_last_post' : 'delete'));
	$sql_data = array();
	$next_post_id = 0;

	$db->sql_transaction();

	if (!delete_posts('post_id', array($post_id), false))
	{
		// Try to delete topic, we may had an previous error causing inconsistency
		if ($post_mode = 'delete_topic')
		{
			delete_topics('topic_id', array($topic_id), false);
		}
		trigger_error('ALREADY_DELETED');
	}

	$db->sql_transaction('commit');

	// Collect the necessary informations for updating the tables
	$sql_data[FORUMS_TABLE] = '';
	switch ($post_mode)
	{
		case 'delete_topic':
			delete_topics('topic_id', array($topic_id), false);
			set_config('num_topics', $config['num_topics'] - 1, true);

			if ($data['topic_type'] != POST_GLOBAL)
			{
				$sql_data[FORUMS_TABLE] .= 'forum_posts = forum_posts - 1, forum_topics_real = forum_topics_real - 1';
				$sql_data[FORUMS_TABLE] .= ($data['topic_approved']) ? ', forum_topics = forum_topics - 1' : '';
			}

			$sql_data[FORUMS_TABLE] .= ($sql_data[FORUMS_TABLE]) ? ', ' : '';
			$sql_data[FORUMS_TABLE] .= implode(', ', update_last_post_information('forum', $forum_id));
			$sql_data[TOPICS_TABLE] = 'topic_replies_real = topic_replies_real - 1' . (($data['post_approved']) ? ', topic_replies = topic_replies - 1' : '');
			break;

		case 'delete_first_post':
			$sql = 'SELECT p.post_id, p.poster_id, p.post_username, u.username
				FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
				WHERE p.topic_id = $topic_id
					AND p.poster_id = u.user_id
				ORDER BY p.post_time ASC";
			$result = $db->sql_query_limit($sql, 1);

			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($data['topic_type'] != POST_GLOBAL)
			{
				$sql_data[FORUMS_TABLE] = 'forum_posts = forum_posts - 1';
			}

			$sql_data[TOPICS_TABLE] = 'topic_first_post_id = ' . intval($row['post_id']) . ", topic_first_poster_name = '" . (($row['poster_id'] == ANONYMOUS) ? $db->sql_escape($row['post_username']) : $db->sql_escape($row['username'])) . "'";
			$sql_data[TOPICS_TABLE] .= ', topic_replies_real = topic_replies_real - 1' . (($data['post_approved']) ? ', topic_replies = topic_replies - 1' : '');

			$next_post_id = (int) $row['post_id'];
			break;

		case 'delete_last_post':
			if ($data['topic_type'] != POST_GLOBAL)
			{
				$sql_data[FORUMS_TABLE] = 'forum_posts = forum_posts - 1';
			}

			$sql_data[FORUMS_TABLE] .= ($sql_data[FORUMS_TABLE]) ? ', ' : '';
			$sql_data[FORUMS_TABLE] .= implode(', ', update_last_post_information('forum', $forum_id));
			$sql_data[TOPICS_TABLE] = 'topic_bumped = 0, topic_bumper = 0, topic_replies_real = topic_replies_real - 1' . (($data['post_approved']) ? ', topic_replies = topic_replies - 1' : '');

			$update = update_last_post_information('topic', $topic_id);
			if (sizeof($update))
			{
				$sql_data[TOPICS_TABLE] .= ', ' . implode(', ', $update);
				$next_post_id = (int) str_replace('topic_last_post_id = ', '', $update[0]);
			}
			else
			{
				$sql = 'SELECT MAX(post_id) as last_post_id
					FROM ' . POSTS_TABLE . "
					WHERE topic_id = $topic_id " .
						((!$auth->acl_get('m_approve')) ? 'AND post_approved = 1' : '');
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$next_post_id = (int) $row['last_post_id'];
			}
			break;

		case 'delete':
			$sql = 'SELECT post_id
				FROM ' . POSTS_TABLE . "
				WHERE topic_id = $topic_id " .
					((!$auth->acl_get('m_approve')) ? 'AND post_approved = 1' : '') . '
					AND post_time > ' . $data['post_time'] . '
				ORDER BY post_time ASC';
			$result = $db->sql_query_limit($sql, 1);

			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($data['topic_type'] != POST_GLOBAL)
			{
				$sql_data[FORUMS_TABLE] = 'forum_posts = forum_posts - 1';
			}

			$sql_data[TOPICS_TABLE] = 'topic_replies_real = topic_replies_real - 1' . (($data['post_approved']) ? ', topic_replies = topic_replies - 1' : '');
			$next_post_id = (int) $row['post_id'];
	}

	$sql_data[USERS_TABLE] = ($auth->acl_get('f_postcount', $forum_id)) ? 'user_posts = user_posts - 1' : '';
	set_config('num_posts', $config['num_posts'] - 1, true);

	$db->sql_transaction();

	$where_sql = array(FORUMS_TABLE => "forum_id = $forum_id", TOPICS_TABLE => "topic_id = $topic_id", USERS_TABLE => 'user_id = ' . $data['poster_id']);

	foreach ($sql_data as $table => $update_sql)
	{
		if ($update_sql)
		{
			$db->sql_query("UPDATE $table SET $update_sql WHERE " . $where_sql[$table]);
		}
	}

	$db->sql_transaction('commit');

	return $next_post_id;
}


/**
* Submit Post
*/
function submit_post($mode, $subject, $username, $topic_type, &$poll, &$data, $update_message = true)
{
	global $db, $auth, $user, $config, $phpEx, $SID, $template, $phpbb_root_path;

	// We do not handle erasing posts here
	if ($mode == 'delete')
	{
		return;
	}

	$current_time = time();

	if ($mode == 'post')
	{
		$post_mode = 'post';
		$update_message = true;
	}
	else if ($mode != 'edit')
	{
		$post_mode = 'reply';
		$update_message = true;
	}
	else if ($mode == 'edit')
	{
		$post_mode = ($data['topic_first_post_id'] == $data['topic_last_post_id']) ? 'edit_topic' : (($data['topic_first_post_id'] == $data['post_id']) ? 'edit_first_post' : (($data['topic_last_post_id'] == $data['post_id']) ? 'edit_last_post' : 'edit'));
	}


	// Collect some basic informations about which tables and which rows to update/insert
	$sql_data = array();
	$poster_id = ($mode == 'edit') ? $data['poster_id'] : (int) $user->data['user_id'];

	// Collect Informations
	switch ($post_mode)
	{
		case 'post':
		case 'reply':
			$sql_data[POSTS_TABLE]['sql'] = array(
				'forum_id' 			=> ($topic_type == POST_GLOBAL) ? 0 : $data['forum_id'],
				'poster_id' 		=> (int) $user->data['user_id'],
				'icon_id'			=> $data['icon_id'],
				'poster_ip' 		=> $user->ip,
				'post_time'			=> $current_time,
				'post_approved' 	=> ($auth->acl_get('f_moderate', $data['forum_id']) && !$auth->acl_get('m_approve')) ? 0 : 1,
				'enable_bbcode' 	=> $data['enable_bbcode'],
				'enable_html' 		=> $data['enable_html'],
				'enable_smilies' 	=> $data['enable_smilies'],
				'enable_magic_url' 	=> $data['enable_urls'],
				'enable_sig' 		=> $data['enable_sig'],
				'post_username'		=> (!$user->data['is_registered']) ? stripslashes($username) : '',
				'post_subject'		=> $subject,
				'post_text' 		=> $data['message'],
				'post_checksum'		=> $data['message_md5'],
				'post_encoding'		=> $user->lang['ENCODING'],
				'post_attachment'	=> (isset($data['filename_data']['physical_filename']) && sizeof($data['filename_data'])) ? 1 : 0,
				'bbcode_bitfield'	=> $data['bbcode_bitfield'],
				'bbcode_uid'		=> $data['bbcode_uid'],
				'post_edit_locked'	=> $data['post_edit_locked']
			);
			break;

		case 'edit_first_post':
		case 'edit':
			
			if (!$auth->acl_gets('m_', 'a_') || $data['post_edit_reason'])
			{
				$sql_data[POSTS_TABLE]['sql'] = array(
					'post_edit_time'	=> $current_time
				);

				$sql_data[POSTS_TABLE]['stat'][] = 'post_edit_count = post_edit_count + 1';
			}

		case 'edit_last_post':
		case 'edit_topic':

			if (($post_mode == 'edit_last_post' || $post_mode == 'edit_topic') && $data['post_edit_reason'])
			{
				$sql_data[POSTS_TABLE]['sql'] = array(
					'post_edit_time'	=> $current_time
				);

				$sql_data[POSTS_TABLE]['stat'][] = 'post_edit_count = post_edit_count + 1';
			}

			if (!isset($sql_data[POSTS_TABLE]['sql']))
			{
				$sql_data[POSTS_TABLE]['sql'] = array();
			}

			$sql_data[POSTS_TABLE]['sql'] = array_merge($sql_data[POSTS_TABLE]['sql'], array(
				'forum_id' 			=> ($topic_type == POST_GLOBAL) ? 0 : $data['forum_id'],
				'poster_id' 		=> $data['poster_id'],
				'icon_id'			=> $data['icon_id'],
				'post_approved' 	=> ($auth->acl_get('f_moderate', $data['forum_id']) && !$auth->acl_get('m_approve')) ? 0 : 1,
				'enable_bbcode' 	=> $data['enable_bbcode'],
				'enable_html' 		=> $data['enable_html'],
				'enable_smilies' 	=> $data['enable_smilies'],
				'enable_magic_url' 	=> $data['enable_urls'],
				'enable_sig' 		=> $data['enable_sig'],
				'post_username'		=> ($username && $data['poster_id'] == ANONYMOUS) ? stripslashes($username) : '',
				'post_subject'		=> $subject,
				'post_edit_reason'	=> $data['post_edit_reason'],
				'post_edit_user'	=> (int) $data['post_edit_user'],
				'post_checksum'		=> $data['message_md5'],
				'post_encoding'		=> $user->lang['ENCODING'],
				'post_attachment'	=> (isset($data['filename_data']['physical_filename']) && sizeof($data['filename_data'])) ? 1 : 0,
				'bbcode_bitfield'	=> $data['bbcode_bitfield'],
				'bbcode_uid'		=> $data['bbcode_uid'],
				'post_edit_locked'	=> $data['post_edit_locked'])
			);

			if ($update_message)
			{
				$sql_data[POSTS_TABLE]['sql']['post_text'] = $data['message'];
			}

			break;
	}

	// And the topic ladies and gentlemen
	switch ($post_mode)
	{
		case 'post':
			$sql_data[TOPICS_TABLE]['sql'] = array(
				'topic_poster'		=> (int) $user->data['user_id'],
				'topic_time'		=> $current_time,
				'forum_id' 			=> ($topic_type == POST_GLOBAL) ? 0 : $data['forum_id'],
				'icon_id'			=> $data['icon_id'],
				'topic_approved'	=> ($auth->acl_get('f_moderate', $data['forum_id']) && !$auth->acl_get('m_approve')) ? 0 : 1,
				'topic_title' 		=> $subject,
				'topic_first_poster_name' => (!$user->data['is_registered'] && $username) ? stripslashes($username) : $user->data['username'],
				'topic_type'		=> $topic_type,
				'topic_time_limit'	=> ($topic_type == POST_STICKY || $topic_type == POST_ANNOUNCE) ? ($data['topic_time_limit'] * 86400) : 0,
				'topic_attachment'	=> (isset($data['filename_data']['physical_filename']) && sizeof($data['filename_data'])) ? 1 : 0
			);

			if (isset($poll['poll_options']) && !empty($poll['poll_options']))
			{
				$sql_data[TOPICS_TABLE]['sql'] = array_merge($sql_data[TOPICS_TABLE]['sql'], array(
					'poll_title'		=> $poll['poll_title'],
					'poll_start'		=> ($poll['poll_start']) ? $poll['poll_start'] : $current_time,
					'poll_max_options'	=> $poll['poll_max_options'],
					'poll_length'		=> ($poll['poll_length'] * 86400),
					'poll_vote_change'	=> $poll['poll_vote_change'])
				);
			}

			$sql_data[USERS_TABLE]['stat'][] = "user_lastpost_time = $current_time" . (($auth->acl_get('f_postcount', $data['forum_id'])) ? ', user_posts = user_posts + 1' : '');
	
			if ($topic_type != POST_GLOBAL)
			{
				if (!$auth->acl_get('f_moderate', $data['forum_id']) || $auth->acl_get('m_approve'))
				{
					$sql_data[FORUMS_TABLE]['stat'][] = 'forum_posts = forum_posts + 1';
				}
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_topics_real = forum_topics_real + 1' . ((!$auth->acl_get('f_moderate', $data['forum_id']) || $auth->acl_get('m_approve')) ? ', forum_topics = forum_topics + 1' : '');
			}
			break;

		case 'reply':
			$sql_data[TOPICS_TABLE]['stat'][] = 'topic_replies_real = topic_replies_real + 1, topic_bumped = 0, topic_bumper = 0' . ((!$auth->acl_get('f_moderate', $data['forum_id']) || $auth->acl_get('m_approve')) ? ', topic_replies = topic_replies + 1' : '');
			$sql_data[USERS_TABLE]['stat'][] = "user_lastpost_time = $current_time" . (($auth->acl_get('f_postcount', $data['forum_id'])) ? ', user_posts = user_posts + 1' : '');

			if ((!$auth->acl_get('f_moderate', $data['forum_id']) || $auth->acl_get('m_approve')) && $topic_type != POST_GLOBAL)
			{
				$sql_data[FORUMS_TABLE]['stat'][] = 'forum_posts = forum_posts + 1';
			}
			break;

		case 'edit_topic':
		case 'edit_first_post':

			$sql_data[TOPICS_TABLE]['sql'] = array(
				'forum_id' 					=> ($topic_type == POST_GLOBAL) ? 0 : $data['forum_id'],
				'icon_id'					=> $data['icon_id'],
				'topic_approved'			=> ($auth->acl_get('f_moderate', $data['forum_id']) && !$auth->acl_get('m_approve')) ? 0 : 1,
				'topic_title' 				=> $subject,
				'topic_first_poster_name'	=> stripslashes($username),
				'topic_type'				=> $topic_type,
				'topic_time_limit'			=> ($topic_type == POST_STICKY || $topic_type == POST_ANNOUNCE) ? ($data['topic_time_limit'] * 86400) : 0,
				'poll_title'				=> ($poll['poll_options']) ? $poll['poll_title'] : '',
				'poll_start'				=> ($poll['poll_options']) ? (($poll['poll_start']) ? $poll['poll_start'] : $current_time) : 0,
				'poll_max_options'			=> ($poll['poll_options']) ? $poll['poll_max_options'] : 1,
				'poll_length'				=> ($poll['poll_options']) ? ($poll['poll_length'] * 86400) : 0,
				'poll_vote_change'			=> $poll['poll_vote_change'],

				'topic_attachment'			=> ($post_mode == 'edit_topic') ? ((isset($data['filename_data']['physical_filename']) && sizeof($data['filename_data'])) ? 1 : 0) : $data['topic_attachment']
			);
			break;
	}

	$db->sql_transaction();

	// Submit new topic
	if ($post_mode == 'post')
	{
		$sql = 'INSERT INTO ' . TOPICS_TABLE . ' ' .
			$db->sql_build_array('INSERT', $sql_data[TOPICS_TABLE]['sql']);
		$db->sql_query($sql);

		$data['topic_id'] = $db->sql_nextid();

		$sql_data[POSTS_TABLE]['sql'] = array_merge($sql_data[POSTS_TABLE]['sql'], array(
			'topic_id' => $data['topic_id'])
		);
		unset($sql_data[TOPICS_TABLE]['sql']);
	}

	// Submit new post
	if ($post_mode == 'post' || $post_mode == 'reply')
	{
		if ($post_mode == 'reply')
		{
			$sql_data[POSTS_TABLE]['sql'] = array_merge($sql_data[POSTS_TABLE]['sql'], array(
				'topic_id' => $data['topic_id'])
			);
		}

		$sql = 'INSERT INTO ' . POSTS_TABLE . ' ' .
			$db->sql_build_array('INSERT', $sql_data[POSTS_TABLE]['sql']);
		$db->sql_query($sql);
		$data['post_id'] = $db->sql_nextid();

		if ($post_mode == 'post')
		{
			$sql_data[TOPICS_TABLE]['sql'] = array(
				'topic_first_post_id'	=> $data['post_id'],
				'topic_last_post_id'	=> $data['post_id'],
				'topic_last_post_time'	=> $current_time,
				'topic_last_poster_id'	=> (int) $user->data['user_id'],
				'topic_last_poster_name'=> (!$user->data['is_registered'] && $username) ? stripslashes($username) : $user->data['username']
			);
		}

		unset($sql_data[POSTS_TABLE]['sql']);
	}

	$make_global = false;

	// Are we globalising or unglobalising?
	if ($post_mode == 'edit_first_post' || $post_mode == 'edit_topic')
	{
		$sql = 'SELECT topic_type, topic_replies_real, topic_approved
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . $data['topic_id'];
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// globalise
		if ($row['topic_type'] != POST_GLOBAL && $topic_type == POST_GLOBAL)
		{
			// Decrement topic/post count
			$make_global = true;
			$sql_data[FORUMS_TABLE]['stat'] = array();

			$sql_data[FORUMS_TABLE]['stat'][] = 'forum_posts = forum_posts - ' . ($row['topic_replies_real'] + 1);
			$sql_data[FORUMS_TABLE]['stat'][] = 'forum_topics_real = forum_topics_real - 1' . (($row['topic_approved']) ? ', forum_topics = forum_topics - 1' : '');

			// Update forum_ids for all posts
			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET forum_id = 0
				WHERE topic_id = ' . $data['topic_id'];
			$db->sql_query($sql);
		}
		// unglobalise
		else if ($row['topic_type'] == POST_GLOBAL && $topic_type != POST_GLOBAL)
		{
			// Increment topic/post count
			$make_global = true;
			$sql_data[FORUMS_TABLE]['stat'] = array();

			$sql_data[FORUMS_TABLE]['stat'][] = 'forum_posts = forum_posts + ' . ($row['topic_replies_real'] + 1);
			$sql_data[FORUMS_TABLE]['stat'][] = 'forum_topics_real = forum_topics_real + 1' . (($row['topic_approved']) ? ', forum_topics = forum_topics + 1' : '');

			// Update forum_ids for all posts
			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET forum_id = ' . $data['forum_id'] . '
				WHERE topic_id = ' . $data['topic_id'];
			$db->sql_query($sql);
		}
	}

	// Update the topics table
	if (isset($sql_data[TOPICS_TABLE]['sql']))
	{
		$db->sql_query('UPDATE ' . TOPICS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_data[TOPICS_TABLE]['sql']) . '
			WHERE topic_id = ' . $data['topic_id']);
	}

	// Update the posts table
	if (isset($sql_data[POSTS_TABLE]['sql']))
	{
		$db->sql_query('UPDATE ' . POSTS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_data[POSTS_TABLE]['sql']) . '
			WHERE post_id = ' . $data['post_id']);
	}

	// Update Poll Tables
	if (isset($poll['poll_options']) && !empty($poll['poll_options']))
	{
		$cur_poll_options = array();

		if ($poll['poll_start'] && $mode == 'edit')
		{
			$sql = 'SELECT * FROM ' . POLL_OPTIONS_TABLE . '
				WHERE topic_id = ' . $data['topic_id'] . '
				ORDER BY poll_option_id';
			$result = $db->sql_query($sql);

			while ($cur_poll_options[] = $db->sql_fetchrow($result));
			$db->sql_freeresult($result);
		}

		$sql_insert_ary = array();
		for ($i = 0, $size = sizeof($poll['poll_options']); $i < $size; $i++)
		{
			if (trim($poll['poll_options'][$i]))
			{
				if (!$cur_poll_options[$i])
				{
					$sql_insert_ary[] = array(
						'poll_option_id'	=> (int) $i,
						'topic_id'			=> (int) $data['topic_id'],
						'poll_option_text'	=> (string) $poll['poll_options'][$i]
					);
				}
				else if ($poll['poll_options'][$i] != $cur_poll_options[$i])
				{
					$sql = "UPDATE " . POLL_OPTIONS_TABLE . "
						SET poll_option_text = '" . $db->sql_escape($poll['poll_options'][$i]) . "'
						WHERE poll_option_id = " . $cur_poll_options[$i]['poll_option_id'] . "
							AND topic_id = " . $data['topic_id'];
					$db->sql_query($sql);
				}
			}
		}

		if (sizeof($sql_insert_ary))
		{
			switch (SQL_LAYER)
			{
				case 'mysql':
				case 'mysql4':
				case 'mysqli':
					$db->sql_query('INSERT INTO ' . POLL_OPTIONS_TABLE . ' ' . $db->sql_build_array('MULTI_INSERT', $sql_insert_ary));
				break;

				default:
					foreach ($sql_insert_ary as $ary)
					{
						$db->sql_query('INSERT INTO ' . PRIVMSGS_TO_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_insert_ary));
					}
				break;
			}
		}

		if (sizeof($poll['poll_options']) < sizeof($cur_poll_options))
		{
			$sql = 'DELETE FROM ' . POLL_OPTIONS_TABLE . '
				WHERE poll_option_id >= ' . sizeof($poll['poll_options']) . '
					AND topic_id = ' . $data['topic_id'];
			$db->sql_query($sql);
		}
	}

	// Submit Attachments
	if (sizeof($data['attachment_data']) && $data['post_id'] && in_array($mode, array('post', 'reply', 'quote', 'edit')))
	{
		$space_taken = $files_added = 0;

		foreach ($data['attachment_data'] as $pos => $attach_row)
		{
			if ($attach_row['attach_id'])
			{
				// update entry in db if attachment already stored in db and filespace
				$sql = 'UPDATE ' . ATTACHMENTS_TABLE . "
					SET comment = '" . $db->sql_escape($attach_row['comment']) . "'
					WHERE attach_id = " . (int) $attach_row['attach_id'];
				$db->sql_query($sql);
			}
			else
			{
				// insert attachment into db
				if (!@file_exists($phpbb_root_path . $config['upload_path'] . '/' . basename($attach_row['physical_filename'])))
				{
					continue;
				}
				
				$attach_sql = array(
					'post_msg_id'		=> $data['post_id'],
					'topic_id'			=> $data['topic_id'],
					'in_message'		=> 0,
					'poster_id'			=> $poster_id,
					'physical_filename'	=> basename($attach_row['physical_filename']),
					'real_filename'		=> basename($attach_row['real_filename']),
					'comment'			=> $attach_row['comment'],
					'extension'			=> $attach_row['extension'],
					'mimetype'			=> $attach_row['mimetype'],
					'filesize'			=> $attach_row['filesize'],
					'filetime'			=> $attach_row['filetime'],
					'thumbnail'			=> $attach_row['thumbnail']
				);

				$sql = 'INSERT INTO ' . ATTACHMENTS_TABLE . ' ' .
					$db->sql_build_array('INSERT', $attach_sql);
				$db->sql_query($sql);

				$space_taken += $attach_row['filesize'];
				$files_added++;
			}
		}

		if (sizeof($data['attachment_data']))
		{
			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_attachment = 1
				WHERE post_id = ' . $data['post_id'];
			$db->sql_query($sql);

			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_attachment = 1
				WHERE topic_id = ' . $data['topic_id'];
			$db->sql_query($sql);
		}

		set_config('upload_dir_size', $config['upload_dir_size'] + $space_taken, true);
		set_config('num_files', $config['num_files'] + $files_added, true);
	}

	$db->sql_transaction('commit');

	if ($post_mode == 'post' || $post_mode == 'reply' || $post_mode == 'edit_last_post')
	{
		if ($topic_type != POST_GLOBAL)
		{
			$sql_data[FORUMS_TABLE]['stat'][] = implode(', ', update_last_post_information('forum', $data['forum_id']));
		}

		$update = update_last_post_information('topic', $data['topic_id']);
		if (sizeof($update))
		{
			$sql_data[TOPICS_TABLE]['stat'][] = implode(', ', $update);
		}
	}

	if ($make_global)
	{
		$sql_data[FORUMS_TABLE]['stat'][] = implode(', ', update_last_post_information('forum', $data['forum_id']));
	}

	if ($post_mode == 'edit_topic')
	{
		$update = update_last_post_information('topic', $data['topic_id']);
		if (sizeof($update))
		{
			$sql_data[TOPICS_TABLE]['stat'][] = implode(', ', $update);
		}
	}

	// Update total post count, do not consider moderated posts/topics
	if (!$auth->acl_get('f_moderate', $data['forum_id']) || $auth->acl_get('m_approve'))
	{
		if ($post_mode == 'post')
		{
			set_config('num_topics', $config['num_topics'] + 1, true);
			set_config('num_posts', $config['num_posts'] + 1, true);
		}

		if ($post_mode == 'reply')
		{
			set_config('num_posts', $config['num_posts'] + 1, true);
		}
	}

	// Update forum stats
	$db->sql_transaction();

	$where_sql = array(POSTS_TABLE => 'post_id = ' . $data['post_id'], TOPICS_TABLE => 'topic_id = ' . $data['topic_id'], FORUMS_TABLE => 'forum_id = ' . $data['forum_id'], USERS_TABLE => 'user_id = ' . $user->data['user_id']);

	foreach ($sql_data as $table => $update_ary)
	{
		if (isset($update_ary['stat']) && implode('', $update_ary['stat']))
		{
			$db->sql_query("UPDATE $table SET " . implode(', ', $update_ary['stat']) . ' WHERE ' . $where_sql[$table]);
		}
	}

	// Delete topic shadows (if any exist). We do not need a shadow topic for an global announcement
	if ($make_global)
	{
		$db->sql_query('DELETE FROM ' . TOPICS_TABLE . '
			WHERE topic_moved_id = ' . $data['topic_id']);
	}

	// Fulltext parse
	if ($update_message && $data['enable_indexing'])
	{
		$search = new fulltext_search();
		$result = $search->add($mode, $data['post_id'], $data['message'], $subject);
	}

	$db->sql_transaction('commit');

	// Delete draft if post was loaded...
	$draft_id = request_var('draft_loaded', 0);
	if ($draft_id)
	{
		$db->sql_query('DELETE FROM ' . DRAFTS_TABLE . " WHERE draft_id = $draft_id AND user_id = " . $user->data['user_id']);
	}

	// Topic Notification
	if (!$data['notify_set'] && $data['notify'])
	{
		$sql = 'INSERT INTO ' . TOPICS_WATCH_TABLE . ' (user_id, topic_id)
			VALUES (' . $user->data['user_id'] . ', ' . $data['topic_id'] . ')';
		$db->sql_query($sql);
	}
	else if ($data['notify_set'] && !$data['notify'])
	{
		$sql = 'DELETE FROM ' . TOPICS_WATCH_TABLE . '
			WHERE user_id = ' . $user->data['user_id'] . '
				AND topic_id = ' . $data['topic_id'];
		$db->sql_query($sql);
	}

	if ($mode == 'post' || $mode == 'reply' || $mode == 'quote')
	{
		// Mark this topic as posted to
		markread('post', $data['forum_id'], $data['topic_id'], $data['post_time']);
	}

	// Mark this topic as read
	markread('topic', $data['forum_id'], $data['topic_id'], $data['post_time']);

	// Send Notifications
	if ($mode != 'edit' && $mode != 'delete' && (!$auth->acl_get('f_moderate', $data['forum_id']) || $auth->acl_get('m_approve')))
	{
		user_notification($mode, stripslashes($subject), stripslashes($data['topic_title']), stripslashes($data['forum_name']), $data['forum_id'], $data['topic_id'], $data['post_id']);
	}

	if ($mode == 'post')
	{
		$url = (!$auth->acl_get('f_moderate', $data['forum_id']) || $auth->acl_get('m_approve')) ? "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f=" . $data['forum_id'] . '&amp;t=' . $data['topic_id'] : "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=" . $data['forum_id'];
	}
	else
	{
		$url = (!$auth->acl_get('f_moderate', $data['forum_id']) || $auth->acl_get('m_approve')) ?  "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f={$data['forum_id']}&amp;t={$data['topic_id']}&amp;p={$data['post_id']}#{$data['post_id']}" : "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f={$data['forum_id']}&amp;t={$data['topic_id']}";
	}

	meta_refresh(3, $url);

	$message = ($auth->acl_get('f_moderate', $data['forum_id']) && !$auth->acl_get('m_approve')) ? (($mode == 'edit') ? 'POST_EDITED_MOD' : 'POST_STORED_MOD') : (($mode == 'edit') ? 'POST_EDITED' : 'POST_STORED');
	$message = $user->lang[$message] . ((!$auth->acl_get('f_moderate', $data['forum_id']) || $auth->acl_get('m_approve')) ? '<br /><br />' . sprintf($user->lang['VIEW_MESSAGE'], '<a href="' . $url . '">', '</a>') : '') . '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="viewforum.' . $phpEx . $SID .'&amp;f=' . $data['forum_id'] . '">', '</a>');
	trigger_error($message);
}

function upload_popup($forum_style)
{
	global $template, $user;

	$user->setup('posting', $forum_style);

	page_header('PROGRESS_BAR');

	$template->set_filenames(array(
		'popup'	=> 'posting_progress_bar.html')
	);

	$template->assign_vars(array(
		'PROGRESS_BAR'	=> $user->img('attach_progress_bar', $user->lang['UPLOAD_IN_PROGRESS']))
	);

	$template->display('popup');
}

?>