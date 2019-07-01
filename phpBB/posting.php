<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);


// Start session management
$user->session_begin();
$auth->acl($user->data);


// Grab only parameters needed here
$post_id	= $request->variable('p', 0);
$topic_id	= $request->variable('t', 0);
$forum_id	= $request->variable('f', 0);
$draft_id	= $request->variable('d', 0);

$preview	= (isset($_POST['preview'])) ? true : false;
$save		= (isset($_POST['save'])) ? true : false;
$load		= (isset($_POST['load'])) ? true : false;
$confirm	= $request->is_set_post('confirm');
$cancel		= (isset($_POST['cancel']) && !isset($_POST['save'])) ? true : false;

$refresh	= (isset($_POST['add_file']) || isset($_POST['delete_file']) || isset($_POST['cancel_unglobalise']) || $save || $load || $preview);
$submit = $request->is_set_post('post') && !$refresh && !$preview;
$mode		= $request->variable('mode', '');

// If the user is not allowed to delete the post, we try to soft delete it, so we overwrite the mode here.
if ($mode == 'delete' && (($confirm && !$request->is_set_post('delete_permanent')) || !$auth->acl_gets('f_delete', 'm_delete', $forum_id)))
{
	$mode = 'soft_delete';
}

$error = $post_data = array();
$current_time = time();

/**
* This event allows you to alter the above parameters, such as submit and mode
*
* Note: $refresh must be true to retain previously submitted form data.
*
* Note: The template class will not work properly until $user->setup() is
* called, and it has not been called yet. Extensions requiring template
* assignments should use an event that comes later in this file.
*
* @event core.modify_posting_parameters
* @var	int		post_id		ID of the post
* @var	int		topic_id	ID of the topic
* @var	int		forum_id	ID of the forum
* @var	int		draft_id	ID of the draft
* @var	bool	submit		Whether or not the form has been submitted
* @var	bool	preview		Whether or not the post is being previewed
* @var	bool	save		Whether or not a draft is being saved
* @var	bool	load		Whether or not a draft is being loaded
* @var	bool	cancel		Whether or not to cancel the form (returns to
*							viewtopic or viewforum depending on if the user
*							is posting a new topic or editing a post)
* @var	bool	refresh		Whether or not to retain previously submitted data
* @var	string	mode		What action to take if the form has been submitted
*							post|reply|quote|edit|delete|bump|smilies|popup
* @var	array	error		Any error strings; a non-empty array aborts
*							form submission.
*							NOTE: Should be actual language strings, NOT
*							language keys.
* @since 3.1.0-a1
* @changed 3.1.2-RC1			Removed 'delete' var as it does not exist
* @changed 3.2.4-RC1		Remove unused 'lastclick' var
*/
$vars = array(
	'post_id',
	'topic_id',
	'forum_id',
	'draft_id',
	'submit',
	'preview',
	'save',
	'load',
	'cancel',
	'refresh',
	'mode',
	'error',
);
extract($phpbb_dispatcher->trigger_event('core.modify_posting_parameters', compact($vars)));

// Was cancel pressed? If so then redirect to the appropriate page
if ($cancel)
{
	$f = ($forum_id) ? 'f=' . $forum_id . '&amp;' : '';
	$redirect = ($post_id) ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", $f . 'p=' . $post_id) . '#p' . $post_id : (($topic_id) ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", $f . 't=' . $topic_id) : (($forum_id) ? append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id) : append_sid("{$phpbb_root_path}index.$phpEx")));
	redirect($redirect);
}

if (in_array($mode, array('post', 'reply', 'quote', 'edit', 'delete')) && !$forum_id)
{
	trigger_error('NO_FORUM');
}

/* @var $phpbb_content_visibility \phpbb\content_visibility */
$phpbb_content_visibility = $phpbb_container->get('content.visibility');

// We need to know some basic information in all cases before we do anything.
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

		// Force forum id
		$sql = 'SELECT forum_id
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . $topic_id;
		$result = $db->sql_query($sql);
		$f_id = (int) $db->sql_fetchfield('forum_id');
		$db->sql_freeresult($result);

		$forum_id = (!$f_id) ? $forum_id : $f_id;

		$sql = 'SELECT f.*, t.*
			FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
			WHERE t.topic_id = $topic_id
				AND f.forum_id = t.forum_id
				AND " . $phpbb_content_visibility->get_visibility_sql('topic', $forum_id, 't.');
	break;

	case 'quote':
	case 'edit':
	case 'delete':
	case 'soft_delete':
		if (!$post_id)
		{
			$user->setup('posting');
			trigger_error('NO_POST');
		}

		// Force forum id
		$sql = 'SELECT forum_id
			FROM ' . POSTS_TABLE . '
			WHERE post_id = ' . $post_id;
		$result = $db->sql_query($sql);
		$f_id = (int) $db->sql_fetchfield('forum_id');
		$db->sql_freeresult($result);

		$forum_id = (!$f_id) ? $forum_id : $f_id;

		$sql = 'SELECT f.*, t.*, p.*, u.username, u.username_clean, u.user_sig, u.user_sig_bbcode_uid, u.user_sig_bbcode_bitfield
			FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f, ' . USERS_TABLE . " u
			WHERE p.post_id = $post_id
				AND t.topic_id = p.topic_id
				AND u.user_id = p.poster_id
				AND f.forum_id = t.forum_id
				AND " . $phpbb_content_visibility->get_visibility_sql('post', $forum_id, 'p.');
	break;

	case 'smilies':
		$sql = '';
		generate_smilies('window', $forum_id);
	break;

	case 'popup':
		if ($forum_id)
		{
			$sql = 'SELECT forum_style
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . $forum_id;
		}
		else
		{
			phpbb_upload_popup();
			return;
		}
	break;

	default:
		$sql = '';
	break;
}

if (!$sql)
{
	$user->setup('posting');
	trigger_error('NO_POST_MODE');
}

$result = $db->sql_query($sql);
$post_data = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$post_data)
{
	if (!($mode == 'post' || $mode == 'bump' || $mode == 'reply'))
	{
		$user->setup('posting');
	}
	trigger_error(($mode == 'post' || $mode == 'bump' || $mode == 'reply') ? 'NO_TOPIC' : 'NO_POST');
}

/**
* This event allows you to bypass reply/quote test of an unapproved post.
*
* @event core.posting_modify_row_data
* @var	array	post_data	All post data from database
* @var	string	mode		What action to take if the form has been submitted
*							post|reply|quote|edit|delete|bump|smilies|popup
* @var	int		topic_id	ID of the topic
* @var	int		forum_id	ID of the forum
* @since 3.2.8-RC1
*/
$vars = array(
	'post_data',
	'mode',
	'topic_id',
	'forum_id',
);
extract($phpbb_dispatcher->trigger_event('core.posting_modify_row_data', compact($vars)));

// Not able to reply to unapproved posts/topics
// TODO: add more descriptive language key
if ($auth->acl_get('m_approve', $forum_id) && ((($mode == 'reply' || $mode == 'bump') && $post_data['topic_visibility'] != ITEM_APPROVED) || ($mode == 'quote' && $post_data['post_visibility'] != ITEM_APPROVED)))
{
	trigger_error(($mode == 'reply' || $mode == 'bump') ? 'TOPIC_UNAPPROVED' : 'POST_UNAPPROVED');
}

if ($mode == 'popup')
{
	phpbb_upload_popup($post_data['forum_style']);
	return;
}

$user->setup(array('posting', 'mcp', 'viewtopic'), $post_data['forum_style']);

if ($config['enable_post_confirm'] && !$user->data['is_registered'])
{
	$captcha = $phpbb_container->get('captcha.factory')->get_instance($config['captcha_plugin']);
	$captcha->init(CONFIRM_POST);
}

// Use post_row values in favor of submitted ones...
$forum_id	= (!empty($post_data['forum_id'])) ? (int) $post_data['forum_id'] : (int) $forum_id;
$topic_id	= (!empty($post_data['topic_id'])) ? (int) $post_data['topic_id'] : (int) $topic_id;
$post_id	= (!empty($post_data['post_id'])) ? (int) $post_data['post_id'] : (int) $post_id;

// Need to login to passworded forum first?
if ($post_data['forum_password'])
{
	login_forum_box(array(
		'forum_id'			=> $forum_id,
		'forum_name'		=> $post_data['forum_name'],
		'forum_password'	=> $post_data['forum_password'])
	);
}

// Check permissions
if ($user->data['is_bot'])
{
	redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
}

// Is the user able to read within this forum?
if (!$auth->acl_get('f_read', $forum_id))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		trigger_error('USER_CANNOT_READ');
	}
	$message = $user->lang['LOGIN_EXPLAIN_POST'];

	if ($request->is_ajax())
	{
		$json = new phpbb\json_response();
		$json->send(array(
			'title'		=> $user->lang['INFORMATION'],
			'message'	=> $message,
		));
	}

	login_box('', $message);
}

// Permission to do the action asked?
$is_authed = false;

switch ($mode)
{
	case 'post':
		if ($auth->acl_get('f_post', $forum_id))
		{
			$is_authed = true;
		}
	break;

	case 'bump':
		if ($auth->acl_get('f_bump', $forum_id))
		{
			$is_authed = true;
		}
	break;

	case 'quote':

		$post_data['post_edit_locked'] = 0;

	// no break;

	case 'reply':
		if ($auth->acl_get('f_reply', $forum_id))
		{
			$is_authed = true;
		}
	break;

	case 'edit':
		if ($user->data['is_registered'] && $auth->acl_gets('f_edit', 'm_edit', $forum_id))
		{
			$is_authed = true;
		}
	break;

	case 'delete':
		if ($user->data['is_registered'] && ($auth->acl_get('m_delete', $forum_id) || ($post_data['poster_id'] == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id))))
		{
			$is_authed = true;
		}

	// no break;

	case 'soft_delete':
		if (!$is_authed && $user->data['is_registered'] && $phpbb_content_visibility->can_soft_delete($forum_id, $post_data['poster_id'], $post_data['post_edit_locked']))
		{
			// Fall back to soft_delete if we have no permissions to delete posts but to soft delete them
			$is_authed = true;
			$mode = 'soft_delete';
		}
	break;
}
/**
* This event allows you to do extra auth checks and verify if the user
* has the required permissions
*
* Extensions should only change the error and is_authed variables.
*
* @event core.modify_posting_auth
* @var	int		post_id		ID of the post
* @var	int		topic_id	ID of the topic
* @var	int		forum_id	ID of the forum
* @var	int		draft_id	ID of the draft
* @var	bool	submit		Whether or not the form has been submitted
* @var	bool	preview		Whether or not the post is being previewed
* @var	bool	save		Whether or not a draft is being saved
* @var	bool	load		Whether or not a draft is being loaded
* @var	bool	refresh		Whether or not to retain previously submitted data
* @var	string	mode		What action to take if the form has been submitted
*							post|reply|quote|edit|delete|bump|smilies|popup
* @var	array	error		Any error strings; a non-empty array aborts
*							form submission.
*							NOTE: Should be actual language strings, NOT
*							language keys.
* @var	bool	is_authed	Does the user have the required permissions?
* @var	array	post_data	All post data from database
* @since 3.1.3-RC1
* @changed 3.1.10-RC1 Added post_data
* @changed 3.2.4-RC1 		Remove unused 'lastclick' var
*/
$vars = array(
	'post_id',
	'topic_id',
	'forum_id',
	'draft_id',
	'submit',
	'preview',
	'save',
	'load',
	'refresh',
	'mode',
	'error',
	'is_authed',
	'post_data',
);
extract($phpbb_dispatcher->trigger_event('core.modify_posting_auth', compact($vars)));

