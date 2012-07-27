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
$user->setup(array('viewtopic', 'revisions'));

$post_id		= $request->variable('p', 0);
$revision_id	= $request->variable('r', 0);
$revert_id		= $request->variable('revert', 0);
$compare		= $request->variable('compare', '');
$delete_ids		= $request->variable('delete_ids', array(0));

$display_comparison = true;
$revert_confirm = $request->is_set_post('confirm');

// Variables for first and last revisions for comparison
$first = $last = null;
$first_id = $last_id = 0;

// If we are given some potential comparison information, try to grab the IDs from the URL
if ($compare)
{
	$matches = array();
	preg_match('/([0-9]+)\.{3}([0-9]+)/', $compare, $matches);

	// Note that $matches[0], in the case of a match, will be X...Y, where X and Y are the given numbers
	// We don't actually have any use for it, we just want the X and Y, so that's what we look at below
	// If we don't see the X...Y pattern, set starting revision ID to the integer value of $compare
	$first_id = (sizeof($matches) == 3) ? $matches[1] : (int) $compare;
	$last_id = (sizeof($matches) == 3) ? $matches[2] : 0;

	// If we don't have a post_id, use the given IDs to figure it out
	// Note that if we don't have a first_id, we don't have a last_id
	if ($first_id && !$post_id)
	{
		$post_id = phpbb_get_revision_post_id($first_id, $db);
	}
}

// Regardless of the provided post ID in the URL, if we are trying to view or revert a revision,
// we set the post ID to the revision's post ID
$post_id = ($revert_id || $revision_id) ? phpbb_get_revision_post_id($revert_id ?: $revision_id, $db) : $post_id;

// If we still can't manage to come up with a post ID, we have nothing else to do here
if (!$post_id)
{
	trigger_error('NO_POST');
}

$post = new phpbb_revisions_post($post_id, $db, $config, $auth);
$post_data = $post->get_post_data();

if (empty($post_data['post_id']))
{
	trigger_error('NO_POST');
}

$revisions = $post->get_revisions();
$total_revisions = sizeof($revisions);

if (!$total_revisions)
{
	trigger_error($user->lang('NO_REVISIONS_POST') . '
		<br /><a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('p' => $post_id)) . "#p$post_id" . '">' . $user->lang('RETURN_POST') . '</a>');
}

$can_revert_wiki = $post_data['post_wiki']
	&& $auth->acl_getf('f_wiki_edit', $post_data['forum_id'])
	&& !$post_data['post_edit_locked'];
$can_revert_own = $user->data['user_id'] == $post_data['poster_id']
	&& $auth->acl_getf('f_revisions', $post_data['forum_id'])
	&& !$post_data['post_edit_locked'];
$can_revert = $auth->acl_get('m_revisions') || $can_revert_wiki || $can_revert_own;

$can_view_wiki_revisions = $post_data['post_wiki'] && $auth->acl_getf('f_wiki_edit', $post_data['forum_id']);
$can_view_own_revisions = $user->data['user_id'] == $post_data['poster_id'] && $auth->acl_getf('f_revisions', $post_data['forum_id']);
$can_view_post_revisions = $auth->acl_get('m_revisions') || $can_view_wiki_revisions ||  $can_view_own_revisions;

if (!$can_view_post_revisions)
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		trigger_error('NO_AUTH_VIEW_REVISIONS');
	}

	login_box('', $user->lang('LOGIN_REVISION'));
}

$l_return = '<br /><a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('p' => $post_id)) . "#p$post_id" . '">' . $user->lang('RETURN_POST') . '</a>
			<br /><a href="' . append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $post_id)) . '">' . $user->lang('RETURN_REVISION') . '</a>';

if (sizeof($delete_ids))
{
	if (!$auth->acl_get('m_revisions'))
	{
		trigger_error($user->lang('NO_AUTH_DELETE_REVISIONS') . $l_return);
	}

	if (confirm_box(true))
	{
		// We have a separate counter instead of relying on sizeof()
		// because we can potentially skip IDs within the loop
		$count = 0;
		foreach ($delete_ids as $delete_id)
		{
			// We only want to delete IDs of revisions on the current post
			// If somehow someone injected IDs of revisions on another post
			// we skip them
			if (empty($revisions[$delete_id]))
			{	
				continue;
			}

			$revisions[$delete_id]->delete();
			$count++;
		}

		// Assuming we actually did something, we ouput a notification at the top of the page
		if ($count)
		{
			$template->assign_vars(array(
				'S_REVISION_DELETED'			=> true,
				'L_REVISIONS_DELETED_SUCCESS'	=> $user->lang('REVISIONS_DELETED_SUCCESS', $count),
			));

			$post_data = $post->get_post_data(true);
			$revisions = $post->get_revisions(true);

			if (!sizeof($revisions))
			{
				trigger_error($user->lang('REVISIONS_DELETED_SUCCESS_NO_MORE') . '
					<br /><a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('p' => $post_id)) . "#p$post_id" . '">' . $user->lang('RETURN_POST') . '</a>');
			}
		}
	}
	else
	{
		$s_hidden_fields = build_hidden_fields(array(
			'p'				=> $post_id,
			'delete_ids'	=> $delete_ids,
		));
		confirm_box(false, 'REVISION_DELETE', $s_hidden_fields);
	}
}

