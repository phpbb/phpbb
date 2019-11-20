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

class pm_view_folder
{
	/**
	 * View message folder
	 * Called from ucp_pm with mode == 'view' && action == 'view_folder'
	 */
	function view_folder($id, $mode, $folder_id, $folder)
	{

		$submit_export = ($this->request->is_set_post('submit_export')) ? true : false;

		$folder_info = get_pm_from($folder_id, $folder, $this->user->data['user_id']);

		add_form_key('ucp_pm_view');

		if (!$submit_export)
		{
			$this->language->add_lang('viewforum');

			// Grab icons
			$icons = $this->cache->obtain_icons();

			$color_rows = ['message_reported', 'marked', 'replied'];

			$_module = new p_master();
			$_module->list_modules('ucp');
			$_module->set_active('zebra');

			$zebra_enabled = ($_module->active_module === false) ? false : true;

			unset($_module);

			if ($zebra_enabled)
			{
				$color_rows = array_merge($color_rows, ['friend', 'foe']);
			}

			foreach ($color_rows as $var)
			{
				$this->template->assign_block_vars('pm_colour_info', [
						'IMG'	=> $this->user->img("pm_{$var}", ''),
						'CLASS'	=> "pm_{$var}_colour",
						'LANG'	=> $this->language->lang(strtoupper($var) . '_MESSAGE')]
				);
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
					$s_folder_move_options .= sprintf($this->language->lang('MOVE_MARKED_TO_FOLDER'), $folder_ary['folder_name']);
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
					'S_MOVE_MARKED_OPTIONS'	=> $s_folder_move_options]
			);

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
					$view_message_url = append_sid("{$this->root_path}ucp.$this->php_ext", "i=$id&amp;mode=view&amp;f=$folder_id&amp;p=$message_id");
					$remove_message_url = append_sid("{$this->root_path}ucp.$this->php_ext", "i=$id&amp;mode=compose&amp;action=delete&amp;p=$message_id");

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
							'U_FOLDER'			=> (isset($folder[$row['folder_id']])) ? append_sid("{$this->root_path}ucp.$this->php_ext", 'folder=' . $row['folder_id']) : '',
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
							'U_MCP_REPORT'		=> (isset($row['report_id'])) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=pm_reports&amp;mode=pm_report_details&amp;r=' . $row['report_id']) : '',
							'RECIPIENTS'		=> ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX) ? implode($this->language->lang('COMMA_SEPARATOR'), $address_list[$message_id]) : '']
					);
				}
				unset($folder_info['rowset']);

				$this->template->assign_vars([
						'S_SHOW_RECIPIENTS'		=> ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX) ? true : false,
						'S_SHOW_COLOUR_LEGEND'	=> true,

						'REPORTED_IMG'			=> $this->user->img('icon_topic_reported', 'PM_REPORTED'),
						'S_PM_ICONS'			=> ($this->config['enable_pm_icons']) ? true : false]
				);
			}
		}
		else
		{
			if (!check_form_key('ucp_pm_view'))
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
						'message'	=> $message_row['message_text']
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
	function get_pm_from($folder_id, $folder, $user_id)
	{

		$start = $this->request->variable('start', 0);

		// Additional vars later, pm ordering is mostly different from post ordering. :/
		$sort_days	= $this->request->variable('st', 0);
		$sort_key	= $this->request->variable('sk', 't');
		$sort_dir	= $this->request->variable('sd', 'd');

		/* @var $pagination \phpbb\pagination */
		$pagination = $phpbb_container->get('pagination');

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

		$base_url = append_sid("{$this->root_path}ucp.$this->php_ext", "i=pm&amp;mode=view&amp;action=view_folder&amp;f=$folder_id&amp;$u_sort_param");
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

			'U_POST_NEW_TOPIC'	=> ($this->auth->acl_get('u_sendpm')) ? append_sid("{$this->root_path}ucp.$this->php_ext", 'i=pm&amp;mode=compose') : '',
			'S_PM_ACTION'		=> append_sid("{$this->root_path}ucp.$this->php_ext", "i=pm&amp;mode=view&amp;action=view_folder&amp;f=$folder_id" . (($start !== 0) ? "&amp;start=$start" : '')),
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
			'rowset'	=> $rowset
		];
	}
}
