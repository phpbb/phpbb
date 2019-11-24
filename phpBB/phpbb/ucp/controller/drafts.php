<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\ucp\controller;

class drafts
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth					Auth object
	 * @param \phpbb\config\config				$config					Config object
	 * @param \phpbb\db\driver\driver_interface	$db						Database object
	 * @param \phpbb\controller\helper			$helper					Controller helper object
	 * @param \phpbb\language\language			$language				Language object
	 * @param \phpbb\request\request			$request				Request object
	 * @param \phpbb\template\template			$template				Template object
	 * @param \phpbb\user						$user					User object
	 * @param string							$root_path				phpBB root path
	 * @param string							$php_ext				php File extension
	 * @param array								$tables					phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth		= $auth;
		$this->config	= $config;
		$this->db		= $db;
		$this->helper	= $helper;
		$this->language	= $language;
		$this->request	= $request;
		$this->template	= $template;
		$this->user		= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	public function main($mode)
	{
		$pm_drafts = $mode === 'pm';

		$this->language->add_lang('posting');

		$this->template->assign_var('S_SHOW_DRAFTS', true);

		$submit		= $this->request->is_set_post('submit');
		$delete		= $this->request->is_set_post('delete');
		$edit		= $this->request->is_set('edit');
		$draft_id	= $this->request->variable('edit', 0);

		$form_key = 'ucp_draft';
		add_form_key($form_key);

		$s_hidden_fields = $edit ? '<input type="hidden" name="edit" value="' . $draft_id . '" />' : '';
		$draft_subject = '';
		$draft_message = '';

		include($this->root_path . 'includes/message_parser.' . $this->php_ext);
		$message_parser = new \parse_message();

		if ($pm_drafts)
		{
			if (!function_exists('get_folder'))
			{
				include($this->root_path . 'includes/functions_privmsgs.' . $this->php_ext);
			}

			get_folder($this->user->data['user_id']);
		}

		$route = $pm_drafts ? 'ucp_pm_drafts' : 'ucp_manage_drafts';
		$return = '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $this->helper->route($route) . '">', '</a>');

		if ($delete)
		{
			if (!check_form_key($form_key))
			{
				return trigger_error($this->language->lang('FORM_INVALID') . $return, E_USER_WARNING);
			}

			$drafts = array_keys($this->request->variable('d', [0 => 0]));

			if (!empty($drafts))
			{
				$sql = 'DELETE FROM ' . $this->tables['drafts'] . '
					WHERE ' . $this->db->sql_in_set('draft_id', $drafts) . '
						AND user_id = ' . (int) $this->user->data['user_id'];
				$this->db->sql_query($sql);
			}

			$this->helper->assign_meta_refresh_var(3, $this->helper->route($route));

			return $this->helper->message($this->language->lang('DRAFTS_DELETED') . $return);
		}

		if ($submit && $edit)
		{
			$draft_subject = $this->request->variable('subject', '', true);
			$draft_message = $this->request->variable('message', '', true);

			if (check_form_key($form_key))
			{
				if ($draft_message && $draft_subject)
				{
					// $this->auth->acl_gets can't be used here because it will check for global forum permissions in this case
					// In general we don't need too harsh checking here for permissions, as this will be handled later when submitting
					$bbcode_status	= $this->auth->acl_get('u_pm_bbcode') || $this->auth->acl_getf_global('f_bbcode');
					$smilies_status	= $this->auth->acl_get('u_pm_smilies') || $this->auth->acl_getf_global('f_smilies');
					$img_status		= $this->auth->acl_get('u_pm_img') || $this->auth->acl_getf_global('f_img');
					$flash_status	= $this->auth->acl_get('u_pm_flash') || $this->auth->acl_getf_global('f_flash');

					$message_parser->message = $draft_message;
					$message_parser->parse($bbcode_status, $this->config['allow_post_links'], $smilies_status, $img_status, $flash_status, true, $this->config['allow_post_links']);

					$draft_row = [
						'draft_subject' => $draft_subject,
						'draft_message' => $message_parser->message,
					];

					$sql = 'UPDATE ' . $this->tables['drafts'] . '
						SET ' . $this->db->sql_build_array('UPDATE', $draft_row) . '
						WHERE draft_id = ' . (int) $draft_id . '
							AND user_id = ' . $this->user->data['user_id'];
					$this->db->sql_query($sql);

					$this->helper->assign_meta_refresh_var(3, $this->helper->route($route));

					return $this->helper->message($this->language->lang('DRAFT_UPDATED') . $return);
				}
				else
				{
					$error = $draft_message === '' ? 'EMPTY_DRAFT' : 'EMPTY_DRAFT_TITLE';

					$this->template->assign_var('ERROR', $this->language->lang($error));
				}
			}
			else
			{
				$this->template->assign_var('ERROR', $this->language->lang('FORM_INVALID'));
			}
		}

		$rowset = [];
		$topic_ids = [];

		if (!$pm_drafts)
		{
			$sql = 'SELECT d.*, f.forum_name
				FROM ' . $this->tables['drafts'] . ' d,
					' . $this->tables['forums'] . ' f
				WHERE d.user_id = ' . (int) $this->user->data['user_id'] . ' ' .
					($edit ? 'AND d.draft_id = ' . (int) $draft_id : '') . '
					AND f.forum_id = d.forum_id
				ORDER BY d.save_time DESC';
		}
		else
		{
			$sql = 'SELECT * FROM ' . $this->tables['drafts'] . '
				WHERE user_id = ' . (int) $this->user->data['user_id'] . ' ' .
					($edit ? 'AND d.draft_id = ' . (int) $draft_id : '') . '
					AND forum_id = 0
					AND topic_id = 0
				ORDER BY save_time DESC';
		}

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['topic_id'])
			{
				$topic_ids[] = (int) $row['topic_id'];
			}

			$rowset[] = $row;
		}
		$this->db->sql_freeresult($result);

		if (!empty($topic_ids))
		{
			$sql = 'SELECT topic_id, forum_id, topic_title
				FROM ' . $this->tables['topics'] . '
				WHERE ' . $this->db->sql_in_set('topic_id', array_unique($topic_ids));
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$topic_rows[(int) $row['topic_id']] = $row;
			}
			$this->db->sql_freeresult($result);
		}

		$row_count = 0;

		foreach ($rowset as $draft)
		{
			$link_topic = $link_forum = $link_pm = false;
			$insert_url = $view_url = $title = '';

			if (!empty($topic_rows[$draft['topic_id']]) && $this->auth->acl_get('f_read', $topic_rows[$draft['topic_id']]['forum_id']))
			{
				$link_topic = true;
				$view_url = append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $topic_rows[$draft['topic_id']]['forum_id'] . '&amp;t=' . $draft['topic_id']);
				$title = $topic_rows[$draft['topic_id']]['topic_title'];

				$insert_url = append_sid("{$this->root_path}posting.$this->php_ext", 'f=' . $topic_rows[$draft['topic_id']]['forum_id'] . '&amp;t=' . $draft['topic_id'] . '&amp;mode=reply&amp;d=' . $draft['draft_id']);
			}
			else if ($this->auth->acl_get('f_read', $draft['forum_id']))
			{
				$link_forum = true;
				$view_url = append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $draft['forum_id']);
				$title = $draft['forum_name'];

				$insert_url = append_sid("{$this->root_path}posting.$this->php_ext", 'f=' . $draft['forum_id'] . '&amp;mode=post&amp;d=' . $draft['draft_id']);
			}
			else if ($pm_drafts)
			{
				$link_pm = true;
				$insert_url = $this->helper->route('ucp_pm_compose', ['d' => $draft['draft_id']]);
			}

			if (!$submit)
			{
				$message_parser->message = $draft['draft_message'];
				$message_parser->decode_message();
				$draft_message = $message_parser->message;
			}

			$template_row = [
				'DATE'				=> $this->user->format_date($draft['save_time']),
				'DRAFT_MESSAGE'		=> $draft_message,
				'DRAFT_SUBJECT'		=> $submit ? $draft_subject : $draft['draft_subject'],
				'TITLE'				=> $title,

				'DRAFT_ID'			=> (int) $draft['draft_id'],
				'FORUM_ID'			=> (int) $draft['forum_id'],
				'TOPIC_ID'			=> (int) $draft['topic_id'],

				'S_LINK_TOPIC'		=> $link_topic,
				'S_LINK_FORUM'		=> $link_forum,
				'S_LINK_PM'			=> $link_pm,
				'S_HIDDEN_FIELDS'	=> $s_hidden_fields,

				'U_INSERT'			=> $insert_url,
				'U_VIEW'			=> $view_url,
				'U_VIEW_EDIT'		=> $this->helper->route($route, ['edit' => $row['draft_id']]),
			];

			$row_count++;

			if ($edit)
			{
				$this->template->assign_vars($template_row);
			}
			else
			{
				$this->template->assign_block_vars('draftrow', $template_row);
			}
		}

		$title = $pm_drafts ? 'UCP_PM_DRAFTS' : 'UCP_MANAGE_DRAFTS';

		$this->template->assign_vars([
			'L_TITLE'				=> $this->language->lang($title),

			'S_DISPLAY_MARK_ALL'	=> !$edit,
			'S_DRAFT_ROWS'			=> !$edit ? $row_count : 0,
			'S_EDIT_DRAFT'			=> $edit,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
			'S_UCP_ACTION'			=> $this->helper->route($route),
		]);

		return $this->helper->render('ucp_main_drafts.html', $this->language->lang($title));
	}
}
