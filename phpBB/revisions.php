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

$post_id	= $request->variable('p', 0);
$revert		= $request->variable('revert', 0);
$compare	= $request->variable('compare', '');

// Variables for first and last revisions for comparison
$first = $last = null;

if (!$post_id)
{
	// If not given a post ID, try to get it from other information
	if ($revert)
	{
		$revert_revision = new phpbb_revisions_revision($revert, $db);

		$post_id = $revert_revision->get_post_id();
	}
	else if ($compare)
	{
		$matches = array();
		preg_match('/([0-9]+)\.{3}([0-9]+)/', $compare, $matches);
		if (!empty($matches))
		{
			// Note that $matches[0], in the case of a match, will be X...Y, where X and Y are the given numbers
			// We don't actually have any use for it, we just want the X and Y, so that's what we look at below

			// If the first number is not 0 (current revision) we can use it to figure out the post ID
			if (!empty($matches[1]))
			{
				$first = phpbb_revisions_revision($matches[1], $db);
				$post_id = $first->get_post_id();
			}

			// If the second number is not 0 (current revision) we can use it to figure out the post ID
			if (!empty($matches[2]))
			{
				$last = phpbb_revisions_revision($matches[2], $db);
				$post_id = $post_id ?: $last->get_post_id();
			}
		}
		else
		{
			// If we don't see the X...Y pattern in $compare, we assume a single ID was given
			// Set the "to" revision to the current post
			$first = phpbb_revisions_revision((int) $compare, $db);

			$post_id = $first->get_post_id();
		}
	}

	// If we still don't have a post ID, we error
	if (!$post_id)
	{
		trigger_error('NO_POST');
	}
}

$post = new phpbb_revisions_post($post_id, $db);
$post_data = $post->get_post_data();

if (empty($post_data['post_id']))
{
	trigger_error('NO_POST');
}

$revisions = $post->get_revisions();
$total_revisions = sizeof($revisions);

if (!$total_revisions)
{
	trigger_error('NO_REVISIONS_POST');
}

$current = $revisions[0];

if (empty($revisions) || ($revert && empty($revisions[$revert])))
{
	trigger_error('ERROR_REVISION_NOT_FOUND');
}

// If we are reverting, the from revision is the current post
// Otherwise, it's the second array element (the first is the current)
if (empty($first))
{
	$first = $revert ? $current : next($revisions);
}

// If we are reversting the, the to revision is the given revision ID
// Otherwise, it is the final (i.e. current) revision
if (empty($last))
{
	$last = $revert ? $revisions[$revert] : $current;
}

// Let's get our diff driver
// @todo either pick a diff engine to use forever, or make this dynamic; for now we go with what we have
$text_diff = new phpbb_revisions_diff_engine_finediff($first->get_text_decoded(), $last->get_text_decoded());
$subject_diff = new phpbb_revisions_diff_engine_finediff($first->get_subject(), $last->get_subject());

$text_diff_rendered = bbcode_nl2br($text_diff->render());
$subject_diff_renedered = bbcode_nl2br($subject_diff->render());

