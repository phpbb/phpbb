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
* ucp_attachments
* User attachments
*/
class ucp_attachments
{
	var $u_action;

	function main($id, $mode)
	{
		global $template, $user, $db, $config, $phpEx, $phpbb_root_path, $phpbb_container, $request, $auth;

		$start		= $request->variable('start', 0);
		$sort_key	= $request->variable('sk', 'a');
		$sort_dir	= $request->variable('sd', 'a');

		$delete		= (isset($_POST['delete'])) ? true : false;
		$delete_ids	= array_keys($request->variable('attachment', array(0)));

		if ($delete && count($delete_ids))
		{
			// Validate $delete_ids...
			$sql = 'SELECT a.attach_id, p.post_edit_locked, t.topic_status, f.forum_id, f.forum_status
				FROM ' . ATTACHMENTS_TABLE . ' a
				LEFT JOIN ' . POSTS_TABLE . ' p
					ON (a.post_msg_id = p.post_id AND a.in_message = 0)
				LEFT JOIN ' . TOPICS_TABLE . ' t
					ON (t.topic_id = p.topic_id AND a.in_message = 0)
				LEFT JOIN ' . FORUMS_TABLE . ' f
					ON (f.forum_id = t.forum_id AND a.in_message = 0)
				WHERE a.poster_id = ' . $user->data['user_id'] . '
					AND a.is_orphan = 0
					AND ' . $db->sql_in_set('a.attach_id', $delete_ids);
			$result = $db->sql_query($sql);

			$delete_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				if (!$auth->acl_get('m_edit', $row['forum_id']) && ($row['forum_status'] == ITEM_LOCKED || $row['topic_status'] == ITEM_LOCKED || $row['post_edit_locked']))
				{
					continue;
				}

				$delete_ids[] = $row['attach_id'];
			}
			$db->sql_freeresult($result);
		}

		if ($delete && count($delete_ids))
		{
			$s_hidden_fields = array(
				'delete'	=> 1
			);

			foreach ($delete_ids as $attachment_id)
			{
				$s_hidden_fields['attachment'][$attachment_id] = 1;
			}

			if (confirm_box(true))
			{
				/** @var \phpbb\attachment\manager $attachment_manager */
				$attachment_manager = $phpbb_container->get('attachment.manager');
				$attachment_manager->delete('attach', $delete_ids);
				unset($attachment_manager);

				meta_refresh(3, $this->u_action);
				$message = ((count($delete_ids) == 1) ? $user->lang['ATTACHMENT_DELETED'] : $user->lang['ATTACHMENTS_DELETED']) . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				confirm_box(false, (count($delete_ids) == 1) ? 'DELETE_ATTACHMENT' : 'DELETE_ATTACHMENTS', build_hidden_fields($s_hidden_fields));
			}
		}

		// Select box eventually
		$sort_key_text = array('a' => $user->lang['SORT_FILENAME'], 'b' => $user->lang['SORT_COMMENT'], 'c' => $user->lang['SORT_EXTENSION'], 'd' => $user->lang['SORT_SIZE'], 'e' => $user->lang['SORT_DOWNLOADS'], 'f' => $user->lang['SORT_POST_TIME'], 'g' => $user->lang['SORT_TOPIC_TITLE']);
		$sort_key_sql = array('a' => 'a.real_filename', 'b' => 'a.attach_comment', 'c' => 'a.extension', 'd' => 'a.filesize', 'e' => 'a.download_count', 'f' => 'a.filetime', 'g' => 't.topic_title');

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

		if (!isset($sort_key_sql[$sort_key]))
		{
			$sort_key = 'a';
		}

