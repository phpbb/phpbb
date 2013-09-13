<?php
/**
*
* @package phpbb_revisions
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* A class representing the comparison between two strings
*
* @package phpbb_revisions
*/
class phpbb_revisions_comparison
{
	/**
	* First revision in comparison
	* @var phpbb_revisions_revision
	*/
	private $first;

	/**
	* Last revision in comparison
	* @var phpbb_revisions_revision
	*/
	private $last;

	/**
	* Post content diff object
	* @var phpbb_revisions_diff_engine_base
	*/
	private $text_diff;
	
	/**
	* Post subject diff object
	* @var phpbb_revisions_diff_engine_base
	*/
	private $subject_diff;

	/**
	* Post content diff rendered for display
	* @var string
	*/
	private $text_diff_rendered;
	
	/**
	* Post subject diff rendered for display
	* @var string
	*/
	private $subject_diff_rendered;

	/**
	* Constructor, initialize some class properties
	*
	* @param phpbb_revisions_revision $first First revision in the comparison
	* @param phpbb_revisions_revision $last Last revision in the comparison
	*/
	public function __construct(phpbb_revisions_revision $first, phpbb_revisions_revision $last)
	{
		$this->first = $first;
		$this->last = $last;

		$this->text_diff = new phpbb_revisions_diff_engine_finediff($first->get_text_decoded(),
			$last->get_text_decoded(), 'character');
		$this->subject_diff = new phpbb_revisions_diff_engine_finediff($first->get_subject(),
			$last->get_subject(), 'character');

		$this->text_diff_rendered = bbcode_nl2br($this->text_diff->render());
		$this->subject_diff_rendered = $this->subject_diff->render();
	}

	/**
	* Get the revision IDs that fall between two give revisions
	*
	* @var array $revisions Array of revisions
	* @return array Array of IDs within the range
	*/
	public function get_comparison_range_ids(array $revisions)
	{
		$first_id = $this->first->get_id();
		$last_id = $this->last->get_id();

		if (!$first_id || ($last_id && $first_id > $last_id))
		{
			$revisions = array_reverse($revisions, true);
		}

		$range_ids = array();

		foreach ($revisions as $revision)
		{
			// We continue through the array until we reach
			// the first revision in the comparison
			if (!sizeof($range_ids) && $revision->get_id() != $first_id)
			{
				continue;
			}

			$range_ids[] = $revision->get_id();

			// Once we reach the comparison's stopping point,
			// we no longer need to loop through the array
			if ($revision->get_id() == $last_id)
			{
				break;
			}
		}

		return $range_ids;
	}