if ($revert_id && $revert_confirm && check_form_key('revert_form', 120))
{
	if (!$can_revert)
	{
		trigger_error($user->lang('NO_AUTH_REVERT') . $l_return);
	}
	else if (empty($revisions[$revert_id]))
	{
		trigger_error($user->lang('ERROR_REVISION_NOT_FOUND') . $l_return);
	}
	else if ($this->post_data['post_edit_locked'] && !$this->auth->acl_get('m_revisions'))
	{
		trigger_error($lang->('ERROR_POST_EDIT_LOCKED') . $l_return);
	}

	$revert_result = $post->revert($revert_id);
	if ($revert_result === phpbb_revisions_post::REVISION_REVERT_SUCCESS)
	{
		// Because we've changed things up, we need to update our arrays
		$post_data = $post->get_post_data(true);
		$revisions = $post->get_revisions(true);

		$template->assign_vars(array(
			'S_POST_REVERTED'	=> true,
		));
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

		trigger_error($user->lang($lang) . $l_return);
	}
}

$current = $post->get_current_revision();

if ($compare && $first_id && $last_id)
{
	$first = $revisions[$first_id];
	$last = $revisions[$last_id];
}
// Handle comparison cases with no final number in the comparison, e.g. 2...
else if ($compare && $first_id)
{
	$first = $revisions[$first_id];
	$last = $current;
}
else if ($revert_id)
{
	$first = $current;
	$last = $revisions[$revert_id];
}
else if ($revision_id)
{
	$display_comparison = false;
	$current = $revisions[$revision_id];
}
else
{
	$first = current($revisions);
	$last = $current;
}

if ($display_comparison)
{
	// @todo #1 - either pick a diff engine to use forever, or make this dynamic; for now we go with what we have
	// @todo #2 - make a new function for this... e.g. generate_text_diff($from, $to[, $engine = 'finediff'])
	$text_diff = new phpbb_revisions_diff_engine_finediff($first->get_text_decoded(), $last->get_text_decoded());
	$subject_diff = new phpbb_revisions_diff_engine_finediff($first->get_subject(), $first->get_subject());

	$text_diff_rendered = bbcode_nl2br($text_diff->render());
	$subject_diff_renedered = $subject_diff->render();

	// We always want the first revision in the comparison at the top, so if
	// the start revision is newer than the ending revision, reverse the list
	if ($first_id > $last_id)
	{
		$revisions = array_reverse($revisions, true);
	}

	$range_ids = array();
	foreach ($revisions as $revision)
	{
		if ($revision != $first && !sizeof($range_ids))
		{
			continue;
		}

		$range_ids[] = $revision->get_id();

		if ($revision == $last)
		{
			break;
		}
	}

	$revision_number = 1;
	$revision_users = array();
	foreach ($revisions as $revision)
	{
		$this_revision_id = $revision->get_id();

		$template->assign_block_vars('revision', array(
			'DATE'				=> $user->format_date($revision->get_time()),
			'ID'				=> $this_revision_id,
			'IN_RANGE'			=> in_array($this_revision_id, $range_ids),
			'NUMBER'			=> $revision_number,
			'REASON'			=> $revision->get_reason(),
			'USERNAME'			=> $revision->get_username(),
			'USER_AVATAR'		=> $revision->get_avatar(20, 20),
			'PROTECTED'			=> $revision->is_protected(), // @todo - Find a good "lock" icon (maybe phpBB already has one?)

			'S_DELETE'			=> $auth->acl_get('m_revisions'),

			'U_REVERT_TO'		=> $can_revert ? append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $post_id, 'revert' => $this_revision_id)) : '',
			'U_REVISION_VIEW'	=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('r' => $this_revision_id)),
			'U_POST'			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('f' => $post_data['forum_id'], 't' => $post_data['topic_id'], 'p' => $post_id)) . '#p' . $post_id,
		));

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
		'S_DISPLAY_COMPARISON'	=> true,
		'L_LAST_REVISION_TIME'	=> $user->lang('LAST_REVISION_TIME', $user->format_date($current->get_time())),

		'L_COMPARE_SUMMARY'		=> $l_compare_summary,
		'L_LINES_ADDED_REMOVED'	=> $l_lines_added_removed,

		'FIRST_REVISION'		=> $first->get_id() ? strtolower($user->lang('REVISION')) . ' ' . $first->get_id() : $user->lang('CURRENT_REVISION'),
		'U_FIRST_REVISION'		=> append_sid("{$phpbb_root_path}revisions.$phpEx", ($first->get_id() ? array('r' => $first->get_id()) : array('p' => $post_id))),
		'LAST_REVISION'			=> $last->get_id() ? strtolower($user->lang('REVISION')) . ' ' . $last->get_id() : $user->lang('CURRENT_REVISION'),
		'U_LAST_REVISION'		=> append_sid("{$phpbb_root_path}revisions.$phpEx", ($last->get_id() ? array('r' => $last->get_id()) : array('p' => $post_id))),

		'POST_USERNAME'		=> get_username_string('full', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
		'U_PROFILE'			=> get_username_string('profile', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
		
		'RANK_TITLE'		=> $post_data['rank_title'],
		'RANK_IMG'			=> $post_data['rank_image'],

		'AVATAR'			=> get_user_avatar($post_data['user_avatar'], $post_data['user_avatar_type'], $post_data['user_avatar_width'], $post_data['user_avatar_height']),

		'POST_DATE'			=> $user->format_date($post_data['post_time']),
		'POST_SUBJECT'		=> $display_comparison ? $subject_diff_renedered : $current->get_subject(),
		'MESSAGE'			=> $display_comparison ? $text_diff_rendered : $current->get_text(),
		'SIGNATURE'			=> ($post_data['enable_sig']) ? $post_data['user_sig_parsed'] : '',

		'POSTER_JOINED'		=> $user->format_date($post_data['user_regdate']),
		'POSTER_POSTS'		=> $post_data['user_posts'],
		'POSTER_LOCATION'	=> $post_data['user_from'],

		'POST_IMG'			=> $user->img('icon_post_target', 'POST'),

		'POST_ID'			=> $post_data['post_id'],
		'POSTER_ID'			=> $post_data['poster_id'],
	));
}