if (!$is_authed || !empty($error))
{
	$check_auth = ($mode == 'quote') ? 'reply' : (($mode == 'soft_delete') ? 'delete' : $mode);

	if ($user->data['is_registered'])
	{
		trigger_error(empty($error) ? 'USER_CANNOT_' . strtoupper($check_auth) : implode('<br/>', $error));
	}
	$message = $user->lang['LOGIN_EXPLAIN_' . strtoupper($mode)];

	if ($request->is_ajax())
	{
		$json = new phpbb\json_response();
		$json->send(array(
			'title'		=> $user->lang['INFORMATION'],
			'message'	=> $message,
		));
	}

	login_box('', $message);
}

// Is the user able to post within this forum?
if ($post_data['forum_type'] != FORUM_POST && in_array($mode, array('post', 'bump', 'quote', 'reply')))
{
	trigger_error('USER_CANNOT_FORUM_POST');
}

// Forum/Topic locked?
if (($post_data['forum_status'] == ITEM_LOCKED || (isset($post_data['topic_status']) && $post_data['topic_status'] == ITEM_LOCKED)) && !$auth->acl_get('m_edit', $forum_id))
{
	trigger_error(($post_data['forum_status'] == ITEM_LOCKED) ? 'FORUM_LOCKED' : 'TOPIC_LOCKED');
}

// Can we edit this post ... if we're a moderator with rights then always yes
// else it depends on editing times, lock status and if we're the correct user
if ($mode == 'edit' && !$auth->acl_get('m_edit', $forum_id))
{
	$force_edit_allowed = false;

	$s_cannot_edit = $user->data['user_id'] != $post_data['poster_id'];
	$s_cannot_edit_time = $config['edit_time'] && $post_data['post_time'] <= time() - ($config['edit_time'] * 60);
	$s_cannot_edit_locked = $post_data['post_edit_locked'];

	/**
	* This event allows you to modify the conditions for the "cannot edit post" checks
	*
	* @event core.posting_modify_cannot_edit_conditions
	* @var	array	post_data	Array with post data
	* @var	bool	force_edit_allowed		Allow the user to edit the post (all permissions and conditions are ignored)
	* @var	bool	s_cannot_edit			User can not edit the post because it's not his
	* @var	bool	s_cannot_edit_locked	User can not edit the post because it's locked
	* @var	bool	s_cannot_edit_time		User can not edit the post because edit_time has passed
	* @since 3.1.0-b4
	*/
	$vars = array(
		'post_data',
		'force_edit_allowed',
		's_cannot_edit',
		's_cannot_edit_locked',
		's_cannot_edit_time',
	);
	extract($phpbb_dispatcher->trigger_event('core.posting_modify_cannot_edit_conditions', compact($vars)));

	if (!$force_edit_allowed)
	{
		if ($s_cannot_edit)
		{
			trigger_error('USER_CANNOT_EDIT');
		}
		else if ($s_cannot_edit_time)
		{
			trigger_error('CANNOT_EDIT_TIME');
		}
		else if ($s_cannot_edit_locked)
		{
			trigger_error('CANNOT_EDIT_POST_LOCKED');
		}
	}
}

// Handle delete mode...
if ($mode == 'delete' || $mode == 'soft_delete')
{
	if ($mode == 'soft_delete' && $post_data['post_visibility'] == ITEM_DELETED)
	{
		$user->setup('posting');
		trigger_error('NO_POST');
	}

	$delete_reason = $request->variable('delete_reason', '', true);
	phpbb_handle_post_delete($forum_id, $topic_id, $post_id, $post_data, ($mode == 'soft_delete' && !$request->is_set_post('delete_permanent')), $delete_reason);
	return;
}

// Handle bump mode...
if ($mode == 'bump')
{
	if ($bump_time = bump_topic_allowed($forum_id, $post_data['topic_bumped'], $post_data['topic_last_post_time'], $post_data['topic_poster'], $post_data['topic_last_poster_id'])
		&& check_link_hash($request->variable('hash', ''), "topic_{$post_data['topic_id']}"))
	{
		$meta_url = phpbb_bump_topic($forum_id, $topic_id, $post_data, $current_time);
		meta_refresh(3, $meta_url);
		$message = $user->lang['TOPIC_BUMPED'];

		if (!$request->is_ajax())
		{
			$message .= '<br /><br />' . $user->lang('VIEW_MESSAGE', '<a href="' . $meta_url . '">', '</a>');
			$message .= '<br /><br />' . $user->lang('RETURN_FORUM', '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id) . '">', '</a>');
		}

		trigger_error($message);
	}

	trigger_error('BUMP_ERROR');
}

// Subject length limiting to 60 characters if first post...
if ($mode == 'post' || ($mode == 'edit' && $post_data['topic_first_post_id'] == $post_data['post_id']))
{
	$template->assign_var('S_NEW_MESSAGE', true);
}

// Determine some vars
if (isset($post_data['poster_id']) && $post_data['poster_id'] == ANONYMOUS)
{
	$post_data['quote_username'] = (!empty($post_data['post_username'])) ? $post_data['post_username'] : $user->lang['GUEST'];
}
else
{
	$post_data['quote_username'] = isset($post_data['username']) ? $post_data['username'] : '';
}

$post_data['post_edit_locked']	= (isset($post_data['post_edit_locked'])) ? (int) $post_data['post_edit_locked'] : 0;
$post_data['post_subject_md5']	= (isset($post_data['post_subject']) && $mode == 'edit') ? md5($post_data['post_subject']) : '';
$post_data['post_subject']		= (in_array($mode, array('quote', 'edit'))) ? $post_data['post_subject'] : ((isset($post_data['topic_title'])) ? $post_data['topic_title'] : '');
$post_data['topic_time_limit']	= (isset($post_data['topic_time_limit'])) ? (($post_data['topic_time_limit']) ? (int) $post_data['topic_time_limit'] / 86400 : (int) $post_data['topic_time_limit']) : 0;
$post_data['poll_length']		= (!empty($post_data['poll_length'])) ? (int) $post_data['poll_length'] / 86400 : 0;
$post_data['poll_start']		= (!empty($post_data['poll_start'])) ? (int) $post_data['poll_start'] : 0;
$post_data['icon_id']			= (!isset($post_data['icon_id']) || in_array($mode, array('quote', 'reply'))) ? 0 : (int) $post_data['icon_id'];
$post_data['poll_options']		= array();

// Get Poll Data
if ($post_data['poll_start'])
{
	$sql = 'SELECT poll_option_text
		FROM ' . POLL_OPTIONS_TABLE . "
		WHERE topic_id = $topic_id
		ORDER BY poll_option_id";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$post_data['poll_options'][] = trim($row['poll_option_text']);
	}
	$db->sql_freeresult($result);
}

/**
* This event allows you to modify the post data before parsing
*
* @event core.posting_modify_post_data
* @var	int		forum_id	ID of the forum
* @var	string	mode		What action to take if the form has been submitted
*							post|reply|quote|edit|delete|bump|smilies|popup
* @var	array	post_data	Array with post data
* @var	int		post_id		ID of the post
* @var	int		topic_id	ID of the topic
* @since 3.2.2-RC1
*/
$vars = array(
	'forum_id',
	'mode',
	'post_data',
	'post_id',
	'topic_id',
);
extract($phpbb_dispatcher->trigger_event('core.posting_modify_post_data', compact($vars)));

if ($mode == 'edit')
{
	$original_poll_data = array(
		'poll_title'		=> $post_data['poll_title'],
		'poll_length'		=> $post_data['poll_length'],
		'poll_max_options'	=> $post_data['poll_max_options'],
		'poll_option_text'	=> implode("\n", $post_data['poll_options']),
		'poll_start'		=> $post_data['poll_start'],
		'poll_last_vote'	=> $post_data['poll_last_vote'],
		'poll_vote_change'	=> $post_data['poll_vote_change'],
	);
}

$orig_poll_options_size = count($post_data['poll_options']);

$message_parser = new parse_message();
/* @var $plupload \phpbb\plupload\plupload */
$plupload = $phpbb_container->get('plupload');

/* @var $mimetype_guesser \phpbb\mimetype\guesser */
$mimetype_guesser = $phpbb_container->get('mimetype.guesser');
$message_parser->set_plupload($plupload);

if (isset($post_data['post_text']))
{
	$message_parser->message = &$post_data['post_text'];
	unset($post_data['post_text']);
}

// Set some default variables
$uninit = array('post_attachment' => 0, 'poster_id' => $user->data['user_id'], 'enable_magic_url' => 0, 'topic_status' => 0, 'topic_type' => POST_NORMAL, 'post_subject' => '', 'topic_title' => '', 'post_time' => 0, 'post_edit_reason' => '', 'notify_set' => 0);

/**
* This event allows you to modify the default variables for post_data, and unset them in post_data if needed
*
* @event core.posting_modify_default_variables
* @var	array	post_data	Array with post data
* @var	array	uninit		Array with default vars to put into post_data, if they aren't there
* @since 3.2.5-RC1
*/
$vars = array(
	'post_data',
	'uninit',
);
extract($phpbb_dispatcher->trigger_event('core.posting_modify_default_variables', compact($vars)));