	/**
	* Assign template vars for the comparison
	*
	* Note that calling this function multiple times will destroy any previous
	*	output from this function or to the template loop revision. If you must
	*	output multiple posts, catch the output from this function and output
	*	it to the template.
	*
	* @var phpbb_revisions_post $post The post containing the compared revisions
	* @var phpbb_template $template Template object
	* @var phpbb_user $user User object
	* @var phpbb_auth $auth Auth object
	* @var phpbb_request $request Request object
	* @var bool $can_restore Whether or not the user has permission to restore
	*						this post to another revision
	* @var string $phpbb_root_path Relative path to phpBB root
	* @var string $phpEx PHP Extension
	* @var array $ajax_data Array of data to be sent with the JSON request
	* @var bool $full_mode When false, revisions are listed without comparison
	*						or management options
	* @return string parsed template output
	*/
	public function output_template_block(phpbb_revisions_post $post, phpbb_template $template, phpbb_user $user, phpbb_auth $auth, phpbb_request $request, $can_restore, $phpbb_root_path, $phpEx, $full_mode = true)
	{
		// Destroy existing loops
		$template->destroy_block_vars('revision');

		$post_data = $post->get_post_data();
		$revisions = $post->get_revisions();
		if (!$full_mode)
		{
			$revisions = array_reverse($post->get_revisions());
		}

		$current = $post->get_current_revision();
		$revisions[] = $current;

		$range_ids = $this->get_comparison_range_ids($revisions);

		$revision_number = 1;
		$revision_users = $revisions_block = array();
		$first_id = $this->first->get_id();
		$last_id = $this->last->get_id();

		foreach ($revisions as $revision)
		{
			$this_revision_id = $revision->get_id();
			$post_id = $revision->get_post_id();
			$in_range = in_array($this_revision_id, $range_ids);
			$revision_block = array(
				'DATE'				=> $user->format_date($revision->get_time()),
				'ID'				=> $this_revision_id,
				'IN_RANGE'			=> $in_range,
				'NUMBER'			=> $revision_number,
				'REASON'			=> $revision->get_reason(),
				'USERNAME'			=> $revision->get_username(),
				'USER_AVATAR'		=> $revision->get_avatar(20, 20),
				'PROTECTED'			=> $revision->is_protected(),
				'IS_CURRENT_POST'	=> $revision->is_current(),

				'FIRST_IN_COMPARE'	=> $revision->get_id() == $first_id,
				'LAST_IN_COMPARE'	=> $revision->get_id() == $last_id,

				'U_RESTORE'			=> append_sid("{$phpbb_root_path}app.$phpEx/post/$post_id/restore/$this_revision_id"),
				'U_REVISION_VIEW'	=> append_sid("{$phpbb_root_path}app.$phpEx/post/$post_id/revision/$this_revision_id"),
				'U_DELETE'			=> append_sid("{$phpbb_root_path}app.$phpEx/post/$post_id/revision/$this_revision_id/delete"),
				'U_PROTECT'			=> append_sid("{$phpbb_root_path}app.$phpEx/post/$post_id/revision/$this_revision_id/protect"),
				'U_UNPROTECT'		=> append_sid("{$phpbb_root_path}app.$phpEx/post/$post_id/revision/$this_revision_id/unprotect"),

				'S_RESTORE'			=> $can_restore,
				'S_DELETE'			=> $auth->acl_get('m_delete_revisions'),
				'S_PROTECT'			=> !$revision->is_protected() && $auth->acl_get('m_protect_revisions'),
				'S_UNPROTECT'		=> $revision->is_protected() && $auth->acl_get('m_protect_revisions'),

				'DELETE_IMG' 		=> $user->img('icon_post_delete', 'DELETE_REVISION'),
			);

			$template->assign_block_vars('revision', $revision_block);
			$revisions_block[] = $revision_block;

			$revision_users[$revision->get_user_id()] = true;

			if (!$full_mode && $revision_number == 5)
			{
				break;
			}
			$revision_number++;
		}

		$l_compare_summary = $user->lang('REVISION_COUNT', count($revisions)) . '
			' . $user->lang('BY') . '
			' . $user->lang('REVISION_USER_COUNT', sizeof($revision_users));
		$additions_count = $this->text_diff->additions_count() + $this->subject_diff->additions_count();
		$deletions_count = $this->text_diff->deletions_count() + $this->subject_diff->deletions_count();
		$l_lines_added_removed = ($additions_count || $deletions_count) ? ' ' . $user->lang('WITH') . ' ' : '';
		$l_lines_added_removed .= $additions_count ? $user->lang('REVISION_ADDITIONS', $additions_count) . ' ' : '';
		$l_lines_added_removed .= $additions_count && $deletions_count ? strtolower($user->lang('AND')) . ' ' : '';
		$l_lines_added_removed .= $deletions_count ? $user->lang('REVISION_DELETIONS', $deletions_count) : '';

		$l_first_revision = $this->first->get_id() ? $user->lang('REVISION') . ' ' . $this->first->get_id() : $user->lang('CURRENT_REVISION');
		$l_last_revision = $last_id ? $user->lang('REVISION') . ' ' . $last_id : $user->lang('CURRENT_REVISION');
		$u_first_revision = append_sid("{$phpbb_root_path}app.$phpEx/post/$post_id/revision/$first_id");
		$u_last_revision = append_sid("{$phpbb_root_path}app.$phpEx/post/$post_id/revision/$last_id");

		$template->assign_vars(array(
			'S_DISPLAY_COMPARISON'	=> $full_mode,
			'L_LAST_REVISION_TIME'	=> $user->lang('LAST_REVISION_TIME', $user->format_date($current->get_time())),

			'L_COMPARE_SUMMARY'		=> $l_compare_summary,
			'L_LINES_ADDED_REMOVED'	=> $l_lines_added_removed,

			'FIRST_REVISION'		=> $l_first_revision,
			'U_FIRST_REVISION'		=> $u_first_revision,
			'LAST_REVISION'			=> $l_last_revision,
			'U_LAST_REVISION'		=> $u_last_revision,
		));

		if ($request->is_ajax())
		{
			$json_response = new phpbb_json_response();
			$json_response->send(array(
				'revisions_block'		=> $revisions_block,
				'text_diff_rendered'	=> $this->get_text_diff_rendered(),
				'subject_diff_rendered'	=> '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('p' => $post_data['post_id'])) . '">' . $this->get_subject_diff_rendered() . '</a>',
				'comparing_to'			=> $user->lang('COMPARING') . ' <a href="' . $u_first_revision . '">' . $l_first_revision . '<a/> ' . $user->lang('WITH') . ' <a href="' . $u_last_revision . '">' . $l_last_revision . '</a>',
				'lines_changed'			=> $l_lines_added_removed,
			));
		}

		$template->set_filenames(array(
			'revisions_comparison_list'	=> 'revisions_comparison_list.html',
		));

		return $template->assign_display('revisions_comparison_list', '', true);
	}

	/**
	* Return the rendered subject diff for display
	*
	* @return string
	*/
	public function get_subject_diff_rendered()
	{
		return $this->subject_diff_rendered;
	}

	/**
	* Return the rendered text diff for display
	*
	* @return string
	*/
	public function get_text_diff_rendered()
	{
		// The \n below is a literal string, rather than the actual new line
		// character. We do this because '\n' is written in the diff itself
		return str_replace('\n', '<br />', $this->text_diff_rendered);
	}
}
