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

define('IN_PHPBB', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_posting.'.$phpEx);
include($phpbb_root_path . 'includes/message_parser.'.$phpEx);


// Start session management
$user->start();
$auth->acl($user->data);


// Grab only parameters needed here
$mode		= (!empty($_REQUEST['mode'])) ? strval($_REQUEST['mode']) : '';
$post_id	= (!empty($_REQUEST['p'])) ? intval($_REQUEST['p']) : false;
$topic_id	= (!empty($_REQUEST['t'])) ? intval($_REQUEST['t']) : false;
$forum_id	= (!empty($_REQUEST['f'])) ? intval($_REQUEST['f']) : false;
$lastclick	= (isset($_POST['lastclick'])) ? intval($_POST['lastclick']) : 0;

$submit		= (isset($_POST['post'])) ? true : false;
$preview	= (isset($_POST['preview'])) ? true : false;
$save		= (isset($_POST['save'])) ? true : false;
$cancel		= (isset($_POST['cancel'])) ? true : false;
$confirm	= (isset($_POST['confirm'])) ? true : false;
$delete		= (isset($_POST['delete'])) ? true : false;

$refresh	= isset($_POST['add_file']) || isset($_POST['delete_file']) || isset($_POST['edit_comment']);

if ($delete && !$preview && !$refresh && $submit)
{
	$mode = 'delete';
}

$error = array();


// Was cancel pressed? If so then redirect to the appropriate page
if ($cancel || time() - $lastclick < 2)
{
	$redirect = ($post_id) ? "viewtopic.$phpEx$SID&p=$post_id#$post_id" : (($topic_id) ? "viewtopic.$phpEx$SID&t=$topic_id" : (($forum_id) ? "viewforum.$phpEx$SID&f=$forum_id" : "index.$phpEx$SID"));
	redirect($redirect);
}

// What is all this following SQL for? Well, we need to know
// some basic information in all cases before we do anything.
$forum_validate = $topic_validate = $post_validate = false;

// Easier validation
$parameters	= array(
	'forums'	=> array(
		'forum_name' => 's', 'parent_id' => 'i', 'forum_parents' => 's', 'forum_status' => 'i', 'forum_type' => 'i', 'enable_icons' => 'i'
	),
	'topics'	=> array(
		'topic_status' => 'i', 'topic_first_post_id' => 'i', 'topic_last_post_id' => 'i', 'topic_type' => 'i', 'topic_title' => 's', 'poll_last_vote' => 'i', 'poll_start' => 'i', 'poll_title' => 's', 'poll_max_options' => 'i', 'poll_length' => 'i'
	),
	'posts'		=> array(
		'post_time' => 'i', 'poster_id' => 'i', 'post_username' => 's', 'post_text' => 's', 'post_subject' => 's', 'post_checksum' => 's', 'post_attachment' => 'i', 'bbcode_uid' => 's', 'enable_magic_url' => 'i', 'enable_sig' => 'i', 'enable_smilies' => 'i', 'enable_bbcode' => 'i', 'post_edit_locked' => 'i', 'username' => 's', 'user_sig' => 's', 'user_sig_bbcode_uid' => 's', 'user_sig_bbcode_bitfield' => 'i'
	)
);

switch ($mode)
{
	case 'post':
		if (!$forum_id)
		{
			trigger_error($user->lang['NO_FORUM']);
		}

		$sql = 'SELECT *
			FROM ' . FORUMS_TABLE . "
			WHERE forum_id = $forum_id";

		$forum_validate = true;
		break;

	case 'reply':
		if (!$topic_id)
		{
			trigger_error($user->lang['NO_TOPIC']);
		}
		if (!$forum_id)
		{
			trigger_error($user->lang['NO_FORUM']);
		}

		$sql = 'SELECT t.*, f.*
			FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
			WHERE t.topic_id = $topic_id
				AND (f.forum_id = t.forum_id 
					OR f.forum_id = $forum_id)";

		$forum_validate = $topic_validate = true;
		break;
		
	case 'quote':
	case 'edit':
	case 'delete':
		if (!$post_id)
		{
			trigger_error($user->lang['NO_POST']);
		}
		if (!$forum_id)
		{
			trigger_error($user->lang['NO_FORUM']);
		}

		$sql = 'SELECT p.*, t.*, f.*, u.username, u.user_sig, u.user_sig_bbcode_uid, u.user_sig_bbcode_bitfield 
			FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f, ' . USERS_TABLE . " u
			WHERE p.post_id = $post_id
				AND t.topic_id = p.topic_id
				AND u.user_id = p.poster_id
				AND (f.forum_id = t.forum_id 
					OR f.forum_id = $forum_id)";

		$forum_validate = $topic_validate = $post_validate = true;
		break;

	case 'topicreview':
		if (!$topic_id)
		{
			trigger_error($user->lang['NO_TOPIC']);
		}
		if (!$forum_id)
		{
			trigger_error($user->lang['NO_FORUM']);
		}

		topic_review($topic_id, $forum_id, false);
		break;

	case 'smilies':
		generate_smilies('window');
		break;

	default:
		$sql = '';
		trigger_error($user->lang['NO_MODE']);
}

if ($sql != '')
{
	$result = $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$quote_username = (!empty($row['username'])) ? $row['username'] : $row['post_username'];

	$forum_id = (int) $row['forum_id'];
	$topic_id = (int) $row['topic_id'];
	$post_id = (int) $row['post_id'];

	$user->setup(false, $row['forum_style']);

	if ($row['forum_password'])
	{
		login_forum_box($row);
	}
	
	// ???
	foreach ($parameters as $parameter => $param_ary)
	{
		foreach ($param_ary as $var => $type)
		{
			switch ($type)
			{
				case 'i':
					$$var = ($forum_validate) ? (int) $row[$var] : false;
					break;
				case 's':
					$$var = ($forum_validate) ? trim($row[$var]) : '';
					break;
				default:
					$$var = false;
			}
		}
	}

	$post_subject = ($post_validate) ? $post_subject : $topic_title;


	$poll_length = ($poll_length) ? $poll_length/3600 : $poll_length;
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

	$message_parser = new parse_message(0); // <- TODO: add constant (MSG_POST/MSG_PM)


	$message_parser->filename_data['filecomment'] = (isset($_POST['filecomment'])) ? trim(strip_tags($_POST['filecomment'])) : '';
	$message_parser->filename_data['filename'] = ($_FILES['fileupload']['name'] != 'none') ? trim($_FILES['fileupload']['name']) : '';

	// Get Attachment Data
	$message_parser->attachment_data = (isset($_POST['attachment_data'])) ? $_POST['attachment_data'] : array();
	
	if ($post_attachment && !$submit && !$refresh && !$preview && $mode == 'edit')
	{
		$sql = 'SELECT d.*
			FROM ' . ATTACHMENTS_TABLE . ' a, ' . ATTACHMENTS_DESC_TABLE . " d
			WHERE a.post_id = $post_id
				AND a.attach_id = d.attach_id
			ORDER BY d.filetime " . ((!$config['display_order']) ? 'DESC' : 'ASC');
		$result = $db->sql_query($sql);

		$message_parser->attachment_data = array_merge($message_parser->attachment_data, $db->sql_fetchrowset($result));
		
		$db->sql_freeresult($result);
	}
	

	if ($poster_id == ANONYMOUS || !$poster_id)
	{
		$username = ($post_validate) ? trim($post_username) : '';
	}
	else
	{
		$username = ($post_validate) ? trim($username) : '';
	}

	$enable_urls = $enable_magic_url;


	if (!$post_validate)
	{
		$enable_sig		= ($config['allow_sig'] && $user->data['user_attachsig']) ? true : false;
		$enable_smilies	= ($config['allow_smilies'] && $user->data['user_allowsmile']) ? true : false;
		$enable_bbcode	= ($config['allow_bbcode'] && $user->data['user_allowbbcode']) ? true : false;
		$enable_urls	= true;
	}

	$enable_magic_url = false;
}


// Notify user checkbox
if ($mode != 'post' && $user->data['user_id'] != ANONYMOUS)
{
	$sql = 'SELECT topic_id
		FROM ' . TOPICS_WATCH_TABLE . '
		WHERE topic_id = ' . $topic_id . '
			AND user_id = ' . $user->data['user_id'];
	$result = $db->sql_query($sql);

	$notify_set = ($db->sql_fetchrow($result)) ? 1 : 0;
	$db->sql_freeresult($result);
}
else
{
	$notify_set = -1;
}


if (!$auth->acl_get('f_' . $mode, $forum_id) && $forum_type == FORUM_POST)
{
	trigger_error($user->lang['USER_CANNOT_' . strtoupper($mode)]);
}


// Forum/Topic locked?
if (($forum_status == ITEM_LOCKED || $topic_status == ITEM_LOCKED) && !$auth->acl_get('m_edit', $forum_id))
{
	$message = ($forum_status == ITEM_LOCKED) ? 'FORUM_LOCKED' : 'TOPIC_LOCKED';
	trigger_error($user->lang[$message]);
}


// Can we edit this post?
if (($mode == 'edit' || $mode == 'delete') && !$auth->acl_get('m_edit', $forum_id) && $config['edit_time'] && $post_time < time() - $config['edit_time'])
{
	trigger_error($user->lang['CANNOT_EDIT_TIME']);
}


// Do we want to edit our post ?
if ($mode == 'edit' && !$auth->acl_get('m_edit', $forum_id) && $user->data['user_id'] != $poster_id)
{
	trigger_error($user->lang['USER_CANNOT_EDIT']);
}


// Is edit posting locked ?
if ($mode == 'edit' && $post_edit_locked && !$auth->acl_get('m_', $forum_id))
{
	trigger_error($user->lang['CANNOT_EDIT_POST_LOCKED']);
}


if ($mode == 'edit')
{
	$message_parser->bbcode_uid = $row['bbcode_uid'];
}


// Delete triggered ?
if ($mode == 'delete' && (($poster_id == $user->data['user_id'] && $user->data['user_id'] != ANONYMOUS && $auth->acl_get('f_delete', $forum_id) && $post_id == $topic_last_post_id) || $auth->acl_get('m_delete', $forum_id)))
{
	// Do we need to confirm ?
	if ($confirm)
	{
		$post_data = array(
			'topic_first_post_id'	=> $topic_first_post_id,
			'topic_last_post_id'	=> $topic_last_post_id,
			'user_id'				=> $poster_id
		);

		$search = new fulltext_search();

		include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

		$topic_sql = array();
		$forum_update_sql = $user_update_sql = '';
		$topic_update_sql = 'topic_replies = topic_replies - 1, topic_replies_real = topic_replies_real - 1';

		// User tries to delete the post twice ? Exit... we do not want the topics table screwed up.
		if (!delete_posts('post_id', array($post_id), FALSE))
		{
			trigger_error($user->lang['ALREADY_DELETED']);
		}

		// Only one post... delete topic
		if ($post_data['topic_first_post_id'] == $post_data['topic_last_post_id'])
		{
			delete_topics('topic_id', array($topic_id), FALSE);
			$forum_update_sql .= ($forum_update_sql != '') ? ', ' : '';
			$forum_update_sql .= 'forum_topics = forum_topics - 1, forum_topics_real = forum_topics_real - 1';
		}

		// Sync last post informations
		$db->sql_transaction();

		$forum_update_sql .= ($forum_update_sql != '') ? ', forum_posts = forum_posts - 1' : 'forum_posts = forum_posts - 1';

		if ($auth->acl_get('f_postcount', $forum_id))
		{
			$user_update_sql .= ($user_update_sql != '') ? ', user_posts = user_posts - 1' : 'user_posts = user_posts - 1';
		}

		$sql = 'SELECT p.post_id, p.poster_id, p.post_username, u.username 
			FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
			WHERE p.topic_id = $topic_id 
				AND p.poster_id = u.user_id 
				AND p.post_approved = 1
			ORDER BY p.post_time DESC";
		$result = $db->sql_query_limit($sql, 1);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// If Post is first post, but not the only post... make next post the topic starter one. ;)
		if ($post_data['topic_first_post_id'] != $post_data['topic_last_post_id'] && $post_id == $post_data['topic_first_post_id'])
		{
			$topic_sql = array(
				'topic_first_post_id'		=> (int) $row['post_id'],
				'topic_first_poster_name'	=> ($row['poster_id'] == ANONYMOUS) ? trim($row['post_username']) : trim($row['username'])
			);
		}

		$post_data['next_post_id'] = $row['post_id'];

		// Update Forum, Topic and User with the gathered Informations
		if ($forum_update_sql != '')
		{
			$sql = "UPDATE " . FORUMS_TABLE . " 
				SET $forum_update_sql
				WHERE forum_id = $forum_id";
			$db->sql_query($sql);
		}

		if ($topic_update_sql != '' || count($topic_sql) > 0)
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . ' 
				SET ' . ( (count($topic_sql) > 0) ? $db->sql_build_array('UPDATE', $topic_sql) : '') . ( ($topic_update_sql != '') ? ((count($topic_sql) > 0) ? ', ' . $topic_update_sql : $topic_update_sql) : '') . ' 
				WHERE topic_id = ' . $topic_id;
			$db->sql_query($sql);
		}

		if ($user_update_sql != '')
		{
			$sql = 'UPDATE ' . USERS_TABLE . ' 
				SET ' . $user_update_sql . ' 
				WHERE user_id = ' . $post_data['user_id'];
			$db->sql_query($sql);
		}

		// Update Forum stats...
		if ($post_data['topic_first_post_id'] != $post_data['topic_last_post_id'])
		{
			update_last_post_information('topic', $topic_id);
		}
		update_last_post_information('forum', $forum_id);

		$db->sql_transaction('commit');


		if ($post_data['topic_first_post_id'] == $post_data['topic_last_post_id'])
		{
			$meta_info = "viewforum.$phpEx$SID&amp;f=$forum_id";
			$message = $user->lang['DELETED'];
		}
		else
		{
			$meta_info = "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;p=" . $post_data['next_post_id'] . '#' . $post_data['next_post_id'];
			$message = $user->lang['DELETED'] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;p=" . $post_data['next_post_id'] . '#' . $post_data['next_post_id'] . '">', '</a>');
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
			'MESSAGE_TEXT'		=> $user->lang['CONFIRM_DELETE'],

			'S_CONFIRM_ACTION'	=> "posting.$phpEx$SID",
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields)
		);
		
		page_footer();
	}
}


