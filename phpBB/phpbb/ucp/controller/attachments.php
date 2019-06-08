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

class attachments
{
	/** @var \phpbb\attachment\manager */
	protected $attachment_manager;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

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
	 * @param \phpbb\config\config				$config					Config object
	 * @param \phpbb\db\driver\driver_interface	$db						Database object
	 * @param \phpbb\controller\helper			$helper					Controller helper object
	 * @param \phpbb\language\language			$lang					Language object
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
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
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
		$this->config				= $config;
		$this->db					= $db;
		$this->helper				= $helper;
		$this->lang					= $lang;
		$this->pagination			= $pagination;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	/**
	 * Display a user's attachments.
	 *
	 * @param int		$page		The page number
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	function main($page = 1)
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
			$sql = 'SELECT attach_id
				FROM ' . $this->tables['attachments'] . '
				WHERE poster_id = ' . (int) $this->user->data['user_id'] . '
					AND is_orphan = 0
					AND ' . $this->db->sql_in_set('attach_id', $delete_ids);
			$result = $this->db->sql_query($sql);

			$delete_ids = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$delete_ids[] = (int) $row['attach_id'];
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
				$return = $this->lang->lang('RETURN_UCP', '<a href="' . $route . '">', '</a>');
				$message = count($delete_ids) === 1 ? $this->lang->lang('ATTACHMENT_DELETED') : $this->lang->lang('ATTACHMENTS_DELETED');

				$this->helper->assign_meta_refresh_var(3, $route);

				return $this->helper->message($message . '<br /><br />' . $return);
			}
			else
			{
				confirm_box(false, count($delete_ids) === 1 ? 'DELETE_ATTACHMENT' : 'DELETE_ATTACHMENTS', build_hidden_fields($s_hidden_fields));

				return redirect($this->helper->route('ucp_manage_attachments'));
			}
		}

		// Select box eventually
		$sort_key_text	= ['a' => $this->lang->lang('SORT_FILENAME'), 'b' => $this->lang->lang('SORT_COMMENT'), 'c' => $this->lang->lang('SORT_EXTENSION'), 'd' => $this->lang->lang('SORT_SIZE'), 'e' => $this->lang->lang('SORT_DOWNLOADS'), 'f' => $this->lang->lang('SORT_POST_TIME'), 'g' => $this->lang->lang('SORT_TOPIC_TITLE')];
		$sort_key_sql	= ['a' => 'a.real_filename', 'b' => 'a.attach_comment', 'c' => 'a.extension', 'd' => 'a.filesize', 'e' => 'a.download_count', 'f' => 'a.filetime', 'g' => 't.topic_title'];

		$sort_dir_text	= ['a' => $this->lang->lang('ASCENDING'), 'd' => $this->lang->lang('DESCENDING')];

		$s_sort_key		= '';
		$s_sort_dir		= '';

		foreach ($sort_key_text as $key => $value)
		{
			$selected = ($sort_key == $key) ? ' selected="selected"' : '';
			$s_sort_key .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

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

		$row_count = 0;

		// Ensure start is a valid value
		$start = $this->pagination->validate_start($start, $limit, $num_attachments);

		$sql = 'SELECT a.*, t.topic_title, p.message_subject as message_title
			FROM ' . $this->tables['attachments'] . ' a
				LEFT JOIN ' . $this->tables['topics'] . ' t 
					ON (a.topic_id = t.topic_id AND a.in_message = 0)
				LEFT JOIN ' . $this->tables['privmsgs'] . ' p 
					ON (a.post_msg_id = p.msg_id AND a.in_message = 1)
			WHERE a.poster_id = ' . (int) $this->user->data['user_id'] . "
				AND a.is_orphan = 0
			ORDER BY $order_by";
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		if ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_var('S_ATTACHMENT_ROWS', true);

			do
			{
				if ($row['in_message'])
				{
					// @todo PM
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
			'NUM_ATTACHMENTS'		=> $this->lang->lang('NUM_ATTACHMENTS', $num_attachments),

			'L_TITLE'				=> $this->lang->lang('UCP_ATTACHMENTS'),

			'S_DISPLAY_MARK_ALL'	=> (bool) $num_attachments,
			'S_DISPLAY_PAGINATION'	=> (bool) $num_attachments,
			'S_UCP_ACTION'			=> $this->helper->route('ucp_manage_attachments'),
			'S_SORT_OPTIONS'		=> $s_sort_key,
			'S_ORDER_SELECT'		=> $s_sort_dir,

			'U_SORT_FILENAME'		=> $this->helper->route('ucp_manage_attachments', $this->get_params('a', $sort_key, $sort_dir)),
			'U_SORT_FILE_COMMENT'	=> $this->helper->route('ucp_manage_attachments', $this->get_params('b', $sort_key, $sort_dir)),
			'U_SORT_EXTENSION'		=> $this->helper->route('ucp_manage_attachments', $this->get_params('c', $sort_key, $sort_dir)),
			'U_SORT_FILESIZE'		=> $this->helper->route('ucp_manage_attachments', $this->get_params('d', $sort_key, $sort_dir)),
			'U_SORT_DOWNLOADS'		=> $this->helper->route('ucp_manage_attachments', $this->get_params('e', $sort_key, $sort_dir)),
			'U_SORT_POST_TIME'		=> $this->helper->route('ucp_manage_attachments', $this->get_params('f', $sort_key, $sort_dir)),
			'U_SORT_TOPIC_TITLE'	=> $this->helper->route('ucp_manage_attachments', $this->get_params('g', $sort_key, $sort_dir)),
		]);

		return $this->helper->render('ucp_attachments.html', $this->lang->lang('UCP_ATTACHMENTS'));
	}

	protected function get_params($key, $sort_key, $sort_dir)
	{
		return [
			'sk' => $key,
			'sd' => ($sort_key === $key && $sort_dir === 'a') ? 'd' : 'a',
		];
	}
}
