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
// * hidden form element containing sid to prevent remote posting - Edwin van Vliet
// * bbcode parsing -> see functions_posting.php
// * multichoice polls
// * permission defined ability for user to add poll options
// * Spellcheck? aspell? or some such?
// * Posting approval

define('IN_PHPBB', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_posting.'.$phpEx);
include($phpbb_root_path . 'includes/message_parser.'.$phpEx);

// Start session management
$user->start();
$auth->acl($user->data);
$user->setup();

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

$refresh = isset($_POST['add_file']) || isset($_POST['delete_file']) || isset($_POST['edit_comment']);

if (($delete) && (!$preview) && (!$refresh) && ($submit))
{
	$mode = 'delete';
}

// Was cancel pressed? If so then redirect to the appropriate page
if ($cancel || time() - $lastclick < 2)
{
	$redirect = ($post_id) ? "viewtopic.$phpEx$SID&p=" . $post_id . "#" . $post_id : (($topic_id) ? "viewtopic.$phpEx$SID&t=" . $topic_id : (($forum_id) ? "viewforum.$phpEx$SID&f=" . $forum_id : "index.$phpEx$SID"));
	redirect($redirect);
}

// What is all this following SQL for? Well, we need to know
// some basic information in all cases before we do anything.
$forum_validate = $topic_validate = $post_validate = false;

// Easier validation
$forum_fields = array('forum_name' => 's', 'parent_id' => 'i', 'forum_parents' => 's', 'forum_status' => 'i', 'forum_type' => 'i', 'enable_icons' => 'i');

$topic_fields = array('topic_status' => 'i', 'topic_first_post_id' => 'i', 'topic_last_post_id' => 'i', 'topic_type' => 'i', 'topic_title' => 's', 'poll_last_vote' => 'i', 'poll_start' => 'i', 'poll_title' => 's', 'poll_max_options' => 'i', 'poll_length' => 'i');

$post_fields = array('post_time' => 'i', 'poster_id' => 'i', 'post_username' => 's', 'post_text' => 's', 'post_subject' => 's', 'post_checksum' => 's', 'post_attachment' => 'i', 'bbcode_uid' => 's', 'enable_magic_url' => 'i', 'enable_sig' => 'i', 'enable_smilies' => 'i', 'enable_bbcode' => 'i', 'post_edit_locked' => 'i');

$sql = '';
switch ($mode)
{
	case 'post':
		if (!$forum_id)
		{
			trigger_error($user->lang['NO_FORUM']);
		}

		$sql = "SELECT *
			FROM " . FORUMS_TABLE . "
			WHERE forum_id = " . $forum_id;

		$forum_validate = true;
		break;

	case 'reply':
		if (!$topic_id)
		{
			trigger_error($user->lang['NO_TOPIC']);
		}

		$sql = "SELECT t.*, f.*
			FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f
			WHERE t.topic_id = " . $topic_id . "
				AND f.forum_id = t.forum_id";

		$forum_validate = $topic_validate = true;
		break;
		
	case 'quote':
	case 'edit':
	case 'delete':
		if (!$post_id)
		{
			trigger_error($user->lang['NO_POST']);
		}

		$sql = "SELECT p.*, t.*, f.*, u.username
			FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f, " . USERS_TABLE . " u
			WHERE p.post_id = " . $post_id . "
				AND t.topic_id = p.topic_id
				AND u.user_id = p.poster_id
				AND f.forum_id = t.forum_id";
		$forum_validate = $topic_validate = $post_validate = true;
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

$message_parser = new parse_message(0); // <- TODO: add constant (MSG_POST/MSG_PM)

if ($sql != '')
{
	$result = $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	// temp temp temp
	$postrow = $row;
	$quote_username = (!empty($row['username'])) ? $row['username'] : $row['post_username'];

	$forum_id = intval($row['forum_id']);
	$topic_id = intval($row['topic_id']);
	$post_id = intval($row['post_id']);

	$user->setup(false, $row['forum_style']);

	if ($row['forum_password'])
	{
		login_forum_box($row);
	}

	foreach ($forum_fields as $var => $type)
	{
		switch ($type)
		{
			case 'i':
				$$var = ($forum_validate) ? intval($row[$var]) : false;
				break;
			case 's':
				$$var = ($forum_validate) ? trim($row[$var]) : '';
				break;
			default:
				$$var = '';
		}
	}

	foreach ($topic_fields as $var => $type)
	{
		switch ($type)
		{
			case 'i':
				$$var = ($topic_validate) ? intval($row[$var]) : false;
				break;
			case 's':
				$$var = ($topic_validate) ? trim($row[$var]) : '';
				break;
			default:
				$$var = '';
		}
	}

	foreach ($post_fields as $var => $type)
	{
		switch ($type)
		{
			case 'i':
				$$var = ($post_validate) ? intval($row[$var]) : false;
				break;
			case 's':
				$$var = ($post_validate) ? trim($row[$var]) : '';
				break;
			default:
				$$var = '';
		}
	}
	$post_subject = ($post_validate) ? $post_subject : $topic_title;

	$poll_length = ($poll_length) ? $poll_length/3600 : $poll_length;
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

	$message_parser->filename_data['filecomment'] = (isset($_POST['filecomment'])) ? trim(strip_tags($_POST['filecomment'])) : '';
	$message_parser->filename_data['filename'] = ($_FILES['fileupload']['name'] != 'none') ? trim($_FILES['fileupload']['name']) : '';

	// Get Attachment Data
	$message_parser->attachment_data = (isset($_POST['attachment_data'])) ? $_POST['attachment_data'] : array();
	
	if ($post_attachment && !$submit && !$refresh && !$preview && $mode == 'edit')
	{
		$sql = 'SELECT d.*
			FROM ' . ATTACHMENTS_TABLE . ' a, ' . ATTACHMENTS_DESC_TABLE . ' d
			WHERE a.post_id = ' . $post_id . '
				AND a.attach_id = d.attach_id
			ORDER BY d.filetime ' . ((!$config['display_order']) ? 'DESC' : 'ASC');
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
		$enable_sig = (intval($config['allow_sig']) && $user->data['user_attachsig']) ? true : false;
		$enable_smilies = (intval($config['allow_smilies']) && $user->data['user_allowsmile']) ? true : false;
		$enable_bbcode = (intval($config['allow_bbcode']) && $user->data['user_allowbbcode']) ? true : false;
		$enable_urls = true;
	}

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
	'm_lock'		=> $auth->acl_get('m_lock', $forum_id),
	'm_edit'		=> $auth->acl_get('m_edit', $forum_id),
	'm_delete'		=> $auth->acl_get('m_delete', $forum_id),

	'u_delete'		=> $auth->acl_get('f_delete', $forum_id),

	'f_attach'		=> $auth->acl_get('f_attach', $forum_id),
	'f_news'		=> $auth->acl_get('f_news', $forum_id),
	'f_announce'	=> $auth->acl_get('f_announce', $forum_id),
	'f_sticky'		=> $auth->acl_get('f_sticky', $forum_id),
	'f_ignoreflood' => $auth->acl_get('f_ignoreflood', $forum_id),
	'f_sigs'		=> $auth->acl_get('f_sigs', $forum_id),
	'f_save'		=> $auth->acl_get('f_save', $forum_id)
);

if (!$auth->acl_get('f_' . $mode, $forum_id) && $forum_type == FORUM_POST)
{
	trigger_error($user->lang['USER_CANNOT_' . strtoupper($mode)]);
}

// Forum/Topic locked?
if (($forum_status == ITEM_LOCKED || $topic_status == ITEM_LOCKED) && !$perm['m_edit'])
{
	$message = ($forum_status == ITEM_LOCKED) ? 'FORUM_LOCKED' : 'TOPIC_LOCKED';
	trigger_error($user->lang[$message]);
}

// Can we edit this post?
if (($mode == 'edit' || $mode == 'delete') && !$perm['m_edit'] && $config['edit_time'] && $post_time < time() - $config['edit_time'])
{
	trigger_error($user->lang['CANNOT_EDIT_TIME']);
}

// Do we want to edit our post ?
if ($mode == 'edit' && !$perm['m_edit'] && $user->data['user_id'] != $poster_id)
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
if ($mode == 'delete' && (($poster_id == $user->data['user_id'] && $user->data['user_id'] != ANONYMOUS && $perm['u_delete'] && $post_id == $topic_last_post_id) || $perm['m_delete']))
{
	// Do we need to confirm ?
	if ($confirm)
	{
		$post_data = array(
			'topic_first_post_id' => $topic_first_post_id,
			'topic_last_post_id' => $topic_last_post_id,
			'user_id' => $poster_id
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
			FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
			WHERE p.topic_id = ' . $topic_id . ' 
				AND p.poster_id = u.user_id 
				AND p.post_approved = 1
			ORDER BY p.post_time DESC';
		$result = $db->sql_query_limit($sql, 1);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// If Post is first post, but not the only post... make next post the topic starter one. ;)
		if ($post_data['topic_first_post_id'] != $post_data['topic_last_post_id'] && $post_id == $post_data['topic_first_post_id'])
		{
			$topic_sql = array(
				'topic_first_post_id'		=> intval($row['post_id']),
				'topic_first_poster_name'	=> ($row['poster_id'] == ANONYMOUS) ? trim($row['post_username']) : trim($row['username'])
			);
		}

		$post_data['next_post_id'] = $row['post_id'];

		// Update Forum, Topic and User with the gathered Informations
		if ($forum_update_sql != '')
		{
			$sql = 'UPDATE ' . FORUMS_TABLE . ' 
				SET ' . $forum_update_sql . ' 
				WHERE forum_id = ' . $forum_id;
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
		$s_hidden_fields = '<input type="hidden" name="p" value="' . $post_id . '" /><input type="hidden" name="mode" value="delete" />';

		page_header($user->lang['DELETE_MESSAGE']);

		$template->set_filenames(array(
			'body' => 'confirm_body.html')
		);

		$template->assign_vars(array(
			'MESSAGE_TITLE'		=> $user->lang['DELETE_MESSAGE'],
			'MESSAGE_TEXT'		=> $user->lang['CONFIRM_DELETE'],

			'S_CONFIRM_ACTION'	=> $phpbb_root_path . 'posting.' . $phpEx . $SID,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields)
		);
		
		page_footer();
	}
}

if ($mode == 'delete' && $poster_id != $user->data['user_id'] && !$perm['u_delete'])
{
	trigger_error($user->lang['DELETE_OWN_POSTS']);
}

if ($mode == 'delete' && $poster_id == $user->data['user_id'] && $perm['u_delete'] && $post_id != $topic_last_post_id)
{
	trigger_error($user->lang['CANNOT_DELETE_REPLIED']);
}

if ($mode == 'delete')
{
	trigger_error('USER_CANNOT_DELETE');
}

// HTML, BBCode, Smilies, Images and Flash status
$html_status = (intval($config['allow_html']) && $auth->acl_get('f_html', $forum_id)) ? true : false;
$bbcode_status = (intval($config['allow_bbcode']) && $auth->acl_get('f_bbcode', $forum_id)) ? true : false;
$smilies_status = (intval($config['allow_smilies']) && $auth->acl_get('f_smilies', $forum_id)) ? true : false;
$img_status = (intval($config['allow_img']) && $auth->acl_get('f_img', $forum_id)) ? true : false;
$flash_status = (intval($config['allow_flash']) && $auth->acl_get('f_flash', $forum_id)) ? true : false;

if ($submit || $preview || $refresh)
{
	$topic_cur_post_id	= (isset($_POST['topic_cur_post_id'])) ? intval($_POST['topic_cur_post_id']) : false;
	$subject			= (!empty($_POST['subject'])) ? trim(htmlspecialchars(strip_tags($_POST['subject']))) : '';

	if (strcmp($subject, strtoupper($subject)) == 0 && $subject != '')
	{
		$subject = phpbb_strtolower($subject);
	}
	
	$message_parser->message = (!empty($_POST['message'])) ? trim(stripslashes($_POST['message'])) : '';
	
	$username			= (!empty($_POST['username'])) ? trim($_POST['username']) : '';
	$topic_type			= (!empty($_POST['topic_type'])) ? intval($_POST['topic_type']) : POST_NORMAL;
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

	if ($poll_delete && (($mode == 'edit' && !empty($poll_options) && empty($poll_last_vote) && $poster_id == $user->data['user_id'] && $perm['u_delete']) || $perm['m_delete']))
	{
		// Delete Poll
		$sql = 'DELETE FROM ' . POLL_OPTIONS_TABLE . '
			WHERE topic_id = ' . $topic_id;
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . POLL_VOTES_TABLE . '
			WHERE topic_id = ' . $topic_id;
		$db->sql_query($sql);

		$topic_sql = array(
			'poll_title'		=> '',
			'poll_start' 		=> 0,
			'poll_length'		=> 0,
			'poll_last_vote'	=> 0, 
			'poll_max_options'	=> 0
		);

		$sql = 'UPDATE ' . TOPICS_TABLE . ' 
			SET ' . $db->sql_build_array('UPDATE', $topic_sql) . ' 
			WHERE topic_id = ' . $topic_id;
		$db->sql_query($sql);

		$poll_title = $poll_length = $poll_option_text = $poll_max_options = '';
	}
	else
	{
		$poll_title			= (!empty($_POST['poll_title'])) ? trim($_POST['poll_title']) : '';
		$poll_length		= (!empty($_POST['poll_length'])) ? $_POST['poll_length'] : '';
		$poll_option_text	= (!empty($_POST['poll_option_text'])) ? $_POST['poll_option_text'] : '';
		$poll_max_options	= (!empty($_POST['poll_max_options'])) ? $_POST['poll_max_options'] : 1;
	}

	$err_msg = '';
	$current_time = time();

	// If replying/quoting and last post id has changed
	// give user option to continu submit or return to post
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
			FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
			WHERE p.topic_id = ' . $topic_id . '
				AND p.poster_id = u.user_id
				AND p.post_id > ' . $topic_cur_post_id . '
				AND p.post_approved = 1
			ORDER BY p.post_time DESC';
		$result = $db->sql_query_limit($sql, $config['posts_per_page']);

		if ($row = $db->sql_fetchrow($result))
		{
			$i = 0;
			do
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

				$message = $row['post_text'];

				$message = (empty($row['enable_smilies']) || empty($config['allow_smilies'])) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $message) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $phpbb_root_path . $config['smilies_path'], $message);

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
					'MESSAGE' 			=> nl2br($message),

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
	// TODO: parse message if any of enable_* switches has changed
	if ($mode != 'edit' || $message_md5 != $post_checksum)
	{
		// Parse message
		if ($result = $message_parser->parse($enable_html, $enable_bbcode, $enable_urls, $enable_smilies, $img_status, $flash_status))
		{
			$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $result;
		}
	}

	$result = $message_parser->parse_attachments($mode, $post_id, $submit, $preview, $refresh);
	
	if (count($result))
	{
		$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . implode('<br />', $result);
	}

	if ($mode != 'edit' && !$preview && !$refresh && !$perm['f_ignoreflood'])
	{
		// Flood check
		$sql = "SELECT MAX(post_time) AS last_post_time
			FROM " . POSTS_TABLE . "
			WHERE " . (($user->data['user_id'] == ANONYMOUS) ? "poster_ip = '" . $user->ip . "'" : "poster_id = " . $user->data['user_id']);
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			if (intval($row['last_post_time']) && ($current_time - intval($row['last_post_time'])) < intval($config['flood_interval']))
			{
				$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $user->lang['FLOOD_ERROR'];
			}
		}
	}

	// Validate username
	if (($username != '' && $user->data['user_id'] == ANONYMOUS) || ($mode == 'edit' && $post_username != ''))
	{
		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		$ucp = new ucp();
		$username = strip_tags(htmlspecialchars($username));
		if (($result = $ucp->validate_username($username)) != false)
		{
			$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $result;
		}
	}

	// Parse subject
	if ($subject == '' && ($mode == 'post' || ($mode == 'edit' && $topic_first_post_id == $post_id)))
	{
		$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $user->lang['EMPTY_SUBJECT'];
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
			case POST_GLOBAL:
				$auth_option = 'global';
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
	if ($err_msg == '' && $submit)
	{
		// Lock/Unlock Topic
		$change_topic_status = $topic_status;

		if ($topic_status == ITEM_LOCKED && !$topic_lock && $perm['m_lock'])
		{
			$change_topic_status = ITEM_UNLOCKED;
		}
		else if ($topic_status == ITEM_UNLOCKED && $topic_lock && $perm['m_lock'])
		{
			$change_topic_status = ITEM_LOCKED;
		}
		
		if ($change_topic_status != $topic_status)
		{
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_status = ' . $change_topic_status . '
				WHERE topic_id = ' . $topic_id . '
					AND topic_moved_id = 0';
			$db->sql_query($sql);
			
			add_log('mod', $forum_id, $topic_id, 'logm_' . (($change_topic_status == ITEM_LOCKED) ? 'lock' : 'unlock'));
		}

		// Lock/Unlock Post Edit
		if ($mode == 'edit' && $post_edit_locked == ITEM_LOCKED && !$post_lock && $perm['m_edit'])
		{
			$post_edit_locked = ITEM_UNLOCKED;
		}
		else if ($mode == 'edit' && $post_edit_locked == ITEM_UNLOCKED && $post_lock && $perm['m_edit'])
		{
			$post_edit_locked = ITEM_LOCKED;
		}

		$post_data = array(
			'topic_first_post_id'	=> $topic_first_post_id,
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

if (!$err_msg && $preview)
{
	if (empty($censors))
	{
		$censors = array();
		obtain_word_list($censors);
	}

	$post_time = $current_time;

	include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
	$bbcode = new bbcode($message_parser->bbcode_bitfield);

	$preview_message = format_display($message_parser->message, $enable_html, $enable_bbcode, $message_parser->bbcode_uid, $enable_urls, $enable_smilies, $enable_sig);
	
	$preview_subject = (sizeof($censors)) ? preg_replace($censors['match'], $censors['replace'], $subject) : $subject;

	// Poll Preview
	if ( ($mode == 'post' || ($mode == 'edit' && $post_id == $topic_first_post_id && empty($poll_last_vote))) && ($auth->acl_get('f_poll', $forum_id) || $auth->acl_get('m_edit', $forum_id)) )
	{
		decode_text($poll_title, $message_parser->bbcode_uid);
		$preview_poll_title = format_display(stripslashes($poll_title), $enable_html, $enable_bbcode, $message_parser->bbcode_uid, $enable_urls, $enable_smilies, false, false);

		$template->assign_vars(array(
			'S_HAS_POLL_OPTIONS' => (sizeof($poll_options)) ? true : false,
			'POLL_QUESTION'		 => $preview_poll_title)
		);

		foreach ($poll_options as $option)
		{
			$template->assign_block_vars('poll_option', array(
				'POLL_OPTION_CAPTION' => format_display(stripslashes($option), $enable_html, $enable_bbcode, $message_parser->bbcode_uid, $enable_urls, $enable_smilies, false, false))
			);
		}
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

if (($mode == 'quote') && (!$preview) && (!$refresh))
{
	$post_text = '[quote="' . $quote_username . '"]' . trim($post_text) . "[/quote]\n";
}

if ( (($mode == 'reply') || ($mode == 'quote')) && (!$preview) && (!$refresh))
{
	$post_subject = ( ( !preg_match('/^Re:/', $post_subject) ) ? 'Re: ' : '' ) . $post_subject;
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
if ( ($mode == 'post') || (($mode == 'edit') && ($post_id == $topic_first_post_id)) )
{
	$topic_types = array(
		'sticky' => array('const' => POST_STICKY, 'lang' => 'POST_STICKY'),
		'announce' => array('const' => POST_ANNOUNCE, 'lang' => 'POST_ANNOUNCEMENT')
//		'global' => array('const' => POST_GLOBAL, 'lang' => 'POST_GLOBAL')
	);
	
	foreach ($topic_types as $auth_key => $topic_value)
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

$html_checked = (isset($enable_html)) ? !$enable_html : ((intval($config['allow_html'])) ? !$user->data['user_allowhtml'] : 1);
$bbcode_checked = (isset($enable_bbcode)) ? !$enable_bbcode : ((intval($config['allow_bbcode'])) ? !$user->data['user_allowbbcode'] : 1);
$smilies_checked = (isset($enable_smilies)) ? !$enable_smilies : ((intval($config['allow_smilies'])) ? !$user->data['user_allowsmile'] : 1);
$urls_checked = (isset($enable_urls)) ? !$enable_urls : 0;
$sig_checked = $enable_sig;
$notify_checked = (isset($notify_set)) ? $notify_set : (($user->data['user_id'] != ANONYMOUS) ? $user->data['user_notify'] : 0);
$lock_topic_checked = (isset($topic_lock)) ? $topic_lock : (($topic_status == ITEM_LOCKED) ? 1 : 0);
$lock_post_checked = (isset($post_lock)) ? $post_lock : $post_edit_locked;

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

$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off' || @ini_get('file_uploads') == '0' || !$config['allow_attachments']) ? '' : 'enctype="multipart/form-data"';

// Start assigning vars for main posting page ...
$template->assign_vars(array(
	'L_POST_A'				=> $page_title,
	'L_ICON'				=> ($mode == 'reply' || $mode == 'quote') ? $user->lang['POST_ICON'] : $user->lang['TOPIC_ICON'], 
	'L_MESSAGE_BODY_EXPLAIN'=> (intval($config['max_post_chars'])) ? sprintf($user->lang['MESSAGE_BODY_EXPLAIN'], intval($config['max_post_chars'])) : '',

	'FORUM_NAME' 			=> $forum_name,
	'FORUM_DESC'			=> (!empty($forum_desc)) ? strip_tags($forum_desc) : '',
	'TOPIC_TITLE' 			=> $topic_title,
	'MODERATORS' 			=> (sizeof($moderators)) ? implode(', ', $moderators[$forum_id]) : '',
	'USERNAME'				=> (((!$preview) && ($mode != 'quote')) || ($preview)) ? stripslashes($username) : '',
	'SUBJECT'				=> $post_subject,
	'PREVIEW_SUBJECT'		=> ($preview && !$err_msg) ? $preview_subject : '',
	'MESSAGE'				=> trim($post_text),
	'PREVIEW_MESSAGE'		=> ($preview && !$err_msg) ? $preview_message : '',
	'HTML_STATUS'			=> ($html_status) ? $user->lang['HTML_IS_ON'] : $user->lang['HTML_IS_OFF'],
	'BBCODE_STATUS'			=> ($bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . "faq.$phpEx$SID&amp;mode=bbcode" . '" target="_phpbbcode">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . "faq.$phpEx$SID&amp;mode=bbcode" . '" target="_phpbbcode">', '</a>'),
	'IMG_STATUS'			=> ($img_status) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
	'FLASH_STATUS'			=> ($flash_status) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
	'SMILIES_STATUS'		=> ($smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
	'MINI_POST_IMG'			=> $user->img('icon_post', $user->lang['POST']),
	'POST_DATE'				=> ($post_time) ? $user->format_date($post_time) : '',
	'ERROR_MESSAGE'			=> $err_msg,

	'U_VIEW_FORUM' 			=> "viewforum.$phpEx$SID&amp;f=" . $forum_id,
	'U_VIEWTOPIC' 			=> ($mode != 'post') ? "viewtopic.$phpEx$SID&amp;" . $forum_id . "&amp;t=" . $topic_id : '',
	'U_REVIEW_TOPIC'		=> ($mode != 'post') ? "posting.$phpEx$SID&amp;mode=topicreview&amp;f=" . $forum_id . "&amp;t=" . $topic_id : '',

	'S_DISPLAY_PREVIEW'		=> ($preview && !$err_msg),
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
	'S_LOCK_POST_ALLOWED'	=> (($mode == 'edit') && ($perm['m_edit'])) ? true : false,
	'S_LOCK_POST_CHECKED'	=> ($lock_post_checked) ? 'checked="checked"' : '',
	'S_MAGIC_URL_CHECKED' 	=> ($urls_checked) ? 'checked="checked"' : '',
	'S_TYPE_TOGGLE'			=> $topic_type_toggle,
	'S_SAVE_ALLOWED'		=> ($perm['f_save']) ? true : false,
	'S_FORM_ENCTYPE'		=> $form_enctype,

	'S_POST_ACTION' 		=> $s_action,
	'S_HIDDEN_FIELDS'		=> $s_hidden_fields)
);

// Poll entry
if ( ( ($mode == 'post') || ( ($mode == 'edit') && ($post_id == $topic_first_post_id) && (empty($poll_last_vote)) )) && ( ($auth->acl_get('f_poll', $forum_id)) || ($perm['m_edit']) ))
{
	$template->assign_vars(array(
		'S_SHOW_POLL_BOX' 	=> true,
		'S_POLL_DELETE' 	=> ($mode == 'edit' && !empty($poll_options) && ((empty($poll_last_vote) && $poster_id == $user->data['user_id'] && $perm['u_delete']) || $perm['m_delete'])) ? true : false,

		'L_POLL_OPTIONS_EXPLAIN'=> sprintf($user->lang['POLL_OPTIONS_EXPLAIN'], $config['max_poll_options']),

		'POLL_TITLE' 		=> $poll_title,
		'POLL_OPTIONS'		=> (!empty($poll_options)) ? implode("\n", $poll_options) : '',
		'POLL_MAX_OPTIONS'	=> (!empty($poll_max_options)) ? $poll_max_options : 1, 
		'POLL_LENGTH' 		=> $poll_length)
	);
}

// Attachment entry
if (($perm['f_attach']) || ($perm['m_edit']))
{
	$template->assign_vars(array(
		'S_SHOW_ATTACH_BOX' => true)
	);

	if (count($message_parser->attachment_data))
	{
		$template->assign_vars(array(
			'S_HAS_ATTACHMENTS' => true)
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
			
			$download_link = ($attach_row['attach_id'] == '-1') ? $config['upload_dir'] . '/' . $attach_row['physical_filename'] : $phpbb_root_path . 'download.' . $phpEx . $SID . '&id=' . intval($attach_row['attach_id']);
				
			$template->assign_block_vars('attach_row', array(
				'FILENAME' => $attach_row['real_filename'],
				'ATTACH_FILENAME' => $attach_row['physical_filename'],
				'FILE_COMMENT' => stripslashes(htmlspecialchars($attach_row['comment'])),
				'ATTACH_ID' => $attach_row['attach_id'],
				'ASSOC_INDEX' => $count,

				'U_VIEW_ATTACHMENT' => $download_link,
				'S_HIDDEN' => $hidden)
			);

			$count++;
		}
	}

	$template->assign_vars(array(
		'FILE_COMMENT' => stripslashes(htmlspecialchars($message_parser->filename_data['filecomment'])),
		'FILESIZE' => $config['max_filesize'],
		'FILENAME' => $message_parser->filename_data['filename'])
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
	topic_review($topic_id, true);
}

page_footer();

// FUNCTIONS

// Topic Review
function topic_review($topic_id, $is_inline_review = false)
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
	$sql = "SELECT t.topic_title, f.forum_id
		FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f
		WHERE t.topic_id = $topic_id
			AND f.forum_id = t.forum_id";
	$result = $db->sql_query($sql);

	if (!($row = $db->sql_fetchrow($result)))
	{
		trigger_error($user->lang['NO_TOPIC']);
	}

	$forum_id = $row['forum_id'];
	$topic_title = $row['topic_title'];

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

		$message = $row['post_text'];

		$message = (empty($row['enable_smilies']) || empty($config['allow_smilies'])) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $message) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $phpbb_root_path . $config['smilies_path'], $message);

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

// Temp Function - strtolower (will have a look at iconv later) - borrowed from php.net
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

?>