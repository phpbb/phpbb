<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : mcp_queue.php
// STARTED   : Mon Sep 02, 2003
// COPYRIGHT : © 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

class mcp_queue extends module
{

	function mcp_queue($id, $mode, $url)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx, $SID;

		$forum_id = request_var('f', 0);
		$start = request_var('start', 0);

		switch ($mode)
		{
			case 'unapproved_topics':
			case 'unapproved_posts':

				$forum_info = array();

				if (!$forum_id)
				{
					if (!$forum_list = implode(', ', get_forum_list('m_approve')))
					{
						trigger_error('NOT_MODERATOR');
					}

					$sql = 'SELECT SUM(forum_topics) as sum_forum_topics 
						FROM ' . FORUMS_TABLE . "
						WHERE forum_id IN ($forum_list)";
					$result = $db->sql_query($sql);
					$forum_info['forum_topics'] = (int) $db->sql_fetchfield('sum_forum_topics', 0, $result);
					$db->sql_freeresult($result);
				}
				else
				{
					$forum_info = get_forum_data(array($forum_id), 'm_approve');

					if (!sizeof($forum_info))
					{
						trigger_error('NOT_MODERATOR');
					}

					$forum_info = $forum_info[$forum_id];
				}

				mcp_sorting($mode, $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $forum_id);
				$forum_topics = ($total == -1) ? $forum_info['forum_topics'] : $total;
				$limit_time_sql = ($sort_days) ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';

				if ($mode == 'unapproved_posts')
				{
					$sql = 'SELECT p.post_id
						FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t' . (($sort_order_sql{0} == 'u') ? ', ' . USERS_TABLE . ' u' : '') . "
						WHERE p.forum_id IN ($forum_list)
							AND p.post_approved = 0
							" . (($sort_order_sql{0} == 'u') ? 'AND u.user_id = p.poster_id' : '') . "
							AND t.topic_id = p.topic_id
							AND t.topic_first_post_id <> p.post_id
						ORDER BY $sort_order_sql";
					$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);

					$i = 0;
					$post_ids = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$post_ids[] = $row['post_id'];
						$row_num[$row['post_id']] = $i++;
					}

					if (sizeof($post_ids))
					{
						$sql = 'SELECT f.forum_id, f.forum_name, t.topic_id, t.topic_title, p.post_id, p.post_username, p.poster_id, p.post_time, u.username
							FROM ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . " u
							WHERE p.post_id IN (" . implode(', ', $post_ids) . ")
								AND t.topic_id = p.topic_id
								AND f.forum_id = p.forum_id
								AND u.user_id = p.poster_id";

						$result = $db->sql_query($sql);
						$post_data = $rowset = array();
						while ($row = $db->sql_fetchrow($result))
						{
							$post_data[$row['post_id']] = $row;
						}
						$db->sql_freeresult($result);

						foreach ($post_ids as $post_id)
						{
							$rowset[] = $post_data[$post_id];
						}
						unset($post_data, $post_ids);
					}
					else
					{
						$rowset = array();
					}
				}
				else
				{
					$sql = 'SELECT f.forum_id, f.forum_name, t.topic_id, t.topic_title, t.topic_time AS post_time, t.topic_poster AS poster_id, t.topic_first_post_id AS post_id, t.topic_first_poster_name AS username
						FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
						WHERE t.topic_approved = 0
							AND t.forum_id IN ($forum_list)
							AND f.forum_id = t.forum_id
						ORDER BY $sort_order_sql";
					$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);

					$rowset = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$rowset[] = $row;
					}
					$db->sql_freeresult($result);
				}

				foreach ($rowset as $row)
				{
					if ($row['poster_id'] == ANONYMOUS)
					{
						$poster = (!empty($row['post_username'])) ? $row['post_username'] : $user->lang['GUEST'];
					}
					else
					{
						$poster = '<a href="memberlist.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['poster_id'] . '">' . $row['username'] . '</a>';
					}

					$s_checkbox = ($mode == 'unapproved_posts') ? '<input type="checkbox" name="post_id_list[]" value="' . $row['post_id'] . '" />' : '<input type="checkbox" name="topic_id_list[]" value="' . $row['topic_id'] . '" />';

					$template->assign_block_vars('postrow', array(
						'U_VIEWFORUM'	=>	"viewforum.$phpEx$SID&amp;f=" . $row['forum_id'],
						// Q: Why accessing the topic by a post_id instead of its topic_id?
						// A: To prevent the post from being hidden because of low karma or wrong encoding
						'U_VIEWTOPIC'	=>	"viewtopic.$phpEx$SID&amp;f=" . $row['forum_id'] . '&amp;p=' . $row['post_id'] . (($mode == 'unapproved_posts') ? '#' . $row['post_id'] : ''),

						'FORUM_NAME'	=>	$row['forum_name'],
						'TOPIC_TITLE'	=>	$row['topic_title'],
						'POSTER'		=>	$poster,
						'POST_TIME'		=>	$user->format_date($row['post_time']),
						'S_CHECKBOX'	=>	$s_checkbox)
					);
				}
				unset($rowset);

				// Now display the page
				$template->assign_vars(array(
					'L_DISPLAY_ITEMS'		=>	($mode == 'unapproved_posts') ? $user->lang['DISPLAY_POSTS'] : $user->lang['DISPLAY_TOPICS'])
				);

				$this->display($user->lang['MCP_QUEUE'], 'mcp_queue.html');
				break;
		}
	}

	function install()
	{
	}

	function uninstall()
	{
	}

	function module()
	{
		$details = array(
			'name'			=> 'MCP - Queue',
			'description'	=> 'Module for management of items waiting for approval', 
			'filename'		=> 'queue',
			'version'		=> '0.1.0', 
			'phpbbversion'	=> '2.2.0'
		);
		return $details;
	}
}

?>