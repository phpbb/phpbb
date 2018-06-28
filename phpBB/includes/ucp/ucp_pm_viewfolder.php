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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* View message folder
* Called from ucp_pm with mode == 'view' && action == 'view_folder'
*/
function view_folder($id, $mode, $folder_id, $folder)
{
	global $user, $template, $auth, $db, $cache, $request;
	global $phpbb_root_path, $config, $phpEx;

	$submit_export = (isset($_POST['submit_export'])) ? true : false;

	$folder_info = get_pm_from($folder_id, $folder, $user->data['user_id']);

	if (!$submit_export)
	{
		$user->add_lang('viewforum');

		// Grab icons
		$icons = $cache->obtain_icons();

		$color_rows = array('marked', 'replied');

		$_module = new p_master();
		$_module->list_modules('ucp');
		$_module->set_active('zebra');

		$zebra_enabled = ($_module->active_module === false) ? false : true;

		unset($_module);

		if ($zebra_enabled)
		{
			$color_rows = array_merge($color_rows, array('friend', 'foe'));
		}

		foreach ($color_rows as $var)
		{
			$template->assign_block_vars('pm_colour_info', array(
				'IMG'	=> $user->img("pm_{$var}", ''),
				'CLASS'	=> "pm_{$var}_colour",
				'LANG'	=> $user->lang[strtoupper($var) . '_MESSAGE'])
			);
		}

		$mark_options = array('mark_important', 'delete_marked');

		// Minimise edits
		if (!$auth->acl_get('u_pm_delete') && $key = array_search('delete_marked', $mark_options))
		{
			unset($mark_options[$key]);
		}

		$s_mark_options = '';
		foreach ($mark_options as $mark_option)
		{
			$s_mark_options .= '<option value="' . $mark_option . '">' . $user->lang[strtoupper($mark_option)] . '</option>';
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
				$s_folder_move_options .= sprintf($user->lang['MOVE_MARKED_TO_FOLDER'], $folder_ary['folder_name']);
				$s_folder_move_options .= (($folder_ary['unread_messages']) ? ' [' . $folder_ary['unread_messages'] . '] ' : '') . '</option>';
			}
		}
		$friend = $foe = array();

		// Get friends and foes
		$sql = 'SELECT *
			FROM ' . ZEBRA_TABLE . '
			WHERE user_id = ' . $user->data['user_id'];
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$friend[$row['zebra_id']] = $row['friend'];
			$foe[$row['zebra_id']] = $row['foe'];
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'S_MARK_OPTIONS'		=> $s_mark_options,
			'S_MOVE_MARKED_OPTIONS'	=> $s_folder_move_options)
		);

		// Okay, lets dump out the page ...
		if (count($folder_info['pm_list']))
		{
			$address_list = array();

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
				$view_message_url = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=$id&amp;mode=view&amp;f=$folder_id&amp;p=$message_id");
				$remove_message_url = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=$id&amp;mode=compose&amp;action=delete&amp;p=$message_id");

				$row_indicator = '';
				foreach ($color_rows as $var)
				{
					if (($var != 'friend' && $var != 'foe' && $row['pm_' . $var])
						||
						(($var == 'friend' || $var == 'foe') && isset(${$var}[$row['author_id']]) && ${$var}[$row['author_id']]))
					{
						$row_indicator = $var;
						break;
					}
				}

				// Send vars to template
				$template->assign_block_vars('messagerow', array(
					'PM_CLASS'			=> ($row_indicator) ? 'pm_' . $row_indicator . '_colour' : '',

					'MESSAGE_AUTHOR_FULL'		=> get_username_string('full', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
					'MESSAGE_AUTHOR_COLOUR'		=> get_username_string('colour', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
					'MESSAGE_AUTHOR'			=> get_username_string('username', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
					'U_MESSAGE_AUTHOR'			=> get_username_string('profile', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),

					'FOLDER_ID'			=> $folder_id,
					'MESSAGE_ID'		=> $message_id,
					'SENT_TIME'			=> $user->format_date($row['message_time']),
					'SUBJECT'			=> censor_text($row['message_subject']),
					'FOLDER'			=> (isset($folder[$row['folder_id']])) ? $folder[$row['folder_id']]['folder_name'] : '',
					'U_FOLDER'			=> (isset($folder[$row['folder_id']])) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'folder=' . $row['folder_id']) : '',
					'PM_ICON_IMG'		=> (!empty($icons[$row['icon_id']])) ? '<img src="' . $config['icons_path'] . '/' . $icons[$row['icon_id']]['img'] . '" width="' . $icons[$row['icon_id']]['width'] . '" height="' . $icons[$row['icon_id']]['height'] . '" alt="" title="" />' : '',
					'PM_ICON_URL'		=> (!empty($icons[$row['icon_id']])) ? $config['icons_path'] . '/' . $icons[$row['icon_id']]['img'] : '',
					'FOLDER_IMG'		=> $user->img($folder_img, $folder_alt),
					'FOLDER_IMG_STYLE'	=> $folder_img,
					'PM_IMG'			=> ($row_indicator) ? $user->img('pm_' . $row_indicator, '') : '',
					'ATTACH_ICON_IMG'	=> ($auth->acl_get('u_pm_download') && $row['message_attachment'] && $config['allow_pm_attach']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',

					'S_PM_UNREAD'		=> ($row['pm_unread']) ? true : false,
					'S_PM_DELETED'		=> ($row['pm_deleted']) ? true : false,
					'S_PM_REPORTED'		=> (isset($row['report_id'])) ? true : false,
					'S_AUTHOR_DELETED'	=> ($row['author_id'] == ANONYMOUS) ? true : false,
					'S_PM_MARKED'		=> $row['pm_marked'],
					'S_AUTHOR_FOE'		=> ($row_indicator == 'foe') ? true : false,

					'U_VIEW_PM'			=> ($row['pm_deleted']) ? '' : $view_message_url,
					'U_REMOVE_PM'		=> ($row['pm_deleted']) ? $remove_message_url : '',
					'U_MCP_REPORT'		=> (isset($row['report_id'])) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=pm_reports&amp;mode=pm_report_details&amp;r=' . $row['report_id']) : '',
					'RECIPIENTS'		=> ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX) ? implode($user->lang['COMMA_SEPARATOR'], $address_list[$message_id]) : '')
				);
			}
			unset($folder_info['rowset']);

			$template->assign_vars(array(
				'S_SHOW_RECIPIENTS'		=> ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX) ? true : false,
				'S_SHOW_COLOUR_LEGEND'	=> true,

				'REPORTED_IMG'			=> $user->img('icon_topic_reported', 'PM_REPORTED'),
				'S_PM_ICONS'			=> ($config['enable_pm_icons']) ? true : false)
			);
		}
	}
	else
	{
		$export_type = $request->variable('export_option', '');
		$enclosure = $request->variable('enclosure', '');
		$delimiter = $request->variable('delimiter', '');

		if ($export_type == 'CSV' && ($delimiter === '' || $enclosure === ''))
		{
			$template->assign_var('PROMPT', true);
		}
		else
		{
			// Build Recipient List if in outbox/sentbox

			$address_temp = $address = $data = array();

			if ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX)
			{
				foreach ($folder_info['rowset'] as $message_id => $row)
				{
					$address_temp[$message_id] = rebuild_header(array('to' => $row['to_address'], 'bcc' => $row['bcc_address']));
					$address[$message_id] = array();
				}
			}

			foreach ($folder_info['pm_list'] as $message_id)
			{
				$row = &$folder_info['rowset'][$message_id];

				include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

				$sql = 'SELECT p.message_text, p.bbcode_uid
					FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . ' p, ' . USERS_TABLE . ' u
					WHERE t.user_id = ' . $user->data['user_id'] . "
						AND p.author_id = u.user_id
						AND t.folder_id = $folder_id
						AND t.msg_id = p.msg_id
						AND p.msg_id = $message_id";
				$result = $db->sql_query_limit($sql, 1);
				$message_row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$_types = array('u', 'g');
				foreach ($_types as $ug_type)
				{
					if (isset($address_temp[$message_id][$ug_type]) && count($address_temp[$message_id][$ug_type]))
					{
						if (!isset($address[$message_id][$ug_type]))
						{
							$address[$message_id][$ug_type] = array();
						}
						if ($ug_type == 'u')
						{
							$sql = 'SELECT user_id as id, username as name
								FROM ' . USERS_TABLE . '
								WHERE ';
						}
						else
						{
							$sql = 'SELECT group_id as id, group_name as name
								FROM ' . GROUPS_TABLE . '
								WHERE ';
						}
						$sql .= $db->sql_in_set(($ug_type == 'u') ? 'user_id' : 'group_id', array_map('intval', array_keys($address_temp[$message_id][$ug_type])));

						$result = $db->sql_query($sql);

						while ($info_row = $db->sql_fetchrow($result))
						{
							$address[$message_id][$ug_type][$address_temp[$message_id][$ug_type][$info_row['id']]][] = $info_row['name'];
							unset($address_temp[$message_id][$ug_type][$info_row['id']]);
						}
						$db->sql_freeresult($result);
					}
				}

				// There is the chance that all recipients of the message got deleted. To avoid creating
				// exports without recipients, we add a bogus "undisclosed recipient".
				if (!(isset($address[$message_id]['g']) && count($address[$message_id]['g'])) &&
					!(isset($address[$message_id]['u']) && count($address[$message_id]['u'])))
				{
					$address[$message_id]['u'] = array();
					$address[$message_id]['u']['to'] = array();
					$address[$message_id]['u']['to'][] = $user->lang['UNDISCLOSED_RECIPIENT'];
				}

				decode_message($message_row['message_text'], $message_row['bbcode_uid']);

				$data[] = array(
					'subject'	=> censor_text($row['message_subject']),
					'sender'	=> $row['username'],
					// ISO 8601 date. For PHP4 we are able to hardcode the timezone because $user->format_date() does not set it.
					'date'		=> $user->format_date($row['message_time'], 'c', true),
					'to'		=> ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX) ? $address[$message_id] : '',
					'message'	=> $message_row['message_text']
				);
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
	global $user, $db, $template, $config, $auth, $phpbb_container, $phpbb_root_path, $phpEx, $request, $phpbb_dispatcher;

	$start = $request->variable('start', 0);

	// Additional vars later, pm ordering is mostly different from post ordering. :/
	$sort_days	= $request->variable('st', 0);
	$sort_key	= $request->variable('sk', 't');
	$sort_dir	= $request->variable('sd', 'd');

	/* @var $pagination \phpbb\pagination */
	$pagination = $phpbb_container->get('pagination');

	// PM ordering options
	$limit_days = array(0 => $user->lang['ALL_MESSAGES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);

	// No sort by Author for sentbox/outbox (already only author available)
	// Also, sort by msg_id for the time - private messages are not as prone to errors as posts are.
	if ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX)
	{
		$sort_by_text = array('t' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);
		$sort_by_sql = array('t' => 'p.message_time', 's' => array('p.message_subject', 'p.message_time'));
	}
	else
	{
		$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);
		$sort_by_sql = array('a' => array('u.username_clean', 'p.message_time'), 't' => 'p.message_time', 's' => array('p.message_subject', 'p.message_time'));
	}

	$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

	$folder_sql = 't.folder_id = ' . (int) $folder_id;

	// Limit pms to certain time frame, obtain correct pm count
	if ($sort_days)
	{
		$min_post_time = time() - ($sort_days * 86400);

		if (isset($_POST['sort']))
		{
			$start = 0;
		}

		$sql = 'SELECT COUNT(t.msg_id) AS pm_count
			FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . " p
			WHERE $folder_sql
				AND t.user_id = $user_id
				AND t.msg_id = p.msg_id
				AND p.message_time >= $min_post_time";
		$result = $db->sql_query_limit($sql, 1);
		$pm_count = (int) $db->sql_fetchfield('pm_count');
		$db->sql_freeresult($result);

		$sql_limit_time = "AND p.message_time >= $min_post_time";
	}
	else
	{
		$pm_count = (!empty($folder[$folder_id]['num_messages'])) ? $folder[$folder_id]['num_messages'] : 0;
		$sql_limit_time = '';
	}

	$base_url = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=pm&amp;mode=view&amp;action=view_folder&amp;f=$folder_id&amp;$u_sort_param");
	$start = $pagination->validate_start($start, $config['topics_per_page'], $pm_count);
	$pagination->generate_template_pagination($base_url, 'pagination', 'start', $pm_count, $config['topics_per_page'], $start);

	$template_vars = array(
		'TOTAL_MESSAGES'	=> $user->lang('VIEW_PM_MESSAGES', (int) $pm_count),

		'POST_IMG'		=> (!$auth->acl_get('u_sendpm')) ? $user->img('button_topic_locked', 'POST_PM_LOCKED') : $user->img('button_pm_new', 'POST_NEW_PM'),

		'S_NO_AUTH_SEND_MESSAGE'	=> !$auth->acl_get('u_sendpm'),

		'S_SELECT_SORT_DIR'		=> $s_sort_dir,
		'S_SELECT_SORT_KEY'		=> $s_sort_key,
		'S_SELECT_SORT_DAYS'	=> $s_limit_days,
		'S_TOPIC_ICONS'			=> ($config['enable_pm_icons']) ? true : false,

		'U_POST_NEW_TOPIC'	=> ($auth->acl_get('u_sendpm')) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;mode=compose') : '',
		'S_PM_ACTION'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", "i=pm&amp;mode=view&amp;action=view_folder&amp;f=$folder_id" . (($start !== 0) ? "&amp;start=$start" : '')),
	);

	/**
	* Modify template variables before they are assigned
	*
	* @event core.ucp_pm_view_folder_get_pm_from_template
	* @var	int		folder_id		Folder ID
	* @var	array	folder			Folder data
	* @var	int		user_id			User ID
	* @var	string	base_url		Pagination base URL
	* @var	int		start			Pagination start
	* @var	int		pm_count		Count of PMs
	* @var	array	template_vars	Template variables to be assigned
	* @since 3.1.11-RC1
	*/
	$vars = array(
		'folder_id',
		'folder',
		'user_id',
		'base_url',
		'start',
		'pm_count',
		'template_vars',
	);
	extract($phpbb_dispatcher->trigger_event('core.ucp_pm_view_folder_get_pm_from_template', compact($vars)));

	$template->assign_vars($template_vars);

	// Grab all pm data
	$rowset = $pm_list = array();

	// If the user is trying to reach late pages, start searching from the end
	$store_reverse = false;
	$sql_limit = $config['topics_per_page'];
	if ($start > $pm_count / 2)
	{
		$store_reverse = true;

		// Select the sort order
		$direction = ($sort_dir == 'd') ? 'ASC' : 'DESC';
		$sql_limit = $pagination->reverse_limit($start, $sql_limit, $pm_count);
		$sql_start = $pagination->reverse_start($start, $sql_limit, $pm_count);
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

	$sql_ary = array(
		'SELECT'	=> 't.*, p.root_level, p.message_time, p.message_subject, p.icon_id, p.to_address, p.message_attachment, p.bcc_address, u.username, u.username_clean, u.user_colour, p.message_reported, (
			SELECT SUM(tu.pm_unread)
			FROM ' . PRIVMSGS_TO_TABLE . ' tu
			LEFT JOIN ' . PRIVMSGS_TABLE . ' pu
				ON (tu.msg_id = pu.msg_id)
			WHERE pu.root_level = p.msg_id
				OR pu.msg_id = p.msg_id
		) AS pm_unread',
		'FROM'		=> array(
			PRIVMSGS_TO_TABLE	=> 't',
		),
		'LEFT_JOIN'	=> array(
			array(
				'FROM'	=> array(PRIVMSGS_TABLE => 'p'),
				'ON'	=> 't.msg_id = p.msg_id',
			),
			array(
				'FROM'	=> array(USERS_TABLE => 'u'),
				'ON'	=> 'p.author_id = u.user_id',
			),
		),
		'WHERE'		=> "t.user_id = $user_id
			AND p.root_level = 0
			AND $folder_sql
			$sql_limit_time",
		'ORDER_BY'	=> $sql_sort_order,
	);

	/**
	* Modify SQL before it is executed
	*
	* @event core.ucp_pm_view_folder_get_pm_from_sql
	* @var	array	sql_ary		SQL array
	* @var	int		sql_limit	SQL limit
	* @var	int		sql_start	SQL start
	* @since 3.1.11-RC1
	*/
	$vars = array(
		'sql_ary',
		'sql_limit',
		'sql_start',
	);
	extract($phpbb_dispatcher->trigger_event('core.ucp_pm_view_folder_get_pm_from_sql', compact($vars)));

	$result = $db->sql_query_limit($db->sql_build_query('SELECT', $sql_ary), $sql_limit, $sql_start);

	$pm_reported = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$rowset[$row['msg_id']] = $row;
		$pm_list[] = $row['msg_id'];
		if ($row['message_reported'])
		{
			$pm_reported[] = $row['msg_id'];
		}
	}
	$db->sql_freeresult($result);

	// Fetch the report_ids, if there are any reported pms.
	if (!empty($pm_reported) && $auth->acl_getf_global('m_report'))
	{
		$sql = 'SELECT pm_id, report_id
			FROM ' . REPORTS_TABLE . '
			WHERE report_closed = 0
				AND ' . $db->sql_in_set('pm_id', $pm_reported);
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$rowset[$row['pm_id']]['report_id'] = $row['report_id'];
		}
		$db->sql_freeresult($result);
	}

	$pm_list = ($store_reverse) ? array_reverse($pm_list) : $pm_list;

	return array(
		'pm_count'	=> $pm_count,
		'pm_list'	=> $pm_list,
		'rowset'	=> $rowset
	);
}

