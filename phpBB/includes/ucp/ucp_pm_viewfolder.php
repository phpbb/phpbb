<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : viewfolder.php
// STARTED   : Sun Apr 11, 2004
// COPYRIGHT : © 2004 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// * Called from ucp_pm with mode == 'view_messages' && action == 'view_folder'

function view_folder($id, $mode, $folder_id, $folder, $type)
{
	global $phpbb_root_path, $phpEx, $SID, $user, $template, $auth, $config, $db;
	
	$user->add_lang('viewforum');
	
	// Grab icons
	$icons = array();
	obtain_icons($icons);

	$color_rows = array('marked', 'replied', 'message_reported', 'friend', 'foe');
	
	foreach ($color_rows as $var)
	{
		$template->assign_block_vars('pm_colour_info', array(
			'IMG'	=> $user->img("pm_{$var}", ''),
			'CLASS' => "pm_{$var}_colour",
			'LANG'	=> $user->lang[strtoupper($var) . '_MESSAGE'])
		);
	}

	$mark_options = array('mark_important', 'delete_marked');

	$s_mark_options = '';
	foreach ($mark_options as $mark_option)
	{
		$s_mark_options .= '<option value="' . $mark_option . '">' . $user->lang[strtoupper($mark_option)] . '</option>';
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
		'S_UNREAD'		=> ($type == 'unread'),
		'S_MARK_OPTIONS'=> $s_mark_options)
	);

	$folder_info = get_pm_from($folder_id, $folder, $user->data['user_id'], "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id", $type);

	// Okay, lets dump out the page ...
	if (sizeof($folder_info['pm_list']))
	{
		// Build Recipient List if in outbox/sentbox - max two additional queries
		$recipient_list = $address_list = $address = array();
		if ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX)
		{
			
			foreach ($folder_info['rowset'] as $message_id => $row)
			{
				$address[$message_id] = rebuild_header(array('to' => $row['to_address'], 'bcc' => $row['bcc_address']));
				foreach (array('u', 'g') as $save)
				{
					if (isset($address[$message_id][$save]) && sizeof($address[$message_id][$save]))
					{
						foreach (array_keys($address[$message_id][$save]) as $ug_id)
						{
							$recipient_list[$save][$ug_id] = array('name' => $user->lang['NA'], 'colour' => '');
						}
					}
				}
			}
		
			foreach (array('u', 'g') as $ug_type)
			{
				if (isset($recipient_list[$ug_type]) && sizeof($recipient_list[$ug_type]))
				{
					$sql = ($ug_type == 'u') ? 'SELECT user_id as id, username as name, user_colour as colour FROM ' . USERS_TABLE . ' WHERE user_id' : 'SELECT group_id as id, group_name as name, group_colour as colour FROM ' . GROUPS_TABLE . ' WHERE group_id';
					$sql .= ' IN (' . implode(', ', array_keys($recipient_list[$ug_type])) . ')';
	
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$recipient_list[$ug_type][$row['id']] = array('name' => $row['name'], 'colour' => $row['colour']);
					}
					$db->sql_freeresult($result);
				}
			}		

			foreach ($address as $message_id => $adr_ary)
			{
				foreach ($adr_ary as $type => $id_ary)
				{
					foreach ($id_ary as $ug_id => $_id)
					{
						$address_list[$message_id][] = (($type == 'u') ? "<a href=\"{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=$ug_id\">" : "<a href=\"{$phpbb_root_path}groupcp.$phpEx$SID&amp;g=$ug_id\">") . (($recipient_list[$type][$ug_id]['colour']) ? '<span style="color:#' . $recipient_list[$type][$ug_id]['colour'] . '">' : '<span>') . $recipient_list[$type][$ug_id]['name'] . '</span></a>';
					}
				}
			}
			
			unset($recipient_list, $address);
		}

		$url = "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id";

		foreach ($folder_info['pm_list'] as $message_id)
		{
			$row =& $folder_info['rowset'][$message_id];

			$folder_img = ($row['unread']) ? 'folder_new' : 'folder';
			$folder_alt = ($row['unread']) ? 'NEW_MESSAGES' : 'NO_NEW_MESSAGES';

			// Generate all URIs ...
			$message_author = "<a href=\"{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['author_id'] . '">' . $row['username'] . '</a>';
			$view_message_url = "$url&amp;f=$folder_id&amp;p=$message_id";

			$row_indicator = '';
			foreach ($color_rows as $var)
			{
				if (($var != 'friend' && $var != 'foe' && $row[$var])
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
	
				'FOLDER_ID' 		=> $folder_id,
				'MESSAGE_ID'		=> $message_id,
				'MESSAGE_AUTHOR'	=> $message_author,
				'SENT_TIME'		 	=> $user->format_date($row['message_time'], $config['board_timezone']),
				'SUBJECT'			=> censor_text($row['message_subject']),
				'FOLDER'			=> (isset($folder[$row['folder_id']])) ? $folder[$row['folder_id']]['folder_name'] : '',
				'U_FOLDER'			=> (isset($folder[$row['folder_id']])) ? "$url&amp;folder=" . $row['folder_id'] : '',
				'PM_ICON_IMG'		=> (!empty($icons[$row['icon_id']])) ? '<img src="' . $config['icons_path'] . '/' . $icons[$row['icon_id']]['img'] . '" width="' . $icons[$row['icon_id']]['width'] . '" height="' . $icons[$row['icon_id']]['height'] . '" alt="" title="" />' : '',
				'FOLDER_IMG'		=> $user->img($folder_img, $folder_alt),
				'PM_IMG'	 		=> ($row_indicator) ? $user->img('pm_' . $row_indicator, '') : '',
				'ATTACH_ICON_IMG'	=> ($auth->acl_get('u_download') && $row['message_attachment'] && $config['pm_attachments'] && $config['auth_download_pm']) ? $user->img('icon_attach', sprintf($user->lang['TOTAL_ATTACHMENTS'], $row['message_attachment'])) : '',

				'S_PM_REPORTED'		=> (!empty($row['message_reported']) && $auth->acl_get('m_')) ? true : false,

				'U_VIEW_PM'			=> $view_message_url,
				'RECIPIENTS'		=> ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX) ? implode(', ', $address_list[$message_id]) : '',
				'U_MCP_REPORT'		=> "{$phpbb_root_path}mcp.$phpEx?sid={$user->session_id}&amp;mode=reports&amp;pm=$message_id")
//				'U_MCP_QUEUE'		=> "mcp.$phpEx?sid={$user->session_id}&amp;mode=mod_queue&amp;t=$topic_id")
			);

			$i++;

			unset($folder_info['rowset'][$message_id]);
		}
	
		$template->assign_vars(array(
			'S_SHOW_RECIPIENTS'	=> ($folder_id == PRIVMSGS_OUTBOX || $folder_id == PRIVMSGS_SENTBOX) ? true : false,
			'S_SHOW_COLOUR_LEGEND'	=> true)
		);
	}
}

