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
 * Parse cfg file
 * @param string $filename
 * @param bool|array $lines
 * @return array
 *
 * @deprecated 4.0.0-a1 (To be removed: 5.0.0)
 */
function parse_cfg_file($filename, $lines = false)
{
	$parsed_items = array();

	if ($lines === false)
	{
		$lines = file($filename);
	}

	foreach ($lines as $line)
	{
		$line = trim($line);

		if (!$line || $line[0] == '#' || ($delim_pos = strpos($line, '=')) === false)
		{
			continue;
		}

		// Determine first occurrence, since in values the equal sign is allowed
		$key = htmlspecialchars(strtolower(trim(substr($line, 0, $delim_pos))), ENT_COMPAT);
		$value = trim(substr($line, $delim_pos + 1));

		if (in_array($value, array('off', 'false', '0')))
		{
			$value = false;
		}
		else if (in_array($value, array('on', 'true', '1')))
		{
			$value = true;
		}
		else if (!trim($value))
		{
			$value = '';
		}
		else if (($value[0] == "'" && $value[strlen($value) - 1] == "'") || ($value[0] == '"' && $value[strlen($value) - 1] == '"'))
		{
			$value = htmlspecialchars(substr($value, 1, strlen($value) - 2), ENT_COMPAT);
		}
		else
		{
			$value = htmlspecialchars($value, ENT_COMPAT);
		}

		$parsed_items[$key] = $value;
	}

	if (isset($parsed_items['parent']) && isset($parsed_items['name']) && $parsed_items['parent'] == $parsed_items['name'])
	{
		unset($parsed_items['parent']);
	}

	return $parsed_items;
}

/**
 * Marks a topic/forum as read
 * Marks a topic as posted to
 *
 * @param string $mode (all, topics, topic, post)
 * @param int|bool $forum_id Used in all, topics, and topic mode
 * @param int|bool $topic_id Used in topic and post mode
 * @param int $post_time 0 means current time(), otherwise to set a specific mark time
 * @param int $user_id can only be used with $mode == 'post'
 * @deprecated 4.0.0-a2 (To be removed: 5.0.0)
 */