		$order_by = $sort_key_sql[$sort_key] . ' ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');

		$sql = 'SELECT COUNT(attach_id) as num_attachments
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE poster_id = ' . $user->data['user_id'] . '
				AND is_orphan = 0';
		$result = $db->sql_query($sql);
		$num_attachments = $db->sql_fetchfield('num_attachments');
		$db->sql_freeresult($result);

		// Ensure start is a valid value
		/* @var $pagination \phpbb\pagination */
		$pagination = $phpbb_container->get('pagination');
		$start = $pagination->validate_start($start, $config['topics_per_page'], $num_attachments);

		$sql = 'SELECT a.*, t.topic_title, pr.message_subject as message_title, p.post_edit_locked, t.topic_status, f.forum_id, f.forum_status
			FROM ' . ATTACHMENTS_TABLE . ' a
				LEFT JOIN ' . POSTS_TABLE . ' p ON (a.post_msg_id = p.post_id AND a.in_message = 0)
				LEFT JOIN ' . TOPICS_TABLE . ' t ON (a.topic_id = t.topic_id AND a.in_message = 0)
				LEFT JOIN ' . FORUMS_TABLE . ' f ON (f.forum_id = t.forum_id AND a.in_message = 0)
				LEFT JOIN ' . PRIVMSGS_TABLE . ' pr ON (a.post_msg_id = pr.msg_id AND a.in_message = 1)
			WHERE a.poster_id = ' . $user->data['user_id'] . "
				AND a.is_orphan = 0
			ORDER BY $order_by";
		$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);

		$row_count = 0;
		if ($row = $db->sql_fetchrow($result))
		{
			$template->assign_var('S_ATTACHMENT_ROWS', true);

			do
			{
				if ($row['in_message'])
				{
					$view_topic = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=pm&amp;p={$row['post_msg_id']}");
				}
				else
				{
					$view_topic = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "t={$row['topic_id']}&amp;p={$row['post_msg_id']}") . "#p{$row['post_msg_id']}";
				}

				$template->assign_block_vars('attachrow', array(
					'ROW_NUMBER'		=> $row_count + ($start + 1),
					'FILENAME'			=> $row['real_filename'],
					'COMMENT'			=> bbcode_nl2br($row['attach_comment']),
					'EXTENSION'			=> $row['extension'],
					'SIZE'				=> get_formatted_filesize($row['filesize']),
					'DOWNLOAD_COUNT'	=> $row['download_count'],
					'POST_TIME'			=> $user->format_date($row['filetime']),
					'TOPIC_TITLE'		=> ($row['in_message']) ? $row['message_title'] : $row['topic_title'],

					'ATTACH_ID'			=> $row['attach_id'],
					'POST_ID'			=> $row['post_msg_id'],
					'TOPIC_ID'			=> $row['topic_id'],

					'S_IN_MESSAGE'		=> $row['in_message'],
					'S_LOCKED'			=> !$row['in_message'] && !$auth->acl_get('m_edit', $row['forum_id']) && ($row['forum_status'] == ITEM_LOCKED || $row['topic_status'] == ITEM_LOCKED || $row['post_edit_locked']),

					'U_VIEW_ATTACHMENT'	=> append_sid("{$phpbb_root_path}download/file.$phpEx", 'id=' . $row['attach_id']),
					'U_VIEW_TOPIC'		=> $view_topic)
				);

				$row_count++;
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);

		$base_url = $this->u_action . "&amp;sk=$sort_key&amp;sd=$sort_dir";
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $num_attachments, $config['topics_per_page'], $start);

		$template->assign_vars(array(
			'TOTAL_ATTACHMENTS'		=> $num_attachments,
			'NUM_ATTACHMENTS'		=> $user->lang('NUM_ATTACHMENTS', $num_attachments),

			'L_TITLE'				=> $user->lang['UCP_ATTACHMENTS'],

			'U_SORT_FILENAME'		=> $this->u_action . "&amp;sk=a&amp;sd=" . (($sort_key == 'a' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_SORT_FILE_COMMENT'	=> $this->u_action . "&amp;sk=b&amp;sd=" . (($sort_key == 'b' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_SORT_EXTENSION'		=> $this->u_action . "&amp;sk=c&amp;sd=" . (($sort_key == 'c' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_SORT_FILESIZE'		=> $this->u_action . "&amp;sk=d&amp;sd=" . (($sort_key == 'd' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_SORT_DOWNLOADS'		=> $this->u_action . "&amp;sk=e&amp;sd=" . (($sort_key == 'e' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_SORT_POST_TIME'		=> $this->u_action . "&amp;sk=f&amp;sd=" . (($sort_key == 'f' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_SORT_TOPIC_TITLE'	=> $this->u_action . "&amp;sk=g&amp;sd=" . (($sort_key == 'g' && $sort_dir == 'a') ? 'd' : 'a'),

			'S_DISPLAY_MARK_ALL'	=> ($num_attachments) ? true : false,
			'S_DISPLAY_PAGINATION'	=> ($num_attachments) ? true : false,
			'S_UCP_ACTION'			=> $this->u_action,
			'S_SORT_OPTIONS' 		=> $s_sort_key,
			'S_ORDER_SELECT'		=> $s_sort_dir)
		);

		$this->tpl_name = 'ucp_attachments';
		$this->page_title = 'UCP_ATTACHMENTS';
	}
}