// Get PM's in folder x from user x
// Get PM's in all folders from user x with type of x (unread, new)
function get_pm_from($folder_id, $folder, $user_id, $url, $type = 'folder')
{
	global $user, $db, $template, $config, $auth, $_POST;

	$start		= request_var('start', 0);

	$sort_days = (isset($_REQUEST['st'])) ? max(intval($_REQUEST['st']), 0) : ((!empty($user->data['user_show_days'])) ? $user->data['user_show_days'] : 0);
	$sort_key = (!empty($_REQUEST['sk'])) ? htmlspecialchars($_REQUEST['sk']) : ((!empty($user->data['user_sortby_type'])) ? $user->data['user_sortby_type'] : 't');
	$sort_dir = (!empty($_REQUEST['sd'])) ? htmlspecialchars($_REQUEST['sd']) : ((!empty($user->data['user_sortby_dir'])) ? $user->data['user_sortby_dir'] : 'd');

	// PM ordering options
	$limit_days = array(0 => $user->lang['ALL_MESSAGES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
	$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);
	$sort_by_sql = array('a' => 'u.username', 't' => 'p.message_time', 's' => 'p.subject');

	$sort_key = (!in_array($sort_key, array('a', 't', 's'))) ? 't' : $sort_key;

	$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

	if ($type != 'folder')
	{
		$folder_sql = ($type == 'unread') ? 't.unread = 1' : 't.new = 1';
		$folder_id = PRIVMSGS_INBOX;
	}
	else
	{
		$folder_sql = 't.folder_id = ' . (int) $folder_id;
	}

	// Limit pms to certain time frame, obtain correct pm count
	if ($sort_days)
	{
		$min_post_time = time() - ($sort_days * 86400);

		$sql = 'SELECT COUNT(t.msg_id) AS pm_count
			FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . " p
			WHERE $folder_sql
				AND t.user_id = $user_id
				AND t.msg_id = p.msg_id
				AND p.message_time >= $min_post_time";
		$result = $db->sql_query_limit($sql, 1);

		if (isset($_POST['sort']))
		{
			$start = 0;
		}

		$pm_count = ($row = $db->sql_fetchrow($result)) ? $row['pm_count'] : 0;
		$db->sql_freeresult($result);

		$sql_limit_time = "AND p.message_time >= $min_post_time";
	}
	else
	{
		if ($type == 'folder')
		{
			$pm_count = $folder[$folder_id]['num_messages'];
		}
		else
		{
			if (in_array($folder_id, array(PRIVMSGS_INBOX, PRIVMSGS_OUTBOX, PRIVMSGS_SENTBOX)))
			{
				$sql = 'SELECT COUNT(t.msg_id) AS pm_count
					FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . " p
					WHERE $folder_sql
						AND t.user_id = $user_id
						AND t.msg_id = p.msg_id";
			}
			else
			{
				$sql = 'SELECT pm_count 
					FROM ' . PRIVMSGS_FOLDER_TABLE . " 
					WHERE folder_id = $folder_id
						AND user_id = $user_id";
			}
			$result = $db->sql_query_limit($sql, 1);
			$pm_count = ($row = $db->sql_fetchrow($result)) ? $row['pm_count'] : 0;
			$db->sql_freeresult($result);
		}

		$sql_limit_time = '';
	}

	$template->assign_vars(array(
		'PAGINATION'	=> generate_pagination("$url&amp;mode=view_messages&amp;action=view_folder&amp;f=$folder_id&amp;$u_sort_param", $pm_count, $config['topics_per_page'], $start),
		'PAGE_NUMBER'	=> on_page($pm_count, $config['topics_per_page'], $start),
		'TOTAL_MESSAGES'=> (($pm_count == 1) ? $user->lang['VIEW_PM_MESSAGE'] : sprintf($user->lang['VIEW_PM_MESSAGES'], $pm_count)),

		'POST_IMG'		=> (!$auth->acl_get('u_sendpm')) ? $user->img('btn_locked', $post_alt) : $user->img('btn_post_pm', $post_alt),

		'REPORTED_IMG'	=> $user->img('icon_reported', 'MESSAGE_REPORTED'),

		'L_NO_MESSAGES'	=> (!$auth->acl_get('u_sendpm')) ? $user->lang['POST_PM_LOCKED'] : $user->lang['NO_MESSAGES'],

		'S_SELECT_SORT_DIR'		=> $s_sort_dir,
		'S_SELECT_SORT_KEY'		=> $s_sort_key,
		'S_SELECT_SORT_DAYS'	=> $s_limit_days,
		'S_TOPIC_ICONS'			=> ($config['enable_pm_icons']) ? true : false, 

		'U_POST_NEW_TOPIC'	=> ($auth->acl_get('u_sendpm')) ? "$url&amp;mode=compose&amp;action=post" : '', 
		'S_PM_ACTION'		=> "$url&amp;mode=view_messages&amp;action=view_folder&amp;f=$folder_id")
	);

	// Grab all pm data
	$rowset = $pm_list = array();

	// If the user is trying to reach late pages, start searching from the end
	$store_reverse = false;
	$sql_limit = $config['topics_per_page'];
	if ($start > $pm_count / 2)
	{
		$store_reverse = true;

		if ($start + $config['topics_per_page'] > $pm_count)
		{
			$sql_limit = min($config['topics_per_page'], max(1, $pm_count - $start));
		}

		// Select the sort order
		$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'ASC' : 'DESC');
		$sql_start = max(0, $pm_count - $sql_limit - $start);
	}
	else
	{
		// Select the sort order
		$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');
		$sql_start = $start;
	}

	$sql = 'SELECT t.*, p.author_id, p.root_level, p.message_time, p.message_subject, p.icon_id, p.message_reported, p.to_address, p.message_attachment, p.bcc_address, u.username 
		FROM ' . PRIVMSGS_TO_TABLE . ' t, ' . PRIVMSGS_TABLE . ' p, ' . USERS_TABLE . " u
		WHERE t.user_id = $user_id
			AND p.author_id = u.user_id
			AND $folder_sql
			AND t.msg_id = p.msg_id
			$sql_limit_time
		ORDER BY $sql_sort_order";

	$result = $db->sql_query_limit($sql, $sql_limit, $sql_start);

	while($row = $db->sql_fetchrow($result))
	{
		$rowset[$row['msg_id']] = $row;
		$pm_list[] = $row['msg_id'];
	}
	$db->sql_freeresult($result);

	$pm_list = ($store_reverse) ? array_reverse($pm_list) : $pm_list;

	return array(
		'pm_count'	=> $pm_count, 
		'pm_list'	=> $pm_list, 
		'rowset'	=> $rowset
	);
}

?>