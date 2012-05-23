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
$user->setup('revisions');

// Initialize variables
$post_id = $request->variable('p', 0);
// Revision id(s)
// To view a single revision, just its ID
// To compare two revisions, should be: #...# e.g. 3...14
$revision_id = $request->variable('r', '0');

if ($revision_id)
{
	$compare = true;

	preg_match('/([0-9]+)\.{3}([0-9]+)/', $revision_id, $match);
	// If we have a range, like 1...2, show a comparison
	// Otherwise, just show the one revision, compared to the previous one
	if (!empty($match))
	{
		$revision_from = new phpbb_revisions_revision($match[1]);
		$revision_to = new phpbb_revisions_revision($match[2]);
	}
	else
	{
		$revision_id = (int) $revision_id;
		$revision_to = new phpbb_revisions_revision($revision_id);
		$revision_from = $revision_to->load_previous();
	}

	if ($user->data['user_id'] != $revision_to->get_poster_id() || !$auth->acl_get('m_revisions'))
	{
		trigger_error('NO_AUTH_VIEW_REVISIONS');
	}
	else if ($revision_to->get('post') != $revision_from->get('post'))
	{
		// @todo - create this language entry
		trigger_error('REVISIONS_FROM_DIFFERENT_POSTS');
	}

	$diff = new phpbb_revisions_diff($revision_from, $revision_to);

	$revisions_compare_title_from = $revision_from->get_id() . ' ' . $user->lang('BY') . ' ' . $revision_from->get('username');
	$revisions_compare_title_to = $revision_to->get_id() . ' ' . $user->lang('BY') . ' ' . $revision_to->get('username');
	$revisions_compare_title_post = '<a href="'	. append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('p' => $revision_to->get('post'))) . '">' . $revision_to->get('subject') . '</a>';

	$template->assign_vars(array(
		'COMPARISON'				=> $compare,
		'DIFF'						=> $diff->render(),
		'REVISIONS_COMPARE_TITLE'	=> $user->lang('REVISIONS_COMPARE_TITLE', $revision_from->get_id(), $revision_to->get_id(), $revisions_compare_title_post),
	));
}
else if ($post_id)
{
	$post = new phpbb_revisions_post($post_id);
	$revisions = $post->load_revisions();

	if ($user->data['user_id'] != $post->get_poster_id() || !$auth->acl_get('m_revisions'))
	{
		trigger_error('NO_AUTH_VIEW_REVISIONS');
	}
	else if (empty($revisions))
	{
		trigger_error('NO_REVISIONS');
	}

	foreach ($revisions as $revision)
	{
		$template->assign_block_vars('revisions', array(
			'POST_VIEW'		=> true,

			'SUBJECT'		=> $revision->get('subject'),
			'TEXT'			=> $revision->get('text'),
			'TEXT_TRUNCATED'=> truncate_string($revision->get('text'), 255, 255, false, '...'),
			'TIME'			=> $user->format_date($revision->get('time')),

			'U_POST'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('p' => $post_id)) . "#p$post_id",

		));
	}
}
else
{
	trigger_error('NO_REVISIONS');
}
page_header('POST_REVISIONS');
$template->set_filenames(array(
	'body'	=> 'revisions_body.html',
));
page_footer();
