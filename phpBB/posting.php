<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : posting.php
// STARTED   : Sat Feb 17, 2001
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
include($phpbb_root_path . 'includes/functions_posting.'.$phpEx);
include($phpbb_root_path . 'includes/message_parser.'.$phpEx);


// Start session management
$user->start();
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
if ($cancel || $current_time - $lastclick < 2)
{
	$redirect = ($post_id) ? "viewtopic.$phpEx$SID&p=$post_id#$post_id" : (($topic_id) ? "viewtopic.$phpEx$SID&t=$topic_id" : (($forum_id) ? "viewforum.$phpEx$SID&f=$forum_id" : "index.$phpEx$SID"));
	redirect($redirect);
}

if (in_array($mode, array('post', 'reply', 'quote', 'edit', 'delete')) && !$forum_id)
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

		$sql = 'SELECT t.*, f.*
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

		$sql = 'SELECT p.*, t.*, f.*, u.username, u.user_sig, u.user_sig_bbcode_uid, u.user_sig_bbcode_bitfield 
			FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f, ' . USERS_TABLE . " u
			WHERE p.post_id = $post_id
				AND t.topic_id = p.topic_id
				AND u.user_id = p.poster_id
				AND (f.forum_id = t.forum_id 
					OR f.forum_id = $forum_id)";
		break;

	case 'smilies':
		generate_smilies('window', $forum_id);
		break;

	default:
		$sql = '';
		trigger_error('NO_POST_MODE');
}

$censors = array();
obtain_word_list($censors);

if ($sql)
{
	$result = $db->sql_query($sql);

	extract($db->sql_fetchrow($result));
	$db->sql_freeresult($result);

	$quote_username = ($username) ? $username : ((isset($post_username)) ? $post_username : '');

	$forum_id	= (int) $forum_id;
	$topic_id	= (int) $topic_id;
	$post_id	= (int) $post_id;

	$post_edit_locked = (int) $post_edit_locked;

	$user->setup(false, $forum_style);

	if ($forum_password)
	{
		login_forum_box(array(
			'forum_id'		=> $forum_id, 
			'forum_password'=> $forum_password)
		);
	}

	$post_subject = (in_array($mode, array('quote', 'edit', 'delete'))) ? $post_subject : $topic_title;

	$topic_time_limit = ($topic_time_limit) ? $topic_time_limit / 86400 : $topic_time_limit;
	$poll_length = ($poll_length) ? $poll_length / 86400 : $poll_length;
	$poll_options = array();

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

	$message_parser = new parse_message(0);


	$message_parser->filename_data['filecomment'] = preg_replace('#&amp;(\#[0-9]+;)#', '&\1', request_var('filecomment', ''));
	$message_parser->filename_data['filename'] = ($_FILES['fileupload']['name'] != 'none') ? trim($_FILES['fileupload']['name']) : '';

	// Get Attachment Data
	$message_parser->attachment_data = (isset($_POST['attachment_data'])) ? $_POST['attachment_data'] : array();

	// 
	foreach ($message_parser->attachment_data as $pos => $var_ary)
	{
		prepare_data($message_parser->attachment_data[$pos]['physical_filename'], true);
		prepare_data($message_parser->attachment_data[$pos]['comment'], true);
		prepare_data($message_parser->attachment_data[$pos]['real_filename'], true);
		prepare_data($message_parser->attachment_data[$pos]['extension'], true);
		prepare_data($message_parser->attachment_data[$pos]['mimetype'], true);

		$message_parser->attachment_data[$pos]['filesize'] = (int) $message_parser->attachment_data[$pos]['filesize'];
		$message_parser->attachment_data[$pos]['filetime'] = (int) $message_parser->attachment_data[$pos]['filetime'];
		$message_parser->attachment_data[$pos]['attach_id'] = (int) $message_parser->attachment_data[$pos]['attach_id'];
		$message_parser->attachment_data[$pos]['thumbnail'] = (int) $message_parser->attachment_data[$pos]['thumbnail'];
	}

	if ($post_attachment && !$submit && !$refresh && !$preview && $mode == 'edit')
	{
		$sql = 'SELECT attach_id, physical_filename, comment, real_filename, extension, mimetype, filesize, filetime, thumbnail
			FROM ' . ATTACHMENTS_TABLE . "
			WHERE post_id = $post_id
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


	if (!in_array($mode, array('quote', 'edit', 'delete')))
	{
		$enable_sig		= ($config['allow_sig'] && $user->optionget('attachsig'));
		$enable_smilies	= ($config['allow_smilies'] && $user->optionget('smile'));
		$enable_bbcode	= ($config['allow_bbcode'] && $user->optionget('bbcode'));
		$enable_urls	= true;
	}

	$enable_magic_url = $drafts = false;

	// User own some drafts?
	if ($user->data['user_id'] != ANONYMOUS && $auth->acl_get('u_savedrafts') && $mode != 'delete')
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
	}
}

// Notify user checkbox
if ($mode != 'post' && $user->data['user_id'] != ANONYMOUS)
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
	trigger_error('USER_CANNOT_' . strtoupper($mode));
}


// Forum/Topic locked?
if (($forum_status == ITEM_LOCKED || $topic_status == ITEM_LOCKED) && !$auth->acl_get('m_edit', $forum_id))
{
	$message = ($forum_status == ITEM_LOCKED) ? 'FORUM_LOCKED' : 'TOPIC_LOCKED';
	trigger_error($message);
}

// Can we edit this post?
if (($mode == 'edit' || $mode == 'delete') && !$auth->acl_get('m_edit', $forum_id) && $config['edit_time'] && $post_time < $current_time - $config['edit_time'])
{
	trigger_error('CANNOT_EDIT_TIME');
}


// Do we want to edit our post ?
if ($mode == 'edit' && !$auth->acl_get('m_edit', $forum_id) && $user->data['user_id'] != $poster_id)
{
	trigger_error('USER_CANNOT_EDIT');
}


// Is edit posting locked ?
if ($mode == 'edit' && $post_edit_locked && !$auth->acl_get('m_', $forum_id))
{
	trigger_error('CANNOT_EDIT_POST_LOCKED');
}


if ($mode == 'edit')
{
	$message_parser->bbcode_uid = $bbcode_uid;
}