foreach ($uninit as $var_name => $default_value)
{
	if (!isset($post_data[$var_name]))
	{
		$post_data[$var_name] = $default_value;
	}
}
unset($uninit);

// Always check if the submitted attachment data is valid and belongs to the user.
// Further down (especially in submit_post()) we do not check this again.
$message_parser->get_submitted_attachment_data($post_data['poster_id']);

if ($post_data['post_attachment'] && !$submit && !$refresh && !$preview && $mode == 'edit')
{
	// Do not change to SELECT *
	$sql = 'SELECT attach_id, is_orphan, attach_comment, real_filename, filesize
		FROM ' . ATTACHMENTS_TABLE . "
		WHERE post_msg_id = $post_id
			AND in_message = 0
			AND is_orphan = 0
		ORDER BY attach_id DESC";
	$result = $db->sql_query($sql);
	$message_parser->attachment_data = array_merge($message_parser->attachment_data, $db->sql_fetchrowset($result));
	$db->sql_freeresult($result);
}

if ($post_data['poster_id'] == ANONYMOUS)
{
	$post_data['username'] = ($mode == 'quote' || $mode == 'edit') ? trim($post_data['post_username']) : '';
}
else
{
	$post_data['username'] = ($mode == 'quote' || $mode == 'edit') ? trim($post_data['username']) : '';
}

$post_data['enable_urls'] = $post_data['enable_magic_url'];

if ($mode != 'edit')
{
	$post_data['enable_sig']		= ($config['allow_sig'] && $user->optionget('attachsig')) ? true: false;
	$post_data['enable_smilies']	= ($config['allow_smilies'] && $user->optionget('smilies')) ? true : false;
	$post_data['enable_bbcode']		= ($config['allow_bbcode'] && $user->optionget('bbcode')) ? true : false;
	$post_data['enable_urls']		= true;
}

if ($mode == 'post')
{
	$post_data['topic_status']		= ($request->is_set_post('lock_topic') && $auth->acl_gets('m_lock', 'f_user_lock', $forum_id)) ? ITEM_LOCKED : ITEM_UNLOCKED;
}

$post_data['enable_magic_url'] = $post_data['drafts'] = false;

// User own some drafts?
if ($user->data['is_registered'] && $auth->acl_get('u_savedrafts') && ($mode == 'reply' || $mode == 'post' || $mode == 'quote'))
{
	$sql = 'SELECT draft_id
		FROM ' . DRAFTS_TABLE . '
		WHERE user_id = ' . $user->data['user_id'] .
			(($forum_id) ? ' AND forum_id = ' . (int) $forum_id : '') .
			(($topic_id) ? ' AND topic_id = ' . (int) $topic_id : '') .
			(($draft_id) ? " AND draft_id <> $draft_id" : '');
	$result = $db->sql_query_limit($sql, 1);

	if ($db->sql_fetchrow($result))
	{
		$post_data['drafts'] = true;
	}
	$db->sql_freeresult($result);
}

$check_value = (($post_data['enable_bbcode']+1) << 8) + (($post_data['enable_smilies']+1) << 4) + (($post_data['enable_urls']+1) << 2) + (($post_data['enable_sig']+1) << 1);

// Check if user is watching this topic
if ($mode != 'post' && $config['allow_topic_notify'] && $user->data['is_registered'])
{
	$sql = 'SELECT topic_id
		FROM ' . TOPICS_WATCH_TABLE . '
		WHERE topic_id = ' . $topic_id . '
			AND user_id = ' . $user->data['user_id'];
	$result = $db->sql_query($sql);
	$post_data['notify_set'] = (int) $db->sql_fetchfield('topic_id');
	$db->sql_freeresult($result);
}

// Do we want to edit our post ?
if ($mode == 'edit' && $post_data['bbcode_uid'])
{
	$message_parser->bbcode_uid = $post_data['bbcode_uid'];
}

// HTML, BBCode, Smilies, Images and Flash status
$bbcode_status	= ($config['allow_bbcode'] && $auth->acl_get('f_bbcode', $forum_id)) ? true : false;
$smilies_status	= ($config['allow_smilies'] && $auth->acl_get('f_smilies', $forum_id)) ? true : false;
$img_status		= ($bbcode_status && $auth->acl_get('f_img', $forum_id)) ? true : false;
$url_status		= ($config['allow_post_links']) ? true : false;
$flash_status	= ($bbcode_status && $auth->acl_get('f_flash', $forum_id) && $config['allow_post_flash']) ? true : false;
$quote_status	= true;

// Save Draft
if ($save && $user->data['is_registered'] && $auth->acl_get('u_savedrafts') && ($mode == 'reply' || $mode == 'post' || $mode == 'quote'))
{
	$subject = $request->variable('subject', '', true);
	$subject = (!$subject && $mode != 'post') ? $post_data['topic_title'] : $subject;
	$message = $request->variable('message', '', true);

	if ($subject && $message)
	{
		if (confirm_box(true))
		{
			$message_parser->message = $message;
			$message_parser->parse($post_data['enable_bbcode'], ($config['allow_post_links']) ? $post_data['enable_urls'] : false, $post_data['enable_smilies'], $img_status, $flash_status, $quote_status, $config['allow_post_links']);

			$sql = 'INSERT INTO ' . DRAFTS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'user_id'		=> (int) $user->data['user_id'],
				'topic_id'		=> (int) $topic_id,
				'forum_id'		=> (int) $forum_id,
				'save_time'		=> (int) $current_time,
				'draft_subject'	=> (string) $subject,
				'draft_message'	=> (string) $message_parser->message)
			);
			$db->sql_query($sql);

			$meta_info = ($mode == 'post') ? append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id) : append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id");

			meta_refresh(3, $meta_info);

			$message = $user->lang['DRAFT_SAVED'] . '<br /><br />';
			$message .= ($mode != 'post') ? sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $meta_info . '">', '</a>') . '<br /><br />' : '';
			$message .= sprintf($user->lang['RETURN_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id) . '">', '</a>');

			trigger_error($message);
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				'mode'		=> $mode,
				'save'		=> true,
				'f'			=> $forum_id,
				't'			=> $topic_id,
				'subject'	=> $subject,
				'message'	=> $message,
				'attachment_data' => $message_parser->attachment_data,
				)
			);

			$hidden_fields = array(
				'icon_id'			=> 0,

				'disable_bbcode'	=> false,
				'disable_smilies'	=> false,
				'disable_magic_url'	=> false,
				'attach_sig'		=> true,
				'lock_topic'		=> false,

				'topic_type'		=> POST_NORMAL,
				'topic_time_limit'	=> 0,

				'poll_title'		=> '',
				'poll_option_text'	=> '',
				'poll_max_options'	=> 1,
				'poll_length'		=> 0,
				'poll_vote_change'	=> false,
			);

			foreach ($hidden_fields as $name => $default)
			{
				if (!isset($_POST[$name]))
				{
					// Don't include it, if its not available
					unset($hidden_fields[$name]);
					continue;
				}

				if (is_bool($default))
				{
					// Use the string representation
					$hidden_fields[$name] = $request->variable($name, '');
				}
				else
				{
					$hidden_fields[$name] = $request->variable($name, $default);
				}
			}

			$s_hidden_fields .= build_hidden_fields($hidden_fields);

			confirm_box(false, 'SAVE_DRAFT', $s_hidden_fields);
		}
	}
	else
	{
		if (utf8_clean_string($subject) === '')
		{
			$error[] = $user->lang['EMPTY_SUBJECT'];
		}

		if (utf8_clean_string($message) === '')
		{
			$error[] = $user->lang['TOO_FEW_CHARS'];
		}
	}
	unset($subject, $message);
}

// Load requested Draft
if ($draft_id && ($mode == 'reply' || $mode == 'quote' || $mode == 'post') && $user->data['is_registered'] && $auth->acl_get('u_savedrafts'))
{
	$sql = 'SELECT draft_subject, draft_message
		FROM ' . DRAFTS_TABLE . "
		WHERE draft_id = $draft_id
			AND user_id = " . $user->data['user_id'];
	$result = $db->sql_query_limit($sql, 1);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($row)
	{
		$post_data['post_subject'] = $row['draft_subject'];
		$message_parser->message = $row['draft_message'];

		$template->assign_var('S_DRAFT_LOADED', true);
	}
	else
	{
		$draft_id = 0;
	}
}

// Load draft overview
if ($load && ($mode == 'reply' || $mode == 'quote' || $mode == 'post') && $post_data['drafts'])
{
	load_drafts($topic_id, $forum_id);
}

/** @var \phpbb\textformatter\utils_interface $bbcode_utils */
$bbcode_utils = $phpbb_container->get('text_formatter.utils');

