<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : ucp_attachments.php
// STARTED   : Mon Nov 03, 2003
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

//
// * Use this for ACP integration - changeable user id
//

class ucp_attachments extends module
{
	function ucp_attachments($id, $mode)
	{
		global $template, $user, $db, $config, $phpEx, $phpbb_root_path, $SID;

		$start	= request_var('start', 0);
		$delete = (isset($_POST['delete'])) ? true : false;
		$confirm = (isset($_POST['confirm'])) ? true : false;
		$delete_ids = isset($_REQUEST['attachment']) ? array_keys(array_map('intval', $_REQUEST['attachment'])) : array();
		
		if ($delete && sizeof($delete_ids))
		{
			$s_hidden_fields = '<input type="hidden" name="delete" value="1" />';
			foreach ($delete_ids as $attachment_id)
			{
				$s_hidden_fields .= '<input type="hidden" name="attachment[' . $attachment_id . ']" value="1" />';
			}

			if (confirm_box(true))
			{
				include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
				delete_attachments('attach', $delete_ids);

				$refresh_url = "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id";
				meta_refresh(3, $refresh_url);
				$message = ((sizeof($delete_ids) == 1) ? $user->lang['ATTACHMENT_DELETED'] : $user->lang['ATTACHMENTS_DELETED']) . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $refresh_url . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				confirm_box(false, (sizeof($delete_ids) == 1) ? 'DELETE_ATTACHMENT' : 'DELETE_ATTACHMENTS', $s_hidden_fields);
			}
		}
		
		$sort_key = request_var('sk', 'a');
		$sort_dir = request_var('sd', 'a');

		// Select box eventually
		$sort_key_text = array('a' => $user->lang['SORT_FILENAME'], 'b' => $user->lang['SORT_COMMENT'], 'c' => $user->lang['SORT_EXTENSION'], 'd' => $user->lang['SORT_SIZE'], 'e' => $user->lang['SORT_DOWNLOADS'], 'f' => $user->lang['SORT_POST_TIME'], 'g' => $user->lang['SORT_TOPIC_TITLE']);
		$sort_key_sql = array('a' => 'a.real_filename', 'b' => 'a.comment', 'c' => 'a.extension', 'd' => 'a.filesize', 'e' => 'a.download_count', 'f' => 'a.filetime', 'g' => 't.topic_title');

		$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);
	
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

		$order_by = $sort_key_sql[$sort_key] . '  ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');
		
		$sql = 'SELECT COUNT(*) as num_attachments
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE poster_id = ' . $user->data['user_id'];
		$result = $db->sql_query_limit($sql, 1);
		$num_attachments = $db->sql_fetchfield('num_attachments', 0, $result);
		$db->sql_freeresult($result);
		
		$sql = 'SELECT a.*, t.topic_title, p.message_subject as message_title
			FROM ' . ATTACHMENTS_TABLE . ' a 
				LEFT JOIN ' . TOPICS_TABLE . ' t ON (a.topic_id = t.topic_id
					AND a.in_message = 0)
				LEFT JOIN ' . PRIVMSGS_TABLE . ' p ON (a.post_msg_id = p.msg_id
					AND a.in_message = 1)
			WHERE a.poster_id = ' . $user->data['user_id'] . "
			ORDER BY $order_by";
		$result = $db->sql_query_limit($sql, $config['posts_per_page'], $start);

		$row_count = 0;
		if ($row = $db->sql_fetchrow($result))
		{
			$template->assign_var('S_ATTACHMENT_ROWS', true);

			do
			{
				if ($row['in_message'])
				{
					$view_topic = "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=pm&amp;p={$row['post_msg_id']}";
				}
				else
				{
					$view_topic = "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;t=" . $row['topic_id'] . '&amp;p=' . $row['post_msg_id'] . '#' . $row['post_msg_id'];
				}

				$template->assign_block_vars('attachrow', array(
					'ROW_NUMBER'		=> $row_count + ($start + 1),
					'FILENAME'			=> $row['real_filename'],
					'COMMENT'			=> str_replace("\n", '<br />', $row['comment']),
					'EXTENSION'			=> $row['extension'],
					'SIZE'				=> ($row['filesize'] >= 1048576) ? ($row['filesize'] >> 20) . ' ' . $user->lang['MB'] : (($row['filesize'] >= 1024) ? ($row['filesize'] >> 10) . ' ' . $user->lang['KB'] : $row['filesize'] . ' ' . $user->lang['BYTES']),
					'DOWNLOAD_COUNT'	=> $row['download_count'],
					'POST_TIME'			=> $user->format_date($row['filetime'], $user->lang['DATE_FORMAT']),
					'TOPIC_TITLE'		=> ($row['in_message']) ? $row['message_title'] : $row['topic_title'],

					'ATTACH_ID'			=> $row['attach_id'],
					'POST_ID'			=> $row['post_msg_id'],
					'TOPIC_ID'			=> $row['topic_id'],
				
					'S_IN_MESSAGE'		=> $row['in_message'],

					'U_VIEW_ATTACHMENT'	=> $phpbb_root_path . 'download.' . $phpEx . $SID . '&amp;id=' . $row['attach_id'],
					'U_VIEW_TOPIC'		=> $view_topic)
				);

				$row_count++;
			} 
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array( 
			'PAGE_NUMBER'			=> on_page($num_attachments, $config['posts_per_page'], $start),
			'PAGINATION'			=> generate_pagination("{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;sk=$sort_key&amp;sd=$sort_dir", $num_attachments, $config['posts_per_page'], $start),
			'TOTAL_ATTACHMENTS'		=> $num_attachments,

			'L_TITLE'				=> $user->lang['UCP_ATTACHMENTS'],

			'U_SORT_FILENAME'		=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;sk=a&amp;sd=" . (($sort_key == 'a' && $sort_dir == 'a') ? 'd' : 'a'), 
			'U_SORT_FILE_COMMENT'	=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;sk=b&amp;sd=" . (($sort_key == 'b' && $sort_dir == 'a') ? 'd' : 'a'), 
			'U_SORT_EXTENSION'		=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;sk=c&amp;sd=" . (($sort_key == 'c' && $sort_dir == 'a') ? 'd' : 'a'), 
			'U_SORT_FILESIZE'		=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;sk=d&amp;sd=" . (($sort_key == 'd' && $sort_dir == 'a') ? 'd' : 'a'), 
			'U_SORT_DOWNLOADS'		=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;sk=e&amp;sd=" . (($sort_key == 'e' && $sort_dir == 'a') ? 'd' : 'a'), 
			'U_SORT_POST_TIME'		=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;sk=f&amp;sd=" . (($sort_key == 'f' && $sort_dir == 'a') ? 'd' : 'a'), 
			'U_SORT_TOPIC_TITLE'	=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;sk=g&amp;sd=" . (($sort_key == 'f' && $sort_dir == 'a') ? 'd' : 'a'), 

			'S_DISPLAY_MARK_ALL'	=> ($num_attachments) ? true : false,
			'S_DISPLAY_PAGINATION'	=> ($num_attachments) ? true : false,
			'S_UCP_ACTION'			=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id",
			'S_SORT_OPTIONS' 		=> $s_sort_key,
			'S_ORDER_SELECT'		=> $s_sort_dir)
		);

		$this->display($user->lang['UCP_ATTACHMENTS'], 'ucp_attachments.html');
	}
}

?>