// Delete triggered ?
if ($mode == 'delete' && (($poster_id == $user->data['user_id'] && $user->data['user_id'] != ANONYMOUS && $auth->acl_get('f_delete', $forum_id) && $post_id == $topic_last_post_id) || $auth->acl_get('m_delete', $forum_id)))
{
	// Do we need to confirm ?
	if ($confirm)
	{
		$data = array(
			'topic_first_post_id' => $topic_first_post_id,
			'topic_last_post_id' => $topic_last_post_id,
			'topic_approved' => $topic_approved,
			'topic_type' => $topic_type,
			'post_approved' => $post_approved,
			'post_time' => $post_time,
			'poster_id' => $poster_id
		);
		
		$next_post_id = delete_post($mode, $post_id, $topic_id, $forum_id, $data);
	
		if ($topic_first_post_id == $topic_last_post_id)
		{
			$meta_info = "viewforum.$phpEx$SID&amp;f=$forum_id";
			$message = $user->lang['POST_DELETED'];
		}
		else
		{
			$meta_info = "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;p=$next_post_id#$next_post_id";
			$message = $user->lang['POST_DELETED'] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;p=$next_post_id#$next_post_id\">", '</a>');
		}

		meta_refresh(3, $meta_info);
		$message .= '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], "<a href=\"viewforum.$phpEx$SID&amp;f=$forum_id\">", '</a>');
		trigger_error($message);

	}
	else
	{
		$s_hidden_fields = '<input type="hidden" name="p" value="' . $post_id . '" /><input type="hidden" name="f" value="' . $forum_id . '" /><input type="hidden" name="mode" value="delete" />';

		page_header($user->lang['DELETE_MESSAGE']);

		$template->set_filenames(array(
			'body' => 'confirm_body.html')
		);

		$template->assign_vars(array(
			'MESSAGE_TITLE'		=> $user->lang['DELETE_MESSAGE'],
			'MESSAGE_TEXT'		=> $user->lang['CONFIRM_DELETE_POST'],

			'S_CONFIRM_ACTION'	=> "posting.$phpEx$SID",
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields)
		);
		
		page_footer();
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
if ($save && $user->data['user_id'] != ANONYMOUS && $auth->acl_get('u_savedrafts'))
{
	$subject = preg_replace('#&amp;(\#[0-9]+;)#', '&\1', request_var('subject', ''));
	$subject = (!$subject && $mode != 'post') ? $topic_title : $subject;
	$message = (isset($_POST['message'])) ? htmlspecialchars(trim(str_replace(array('\\\'', '\\"', '\\0', '\\\\'), array('\'', '"', '\0', '\\'), $_POST['message']))) : '';
	$message = preg_replace('#&amp;(\#[0-9]+;)#', '&\1', $message);

	if (!$subject && !$message)
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
if ($draft_id && $user->data['user_id'] != ANONYMOUS && $auth->acl_get('u_savedrafts'))
{
	$sql = 'SELECT draft_subject, draft_message 
		FROM ' . DRAFTS_TABLE . " 
		WHERE draft_id = $draft_id
			AND user_id = " . $user->data['user_id'];
	$result = $db->sql_query_limit($sql, 1);
	
	if ($row = $db->sql_fetchrow($result))
	{
		$_REQUEST['subject'] = $row['draft_subject'];
		$_POST['message'] = $row['draft_message'];
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
	$subject			= request_var('subject', '');

	if (strcmp($subject, strtoupper($subject)) == 0 && $subject)
	{
		$subject = phpbb_strtolower($subject);
	}
	$subject = preg_replace('#&amp;(\#[0-9]+;)#', '&\1', $subject);

	
	$message_parser->message = (isset($_POST['message'])) ? htmlspecialchars(trim(str_replace(array('\\\'', '\\"', '\\0', '\\\\'), array('\'', '"', '\0', '\\'), $_POST['message']))) : '';
	$message_parser->message = preg_replace('#&amp;(\#[0-9]+;)#', '&\1', $message_parser->message);

	$username			= ($_POST['username']) ? request_var('username', '') : $username;
	$post_edit_reason	= ($_POST['edit_reason'] && $mode == 'edit' && $user->data['user_id'] != $poster_id) ? request_var('edit_reason', '') : '';

	$topic_type			= (isset($_POST['topic_type'])) ? (int) $_POST['topic_type'] : (($mode != 'post') ? $topic_type : POST_NORMAL);
	$topic_time_limit	= (isset($_POST['topic_time_limit'])) ? (int) $_POST['topic_time_limit'] : (($mode != 'post') ? $topic_time_limit : 0);
	$icon_id			= request_var('icon', 0);

	$enable_html 		= (!$html_status || $_POST['disable_html']) ? false : true;
	$enable_bbcode 		= (!$bbcode_status || $_POST['disable_bbcode']) ? false : true;
	$enable_smilies		= (!$smilies_status || $_POST['disable_smilies']) ? false : true;
	$enable_urls 		= (isset($_POST['disable_magic_url'])) ? 0 : 1;
	$enable_sig			= (!$config['allow_sig']) ? false : (($_POST['attach_sig'] && $user->data['user_id'] != ANONYMOUS) ? true : false);

	$notify				= ($_POST['notify']);
	$topic_lock			= (isset($_POST['lock_topic']));
	$post_lock			= (isset($_POST['lock_post']));

	$poll_delete		= (isset($_POST['poll_delete']));
	
	// Faster than crc32
	$check_value	= (($preview || $refresh) && isset($_POST['status_switch'])) ? (int) $_POST['status_switch'] : (($enable_html+1) << 16) + (($enable_bbcode+1) << 8) + (($enable_smilies+1) << 4) + (($enable_urls+1) << 2) + (($enable_sig+1) << 1);
	$status_switch	= (isset($_POST['status_switch']) && (int) $_POST['status_switch'] != $check_value);


	if ($poll_delete && (($mode == 'edit' && $poll_options && !$poll_last_vote && $poster_id == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id)) || $auth->acl_get('m_delete', $forum_id)))
	{
		// Delete Poll
		$sql = 'DELETE FROM ' . POLL_OPTIONS_TABLE . ', ' . POLL_VOTES_TABLE . "
			WHERE topic_id = $topic_id";
		$db->sql_query($sql);

		$topic_sql = array(
			'poll_title'		=> '',
			'poll_start' 		=> 0,
			'poll_length'		=> 0,
			'poll_last_vote'	=> 0, 
			'poll_max_options'	=> 0
		);

		$sql = 'UPDATE ' . TOPICS_TABLE . ' 
			SET ' . $db->sql_build_array('UPDATE', $topic_sql) . " 
			WHERE topic_id = $topic_id";
		$db->sql_query($sql);

		$poll_title = $poll_length = $poll_option_text = $poll_max_options = '';
	}
	else
	{
		$poll_title			= request_var('poll_title', '');
		$poll_length		= request_var('poll_length', 0);
		$poll_option_text	= request_var('poll_option_text', '');
		$poll_max_options	= request_var('poll_max_options', 1);
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


	// Grab md5 'checksum' of new message
	$message_md5 = md5($message_parser->message);

	// Check checksum ... don't re-parse message if the same
	if ($mode != 'edit' || $message_md5 != $post_checksum || $status_switch || $preview)
	{
		// Parse message
		$message_parser->parse($enable_html, $enable_bbcode, $enable_urls, $enable_smilies, $img_status, $flash_status, $quote_status);
	}

	$message_parser->parse_attachments($mode, $post_id, $submit, $preview, $refresh);

	if ($mode != 'edit' && !$preview && !$refresh && $config['flood_interval'] && !$auth->acl_get('f_ignoreflood', $forum_id))
	{
		// Flood check
		$last_post_time = 0;

		if ($user->data['user_id'] != ANONYMOUS)
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
		}

		if ($last_post_time)
		{
			if ($last_post_time && ($current_time - $last_post_time) < intval($config['flood_interval']))
			{
				$error[] = $user->lang['FLOOD_ERROR'];
			}
		}
		$db->sql_freeresult($result);
	}

	// Validate username
	// TODO
	if (($username && $user->data['user_id'] == ANONYMOUS) || ($mode == 'edit' && $post_username))
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
	
	$poll_data = array(
		'poll_title'		=> $poll_title,
		'poll_length'		=> $poll_length,
		'poll_max_options'	=> $poll_max_options,
		'poll_option_text'	=> $poll_option_text,
		'poll_start'		=> $poll_start,
		'poll_last_vote'	=> $poll_last_vote,
		'enable_html'		=> $enable_html,
		'enable_bbcode'		=> $enable_bbcode,
		'bbcode_uid'		=> $message_parser->bbcode_uid,
		'enable_urls'		=> $enable_urls,
		'enable_smilies'	=> $enable_smilies
	);

	$poll = array();
	$message_parser->parse_poll($poll, $poll_data);

	$poll_options = $poll['poll_options'];
	$poll_title = $poll['poll_title'];

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
			$perm_lock_unlock = ($auth->acl_get('m_lock', $forum_id) || ($auth->acl_get('f_user_lock', $forum_id) && $user->data['user_id'] != ANONYMOUS && $user->data['user_id'] == $topic_poster));

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
			
				$user_lock = ($auth->acl_get('f_user_lock', $forum_id) && $user->data['user_id'] != ANONYMOUS && $user->data['user_id'] == $topic_poster) ? 'USER_' : '';

				add_log('mod', $forum_id, $topic_id, sprintf($user->lang['LOGM_' . $user_lock . (($change_topic_status == ITEM_LOCKED) ? 'LOCK' : 'UNLOCK')], '<a href="' . generate_board_url() . "/viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id" . '" class="gen" target="_blank">' . $topic_title . '</a>'));
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
				'topic_first_post_id'	=> (int) $topic_first_post_id,
				'topic_last_post_id'	=> (int) $topic_last_post_id,
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
				'message_md5'			=> (int) $message_md5,
				'post_checksum'			=> (int) $post_checksum,
				'post_edit_reason'		=> $post_edit_reason,
				'post_edit_user'		=> ($mode == 'edit') ? $user->data['user_id'] : $post_edit_user,
				'forum_parents'			=> $forum_parents,
				'forum_name'			=> $forum_name,
				'notify'				=> $notify,
				'notify_set'			=> $notify_set,
				'poster_ip'				=> (int) $poster_ip,
				'post_edit_locked'		=> (int) $post_edit_locked,
				'bbcode_bitfield'		=> (int) $message_parser->bbcode_bitfield
			);
			
			submit_post($mode, $message_parser->message, $subject, $username, $topic_type, $message_parser->bbcode_uid, $poll, $message_parser->attachment_data, $message_parser->filename_data, $post_data);
		}
	}	

	$post_text = $message_parser->message;
	$post_subject = stripslashes($subject);
}

