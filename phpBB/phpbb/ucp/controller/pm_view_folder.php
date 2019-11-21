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

use phpbb\exception\http_exception;

class pm_view_folder
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

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

	/** @var \phpbb\ucp\controller\pm_view_message */
	protected $ucp_pm_message;

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
	 * @param \phpbb\auth\auth					$auth				Auth object
	 * @param \phpbb\cache\service				$cache				Cache object
	 * @param \phpbb\config\config				$config				Config object
	 * @param \phpbb\db\driver\driver_interface	$db					Database object
	 * @param \phpbb\event\dispatcher			$dispatcher			Event dispatcher object
	 * @param \phpbb\controller\helper			$helper				Controller helper object
	 * @param \phpbb\language\language			$language			Language object
	 * @param \phpbb\pagination					$pagination			Pagination object
	 * @param \phpbb\request\request			$request			Request object
	 * @param \phpbb\template\template			$template			Template object
	 * @param pm_view_message					$ucp_pm_message		UCP PM Message object
	 * @param \phpbb\user						$user				User object
	 * @param string							$root_path			phpBB root path
	 * @param string							$php_ext			php File extensions
	 * @param array								$tables				phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\cache\service $cache,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		pm_view_message $ucp_pm_message,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth				= $auth;
		$this->cache			= $cache;
		$this->config			= $config;
		$this->db				= $db;
		$this->dispatcher		= $dispatcher;
		$this->helper			= $helper;
		$this->language			= $language;
		$this->pagination		= $pagination;
		$this->request			= $request;
		$this->template			= $template;
		$this->ucp_pm_message	= $ucp_pm_message;
		$this->user				= $user;

		$this->root_path		= $root_path;
		$this->php_ext			= $php_ext;
		$this->tables			= $tables;
	}

	public function main($folder)
	{
		if ($this->request->is_set('p', \phpbb\request\request_interface::GET))
		{
			$redirect_url = $this->helper->route('ucp_pm_folder', ['folder' => 'inbox', 'p' => $this->request->variable('p', 0)]);
			login_box($redirect_url, $this->language->lang('LOGIN_EXPLAIN_UCP'));
		}

		if (!$this->user->data['is_registered'])
		{
			throw new http_exception(400, 'NO_MESSAGE');
		}

		// Is PM disabled?
		if (!$this->config['allow_privmsg'])
		{
			throw new http_exception(400, 'PM_DISABLED');
		}

		$form_key = 'ucp_pm_view';
		add_form_key($form_key);

		if (!function_exists('get_folder'))
		{
			include($this->root_path . 'includes/functions_privmsgs.' . $this->php_ext);
		}

		$this->language->add_lang('posting');
		$this->template->assign_var('S_PRIVMSGS', true);

		if (!in_array($folder, ['inbox', 'outbox', 'sentbox']))
		{
			$folder = (int) $folder;
		}
		else
		{
			$folder = $folder === 'inbox' ? PRIVMSGS_INBOX : ($folder === 'outbox' ? PRIVMSGS_OUTBOX : PRIVMSGS_SENTBOX);
		}

		set_user_message_limit();

		if ($folder)
		{
			$folder_id = $folder;
			$action = 'view_folder';
		}
		else
		{
			$folder_id = $this->request->variable('f', PRIVMSGS_NO_BOX);
			$action = $this->request->variable('action', 'view_folder');
		}

		$msg_id = $this->request->variable('p', 0);
		$view	= $this->request->variable('view', '');

		// View message if specified
		if ($msg_id)
		{
			$action = 'view_message';
		}

		if (!$this->auth->acl_get('u_readpm'))
		{
			send_status_line(403, 'Forbidden');
			trigger_error('NO_AUTH_READ_MESSAGE', E_USER_WARNING);
		}

		if ($view == 'print' && (!$this->config['print_pm'] || !$this->auth->acl_get('u_pm_printpm')))
		{
			send_status_line(403, 'Forbidden');
			trigger_error('NO_AUTH_PRINT_MESSAGE', E_USER_WARNING);
		}

		// Do not allow hold messages to be seen
		if ($folder_id == PRIVMSGS_HOLD_BOX)
		{
			trigger_error('NO_AUTH_READ_HOLD_MESSAGE', E_USER_WARNING);
		}

		// First Handle Mark actions and moving messages
		$submit_mark	= ($this->request->is_set_post('submit_mark')) ? true : false;
		$move_pm		= ($this->request->is_set_post('move_pm')) ? true : false;
		$mark_option	= $this->request->variable('mark_option', '');
		$dest_folder	= $this->request->variable('dest_folder', PRIVMSGS_NO_BOX);

		// Is moving PM triggered through mark options?
		if (!in_array($mark_option, ['mark_important', 'delete_marked']) && $submit_mark)
		{
			$move_pm = true;
			$dest_folder = (int) $mark_option;
			$submit_mark = false;
		}

		// Move PM
		if ($move_pm)
		{
			if (!check_form_key($form_key))
			{
				trigger_error('FORM_INVALID');
			}

			$move_msg_ids	= ($this->request->is_set_post('marked_msg_id')) ? $this->request->variable('marked_msg_id', [0]) : [];
			$cur_folder_id	= $this->request->variable('cur_folder_id', PRIVMSGS_NO_BOX);

			if (move_pm($this->user->data['user_id'], $this->user->data['message_limit'], $move_msg_ids, $dest_folder, $cur_folder_id))
			{
				// Return to folder view if single message moved
				if ($action == 'view_message')
				{
					$msg_id		= 0;
					$folder_id	= $this->request->variable('cur_folder_id', PRIVMSGS_NO_BOX);
					$action		= 'view_folder';
				}
			}
		}

		// Message Mark Options
		if ($submit_mark)
		{
			handle_mark_actions($this->user->data['user_id'], $mark_option);
		}

		// If new messages arrived, place them into the appropriate folder
		$num_not_moved = $num_removed = 0;
		$release = $this->request->variable('release', 0);

		if ($this->user->data['user_new_privmsg'] && ($action == 'view_folder' || $action == 'view_message'))
		{
			$return = place_pm_into_folder($global_privmsgs_rules, $release);
			$num_not_moved = $return['not_moved'];
			$num_removed = $return['removed'];
		}

		if (!$msg_id && $folder_id == PRIVMSGS_NO_BOX)
		{
			$folder_id = PRIVMSGS_INBOX;
		}
		else if ($msg_id && $folder_id == PRIVMSGS_NO_BOX)
		{
			$sql = 'SELECT folder_id
				FROM ' . $this->tables['privmsgs_to'] . "
				WHERE msg_id = $msg_id
					AND folder_id <> " . PRIVMSGS_NO_BOX . '
					AND user_id = ' . $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$row)
			{
				trigger_error('NO_MESSAGE', E_USER_WARNING);
			}
			$folder_id = (int) $row['folder_id'];
		}

		if ($this->request->variable('mark', '') == 'all' && check_link_hash($this->request->variable('token', ''), 'mark_all_pms_read'))
		{
			mark_folder_read($this->user->data['user_id'], $folder_id);

			meta_refresh(3, $this->helper->route('ucp_pm_view', ['folder' => $folder_id]));
			$message = $this->language->lang('PM_MARK_ALL_READ_SUCCESS');

			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response();
				$json_response->send([
					'MESSAGE_TITLE'	=> $this->language->lang('INFORMATION'),
					'MESSAGE_TEXT'	=> $message,
					'success'		=> true,
				]);
			}
			$message .= '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $this->helper->route('ucp_pm_view', ['folder' => $folder_id]) . '">', '</a>');

			trigger_error($message);
		}

		$message_row = [];
		if ($action == 'view_message' && $msg_id)
		{
			// Get Message user want to see
			if ($view == 'next' || $view == 'previous')
			{
				$sql_condition = ($view == 'next') ? '>' : '<';
				$sql_ordering = ($view == 'next') ? 'ASC' : 'DESC';

				$sql = 'SELECT t.msg_id
					FROM ' . $this->tables['privmsgs_to'] . ' t, ' . $this->tables['privmsgs'] . ' p, ' . $this->tables['privmsgs'] . " p2
					WHERE p2.msg_id = $msg_id
						AND t.folder_id = $folder_id
						AND t.user_id = " . $this->user->data['user_id'] . "
						AND t.msg_id = p.msg_id
						AND p.message_time $sql_condition p2.message_time
					ORDER BY p.message_time $sql_ordering";
				$result = $this->db->sql_query_limit($sql, 1);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$row)
				{
					$message = ($view == 'next') ? 'NO_NEWER_PM' : 'NO_OLDER_PM';
					trigger_error($message, E_USER_WARNING);
				}
				else
				{
					$msg_id = $row['msg_id'];
				}
			}

			$sql = 'SELECT t.*, p.*, u.*
				FROM ' . $this->tables['privmsgs_to'] . ' t, ' . $this->tables['privmsgs'] . ' p, ' . $this->tables['users'] . ' u
				WHERE t.user_id = ' . $this->user->data['user_id'] . "
					AND p.author_id = u.user_id
					AND t.folder_id = $folder_id
					AND t.msg_id = p.msg_id
					AND p.msg_id = $msg_id";
			$result = $this->db->sql_query($sql);
			$message_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$message_row)
			{
				trigger_error('NO_MESSAGE', E_USER_WARNING);
			}

			// Update unread status
			update_unread_status($message_row['pm_unread'], $message_row['msg_id'], $this->user->data['user_id'], $folder_id);
		}

		$folder = get_folder($this->user->data['user_id'], $folder_id);

		$s_folder_options = $s_to_folder_options = '';
		foreach ($folder as $f_id => $folder_ary)
		{
			$option = '<option' . ((!in_array($f_id, [PRIVMSGS_INBOX, PRIVMSGS_OUTBOX, PRIVMSGS_SENTBOX])) ? ' class="sep"' : '') . ' value="' . $f_id . '"' . (($f_id == $folder_id) ? ' selected="selected"' : '') . '>' . $folder_ary['folder_name'] . (($folder_ary['unread_messages']) ? ' [' . $folder_ary['unread_messages'] . '] ' : '') . '</option>';

			$s_to_folder_options .= ($f_id != PRIVMSGS_OUTBOX && $f_id != PRIVMSGS_SENTBOX) ? $option : '';
			$s_folder_options .= $option;
		}
		clean_sentbox($folder[PRIVMSGS_SENTBOX]['num_messages']);

		// Header for message view - folder and so on
		$folder_status = get_folder_status($folder_id, $folder);

		$this->template->assign_vars([
			'CUR_FOLDER_ID'			=> $folder_id,
			'CUR_FOLDER_NAME'		=> $folder_status['folder_name'],
			'NUM_NOT_MOVED'			=> $num_not_moved,
			'NUM_REMOVED'			=> $num_removed,
			'RELEASE_MESSAGE_INFO'	=> $this->language->lang('RELEASE_MESSAGES', '<a href="' . $this->helper->route('ucp_pm_view', ['folder' => $folder_id, 'release' => '1']) . '">', '</a>'),
			'NOT_MOVED_MESSAGES'	=> $this->language->lang('NOT_MOVED_MESSAGES', (int) $num_not_moved),
			'RULE_REMOVED_MESSAGES'	=> $this->language->lang('RULE_REMOVED_MESSAGES', (int) $num_removed),

			'S_FOLDER_OPTIONS'		=> $s_folder_options,
			'S_TO_FOLDER_OPTIONS'	=> $s_to_folder_options,
			'S_FOLDER_ACTION'		=> $this->helper->route('ucp_pm_view', ['folder' => $folder_id, 'action' => 'view_folder']),
			'S_PM_ACTION'			=> $this->helper->route('ucp_pm_view', ['folder' => $folder_id, 'action' => $action]),

			'U_INBOX'				=> $this->helper->route('ucp_pm_view', ['folder' => 'inbox']),
			'U_OUTBOX'				=> $this->helper->route('ucp_pm_view', ['folder' => 'outbox']),
			'U_SENTBOX'				=> $this->helper->route('ucp_pm_view', ['folder' => 'sentbox']),
			'U_CREATE_FOLDER'		=> $this->helper->route('ucp_pm_settings'),
			'U_CURRENT_FOLDER'		=> $this->helper->route('ucp_pm_view', ['folder' => $folder_id, 'action' => 'view_folder']),
			'U_MARK_ALL'			=> $this->helper->route('ucp_pm_view', ['folder' => $folder_id, 'mark' => 'all', 'token' => generate_link_hash('mark_all_pms_read')]),

			'S_IN_INBOX'			=> ($folder_id == PRIVMSGS_INBOX) ? true : false,
			'S_IN_OUTBOX'			=> ($folder_id == PRIVMSGS_OUTBOX) ? true : false,
			'S_IN_SENTBOX'			=> ($folder_id == PRIVMSGS_SENTBOX) ? true : false,

			'FOLDER_STATUS'				=> $folder_status['message'],
			'FOLDER_MAX_MESSAGES'		=> $folder_status['max'],
			'FOLDER_CUR_MESSAGES'		=> $folder_status['cur'],
			'FOLDER_REMAINING_MESSAGES'	=> $folder_status['remaining'],
			'FOLDER_PERCENT'			=> $folder_status['percent'],
		]);

		if ($action == 'view_message')
		{
			$this->template->assign_vars([
				'S_VIEW_MESSAGE'		=> true,
				'L_RETURN_TO_FOLDER'	=> $this->language->lang('RETURN_TO', $folder_status['folder_name']),
				'MSG_ID'				=> $msg_id,
			]);

			if (!$msg_id)
			{
				trigger_error('NO_MESSAGE', E_USER_WARNING);
			}

			$tpl_file = ($view == 'print') ? 'ucp_pm_viewmessage_print' : 'ucp_pm_viewmessage';

			$this->ucp_pm_message->view_message($folder_id, $msg_id, $folder, $message_row);
		}
		else
		{
			$tpl_file = 'ucp_pm_viewfolder';

			$this->view_folder($folder_id, $folder);
		}

		return $this->helper->render($tpl_file, $this->language->lang('UCP_PM_VIEW'));
	}

	/**
	 * View message folder
	 * Called from ucp_pm with mode == 'view' && action == 'view_folder'
	 */
	protected function view_folder($folder_id, $folder)
	{
		$form_key = 'ucp_pm_view';
		add_form_key($form_key);

		$submit_export = $this->request->is_set_post('submit_export');

		$folder_info = $this->get_pm_from($folder_id, $folder, $this->user->data['user_id']);

		if (!$submit_export)
		{
			$this->language->add_lang('viewforum');

			// Grab icons
			$icons = $this->cache->obtain_icons();

			$color_rows = ['message_reported', 'marked', 'replied', 'friend', 'foe'];

			foreach ($color_rows as $var)
			{
				$this->template->assign_block_vars('pm_colour_info', [
					'IMG'	=> $this->user->img("pm_{$var}", ''),
					'CLASS'	=> "pm_{$var}_colour",
					'LANG'	=> $this->language->lang(strtoupper($var) . '_MESSAGE'),
				]);
			}

			$mark_options = ['mark_important', 'delete_marked'];

			// Minimise edits
			if (!$this->auth->acl_get('u_pm_delete') && $key = array_search('delete_marked', $mark_options))
			{
				unset($mark_options[$key]);
			}

			$s_mark_options = '';
			foreach ($mark_options as $mark_option)
			{
				$s_mark_options .= '<option value="' . $mark_option . '">' . $this->language->lang(strtoupper($mark_option)) . '</option>';
			}

			// We do the folder moving options here too, for template authors to use...
			$s_folder_move_options = '';
			if ($folder_id != PRIVMSGS_NO_BOX && $folder_id != PRIVMSGS_OUTBOX)
			{
				foreach ($folder as $f_id => $folder_ary)
				{
					if ($f_id == PRIVMSGS_OUTBOX || $f_id == PRIVMSGS_SENTBOX || $f_id == $folder_id)
					{
						continue;
					}

					$s_folder_move_options .= '<option' . (($f_id != PRIVMSGS_INBOX) ? ' class="sep"' : '') . ' value="' . $f_id . '">';
					$s_folder_move_options .= $this->language->lang('MOVE_MARKED_TO_FOLDER', $folder_ary['folder_name']);
					$s_folder_move_options .= (($folder_ary['unread_messages']) ? ' [' . $folder_ary['unread_messages'] . '] ' : '') . '</option>';
				}
			}
			$friend = $foe = [];

			// Get friends and foes
			$sql = 'SELECT *
				FROM ' . $this->tables['zebra'] . '
				WHERE user_id = ' . $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$friend[$row['zebra_id']] = $row['friend'];
				$foe[$row['zebra_id']] = $row['foe'];
			}
			$this->db->sql_freeresult($result);

			$this->template->assign_vars([
				'S_MARK_OPTIONS'		=> $s_mark_options,
				'S_MOVE_MARKED_OPTIONS'	=> $s_folder_move_options,
			]);

			// Okay, lets dump out the page ...
			if (count($folder_info['pm_list']))
			{
				$address_list = [];

				// Build Recipient List if in outbox/sentbox - max two additional queries
				if ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX)
				{
					$address_list = get_recipient_strings($folder_info['rowset']);
				}

				foreach ($folder_info['pm_list'] as $message_id)
				{
					$row = &$folder_info['rowset'][$message_id];

					$folder_img = ($row['pm_unread']) ? 'pm_unread' : 'pm_read';
					$folder_alt = ($row['pm_unread']) ? 'NEW_MESSAGES' : 'NO_NEW_MESSAGES';

					// Generate all URIs ...
					$view_message_url = $this->helper->route('ucp_pm_view', ['folder' => $folder_id, 'p' => $message_id]);
					$remove_message_url = $this->helper->route('ucp_pm_compose', ['action' => 'delete', 'p' => $message_id]);

					$row_indicator = '';
					foreach ($color_rows as $var)
					{
						if (($var !== 'friend' && $var !== 'foe' && $row[($var === 'message_reported') ? $var : "pm_{$var}"])
							||
							(($var === 'friend' || $var === 'foe') && isset(${$var}[$row['author_id']]) && ${$var}[$row['author_id']]))
						{
							$row_indicator = $var;
							break;
						}
					}

					// Send vars to template
					$this->template->assign_block_vars('messagerow', [
						'PM_CLASS'			=> ($row_indicator) ? 'pm_' . $row_indicator . '_colour' : '',

						'MESSAGE_AUTHOR_FULL'		=> get_username_string('full', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
						'MESSAGE_AUTHOR_COLOUR'		=> get_username_string('colour', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
						'MESSAGE_AUTHOR'			=> get_username_string('username', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
						'U_MESSAGE_AUTHOR'			=> get_username_string('profile', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),

						'FOLDER_ID'			=> $folder_id,
						'MESSAGE_ID'		=> $message_id,
						'SENT_TIME'			=> $this->user->format_date($row['message_time']),
						'SUBJECT'			=> censor_text($row['message_subject']),
						'FOLDER'			=> (isset($folder[$row['folder_id']])) ? $folder[$row['folder_id']]['folder_name'] : '',
						'U_FOLDER'			=> (isset($folder[$row['folder_id']])) ? $this->helper->route('ucp_pm_view', ['folder' => $row['folder_id']]) : '',
						'PM_ICON_IMG'		=> (!empty($icons[$row['icon_id']])) ? '<img src="' . $this->config['icons_path'] . '/' . $icons[$row['icon_id']]['img'] . '" width="' . $icons[$row['icon_id']]['width'] . '" height="' . $icons[$row['icon_id']]['height'] . '" alt="" title="" />' : '',
						'PM_ICON_URL'		=> (!empty($icons[$row['icon_id']])) ? $this->config['icons_path'] . '/' . $icons[$row['icon_id']]['img'] : '',
						'FOLDER_IMG'		=> $this->user->img($folder_img, $folder_alt),
						'FOLDER_IMG_STYLE'	=> $folder_img,
						'PM_IMG'			=> ($row_indicator) ? $this->user->img('pm_' . $row_indicator, '') : '',
						'ATTACH_ICON_IMG'	=> ($this->auth->acl_get('u_pm_download') && $row['message_attachment'] && $this->config['allow_pm_attach']) ? $this->user->img('icon_topic_attach', $this->language->lang('TOTAL_ATTACHMENTS')) : '',

						'S_PM_UNREAD'		=> ($row['pm_unread']) ? true : false,
						'S_PM_DELETED'		=> ($row['pm_deleted']) ? true : false,
						'S_PM_REPORTED'		=> (isset($row['report_id'])) ? true : false,
						'S_AUTHOR_DELETED'	=> ($row['author_id'] == ANONYMOUS) ? true : false,

						'U_VIEW_PM'			=> ($row['pm_deleted']) ? '' : $view_message_url,
						'U_REMOVE_PM'		=> ($row['pm_deleted']) ? $remove_message_url : '',
						'U_MCP_REPORT'		=> (isset($row['report_id'])) ? $this->helper->route('mcp_pm_report_details', ['r' => $row['report_id']]) :'',
						'RECIPIENTS'		=> ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX) ? implode($this->language->lang('COMMA_SEPARATOR'), $address_list[$message_id]) : '',
					]);
				}
				unset($folder_info['rowset']);

				$this->template->assign_vars([
					'S_SHOW_RECIPIENTS'		=> ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX) ? true : false,
					'S_SHOW_COLOUR_LEGEND'	=> true,

					'REPORTED_IMG'			=> $this->user->img('icon_topic_reported', 'PM_REPORTED'),
					'S_PM_ICONS'			=> ($this->config['enable_pm_icons']) ? true : false,
				]);
			}
		}
		else
		{
			if (!check_form_key($form_key))
			{
				trigger_error('FORM_INVALID');
			}

			$export_type = $this->request->variable('export_option', '');
			$enclosure = $this->request->variable('enclosure', '');
			$delimiter = $this->request->variable('delimiter', '');

			if ($export_type == 'CSV' && ($delimiter === '' || $enclosure === ''))
			{
				$this->template->assign_var('PROMPT', true);
			}
			else
			{
				// Build Recipient List if in outbox/sentbox

				$address_temp = $address = $data = [];

				if ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX)
				{
					foreach ($folder_info['rowset'] as $message_id => $row)
					{
						$address_temp[$message_id] = rebuild_header(['to' => $row['to_address'], 'bcc' => $row['bcc_address']]);
						$address[$message_id] = [];
					}
				}

				foreach ($folder_info['pm_list'] as $message_id)
				{
					$row = &$folder_info['rowset'][$message_id];

					include_once($this->root_path . 'includes/functions_posting.' . $this->php_ext);

					$sql = 'SELECT p.message_text, p.bbcode_uid
						FROM ' . $this->tables['privmsgs_to'] . ' t, ' . $this->tables['privmsgs'] . ' p, ' . $this->tables['users'] . ' u
						WHERE t.user_id = ' . $this->user->data['user_id'] . "
							AND p.author_id = u.user_id
							AND t.folder_id = $folder_id
							AND t.msg_id = p.msg_id
							AND p.msg_id = $message_id";
					$result = $this->db->sql_query_limit($sql, 1);
					$message_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					$_types = ['u', 'g'];
					foreach ($_types as $ug_type)
					{
						if (isset($address_temp[$message_id][$ug_type]) && count($address_temp[$message_id][$ug_type]))
						{
							if (!isset($address[$message_id][$ug_type]))
							{
								$address[$message_id][$ug_type] = [];
							}
							if ($ug_type == 'u')
							{
								$sql = 'SELECT user_id as id, username as name
									FROM ' . $this->tables['users'] . '
									WHERE ';
							}
							else
							{
								$sql = 'SELECT group_id as id, group_name as name
									FROM ' . $this->tables['groups'] . '
									WHERE ';
							}
							$sql .= $this->db->sql_in_set(($ug_type == 'u') ? 'user_id' : 'group_id', array_map('intval', array_keys($address_temp[$message_id][$ug_type])));

							$result = $this->db->sql_query($sql);

							while ($info_row = $this->db->sql_fetchrow($result))
							{
								$address[$message_id][$ug_type][$address_temp[$message_id][$ug_type][$info_row['id']]][] = $info_row['name'];
								unset($address_temp[$message_id][$ug_type][$info_row['id']]);
							}
							$this->db->sql_freeresult($result);
						}
					}

					// There is the chance that all recipients of the message got deleted. To avoid creating
					// exports without recipients, we add a bogus "undisclosed recipient".
					if (!(isset($address[$message_id]['g']) && count($address[$message_id]['g'])) &&
						!(isset($address[$message_id]['u']) && count($address[$message_id]['u'])))
					{
						$address[$message_id]['u'] = [];
						$address[$message_id]['u']['to'] = [];
						$address[$message_id]['u']['to'][] = $this->language->lang('UNDISCLOSED_RECIPIENT');
					}

					decode_message($message_row['message_text'], $message_row['bbcode_uid']);

					$data[] = [
						'subject'	=> censor_text($row['message_subject']),
						'sender'	=> $row['username'],
						// ISO 8601 date. For PHP4 we are able to hardcode the timezone because $this->user->format_date() does not set it.
						'date'		=> $this->user->format_date($row['message_time'], 'c', true),
						'to'		=> ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX) ? $address[$message_id] : '',
						'message'	=> $message_row['message_text'],
					];
				}

				switch ($export_type)
				{
					case 'CSV':
					case 'CSV_EXCEL':
						$mimetype = 'text/csv';
						$filetype = 'csv';

						if ($export_type == 'CSV_EXCEL')
						{
							$enclosure = '"';
							$delimiter = ',';
							$newline = "\r\n";
						}
						else
						{
							$newline = "\n";
						}

						$string = '';
						foreach ($data as $value)
						{
							$recipients = $value['to'];
							$value['to'] = $value['bcc'] = '';

							if (is_array($recipients))
							{
								foreach ($recipients as $values)
								{
									$value['bcc'] .= (isset($values['bcc']) && is_array($values['bcc'])) ? ',' . implode(',', $values['bcc']) : '';
									$value['to'] .= (isset($values['to']) && is_array($values['to'])) ? ',' . implode(',', $values['to']) : '';
								}

								// Remove the commas which will appear before the first entry.
								$value['to'] = substr($value['to'], 1);
								$value['bcc'] = substr($value['bcc'], 1);
							}

							foreach ($value as $tag => $text)
							{
								$cell = str_replace($enclosure, $enclosure . $enclosure, $text);

								if (strpos($cell, $enclosure) !== false || strpos($cell, $delimiter) !== false || strpos($cell, $newline) !== false)
								{
									$string .= $enclosure . $text . $enclosure . $delimiter;
								}
								else
								{
									$string .= $cell . $delimiter;
								}
							}
							$string = substr($string, 0, -1) . $newline;
						}
					break;

					case 'XML':
						$mimetype = 'application/xml';
						$filetype = 'xml';
						$string = '<?xml version="1.0"?>' . "\n";
						$string .= "<phpbb>\n";

						foreach ($data as $value)
						{
							$string .= "\t<privmsg>\n";

							if (is_array($value['to']))
							{
								foreach ($value['to'] as $key => $values)
								{
									foreach ($values as $type => $types)
									{
										foreach ($types as $name)
										{
											$string .= "\t\t<recipient type=\"$type\" status=\"$key\">$name</recipient>\n";
										}
									}
								}
							}

							unset($value['to']);

							foreach ($value as $tag => $text)
							{
								$string .= "\t\t<$tag>$text</$tag>\n";
							}

							$string .= "\t</privmsg>\n";
						}
						$string .= '</phpbb>';
					break;
				}

				header('Cache-Control: private, no-cache');
				header("Content-Type: $mimetype; name=\"data.$filetype\"");
				header("Content-disposition: attachment; filename=data.$filetype");
				echo $string;
				exit;
			}
		}
	}

	/**
	 * Get Messages from folder/user
	 */
	protected function get_pm_from($folder_id, $folder, $user_id)
	{
		$start = $this->request->variable('start', 0);

		// Additional vars later, pm ordering is mostly different from post ordering. :/
		$sort_days	= $this->request->variable('st', 0);
		$sort_key	= $this->request->variable('sk', 't');
		$sort_dir	= $this->request->variable('sd', 'd');

		// PM ordering options
		$limit_days = [0 => $this->language->lang('ALL_MESSAGES'), 1 => $this->language->lang('1_DAY'), 7 => $this->language->lang('7_DAYS'), 14 => $this->language->lang('2_WEEKS'), 30 => $this->language->lang('1_MONTH'), 90 => $this->language->lang('3_MONTHS'), 180 => $this->language->lang('6_MONTHS'), 365 => $this->language->lang('1_YEAR')];

		// No sort by Author for sentbox/outbox (already only author available)
		// Also, sort by msg_id for the time - private messages are not as prone to errors as posts are.
		if ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX)
		{
			$sort_by_text = ['t' => $this->language->lang('POST_TIME'), 's' => $this->language->lang('SUBJECT')];
			$sort_by_sql = ['t' => 'p.message_time', 's' => ['p.message_subject', 'p.message_time']];
		}
		else
		{
			$sort_by_text = ['a' => $this->language->lang('AUTHOR'), 't' => $this->language->lang('POST_TIME'), 's' => $this->language->lang('SUBJECT')];
			$sort_by_sql = ['a' => ['u.username_clean', 'p.message_time'], 't' => 'p.message_time', 's' => ['p.message_subject', 'p.message_time']];
		}

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		$folder_sql = 't.folder_id = ' . (int) $folder_id;

		// Limit pms to certain time frame, obtain correct pm count
		if ($sort_days)
		{
			$min_post_time = time() - ($sort_days * 86400);

			if ($this->request->is_set_post('sort'))
			{
				$start = 0;
			}

			$sql = 'SELECT COUNT(t.msg_id) AS pm_count
				FROM ' . $this->tables['privmsgs_to'] . ' t, ' . $this->tables['privmsgs'] . " p
				WHERE $folder_sql
					AND t.user_id = $user_id
					AND t.msg_id = p.msg_id
					AND p.message_time >= $min_post_time";
			$result = $this->db->sql_query_limit($sql, 1);
			$pm_count = (int) $this->db->sql_fetchfield('pm_count');
			$this->db->sql_freeresult($result);

			$sql_limit_time = "AND p.message_time >= $min_post_time";
		}
		else
		{
			$pm_count = (!empty($folder[$folder_id]['num_messages'])) ? $folder[$folder_id]['num_messages'] : 0;
			$sql_limit_time = '';
		}

		$base_url = $this->helper->route('ucp_pm_view', ['action' => 'view_folder', 'folder' => $folder_id]) . '&amp;' . $u_sort_param;
		$start = $this->pagination->validate_start($start, $this->config['topics_per_page'], $pm_count);
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $pm_count, $this->config['topics_per_page'], $start);

		$template_vars = [
			'TOTAL_MESSAGES'	=> $this->language->lang('VIEW_PM_MESSAGES', (int) $pm_count),

			'POST_IMG'		=> (!$this->auth->acl_get('u_sendpm')) ? $this->user->img('button_topic_locked', 'POST_PM_LOCKED') : $this->user->img('button_pm_new', 'POST_NEW_PM'),

			'S_NO_AUTH_SEND_MESSAGE'	=> !$this->auth->acl_get('u_sendpm'),

			'S_SELECT_SORT_DIR'		=> $s_sort_dir,
			'S_SELECT_SORT_KEY'		=> $s_sort_key,
			'S_SELECT_SORT_DAYS'	=> $s_limit_days,
			'S_TOPIC_ICONS'			=> ($this->config['enable_pm_icons']) ? true : false,

			'U_POST_NEW_TOPIC'	=> ($this->auth->acl_get('u_sendpm')) ? $this->helper->route('ucp_pm_compose') : '',
			'S_PM_ACTION'		=> $this->helper->route('ucp_pm_view', ['action' => 'view_folder', 'folder' => $folder_id, 'start' => $start]),
		];

		/**
		 * Modify template variables before they are assigned
		 *
		 * @event core.ucp_pm_view_folder_get_pm_from_template
		 * @var int		folder_id		Folder ID
		 * @var array	folder			Folder data
		 * @var int		user_id			User ID
		 * @var string	base_url		Pagination base URL
		 * @var int		start			Pagination start
		 * @var int		pm_count		Count of PMs
		 * @var array	template_vars	Template variables to be assigned
		 * @since 3.1.11-RC1
		 */
		$vars = [
			'folder_id',
			'folder',
			'user_id',
			'base_url',
			'start',
			'pm_count',
			'template_vars',
		];
		extract($this->dispatcher->trigger_event('core.ucp_pm_view_folder_get_pm_from_template', compact($vars)));

		$this->template->assign_vars($template_vars);

		// Grab all pm data
		$rowset = $pm_list = [];

		// If the user is trying to reach late pages, start searching from the end
		$store_reverse = false;
		$sql_limit = $this->config['topics_per_page'];
		if ($start > $pm_count / 2)
		{
			$store_reverse = true;

			// Select the sort order
			$direction = ($sort_dir == 'd') ? 'ASC' : 'DESC';
			$sql_limit = $this->pagination->reverse_limit($start, $sql_limit, $pm_count);
			$sql_start = $this->pagination->reverse_start($start, $sql_limit, $pm_count);
		}
		else
		{
			// Select the sort order
			$direction = ($sort_dir == 'd') ? 'DESC' : 'ASC';
			$sql_start = $start;
		}

		// Sql sort order
		if (is_array($sort_by_sql[$sort_key]))
		{
			$sql_sort_order = implode(' ' . $direction . ', ', $sort_by_sql[$sort_key]) . ' ' . $direction;
		}
		else
		{
			$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . $direction;
		}

		$sql_ary = [
			'SELECT'	=> 't.*, p.root_level, p.message_time, p.message_subject, p.icon_id, p.to_address, p.message_attachment, p.bcc_address, u.username, u.username_clean, u.user_colour, p.message_reported',
			'FROM'		=> [
				$this->tables['privmsgs_to']	=> 't',
				$this->tables['privmsgs']		=> 'p',
				$this->tables['users']			=> 'u',
			],
			'WHERE'		=> "t.user_id = $user_id
			AND p.author_id = u.user_id
			AND $folder_sql
			AND t.msg_id = p.msg_id
			$sql_limit_time",
			'ORDER_BY'	=> $sql_sort_order,
		];

		/**
		 * Modify SQL before it is executed
		 *
		 * @event core.ucp_pm_view_folder_get_pm_from_sql
		 * @var array	sql_ary		SQL array
		 * @var int		sql_limit	SQL limit
		 * @var int		sql_start	SQL start
		 * @since 3.1.11-RC1
		 */
		$vars = [
			'sql_ary',
			'sql_limit',
			'sql_start',
		];
		extract($this->dispatcher->trigger_event('core.ucp_pm_view_folder_get_pm_from_sql', compact($vars)));

		$result = $this->db->sql_query_limit($this->db->sql_build_query('SELECT', $sql_ary), $sql_limit, $sql_start);

		$pm_reported = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[$row['msg_id']] = $row;
			$pm_list[] = $row['msg_id'];
			if ($row['message_reported'])
			{
				$pm_reported[] = $row['msg_id'];
			}
		}
		$this->db->sql_freeresult($result);

		// Fetch the report_ids, if there are any reported pms.
		if (!empty($pm_reported) && $this->auth->acl_getf_global('m_report'))
		{
			$sql = 'SELECT pm_id, report_id
				FROM ' . $this->tables['reports'] . '
				WHERE report_closed = 0
					AND ' . $this->db->sql_in_set('pm_id', $pm_reported);
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$rowset[$row['pm_id']]['report_id'] = $row['report_id'];
			}
			$this->db->sql_freeresult($result);
		}

		$pm_list = ($store_reverse) ? array_reverse($pm_list) : $pm_list;

		return [
			'pm_count'	=> $pm_count,
			'pm_list'	=> $pm_list,
			'rowset'	=> $rowset,
		];
	}
}