if ($submit || $preview || $refresh)
{
	$post_data['topic_cur_post_id']	= $request->variable('topic_cur_post_id', 0);
	$post_data['post_subject']		= $request->variable('subject', '', true);
	$message_parser->message		= $request->variable('message', '', true);

	$post_data['username']			= $request->variable('username', $post_data['username'], true);
	$post_data['post_edit_reason']	= ($request->variable('edit_reason', false, false, \phpbb\request\request_interface::POST) && $mode == 'edit' && $auth->acl_get('m_edit', $forum_id)) ? $request->variable('edit_reason', '', true) : '';

	$post_data['orig_topic_type']	= $post_data['topic_type'];
	$post_data['topic_type']		= $request->variable('topic_type', (($mode != 'post') ? (int) $post_data['topic_type'] : POST_NORMAL));
	$post_data['topic_time_limit']	= $request->variable('topic_time_limit', (($mode != 'post') ? (int) $post_data['topic_time_limit'] : 0));

	if ($post_data['enable_icons'] && $auth->acl_get('f_icons', $forum_id))
	{
		$post_data['icon_id'] = $request->variable('icon', (int) $post_data['icon_id']);
	}

	$post_data['enable_bbcode']		= (!$bbcode_status || isset($_POST['disable_bbcode'])) ? false : true;
	$post_data['enable_smilies']	= (!$smilies_status || isset($_POST['disable_smilies'])) ? false : true;
	$post_data['enable_urls']		= (isset($_POST['disable_magic_url'])) ? 0 : 1;
	$post_data['enable_sig']		= (!$config['allow_sig'] || !$auth->acl_get('f_sigs', $forum_id) || !$auth->acl_get('u_sig')) ? false : ((isset($_POST['attach_sig']) && $user->data['is_registered']) ? true : false);

	if ($config['allow_topic_notify'] && $user->data['is_registered'])
	{
		$notify = (isset($_POST['notify'])) ? true : false;
	}
	else
	{
		$notify = false;
	}

	$topic_lock			= (isset($_POST['lock_topic'])) ? true : false;
	$post_lock			= (isset($_POST['lock_post'])) ? true : false;
	$poll_delete		= (isset($_POST['poll_delete'])) ? true : false;

	if ($submit)
	{
		$status_switch = (($post_data['enable_bbcode']+1) << 8) + (($post_data['enable_smilies']+1) << 4) + (($post_data['enable_urls']+1) << 2) + (($post_data['enable_sig']+1) << 1);
		$status_switch = ($status_switch != $check_value);
	}
	else
	{
		$status_switch = 1;
	}

	// Delete Poll
	if ($poll_delete && $mode == 'edit' && count($post_data['poll_options']) &&
		((!$post_data['poll_last_vote'] && $post_data['poster_id'] == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id)) || $auth->acl_get('m_delete', $forum_id)))
	{
		if ($submit && check_form_key('posting'))
		{
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
				'poll_max_options'	=> 0,
				'poll_vote_change'	=> 0
			);

			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $topic_sql) . "
				WHERE topic_id = $topic_id";
			$db->sql_query($sql);
		}

		$post_data['poll_title'] = $post_data['poll_option_text'] = '';
		$post_data['poll_vote_change'] = $post_data['poll_max_options'] = $post_data['poll_length'] = 0;
	}
	else
	{
		$post_data['poll_title']		= $request->variable('poll_title', '', true);
		$post_data['poll_length']		= $request->variable('poll_length', 0);
		$post_data['poll_option_text']	= $request->variable('poll_option_text', '', true);
		$post_data['poll_max_options']	= $request->variable('poll_max_options', 1);
		$post_data['poll_vote_change']	= ($auth->acl_get('f_votechg', $forum_id) && $auth->acl_get('f_vote', $forum_id) && isset($_POST['poll_vote_change'])) ? 1 : 0;
	}

	// If replying/quoting and last post id has changed
	// give user option to continue submit or return to post
	// notify and show user the post made between his request and the final submit
	if (($mode == 'reply' || $mode == 'quote') && $post_data['topic_cur_post_id'] && $post_data['topic_cur_post_id'] != $post_data['topic_last_post_id'])
	{
		// Only do so if it is allowed forum-wide
		if ($post_data['forum_flags'] & FORUM_FLAG_POST_REVIEW)
		{
			if (topic_review($topic_id, $forum_id, 'post_review', $post_data['topic_cur_post_id']))
			{
				$template->assign_var('S_POST_REVIEW', true);
			}

			$submit = false;
			$refresh = true;
		}
	}

	// Parse Attachments - before checksum is calculated
	if ($message_parser->check_attachment_form_token($language, $request, 'posting'))
	{
		$message_parser->parse_attachments('fileupload', $mode, $forum_id, $submit, $preview, $refresh);
	}

	/**
	* This event allows you to modify message text before parsing
	*
	* @event core.posting_modify_message_text
	* @var	array	post_data	Array with post data
	* @var	string	mode		What action to take if the form is submitted
	*				post|reply|quote|edit|delete|bump|smilies|popup
	* @var	int	post_id		ID of the post
	* @var	int	topic_id	ID of the topic
	* @var	int	forum_id	ID of the forum
	* @var	bool	submit		Whether or not the form has been submitted
	* @var	bool	preview		Whether or not the post is being previewed
	* @var	bool	save		Whether or not a draft is being saved
	* @var	bool	load		Whether or not a draft is being loaded
	* @var	bool	cancel		Whether or not to cancel the form (returns to
	*				viewtopic or viewforum depending on if the user
	*				is posting a new topic or editing a post)
	* @var	bool	refresh		Whether or not to retain previously submitted data
	* @var	object	message_parser	The message parser object
	* @var	array	error		Array of errors
	* @since 3.1.2-RC1
	* @changed 3.1.11-RC1 Added error
	*/
	$vars = array(
		'post_data',
		'mode',
		'post_id',
		'topic_id',
		'forum_id',
		'submit',
		'preview',
		'save',
		'load',
		'cancel',
		'refresh',
		'message_parser',
		'error',
	);
	extract($phpbb_dispatcher->trigger_event('core.posting_modify_message_text', compact($vars)));

	// Grab md5 'checksum' of new message
	$message_md5 = md5($message_parser->message);

	// If editing and checksum has changed we know the post was edited while we're editing
	// Notify and show user the changed post
	if ($mode == 'edit' && $post_data['forum_flags'] & FORUM_FLAG_POST_REVIEW)
	{
		$edit_post_message_checksum = $request->variable('edit_post_message_checksum', '');
		$edit_post_subject_checksum = $request->variable('edit_post_subject_checksum', '');

		// $post_data['post_checksum'] is the checksum of the post submitted in the meantime
		// $message_md5 is the checksum of the post we're about to submit
		// $edit_post_message_checksum is the checksum of the post we're editing
		// ...

		// We make sure nobody else made exactly the same change
		// we're about to submit by also checking $message_md5 != $post_data['post_checksum']
		if ($edit_post_message_checksum !== '' &&
			$edit_post_message_checksum != $post_data['post_checksum'] &&
			$message_md5 != $post_data['post_checksum']
			||
			$edit_post_subject_checksum !== '' &&
			$edit_post_subject_checksum != $post_data['post_subject_md5'] &&
			md5($post_data['post_subject']) != $post_data['post_subject_md5'])
		{
			if (topic_review($topic_id, $forum_id, 'post_review_edit', $post_id))
			{
				$template->assign_vars(array(
					'S_POST_REVIEW'			=> true,

					'L_POST_REVIEW'			=> $user->lang['POST_REVIEW_EDIT'],
					'L_POST_REVIEW_EXPLAIN'	=> $user->lang['POST_REVIEW_EDIT_EXPLAIN'],
				));
			}

			$submit = false;
			$refresh = true;
		}
	}

	// Check checksum ... don't re-parse message if the same
	$update_message = ($mode != 'edit' || $message_md5 != $post_data['post_checksum'] || $status_switch || strlen($post_data['bbcode_uid']) < BBCODE_UID_LEN) ? true : false;

	// Also check if subject got updated...
	$update_subject = $mode != 'edit' || ($post_data['post_subject_md5'] && $post_data['post_subject_md5'] != md5($post_data['post_subject']));

	// Parse message
	if ($update_message)
	{
		if (count($message_parser->warn_msg))
		{
			$error[] = implode('<br />', $message_parser->warn_msg);
			$message_parser->warn_msg = array();
		}

		if (!$preview || !empty($message_parser->message))
		{
			$message_parser->parse($post_data['enable_bbcode'], ($config['allow_post_links']) ? $post_data['enable_urls'] : false, $post_data['enable_smilies'], $img_status, $flash_status, $quote_status, $config['allow_post_links']);
		}

		// On a refresh we do not care about message parsing errors
		if (count($message_parser->warn_msg) && $refresh && !$preview)
		{
			$message_parser->warn_msg = array();
		}
	}
	else
	{
		$message_parser->bbcode_bitfield = $post_data['bbcode_bitfield'];
	}

	$ignore_flood = $auth->acl_get('u_ignoreflood') ? true : $auth->acl_get('f_ignoreflood', $forum_id);
	if ($mode != 'edit' && !$preview && !$refresh && $config['flood_interval'] && !$ignore_flood)
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

		if ($last_post_time && ($current_time - $last_post_time) < intval($config['flood_interval']))
		{
			$error[] = $user->lang['FLOOD_ERROR'];
		}
	}

	// Validate username
	if (($post_data['username'] && !$user->data['is_registered']) || ($mode == 'edit' && $post_data['poster_id'] == ANONYMOUS && $post_data['username'] && $post_data['post_username'] && $post_data['post_username'] != $post_data['username']))
	{
		if (!function_exists('validate_username'))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}

		$user->add_lang('ucp');

		if (($result = validate_username($post_data['username'], (!empty($post_data['post_username'])) ? $post_data['post_username'] : '')) !== false)
		{
			$error[] = $user->lang[$result . '_USERNAME'];
		}

		if (($result = validate_string($post_data['username'], false, $config['min_name_chars'], $config['max_name_chars'])) !== false)
		{
			$min_max_amount = ($result == 'TOO_SHORT') ? $config['min_name_chars'] : $config['max_name_chars'];
			$error[] = $user->lang('FIELD_' . $result, $min_max_amount, $user->lang['USERNAME']);
		}
	}

	if ($config['enable_post_confirm'] && !$user->data['is_registered'] && in_array($mode, array('quote', 'post', 'reply')))
	{
		$captcha_data = array(
			'message'	=> $request->variable('message', '', true),
			'subject'	=> $request->variable('subject', '', true),
			'username'	=> $request->variable('username', '', true),
		);
		$vc_response = $captcha->validate($captcha_data);
		if ($vc_response)
		{
			$error[] = $vc_response;
		}
	}

	// check form
	if (($submit || $preview) && !check_form_key('posting'))
	{
		$error[] = $user->lang['FORM_INVALID'];
	}

	if ($submit && $mode == 'edit' && $post_data['post_visibility'] == ITEM_DELETED && !isset($_POST['soft_delete']) && $auth->acl_get('m_approve', $forum_id))
	{
		$is_first_post = ($post_id == $post_data['topic_first_post_id'] || !$post_data['topic_posts_approved']);
		$is_last_post = ($post_id == $post_data['topic_last_post_id'] || !$post_data['topic_posts_approved']);
		$updated_post_data = $phpbb_content_visibility->set_post_visibility(ITEM_APPROVED, $post_id, $post_data['topic_id'], $post_data['forum_id'], $user->data['user_id'], time(), '', $is_first_post, $is_last_post);

		if (!empty($updated_post_data))
		{
			// Update the post_data, so we don't need to refetch it.
			$post_data = array_merge($post_data, $updated_post_data);
		}
	}

	// Parse subject
	if (!$preview && !$refresh && utf8_clean_string($post_data['post_subject']) === '' && ($mode == 'post' || ($mode == 'edit' && $post_data['topic_first_post_id'] == $post_id)))
	{
		$error[] = $user->lang['EMPTY_SUBJECT'];
	}

	// Check for out-of-bounds characters that are currently
	// not supported by utf8_bin in MySQL
	if (preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $post_data['post_subject'], $matches))
	{
		$character_list = implode('<br />', $matches[0]);
		$error[] = $user->lang('UNSUPPORTED_CHARACTERS_SUBJECT', $character_list);
	}

	$post_data['poll_last_vote'] = (isset($post_data['poll_last_vote'])) ? $post_data['poll_last_vote'] : 0;

	if ($post_data['poll_option_text'] &&
		($mode == 'post' || ($mode == 'edit' && $post_id == $post_data['topic_first_post_id']/* && (!$post_data['poll_last_vote'] || $auth->acl_get('m_edit', $forum_id))*/))
		&& $auth->acl_get('f_poll', $forum_id))
	{
		$poll = array(
			'poll_title'		=> $post_data['poll_title'],
			'poll_length'		=> $post_data['poll_length'],
			'poll_max_options'	=> $post_data['poll_max_options'],
			'poll_option_text'	=> $post_data['poll_option_text'],
			'poll_start'		=> $post_data['poll_start'],
			'poll_last_vote'	=> $post_data['poll_last_vote'],
			'poll_vote_change'	=> $post_data['poll_vote_change'],
			'enable_bbcode'		=> $post_data['enable_bbcode'],
			'enable_urls'		=> $post_data['enable_urls'],
			'enable_smilies'	=> $post_data['enable_smilies'],
			'img_status'		=> $img_status
		);

		$message_parser->parse_poll($poll);

		$post_data['poll_options'] = (isset($poll['poll_options'])) ? $poll['poll_options'] : array();
		$post_data['poll_title'] = (isset($poll['poll_title'])) ? $poll['poll_title'] : '';

		/* We reset votes, therefore also allow removing options
		if ($post_data['poll_last_vote'] && ($poll['poll_options_size'] < $orig_poll_options_size))
		{
			$message_parser->warn_msg[] = $user->lang['NO_DELETE_POLL_OPTIONS'];
		}*/
	}
	else if ($mode == 'edit' && $post_id == $post_data['topic_first_post_id'] && $auth->acl_get('f_poll', $forum_id))
	{
		// The user removed all poll options, this is equal to deleting the poll.
		$poll = array(
			'poll_title'		=> '',
			'poll_length'		=> 0,
			'poll_max_options'	=> 0,
			'poll_option_text'	=> '',
			'poll_start'		=> 0,
			'poll_last_vote'	=> 0,
			'poll_vote_change'	=> 0,
			'poll_options'		=> array(),
		);

		$post_data['poll_options'] = array();
		$post_data['poll_title'] = '';
		$post_data['poll_start'] = $post_data['poll_length'] = $post_data['poll_max_options'] = $post_data['poll_last_vote'] = $post_data['poll_vote_change'] = 0;
	}
	else if (!$auth->acl_get('f_poll', $forum_id) && ($mode == 'edit') && ($post_id == $post_data['topic_first_post_id']) && !$bbcode_utils->is_empty($original_poll_data['poll_title']))
	{
		// We have a poll but the editing user is not permitted to create/edit it.
		// So we just keep the original poll-data.
		// Decode the poll title and options text fisrt.
		$original_poll_data['poll_title'] = $bbcode_utils->unparse($original_poll_data['poll_title']);
		$original_poll_data['poll_option_text'] = $bbcode_utils->unparse($original_poll_data['poll_option_text']);
		$original_poll_data['poll_options'] = explode("\n", $original_poll_data['poll_option_text']);

		$poll = array_merge($original_poll_data, array(
			'enable_bbcode'		=> $post_data['enable_bbcode'],
			'enable_urls'		=> $post_data['enable_urls'],
			'enable_smilies'	=> $post_data['enable_smilies'],
			'img_status'		=> $img_status,
		));

		$message_parser->parse_poll($poll);

		$post_data['poll_options'] = (isset($poll['poll_options'])) ? $poll['poll_options'] : array();
		$post_data['poll_title'] = (isset($poll['poll_title'])) ? $poll['poll_title'] : '';
	}
	else
	{
		$poll = array();
	}

	// Check topic type
	if ($post_data['topic_type'] != POST_NORMAL && ($mode == 'post' || ($mode == 'edit' && $post_data['topic_first_post_id'] == $post_id)))
	{
		switch ($post_data['topic_type'])
		{
			case POST_GLOBAL:
				$auth_option = 'f_announce_global';
			break;

			case POST_ANNOUNCE:
				$auth_option = 'f_announce';
			break;

			case POST_STICKY:
				$auth_option = 'f_sticky';
			break;

			default:
				$auth_option = '';
			break;
		}

		if ($auth_option != '' && !$auth->acl_get($auth_option, $forum_id))
		{
			// There is a special case where a user edits his post whereby the topic type got changed by an admin/mod.
			// Another case would be a mod not having sticky permissions for example but edit permissions.
			if ($mode == 'edit')
			{
				// To prevent non-authed users messing around with the topic type we reset it to the original one.
				$post_data['topic_type'] = $post_data['orig_topic_type'];
			}
			else
			{
				$error[] = $user->lang['CANNOT_POST_' . str_replace('F_', '', strtoupper($auth_option))];
			}
		}
	}

	if (count($message_parser->warn_msg))
	{
		$error[] = implode('<br />', $message_parser->warn_msg);
	}

	// DNSBL check
	if ($config['check_dnsbl'] && !$refresh)
	{
		if (($dnsbl = $user->check_dnsbl('post')) !== false)
		{
			$error[] = sprintf($user->lang['IP_BLACKLISTED'], $user->ip, $dnsbl[1]);
		}
	}

	/**
	* This event allows you to define errors before the post action is performed
	*
	* @event core.posting_modify_submission_errors
	* @var	array	post_data	Array with post data
	* @var	array	poll		Array with poll data from post (must be used instead of the post_data equivalent)
	* @var	string	mode		What action to take if the form is submitted
	*				post|reply|quote|edit|delete|bump|smilies|popup
	* @var	int	post_id		ID of the post
	* @var	int	topic_id	ID of the topic
	* @var	int	forum_id	ID of the forum
	* @var	bool	submit		Whether or not the form has been submitted
	* @var	array	error		Any error strings; a non-empty array aborts form submission.
	*				NOTE: Should be actual language strings, NOT language keys.
	* @since 3.1.0-RC5
	* @changed 3.1.5-RC1 Added poll array to the event
	* @changed 3.2.0-a1 Removed undefined page_title
	*/
	$vars = array(
		'post_data',
		'poll',
		'mode',
		'post_id',
		'topic_id',
		'forum_id',
		'submit',
		'error',
	);
	extract($phpbb_dispatcher->trigger_event('core.posting_modify_submission_errors', compact($vars)));

	// Store message, sync counters
	if (!count($error) && $submit)
	{
		if ($submit)
		{
			// Lock/Unlock Topic
			$change_topic_status = $post_data['topic_status'];
			$perm_lock_unlock = ($auth->acl_get('m_lock', $forum_id) || ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && !empty($post_data['topic_poster']) && $user->data['user_id'] == $post_data['topic_poster'] && $post_data['topic_status'] == ITEM_UNLOCKED)) ? true : false;

			if ($post_data['topic_status'] == ITEM_LOCKED && !$topic_lock && $perm_lock_unlock)
			{
				$change_topic_status = ITEM_UNLOCKED;
			}
			else if ($post_data['topic_status'] == ITEM_UNLOCKED && $topic_lock && $perm_lock_unlock)
			{
				$change_topic_status = ITEM_LOCKED;
			}

			if ($change_topic_status != $post_data['topic_status'])
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . "
					SET topic_status = $change_topic_status
					WHERE topic_id = $topic_id
						AND topic_moved_id = 0";
				$db->sql_query($sql);

				$user_lock = ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && $user->data['user_id'] == $post_data['topic_poster']) ? 'USER_' : '';

				$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_' . $user_lock . (($change_topic_status == ITEM_LOCKED) ? 'LOCK' : 'UNLOCK'), false, array(
					'forum_id' => $forum_id,
					'topic_id' => $topic_id,
					$post_data['topic_title']
				));
			}

			// Lock/Unlock Post Edit
			if ($mode == 'edit' && $post_data['post_edit_locked'] == ITEM_LOCKED && !$post_lock && $auth->acl_get('m_edit', $forum_id))
			{
				$post_data['post_edit_locked'] = ITEM_UNLOCKED;
			}
			else if ($mode == 'edit' && $post_data['post_edit_locked'] == ITEM_UNLOCKED && $post_lock && $auth->acl_get('m_edit', $forum_id))
			{
				$post_data['post_edit_locked'] = ITEM_LOCKED;
			}

			$data = array(
				'topic_title'			=> (empty($post_data['topic_title'])) ? $post_data['post_subject'] : $post_data['topic_title'],
				'topic_first_post_id'	=> (isset($post_data['topic_first_post_id'])) ? (int) $post_data['topic_first_post_id'] : 0,
				'topic_last_post_id'	=> (isset($post_data['topic_last_post_id'])) ? (int) $post_data['topic_last_post_id'] : 0,
				'topic_time_limit'		=> (int) $post_data['topic_time_limit'],
				'topic_attachment'		=> (isset($post_data['topic_attachment'])) ? (int) $post_data['topic_attachment'] : 0,
				'post_id'				=> (int) $post_id,
				'topic_id'				=> (int) $topic_id,
				'forum_id'				=> (int) $forum_id,
				'icon_id'				=> (int) $post_data['icon_id'],
				'poster_id'				=> (int) $post_data['poster_id'],
				'enable_sig'			=> (bool) $post_data['enable_sig'],
				'enable_bbcode'			=> (bool) $post_data['enable_bbcode'],
				'enable_smilies'		=> (bool) $post_data['enable_smilies'],
				'enable_urls'			=> (bool) $post_data['enable_urls'],
				'enable_indexing'		=> (bool) $post_data['enable_indexing'],
				'message_md5'			=> (string) $message_md5,
				'post_checksum'			=> (isset($post_data['post_checksum'])) ? (string) $post_data['post_checksum'] : '',
				'post_edit_reason'		=> $post_data['post_edit_reason'],
				'post_edit_user'		=> ($mode == 'edit') ? $user->data['user_id'] : ((isset($post_data['post_edit_user'])) ? (int) $post_data['post_edit_user'] : 0),
				'forum_parents'			=> $post_data['forum_parents'],
				'forum_name'			=> $post_data['forum_name'],
				'notify'				=> $notify,
				'notify_set'			=> $post_data['notify_set'],
				'poster_ip'				=> (isset($post_data['poster_ip'])) ? $post_data['poster_ip'] : $user->ip,
				'post_edit_locked'		=> (int) $post_data['post_edit_locked'],
				'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
				'bbcode_uid'			=> $message_parser->bbcode_uid,
				'message'				=> $message_parser->message,
				'attachment_data'		=> $message_parser->attachment_data,
				'filename_data'			=> $message_parser->filename_data,
				'topic_status'			=> $post_data['topic_status'],

				'topic_visibility'			=> (isset($post_data['topic_visibility'])) ? $post_data['topic_visibility'] : false,
				'post_visibility'			=> (isset($post_data['post_visibility'])) ? $post_data['post_visibility'] : false,
			);

			if ($mode == 'edit')
			{
				$data['topic_posts_approved'] = $post_data['topic_posts_approved'];
				$data['topic_posts_unapproved'] = $post_data['topic_posts_unapproved'];
				$data['topic_posts_softdeleted'] = $post_data['topic_posts_softdeleted'];
			}

			// Only return the username when it is either a guest posting or we are editing a post and
			// the username was supplied; otherwise post_data might hold the data of the post that is
			// being quoted (which could result in the username being returned being that of the quoted
			// post's poster, not the poster of the current post). See: PHPBB3-11769 for more information.
			$post_author_name = ((!$user->data['is_registered'] || $mode == 'edit') && $post_data['username'] !== '') ? $post_data['username'] : '';

			/**
			* This event allows you to define errors before the post action is performed
			*
			* @event core.posting_modify_submit_post_before
			* @var	array	post_data	Array with post data
			* @var	array	poll		Array with poll data
			* @var	array	data		Array with post data going to be stored in the database
			* @var	string	mode		What action to take if the form is submitted
			*				post|reply|quote|edit|delete
			* @var	int	post_id		ID of the post
			* @var	int	topic_id	ID of the topic
			* @var	int	forum_id	ID of the forum
			* @var	string	post_author_name	Author name for guest posts
			* @var	bool	update_message		Boolean if the post message was changed
			* @var	bool	update_subject		Boolean if the post subject was changed
			*				NOTE: Should be actual language strings, NOT language keys.
			* @since 3.1.0-RC5
			* @changed 3.1.6-RC1 remove submit and error from event  Submit and Error are checked previously prior to running event
			* @change 3.2.0-a1 Removed undefined page_title
			*/
			$vars = array(
				'post_data',
				'poll',
				'data',
				'mode',
				'post_id',
				'topic_id',
				'forum_id',
				'post_author_name',
				'update_message',
				'update_subject',
			);
			extract($phpbb_dispatcher->trigger_event('core.posting_modify_submit_post_before', compact($vars)));

			// The last parameter tells submit_post if search indexer has to be run
			$redirect_url = submit_post($mode, $post_data['post_subject'], $post_author_name, $post_data['topic_type'], $poll, $data, $update_message, ($update_message || $update_subject) ? true : false);

			/**
			* This event allows you to define errors after the post action is performed
			*
			* @event core.posting_modify_submit_post_after
			* @var	array	post_data	Array with post data
			* @var	array	poll		Array with poll data
			* @var	array	data		Array with post data going to be stored in the database
			* @var	string	mode		What action to take if the form is submitted
			*				post|reply|quote|edit|delete
			* @var	int	post_id		ID of the post
			* @var	int	topic_id	ID of the topic
			* @var	int	forum_id	ID of the forum
			* @var	string	post_author_name	Author name for guest posts
			* @var	bool	update_message		Boolean if the post message was changed
			* @var	bool	update_subject		Boolean if the post subject was changed
			* @var	string	redirect_url		URL the user is going to be redirected to
			*				NOTE: Should be actual language strings, NOT language keys.
			* @since 3.1.0-RC5
			* @changed 3.1.6-RC1 remove submit and error from event  Submit and Error are checked previously prior to running event
			* @change 3.2.0-a1 Removed undefined page_title
			*/
			$vars = array(
				'post_data',
				'poll',
				'data',
				'mode',
				'post_id',
				'topic_id',
				'forum_id',
				'post_author_name',
				'update_message',
				'update_subject',
				'redirect_url',
			);
			extract($phpbb_dispatcher->trigger_event('core.posting_modify_submit_post_after', compact($vars)));

			if ($config['enable_post_confirm'] && !$user->data['is_registered'] && (isset($captcha) && $captcha->is_solved() === true) && ($mode == 'post' || $mode == 'reply' || $mode == 'quote'))
			{
				$captcha->reset();
			}

			// Handle delete mode...
			if ($request->is_set_post('delete') || $request->is_set_post('delete_permanent'))
			{
				$delete_reason = $request->variable('delete_reason', '', true);
				phpbb_handle_post_delete($forum_id, $topic_id, $post_id, $post_data, !$request->is_set_post('delete_permanent'), $delete_reason);
				return;
			}

			// Check the permissions for post approval.
			// Moderators must go through post approval like ordinary users.
			if ((!$auth->acl_get('f_noapprove', $data['forum_id']) && empty($data['force_approved_state'])) || (isset($data['force_approved_state']) && !$data['force_approved_state']))
			{
				meta_refresh(10, $redirect_url);
				$message = ($mode == 'edit') ? $user->lang['POST_EDITED_MOD'] : $user->lang['POST_STORED_MOD'];
				$message .= (($user->data['user_id'] == ANONYMOUS) ? '' : ' '. $user->lang['POST_APPROVAL_NOTIFY']);
				$message .= '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $data['forum_id']) . '">', '</a>');
				trigger_error($message);
			}

			redirect($redirect_url);
		}
	}
}

