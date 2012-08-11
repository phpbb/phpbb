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
$first_id 		= $request->variable('first', 0);
$last_id 		= $request->variable('last', 0);
$delete			= $request->variable('delete', 0);
$protect		= $request->variable('protect', 0);
$unprotect		= $request->variable('unprotect', 0);

$display_comparison = true;
$revert_confirm = $request->is_set_post('confirm');

// Variables for first and last revisions for comparison
$first = $last = null;

// Attempt to obtain the post ID using give revision IDs
$post_id = $post_id ?: phpbb_get_revision_post_id(array(
	$first_id,
	$last_id,
	$revert_id,
	$revision_id,
	$delete,
	$protect,
	$unprotect,
), $db);

// If we still don't have a post ID, there is nothing else to do here
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

if ($delete || $protect || $unprotect)
{
	if ($delete)
	{
		if (!$auth->acl_get('m_delete_revisions', $post_data['forum_id']))
		{
			if ($request->is_ajax())
			{
				$json_response = new phpbb_json_response();
				$json_response->send(array(
					'success' => false,
					'message' => $user->lang('NO_AUTH_DELETE_REVISIONS'),
				));
			}

			trigger_error($user->lang('NO_AUTH_DELETE_REVISIONS') . $l_return);
		}

		$action = 'delete';
		$action_id = $delete;
		$action_confirm_lang = 'REVISION_DELETE';
		$action_success_lang = 'REVISION_DELETED_SUCCESS';
	}
	else if ($protect)
	{
		if (!$auth->acl_get('m_protect_revisions', $post_data['forum_id']))
		{
			if ($request->is_ajax())
			{
				$json_response = new phpbb_json_response();
				$json_response->send(array(
					'success' => false,
					'message' => $user->lang('NO_AUTH_PROTECT_REVISIONS'),
				));
			}

			trigger_error($user->lang('NO_AUTH_PROTECT_REVISIONS') . $l_return);
		}

		$action = 'protect';
		$action_id = $protect;
		$action_confirm_lang = 'REVISION_PROTECT';
		$action_success_lang = 'REVISION_PROTECTED_SUCCESS';
	}
	else if ($unprotect)
	{
		if (!$auth->acl_get('m_protect_revisions', $post_data['forum_id']))
		{
			if ($request->is_ajax())
			{
				$json_response = new phpbb_json_response();
				$json_response->send(array(
					'success' => false,
					'message' => $user->lang('NO_AUTH_UNPROTECT_REVISIONS'),
				));
			}

			trigger_error($user->lang('NO_AUTH_UNPROTECT_REVISIONS') . $l_return);
		}

		$action = 'unprotect';
		$action_id = $unprotect;
		$action_confirm_lang = 'REVISION_UNPROTECT';
		$action_success_lang = 'REVISION_UNPROTECTED_SUCCESS';
	}

	if (confirm_box(true))
	{
		if (!empty($revisions[$action_id]))
		{	
			$revisions[$action_id]->$action();

			$template->assign_vars(array(
				'L_REVISIONS_ACTION_SUCCESS'	=> $user->lang($action_success_lang),
			));

			$post_data = $post->get_post_data(true);
			$revisions = $post->get_revisions(true);

			if (!sizeof($revisions))
			{
				if ($request->is_ajax())
				{
					$json_response = new phpbb_json_response();
					$json_response->send(array(
						'success' => true,
						'message' => $user->lang('REVISION_DELETED_SUCCESS_NO_MORE'),
					));
				}

				trigger_error($user->lang('REVISION_DELETED_SUCCESS_NO_MORE') . '
					<br /><a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('p' => $post_id)) . "#p$post_id" . '">' . $user->lang('RETURN_POST') . '</a>');
			}

			if ($request->is_ajax())
			{
				$data = array(
					'success'			=> true,
					'message'			=> $user->lang($action_success_lang),
					'link_protect'		=> '(<a href="' . append_sid("{$phpbb_root_path}revisions.$phpEx", array('protect' => $unprotect)) . '" data-ajax="revisions.protect">' . $user->lang('PROTECT') . '</a>)',
					'link_unprotect'	=> '(<a href="' . append_sid("{$phpbb_root_path}revisions.$phpEx", array('unprotect' => $protect)) . '" data-ajax="revisions.unprotect">' . $user->lang('UNPROTECT') . '</a>)',
				);
				$json_response = new phpbb_json_response();
				$json_response->send($data);
			}
		}
	}
	else
	{
		$s_hidden_fields = build_hidden_fields(array(
			'p'				=> $post_id,
			'delete'		=> $delete,
			'protect'		=> $protect,
			'unprotect'		=> $unprotect,
		));
		confirm_box(false, $action_confirm_lang, $s_hidden_fields);
	}
}