function markread($mode, $forum_id = false, $topic_id = false, $post_time = 0, $user_id = 0)
{
	global $db, $user, $config;
	global $request, $phpbb_container, $phpbb_dispatcher;

	$post_time = ($post_time === 0 || $post_time > time()) ? time() : (int) $post_time;

	$should_markread = true;

	/**
	 * This event is used for performing actions directly before marking forums,
	 * topics or posts as read.
	 *
	 * It is also possible to prevent the marking. For that, the $should_markread parameter
	 * should be set to FALSE.
	 *
	 * @event core.markread_before
	 * @var	string	mode				Variable containing marking mode value
	 * @var	mixed	forum_id			Variable containing forum id, or false
	 * @var	mixed	topic_id			Variable containing topic id, or false
	 * @var	int		post_time			Variable containing post time
	 * @var	int		user_id				Variable containing the user id
	 * @var	bool	should_markread		Flag indicating if the markread should be done or not.
	 * @since 3.1.4-RC1
	 * @deprecated 4.0.0-a2 (To be removed: 5.0.0)
	 */
	$vars = array(
		'mode',
		'forum_id',
		'topic_id',
		'post_time',
		'user_id',
		'should_markread',
	);
	extract($phpbb_dispatcher->trigger_event('core.markread_before', compact($vars)));

	if (!$should_markread)
	{
		return;
	}

	if ($mode == 'all')
	{
		if (empty($forum_id))
		{
			// Mark all forums read (index page)
			/* @var $phpbb_notifications \phpbb\notification\manager */
			$phpbb_notifications = $phpbb_container->get('notification_manager');

			// Mark all topic notifications read for this user
			$phpbb_notifications->mark_notifications(array(
				'notification.type.topic',
				'notification.type.mention',
				'notification.type.quote',
				'notification.type.bookmark',
				'notification.type.post',
				'notification.type.approve_topic',
				'notification.type.approve_post',
				'notification.type.forum',
			), false, $user->data['user_id'], $post_time);

			if ($config['load_db_lastread'] && $user->data['is_registered'])
			{
				// Mark all forums read (index page)
				$tables = array(TOPICS_TRACK_TABLE, FORUMS_TRACK_TABLE);
				foreach ($tables as $table)
				{
					$sql = 'DELETE FROM ' . $table . "
						WHERE user_id = {$user->data['user_id']}
							AND mark_time < $post_time";
					$db->sql_query($sql);
				}

				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_lastmark = $post_time
					WHERE user_id = {$user->data['user_id']}
						AND user_lastmark < $post_time";
				$db->sql_query($sql);
			}
			else if ($config['load_anon_lastread'] || $user->data['is_registered'])
			{
				$tracking_topics = $request->variable($config['cookie_name'] . '_track', '', true, \phpbb\request\request_interface::COOKIE);
				$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();

				unset($tracking_topics['tf']);
				unset($tracking_topics['t']);
				unset($tracking_topics['f']);
				$tracking_topics['l'] = base_convert($post_time - $config['board_startdate'], 10, 36);

				$user->set_cookie('track', tracking_serialize($tracking_topics), $post_time + 31536000);
				$request->overwrite($config['cookie_name'] . '_track', tracking_serialize($tracking_topics), \phpbb\request\request_interface::COOKIE);

				unset($tracking_topics);

				if ($user->data['is_registered'])
				{
					$sql = 'UPDATE ' . USERS_TABLE . "
						SET user_lastmark = $post_time
						WHERE user_id = {$user->data['user_id']}
							AND user_lastmark < $post_time";
					$db->sql_query($sql);
				}
			}
		}
	}
	else if ($mode == 'topics')
	{
		// Mark all topics in forums read
		if (!is_array($forum_id))
		{
			$forum_id = array($forum_id);
		}
		else
		{
			$forum_id = array_unique($forum_id);
		}

		/* @var $phpbb_notifications \phpbb\notification\manager */
		$phpbb_notifications = $phpbb_container->get('notification_manager');

		$phpbb_notifications->mark_notifications_by_parent(array(
			'notification.type.topic',
			'notification.type.approve_topic',
		), $forum_id, $user->data['user_id'], $post_time);

		// Mark all post/quote notifications read for this user in this forum
		$topic_ids = array();
		$sql = 'SELECT topic_id
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $db->sql_in_set('forum_id', $forum_id);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$topic_ids[] = $row['topic_id'];
		}
		$db->sql_freeresult($result);

		$phpbb_notifications->mark_notifications_by_parent(array(
			'notification.type.mention',
			'notification.type.quote',
			'notification.type.bookmark',
			'notification.type.post',
			'notification.type.approve_post',
			'notification.type.forum',
		), $topic_ids, $user->data['user_id'], $post_time);

		// Add 0 to forums array to mark global announcements correctly
		// $forum_id[] = 0;

		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			$sql = 'DELETE FROM ' . TOPICS_TRACK_TABLE . "
				WHERE user_id = {$user->data['user_id']}
					AND mark_time < $post_time
					AND " . $db->sql_in_set('forum_id', $forum_id);
			$db->sql_query($sql);

			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TRACK_TABLE . "
				WHERE user_id = {$user->data['user_id']}
					AND " . $db->sql_in_set('forum_id', $forum_id);
			$result = $db->sql_query($sql);

			$sql_update = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$sql_update[] = (int) $row['forum_id'];
			}
			$db->sql_freeresult($result);

			if (count($sql_update))
			{
				$sql = 'UPDATE ' . FORUMS_TRACK_TABLE . "
					SET mark_time = $post_time
					WHERE user_id = {$user->data['user_id']}
						AND mark_time < $post_time
						AND " . $db->sql_in_set('forum_id', $sql_update);
				$db->sql_query($sql);
			}

			if ($sql_insert = array_diff($forum_id, $sql_update))
			{
				$sql_ary = array();
				foreach ($sql_insert as $f_id)
				{
					$sql_ary[] = array(
						'user_id'	=> (int) $user->data['user_id'],
						'forum_id'	=> (int) $f_id,
						'mark_time'	=> $post_time,
					);
				}

				$db->sql_multi_insert(FORUMS_TRACK_TABLE, $sql_ary);
			}
		}
		else if ($config['load_anon_lastread'] || $user->data['is_registered'])
		{
			$tracking = $request->variable($config['cookie_name'] . '_track', '', true, \phpbb\request\request_interface::COOKIE);
			$tracking = ($tracking) ? tracking_unserialize($tracking) : array();

			foreach ($forum_id as $f_id)
			{
				$topic_ids36 = (isset($tracking['tf'][$f_id])) ? $tracking['tf'][$f_id] : array();

				if (isset($tracking['tf'][$f_id]))
				{
					unset($tracking['tf'][$f_id]);
				}

				foreach ($topic_ids36 as $topic_id36)
				{
					unset($tracking['t'][$topic_id36]);
				}

				if (isset($tracking['f'][$f_id]))
				{
					unset($tracking['f'][$f_id]);
				}

				$tracking['f'][$f_id] = base_convert($post_time - $config['board_startdate'], 10, 36);
			}

			if (isset($tracking['tf']) && empty($tracking['tf']))
			{
				unset($tracking['tf']);
			}

			$user->set_cookie('track', tracking_serialize($tracking), $post_time + 31536000);
			$request->overwrite($config['cookie_name'] . '_track', tracking_serialize($tracking), \phpbb\request\request_interface::COOKIE);

			unset($tracking);
		}
	}
	else if ($mode == 'topic')
	{
		if ($topic_id === false || $forum_id === false)
		{
			return;
		}

		/* @var $phpbb_notifications \phpbb\notification\manager */
		$phpbb_notifications = $phpbb_container->get('notification_manager');

		// Mark post notifications read for this user in this topic
		$phpbb_notifications->mark_notifications(array(
			'notification.type.topic',
			'notification.type.approve_topic',
		), $topic_id, $user->data['user_id'], $post_time);

		$phpbb_notifications->mark_notifications_by_parent(array(
			'notification.type.mention',
			'notification.type.quote',
			'notification.type.bookmark',
			'notification.type.post',
			'notification.type.approve_post',
			'notification.type.forum',
		), $topic_id, $user->data['user_id'], $post_time);

		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			$sql = 'UPDATE ' . TOPICS_TRACK_TABLE . "
				SET mark_time = $post_time
				WHERE user_id = {$user->data['user_id']}
					AND mark_time < $post_time
					AND topic_id = $topic_id";
			$db->sql_query($sql);

			// insert row
			if (!$db->sql_affectedrows())
			{
				$db->sql_return_on_error(true);

				$sql_ary = array(
					'user_id'		=> (int) $user->data['user_id'],
					'topic_id'		=> (int) $topic_id,
					'forum_id'		=> (int) $forum_id,
					'mark_time'		=> $post_time,
				);

				$db->sql_query('INSERT INTO ' . TOPICS_TRACK_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

				$db->sql_return_on_error(false);
			}
		}
		else if ($config['load_anon_lastread'] || $user->data['is_registered'])
		{
			$tracking = $request->variable($config['cookie_name'] . '_track', '', true, \phpbb\request\request_interface::COOKIE);
			$tracking = ($tracking) ? tracking_unserialize($tracking) : array();

			$topic_id36 = base_convert($topic_id, 10, 36);

			if (!isset($tracking['t'][$topic_id36]))
			{
				$tracking['tf'][$forum_id][$topic_id36] = true;
			}

			$tracking['t'][$topic_id36] = base_convert($post_time - (int) $config['board_startdate'], 10, 36);

			// If the cookie grows larger than 10000 characters we will remove the smallest value
			// This can result in old topics being unread - but most of the time it should be accurate...
			if (strlen($request->variable($config['cookie_name'] . '_track', '', true, \phpbb\request\request_interface::COOKIE)) > 10000)
			{
				//echo 'Cookie grown too large' . print_r($tracking, true);

				// We get the ten most minimum stored time offsets and its associated topic ids
				$time_keys = array();
				for ($i = 0; $i < 10 && count($tracking['t']); $i++)
				{
					$min_value = min($tracking['t']);
					$m_tkey = array_search($min_value, $tracking['t']);
					unset($tracking['t'][$m_tkey]);

					$time_keys[$m_tkey] = $min_value;
				}

				// Now remove the topic ids from the array...
				foreach ($tracking['tf'] as $f_id => $topic_id_ary)
				{
					foreach ($time_keys as $m_tkey => $min_value)
					{
						if (isset($topic_id_ary[$m_tkey]))
						{
							$tracking['f'][$f_id] = $min_value;
							unset($tracking['tf'][$f_id][$m_tkey]);
						}
					}
				}

				if ($user->data['is_registered'])
				{
					$user->data['user_lastmark'] = intval(base_convert(max($time_keys) + $config['board_startdate'], 36, 10));

					$sql = 'UPDATE ' . USERS_TABLE . "
						SET user_lastmark = $post_time
						WHERE user_id = {$user->data['user_id']}
							AND mark_time < $post_time";
					$db->sql_query($sql);
				}
				else
				{
					$tracking['l'] = max($time_keys);
				}
			}

			$user->set_cookie('track', tracking_serialize($tracking), $post_time + 31536000);
			$request->overwrite($config['cookie_name'] . '_track', tracking_serialize($tracking), \phpbb\request\request_interface::COOKIE);
		}
	}
	else if ($mode == 'post')
	{
		if ($topic_id === false)
		{
			return;
		}

		$use_user_id = (!$user_id) ? $user->data['user_id'] : $user_id;

		if ($config['load_db_track'] && $use_user_id != ANONYMOUS)
		{
			$db->sql_return_on_error(true);

			$sql_ary = array(
				'user_id'		=> (int) $use_user_id,
				'topic_id'		=> (int) $topic_id,
				'topic_posted'	=> 1,
			);

			$db->sql_query('INSERT INTO ' . TOPICS_POSTED_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

			$db->sql_return_on_error(false);
		}
	}

	/**
	 * This event is used for performing actions directly after forums,
	 * topics or posts have been marked as read.
	 *
	 * @event core.markread_after
	 * @var	string		mode				Variable containing marking mode value
	 * @var	mixed		forum_id			Variable containing forum id, or false
	 * @var	mixed		topic_id			Variable containing topic id, or false
	 * @var	int			post_time			Variable containing post time
	 * @var	int			user_id				Variable containing the user id
	 * @since 3.2.6-RC1
	 * @deprecated 4.0.0-a2 (To be removed: 5.0.0)
	 */
	$vars = array(
		'mode',
		'forum_id',
		'topic_id',
		'post_time',
		'user_id',
	);
	extract($phpbb_dispatcher->trigger_event('core.markread_after', compact($vars)));
}
