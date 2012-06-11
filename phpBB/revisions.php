<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
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

$post_id = $request->variable('p', 0);
$revert = $request->variable('revert', 0);

if (!$post_id)
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
$total_revisions = sizeof($revisions);

if (!$total_revisions)
{
	trigger_error('NO_REVISIONS_POST');
}

$current = $revisions[0];

if(empty($revisions) || ($revert && empty($revisions[$revert])))
{
	trigger_error('ERROR_REVISION_NOT_FOUND');
}

// If we are reverting, the from revision is the current post
// Otherwise, it's the second array element (the first is the current)
$first = $revert ? $current : next($revisions);

// If we are reversting the, the to revision is the given revision ID
// Otherwise, it is the final (i.e. current) revision
$last = $revert ? $revisions[$revert] : $current;

// Let's get our diff driver
// @todo either pick a diff engine to use forever, or make this dynamic; for now we go with what we have
$text_diff = new phpbb_revisions_diff_engine_finediff($first->get('text_decoded'), $last->get('text_decoded'));
$subject_diff = new phpbb_revisions_diff_engine_finediff($first->get('subject'), $last->get('subject'));

$text_diff_rendered = bbcode_nl2br($text_diff->render());
$subject_diff_renedered = bbcode_nl2br($subject_diff->render());

$template->assign_vars(array(
	'POST_USERNAME'		=> get_username_string('full', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
	'U_PROFILE'			=> get_username_string('profile', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
	
	'RANK_TITLE'		=> $post_data['rank_title'],
	'RANK_IMG'			=> $post_data['rank_image'],

	'AVATAR'			=> get_user_avatar($post_data['user_avatar'], $post_data['user_avatar_type'], $post_data['user_avatar_width'], $post_data['user_avatar_height']),

	'POST_DATE'			=> $user->format_date($post_data['post_time']),
	'POST_SUBJECT'		=> $revert ? $subject_diff_renedered : $current->get('subject'),
	'MESSAGE'			=> $revert ? $text_diff_rendered : $current->get('text'),
	'SIGNATURE'			=> ($post_data['enable_sig']) ? $post_data['user_sig_parsed'] : '',

	'POSTER_JOINED'		=> $user->format_date($post_data['user_regdate']),
	'POSTER_POSTS'		=> $post_data['user_posts'],
	'POSTER_LOCATION'	=> $post_data['user_from'],

	'MINI_POST_IMG'		=> $user->img('icon_post_target', 'POST'),
	'U_MINI_POST'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $post_data['post_id']) . '#p' . $post_data['post_id'],

	'POST_ID'			=> $post_data['post_id'],
	'POSTER_ID'			=> $post_data['poster_id'],

	'L_LAST_REVISION_TIME'	=> $user->lang('LAST_REVISION_TIME', $user->format_date($current->get('time'))),
));

if ($revert)
{
	add_form_key('revert_form');

	if ($revert_confirm = $request->variable('confirm', 0) && check_form_key('revert_form', 120))
	{
		if (($revert_result = $post->revert($revert)) === phpbb_revisions_post::REVISION_REVERT_SUCCESS)
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
				case phpbb_revisions_post::REVISION_NOT_FOUND:
					$lang = 'ERROR_REVISION_NOT_FOUND';
				break;

				case phpbb_revisions_post::REVISION_INSERT_FAIL:
					$lang = 'ERROR_REVISION_INSERT_FAIL';
				break;

				case phpbb_revisions_post::REVISION_POST_UPDATE_FAIL:
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

		$template->assign_vars(array(
			'U_ACTION'			=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $post_id, 'revert' => $revert)),
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
		));

		// Ready the page for viewing
		page_header($user->lang('REVISIONS_REVERT_TITLE'), false);

		$template->set_filenames(array(
			'body'		=> 'revisions_revert_body.html',
		));

		page_footer();		
	}
}

$template->assign_vars(array(
	// Comparison template variables
	'DISPLAY_COMPARISON'	=> true,
	'TEXT_DIFF'				=> $text_diff_rendered,
	'SUBJECT_DIFF'			=> $subject_diff_renedered,
	//'L_COMPARE_SUMMARY'		=> $l_compare_summary,
	//'L_LINES_ADDED_REMOVED'	=> $l_lines_added_removed,
));

$revision_number = 1;
foreach ($revisions as $revision)
{
	$template->assign_block_vars('revision', array(
		'USERNAME'			=> $revision->get('username'),
		'USER_AVATAR'		=> $revision->get_avatar(20, 20),
		'DATE'				=> $user->format_date($revision->get('time')),
		'REASON'			=> $revision->get('reason'),
		'ID'				=> $revision->get('id'),
		'NUMBER'			=> $revision_number,

		'IN_RANGE'			=> true, // @todo when viewing revision ranges is implemented, this will need to be changed
		'CURRENT_REVISION'	=> $revision->get('id') === 0,

		'U_REVISION_VIEW'	=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('r' => $revision->get('id'))),
		'U_POST'			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('p' => $revision->get('post'))). '#p' . $revision->get('post'),
		'U_REVERT_TO'		=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $revision->get('post'), 'revert' => $revision->get('id'))),
	));
	$revision_number++;
}

page_header($user->lang('REVISIONS_COMPARE_TITLE'), false);

$template->set_filenames(array(
	'body'		=> 'revisions_body.html',
));

page_footer();
