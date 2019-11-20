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

/**
 * User attachments
 */
class attachments
{
	/** @var \phpbb\attachment\manager */
	protected $attachment_manager;

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

	/** @var \phpbb\pagination */
	protected $pagination;

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
	 * @param \phpbb\attachment\manager			$attachment_manager		Attachment manager object
	 * @param \phpbb\auth\auth					$auth					Auth object
	 * @param \phpbb\config\config				$config					Config object
	 * @param \phpbb\db\driver\driver_interface	$db						Database object
	 * @param \phpbb\controller\helper			$helper					Controller helper object
	 * @param \phpbb\language\language			$language				Language object
	 * @param \phpbb\pagination					$pagination				Pagination object
	 * @param \phpbb\request\request			$request				Request object
	 * @param \phpbb\template\template			$template				Template object
	 * @param \phpbb\user						$user					User object
	 * @param string							$root_path				phpBB root path
	 * @param string							$php_ext				php File extension
	 * @param array								$tables					phpBB tables
	 */
	public function __construct(
		\phpbb\attachment\manager $attachment_manager,
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->attachment_manager	= $attachment_manager;
		$this->auth					= $auth;
		$this->config				= $config;
		$this->db					= $db;
		$this->helper				= $helper;
		$this->language				= $language;
		$this->pagination			= $pagination;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	public function main($page = 1)
	{
		$limit		= (int) $this->config['topics_per_page'];
		$start		= ($page - 1) * $limit;

		$sort_key	= $this->request->variable('sk', 'a');
		$sort_dir	= $this->request->variable('sd', 'a');

		$delete		= $this->request->is_set_post('delete');
		$delete_ids	= array_keys($this->request->variable('attachment', [0]));

		if ($delete && !empty($delete_ids))
		{
			// Validate $delete_ids...
			$sql = 'SELECT a.attach_id, p.post_edit_locked, t.topic_status, f.forum_id, f.forum_status
				FROM ' . $this->tables['attachments'] . ' a
				LEFT JOIN ' . $this->tables['posts'] . ' p
					ON (a.post_msg_id = p.post_id AND a.in_message = 0)
				LEFT JOIN ' . $this->tables['topics'] . ' t
					ON (t.topic_id = p.topic_id AND a.in_message = 0)
				LEFT JOIN ' . $this->tables['forums'] . ' f
					ON (f.forum_id = t.forum_id AND a.in_message = 0)
				WHERE a.poster_id = ' . (int) $this->user->data['user_id'] . '
					AND a.is_orphan = 0
					AND ' . $this->db->sql_in_set('a.attach_id', $delete_ids);
			$result = $this->db->sql_query($sql);

			$delete_ids = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				if (!$this->auth->acl_get('m_edit', $row['forum_id'])
					&& ($row['forum_status'] == ITEM_LOCKED || $row['topic_status'] == ITEM_LOCKED || $row['post_edit_locked'])
				)
				{
					continue;
				}

				$delete_ids[] = $row['attach_id'];
			}
			$this->db->sql_freeresult($result);
		}

		if ($delete && !empty($delete_ids))
		{
			$s_hidden_fields = ['delete' => 1];

			foreach ($delete_ids as $attachment_id)
			{
				$s_hidden_fields['attachment'][$attachment_id] = 1;
			}

			if (confirm_box(true))
			{
				$this->attachment_manager->delete('attach', $delete_ids);

				$route = $this->helper->route('ucp_manage_attachments');
				$return = $this->language->lang('RETURN_UCP', '<a href="' . $route . '">', '</a>');
				$message = count($delete_ids) === 1 ? $this->language->lang('ATTACHMENT_DELETED') : $this->language->lang('ATTACHMENTS_DELETED');

				$this->helper->assign_meta_refresh_var(3, $route);

				return $this->helper->message($message . '<br /><br />' . $return);
			}
			else
			{
				confirm_box(false, count($delete_ids) == 1 ? 'DELETE_ATTACHMENT' : 'DELETE_ATTACHMENTS', build_hidden_fields($s_hidden_fields));

				return redirect($this->helper->route('ucp_manage_attachments'));
			}
		}

		// Select box eventually
		$sort_dir_text = ['a' => $this->language->lang('ASCENDING'), 'd' => $this->language->lang('DESCENDING')];
		$sort_key_text = ['a' => $this->language->lang('SORT_FILENAME'), 'b' => $this->language->lang('SORT_COMMENT'), 'c' => $this->language->lang('SORT_EXTENSION'), 'd' => $this->language->lang('SORT_SIZE'), 'e' => $this->language->lang('SORT_DOWNLOADS'), 'f' => $this->language->lang('SORT_POST_TIME'), 'g' => $this->language->lang('SORT_TOPIC_TITLE')];
		$sort_key_sql = ['a' => 'a.real_filename', 'b' => 'a.attach_comment', 'c' => 'a.extension', 'd' => 'a.filesize', 'e' => 'a.download_count', 'f' => 'a.filetime', 'g' => 't.topic_title'];

		$s_sort_key = '';
		foreach ($sort_key_text as $key => $value)
		{
			$selected = ($sort_key == $key) ? ' selected="selected"' : '';
			$s_sort_key .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		$s_sort_dir = '';
		foreach ($sort_dir_text as $key => $value)
		{
			$selected = ($sort_dir == $key) ? ' selected="selected"' : '';
			$s_sort_dir .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		if (!isset($sort_key_sql[$sort_key]))
		{
			$sort_key = 'a';
		}

		$order_by = $sort_key_sql[$sort_key] . ' ' . ($sort_dir === 'a' ? 'ASC' : 'DESC');

		$sql = 'SELECT COUNT(attach_id) as num_attachments
			FROM ' . $this->tables['attachments'] . '
			WHERE is_orphan = 0 
				AND poster_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$num_attachments = (int) $this->db->sql_fetchfield('num_attachments');
		$this->db->sql_freeresult($result);

		// Ensure start is a valid value
		$start = $this->pagination->validate_start($start, $limit, $num_attachments);

		$row_count = 0;

		$sql = 'SELECT a.*, t.topic_title, pr.message_subject as message_title, p.post_edit_locked, t.topic_status, f.forum_id, f.forum_status
			FROM ' . $this->tables['attachments'] . ' a
			LEFT JOIN ' . $this->tables['posts'] . ' p
				ON (a.post_msg_id = p.post_id AND a.in_message = 0)
			LEFT JOIN ' . $this->tables['topics'] . ' t
				ON (a.topic_id = t.topic_id AND a.in_message = 0)
			LEFT JOIN ' . $this->tables['forums'] . ' f
				ON (f.forum_id = t.forum_id AND a.in_message = 0)
			LEFT JOIN ' . $this->tables['privmsgs'] . ' pr
				ON (a.post_msg_id = pr.msg_id AND a.in_message = 1)
			WHERE a.poster_id = ' . (int) $this->user->data['user_id'] . '
				AND a.is_orphan = 0
			ORDER BY ' . $order_by;
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		if ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_var('S_ATTACHMENT_ROWS', true);

			do
			{
				if ($row['in_message'])
				{
					/* @todo pm route */
					$view_topic = append_sid("{$this->root_path}ucp.$this->php_ext", "i=pm&amp;p={$row['post_msg_id']}");
				}
				else
				{
					$view_topic = append_sid("{$this->root_path}viewtopic.$this->php_ext", "t={$row['topic_id']}&amp;p={$row['post_msg_id']}") . "#p{$row['post_msg_id']}";
				}

				$this->template->assign_block_vars('attachrow', [
					'COMMENT'			=> bbcode_nl2br($row['attach_comment']),
					'DOWNLOAD_COUNT'	=> $row['download_count'],
					'EXTENSION'			=> $row['extension'],
					'FILENAME'			=> $row['real_filename'],
					'POST_TIME'			=> $this->user->format_date($row['filetime']),
					'ROW_NUMBER'		=> $row_count + $start + 1,
					'SIZE'				=> get_formatted_filesize($row['filesize']),
					'TOPIC_TITLE'		=> $row['in_message'] ? $row['message_title'] : $row['topic_title'],

					'ATTACH_ID'			=> $row['attach_id'],
					'POST_ID'			=> $row['post_msg_id'],
					'TOPIC_ID'			=> $row['topic_id'],

					'S_IN_MESSAGE'		=> $row['in_message'],
					'S_LOCKED'			=> !$row['in_message'] && !$this->auth->acl_get('m_edit', $row['forum_id']) && ($row['forum_status'] == ITEM_LOCKED || $row['topic_status'] == ITEM_LOCKED || $row['post_edit_locked']),

					'U_VIEW_ATTACHMENT'	=> append_sid("{$this->root_path}download/file.$this->php_ext", 'id=' . $row['attach_id']),
					'U_VIEW_TOPIC'		=> $view_topic,
				]);

				$row_count++;
			}
			while ($row = $this->db->sql_fetchrow($result));
		}
		$this->db->sql_freeresult($result);

		$this->pagination->generate_template_pagination([
			'routes' => ['ucp_manage_attachments', 'ucp_manage_attachments_pagination'],
			'params' => ['sk' => $sort_key, 'sd' => $sort_dir],
		], 'pagination', 'page', $num_attachments, $limit, $start);

		$this->template->assign_vars([
			'TOTAL_ATTACHMENTS'		=> $num_attachments,
			'NUM_ATTACHMENTS'		=> $this->language->lang('NUM_ATTACHMENTS', $num_attachments),

			'L_TITLE'				=> $this->language->lang('UCP_ATTACHMENTS'),

			'S_DISPLAY_MARK_ALL'	=> (bool) $num_attachments,
			'S_DISPLAY_PAGINATION'	=> (bool) $num_attachments,
			'S_ORDER_SELECT'		=> $s_sort_dir,
			'S_SORT_OPTIONS' 		=> $s_sort_key,
			'S_UCP_ACTION'			=> $this->helper->route('ucp_manage_attachments'),

			'U_SORT_FILENAME'		=> $this->helper->route('ucp_manage_attachments', $this->get_params('a', $sort_key, $sort_dir)),
			'U_SORT_FILE_COMMENT'	=> $this->helper->route('ucp_manage_attachments', $this->get_params('b', $sort_key, $sort_dir)),
			'U_SORT_EXTENSION'		=> $this->helper->route('ucp_manage_attachments', $this->get_params('c', $sort_key, $sort_dir)),
			'U_SORT_FILESIZE'		=> $this->helper->route('ucp_manage_attachments', $this->get_params('d', $sort_key, $sort_dir)),
			'U_SORT_DOWNLOADS'		=> $this->helper->route('ucp_manage_attachments', $this->get_params('e', $sort_key, $sort_dir)),
			'U_SORT_POST_TIME'		=> $this->helper->route('ucp_manage_attachments', $this->get_params('f', $sort_key, $sort_dir)),
			'U_SORT_TOPIC_TITLE'	=> $this->helper->route('ucp_manage_attachments', $this->get_params('g', $sort_key, $sort_dir)),
		]);

		return $this->helper->render('ucp_attachments.html', $this->language->lang('UCP_ATTACHMENTS'));
	}

	protected function get_params($key, $sort_key, $sort_dir)
	{
		return [
			'sk' => $key,
			'sd' => ($sort_key === $key && $sort_dir === 'a') ? 'd' : 'a',
		];
	}
}