// Preview
if (!count($error) && $preview)
{
	$post_data['post_time'] = ($mode == 'edit') ? $post_data['post_time'] : $current_time;

	$preview_message = $message_parser->format_display($post_data['enable_bbcode'], $post_data['enable_urls'], $post_data['enable_smilies'], false);

	$preview_signature = ($mode == 'edit') ? $post_data['user_sig'] : $user->data['user_sig'];
	$preview_signature_uid = ($mode == 'edit') ? $post_data['user_sig_bbcode_uid'] : $user->data['user_sig_bbcode_uid'];
	$preview_signature_bitfield = ($mode == 'edit') ? $post_data['user_sig_bbcode_bitfield'] : $user->data['user_sig_bbcode_bitfield'];

	// Signature
	if ($post_data['enable_sig'] && $config['allow_sig'] && $preview_signature && $auth->acl_get('f_sigs', $forum_id))
	{
		$flags = ($config['allow_sig_bbcode']) ? OPTION_FLAG_BBCODE : 0;
		$flags |= ($config['allow_sig_links']) ? OPTION_FLAG_LINKS : 0;
		$flags |= ($config['allow_sig_smilies']) ? OPTION_FLAG_SMILIES : 0;

		$preview_signature = generate_text_for_display($preview_signature, $preview_signature_uid, $preview_signature_bitfield, $flags, false);
	}
	else
	{
		$preview_signature = '';
	}

	$preview_subject = censor_text($post_data['post_subject']);

	// Poll Preview
	if (!$poll_delete && ($mode == 'post' || ($mode == 'edit' && $post_id == $post_data['topic_first_post_id']/* && (!$post_data['poll_last_vote'] || $auth->acl_get('m_edit', $forum_id))*/))
	&& $auth->acl_get('f_poll', $forum_id))
	{
		$parse_poll = new parse_message($post_data['poll_title']);
		$parse_poll->bbcode_uid = $message_parser->bbcode_uid;
		$parse_poll->bbcode_bitfield = $message_parser->bbcode_bitfield;

		$parse_poll->format_display($post_data['enable_bbcode'], $post_data['enable_urls'], $post_data['enable_smilies']);

		if ($post_data['poll_length'])
		{
			$poll_end = ($post_data['poll_length'] * 86400) + (($post_data['poll_start']) ? $post_data['poll_start'] : time());
		}

		$template->assign_vars(array(
			'S_HAS_POLL_OPTIONS'	=> (count($post_data['poll_options'])),
			'S_IS_MULTI_CHOICE'		=> ($post_data['poll_max_options'] > 1) ? true : false,

			'POLL_QUESTION'		=> $parse_poll->message,

			'L_POLL_LENGTH'		=> ($post_data['poll_length']) ? sprintf($user->lang['POLL_RUN_TILL'], $user->format_date($poll_end)) : '',
			'L_MAX_VOTES'		=> $user->lang('MAX_OPTIONS_SELECT', (int) $post_data['poll_max_options']),
		));

		$preview_poll_options = array();
		foreach ($post_data['poll_options'] as $poll_option)
		{
			$parse_poll->message = $poll_option;
			$parse_poll->format_display($post_data['enable_bbcode'], $post_data['enable_urls'], $post_data['enable_smilies']);
			$preview_poll_options[] = $parse_poll->message;
		}
		unset($parse_poll);

		foreach ($preview_poll_options as $key => $option)
		{
			$template->assign_block_vars('poll_option', array(
				'POLL_OPTION_CAPTION'	=> $option,
				'POLL_OPTION_ID'		=> $key + 1)
			);
		}
		unset($preview_poll_options);
	}

	// Attachment Preview
	if (count($message_parser->attachment_data))
	{
		$template->assign_var('S_HAS_ATTACHMENTS', true);

		$update_count = array();
		$attachment_data = $message_parser->attachment_data;

		parse_attachments($forum_id, $preview_message, $attachment_data, $update_count, true);

		foreach ($attachment_data as $i => $attachment)
		{
			$template->assign_block_vars('attachment', array(
				'DISPLAY_ATTACHMENT'	=> $attachment)
			);
		}
		unset($attachment_data);
	}

	if (!count($error))
	{
		$template->assign_vars(array(
			'PREVIEW_SUBJECT'		=> $preview_subject,
			'PREVIEW_MESSAGE'		=> $preview_message,
			'PREVIEW_SIGNATURE'		=> $preview_signature,

			'S_DISPLAY_PREVIEW'		=> !empty($preview_message),
		));
	}
}