// Preview
if (!sizeof($error) && $preview)
{
	$post_time = ($mode == 'edit') ? $post_time : $current_time;

	$preview_subject = (sizeof($censors['match'])) ? preg_replace($censors['match'], $censors['replace'], $subject) : $subject;

	$preview_signature = ($mode == 'edit') ? $user_sig : $user->data['user_sig'];
	$preview_signature_uid = ($mode == 'edit') ? $user_sig_bbcode_uid : $user->data['user_sig_bbcode_uid'];
	$preview_signature_bitfield = ($mode == 'edit') ? $user_sig_bbcode_bitfield : $user->data['user_sig_bbcode_bitfield'];

	include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
	$bbcode = new bbcode($message_parser->bbcode_bitfield | $preview_signature_bitfield);

	$preview_message = $message_parser->message;
	format_display($preview_message, $preview_signature, $message_parser->bbcode_uid, $preview_signature_uid, $enable_html, $enable_bbcode, $enable_urls, $enable_smilies, $enable_sig);

	// Poll Preview
	if (($mode == 'post' || ($mode == 'edit' && $post_id == $topic_first_post_id && !$poll_last_vote)) && ($auth->acl_get('f_poll', $forum_id) || $auth->acl_get('m_edit', $forum_id)))
	{
		decode_text($poll_title, $message_parser->bbcode_uid);
		$preview_poll_title = format_display($poll_title, $null, $message_parser->bbcode_uid, false, $enable_html, $enable_bbcode, $enable_urls, $enable_smilies, false, false);

		$template->assign_vars(array(
			'S_HAS_POLL_OPTIONS' => (sizeof($poll_options)),
			'POLL_QUESTION'		 => $preview_poll_title)
		);

		foreach ($poll_options as $option)
		{
			$template->assign_block_vars('poll_option', array(
				'POLL_OPTION_CAPTION'	=> format_display(stripslashes($option), $enable_html, $enable_bbcode, $message_parser->bbcode_uid, $enable_urls, $enable_smilies, false, false))
			);
		}
	}

	// Attachment Preview
	if (sizeof($message_parser->attachment_data))
	{
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		$extensions = $update_count = array();
		
		$template->assign_var('S_HAS_ATTACHMENTS', true);
		display_attachments('attachment', $message_parser->attachment_data, $update_count, true);
	}
}


// Decode text for message display
$bbcode_uid = ($mode == 'quote' && !$preview && !$refresh && !sizeof($error)) ? $bbcode_uid : $message_parser->bbcode_uid;

decode_text($post_text, $bbcode_uid);

if ($subject)
{
	decode_text($subject, $bbcode_uid);
}


// Save us some processing time. ;)
if (count($poll_options))
{
	$poll_options_tmp = implode("\n", $poll_options);
	decode_text($poll_options_tmp);
	$poll_options = explode("\n", $poll_options_tmp);
}


if ($mode == 'quote' && !$preview && !$refresh)
{
	$post_text = '[quote="' . $quote_username . '"]' . ((sizeof($censors['match'])) ? preg_replace($censors['match'], $censors['replace'], trim($post_text)) : trim($post_text)) . "[/quote]\n";
}


if (($mode == 'reply' || $mode == 'quote') && !$preview && !$refresh)
{
	$post_subject = ((!preg_match('/^Re:/', $post_subject)) ? 'Re: ' : '') . ((sizeof($censors['match'])) ? preg_replace($censors['match'], $censors['replace'], $post_subject) : $post_subject);
}


// MAIN POSTING PAGE BEGINS HERE

// Forum moderators?
get_moderators($moderators, $forum_id);


// Generate smilie listing
generate_smilies('inline', $forum_id);


// Generate Topic icons
$s_topic_icons = false;
if ($enable_icons)
{
	// Grab icons
	$icons = array();
	obtain_icons($icons);

	if (sizeof($icons))
	{
		foreach ($icons as $id => $data)
		{
			if ($data['display'])
			{
				$template->assign_block_vars('topic_icon', array(
					'ICON_ID'		=> $id,
					'ICON_IMG'		=> $phpbb_root_path . $config['icons_path'] . '/' . $data['img'],
					'ICON_WIDTH'	=> $data['width'],
					'ICON_HEIGHT' 	=> $data['height'],

					'S_ICON_CHECKED' => ($id == $icon_id && $mode != 'reply') ? ' checked="checked"' : '')
				);
			}
		}

		$s_topic_icons = true;
	}
}

// Topic type selection ... only for first post in topic.
$topic_type_toggle = false;
if ($mode == 'post' || ($mode == 'edit' && $post_id == $topic_first_post_id))
{
	$topic_types = array(
		'sticky' => array('const' => POST_STICKY, 'lang' => 'POST_STICKY'),
		'announce' => array('const' => POST_ANNOUNCE, 'lang' => 'POST_ANNOUNCEMENT'),
		'global' => array('const' => POST_GLOBAL, 'lang' => 'POST_GLOBAL')
	);
	
	$topic_type_array = array();
	
	foreach ($topic_types as $auth_key => $topic_value)
	{
		// Temp - we do not have a special post global announcement permission
		$auth_key = ($auth_key == 'global') ? 'announce' : $auth_key;

		if ($auth->acl_get('f_' . $auth_key, $forum_id))
		{
			$topic_type_toggle = true;

			$topic_type_array[] = array(
				'VALUE' => $topic_value['const'],
				'S_CHECKED' => ($topic_type == $topic_value['const'] || ($forum_id == 0 && $topic_value['const'] == POST_GLOBAL)) ? ' checked="checked"' : '',
				'L_TOPIC_TYPE' => $user->lang[$topic_value['lang']]
			);
		}
	}

	if ($topic_type_toggle)
	{
		$topic_type_array = array_merge(array(0 => array(
			'VALUE' => POST_NORMAL,
			'S_CHECKED' => ($topic_type == POST_NORMAL) ? ' checked="checked"' : '',
			'L_TOPIC_TYPE' => $user->lang['POST_NORMAL'])), 
			
			$topic_type_array
		);
		
		foreach ($topic_type_array as $array)
		{
			$template->assign_block_vars('topic_type', $array);
		}

		$template->assign_vars(array(
			'S_TOPIC_TYPE_STICKY'	=> ($auth->acl_get('f_sticky', $forum_id)),
			'S_TOPIC_TYPE_ANNOUNCE'	=> ($auth->acl_get('f_announce', $forum_id)))
		);
	}
}

