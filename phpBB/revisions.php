<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('revisions', 'viewtopic'));

if (!($post_id = $request->variable('p', 0)))
{
	trigger_error('NO_POST');
}

$post = new phpbb_revisions_post($post_id);
$post_data = $post->get_post_data();

if (empty($post_data['post_id']))
{
	trigger_error('NO_POST');
}

$revisions = $post->get_revisions();

// Get the total number of revisions
$total_revisions = count($revisions);

if (!count($total_revisions))
{
	trigger_error('NO_REVISIONS_POST');
}

// Handle reverting to a different revision
if ($revert = $request->variable('revert', 0))
{
	if(empty($this->revisions) || empty($this->revisions[$new_revision_id]))
	{
		trigger_error('ERROR_REVISION_NOT_FOUND');
	}
	
	if (confirm_box(true))
	{
		if (($revert_result = $post->revert($revert)) === REVISION_REVERT_SUCCESS)
		{
			// Because we've changed things up, we need to update our arrays
			$post_data = $post->get_post_data(true);
			$revisions = $post->get_revisions(true);
			$template->assign_var('S_POST_REVERTED', true);
		}
		else
		{
			switch ($revert_result)
			{
				default:
				case REVISION_NOT_FOUND:
					$lang = 'ERROR_REVISION_NOT_FOUND';
				break;

				case REVISION_INSERT_FAIL:
					$lang = 'ERROR_REVISION_INSERT_FAIL';
				break;

				case REVISION_POST_UPDATE_FAIL:
					$lang = 'ERROR_REVISION_POST_UPDATE_FAIL';
				break;
			}

			trigger_error($lang);
		}
	}
	else
	{
		$s_hidden_fields = build_hidden_fields(array(
			'post_id'	=> $post_id,
			'revert'	=> $revert,
		));

		confirm_box(false, 'REVERT_POST_TITLE', $s_hidden_fields);
	}
}

// Display the current revision of the post as it would appear in the topic
$current = $revisions[0];
$template->assign_vars(array(
	'POST_USERNAME'		=> get_username_string('full', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
	'U_PROFILE'			=> get_username_string('profile', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
	
	'RANK_TITLE'		=> $post_data['rank_title'],
	'RANK_IMG'			=> $post_data['rank_image'],

	'AVATAR'			=> get_user_avatar($post_data['user_avatar'], $post_data['user_avatar_type'], $post_data['user_avatar_width'], $post_data['user_avatar_height']),

	'POST_DATE'			=> $user->format_date($post_data['post_time']),
	'POST_SUBJECT'		=> $current->get('subject'),
	'MESSAGE'			=> $current->get('text'),
	'SIGNATURE'			=> ($post_data['enable_sig']) ? $post_data['user_sig_parsed'] : '',

	'POSTER_JOINED'		=> $user->format_date($post_data['user_regdate']),
	'POSTER_POSTS'		=> $post_data['user_posts'],
	'POSTER_LOCATION'	=> $post_data['user_from'],

	'MINI_POST_IMG'		=> $user->img('icon_post_target', 'POST'),
	'U_MINI_POST'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $post_data['post_id']) . '#p' . $post_data['post_id'],

	'POST_ID'			=> $post_data['post_id'],
	'POSTER_ID'			=> $post_data['poster_id'],

	// Comparison template variables
	'DISPLAY_COMPARISON'	=> true,
	'TEXT_DIFF'				=> $current->get('text'),
	'SUBJECT_DIFF'			=> $current->get('subject'),
	//'L_COMPARE_SUMMARY'		=> $l_compare_summary,
	'L_LAST_REVISION_TIME'	=> $user->lang('LAST_REVISION_TIME', $user->format_date($current->get('time'))),
	//'L_LINES_ADDED_REMOVED'	=> $l_lines_added_removed,
));

$revision_number = 1;
foreach ($revisions as $revision)
{
	$template->assign_block_vars('revisions', array(
		'USERNAME'			=> $revision->get('username'),
		'USER_AVATAR'		=> $revision->get_avatar(20, 20),
		'DATE'				=> $user->format_date($revision->get('time')),
		'REASON'			=> $revision->get('reason'),
		'ID'				=> $revision->get('id'),
		'NUMBER'			=> $revision_number,

		'IN_RANGE'			=> true,
		'CURRENT_REVISION'	=> $revision->get('id') === 0,

		'U_REVISION_VIEW'	=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('r' => $revision->get('id'))),
		'U_POST'			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('p' => $revision->get('post'))). '#p' . $revision->get('post'),
		'U_REVERT_TO'		=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $revision->get('post'), 'revert' => $revision->get('id'))),
	));
	$revision_number++;
}

// Ready the page for viewing
page_header($user->lang('REVISIONS_COMPARE_TITLE'), false);

$template->set_filenames(array(
	'body'		=> 'revisions_body.html',
));

page_footer();