// Remove quotes that would become nested too deep before decoding the text
$generate_quote = ($mode == 'quote' && !$submit && !$preview && !$refresh);
if ($generate_quote && $config['max_quote_depth'] > 0)
{
	$tmp_bbcode_uid = $message_parser->bbcode_uid;
	$message_parser->bbcode_uid = $post_data['bbcode_uid'];
	$message_parser->remove_nested_quotes($config['max_quote_depth'] - 1);
	$message_parser->bbcode_uid = $tmp_bbcode_uid;
}

// Decode text for message display
$post_data['bbcode_uid'] = ($mode == 'quote' && !$preview && !$refresh && !count($error)) ? $post_data['bbcode_uid'] : $message_parser->bbcode_uid;
$message_parser->decode_message($post_data['bbcode_uid']);

if ($generate_quote)
{
	// Remove attachment bbcode tags from the quoted message to avoid mixing with the new post attachments if any
	$message_parser->message = preg_replace('#\[attachment=([0-9]+)\](.*?)\[\/attachment\]#uis', '\\2', $message_parser->message);

	$quote_attributes = array(
						'author'  => $post_data['quote_username'],
						'post_id' => $post_data['post_id'],
						'time'    => $post_data['post_time'],
						'user_id' => $post_data['poster_id'],
	);

	/**
	* This event allows you to modify the quote attributes of the post being quoted
	*
	* @event core.posting_modify_quote_attributes
	* @var	array	quote_attributes	Array with quote attributes
	* @var	array	post_data			Array with post data
	* @since 3.2.6-RC1
	*/
	$vars = array(
		'quote_attributes',
		'post_data',
	);
	extract($phpbb_dispatcher->trigger_event('core.posting_modify_quote_attributes', compact($vars)));

	/** @var \phpbb\language\language $language */
	$language = $phpbb_container->get('language');
	phpbb_format_quote($language, $message_parser, $bbcode_utils, $bbcode_status, $quote_attributes);
}

