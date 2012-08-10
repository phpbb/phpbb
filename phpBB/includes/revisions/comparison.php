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
	* @param int $post_id Post ID
	* @param dbal $dbal phpBB DBAL object
	*/
	public function __construct(phpbb_revisions_revision $first, phpbb_revisions_revision $last)
	{
		$this->first = $first;
		$this->last = $last;

		$this->text_diff = new phpbb_revisions_diff_engine_finediff($first->get_text_decoded(),
			$last->get_text_decoded());
		$this->subject_diff = new phpbb_revisions_diff_engine_finediff($first->get_subject(),
			$last->get_subject());

		$this->text_diff_rendered = bbcode_nl2br($this->text_diff->render());
		$this->subject_diff_rendered = $this->subject_diff->render();
	}

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

	public function output_template_block(phpbb_revisions_post $post, phpbb_template $template, phpbb_user $user, phpbb_auth $auth, $can_revert, $phpbb_root_path, $phpEx)
	{
		$post_data = $post->get_post_data();
		$revisions = $post->get_revisions();

		$current = $post->get_current_revision();
		$revisions[] = $current;

		$range_ids = $this->get_comparison_range_ids($revisions);

		$revision_number = 1;
		$revision_users = array();
		$first_id = $this->first->get_id();
		$last_id = $this->last->get_id();

		foreach ($revisions as $revision)
		{
			$this_revision_id = $revision->get_id();
			$post_id = $revision->get_post_id();

			$template->assign_block_vars('revision', array(
				'DATE'				=> $user->format_date($revision->get_time()),
				'ID'				=> $this_revision_id,
				'IN_RANGE'			=> in_array($this_revision_id, $range_ids),
				'NUMBER'			=> $revision_number,
				'REASON'			=> $revision->get_reason(),
				'USERNAME'			=> $revision->get_username(),
				'USER_AVATAR'		=> $revision->get_avatar(20, 20),
				'PROTECTED'			=> $revision->is_protected(),
				'IS_CURRENT_POST'	=> $revision->is_current(),

				'FIRST_IN_COMPARE'	=> $revision->get_id() == $first_id,
				'LAST_IN_COMPARE'	=> $revision->get_id() == $last_id,

				'U_REVERT_TO'		=> $can_revert ? append_sid("{$phpbb_root_path}revisions.$phpEx", array('revert' => $this_revision_id)) : '',
				'U_REVISION_VIEW'	=> append_sid("{$phpbb_root_path}revisions.$phpEx", array('r' => $this_revision_id)),
				'U_DELETE'			=> $auth->acl_get('m_delete_revisions') ? append_sid("{$phpbb_root_path}revisions.$phpEx", array('delete' => $this_revision_id)) : '',
				'U_PROTECT'			=> (!$revision->is_protected() && $auth->acl_get('m_protect_revisions')) ? append_sid("{$phpbb_root_path}revisions.$phpEx", array('protect' => $this_revision_id)) : '',
				'U_UNPROTECT'		=> ($revision->is_protected() && $auth->acl_get('m_protect_revisions')) ? append_sid("{$phpbb_root_path}revisions.$phpEx", array('unprotect' => $this_revision_id)) : '',

				'DELETE_IMG' 		=> $user->img('icon_post_delete', 'DELETE_REVISION'),
			));

			$revision_users[$revision->get_user_id()] = true;
			$revision_number++;
		}

		$l_compare_summary = $user->lang('REVISION_COUNT', count($revisions)) . '
			' . $user->lang('BY') . '
			' . $user->lang('REVISION_USER_COUNT', sizeof($revision_users));
		$additions_count = $this->text_diff->additions_count() + $this->subject_diff->additions_count();
		$deletions_count = $this->text_diff->deletions_count() + $this->subject_diff->deletions_count();
		$l_lines_added_removed = $additions_count ? $user->lang('REVISION_ADDITIONS', $additions_count) . ' ' : '';
		$l_lines_added_removed .= ($l_lines_added_removed && $additions_count && $deletions_count) ? strtolower($user->lang('AND')) . ' ' : '';
		$l_lines_added_removed .= $deletions_count ? $user->lang('REVISION_DELETIONS', $deletions_count) : '';

		$template->assign_vars(array(
			'S_DISPLAY_COMPARISON'	=> true,
			'L_LAST_REVISION_TIME'	=> $user->lang('LAST_REVISION_TIME', $user->format_date($current->get_time())),

			'L_COMPARE_SUMMARY'		=> $l_compare_summary,
			'L_LINES_ADDED_REMOVED'	=> $l_lines_added_removed,

			'FIRST_REVISION'		=> $this->first->get_id() ? strtolower($user->lang('REVISION')) . ' ' . $this->first->get_id() : $user->lang('CURRENT_REVISION'),
			'U_FIRST_REVISION'		=> append_sid("{$phpbb_root_path}revisions.$phpEx", ($first_id ? array('r' => $this->first->get_id()) : array('p' => $post_data['post_id']))),
			'LAST_REVISION'			=> $last_id ? strtolower($user->lang('REVISION')) . ' ' . $last_id : $user->lang('CURRENT_REVISION'),
			'U_LAST_REVISION'		=> append_sid("{$phpbb_root_path}revisions.$phpEx", ($last_id ? array('r' => $last_id) : array('p' => $post_data['post_id']))),
		));
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
		return $this->text_diff_rendered;
	}
}