if ($mode == 'delete' && $poster_id != $user->data['user_id'] && !$auth->acl_get('f_delete', $forum_id))
{
	trigger_error($user->lang['DELETE_OWN_POSTS']);
}


if ($mode == 'delete' && $poster_id == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id) && $post_id != $topic_last_post_id)
{
	trigger_error($user->lang['CANNOT_DELETE_REPLIED']);
}

if ($mode == 'delete')
{
	trigger_error('USER_CANNOT_DELETE');
}


// HTML, BBCode, Smilies, Images and Flash status
$html_status	= ($config['allow_html'] && $auth->acl_get('f_html', $forum_id)) ? true : false;
$bbcode_status	= ($config['allow_bbcode'] && $auth->acl_get('f_bbcode', $forum_id)) ? true : false;
$smilies_status	= ($config['allow_smilies'] && $auth->acl_get('f_smilies', $forum_id)) ? true : false;
$img_status		= ($config['allow_img'] && $auth->acl_get('f_img', $forum_id)) ? true : false;
$flash_status	= ($config['allow_flash'] && $auth->acl_get('f_flash', $forum_id)) ? true : false;


if ($submit || $preview || $refresh)
{
	$topic_cur_post_id	= (isset($_POST['topic_cur_post_id'])) ? intval($_POST['topic_cur_post_id']) : false;
	$subject			= (!empty($_POST['subject'])) ? trim(htmlspecialchars(strip_tags($_POST['subject']))) : '';

	if (strcmp($subject, strtoupper($subject)) == 0 && $subject != '')
	{
		$subject = phpbb_strtolower($subject);
	}
	
	$message_parser->message = (!empty($_POST['message'])) ? trim(str_replace(array('\\\'', '\\"', '\\0', '\\\\'), array('\'', '"', '\0', '\\'), $_POST['message'])) : '';
	
	$username			= (!empty($_POST['username'])) ? trim($_POST['username']) : ((!empty($username)) ? $username : '');
	$topic_type			= (!empty($_POST['topic_type'])) ? (int) $_POST['topic_type'] : (($mode != 'post') ? $topic_type : POST_NORMAL);
	$icon_id			= (!empty($_POST['icon'])) ? intval($_POST['icon']) : 0;

	$enable_html 		= (!$html_status || !empty($_POST['disable_html'])) ? FALSE : TRUE;
	$enable_bbcode 		= (!$bbcode_status || !empty($_POST['disable_bbcode'])) ? FALSE : TRUE;
	$enable_smilies		= (!$smilies_status || !empty($_POST['disable_smilies'])) ? FALSE : TRUE;
	$enable_urls 		= (isset($_POST['disable_magic_url'])) ? 0 : 1;
	$enable_sig			= (!$config['allow_sig']) ? false : ((!empty($_POST['attach_sig'])) ? true : false);

	$notify				= (!empty($_POST['notify'])) ? true : false;
	$topic_lock			= (isset($_POST['lock_topic'])) ? true : false;
	$post_lock			= (isset($_POST['lock_post'])) ? true : false;

	$poll_delete		= (isset($_POST['poll_delete'])) ? true : false;


	// Faster than crc32
	$check_value	= (($enable_html+1) << 16) + (($enable_bbcode+1) << 8) + (($enable_smilies+1) << 4) + (($enable_urls+1) << 2) + (($enable_sig+1) << 1);
	$status_switch	= (isset($_POST['status_switch']) && intval($_POST['status_switch']) != $check_value) ? true : false;


	if ($poll_delete && (($mode == 'edit' && !empty($poll_options) && empty($poll_last_vote) && $poster_id == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id)) || $auth->acl_get('m_delete', $forum_id)))
	{
		// Delete Poll
		$sql = 'DELETE FROM ' . POLL_OPTIONS_TABLE . "
			WHERE topic_id = $topic_id";
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . POLL_VOTES_TABLE . "
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
		$poll_title			= (!empty($_POST['poll_title'])) ? trim($_POST['poll_title']) : '';
		$poll_length		= (!empty($_POST['poll_length'])) ? intval($_POST['poll_length']) : 0;
		$poll_option_text	= (!empty($_POST['poll_option_text'])) ? trim($_POST['poll_option_text']) : '';
		$poll_max_options	= (!empty($_POST['poll_max_options'])) ? intval($_POST['poll_max_options']) : 1;
	}


	$current_time = time();


	// If replying/quoting and last post id has changed
	// give user option to continue submit or return to post
	// notify and show user the post made between his request and the final submit
	if (($mode == 'reply' || $mode == 'quote') && $topic_cur_post_id != $topic_last_post_id)
	{
		$template->assign_vars(array(
			'S_POST_REVIEW' => true)
		);

		// Define censored word matches
		if (empty($censors))
		{
			$censors = array();
			obtain_word_list($censors);
		}

		// Go ahead and pull all data for the remaining posts
		$sql = 'SELECT u.username, u.user_id, p.* 
			FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
			WHERE p.topic_id = $topic_id
				AND p.poster_id = u.user_id
				AND p.post_id > $topic_cur_post_id
				AND p.post_approved = 1
			ORDER BY p.post_time DESC";
		$result = $db->sql_query_limit($sql, $config['posts_per_page']);

		if ($row = $db->sql_fetchrow($result))
		{
			$i = 0;
			do
			{
				$user_id = $row['user_id'];
				$poster = $row['username'];

				// Handle anon users posting with usernames
				if ($user_id == ANONYMOUS && $row['post_username'] != '')
				{
					$poster = $row['post_username'];
					$poster_rank = $user->lang['GUEST'];
				}

				$post_subject = ($row['post_subject'] != '') ? $row['post_subject'] : '';
				$message = (empty($row['enable_smilies']) || empty($config['allow_smilies'])) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $row['post_text']) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $phpbb_root_path . $config['smilies_path'], $row['post_text']);

				if (count($censors['match']))
				{
					$post_subject = preg_replace($censors['match'], $censors['replace'], $post_subject);
					$message = preg_replace($censors['match'], $censors['replace'], $message);
				}

				$template->assign_block_vars('post_postrow', array(
					'MINI_POST_IMG' 	=> $user->img('icon_post', $user->lang['POST']),
					'POSTER_NAME' 		=> $poster,
					'POST_DATE' 		=> $user->format_date($row['post_time']),
					'POST_SUBJECT' 		=> $post_subject,
					'MESSAGE' 			=> str_replace("\n", '<br />', $message),

					'S_ROW_COUNT'		=> $i++)
				);
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);

		$submit = FALSE;
		$refresh = TRUE;
	}


	// Grab md5 'checksum' of new message
	$message_md5 = md5($message_parser->message);

	// Check checksum ... don't re-parse message if the same
	if ($mode != 'edit' || $message_md5 != $post_checksum || $status_switch || $preview)
	{
		// Parse message
		$message_parser->parse($enable_html, $enable_bbcode, $enable_urls, $enable_smilies, $img_status, $flash_status);
	}

	$message_parser->parse_attachments($mode, $post_id, $submit, $preview, $refresh);

	if ($mode != 'edit' && !$preview && !$refresh && !$auth->acl_get('f_ignoreflood', $forum_id))
	{
		// Flood check
		$sql = 'SELECT MAX(post_time) AS last_post_time
			FROM ' . POSTS_TABLE . '
			WHERE ' . (($user->data['user_id'] == ANONYMOUS) ? "poster_ip = '" . $user->ip . "'" : 'poster_id = ' . $user->data['user_id']);
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			if (intval($row['last_post_time']) && ($current_time - intval($row['last_post_time'])) < intval($config['flood_interval']))
			{
				$error[] = $user->lang['FLOOD_ERROR'];
			}
		}
	}

	// Validate username
	// TODO
	if (($username != '' && $user->data['user_id'] == ANONYMOUS) || ($mode == 'edit' && $post_username != ''))
	{
		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		$username = strip_tags(htmlspecialchars($username));

		if (($result = validate_username($username)) != false)
		{
			$error[] = $result;
		}
	}

	// Parse subject
	if ($subject == '' && ($mode == 'post' || ($mode == 'edit' && $topic_first_post_id == $post_id)))
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
	if ($topic_type != POST_NORMAL)
	{
		switch ($topic_type)
		{
			case POST_GLOBAL:
//				$auth_option = 'a_news';
//				break;
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
			$error[] = $user->lang['CANNOT_POST_' . strtoupper($auth_option)];
		}
	}

	if (sizeof($message_parser->warn_msg))
	{
		$error[] = implode('<br />', $message_parser->warn_msg);
	}

	// Store message, sync counters
	if (!sizeof($error) && $submit)
	{
		// Lock/Unlock Topic
		$change_topic_status = $topic_status;

		if ($topic_status == ITEM_LOCKED && !$topic_lock && $auth->acl_get('m_lock', $forum_id))
		{
			$change_topic_status = ITEM_UNLOCKED;
		}
		else if ($topic_status == ITEM_UNLOCKED && $topic_lock && $auth->acl_get('m_lock', $forum_id))
		{
			$change_topic_status = ITEM_LOCKED;
		}
		
		if ($change_topic_status != $topic_status)
		{
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

			$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_status = $change_topic_status
				WHERE topic_id = $topic_id
					AND topic_moved_id = 0";
			$db->sql_query($sql);
			
			add_log('mod', $forum_id, $topic_id, 'logm_' . (($change_topic_status == ITEM_LOCKED) ? 'lock' : 'unlock'));
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
			'topic_first_post_id'	=> $topic_first_post_id,
			'topic_last_post_id'	=> $topic_last_post_id,
			'post_id'				=> $post_id,
			'topic_id'				=> $topic_id,
			'forum_id'				=> $forum_id,
			'icon_id'				=> $icon_id,
			'poster_id'				=> $poster_id,
			'enable_sig'			=> $enable_sig,
			'enable_bbcode'			=> $enable_bbcode,
			'enable_html' 			=> $enable_html,
			'enable_smilies'		=> $enable_smilies,
			'enable_urls'			=> $enable_urls,
			'message_md5'			=> $message_md5,
			'post_checksum'			=> $post_checksum,
			'forum_parents'			=> $forum_parents,
			'notify'				=> $notify,
			'notify_set'			=> $notify_set,
			'post_edit_locked'		=> $post_edit_locked,
			'bbcode_bitfield'		=> $message_parser->bbcode_bitfield
		);
		submit_post($mode, $message_parser->message, $subject, $username, $topic_type, $message_parser->bbcode_uid, $poll, $message_parser->attachment_data, $message_parser->filename_data, $post_data);
	}	

	$post_text = $message_parser->message;
	$post_subject = $topic_title = stripslashes($subject);
}

