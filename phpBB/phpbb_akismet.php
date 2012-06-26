<?php
/**
*
* @package phpBB3-Akismet
* @copyright (c) 2012 Nathaniel Guse
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* Report a post as spam
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/akismet/phpbb_akismet.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('mods/phpbb_akismet', 'mcp'));

$post_id = request_var('p', 0);

$sql = 'SELECT * FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
	WHERE p.post_id = ' . $post_id . '
		AND u.user_id = p.poster_id
		AND akismet_spam <> 1';
$result = $db->sql_query($sql);
$post = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$post)
{
	trigger_error($user->lang['POST_NOT_EXIST'] . '<br /><a href="javascript:history.go(-1);">' . $user->lang['BACK_TO_PREV'] . '</a>');
}

$decoded = $post['post_text'];
decode_message($decoded, $post['bbcode_uid']);

$flags = (($post['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) + (($post['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + (($post['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
$post_text = generate_text_for_display($post['post_text'], $post['bbcode_uid'], $post['bbcode_bitfield'], $flags);

$username = get_username_string('full', $post['poster_id'], $post['username'], $post['user_colour'], $post['post_username']);

if (confirm_box(true))
{
	$phpbb_akismet = new phpbb_akismet();
	$phpbb_akismet->report_spam($post_id);

	if (!function_exists('delete_posts'))
	{
		include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
	}

	delete_posts('post_id', $post_id);

	// Try to find the previous post
	$sql = 'SELECT * FROM ' . POSTS_TABLE . '
		WHERE topic_id = ' . $post['topic_id'] . '
			AND post_approved = 1
			AND post_id < ' . $post_id . '
		ORDER BY post_time DESC';
	$result = $db->sql_query_limit($sql, 1);
	$redirect_post = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$redirect_post)
	{
		// Try to find the next post
		$sql = 'SELECT * FROM ' . POSTS_TABLE . '
			WHERE topic_id = ' . $post['topic_id'] . '
				AND post_approved = 1
				AND post_id > ' . $post_id . '
			ORDER BY post_time ASC';
		$result = $db->sql_query_limit($sql, 1);
		$redirect_post = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
	}

	if ($redirect_post)
	{
		trigger_error($user->lang['PHPBB_AKISMET_REMOVE_SPAM_COMPLETE'] . sprintf($user->lang['RETURN_TOPIC'], '<br /><br /><a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", "t={$redirect_post['topic_id']}&amp;p={$redirect_post['post_id']}#p{$redirect_post['post_id']}") . '">', '</a>'));
	}

	trigger_error($user->lang['PHPBB_AKISMET_REMOVE_SPAM_COMPLETE'] . sprintf($user->lang['RETURN_FORUM'], '<br /><br /><a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", "f={$post['forum_id']}") . '">', '</a>'));
}
else
{
	$user->lang['PHPBB_AKISMET_REMOVE_SPAM_CONFIRM'] = sprintf($user->lang['PHPBB_AKISMET_REMOVE_SPAM_CONFIRM'], $username, $post_text);
	confirm_box(false, 'PHPBB_AKISMET_REMOVE_SPAM');
}