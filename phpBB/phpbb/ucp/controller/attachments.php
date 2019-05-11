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

namespace phpbb\mcp\controller;

class attachments
{
	/** @var \phpbb\attachment\manager */
	protected $attachment_manager;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

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

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\attachment\manager			$attachment_manager	Attachment manager object
	 * @param \phpbb\config\config				$config					Config object
	 * @param \phpbb\db\driver\driver_interface	$db						Database object
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
		$this->lang					= $lang;
		$this->pagination			= $pagination;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	function main($id, $mode)
	{
		$start		= $this->request->variable('start', 0);
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

				meta_refresh(3, $this->u_action);
				$message = (count($delete_ids) === 1 ? $this->lang->lang('ATTACHMENT_DELETED') : $this->lang->lang('ATTACHMENTS_DELETED')) . '<br /><br />' . $this->lang->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				confirm_box(false, count($delete_ids) === 1 ? 'DELETE_ATTACHMENT' : 'DELETE_ATTACHMENTS', build_hidden_fields($s_hidden_fields));
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
			WHERE poster_id = ' . (int) $this->user->data['user_id'] . '
				AND is_orphan = 0';
		$result = $this->db->sql_query($sql);
		$num_attachments = (int) $this->db->sql_fetchfield('num_attachments');
		$this->db->sql_freeresult($result);

		// Ensure start is a valid value
		$start = $this->pagination->validate_start($start, $this->config['topics_per_page'], $num_attachments);

		$sql = 'SELECT a.*, t.topic_title, p.message_subject as message_title
			FROM ' . $this->tables['attachments'] . ' a
				LEFT JOIN ' . $this->tables['topics'] . ' t 
					ON (a.topic_id = t.topic_id AND a.in_message = 0)
				LEFT JOIN ' . $this->tables['privmsgs'] . ' p 
					ON (a.post_msg_id = p.msg_id AND a.in_message = 1)
			WHERE a.poster_id = ' . (int) $this->user->data['user_id'] . "
				AND a.is_orphan = 0
			ORDER BY $order_by";
		$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);

		$row_count = 0;
		if ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_var('S_ATTACHMENT_ROWS', true);

			do
			{
				if ($row['in_message'])
				{
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

		$base_url = $this->u_action . "&amp;sk=$sort_key&amp;sd=$sort_dir";
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $num_attachments, $this->config['topics_per_page'], $start);

		$this->template->assign_vars([
			'TOTAL_ATTACHMENTS'		=> $num_attachments,
			'NUM_ATTACHMENTS'		=> $this->lang->lang('NUM_ATTACHMENTS', $num_attachments),

			'L_TITLE'				=> $this->lang->lang('UCP_ATTACHMENTS'),

			'S_DISPLAY_MARK_ALL'	=> (bool) $num_attachments,
			'S_DISPLAY_PAGINATION'	=> (bool) $num_attachments,
			'S_UCP_ACTION'			=> $this->u_action,
			'S_SORT_OPTIONS'		=> $s_sort_key,
			'S_ORDER_SELECT'		=> $s_sort_dir,

			'U_SORT_FILENAME'		=> $this->u_action . "&amp;sk=a&amp;sd=" . (($sort_key == 'a' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_SORT_FILE_COMMENT'	=> $this->u_action . "&amp;sk=b&amp;sd=" . (($sort_key == 'b' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_SORT_EXTENSION'		=> $this->u_action . "&amp;sk=c&amp;sd=" . (($sort_key == 'c' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_SORT_FILESIZE'		=> $this->u_action . "&amp;sk=d&amp;sd=" . (($sort_key == 'd' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_SORT_DOWNLOADS'		=> $this->u_action . "&amp;sk=e&amp;sd=" . (($sort_key == 'e' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_SORT_POST_TIME'		=> $this->u_action . "&amp;sk=f&amp;sd=" . (($sort_key == 'f' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_SORT_TOPIC_TITLE'	=> $this->u_action . "&amp;sk=g&amp;sd=" . (($sort_key == 'g' && $sort_dir == 'a') ? 'd' : 'a'),
		]);

		$this->tpl_name = 'ucp_attachments';
		$this->page_title = 'UCP_ATTACHMENTS';
	}
}