// Preview
if (!sizeof($error) && $preview)
{
	if (empty($censors))
	{
		$censors = array();
		obtain_word_list($censors);
	}

	$post_time = ($mode == 'edit') ? $post_time : $current_time;

	$preview_subject = (sizeof($censors)) ? preg_replace($censors['match'], $censors['replace'], $subject) : $subject;

	$preview_signature = ($mode == 'edit') ? $user_sig : $user->data['user_sig'];
	$preview_signature_uid = ($mode == 'edit') ? $user_sig_bbcode_uid : $user->data['user_sig_bbcode_uid'];
	$preview_signature_bitfield = ($mode == 'edit') ? $user_sig_bbcode_bitfield : $user->data['user_sig_bbcode_bitfield'];

	include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
	$bbcode = new bbcode($message_parser->bbcode_bitfield | $preview_signature_bitfield);

	$preview_message = $message_parser->message;
	format_display($preview_message, $preview_signature, $message_parser->bbcode_uid, $preview_signature_uid, $enable_html, $enable_bbcode, $enable_urls, $enable_smilies, $enable_sig);

	// Poll Preview
	if (($mode == 'post' || ($mode == 'edit' && $post_id == $topic_first_post_id && empty($poll_last_vote))) && ($auth->acl_get('f_poll', $forum_id) || $auth->acl_get('m_edit', $forum_id)))
	{
		decode_text($poll_title, $message_parser->bbcode_uid);
		$preview_poll_title = format_display(stripslashes($poll_title), $null, $message_parser->bbcode_uid, false, $enable_html, $enable_bbcode, $enable_urls, $enable_smilies, false, false);

		$template->assign_vars(array(
			'S_HAS_POLL_OPTIONS' => (sizeof($poll_options)) ? true : false,
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
		
		$template->assign_block_vars('postrow', array(
			'S_HAS_ATTACHMENTS'	=> true)
		);

		display_attachments($message_parser->attachment_data, $update_count, true);
	}
}


// Decode text for message display
$bbcode_uid = ($mode == 'quote' && !$preview) ? $row['bbcode_uid'] : $message_parser->bbcode_uid;


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
	$post_text = '[quote="' . $quote_username . '"]' . trim($post_text) . "[/quote]\n";
}


if (($mode == 'reply' || $mode == 'quote') && !$preview && !$refresh)
{
	$post_subject = ((!preg_match('/^Re:/', $post_subject)) ? 'Re: ' : '') . $post_subject;
}


// MAIN POSTING PAGE BEGINS HERE

// Forum moderators?
get_moderators($moderators, $forum_id);


// Generate smilies and topic icon listings
generate_smilies('inline');


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
$topic_type_toggle = '';
if ($mode == 'post' || ($mode == 'edit' && $post_id == $topic_first_post_id))
{
	$topic_types = array(
		'sticky' => array('const' => POST_STICKY, 'lang' => 'POST_STICKY'),
		'announce' => array('const' => POST_ANNOUNCE, 'lang' => 'POST_ANNOUNCEMENT'),
		'global' => array('const' => POST_GLOBAL, 'lang' => 'POST_GLOBAL')
	);
	

	foreach ($topic_types as $auth_key => $topic_value)
	{
		if ($auth->acl_get('f_' . $auth_key, $forum_id))
		{
			$topic_type_toggle .= '<input type="radio" name="topic_type" value="' . $topic_value['const'] . '"';
			if ($topic_type == $topic_value['const'] || ($forum_id == 0 && $topic_value['const'] == POST_GLOBAL))
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

$html_checked		= (isset($enable_html)) ? !$enable_html : ((intval($config['allow_html'])) ? !$user->data['user_allowhtml'] : 1);
$bbcode_checked		= (isset($enable_bbcode)) ? !$enable_bbcode : ((intval($config['allow_bbcode'])) ? !$user->data['user_allowbbcode'] : 1);
$smilies_checked	= (isset($enable_smilies)) ? !$enable_smilies : ((intval($config['allow_smilies'])) ? !$user->data['user_allowsmile'] : 1);
$urls_checked		= (isset($enable_urls)) ? !$enable_urls : 0;
$sig_checked		= $enable_sig;
$notify_checked		= (isset($notify)) ? $notify : (($notify_set == -1) ? (($user->data['user_id'] != ANONYMOUS) ? $user->data['user_notify'] : 0) : $notify_set);
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
$s_hidden_fields .= '<input type="hidden" name="lastclick" value="' . time() . '" />';
$s_hidden_fields .= (isset($check_value)) ? '<input type="hidden" name="status_switch" value="' . $check_value . '" />' : '';

$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off' || @ini_get('file_uploads') == '0' || !$config['allow_attachments'] || !$auth->acl_get('f_attach', $forum_id)) ? '' : 'enctype="multipart/form-data"';

// Start assigning vars for main posting page ...
$template->assign_vars(array(
	'L_POST_A'				=> $page_title,
	'L_ICON'				=> ($mode == 'reply' || $mode == 'quote') ? $user->lang['POST_ICON'] : $user->lang['TOPIC_ICON'], 
	'L_MESSAGE_BODY_EXPLAIN'=> (intval($config['max_post_chars'])) ? sprintf($user->lang['MESSAGE_BODY_EXPLAIN'], intval($config['max_post_chars'])) : '',

	'FORUM_NAME' 			=> $forum_name,
	'FORUM_DESC'			=> (!empty($forum_desc)) ? strip_tags($forum_desc) : '',
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

	'U_VIEW_FORUM' 			=> "viewforum.$phpEx$SID&amp;f=" . $forum_id,
	'U_VIEWTOPIC' 			=> ($mode != 'post') ? "viewtopic.$phpEx$SID&amp;$forum_id&amp;t=$topic_id" : '',
	'U_REVIEW_TOPIC'		=> ($mode != 'post') ? "posting.$phpEx$SID&amp;mode=topicreview&amp;f=$forum_id&amp;t=$topic_id" : '',

	'S_DISPLAY_PREVIEW'		=> ($preview && !sizeof($error)),
	'S_DISPLAY_REVIEW'		=> ($mode == 'reply' || $mode == 'quote') ? true : false,
	'S_DISPLAY_USERNAME'	=> ($user->data['user_id'] == ANONYMOUS || ($mode == 'edit' && $post_username != '')) ? true : false,
	'S_SHOW_TOPIC_ICONS'	=> $s_topic_icons,
	'S_DELETE_ALLOWED' 		=> ($mode == 'edit' && (($post_id == $topic_last_post_id && $poster_id == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id)) || $auth->acl_get('m_delete', $forum_id))) ? true : false,
	'S_HTML_ALLOWED'		=> $html_status,
	'S_HTML_CHECKED' 		=> ($html_checked) ? 'checked="checked"' : '',
	'S_BBCODE_ALLOWED'		=> $bbcode_status,
	'S_BBCODE_CHECKED' 		=> ($bbcode_checked) ? 'checked="checked"' : '',
	'S_SMILIES_ALLOWED'		=> $smilies_status,
	'S_SMILIES_CHECKED' 	=> ($smilies_checked) ? 'checked="checked"' : '',
	'S_SIG_ALLOWED'			=> ($auth->acl_get('f_sigs', $forum_id) && $config['allow_sig']) ? true : false,
	'S_SIGNATURE_CHECKED' 	=> ($sig_checked) ? 'checked="checked"' : '',
	'S_NOTIFY_ALLOWED'		=> ($user->data['user_id'] != ANONYMOUS) ? true : false,
	'S_NOTIFY_CHECKED' 		=> ($notify_checked) ? 'checked="checked"' : '',
	'S_LOCK_TOPIC_ALLOWED'	=> (($mode == 'edit' || $mode == 'reply' || $mode == 'quote') && $auth->acl_get('m_lock', $forum_id)) ? true : false,
	'S_LOCK_TOPIC_CHECKED'	=> ($lock_topic_checked) ? 'checked="checked"' : '',
	'S_LOCK_POST_ALLOWED'	=> ($mode == 'edit' && $auth->acl_get('m_edit', $forum_id)) ? true : false,
	'S_LOCK_POST_CHECKED'	=> ($lock_post_checked) ? 'checked="checked"' : '',
	'S_MAGIC_URL_CHECKED' 	=> ($urls_checked) ? 'checked="checked"' : '',
	'S_TYPE_TOGGLE'			=> $topic_type_toggle,
	'S_SAVE_ALLOWED'		=> ($auth->acl_get('f_save', $forum_id)) ? true : false,
	'S_FORM_ENCTYPE'		=> $form_enctype,

	'S_POST_ACTION' 		=> $s_action,
	'S_HIDDEN_FIELDS'		=> $s_hidden_fields)
);

// Poll entry
if (($mode == 'post' || ($mode == 'edit' && $post_id == $topic_first_post_id && empty($poll_last_vote))) && ($auth->acl_get('f_poll', $forum_id) || $auth->acl_get('m_edit', $forum_id)))
{
	$template->assign_vars(array(
		'S_SHOW_POLL_BOX'		=> true,
		'S_POLL_DELETE'			=> ($mode == 'edit' && !empty($poll_options) && ((empty($poll_last_vote) && $poster_id == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id)) || $auth->acl_get('m_delete', $forum_id))) ? true : false,

		'L_POLL_OPTIONS_EXPLAIN'=> sprintf($user->lang['POLL_OPTIONS_EXPLAIN'], $config['max_poll_options']),

		'POLL_TITLE' 			=> $poll_title,
		'POLL_OPTIONS'			=> (!empty($poll_options)) ? implode("\n", $poll_options) : '',
		'POLL_MAX_OPTIONS'		=> (!empty($poll_max_options)) ? $poll_max_options : 1, 
		'POLL_LENGTH' 			=> $poll_length)
	);
}
else if ($mode == 'edit' && !empty($poll_last_vote) && ($auth->acl_get('f_poll', $forum_id) || $auth->acl_get('m_edit', $forum_id)))
{
	$template->assign_vars(array(
		'S_POLL_DELETE'			=> ($mode == 'edit' && !empty($poll_options) && ($auth->acl_get('f_delete', $forum_id) || $auth->acl_get('m_delete', $forum_id))) ? true : false)
	);
}

// Attachment entry
if ($auth->acl_get('f_attach', $forum_id) || $auth->acl_get('m_edit', $forum_id))
{
	$template->assign_vars(array(
		'S_SHOW_ATTACH_BOX'	=> true)
	);

	if (count($message_parser->attachment_data))
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
			
			$download_link = ($attach_row['attach_id'] == '-1') ? $config['upload_dir'] . '/' . $attach_row['physical_filename'] : $phpbb_root_path . "download.$phpEx$SID&id=" . intval($attach_row['attach_id']);
				
			$template->assign_block_vars('attach_row', array(
				'FILENAME'			=> $attach_row['real_filename'],
				'ATTACH_FILENAME'	=> $attach_row['physical_filename'],
				'FILE_COMMENT'		=> stripslashes(htmlspecialchars($attach_row['comment'])),
				'ATTACH_ID'			=> $attach_row['attach_id'],
				'ASSOC_INDEX'		=> $count,

				'U_VIEW_ATTACHMENT' => $download_link,
				'S_HIDDEN'			=> $hidden)
			);

			$count++;
		}
	}

	$template->assign_vars(array(
		'FILE_COMMENT'	=> stripslashes(htmlspecialchars($message_parser->filename_data['filecomment'])),
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
	topic_review($topic_id, $forum_id, true);
}

page_footer();


// ---------
// FUNCTIONS
//

// Submit Post
function submit_post($mode, $message, $subject, $username, $topic_type, $bbcode_uid, $poll, $attach_data, $filename_data, $data)
{
	global $db, $auth, $user, $config, $phpEx, $SID, $template;

	$current_time = time();

	$db->sql_transaction();

	$poster_id = ($mode == 'edit') ? $data['poster_id'] : (int) $user->data['user_id'];
	$post_username = (($mode == 'edit' && $username != '' && $data['poster_id'] == ANONYMOUS) || ($mode != 'edit' && $user->data['user_id'] == ANONYMOUS)) ? stripslashes($username) : '';
	$stat_username = ($mode != 'edit') ? (($user->data['user_id'] == ANONYMOUS && !empty($username)) ? stripslashes($username) : stripslashes($user->data['username'])) : (($username) ? stripslashes($username) : '');

	// Initial Topic table info
	if ($mode == 'post' || ($mode == 'edit' && $data['topic_first_post_id'] == $data['post_id']))
	{
		$topic_sql = array(
			'forum_id' 					=> ($topic_type == POST_GLOBAL) ? 0 : $data['forum_id'],
			'topic_title' 				=> stripslashes($subject),
			'topic_time'				=> $current_time,
			'topic_type'				=> $topic_type,
			'topic_approved'			=> ($auth->acl_get('f_moderate', $data['forum_id'])) ? 0 : 1, 
			'icon_id'					=> $data['icon_id'],
			'topic_attachment'			=> (sizeof($filename_data['physical_filename'])) ? 1 : 0
		);

		if (!empty($poll['poll_options']))
		{
			$topic_sql = array_merge($topic_sql, array(
				'poll_title'			=> stripslashes($poll['poll_title']),
				'poll_start'			=> ($poll['poll_start']) ? $poll['poll_start'] : $current_time, 
				'poll_max_options'		=> $poll['poll_max_options'], 
				'poll_length'			=> $poll['poll_length'] * 86400)
			);
		}

		if ($mode == 'post')
		{
			$topic_sql = array_merge($topic_sql, array(
				'topic_poster'				=> $poster_id,
				'topic_first_poster_name'	=> $stat_username)
			);
		}
		
		$sql = ($mode == 'post') ? 'INSERT INTO ' . TOPICS_TABLE . ' ' . $db->sql_build_array('INSERT', $topic_sql) : 'UPDATE ' . TOPICS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $topic_sql) . ' WHERE topic_id = ' . $data['topic_id'];
		$db->sql_query($sql);

		$data['topic_id'] = ($mode == 'post') ? $db->sql_nextid() : $data['topic_id'];
	}

	// Post table info
	$post_sql = array(
		'topic_id' 			=> $data['topic_id'],
		'forum_id' 			=> ($topic_type == POST_GLOBAL) ? 0 : $data['forum_id'],
		'poster_id' 		=> $poster_id,
		'post_username'		=> $post_username, 
		'post_subject'		=> stripslashes($subject),
		'icon_id'			=> $data['icon_id'], 
		'poster_ip' 		=> $user->ip,
		'post_approved' 	=> ($auth->acl_get('f_moderate', $data['forum_id'])) ? 0 : 1,
		'post_edit_time' 	=> ($mode == 'edit' && $data['poster_id'] == $user->data['user_id']) ? $current_time : 0,
		'enable_sig' 		=> $data['enable_sig'],
		'enable_bbcode' 	=> $data['enable_bbcode'],
		'enable_html' 		=> $data['enable_html'],
		'enable_smilies' 	=> $data['enable_smilies'],
		'enable_magic_url' 	=> $data['enable_urls'],
		'bbcode_uid'		=> $bbcode_uid,
		'bbcode_bitfield'	=> $data['bbcode_bitfield'],
		'post_edit_locked'	=> $data['post_edit_locked'],
		'post_text' 		=> $message
	);

	if ($mode != 'edit')
	{
		$post_sql['post_time'] = $current_time;
	}

	if ($mode != 'edit' || $data['message_md5'] != $data['post_checksum'])
	{
		$post_sql = array_merge($post_sql, array(
			'post_checksum' => $data['message_md5'],
			'post_encoding' => $user->lang['ENCODING'])
		);
	}
	
	if ($mode == 'edit')
	{
		$sql = 'UPDATE ' . POSTS_TABLE . ' 
			SET ' . $db->sql_build_array('UPDATE', $post_sql) . 
			(($data['poster_id'] == $user->data['user_id'] && $data['post_id'] != $data['topic_last_post_id']) ? ' , post_edit_count = post_edit_count + 1' : '') . '
			WHERE post_id = ' . $data['post_id'];
	}
	else
	{
		$sql = 'INSERT INTO ' . POSTS_TABLE . ' ' . 
			$db->sql_build_array('INSERT', $post_sql);
	}
	$db->sql_query($sql);

	$data['post_id'] = ($mode == 'edit') ? $data['post_id'] : $db->sql_nextid();

	// Submit Poll
	if (!empty($poll['poll_options']))
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
				if (empty($cur_poll_options[$i]))
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
	if (count($attach_data) && !empty($data['post_id']) && ($mode == 'post' || $mode == 'reply' || $mode == 'edit'))
	{
		foreach ($attach_data as $attach_row)
		{
			if ($attach_row['attach_id'] != '-1')
			{
				// update entry in db if attachment already stored in db and filespace
				$attach_sql = array(
					'comment' => trim($attach_row['comment'])
				);
			
				$sql = 'UPDATE ' . ATTACHMENTS_DESC_TABLE . ' 
					SET ' . $db->sql_build_array('UPDATE', $attach_sql) . ' 
					WHERE attach_id = ' . (int) $attach_row['attach_id'];
				$db->sql_query($sql);
			}
			else
			{
				// insert attachment into db 
				$attach_sql = array(
					'physical_filename'	=> $attach_row['physical_filename'],
					'real_filename'		=> $attach_row['real_filename'],
					'comment'			=> trim($attach_row['comment']),
					'extension'			=> $attach_row['extension'],
					'mimetype'			=> $attach_row['mimetype'],
					'filesize'			=> $attach_row['filesize'],
					'filetime'			=> $attach_row['filetime'],
					'thumbnail'			=> $attach_row['thumbnail']
				);

				$sql = 'INSERT INTO ' . ATTACHMENTS_DESC_TABLE . ' ' . 
					$db->sql_build_array('INSERT', $attach_sql);
				$db->sql_query($sql);

				$attach_sql = array(
					'attach_id'		=> $db->sql_nextid(),
					'post_id'		=> $data['post_id'],
					'privmsgs_id'	=> 0,
					'user_id_from'	=> $poster_id,
					'user_id_to'	=> 0
				);

				$sql = 'INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . 
					$db->sql_build_array('INSERT', $attach_sql);
				$db->sql_query($sql);
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
	}

	// Fulltext parse
	if ($data['message_md5'] != $data['post_checksum'])
	{
		$search = new fulltext_search();
		$result = $search->add($mode, $data['post_id'], $message, $subject);
	}

	// Sync forums, topics and users ...
	if ($mode != 'edit')
	{
		if ($topic_type != POST_GLOBAL)
		{
			$forum_topics_sql = ($mode == 'post') ? ', forum_topics = forum_topics + 1, forum_topics_real = forum_topics_real + 1' : '';

			$forum_sql = array(
				'forum_last_post_id' 	=> $data['post_id'],
				'forum_last_post_time' 	=> $current_time,
				'forum_last_poster_id' 	=> $poster_id,
				'forum_last_poster_name'=> $stat_username,
			);

			$sql = 'UPDATE ' . FORUMS_TABLE . ' 
				SET ' . $db->sql_build_array('UPDATE', $forum_sql) . ', forum_posts = forum_posts + 1' . $forum_topics_sql . ' 
				WHERE forum_id = ' . $data['forum_id'];
			$db->sql_query($sql);
		}

		// Update topic: first/last post info, replies
		$topic_sql = array(
			'topic_last_post_id' 	=> $data['post_id'],
			'topic_last_post_time' 	=> $current_time,
			'topic_last_poster_id' 	=> $poster_id,
			'topic_last_poster_name'=> $stat_username
		);

		if ($mode == 'post')
		{
			$topic_sql = array_merge($topic_sql, array(
				'topic_first_post_id' 		=> $data['post_id'])
			);
		}

		$topic_replies_sql = ($mode == 'reply' || $mode == 'quote') ? ', topic_replies = topic_replies + 1, topic_replies_real = topic_replies_real + 1' : '';
		$sql = 'UPDATE ' . TOPICS_TABLE . ' 
			SET ' . $db->sql_build_array('UPDATE', $topic_sql) . $topic_replies_sql . ' 
			WHERE topic_id = ' . $data['topic_id'];
		$db->sql_query($sql);

		// Update user post count ... if appropriate
		if ($user->data['user_id'] != ANONYMOUS && $auth->acl_get('f_postcount', $data['forum_id']))
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_posts = user_posts + 1
				WHERE user_id = ' . intval($user->data['user_id']);
			$db->sql_query($sql);
		}

		// post counts for index, etc.
		if ($mode == 'post')
		{
			set_config('num_topics', $config['num_topics'] + 1, TRUE);
		}

		set_config('num_posts', $config['num_posts'] + 1, TRUE);
	}
	else if ($mode == 'edit' && $data['post_id'] == $data['topic_last_post_id'] && $poster_id == ANONYMOUS)
	{
		$sql = 'UPDATE ' . TOPICS_TABLE . "
			SET topic_last_poster_name = '$stat_username'
			WHERE topic_id = " . $data['topic_id'];
		$db->sql_query($sql);
	}

	// Topic Notification
	if (($data['notify_set'] == 0 || $data['notify_set'] == -1) && $data['notify'])
	{
		$sql = 'INSERT INTO ' . TOPICS_WATCH_TABLE . ' (user_id, topic_id)
			VALUES (' . $user->data['user_id'] . ', ' . $data['topic_id'] . ')';
		$db->sql_query($sql);
	}
	else if ($data['notify_set'] == 1 && !$data['notify'])
	{
		$sql = 'DELETE FROM ' . TOPICS_WATCH_TABLE . '
			WHERE user_id = ' . $user->data['user_id'] . '
				AND topic_id = ' . $data['topic_id'];
		$db->sql_query($sql);
	}
		
	// Mark this topic as read and posted to.
	$mark_mode = ($mode == 'post' || $mode == 'reply' || $mode == 'quote') ? 'post' : 'topic';
	markread($mark_mode, $data['forum_id'], $data['topic_id'], $data['post_time']);

	$db->sql_transaction('commit');

	// Send Notifications
	if ($mode != 'edit' && $mode != 'delete')
	{
		user_notification($mode, stripslashes($subject), $data['forum_id'], $data['topic_id'], $data['post_id']);
	}

	meta_refresh(3, "viewtopic.$phpEx$SID&amp;f=" . $data['forum_id'] . '&amp;t=' . $data['topic_id'] . '&amp;p=' . $data['post_id'] . '#' . $data['post_id']);

	$message = ($auth->acl_get('f_moderate', $data['forum_id'])) ? 'POST_STORED_MOD' : 'POST_STORED';
	$message = $user->lang[$message] . '<br /><br />' . sprintf($user->lang['VIEW_MESSAGE'], '<a href="viewtopic.' . $phpEx . $SID .'&amp;f=' . $data['forum_id'] . '&amp;t=' . $data['topic_id'] . '&amp;p=' . $data['post_id'] . '#' . $data['post_id'] . '">', '</a>') . '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="viewforum.' . $phpEx . $SID .'&amp;f=' . $data['forum_id'] . '">', '</a>');
	trigger_error($message);
}

// User Notification
function user_notification($mode, $subject, $forum_id, $topic_id, $post_id)
{
	global $db, $user, $config, $phpEx;

	$topic_notification = ($mode == 'reply' || $mode == 'quote') ? true : false;
	$newtopic_notification = ($mode == 'post') ? true : false;

	if (empty($censors))
	{
		$censors = array();
		obtain_word_list($censors);
	}

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

	$notify_rows = $allowed_users = $user_ids = $delete_ids = array();



	//
	if ($topic_notification)
	{
		$sql = 'SELECT u.user_id, u.username, u.user_email, u.user_lang, t.topic_title, f.forum_name
			FROM ' . TOPICS_WATCH_TABLE . ' tw, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u, ' . FORUMS_TABLE . " f
			WHERE tw.topic_id = $topic_id
				AND tw.user_id NOT IN ($sql_ignore_users) 
				AND tw.notify_status = 0
				AND f.forum_id = $forum_id
				AND t.topic_id = tw.topic_id 
				AND u.user_id = tw.user_id";
	}
	else if ($newtopic_notification)
	{
		$sql = 'SELECT u.user_id, u.username, u.user_email, u.user_lang, f.forum_name 
			FROM ' . USERS_TABLE . ' u, ' . FORUMS_WATCH_TABLE . ' fw, ' . FORUMS_TABLE . " f 
			WHERE fw.forum_id = $forum_id
				AND fw.user_id NOT IN ($sql_ignore_users) 
				AND fw.notify_status = 0
				AND f.forum_id = fw.forum_id
				AND u.user_id = fw.user_id";
	}
	else
	{
		trigger_error('WRONG_NOTIFICATION_MODE');
	}
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		if ($topic_notification)
		{
			decode_text($row['topic_title']);
			$topic_title = (sizeof($censors)) ? preg_replace($censors['match'], $censors['replace'], $row['topic_title']) : $row['topic_title'];
		}
		else
		{
			decode_text($subject);
			$topic_title = (sizeof($censors)) ? preg_replace($censors['match'], $censors['replace'], $subject) : $subject;
			$forum_name = $row['forum_name'];
		}

		do
		{
			$user_ids[] = $row['user_id'];
			$notify_rows[] = $row;
		}
		while ($row = $db->sql_fetchrow($result));
	}
	$db->sql_freeresult($result);

	if (sizeof($user_ids))
	{
		$sql = 'SELECT a.user_id
			FROM ' . ACL_OPTIONS_TABLE . ' ao, ' . ACL_USERS_TABLE . " a 
			WHERE a.user_id IN (" . implode(', ', $user_ids) . ")
				AND ao.auth_option_id = a.auth_option_id
				AND ao.auth_option = 'f_read'
				AND a.forum_id = $forum_id";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$allowed_users[] = $row['user_id'];
		}
		$db->sql_freeresult($result);


		// Now grab group settings... 
		$sql = "SELECT ug.user_id, MIN(a.auth_setting) as min_setting
			FROM " . USER_GROUP_TABLE . " ug, " . ACL_OPTIONS_TABLE . " ao, " . ACL_GROUPS_TABLE . " a 
			WHERE ug.user_id IN (" . implode(', ', $user_ids) . ")
				AND a.group_id = ug.group_id
				AND ao.auth_option_id = a.auth_option_id 
				AND ao.auth_option = 'f_read'
				AND a.forum_id = $forum_id
				GROUP BY ug.user_id";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['min_setting'] == 1)
			{
				$allowed_users[] = $row['user_id'];
			}
		}
		$db->sql_freeresult($result);

		$allowed_users = array_unique($allowed_users);
	}
	else
	{
		return;
	}
	unset($user_ids);

	$email_users = array();
	$update_watched_sql_topic = $update_watched_sql_forum = '';
	//
				
	$which_sql = ($topic_notification) ? 'update_watched_sql_topic' : 'update_watched_sql_forum';

	foreach ($notify_rows as $row)
	{
		if (trim($row['user_email']) != '' && in_array($row['user_id'], $allowed_users))
		{
			$row['email_template'] = ($topic_notification) ? 'topic_notify' : 'newtopic_notify';
			$email_users[] = $row;

			$$which_sql .= ($$which_sql != '') ? ', ' . $row['user_id'] : $row['user_id'];
		}
		else
		{
			$delete_ids[] = $row['user_id'];
		}
	}
	
	// Handle remaining Notifications (Forum)
	if ($topic_notification)
	{
		$already_notified = ($update_watched_sql_topic == '') ? '' : $update_watched_sql_topic . ', ';
		$already_notified .= ($update_watched_sql_forum == '') ? '' : $update_watched_sql_forum . ', ';

		$sql = 'SELECT u.user_id, u.username, u.user_email, u.user_lang, t.topic_title, f.forum_name 
			FROM ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u, ' . FORUMS_WATCH_TABLE . ' fw, ' . FORUMS_TABLE . " f 
			WHERE fw.forum_id = $forum_id
				AND fw.user_id NOT IN ($already_notified " . ((sizeof($delete_ids)) ? implode(',', $delete_ids) . ',' : '') . " $sql_ignore_users) 
				AND fw.notify_status = 0
				AND t.topic_id = $topic_id
				AND f.forum_id = fw.forum_id
				AND u.user_id = fw.user_id";
		$result = $db->sql_query($sql);
			
		if ($row = $db->sql_fetchrow($result))
		{
			$forum_name = $row['forum_name'];

			do
			{
				if (trim($row['user_email']) != '' && in_array($row['user_id'], $allowed_users))
				{
					$row['email_template'] = 'forum_notify';
					$email_users[] = $row;

					$update_watched_sql_forum .= ($update_watched_sql_forum != '') ? ', ' . $row['user_id'] : $row['user_id'];
				}
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);
	}

	// We are using an email queue here, no emails are sent now, only queued.
	if (sizeof($email_users) && $config['email_enable'])
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
		@set_time_limit(60);

		include($phpbb_root_path . 'includes/emailer.'.$phpEx);
		$emailer = new emailer(true); // use queue

		$email_list_ary = array();
		foreach ($email_users as $row)
		{ 
			$pos = sizeof($email_list_ary[$row['email_template']]);
			$email_list_ary[$row['email_template']][$pos]['email'] = $row['user_email'];
			$email_list_ary[$row['email_template']][$pos]['name'] = $row['username'];
			$email_list_ary[$row['email_template']][$pos]['lang'] = $row['user_lang'];
		}
		unset($email_users);

		foreach ($email_list_ary as $email_template => $email_list)
		{
			foreach ($email_list as $addr)
			{
				$emailer->template($email_template, $addr['lang']);

				$emailer->replyto($config['board_email']);
				$emailer->to($addr['email'], $addr['name']);

				$emailer->assign_vars(array(
					'EMAIL_SIG'		=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),
					'SITENAME'		=> $config['sitename'],
					'TOPIC_TITLE'	=> trim($topic_title),  
					'FORUM_NAME'	=> trim($forum_name), 

					'U_TOPIC'				=> generate_board_url() . '/viewtopic.'.$phpEx . '?t=' . $topic_id . '&p=' . $post_id . '#' . $post_id,
					'U_FORUM'				=> generate_board_url() . '/viewforum.'.$phpEx . '?f=' . $forum_id,
					'U_STOP_WATCHING_TOPIC' => generate_board_url() . '/viewtopic.'.$phpEx . '?t=' . $topic_id . '&unwatch=topic',
					'U_STOP_WATCHING_FORUM' => generate_board_url() . '/viewforum.'.$phpEx . '?f=' . $forum_id . '&unwatch=forum')
				);

				$emailer->send();
				$emailer->reset();
			}
		}
	
		$emailer->mail_queue->save();
	}
	unset($email_list_ary);
	
	if ($update_watched_sql_topic != '')
	{
		$sql = 'UPDATE ' . TOPICS_WATCH_TABLE . "
			SET notify_status = 1
			WHERE topic_id = $topic_id
				AND user_id IN (" . $update_watched_sql_topic . ")";
		$db->sql_query($sql);
	}

	if ($update_watched_sql_forum != '')
	{
		$sql = 'UPDATE ' . FORUMS_WATCH_TABLE . "
			SET notify_status = 1
			WHERE forum_id = $forum_id
				AND user_id IN (" . $update_watched_sql_forum . ")";
		$db->sql_query($sql);
	}

	if (sizeof($delete_ids))
	{
		$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
			WHERE topic_id = $topic_id
				AND user_id IN (" . implode(', ', $delete_ids) . ")";
		$db->sql_query($sql);
	}
}

