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
$user->setup('viewtopic');

// Initialize variables
$post_id = $request->variable('p', 0);
// Single revision to view
$revision_id = $request->variable('r', 0);
// Starting revision for comparison
$from = $request->variable('from', 0);
// Ending revision for comparison
$to = $request->variable('to', 0);

if ($revision_id || ($to && !$from))
{
	$revision_id = $revision_id ?: $to;
	$revision = new phpbb_revisions_revision($revision_id);

	if ($user->data['user_id'] != $revision->get_poster_id() || !$auth->acl_get('m_revisions'))
	{
		// @todo - create this language entry
		trigger_error('NO_AUTH_VIEW_REVISIONS');
	}

	$template->assign_vars(array(
		'SUBJECT'	=> $revision->get('subject'),
		'TEXT'		=> $revision->get('text'),
		'TIME'		=> $user->format_date($revision->get('time')),
	));
}
else if ($to && $from)
{
	$revision_to = new phpbb_revisions_revision($to);
	$revision_from = new phpbb_revisions_revision($from);

	if ($user->data['user_id'] != $revision_to->get_poster_id() || !$auth->acl_get('m_revisions'))
	{
		// @todo - create this language entry
		trigger_error('NO_AUTH_VIEW_REVISIONS');
	}
	else if ($revision_to->get('post') != $revision_from->get('post'))
	{
		// @todo - create this language entry
		trigger_error('REVISIONS_FROM_DIFFERENT_POSTS');
	}

	$comparison = $revision_to->compare_to($revision_from);

	print_r($comparison);
}
else if ($post_id)
{
	$post = new phpbb_revisions_post();
	$revisions = $post->load_revisions($post_id);

	if ($user->data['user_id'] != $post->get_poster_id() || !$auth->acl_get('m_revisions'))
	{
		// @todo - create this language entry
		trigger_error('NO_AUTH_VIEW_REVISIONS');
	}
	else if (empty($revisions) || $revisions)
	{
		trigger_error('NO_REVISIONS');
	}

	foreach ($revisions as $revision)
	{
		$template->assign_block_vars('revisions', array(
			'SUBJECT'		=> $revision->get('subject'),
			'TEXT'			=> $revision->get('text'),
			'TIME'			=> $user->format_date($revision->get('time')),
		));
	}
}
else
{
	trigger_error('NO_REVISIONS');
}