$navlinks = array(
	array(
		'name'	=> $post_data['forum_name'],
		'link'	=> append_sid("{$phpbb_root_path}viewforum.$phpEx", array('f' => $post_data['forum_id'])),
	),
	array(
		'name'	=> $post_data['topic_title'],
		'link'	=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('f' => $post_data['forum_id'], 't' => $post_data['topic_id'])) . "#p$post_id",
	),
);

$page_title = 'REVISIONS_COMPARE_TITLE';
$tpl_name = 'revisions_body.html';

$bad_form = ($revert_confirm && !check_form_key('revert_form', 120));
if ($revert_id && (!$revert_confirm || $bad_form))
{
	if (!$can_revert)
	{
		trigger_error('NO_AUTH_REVERT');
	}

	add_form_key('revert_form');

	$template->assign_vars(array(
		'U_ACTION'			=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $post_id, 'revert' => $revert_id)),
		'BAD_FORM'			=> $bad_form,
		'VIEWING_REVERT'	=> true,
		'S_HIDDEN_FIELDS'	=> build_hidden_fields(array(
			'post_id'	=> $post_id,
			'revert'	=> $revert_id,
		)),
	));

	$page_title = 'REVISIONS_REVERT_TITLE';
	$tpl_name = 'revisions_revert_body.html';

	$navlinks[] = array(
		'name'	=> $user->lang('REVERTING_POST'),
		'link'	=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $post_id, 'revert' => $revert_id)),
	);
}
else
{
	$navlinks[] = array(
		'name'	=> $user->lang('VIEWING_POST_REVISION_HISTORY'),
		'link'	=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $post_id)),
	);

	if ($revision_id)
	{
		$navlinks[] = array(
			'name'	=> $user->lang('VIEWING_POST_REVISION'),
			'link'	=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('r' => $revision_id)),
		);
	}
}

foreach ($navlinks as $link)
{
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=> $link['name'],
		'U_VIEW_FORUM'	=> $link['link'],
	));
}

$template->assign_vars(array(
	'U_VIEW_REVISIONS'	=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $post_id)),
	'U_VIEW_POST'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('f' => $post_data['forum_id'], 't' => $post_data['topic_id'], 'p' => $post_id)) . '#p' . $post_id,
));

page_header($page_title, false);

$template->set_filenames(array(
	'body'		=> $tpl_name,
));

page_footer();