$html_checked		= (isset($enable_html)) ? !$enable_html : (($config['allow_html']) ? !$user->optionget('html') : 1);
$bbcode_checked		= (isset($enable_bbcode)) ? !$enable_bbcode : (($config['allow_bbcode']) ? !$user->optionget('bbcode') : 1);
$smilies_checked	= (isset($enable_smilies)) ? !$enable_smilies : (($config['allow_smilies']) ? !$user->optionget('smile') : 1);
$urls_checked		= (isset($enable_urls)) ? !$enable_urls : 0;
$sig_checked		= $enable_sig;
$notify_checked		= (isset($notify)) ? $notify : ((!$notify_set) ? (($user->data['user_id'] != ANONYMOUS) ? $user->data['user_notify'] : 0) : 1);
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

// Build navigation links
$forum_data = array(
	'parent_id'		=> $parent_id,
	'forum_parents'	=> $forum_parents,
	'forum_name'	=> $forum_name,
	'forum_id'		=> $forum_id,
	'forum_desc'	=> ''
);
generate_forum_nav($forum_data);

$s_hidden_fields = ($mode == 'reply' || $mode == 'quote') ? '<input type="hidden" name="topic_cur_post_id" value="' . $topic_last_post_id . '" />' : '';
$s_hidden_fields .= '<input type="hidden" name="lastclick" value="' . $current_time . '" />';
$s_hidden_fields .= (isset($check_value)) ? '<input type="hidden" name="status_switch" value="' . $check_value . '" />' : '';
$s_hidden_fields .= ($draft_id || isset($_REQUEST['draft_loaded'])) ? '<input type="hidden" name="draft_loaded" value="' . ((isset($_REQUEST['draft_loaded'])) ? intval($_REQUEST['draft_loaded']) : $draft_id) . '" />' : '';

$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off' || @ini_get('file_uploads') == '0' || !$config['allow_attachments'] || !$auth->acl_gets('f_attach', 'u_attach', $forum_id)) ? '' : 'enctype="multipart/form-data"';

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
	'MESSAGE'				=> trim($post_text),
	'PREVIEW_SUBJECT'		=> ($preview && !sizeof($error)) ? $preview_subject : '',
	'PREVIEW_MESSAGE'		=> ($preview && !sizeof($error)) ? $preview_message : '', 
	'PREVIEW_SIGNATURE'		=> ($preview && !sizeof($error)) ? $preview_signature : '', 
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

	'S_DISPLAY_PREVIEW'		=> ($preview && !sizeof($error)),
	'S_EDIT_POST'			=> ($mode == 'edit'),
	'S_EDIT_REASON'			=> ($mode == 'edit' && $user->data['user_id'] != $poster_id),
	'S_DISPLAY_USERNAME'	=> ($user->data['user_id'] == ANONYMOUS || ($mode == 'edit' && $post_username)),
	'S_SHOW_TOPIC_ICONS'	=> $s_topic_icons,
	'S_DELETE_ALLOWED' 		=> ($mode == 'edit' && (($post_id == $topic_last_post_id && $poster_id == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id)) || $auth->acl_get('m_delete', $forum_id))),
	'S_HTML_ALLOWED'		=> $html_status,
	'S_HTML_CHECKED' 		=> ($html_checked) ? ' checked="checked"' : '',
	'S_BBCODE_ALLOWED'		=> $bbcode_status,
	'S_BBCODE_CHECKED' 		=> ($bbcode_checked) ? ' checked="checked"' : '',
	'S_SMILIES_ALLOWED'		=> $smilies_status,
	'S_SMILIES_CHECKED' 	=> ($smilies_checked) ? ' checked="checked"' : '',
	'S_SIG_ALLOWED'			=> ($auth->acl_get('f_sigs', $forum_id) && $config['allow_sig'] && $user->data['user_id'] != ANONYMOUS),
	'S_SIGNATURE_CHECKED' 	=> ($sig_checked) ? ' checked="checked"' : '',
	'S_NOTIFY_ALLOWED'		=> ($user->data['user_id'] != ANONYMOUS),
	'S_NOTIFY_CHECKED' 		=> ($notify_checked) ? ' checked="checked"' : '',
	'S_LOCK_TOPIC_ALLOWED'	=> (($mode == 'edit' || $mode == 'reply' || $mode == 'quote') && ($auth->acl_get('m_lock', $forum_id) || ($auth->acl_get('f_user_lock', $forum_id) && $user->data['user_id'] != ANONYMOUS && $user->data['user_id'] == $topic_poster))),
	'S_LOCK_TOPIC_CHECKED'	=> ($lock_topic_checked) ? ' checked="checked"' : '',
	'S_LOCK_POST_ALLOWED'	=> ($mode == 'edit' && $auth->acl_get('m_edit', $forum_id)),
	'S_LOCK_POST_CHECKED'	=> ($lock_post_checked) ? ' checked="checked"' : '',
	'S_MAGIC_URL_CHECKED' 	=> ($urls_checked) ? ' checked="checked"' : '',
	'S_TYPE_TOGGLE'			=> $topic_type_toggle,
	'S_SAVE_ALLOWED'		=> ($auth->acl_get('u_savedrafts') && $user->data['user_id'] != ANONYMOUS),
	'S_HAS_DRAFTS'			=> ($auth->acl_get('u_savedrafts') && $user->data['user_id'] != ANONYMOUS && $drafts),
	'S_FORM_ENCTYPE'		=> $form_enctype,

	'S_POST_ACTION' 		=> $s_action,
	'S_HIDDEN_FIELDS'		=> $s_hidden_fields)
);

// Poll entry
if (($mode == 'post' || ($mode == 'edit' && $post_id == $topic_first_post_id && !$poll_last_vote)) && ($auth->acl_get('f_poll', $forum_id) || $auth->acl_get('m_edit', $forum_id)))
{
	$template->assign_vars(array(
		'S_SHOW_POLL_BOX'		=> true,
		'S_POLL_DELETE'			=> ($mode == 'edit' && $poll_options && ((!$poll_last_vote && $poster_id == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id)) || $auth->acl_get('m_delete', $forum_id))),

		'L_POLL_OPTIONS_EXPLAIN'=> sprintf($user->lang['POLL_OPTIONS_EXPLAIN'], $config['max_poll_options']),

		'POLL_TITLE' 			=> $poll_title,
		'POLL_OPTIONS'			=> ($poll_options) ? implode("\n", $poll_options) : '',
		'POLL_MAX_OPTIONS'		=> ($poll_max_options) ? $poll_max_options : 1, 
		'POLL_LENGTH' 			=> $poll_length)
	);
}
else if ($mode == 'edit' && $poll_last_vote && ($auth->acl_get('f_poll', $forum_id) || $auth->acl_get('m_edit', $forum_id)))
{
	$template->assign_vars(array(
		'S_POLL_DELETE'			=> ($mode == 'edit' && $poll_options && ($auth->acl_get('f_delete', $forum_id) || $auth->acl_get('m_delete', $forum_id))))
	);
}

