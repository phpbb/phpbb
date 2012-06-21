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
		// We don't want to load all of the revision's data yet, as we will do that once we have the post ID
		// Right now all we want to do is get the post ID, so we pass the third parameter as false
		$temp_rev = new phpbb_revisions_revision($first_id, $db, false);
		$post_id = $temp_rev->get_post_id();
	}
}

// Regardless of the provided post ID in the URL, if we are trying to revert a revision,
// we set the post ID to the revision's post ID
if ($revert)
{
	$revert_revision = new phpbb_revisions_revision($revert, $db, false);
	$post_id = $revert_revision->get_post_id();
}

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
	trigger_error('NO_REVISIONS_POST');
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

if ($revert && $revert_confirm && check_form_key('revert_form', 120))
{
	if (!$can_revert)
	{
		trigger_error('NO_AUTH_REVERT');
	}
	else if (empty($revisions[$revert]))
	{
		trigger_error('ERROR_REVISION_NOT_FOUND');
	}

	$revert_result = $post->revert($revert);
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

$current = $post->get_current_revision();

// If we are reverting, the from revision is the current post
// Otherwise, it's our first revision in the array
$first = $revert ? $current : current($revisions);

// If we are reversting the, the to revision is the given revision ID
// Otherwise, it is the final (i.e. current) revision
$last = $revert ? $revisions[$revert] : $current;

// Ensure that we have the proper revision IDs
$first_id = $first->get_id();
$last_id = $last->get_id();

// Let's get our diff driver
// @todo #1 - either pick a diff engine to use forever, or make this dynamic; for now we go with what we have
// @todo #2 - make a new function for this... e.g. generate_text_diff($from, $to[, $engine = 'finediff'])
$text_diff = new phpbb_revisions_diff_engine_finediff($first->get_text_decoded(), $last->get_text_decoded());
$subject_diff = new phpbb_revisions_diff_engine_finediff($first->get_subject(), $last->get_subject());

$text_diff_rendered = bbcode_nl2br($text_diff->render());
$subject_diff_renedered = bbcode_nl2br($subject_diff->render());

$reverse = $first_id > $last_id || !$last->is_current();

if ($reverse)
{
	$revisions = array_reverse($revisions, true);
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

		'IN_RANGE'			=> true, //@todo: work out this logic >> ($last_id == $current->get_id() || $revision->get_id() <= $last_id) && $revision->get_id() >= $first_id,

		'U_REVISION_VIEW'	=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('r' => $revision->get_id())),
		'U_POST'			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('p' => $revision->get_post_id())). '#p' . $revision->get_post_id(),
		'U_REVERT_TO'		=> $can_revert ? append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $revision->get_post_id(), 'revert' => $revision->get_id())) : '',
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
	'POST_USERNAME'		=> get_username_string('full', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
	'U_PROFILE'			=> get_username_string('profile', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
	
	'RANK_TITLE'		=> $post_data['rank_title'],
	'RANK_IMG'			=> $post_data['rank_image'],

	'AVATAR'			=> get_user_avatar($post_data['user_avatar'], $post_data['user_avatar_type'], $post_data['user_avatar_width'], $post_data['user_avatar_height']),

	'POST_DATE'			=> $user->format_date($post_data['post_time']),
	'POST_SUBJECT'		=> $revert && !$revert_confirm ? $subject_diff_renedered : $current->get_subject(),
	'MESSAGE'			=> $revert && !$revert_confirm ? $text_diff_rendered : $current->get_text(),
	'SIGNATURE'			=> ($post_data['enable_sig']) ? $post_data['user_sig_parsed'] : '',

	'POSTER_JOINED'		=> $user->format_date($post_data['user_regdate']),
	'POSTER_POSTS'		=> $post_data['user_posts'],
	'POSTER_LOCATION'	=> $post_data['user_from'],

	'MINI_POST_IMG'		=> $user->img('icon_post_target', 'POST'),
	'U_MINI_POST'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $post_data['post_id']) . '#p' . $post_data['post_id'],

	'POST_ID'			=> $post_data['post_id'],
	'POSTER_ID'			=> $post_data['poster_id'],

	'L_LAST_REVISION_TIME'	=> $user->lang('LAST_REVISION_TIME', $user->format_date($current->get_time())),

	'TEXT_DIFF'				=> $text_diff_rendered,
	'SUBJECT_DIFF'			=> $subject_diff_renedered,
	'L_COMPARE_SUMMARY'		=> $l_compare_summary,
	'L_LINES_ADDED_REMOVED'	=> $l_lines_added_removed,
));

$page_title = 'REVISIONS_COMPARE_TITLE';
$tpl_name = 'revisions_body.html';

$bad_form = ($revert_confirm && !check_form_key('revert_form', 120));
if ($revert && (!$revert_confirm || $bad_form))
{
	if (!$can_revert)
	{
		trigger_error('NO_AUTH_REVERT');
	}

	add_form_key('revert_form');

	$template->assign_vars(array(
		'U_ACTION'			=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $post_id, 'revert' => $revert)),
		'BAD_FORM'			=> $bad_form,
		'S_HIDDEN_FIELDS'	=> build_hidden_fields(array(
			'post_id'	=> $post_id,
			'revert'	=> $revert,
		)),
	));

	$page_title = 'REVISIONS_REVERT_TITLE';
	$tpl_name = 'revisions_revert_body.html';
}

page_header($page_title, false);

$template->set_filenames(array(
	'body'		=> $tpl_name,
));

page_footer();
