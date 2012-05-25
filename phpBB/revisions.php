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

// Initialize data from URL
$post_id = $request->variable('p', 0);
// This defaults to a string on purpose
$revision_id = $request->variable('r', '');

// Initialize some defaults
$revision_from_id = $revision_to_id = 0;

if ($revision_id)
{
	preg_match('/([0-9]+)\.{3}([0-9]+)/', $revision_id, $match);
	if (!empty($match))
	{
		// When revisions.php?r=1...2
		// $match[0] contains 1...2
		// $match[1] and $match[2] contain 1 and 2 respectively
		$revision_from_id = $match[1];
		$revision_to_id = $match[2];
	}
	else
	{
		$revision_to_id = (int) $revision_id;
		if (!$revision_to_id)
		{
			trigger_error('NO_REVISIONS');
		}
	}

	// Now get the post ID from the to (ending) revision ID
	// since in the latter case, that's all we know
	$sql = 'SELECT post_id
		FROM ' . POST_REVISIONS_TABLE . '
		WHERE revision_id = ' . (int) $revision_to_id;
	$result = $db->sql_query($sql);
	$post_id = $db->sql_fetchrow('post_id');

	if (!$post_id)
	{
		trigger_error('NO_REVISIONS_POST');
	}
}

if ($post_id)
{
	$post = new phpbb_revisions_post($post_id);
	$post_data = $post->get('post_data');

	// We check one property that all posts should have
	// to make sure we specified an existing post
	if (empty($post_data['post_id']))
	{
		trigger_error('NO_POST');
	}

	$revisions = $post->load_revisions();

	// Get the total number of revisions
	$total_revisions = count($revisions);

	// We want to do two different things depending on how many revisions we have
	if (!$total_revisions)
	{
		trigger_error('NO_REVISIONS_POST');
	}
	else if ($total_revisions == 1)
	{
		// Stuff to do if we only have one revision to work with.

		// Basically, we just display the revision and explain that it's the only one.
	}
	else if ($total_revisions > 1)
	{
		// Sort the revisions based on ID with a custom sort method
		usort($revisions, array('phpbb_revisions_post', 'sort_post_revisions'));

		// Now we count the number of different users who have made revisions to the post
		$revision_users = array();
		foreach ($revisions as $revision)
		{
			if (!in_array($revision->get('user_id'), $revision_users))
			{
				$revision_users[] = $revision->get('user_id');
			}
		}
		$total_revision_users = count($revision_users);

		$l_compare_summary = $user->lang('REVISION_COUNT', $total_revisions) . ' ' . $user->lang('BY') . ' ' . $user->lang('REVISION_USER_COUNT', $total_revision_users);
		$l_last_revision_time = $user->lang('LAST_REVISION_TIME', $user->format_date($post_data['post_edit_time']));

		//  If we have not been given an ending point, set it to the final revision
		$revision_to = $revision_to_id && isset($revisions[$revision_to_id]) ? $revisions[$revision_to_id] : $revisions[$total_revisions - 1];
		// Likewise, if we have not been given a starting point, set it to the revision prior to the ending one
		$revision_from = $revision_from_id && isset($revisions[$revision_from_id]) ? $revisions[$revision_from_id] : $revisions[/*Figure out the previous revision ID and put it here*/0];

		$diff = new phpbb_revisions_diff($revision_from, $revision_to);

		// We want to display a list of revisions with a few details about each
		foreach ($revisions as $revision)
		{
			$template->assign_block_vars('revisions', array(
				'USERNAME'			=> $revision->get('username'),
				'USER_AVATAR'		=> $revision->get_avatar(20, 20),
				'DATE'				=> $user->format_date($revision->get('time')),

				'U_REVISION_VIEW'	=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('r' => $revision->get('id'))),
			));
		}

		$template->assign_vars(array(
			'DISPLAY_COMPARISON'	=> true,
			'TEXT_DIFF'				=> $diff->render('text') ?: ($user->lang('NO_DIFF') . '<br />' . $revision_to->get('text')),
			'SUBJECT_DIFF'			=> $diff->render('subject') ?: ($user->lang('NO_DIFF') . '<br />' . $revision_to->get('subject')),

			'L_COMPARE_SUMMARY'		=> $l_compare_summary,
			'L_LAST_REVISION_TIME'	=> $l_last_revision_time,
		));
	}
	// Ready the page for viewing
	page_header($user->lang('REVISIONS_COMPARE_TITLE'), false);

	$template->set_filenames(array(
		'body'		=> 'revisions_body.html',
	));

	page_footer();
}

// Generally, we won't get to this point, but just in case
trigger_error('NO_REVISIONS');