if (($mode == 'reply' || $mode == 'quote') && !$submit && !$preview && !$refresh)
{
	$post_data['post_subject'] = ((strpos($post_data['post_subject'], 'Re: ') !== 0) ? 'Re: ' : '') . censor_text($post_data['post_subject']);

	$post_subject = $post_data['post_subject'];

	/**
	* This event allows you to modify the post subject of the post being quoted
	*
	* @event core.posting_modify_post_subject
	* @var	string		post_subject	String with the post subject already censored.
	* @since 3.2.8-RC1
	*/
	$vars = array('post_subject');
	extract($phpbb_dispatcher->trigger_event('core.posting_modify_post_subject', compact($vars)));

	$post_data['post_subject'] = $post_subject;
}

$attachment_data = $message_parser->attachment_data;
$filename_data = $message_parser->filename_data;
$post_data['post_text'] = $message_parser->message;

if (count($post_data['poll_options']) || (isset($post_data['poll_title']) && !$bbcode_utils->is_empty($post_data['poll_title'])))
{
	$message_parser->message = $post_data['poll_title'];
	$message_parser->bbcode_uid = $post_data['bbcode_uid'];

	$message_parser->decode_message();
	$post_data['poll_title'] = $message_parser->message;

	$message_parser->message = implode("\n", $post_data['poll_options']);
	$message_parser->decode_message();
	$post_data['poll_options'] = explode("\n", $message_parser->message);
}

// MAIN POSTING PAGE BEGINS HERE

// Forum moderators?
$moderators = array();
if ($config['load_moderators'])
{
	get_moderators($moderators, $forum_id);
}

// Generate smiley listing
generate_smilies('inline', $forum_id);

// Generate inline attachment select box
posting_gen_inline_attachments($attachment_data);

// Do show topic type selection only in first post.
$topic_type_toggle = false;

if ($mode == 'post' || ($mode == 'edit' && $post_id == $post_data['topic_first_post_id']))
{
	$topic_type_toggle = posting_gen_topic_types($forum_id, $post_data['topic_type']);
}

$s_topic_icons = false;
if ($post_data['enable_icons'] && $auth->acl_get('f_icons', $forum_id))
{
	$s_topic_icons = posting_gen_topic_icons($mode, $post_data['icon_id']);
}

$bbcode_checked		= (isset($post_data['enable_bbcode'])) ? !$post_data['enable_bbcode'] : (($config['allow_bbcode']) ? !$user->optionget('bbcode') : 1);
$smilies_checked	= (isset($post_data['enable_smilies'])) ? !$post_data['enable_smilies'] : (($config['allow_smilies']) ? !$user->optionget('smilies') : 1);
$urls_checked		= (isset($post_data['enable_urls'])) ? !$post_data['enable_urls'] : 0;
$sig_checked		= $post_data['enable_sig'];
$lock_topic_checked	= (isset($topic_lock) && $topic_lock) ? $topic_lock : (($post_data['topic_status'] == ITEM_LOCKED) ? 1 : 0);
$lock_post_checked	= (isset($post_lock)) ? $post_lock : $post_data['post_edit_locked'];

// If the user is replying or posting and not already watching this topic but set to always being notified we need to overwrite this setting
$notify_set			= ($mode != 'edit' && $config['allow_topic_notify'] && $user->data['is_registered'] && !$post_data['notify_set']) ? $user->data['user_notify'] : $post_data['notify_set'];
$notify_checked		= (isset($notify)) ? $notify : (($mode == 'post') ? $user->data['user_notify'] : $notify_set);

// Page title & action URL
$s_action = append_sid("{$phpbb_root_path}posting.$phpEx", "mode=$mode&amp;f=$forum_id");
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
	break;
}

// Build Navigation Links
generate_forum_nav($post_data);

// Build Forum Rules
generate_forum_rules($post_data);

// Posting uses is_solved for legacy reasons. Plugins have to use is_solved to force themselves to be displayed.
if ($config['enable_post_confirm'] && !$user->data['is_registered'] && (isset($captcha) && $captcha->is_solved() === false) && ($mode == 'post' || $mode == 'reply' || $mode == 'quote'))
{

	$template->assign_vars(array(
		'S_CONFIRM_CODE'			=> true,
		'CAPTCHA_TEMPLATE'			=> $captcha->get_template(),
	));
}

$s_hidden_fields = ($mode == 'reply' || $mode == 'quote') ? '<input type="hidden" name="topic_cur_post_id" value="' . $post_data['topic_last_post_id'] . '" />' : '';
$s_hidden_fields .= ($draft_id || isset($_REQUEST['draft_loaded'])) ? '<input type="hidden" name="draft_loaded" value="' . $request->variable('draft_loaded', $draft_id) . '" />' : '';

if ($mode == 'edit')
{
	$s_hidden_fields .= build_hidden_fields(array(
		'edit_post_message_checksum'	=> $post_data['post_checksum'],
		'edit_post_subject_checksum'	=> $post_data['post_subject_md5'],
	));
}

// Add the confirm id/code pair to the hidden fields, else an error is displayed on next submit/preview
if (isset($captcha) && $captcha->is_solved() !== false)
{
	$s_hidden_fields .= build_hidden_fields($captcha->get_hidden_fields());
}

$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off' || !$config['allow_attachments'] || !$auth->acl_get('u_attach') || !$auth->acl_get('f_attach', $forum_id)) ? '' : ' enctype="multipart/form-data"';
add_form_key('posting');

/** @var \phpbb\controller\helper $controller_helper */
$controller_helper = $phpbb_container->get('controller.helper');