$template->assign_vars(array(
	'POST_USERNAME'		=> get_username_string('full', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
	'U_PROFILE'			=> get_username_string('profile', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
	
	'RANK_TITLE'		=> $post_data['rank_title'],
	'RANK_IMG'			=> $post_data['rank_image'],

	'AVATAR'			=> get_user_avatar($post_data['user_avatar'], $post_data['user_avatar_type'], $post_data['user_avatar_width'], $post_data['user_avatar_height']),

	'POST_DATE'			=> $user->format_date($post_data['post_time']),
	'POST_SUBJECT'		=> $revert ? $subject_diff_renedered : $current->get_subject(),
	'MESSAGE'			=> $revert ? $text_diff_rendered : $current->get_text(),
	'SIGNATURE'			=> ($post_data['enable_sig']) ? $post_data['user_sig_parsed'] : '',

	'POSTER_JOINED'		=> $user->format_date($post_data['user_regdate']),
	'POSTER_POSTS'		=> $post_data['user_posts'],
	'POSTER_LOCATION'	=> $post_data['user_from'],

	'MINI_POST_IMG'		=> $user->img('icon_post_target', 'POST'),
	'U_MINI_POST'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $post_data['post_id']) . '#p' . $post_data['post_id'],

	'POST_ID'			=> $post_data['post_id'],
	'POSTER_ID'			=> $post_data['poster_id'],

	'L_LAST_REVISION_TIME'	=> $user->lang('LAST_REVISION_TIME', $user->format_date($current->get_time())),
));

if ($revert)
{
	add_form_key('revert_form');

	$revert_confirm = $request->variable('confirm', 0);
	if ($revert_confirm && check_form_key('revert_form', 120))
	{
		$revert_result = $post->revert($revert);
		if ($revert_result === phpbb_revisions_post::REVISION_REVERT_SUCCESS)
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

				case phpbb_revisions_post::POST_EDIT_LOCKED:
					$lang = 'ERROR_POST_EDIT_LOCKED';
				break;
			}

			$u_return_post = append("{$phpbb_root_path}viewtopic.$phpEx", array('p' => $post_id)) . "#p$post_id";
			$u_return_revision = append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $post_id));

			trigger_error($lang . '
				<br /><a href="' . $u_return_post . '">' . $user->lang('RETURN_POST') . '</a>
				<br /><a href="' . $u_return_revision . '">' . $user->lang('RETURN_REVISION') . '</a>');
		}
	}
	else
	{
		$template->assign_vars(array(
			'U_ACTION'			=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $post_id, 'revert' => $revert)),
			'S_HIDDEN_FIELDS'	=> build_hidden_fields(array(
				'post_id'	=> $post_id,
				'revert'	=> $revert,
			)),
		));

		page_header($user->lang('REVISIONS_REVERT_TITLE'), false);

		$template->set_filenames(array(
			'body'		=> 'revisions_revert_body.html',
		));

		page_footer();		
	}
}

$revision_number = 1;
$revision_users = array();
foreach ($revisions as $revision)
{
	$template->assign_block_vars('revision', array(
		'USERNAME'			=> $revision->get_username(),
		'USER_AVATAR'		=> $revision->get_avatar(20, 20),
		'DATE'				=> $user->format_date($revision->get_time()),
		'REASON'			=> $revision->get_reason(),
		'ID'				=> $revision->get_id(),
		'NUMBER'			=> $revision_number,

		'IN_RANGE'			=> true, // @todo when viewing revision ranges is implemented, this will need to be changed
		'CURRENT_REVISION'	=> $revision->get_id() === 0,

		'U_REVISION_VIEW'	=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('r' => $revision->get_id())),
		'U_POST'			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('p' => $revision->get_post())). '#p' . $revision->get_post(),
		'U_REVERT_TO'		=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $revision->get_post(), 'revert' => $revision->get_id())),
	));

	// We assign it the user ID as the key so we don't have to think about potential duplications
	$revision_users[$revision->get_user_id()] = true;
	$revision_number++;
}

$l_compare_summary = $user->lang('REVISION_COUNT', $total_revisions) . '
	' . $user->lang('BY') . '
	' . $user->lang('REVISION_USER_COUNT', sizeof($revision_users));
$l_lines_added_removed = $user->lang('REVISION_ADDITIONS', $text_diff->additions_count() + $subject_diff->additions_count()) . '
	' . $user->lang('AND') . '
	' . $user->lang('REVISION_DELETIONS', $text_diff->deletions_count() + $subject_diff->deletions_count());

$template->assign_vars(array(
	// Comparison template variables
	'DISPLAY_COMPARISON'	=> true,
	'TEXT_DIFF'				=> $text_diff_rendered,
	'SUBJECT_DIFF'			=> $subject_diff_renedered,
	'L_COMPARE_SUMMARY'		=> $l_compare_summary,
	'L_LINES_ADDED_REMOVED'	=> $l_lines_added_removed,
));

page_header($user->lang('REVISIONS_COMPARE_TITLE'), false);

$template->set_filenames(array(
	'body'		=> 'revisions_body.html',
));

page_footer();