/**
 * View private message
 */
function view_message($id, $mode, $folder_id, $msg_id, $folder, $message_row)
{
	global $user, $template, $auth, $db, $phpbb_container;
	global $phpbb_root_path, $request, $phpEx, $config, $phpbb_dispatcher;

	$user->add_lang(array('viewtopic', 'memberlist'));

	$msg_id		= (int) $msg_id;
	$folder_id	= (int) $folder_id;
	$author_id	= (int) $message_row['author_id'];
	$view		= $request->variable('view', '');

	// Not able to view message, it was deleted by the sender
	if ($message_row['pm_deleted'])
	{
		$meta_info = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=pm&amp;folder=$folder_id");
		$message = $user->lang['NO_AUTH_READ_REMOVED_MESSAGE'];

		$message .= '<br /><br />' . sprintf($user->lang['RETURN_FOLDER'], '<a href="' . $meta_info . '">', '</a>');
		send_status_line(403, 'Forbidden');
		trigger_error($message);
	}

	// Do not allow hold messages to be seen
	if ($folder_id == PRIVMSGS_HOLD_BOX)
	{
		trigger_error('NO_AUTH_READ_HOLD_MESSAGE');
	}

	// Load the custom profile fields
	if ($config['load_cpf_pm'])
	{
		/* @var $cp \phpbb\profilefields\manager */
		$cp = $phpbb_container->get('profilefields.manager');

		$profile_fields = $cp->grab_profile_fields_data($author_id);
	}

	// Assign TO/BCC Addresses to template
	write_pm_addresses(array('to' => $message_row['to_address'], 'bcc' => $message_row['bcc_address']), $author_id);

	$user_info = get_user_information($author_id, $message_row);

	// Parse the message and subject
	$parse_flags = ($message_row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
	$message = generate_text_for_display($message_row['message_text'], $message_row['bbcode_uid'], $message_row['bbcode_bitfield'], $parse_flags, true);

	// Replace naughty words such as farty pants
	$message_row['message_subject'] = censor_text($message_row['message_subject']);

	// Editing information
	if ($message_row['message_edit_count'] && $config['display_last_edited'])
	{
		if (!$message_row['message_edit_user'])
		{
			$display_username = get_username_string('full', $author_id, $user_info['username'], $user_info['user_colour']);
		}
		else
		{
			$edit_user_info = get_user_information($message_row['message_edit_user'], false);
			$display_username = get_username_string('full', $message_row['message_edit_user'], $edit_user_info['username'], $edit_user_info['user_colour']);
		}
		$l_edited_by = '<br /><br />' . $user->lang('EDITED_TIMES_TOTAL', (int) $message_row['message_edit_count'], $display_username, $user->format_date($message_row['message_edit_time'], false, true));
	}
	else
	{
		$l_edited_by = '';
	}

	// Pull attachment data
	$display_notice = false;
	$attachments = array();

	if ($message_row['message_attachment'] && $config['allow_pm_attach'])
	{
		if ($auth->acl_get('u_pm_download'))
		{
			$sql = 'SELECT *
				FROM ' . ATTACHMENTS_TABLE . "
				WHERE post_msg_id = $msg_id
					AND in_message = 1
				ORDER BY filetime DESC, post_msg_id ASC";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$attachments[] = $row;
			}
			$db->sql_freeresult($result);

			// No attachments exist, but message table thinks they do so go ahead and reset attach flags
			if (!count($attachments))
			{
				$sql = 'UPDATE ' . PRIVMSGS_TABLE . "
					SET message_attachment = 0
					WHERE msg_id = $msg_id";
				$db->sql_query($sql);
			}
		}
		else
		{
			$display_notice = true;
		}
	}

	// Assign inline attachments
	if (!empty($attachments))
	{
		$update_count = array();
		parse_attachments(false, $message, $attachments, $update_count);

		// Update the attachment download counts
		if (count($update_count))
		{
			$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
				SET download_count = download_count + 1
				WHERE ' . $db->sql_in_set('attach_id', array_unique($update_count));
			$db->sql_query($sql);
		}
	}

	$user_info['sig'] = '';

	$signature = ($message_row['enable_sig'] && $config['allow_sig'] && $auth->acl_get('u_sig') && $user->optionget('viewsigs')) ? $user_info['user_sig'] : '';

	// End signature parsing, only if needed
	if ($signature)
	{
		$parse_flags = ($user_info['user_sig_bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
		$signature = generate_text_for_display($signature, $user_info['user_sig_bbcode_uid'], $user_info['user_sig_bbcode_bitfield'], $parse_flags, true);
	}

	$url = append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm');

	// Number of "to" recipients
	$num_recipients = (int) preg_match_all('/:?(u|g)_([0-9]+):?/', $message_row['to_address'], $match);

	$bbcode_status	= ($config['allow_bbcode'] && $config['auth_bbcode_pm'] && $auth->acl_get('u_pm_bbcode')) ? true : false;

	// Get the profile fields template data
	$cp_row = array();
	if ($config['load_cpf_pm'] && isset($profile_fields[$author_id]))
	{
		// Filter the fields we don't want to show
		foreach ($profile_fields[$author_id] as $used_ident => $profile_field)
		{
			if (!$profile_field['data']['field_show_on_pm'])
			{
				unset($profile_fields[$author_id][$used_ident]);
			}
		}

		if (isset($profile_fields[$author_id]))
		{
			$cp_row = $cp->generate_profile_fields_template_data($profile_fields[$author_id]);
		}
	}

	$u_pm = $u_jabber = '';

	if ($config['allow_privmsg'] && $auth->acl_get('u_sendpm') && ($user_info['user_allow_pm'] || $auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_')))
	{
		$u_pm = append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;mode=compose&amp;u=' . $author_id);
	}

	if ($config['jab_enable'] && $user_info['user_jabber'] && $auth->acl_get('u_sendim'))
	{
		$u_jabber = append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=contact&amp;action=jabber&amp;u=' . $author_id);
	}

	$msg_data = array(
		'MESSAGE_AUTHOR_FULL'		=> get_username_string('full', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),
		'MESSAGE_AUTHOR_COLOUR'		=> get_username_string('colour', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),
		'MESSAGE_AUTHOR'			=> get_username_string('username', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),
		'U_MESSAGE_AUTHOR'			=> get_username_string('profile', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username']),

		'RANK_TITLE'		=> $user_info['rank_title'],
		'RANK_IMG'			=> $user_info['rank_image'],
		'AUTHOR_AVATAR'		=> (isset($user_info['avatar'])) ? $user_info['avatar'] : '',
		'AUTHOR_JOINED'		=> $user->format_date($user_info['user_regdate']),
		'AUTHOR_POSTS'		=> (int) $user_info['user_posts'],
		'U_AUTHOR_POSTS'	=> ($config['load_search'] && $auth->acl_get('u_search')) ? append_sid("{$phpbb_root_path}search.$phpEx", "author_id=$author_id&amp;sr=posts") : '',
		'CONTACT_USER'		=> $user->lang('CONTACT_USER', get_username_string('username', $author_id, $user_info['username'], $user_info['user_colour'], $user_info['username'])),

		'ONLINE_IMG'		=> (!$config['load_onlinetrack']) ? '' : ((isset($user_info['online']) && $user_info['online']) ? $user->img('icon_user_online', $user->lang['ONLINE']) : $user->img('icon_user_offline', $user->lang['OFFLINE'])),
		'S_ONLINE'			=> (!$config['load_onlinetrack']) ? false : ((isset($user_info['online']) && $user_info['online']) ? true : false),
		'DELETE_IMG'		=> $user->img('icon_post_delete', $user->lang['DELETE_MESSAGE']),
		'INFO_IMG'			=> $user->img('icon_post_info', $user->lang['VIEW_PM_INFO']),
		'PROFILE_IMG'		=> $user->img('icon_user_profile', $user->lang['READ_PROFILE']),
		'EMAIL_IMG'			=> $user->img('icon_contact_email', $user->lang['SEND_EMAIL']),
		'QUOTE_IMG'			=> $user->img('icon_post_quote', $user->lang['POST_QUOTE_PM']),
		'REPLY_IMG'			=> $user->img('button_pm_reply', $user->lang['POST_REPLY_PM']),
		'REPORT_IMG'		=> $user->img('icon_post_report', 'REPORT_PM'),
		'EDIT_IMG'			=> $user->img('icon_post_edit', $user->lang['POST_EDIT_PM']),
		'MINI_POST_IMG'		=> $user->img('icon_post_target', $user->lang['PM']),

		'SENT_DATE'			=> ($view == 'print') ? $user->format_date($message_row['message_time'], false, true) : $user->format_date($message_row['message_time']),
		'SUBJECT'			=> $message_row['message_subject'],
		'MESSAGE'			=> $message,
		'SIGNATURE'			=> ($message_row['enable_sig']) ? $signature : '',
		'EDITED_MESSAGE'	=> $l_edited_by,
		'MESSAGE_ID'		=> $message_row['msg_id'],

		'U_PM'			=>  $u_pm,
		'U_JABBER'		=>  $u_jabber,

		'U_DELETE'			=> ($auth->acl_get('u_pm_delete')) ? "$url&amp;mode=compose&amp;action=delete&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_EMAIL'			=> $user_info['email'],
		'U_REPORT'			=> ($config['allow_pm_report']) ? $phpbb_container->get('controller.helper')->route('phpbb_report_pm_controller', array('id' => $message_row['msg_id'])) : '',
		'U_QUOTE'			=> ($auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? "$url&amp;mode=compose&amp;action=quote&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_EDIT'			=> (($message_row['message_time'] > time() - ($config['pm_edit_time'] * 60) || !$config['pm_edit_time']) && $folder_id == PRIVMSGS_OUTBOX && $auth->acl_get('u_pm_edit')) ? "$url&amp;mode=compose&amp;action=edit&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_POST_REPLY_PM'	=> ($auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? "$url&amp;mode=compose&amp;action=reply&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
		'U_POST_REPLY_ALL'	=> ($auth->acl_get('u_sendpm') && $author_id != ANONYMOUS) ? "$url&amp;mode=compose&amp;action=reply&amp;f=$folder_id&amp;reply_to_all=1&amp;p=" . $message_row['msg_id'] : '',
		'U_PREVIOUS_PM'		=> "$url&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] . "&amp;view=previous",
		'U_NEXT_PM'			=> "$url&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] . "&amp;view=next",

		'U_PM_ACTION'		=> $url . '&amp;mode=compose&amp;f=' . $folder_id . '&amp;p=' . $message_row['msg_id'],

		'S_HAS_ATTACHMENTS'	=> (count($attachments)) ? true : false,
		'S_DISPLAY_NOTICE'	=> $display_notice && $message_row['message_attachment'],
		'S_AUTHOR_DELETED'	=> ($author_id == ANONYMOUS) ? true : false,
		'S_SPECIAL_FOLDER'	=> in_array($folder_id, array(PRIVMSGS_NO_BOX, PRIVMSGS_OUTBOX)),
		'S_PM_RECIPIENTS'	=> $num_recipients,
		'S_BBCODE_ALLOWED'	=> ($bbcode_status) ? 1 : 0,
		'S_CUSTOM_FIELDS'	=> (!empty($cp_row['row'])) ? true : false,

		'U_PRINT_PM'		=> ($config['print_pm'] && $auth->acl_get('u_pm_printpm')) ? "$url&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] . "&amp;view=print" : '',
		'U_FORWARD_PM'		=> ($config['forward_pm'] && $auth->acl_get('u_sendpm') && $auth->acl_get('u_pm_forward')) ? "$url&amp;mode=compose&amp;action=forward&amp;f=$folder_id&amp;p=" . $message_row['msg_id'] : '',
	);

	/**
	 * Modify pm and sender data before it is assigned to the template
	 *
	 * @event core.ucp_pm_view_messsage
	 * @var	mixed	id			Active module category (can be int or string)
	 * @var	string	mode		Active module
	 * @var	int		folder_id	ID of the folder the message is in
	 * @var	int		msg_id		ID of the private message
	 * @var	array	folder		Array with data of user's message folders
	 * @var	array	message_row	Array with message data
	 * @var	array	cp_row		Array with senders custom profile field data
	 * @var	array	msg_data	Template array with message data
	 * @var 	array	user_info	User data of the sender
	 * @since 3.1.0-a1
	 * @changed 3.1.6-RC1		Added user_info into event
	 * @changed 3.2.2-RC1		Deprecated
	 * @deprecated 4.0.0			Event name is misspelled and is replaced with new event with correct name
	 */
	$vars = array(
		'id',
		'mode',
		'folder_id',
		'msg_id',
		'folder',
		'message_row',
		'cp_row',
		'msg_data',
		'user_info',
	);
	extract($phpbb_dispatcher->trigger_event('core.ucp_pm_view_messsage', compact($vars)));

	/**
	 * Modify pm and sender data before it is assigned to the template
	 *
	 * @event core.ucp_pm_view_message
	 * @var	mixed	id			Active module category (can be int or string)
	 * @var	string	mode		Active module
	 * @var	int		folder_id	ID of the folder the message is in
	 * @var	int		msg_id		ID of the private message
	 * @var	array	folder		Array with data of user's message folders
	 * @var	array	message_row	Array with message data
	 * @var	array	cp_row		Array with senders custom profile field data
	 * @var	array	msg_data	Template array with message data
	 * @var array	user_info	User data of the sender
	 * @since 3.2.2-RC1
	 */
	$vars = array(
		'id',
		'mode',
		'folder_id',
		'msg_id',
		'folder',
		'message_row',
		'cp_row',
		'msg_data',
		'user_info',
	);
	extract($phpbb_dispatcher->trigger_event('core.ucp_pm_view_message', compact($vars)));

	$template->assign_vars($msg_data);

	$contact_fields = array(
		array(
			'ID'		=> 'pm',
			'NAME'		=> $user->lang['SEND_PRIVATE_MESSAGE'],
			'U_CONTACT' => $u_pm,
		),
		array(
			'ID'		=> 'email',
			'NAME'		=> $user->lang['SEND_EMAIL'],
			'U_CONTACT'	=> $user_info['email'],
		),
		array(
			'ID'		=> 'jabber',
			'NAME'		=> $user->lang['JABBER'],
			'U_CONTACT'	=> $u_jabber,
		),
	);

	foreach ($contact_fields as $field)
	{
		if ($field['U_CONTACT'])
		{
			$template->assign_block_vars('contact', $field);
		}
	}

	// Display the custom profile fields
	if (!empty($cp_row['row']))
	{
		$template->assign_vars($cp_row['row']);

		foreach ($cp_row['blockrow'] as $cp_block_row)
		{
			$template->assign_block_vars('custom_fields', $cp_block_row);

			if ($cp_block_row['S_PROFILE_CONTACT'])
			{
				$template->assign_block_vars('contact', array(
					'ID'		=> $cp_block_row['PROFILE_FIELD_IDENT'],
					'NAME'		=> $cp_block_row['PROFILE_FIELD_NAME'],
					'U_CONTACT'	=> $cp_block_row['PROFILE_FIELD_CONTACT'],
				));
			}
		}
	}

	// Display not already displayed Attachments for this post, we already parsed them. ;)
	if (isset($attachments) && count($attachments))
	{
		foreach ($attachments as $attachment)
		{
			$template->assign_block_vars('attachment', array(
					'DISPLAY_ATTACHMENT'	=> $attachment)
			);
		}
	}

	if (!isset($_REQUEST['view']) || $request->variable('view', '') != 'print')
	{
		// Message History
		if (message_history($msg_id, $user->data['user_id'], $message_row, $folder))
		{
			$template->assign_var('S_DISPLAY_HISTORY', true);
		}
	}
}

/**
 * Get user information (only for message display)
 */
function get_user_information($user_id, $user_row)
{
	global $db, $auth, $user;
	global $phpbb_root_path, $phpEx, $config;

	if (!$user_id)
	{
		return array();
	}

	if (empty($user_row))
	{
		$sql = 'SELECT *
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $user_id;
		$result = $db->sql_query($sql);
		$user_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
	}

	// Some standard values
	$user_row['online'] = false;
	$user_row['rank_title'] = $user_row['rank_image'] = $user_row['rank_image_src'] = $user_row['email'] = '';

	// Generate online information for user
	if ($config['load_onlinetrack'])
	{
		$sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
			FROM ' . SESSIONS_TABLE . "
			WHERE session_user_id = $user_id
			GROUP BY session_user_id";
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$update_time = $config['load_online_time'] * 60;
		if ($row)
		{
			$user_row['online'] = (time() - $update_time < $row['online_time'] && ($row['viewonline'] || $auth->acl_get('u_viewonline'))) ? true : false;
		}
	}

	$user_row['avatar'] = ($user->optionget('viewavatars')) ? phpbb_get_user_avatar($user_row) : '';

	if (!function_exists('phpbb_get_user_rank'))
	{
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	}

	$user_rank_data = phpbb_get_user_rank($user_row, $user_row['user_posts']);
	$user_row['rank_title'] = $user_rank_data['title'];
	$user_row['rank_image'] = $user_rank_data['img'];
	$user_row['rank_image_src'] = $user_rank_data['img_src'];

	if ((!empty($user_row['user_allow_viewemail']) && $auth->acl_get('u_sendemail')) || $auth->acl_get('a_email'))
	{
		$user_row['email'] = ($config['board_email_form'] && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=email&amp;u=$user_id") : ((($config['board_hide_emails'] && !$auth->acl_get('a_email')) || empty($user_row['user_email'])) ? '' : 'mailto:' . $user_row['user_email']);
	}

	return $user_row;
}