if ($revert_id && $revert_confirm && check_form_key('revert_form', 120))
{
	$error = '';

	if (!$can_revert)
	{
		$error = 'NO_AUTH_REVERT';
	}
	else if (empty($revisions[$revert_id]))
	{
		$error = 'ERROR_REVISION_NOT_FOUND';
	}
	else if ($this->post_data['post_edit_locked'] && !$this->auth->acl_get('m_revisions'))
	{
		$error = 'ERROR_POST_EDIT_LOCKED';
	}

	if ($error)
	{
		if ($request->is_ajax())
		{
			$json_response = new phpbb_json_response();
			$json_response->send(array(
				'success' => false,
				'message' => $user->lang($error),
			));
		}

		trigger_error($user->lang($error) . $l_return);
	}

	$revert_result = $post->revert($revert_id);
	if ($revert_result === phpbb_revisions_post::REVISION_REVERT_SUCCESS)
	{
		// Because we've changed things up, we need to update our arrays
		$post_data = $post->get_post_data(true);
		$revisions = $post->get_revisions(true);

		$template->assign_vars(array(
			'L_REVISIONS_ACTION_SUCCESS'	=> $user->lang('POST_REVERTED_SUCCESS'),
		));

		if ($request->is_ajax())
		{
			$json_response = new phpbb_json_response();
			$json_response->send(array(
				'success' => true,
				'message' => $user->lang('POST_REVERTED_SUCCESS'),
			));
		}

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

		if ($request->is_ajax())
		{
			$json_response = new phpbb_json_response();
			$json_response->send(array(
				'success' => false,
				'message' => $user->lang($lang),
			));
		}

		trigger_error($user->lang($lang) . $l_return);
	}
}

$current = $post->get_current_revision();

if ($first_id || $last_id)
{
	$first = $first_id ? $revisions[$first_id] : $current;
	$last = $last_id ? $revisions[$last_id] : $current;
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

$comparison = null;
if ($display_comparison)
{
	$comparison = new phpbb_revisions_comparison($first, $last);
	$comparison->output_template_block($post, $template, $user, $auth, $request, $can_revert, $phpbb_root_path, $phpEx);
}

$template->assign_vars(array(
	'POST_USERNAME'		=> get_username_string('full', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
	'U_PROFILE'			=> get_username_string('profile', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
	
	'RANK_TITLE'		=> $post_data['rank_title'],
	'RANK_IMG'			=> $post_data['rank_image'],

	'AVATAR'			=> get_user_avatar($post_data['user_avatar'], $post_data['user_avatar_type'], $post_data['user_avatar_width'], $post_data['user_avatar_height']),

	'POST_DATE'			=> $user->format_date($post_data['post_time']),
	'POST_SUBJECT'		=> $comparison ? $comparison->get_subject_diff_rendered() : $current->get_subject(),
	'CURRENT_SUBJECT' 	=> $current->get_subject(),
	'MESSAGE'			=> $comparison ? $comparison->get_text_diff_rendered() : $current->get_text(),
	'SIGNATURE'			=> ($post_data['enable_sig']) ? $post_data['user_sig_parsed'] : '',

	'POSTER_JOINED'		=> $user->format_date($post_data['user_regdate']),
	'POSTER_POSTS'		=> $post_data['user_posts'],
	'POSTER_LOCATION'	=> $post_data['user_from'],

	'POST_IMG'			=> $user->img('icon_post_target', 'POST'),

	'POST_ID'			=> $post_data['post_id'],
	'POSTER_ID'			=> $post_data['poster_id'],

	'L_VIEWING_POST_REVISION_EXPLAIN'	=> !$display_comparison ? $user->lang('VIEWING_POST_REVISION_EXPLAIN', $current->get_username() . $current->get_avatar(20, 20), $user->format_date($current->get_time())) : '',

	'U_VIEW_REVISIONS'	=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('p' => $post_id)),
	'U_VIEW_POST'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('f' => $post_data['forum_id'], 't' => $post_data['topic_id'], 'p' => $post_id)) . '#p' . $post_id,
));

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

$page_title = $display_comparison ? 'REVISIONS_COMPARE_TITLE' : 'REVISION_VIEW_TITLE';
$tpl_name = $display_comparison ? 'revisions_body.html' : 'revisions_view_body.html';

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

page_header($user->lang($page_title), false);

$template->set_filenames(array(
	'body'		=> $tpl_name,
));

page_footer();