// Build array of variables for main posting page
$page_data = array(
	'L_POST_A'					=> $page_title,
	'L_ICON'					=> ($mode == 'reply' || $mode == 'quote' || ($mode == 'edit' && $post_id != $post_data['topic_first_post_id'])) ? $user->lang['POST_ICON'] : $user->lang['TOPIC_ICON'],
	'L_MESSAGE_BODY_EXPLAIN'	=> $user->lang('MESSAGE_BODY_EXPLAIN', (int) $config['max_post_chars']),
	'L_DELETE_POST_PERMANENTLY'	=> $user->lang('DELETE_POST_PERMANENTLY', 1),

	'FORUM_NAME'			=> $post_data['forum_name'],
	'FORUM_DESC'			=> ($post_data['forum_desc']) ? generate_text_for_display($post_data['forum_desc'], $post_data['forum_desc_uid'], $post_data['forum_desc_bitfield'], $post_data['forum_desc_options']) : '',
	'TOPIC_TITLE'			=> censor_text($post_data['topic_title']),
	'MODERATORS'			=> (count($moderators)) ? implode($user->lang['COMMA_SEPARATOR'], $moderators[$forum_id]) : '',
	'USERNAME'				=> ((!$preview && $mode != 'quote') || $preview) ? $post_data['username'] : '',
	'SUBJECT'				=> $post_data['post_subject'],
	'MESSAGE'				=> $post_data['post_text'],
	'BBCODE_STATUS'			=> $user->lang(($bbcode_status ? 'BBCODE_IS_ON' : 'BBCODE_IS_OFF'), '<a href="' . $controller_helper->route('phpbb_help_bbcode_controller') . '">', '</a>'),
	'IMG_STATUS'			=> ($img_status) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
	'FLASH_STATUS'			=> ($flash_status) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
	'SMILIES_STATUS'		=> ($smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
	'URL_STATUS'			=> ($bbcode_status && $url_status) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],
	'MAX_FONT_SIZE'			=> (int) $config['max_post_font_size'],
	'MINI_POST_IMG'			=> $user->img('icon_post_target', $user->lang['POST']),
	'POST_DATE'				=> ($post_data['post_time']) ? $user->format_date($post_data['post_time']) : '',
	'ERROR'					=> (count($error)) ? implode('<br />', $error) : '',
	'TOPIC_TIME_LIMIT'		=> (int) $post_data['topic_time_limit'],
	'EDIT_REASON'			=> $request->variable('edit_reason', '', true),
	'SHOW_PANEL'			=> $request->variable('show_panel', ''),
	'U_VIEW_FORUM'			=> append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$forum_id"),
	'U_VIEW_TOPIC'			=> ($mode != 'post') ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id") : '',
	'U_PROGRESS_BAR'		=> append_sid("{$phpbb_root_path}posting.$phpEx", "f=$forum_id&amp;mode=popup"),
	'UA_PROGRESS_BAR'		=> addslashes(append_sid("{$phpbb_root_path}posting.$phpEx", "f=$forum_id&amp;mode=popup")),

	'S_PRIVMSGS'				=> false,
	'S_CLOSE_PROGRESS_WINDOW'	=> (isset($_POST['add_file'])) ? true : false,
	'S_EDIT_POST'				=> ($mode == 'edit') ? true : false,
	'S_EDIT_REASON'				=> ($mode == 'edit' && $auth->acl_get('m_edit', $forum_id)) ? true : false,
	'S_DISPLAY_USERNAME'		=> (!$user->data['is_registered'] || ($mode == 'edit' && $post_data['poster_id'] == ANONYMOUS)) ? true : false,
	'S_SHOW_TOPIC_ICONS'		=> $s_topic_icons,
	'S_DELETE_ALLOWED'			=> ($mode == 'edit' && (($post_id == $post_data['topic_last_post_id'] && $post_data['poster_id'] == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id) && !$post_data['post_edit_locked'] && ($post_data['post_time'] > time() - ($config['delete_time'] * 60) || !$config['delete_time'])) || $auth->acl_get('m_delete', $forum_id))) ? true : false,
	'S_BBCODE_ALLOWED'			=> ($bbcode_status) ? 1 : 0,
	'S_BBCODE_CHECKED'			=> ($bbcode_checked) ? ' checked="checked"' : '',
	'S_SMILIES_ALLOWED'			=> $smilies_status,
	'S_SMILIES_CHECKED'			=> ($smilies_checked) ? ' checked="checked"' : '',
	'S_SIG_ALLOWED'				=> ($auth->acl_get('f_sigs', $forum_id) && $config['allow_sig'] && $user->data['is_registered']) ? true : false,
	'S_SIGNATURE_CHECKED'		=> ($sig_checked) ? ' checked="checked"' : '',
	'S_NOTIFY_ALLOWED'			=> (!$user->data['is_registered'] || ($mode == 'edit' && $user->data['user_id'] != $post_data['poster_id']) || !$config['allow_topic_notify'] || !$config['email_enable']) ? false : true,
	'S_NOTIFY_CHECKED'			=> ($notify_checked) ? ' checked="checked"' : '',
	'S_LOCK_TOPIC_ALLOWED'		=> (($mode == 'edit' || $mode == 'reply' || $mode == 'quote' || $mode == 'post') && ($auth->acl_get('m_lock', $forum_id) || ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && !empty($post_data['topic_poster']) && $user->data['user_id'] == $post_data['topic_poster'] && $post_data['topic_status'] == ITEM_UNLOCKED))) ? true : false,
	'S_LOCK_TOPIC_CHECKED'		=> ($lock_topic_checked) ? ' checked="checked"' : '',
	'S_LOCK_POST_ALLOWED'		=> ($mode == 'edit' && $auth->acl_get('m_edit', $forum_id)) ? true : false,
	'S_LOCK_POST_CHECKED'		=> ($lock_post_checked) ? ' checked="checked"' : '',
	'S_SOFTDELETE_CHECKED'		=> ($mode == 'edit' && $post_data['post_visibility'] == ITEM_DELETED) ? ' checked="checked"' : '',
	'S_SOFTDELETE_ALLOWED'		=> ($mode == 'edit' && $phpbb_content_visibility->can_soft_delete($forum_id, $post_data['poster_id'], $lock_post_checked)) ? true : false,
	'S_RESTORE_ALLOWED'			=> $auth->acl_get('m_approve', $forum_id),
	'S_IS_DELETED'				=> ($mode == 'edit' && $post_data['post_visibility'] == ITEM_DELETED) ? true : false,
	'S_LINKS_ALLOWED'			=> $url_status,
	'S_MAGIC_URL_CHECKED'		=> ($urls_checked) ? ' checked="checked"' : '',
	'S_TYPE_TOGGLE'				=> $topic_type_toggle,
	'S_SAVE_ALLOWED'			=> ($auth->acl_get('u_savedrafts') && $user->data['is_registered'] && $mode != 'edit') ? true : false,
	'S_HAS_DRAFTS'				=> ($auth->acl_get('u_savedrafts') && $user->data['is_registered'] && $post_data['drafts']) ? true : false,
	'S_FORM_ENCTYPE'			=> $form_enctype,

	'S_BBCODE_IMG'			=> $img_status,
	'S_BBCODE_URL'			=> $url_status,
	'S_BBCODE_FLASH'		=> $flash_status,
	'S_BBCODE_QUOTE'		=> $quote_status,

	'S_POST_ACTION'			=> $s_action,
	'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
	'S_ATTACH_DATA'			=> json_encode($message_parser->attachment_data),
	'S_IN_POSTING'			=> true,
);

// Build custom bbcodes array
display_custom_bbcodes();

// Poll entry
if (($mode == 'post' || ($mode == 'edit' && $post_id == $post_data['topic_first_post_id']/* && (!$post_data['poll_last_vote'] || $auth->acl_get('m_edit', $forum_id))*/))
	&& $auth->acl_get('f_poll', $forum_id))
{
	$page_data = array_merge($page_data, array(
		'S_SHOW_POLL_BOX'		=> true,
		'S_POLL_VOTE_CHANGE'	=> ($auth->acl_get('f_votechg', $forum_id) && $auth->acl_get('f_vote', $forum_id)),
		'S_POLL_DELETE'			=> ($mode == 'edit' && count($post_data['poll_options']) && ((!$post_data['poll_last_vote'] && $post_data['poster_id'] == $user->data['user_id'] && $auth->acl_get('f_delete', $forum_id)) || $auth->acl_get('m_delete', $forum_id))),
		'S_POLL_DELETE_CHECKED'	=> (!empty($poll_delete)) ? true : false,

		'L_POLL_OPTIONS_EXPLAIN'	=> $user->lang('POLL_OPTIONS_' . (($mode == 'edit') ? 'EDIT_' : '') . 'EXPLAIN', (int) $config['max_poll_options']),

		'VOTE_CHANGE_CHECKED'	=> (!empty($post_data['poll_vote_change'])) ? ' checked="checked"' : '',
		'POLL_TITLE'			=> (isset($post_data['poll_title'])) ? $post_data['poll_title'] : '',
		'POLL_OPTIONS'			=> (!empty($post_data['poll_options'])) ? implode("\n", $post_data['poll_options']) : '',
		'POLL_MAX_OPTIONS'		=> (isset($post_data['poll_max_options'])) ? (int) $post_data['poll_max_options'] : 1,
		'POLL_LENGTH'			=> $post_data['poll_length'],
		)
	);
}

/**
* This event allows you to modify template variables for the posting screen
*
* @event core.posting_modify_template_vars
* @var	array	post_data	Array with post data
* @var	array	moderators	Array with forum moderators
* @var	string	mode		What action to take if the form is submitted
*				post|reply|quote|edit|delete|bump|smilies|popup
* @var	string	page_title	Title of the mode page
* @var	bool	s_topic_icons	Whether or not to show the topic icons
* @var	string	form_enctype	If attachments are allowed for this form
*				"multipart/form-data" or empty string
* @var	string	s_action	The URL to submit the POST data to
* @var	string	s_hidden_fields	Concatenated hidden input tags of posting form
* @var	int	post_id		ID of the post
* @var	int	topic_id	ID of the topic
* @var	int	forum_id	ID of the forum
* @var	int	draft_id	ID of the draft
* @var	bool	submit		Whether or not the form has been submitted
* @var	bool	preview		Whether or not the post is being previewed
* @var	bool	save		Whether or not a draft is being saved
* @var	bool	load		Whether or not a draft is being loaded
* @var	bool	cancel		Whether or not to cancel the form (returns to
*				viewtopic or viewforum depending on if the user
*				is posting a new topic or editing a post)
* @var	array	error		Any error strings; a non-empty array aborts
*				form submission.
*				NOTE: Should be actual language strings, NOT
*				language keys.
* @var	bool	refresh		Whether or not to retain previously submitted data
* @var	array	page_data	Posting page data that should be passed to the
*				posting page via $template->assign_vars()
* @var	object	message_parser	The message parser object
* @since 3.1.0-a1
* @changed 3.1.0-b3 Added vars post_data, moderators, mode, page_title,
*		s_topic_icons, form_enctype, s_action, s_hidden_fields,
*		post_id, topic_id, forum_id, submit, preview, save, load,
*		delete, cancel, refresh, error, page_data, message_parser
* @changed 3.1.2-RC1 Removed 'delete' var as it does not exist
* @changed 3.1.5-RC1 Added poll variables to the page_data array
* @changed 3.1.6-RC1 Added 'draft_id' var
*/
$vars = array(
	'post_data',
	'moderators',
	'mode',
	'page_title',
	's_topic_icons',
	'form_enctype',
	's_action',
	's_hidden_fields',
	'post_id',
	'topic_id',
	'forum_id',
	'draft_id',
	'submit',
	'preview',
	'save',
	'load',
	'cancel',
	'refresh',
	'error',
	'page_data',
	'message_parser',
);
extract($phpbb_dispatcher->trigger_event('core.posting_modify_template_vars', compact($vars)));

// Start assigning vars for main posting page ...
$template->assign_vars($page_data);

// Show attachment box for adding attachments if true
$allowed = ($auth->acl_get('f_attach', $forum_id) && $auth->acl_get('u_attach') && $config['allow_attachments'] && $form_enctype);

if ($allowed)
{
	$max_files = ($auth->acl_get('a_') || $auth->acl_get('m_', $forum_id)) ? 0 : (int) $config['max_attachments'];
	$plupload->configure($cache, $template, $s_action, $forum_id, $max_files);
}

// Attachment entry
posting_gen_attachment_entry($attachment_data, $filename_data, $allowed);

// Output page ...
page_header($page_title);

$template->set_filenames(array(
	'body' => 'posting_body.html')
);

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));

// Topic review
if ($mode == 'reply' || $mode == 'quote')
{
	if (topic_review($topic_id, $forum_id))
	{
		$template->assign_var('S_DISPLAY_REVIEW', true);
	}
}

page_footer();