// Attachment entry
if ($auth->acl_gets('f_attach', 'u_attach', $forum_id) && $config['allow_attachments'] && $form_enctype)
{
	$template->assign_vars(array(
		'S_SHOW_ATTACH_BOX'	=> true)
	);

	if (sizeof($message_parser->attachment_data))
	{
		$template->assign_vars(array(
			'S_HAS_ATTACHMENTS'	=> true)
		);
		
		$count = 0;
		foreach ($message_parser->attachment_data as $attach_row)
		{
			$hidden = '';
			$attach_row['real_filename'] = stripslashes($attach_row['real_filename']);

			foreach ($attach_row as $key => $value)
			{
				$hidden .= '<input type="hidden" name="attachment_data[' . $count . '][' . $key . ']" value="' . $value . '" />';
			}
			
			$download_link = (!$attach_row['attach_id']) ? $config['upload_dir'] . '/' . $attach_row['physical_filename'] : $phpbb_root_path . "download.$phpEx$SID&id=" . intval($attach_row['attach_id']);
				
			$template->assign_block_vars('attach_row', array(
				'FILENAME'			=> $attach_row['real_filename'],
				'ATTACH_FILENAME'	=> $attach_row['physical_filename'],
				'FILE_COMMENT'		=> $attach_row['comment'],
				'ATTACH_ID'			=> $attach_row['attach_id'],
				'ASSOC_INDEX'		=> $count,

				'U_VIEW_ATTACHMENT' => $download_link,
				'S_HIDDEN'			=> $hidden)
			);

			$count++;
		}
	}

	$template->assign_vars(array(
		'FILE_COMMENT'	=> $message_parser->filename_data['filecomment'], 
		'FILESIZE'		=> $config['max_filesize'],
		'FILENAME'		=> $message_parser->filename_data['filename'])
	);
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


// ---------
// FUNCTIONS
//


// User Notification
function user_notification($mode, $subject, $topic_title, $forum_name, $forum_id, $topic_id, $post_id)
{
	global $db, $user, $censors, $config, $phpbb_root_path, $phpEx, $auth;

	$topic_notification = ($mode == 'reply' || $mode == 'quote');
	$forum_notification = ($mode == 'post');

	if (!$topic_notification && !$forum_notification)
	{
		trigger_error('WRONG_NOTIFICATION_MODE');
	}

	if (!$censors)
	{
		$censors = array();
		obtain_word_list($censors);
	}

	$topic_title = ($topic_notification) ? $topic_title : $subject;
	decode_text($topic_title);
	$topic_title = (sizeof($censors['match'])) ? preg_replace($censors['match'], $censors['replace'], $topic_title) : $topic_title;

	// Get banned User ID's
	$sql = 'SELECT ban_userid 
		FROM ' . BANLIST_TABLE;
	$result = $db->sql_query($sql);

	$sql_ignore_users = ANONYMOUS . ', ' . $user->data['user_id'];
	while ($row = $db->sql_fetchrow($result))
	{
		if (isset($row['ban_userid']))
		{
			$sql_ignore_users .= ', ' . $row['ban_userid'];
		}
	}
	$db->sql_freeresult($result);

	$notify_rows = array();

	// -- get forum_userids	|| topic_userids
	$sql = 'SELECT u.user_id, u.username, u.user_email, u.user_lang, u.user_notify_type, u.user_jabber 
		FROM ' . (($topic_notification) ? TOPICS_WATCH_TABLE : FORUMS_WATCH_TABLE) . ' w, ' . USERS_TABLE . ' u
		WHERE w.' . (($topic_notification) ? 'topic_id' : 'forum_id') . ' = ' . (($topic_notification) ? $topic_id : $forum_id) . "
			AND w.user_id NOT IN ($sql_ignore_users)
			AND w.notify_status = 0
			AND u.user_id = w.user_id";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$notify_rows[$row['user_id']] = array(
			'user_id'		=> $row['user_id'],
			'username'		=> $row['username'],
			'user_email'	=> $row['user_email'],
			'user_jabber'	=> $row['user_jabber'], 
			'user_lang'		=> $row['user_lang'], 
			'notify_type'	=> ($topic_notification) ? 'topic' : 'forum',
			'template'		=> ($topic_notification) ? 'topic_notify' : 'newtopic_notify',
			'method'		=> $row['user_notify_type'], 
			'allowed'		=> false
		);
	}
	$db->sql_freeresult($result);
	
	// forum notification is sent to those not receiving post notification
	if ($topic_notification)
	{
		if (sizeof($notify_rows))
		{
			$sql_ignore_users .= ', ' . implode(', ', array_keys($notify_rows));
		}

		$sql = 'SELECT u.user_id, u.username, u.user_email, u.user_lang, u.user_notify_type, u.user_jabber 
			FROM ' . FORUMS_WATCH_TABLE . ' fw, ' . USERS_TABLE . " u
			WHERE fw.forum_id = $forum_id
				AND fw.user_id NOT IN ($sql_ignore_users)
				AND fw.notify_status = 0
				AND u.user_id = fw.user_id";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$notify_rows[$row['user_id']] = array(
				'user_id'		=> $row['user_id'],
				'username'		=> $row['username'],
				'user_email'	=> $row['user_email'],
				'user_jabber'	=> $row['user_jabber'], 
				'user_lang'		=> $row['user_lang'],
				'notify_type'	=> 'forum',
				'template'		=> 'forum_notify',
				'method'		=> $row['user_notify_type'], 
				'allowed'		=> false
			);
		}
		$db->sql_freeresult($result);
	}

	if (!sizeof($notify_rows))
	{
		return;
	}

	foreach ($auth->acl_get_list(array_keys($notify_rows), 'f_read', $forum_id) as $forum_id => $forum_ary)
	{
		foreach ($forum_ary as $auth_option => $user_ary)
		{
			foreach ($user_ary as $user_id)
			{
				$notify_rows[$user_id]['allowed'] = true;
			}
		}
	}


	// Now, we have to do a little step before really sending, we need to distinguish our users a little bit. ;)
	$email_users = $delete_ids = $update_notification = array();
	foreach ($notify_rows as $user_id => $row)
	{
		if (!$row['allowed'] || !trim($row['user_email']))
		{
			$delete_ids[$row['notify_type']][] = $row['user_id'];
		}
		else
		{
			$msg_users[] = $row;
			$update_notification[$row['notify_type']][] = $row['user_id'];
		}
	}
	unset($notify_rows);

	// Now, we are able to really send out notifications
	if (sizeof($msg_users))
	{
		include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);
		$messenger = new messenger();

		$email_sig = str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']);

		$msg_list_ary = array();
		foreach ($msg_users as $row)
		{ 
			$pos = sizeof($msg_list_ary[$row['template']]);

			$msg_list_ary[$row['template']][$pos]['method']	= $row['method'];
			$msg_list_ary[$row['template']][$pos]['email']	= $row['user_email'];
			$msg_list_ary[$row['template']][$pos]['jabber']	= $row['user_jabber'];
			$msg_list_ary[$row['template']][$pos]['name']	= $row['username'];
			$msg_list_ary[$row['template']][$pos]['lang']	= $row['user_lang'];
		}
		unset($email_users);

		foreach ($msg_list_ary as $email_template => $email_list)
		{
			foreach ($email_list as $addr)
			{
				$messenger->template($email_template, $addr['lang']);

				$messenger->replyto($config['board_email']);
				$messenger->to($addr['email'], $addr['name']);
				$messenger->im($addr['jabber'], $addr['name']);

				$messenger->assign_vars(array(
					'EMAIL_SIG'		=> $email_sig,
					'SITENAME'		=> $config['sitename'],
					'TOPIC_TITLE'	=> $topic_title,  
					'FORUM_NAME'	=> $forum_name,

					'U_FORUM'				=> generate_board_url() . "/viewforum.$phpEx?f=$forum_id&e=1",
					'U_TOPIC'				=> generate_board_url() . "/viewtopic.$phpEx?f=$forum_id&t=$topic_id&e=1",
					'U_NEWEST_POST'			=> generate_board_url() . "/viewtopic.$phpEx?f=$forum_id&t=$topic_id&p=$post_id&e=1#$post_id",
					'U_STOP_WATCHING_TOPIC' => generate_board_url() . "/viewtopic.$phpEx?f=$forum_id&t=$topic_id&unwatch=topic",
					'U_STOP_WATCHING_FORUM' => generate_board_url() . "/viewforum.$phpEx?f=$forum_id&unwatch=forum", 
				));

				$messenger->send($addr['method']);
				$messenger->reset();
			}
		}
		unset($email_list_ary);

		if ($messenger->queue)
		{
			$messenger->queue->save();
		}
	}

	// Handle the DB updates
	$db->sql_transaction();

	if (sizeof($update_notification['topic']))
	{
		$db->sql_query('UPDATE ' . TOPICS_WATCH_TABLE . "
			SET notify_status = 1
			WHERE topic_id = $topic_id
				AND user_id IN (" . implode(', ', $update_notification['topic']) . ")");
	}

	if (sizeof($update_notification['forum']))
	{
		$db->sql_query('UPDATE ' . FORUMS_WATCH_TABLE . "
			SET notify_status = 1
			WHERE forum_id = $forum_id
				AND user_id IN (" . implode(', ', $update_notification['forum']) . ")");
	}

	// Now delete the user_ids not authorized to receive notifications on this topic/forum
	if (sizeof($delete_ids['topic']))
	{
		$db->sql_query('DELETE FROM ' . TOPICS_WATCH_TABLE . "
			WHERE topic_id = $topic_id
				AND user_id IN (" . implode(', ', $delete_ids['topic']) . ")");
	}

	if (sizeof($delete_ids['forum']))
	{
		$db->sql_query('DELETE FROM ' . FORUMS_WATCH_TABLE . "
			WHERE forum_id = $forum_id
				AND user_id IN (" . implode(', ', $delete_ids['forum']) . ")");
	}

	$db->sql_transaction('commit');

}

// Topic Review
function topic_review($topic_id, $forum_id, $mode = 'topic_review', $cur_post_id = 0)
{
	global $user, $auth, $db, $template, $bbcode, $template;
	global $censors, $config, $phpbb_root_path, $phpEx, $SID;

	// Define censored word matches
	if (!$censors)
	{
		$censors = array();
		obtain_word_list($censors);
	}

	// Go ahead and pull all data for this topic
	$sql = 'SELECT u.username, u.user_id, u.user_karma, p.post_id, p.post_username, p.post_subject, p.post_text, p.enable_smilies, p.bbcode_uid, p.bbcode_bitfield, p.post_time
		FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
		WHERE p.topic_id = $topic_id
			AND p.poster_id = u.user_id
			" . ((!$auth->acl_get('m_approve', $forum_id)) ? 'AND p.post_approved = 1' : '') . '
			' . (($mode == 'post_review') ? " AND p.post_id > $cur_post_id" : '') . '
		ORDER BY p.post_time DESC';
	$result = $db->sql_query_limit($sql, $config['posts_per_page']);

	if (!$row = $db->sql_fetchrow($result))
	{
		return false;
	}

	$bbcode_bitfield = 0;
	do
	{
		$rowset[] = $row;
		$bbcode_bitfield |= $row['bbcode_bitfield'];
	}
	while ($row = $db->sql_fetchrow($result));
	$db->sql_freeresult($result);

	// Instantiate BBCode class
	if (!isset($bbcode) && $bbcode_bitfield)
	{
		include($phpbb_root_path . 'includes/bbcode.'.$phpEx);
		$bbcode = new bbcode($bbcode_bitfield);
	}

	foreach ($rowset as $i => $row)
	{
		$poster_id = $row['user_id'];
		$poster = $row['username'];

		// Handle anon users posting with usernames
		if ($poster_id == ANONYMOUS && $row['post_username'])
		{
			$poster = $row['post_username'];
			$poster_rank = $user->lang['GUEST'];
		}

		$post_subject = $row['post_subject'];
		$message = $row['post_text'];

		if ($row['bbcode_bitfield'])
		{
			$bbcode->bbcode_second_pass($message, $row['bbcode_uid'], $row['bbcode_bitfield']);
		}

		$message = (!$row['enable_smilies'] || !$config['allow_smilies']) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $message) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $phpbb_root_path . $config['smilies_path'], $message);

		if (sizeof($censors['match']))
		{
			$post_subject = preg_replace($censors['match'], $censors['replace'], $post_subject);
			$message = preg_replace($censors['match'], $censors['replace'], $message);
		}

		$template->assign_block_vars($mode . '_row', array(
			'KARMA_IMG'		=> '<img src="images/karma' . $row['user_karma'] . '.gif" alt="' . $user->lang['KARMA_LEVEL'] . ': ' . $user->lang['KARMA'][$row['user_karma']] . '" title="' . $user->lang['KARMA_LEVEL'] . ': ' .  $user->lang['KARMA'][$row['user_karma']] . '" />',
			'POSTER_NAME' 	=> $poster,
			'POST_SUBJECT' 	=> $post_subject,
			'MINI_POST_IMG' => $user->img('icon_post', $user->lang['POST']),
			'POST_DATE' 	=> $user->format_date($row['post_time']),
			'MESSAGE' 		=> str_replace("\n", '<br />', $message), 

			'U_POST_ID'		=> $row['post_id'],
			'U_MINI_POST'	=> "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;p=" . $row['post_id'] . '#' . $row['post_id'],
			'U_QUOTE'		=> ($quote_status) ? 'javascript:addquote(' . $row['post_id'] . ", '" . str_replace("'", "\\'", $poster) . "')" : '', 

			'S_ROW_COUNT'	=> $i)
		);
		unset($rowset[$i]);
	}

	if ($mode == 'topic_review')
	{
		$template->assign_var('QUOTE_IMG', $user->img('btn_quote', $user->lang['QUOTE_POST']));
	}

	return true;
}


// Temp Function - strtolower - borrowed from php.net
function phpbb_strtolower($string)
{
	$new_string = '';

	for ($i = 0; $i < strlen($string); $i++) 
	{
		if (ord(substr($string, $i, 1)) > 0xa0) 
		{
			$new_string .= strtolower(substr($string, $i, 2));
			$i++;
		} 
		else 
		{
			$new_string .= strtolower($string{$i});
		}
	}

	return $new_string;
}

// Delete Post
function delete_post($mode, $post_id, $topic_id, $forum_id, $data)
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
						(($auth->acl_get('m_approve')) ? 'AND post_approved = 1' : '');
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
					(($auth->acl_get('m_approve')) ? 'AND post_approved = 1' : '') . '
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


// Submit Post
function submit_post($mode, $message, $subject, $username, $topic_type, $bbcode_uid, $poll, $attach_data, $filename_data, $data)
{
	global $db, $auth, $user, $config, $phpEx, $SID, $template;

	// We do not handle erasing posts here
	if ($mode == 'delete')
	{
		return;
	}
	
	$current_time = time();

	if ($mode == 'post')
	{
		$post_mode = 'post';
	}
	else if ($mode != 'edit')
	{
		$post_mode = 'reply';
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
				'post_approved' 	=> ($auth->acl_get('f_moderate', $data['forum_id'])) ? 0 : 1,
				'enable_bbcode' 	=> $data['enable_bbcode'],
				'enable_html' 		=> $data['enable_html'],
				'enable_smilies' 	=> $data['enable_smilies'],
				'enable_magic_url' 	=> $data['enable_urls'],
				'enable_sig' 		=> $data['enable_sig'],
				'post_username'		=> ($user->data['user_id'] == ANONYMOUS) ? stripslashes($username) : '', 
				'post_subject'		=> $subject,
				'post_text' 		=> $message,
				'post_checksum'		=> $data['message_md5'],
				'post_encoding'		=> $user->lang['ENCODING'],
				'post_attachment'	=> (sizeof($filename_data['physical_filename'])) ? 1 : 0,
				'bbcode_bitfield'	=> $data['bbcode_bitfield'],
				'bbcode_uid'		=> $bbcode_uid,
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

		case 'edit_topic':
		case 'edit_last_post':
		
			$sql_data[POSTS_TABLE]['sql'] = array_merge($sql_data[POSTS_TABLE]['sql'], array(
				'forum_id' 			=> ($topic_type == POST_GLOBAL) ? 0 : $data['forum_id'],
				'poster_id' 		=> $data['poster_id'],
				'icon_id'			=> $data['icon_id'],
				'post_approved' 	=> ($auth->acl_get('f_moderate', $data['forum_id'])) ? 0 : 1,
				'enable_bbcode' 	=> $data['enable_bbcode'],
				'enable_html' 		=> $data['enable_html'],
				'enable_smilies' 	=> $data['enable_smilies'],
				'enable_magic_url' 	=> $data['enable_urls'],
				'enable_sig' 		=> $data['enable_sig'],
				'post_username'		=> ($username && $data['poster_id'] == ANONYMOUS) ? stripslashes($username) : '', 
				'post_subject'		=> $subject,
				'post_text' 		=> $message,
				'post_edit_reason'	=> $data['post_edit_reason'],
				'post_edit_user'	=> $data['post_edit_user'],
				'post_checksum'		=> $data['message_md5'],
				'post_encoding'		=> $user->lang['ENCODING'],
				'post_attachment'	=> (sizeof($filename_data['physical_filename'])) ? 1 : 0,
				'bbcode_bitfield'	=> $data['bbcode_bitfield'],
				'bbcode_uid'		=> $bbcode_uid,
				'post_edit_locked'	=> $data['post_edit_locked'])
			);
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
				'topic_approved'	=> ($auth->acl_get('f_moderate', $data['forum_id'])) ? 0 : 1, 
				'topic_title' 		=> $subject,
				'topic_first_poster_name' => ($user->data['user_id'] == ANONYMOUS && $username) ? stripslashes($username) : $user->data['username'],
				'topic_type'		=> $topic_type,
				'topic_time_limit'	=> ($topic_type == POST_STICKY || $topic_type == POST_ANNOUNCE) ? ($data['topic_time_limit'] * 86400) : 0,
				'topic_attachment'	=> (sizeof($filename_data['physical_filename'])) ? 1 : 0
			);

			if ($poll['poll_options'])
			{
				$sql_data[TOPICS_TABLE]['sql'] = array_merge($sql_data[TOPICS_TABLE]['sql'], array(
					'poll_title'		=> $poll['poll_title'],
					'poll_start'		=> ($poll['poll_start']) ? $poll['poll_start'] : $current_time, 
					'poll_max_options'	=> $poll['poll_max_options'], 
					'poll_length'		=> $poll['poll_length'] * 86400)
				);
			}
			
			$sql_data[USERS_TABLE]['stat'][] = "user_lastpost_time = $current_time" . (($auth->acl_get('f_postcount', $data['forum_id'])) ? ', user_posts = user_posts + 1' : '');
			$sql_data[FORUMS_TABLE]['stat'][] = 'forum_posts = forum_posts + 1'; //(!$auth->acl_get('f_moderate', $data['forum_id'])) ? 'forum_posts = forum_posts + 1' : '';
			$sql_data[FORUMS_TABLE]['stat'][] = 'forum_topics_real = forum_topics_real + 1' . ((!$auth->acl_get('f_moderate', $data['forum_id'])) ? ', forum_topics = forum_topics + 1' : '');
			break;
			
		case 'reply':
			$sql_data[TOPICS_TABLE]['stat'][] = 'topic_replies_real = topic_replies_real + 1, topic_bumped = 0, topic_bumper = 0' . ((!$auth->acl_get('f_moderate', $data['forum_id'])) ? ', topic_replies = topic_replies + 1' : '');
			$sql_data[USERS_TABLE]['stat'][] = "user_lastpost_time = $current_time" . (($auth->acl_get('f_postcount', $data['forum_id'])) ? ', user_posts = user_posts + 1' : '');
			$sql_data[FORUMS_TABLE]['stat'][] = 'forum_posts = forum_posts + 1'; //(!$auth->acl_get('f_moderate', $data['forum_id'])) ? 'forum_posts = forum_posts + 1' : '';
			break;

		case 'edit_topic':
		case 'edit_first_post':

			$sql_data[TOPICS_TABLE]['sql'] = array(
				'forum_id' 					=> ($topic_type == POST_GLOBAL) ? 0 : $data['forum_id'],
				'icon_id'					=> $data['icon_id'],
				'topic_approved'			=> ($auth->acl_get('f_moderate', $data['forum_id'])) ? 0 : 1, 
				'topic_title' 				=> $subject,
				'topic_first_poster_name'	=> stripslashes($username),
				'topic_type'				=> $topic_type,
				'topic_time_limit'			=> ($topic_type == POST_STICKY || $topic_type == POST_ANNOUNCE) ? ($data['topic_time_limit'] * 86400) : 0,
				'poll_title'				=> ($poll['poll_options']) ? $poll['poll_title'] : '',
				'poll_start'				=> ($poll['poll_options']) ? (($poll['poll_start']) ? $poll['poll_start'] : $current_time) : 0, 
				'poll_max_options'			=> ($poll['poll_options']) ? $poll['poll_max_options'] : 1, 
				'poll_length'				=> ($poll['poll_options']) ? $poll['poll_length'] * 86400 : 0,

				'topic_attachment'			=> ($post_mode == 'edit_topic') ? ((sizeof($filename_data['physical_filename'])) ? 1 : 0) : $data['topic_attachment']
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
				'topic_first_post_id' => $data['post_id'],
				'topic_last_post_id' => $data['post_id'],
				'topic_last_post_time' => $current_time,
				'topic_last_poster_id' => (int) $user->data['user_id'],
				'topic_last_poster_name' => ($user->data['user_id'] == ANONYMOUS && $username) ? stripslashes($username) : $user->data['username']
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

		// globalise
		if ((int)$row['topic_type'] != POST_GLOBAL && $topic_type == POST_GLOBAL)
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
		else if ((int)$row['topic_type'] == POST_GLOBAL && $topic_type != POST_GLOBAL)
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

	// Update Poll Tables and Attachment Entries
	if ($poll['poll_options'])
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

		for ($i = 0; $i < sizeof($poll['poll_options']); $i++)
		{
			if (trim($poll['poll_options'][$i]))
			{
				if (!$cur_poll_options[$i])
				{
					$sql = 'INSERT INTO ' . POLL_OPTIONS_TABLE . "  (poll_option_id, topic_id, poll_option_text)
						VALUES ($i, " . $data['topic_id'] . ", '" . $db->sql_escape($poll['poll_options'][$i]) . "')";
					$db->sql_query($sql);
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
			
		if (sizeof($poll['poll_options']) < sizeof($cur_poll_options))
		{
			$sql = 'DELETE FROM ' . POLL_OPTIONS_TABLE . '
				WHERE poll_option_id > ' . sizeof($poll['poll_options']) . ' 
					AND topic_id = ' . $data['topic_id'];
			$db->sql_query($sql);
		}
	}

	// Submit Attachments
	if (count($attach_data) && $data['post_id'] && in_array($mode, array('post', 'reply', 'quote', 'edit')))
	{
		$space_taken = $files_added = 0;
		foreach ($attach_data as $attach_row)
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
				$attach_sql = array(
					'post_id'			=> $data['post_id'],
					'topic_id'			=> $data['topic_id'],
					'poster_id'			=> $poster_id,
					'physical_filename'	=> $attach_row['physical_filename'],
					'real_filename'		=> $attach_row['real_filename'],
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
		
		if (count($attach_data))
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
			// We get the last post information not for posting or replying, we can assume the correct params here, which is much faster
			if ($post_mode == 'edit_last_post')
			{
				$sql_data[FORUMS_TABLE]['stat'][] = implode(', ', update_last_post_information('forum', $data['forum_id']));
			}
			else if (!$auth->acl_get('f_moderate', $data['forum_id']))
			{
				$update_sql = 'forum_last_post_id = ' . $data['post_id'];
				$update_sql .= ", forum_last_post_time = $current_time";
				$update_sql .= ', forum_last_poster_id = ' . $user->data['user_id'];
				$update_sql .= ", forum_last_poster_name = '" . (($user->data['user_id'] == ANONYMOUS) ? $db->sql_escape(stripslashes($username)) : $db->sql_escape($user->data['username'])) . "'";
				$sql_data[FORUMS_TABLE]['stat'][] = $update_sql;
			}
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
	if (!$auth->acl_get('f_moderate', $data['forum_id']))
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
		if (implode('', $update_ary['stat']))
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
	if ($data['message_md5'] != $data['post_checksum'] && $data['enable_indexing'])
	{
		$search = new fulltext_search();
		$result = $search->add($mode, $data['post_id'], $message, $subject);
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
		
	// Mark this topic as read and posted to.
	$mark_mode = ($mode == 'post' || $mode == 'reply' || $mode == 'quote') ? 'post' : 'topic';
	markread($mark_mode, $data['forum_id'], $data['topic_id'], $data['post_time']);

	// Send Notifications
	if ($mode != 'edit' && $mode != 'delete')
	{
		user_notification($mode, stripslashes($subject), stripslashes($data['topic_title']), stripslashes($data['forum_name']), $data['forum_id'], $data['topic_id'], $data['post_id']);
	}

	meta_refresh(3, "viewtopic.$phpEx$SID&amp;f=" . $data['forum_id'] . '&amp;t=' . $data['topic_id'] . '&amp;p=' . $data['post_id'] . '#' . $data['post_id']);

	$message = ($auth->acl_get('f_moderate', $data['forum_id'])) ? 'POST_STORED_MOD' : 'POST_STORED';
	$message = $user->lang[$message] . ((!$auth->acl_get('f_moderate', $data['forum_id'])) ? '<br /><br />' . sprintf($user->lang['VIEW_MESSAGE'], '<a href="viewtopic.' . $phpEx . $SID .'&amp;f=' . $data['forum_id'] . '&amp;t=' . $data['topic_id'] . '&amp;p=' . $data['post_id'] . '#' . $data['post_id'] . '">', '</a>') : '') . '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="viewforum.' . $phpEx . $SID .'&amp;f=' . $data['forum_id'] . '">', '</a>');
	trigger_error($message);
}

// Load Drafts
function load_drafts($topic_id = 0, $forum_id = 0)
{
	global $user, $db, $template, $phpEx, $SID, $auth;

	// Only those fitting into this forum...
	$sql = 'SELECT d.draft_id, d.topic_id, d.forum_id, d.draft_subject, d.save_time, f.forum_name
		FROM ' . DRAFTS_TABLE . ' d, ' . FORUMS_TABLE . ' f
		WHERE d.user_id = ' . $user->data['user_id'] . '
			AND f.forum_id = d.forum_id ' . 
			(($forum_id) ? " AND f.forum_id = $forum_id" : '') . '
		ORDER BY save_time DESC';
	$result = $db->sql_query($sql);

	$draftrows = $topic_ids = array();

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['topic_id'])
		{
			$topic_ids[] = (int) $row['topic_id'];
		}
		$draftrows[] = $row;
	}
	$db->sql_freeresult($result);
				
	if (sizeof($topic_ids))
	{
		$sql = 'SELECT topic_id, forum_id, topic_title
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id IN (' . implode(',', array_unique($topic_ids)) . ')';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$topic_rows[$row['topic_id']] = $row;
		}
		$db->sql_freeresult($result);
	}
	unset($topic_ids);
	
	if (sizeof($draftrows))
	{
		$row_count = 0;
		$template->assign_var('S_SHOW_DRAFTS', true);

		foreach ($draftrows as $draft)
		{
			$link_topic = $link_forum = 0;
			$insert_url = $view_url = $title = '';

			if (isset($topic_rows[$draft['topic_id']]) && $auth->acl_get('f_read', $topic_rows[$draft['topic_id']]['forum_id']))
			{
				$link_topic = true;
				$view_url = "viewtopic.$phpEx$SID&amp;f=" . $topic_rows[$draft['topic_id']]['forum_id'] . "&amp;t=" . $draft['topic_id'];
				$title = ($draft['topic_id'] == $topic_id && $topic_id) ? $user->lang['CURRENT_TOPIC'] : $topic_rows[$draft['topic_id']]['topic_title'];

				$insert_url = "posting.$phpEx$SID&amp;f=" . $topic_rows[$draft['topic_id']]['forum_id'] . '&amp;t=' . $draft['topic_id'] . '&amp;mode=reply&amp;d=' . $draft['draft_id'];
			}
			else if ($auth->acl_get('f_read', $draft['forum_id']))
			{
				$link_forum = true;
				$view_url = "viewforum.$phpEx$SID&amp;f=" . $draft['forum_id'];
				$title = $draft['forum_name'];

				$insert_url = "posting.$phpEx$SID&amp;f=" . $draft['forum_id'] . '&amp;mode=post&amp;d=' . $draft['draft_id'];
			}
						
			$template->assign_block_vars('draftrow', array(
				'DRAFT_ID' => $draft['draft_id'],
				'DATE' => $user->format_date($draft['save_time']),
				'DRAFT_SUBJECT' => $draft['draft_subject'],

				'TITLE' => $title,
				'U_VIEW' => $view_url,
				'U_INSERT' => $insert_url,

				'S_ROW_COUNT' => $row_count++,
				'S_LINK_TOPIC'	=> $link_topic,
				'S_LINK_FORUM'	=> $link_forum)
			);
		}
	}
}

function prepare_data(&$variable, $change = false)
{
	if (!$change)
	{
//		return htmlspecialchars(trim(str_replace(array('\\\'', '\\"', '\\0', '\\\\'), array('\'', '"', '\0', '\\'), $variable)));
		return htmlspecialchars(trim(stripslashes(preg_replace(array("#[ \xFF]{2,}#s", "#[\r\n]{2,}#s"), array(' ', "\n"), $variable))));

	}

//	$variable = htmlspecialchars(trim(str_replace(array('\\\'', '\\"', '\\0', '\\\\'), array('\'', '"', '\0', '\\'), $variable)));
	$variable = htmlspecialchars(trim(stripslashes(preg_replace(array("#[ \xFF]{2,}#s", "#[\r\n]{2,}#s"), array(' ', "\n"), $variable))));

}


//
// FUNCTIONS
// ---------

?>