// Topic Review
function topic_review($topic_id, $forum_id, $is_inline_review = false)
{
	global $template;

	if ($is_inline_review)
	{
		$template->assign_vars(array(
			'S_DISPLAY_INLINE'	=> true)
		);

		return;
	}

	global $user, $auth, $db, $template, $bbcode;
	global $censors, $config, $phpbb_root_path, $phpEx, $SID;

	// Define censored word matches
	if (empty($censors))
	{
		$censors = array();
		obtain_word_list($censors);
	}

	// Get topic info ...
	$sql = 'SELECT t.topic_title, f.forum_id, f.forum_style 
		FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
		WHERE t.topic_id = $topic_id
			AND f.forum_id IN (t.forum_id, $forum_id)";
	$result = $db->sql_query($sql);

	if (!($row = $db->sql_fetchrow($result)))
	{
		trigger_error($user->lang['NO_TOPIC']);
	}

	$forum_id = $row['forum_id'];
	$topic_title = $row['topic_title'];

	$user->setup(false, $row['forum_style']);

	if (!$auth->acl_get('f_read', $forum_id))
	{
		trigger_error($user->lang['SORRY_AUTH_READ']);
	}

	if (count($censors['match']))
	{
		$topic_title = preg_replace($censors['match'], $censors['replace'], $topic_title);
	}

	$page_title = $user->lang['TOPIC_REVIEW'] . ' - ' . $topic_title;

	// Go ahead and pull all data for this topic
	$sql = 'SELECT u.username, u.user_id, p.post_id, p.post_username, p.post_subject, p.post_text, p.enable_smilies, p.bbcode_uid, p.bbcode_bitfield, p.post_time
		FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
		WHERE p.topic_id = $topic_id
			AND p.poster_id = u.user_id
			" . ((!$auth->acl_get('m_approve', $forum_id)) ? 'AND p.post_approved = 1' : '') . '
		ORDER BY p.post_time DESC';
	$result = $db->sql_query_limit($sql, $config['posts_per_page']);

	// Okay, let's do the loop, yeah come on baby let's do the loop
	// and it goes like this ...
	if (!$row = $db->sql_fetchrow($result))
	{
		trigger_error($user->lang['NO_TOPIC']);
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
		if ($poster_id == ANONYMOUS && $row['post_username'] != '')
		{
			$poster = $row['post_username'];
			$poster_rank = $user->lang['GUEST'];
		}

		$post_subject = ($row['post_subject'] != '') ? $row['post_subject'] : '';
		$message = (empty($row['enable_smilies']) || empty($config['allow_smilies'])) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $row['post_text']) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $phpbb_root_path . $config['smilies_path'], $row['post_text']);

		if ($row['bbcode_bitfield'])
		{
			$bbcode->bbcode_second_pass(&$message, $row['bbcode_uid'], $row['bbcode_bitfield']);
		}

		if (count($censors['match']))
		{
			$post_subject = preg_replace($censors['match'], $censors['replace'], $post_subject);
			$message = preg_replace($censors['match'], $censors['replace'], $message);
		}

		$template->assign_block_vars('postrow', array(
			'MINI_POST_IMG' => $user->img('icon_post', $user->lang['POST']),
			'POSTER_NAME' 	=> $poster,
			'POST_DATE' 	=> $user->format_date($row['post_time']),
			'POST_SUBJECT' 	=> $post_subject,
			'POST_ID'		=> $row['post_id'],
			'MESSAGE' 		=> str_replace("\n", '<br />', $message), 

			'U_QUOTE'		=> ($auth->acl_get('f_quote', $forum_id)) ? "javascript:addquote(" . $row['post_id'] . ", '$poster')" : '', 

			'S_ROW_COUNT'	=> $i)
		);
		unset($rowset[$i]);
	}

	//
	$template->assign_var('QUOTE_IMG', $user->img('btn_quote', $user->lang['QUOTE_POST']));

	//
	page_header($page_title);

	$template->set_filenames(array(
		'body' => 'posting_topic_review.html')
	);

	page_footer();
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

//
// FUNCTIONS
// ---------

?>