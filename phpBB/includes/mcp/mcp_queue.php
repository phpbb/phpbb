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

/*

  TODO

- order items by forum?
- ability to restrict listing to a certain forum?
- pagination missing

*/

class mcp_queue extends mcp
{
	function init()
	{
		global $db;

		// Validate input
		$this->mcp_init();
	}

	function main($mode)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx, $SID;

		$this->mode = $mode;

		switch ($mode)
		{
			case 'approve':
				//
				// TODO: increment post counts
				//       warn users watching forums/topics
				//

				// Get topic data
				if ($post_id_list = $this->get_post_ids('m_approve'))
				{
					$post_mode = 'unapproved_posts';

					$sql = 'SELECT topic_id
						FROM ' . POSTS_TABLE . '
						WHERE post_id IN (' . implode(', ', $post_id_list) . ')';
					$result = $db->sql_query($sql);
					
					$topic_id_list = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$topic_id_list[] = $row['topic_id'];

						// TODO: log posts being approved? that would be a hell lot of logging
						//add_log('mod', $row['forum_id'], $row['topic_id'], 'LOG_APPROVE_POST', $row['post_id']);
					}

					$msg = (count($post_id_list) == 1) ? $user->lang['POST_APPROVED_SUCCESS'] : $user->lang['POSTS_APPROVED_SUCCESS'];

					// Return to topic
					$redirect_url = "viewtopic.$phpEx$SID&amp;f={$this->forum_id}&amp;p={$this->post_id}#{$this->post_id}";

					if ($this->quickmod)
					{
						meta_refresh(3, $redirect_url);
						$return_topic = '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $redirect_url . '">', '</a>');
					}
					else
					{
						if (count($post_id_list) == 1)
						{
							return_link('RETURN_POST', $this->url . '&amp;mode=post_details');
						}
						return_link('RETURN_TOPIC', $redirect_url);
					}
				}
				elseif ($topic_id_list = $this->get_topic_ids('m_approve'))
				{
					$post_mode = 'unapproved_topics';

					$sql = 'SELECT forum_id, topic_title, topic_first_post_id
						FROM ' . TOPICS_TABLE . '
						WHERE topic_id IN (' . implode(', ', $topic_id_list) . ')';
					$result = $db->sql_query($sql);
					
					$post_id_list = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$post_id_list[] = $row['topic_first_post_id'];
						add_log('mod', $row['forum_id'], $row['topic_id'], 'LOG_APPROVE_TOPIC', $row['topic_title']);
					}

					$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET topic_approved = 1
						WHERE topic_id IN (' . implode(', ', $topic_id_list) . ')';
					$db->sql_query($sql);

					$msg = (count($topic_id_list) == 1) ? $user->lang['TOPIC_APPROVED_SUCCESS'] : $user->lang['TOPICS_APPROVED_SUCCESS'];

					// Return to forum
					$redirect_url = "viewforum.$phpEx$SID&amp;f=" . $this->forum_id;

					if ($this->quickmod)
					{
						meta_refresh(3, $redirect_url);
						$return_topic = '';
					}
					else
					{
						return_link('RETURN_FORUM', $redirect_url);
					}
				}
				else
				{
					trigger_error('NOT_MODERATOR');
				}

				// Update approved flag
				$sql = 'UPDATE ' . POSTS_TABLE . '
					SET post_approved = 1
					WHERE post_id IN (' . implode(', ', $post_id_list) . ')';
				$db->sql_query($sql);

				// Now resync everything
				sync('topic', 'topic_id', $topic_id_list, TRUE, TRUE);

				// Back to... whatever
				if ($this->quickmod)
				{
					trigger_error($msg . $return_topic . '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], "<a href=\"viewforum.$phpEx$SID&amp;f=" . $this->forum_id . '">', '</a>'));
				}
				else
				{
					$template->assign_var('MESSAGE', $msg);
					$this->main($post_mode);
				}
			break;

			case 'unapproved_topics':
			case 'unapproved_posts':
				if (!$forum_list = implode(', ', get_forum_list('m_approve')))
				{
					trigger_error('NOT_MODERATOR');
				}

				$this->mcp_sorting($mode, $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $this->forum_id);
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
					$result = $db->sql_query_limit($sql, $config['topics_per_page'], $this->start);

					$i = 0;
					$post_ids = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$post_ids[] = $row['post_id'];
						$row_num[$row['post_id']] = $i++;
					}

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
					$sql = 'SELECT f.forum_id, f.forum_name, t.topic_id, t.topic_title, t.topic_time AS post_time, t.topic_poster AS poster_id, t.topic_first_post_id AS post_id, t.topic_first_poster_name AS username
						FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
						WHERE t.topic_approved = 0
							AND t.forum_id IN ($forum_list)
							AND f.forum_id = t.forum_id
						ORDER BY $sort_order_sql";
					$result = $db->sql_query_limit($sql, $config['topics_per_page'], $this->start);

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
						'S_CHECKBOX'	=>	$s_checkbox
					));
				}
				unset($rowset);

				// Now display the page
				$template->assign_vars(array(
					'L_DISPLAY_ITEMS'		=>	($mode == 'unapproved_posts') ? $user->lang['DISPLAY_POSTS'] : $user->lang['DISPLAY_POSTS']
				));

				$this->display($user->lang['MCP_QUEUE'], 'mcp_queue.html');
			break;

			default:
				trigger_error('UNKNOWN_MODE');
		}
	}

	// This function simply puts the number of unapproved items in menu titles
	function alter_menu()
	{
		global $db, $user;

		$forum_list = get_forum_list('m_approve');

		$sql = 'SELECT COUNT(*) AS total
			FROM ' . TOPICS_TABLE . '
			WHERE forum_id IN (' . implode(', ', $forum_list) . ')
				AND topic_approved = 0';
		$result = $db->sql_query($sql);
		$total_topics = $db->sql_fetchfield('total', 0, $result);

		$sql = 'SELECT COUNT(*) AS total
			FROM ' . POSTS_TABLE . '
			WHERE forum_id IN (' . implode(', ', $forum_list) . ')
				AND post_approved = 0';
		$result = $db->sql_query($sql);
		$total_posts = $db->sql_fetchfield('total', 0, $result) - $total_topics;

		$this->subs['unapproved_topics']['title'] = sprintf($this->subs['unapproved_topics']['title'], ($total_topics) ? $total_topics : $user->lang['NONE']);
		$this->subs['unapproved_posts']['title'] = sprintf($this->subs['unapproved_posts']['title'], ($total_posts) ? $total_posts : $user->lang['NONE']);
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