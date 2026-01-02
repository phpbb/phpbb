<?php

namespace phpbb\notification;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\event\dispatcher;
use phpbb\request\request;
use phpbb\request\request_interface;
use phpbb\user;

class helper
{
	/** @var driver_interface */
	protected $db;

	/** @var config */
	protected $config;

	/** @var dispatcher */
	protected $dispatcher;

	/** @var manager */
	protected $notification_manager;

	/** @var request */
	protected $request;

	/** @var user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param driver_interface $db
	 * @param config $config
	 * @param dispatcher $dispatcher
	 * @param manager $notification_manager
	 * @param request $request
	 * @param user $user
	 */
	public function __construct(driver_interface $db, config $config, dispatcher $dispatcher, manager $notification_manager, request $request, user $user)
	{
		$this->db = $db;
		$this->config = $config;
		$this->dispatcher = $dispatcher;
		$this->notification_manager = $notification_manager;
		$this->request = $request;
		$this->user = $user;
	}

	public function markread_all($forum_id, $post_time): void
	{
		$post_time = ($post_time === 0 || $post_time > time()) ? time() : (int) $post_time;

		if (empty($forum_id))
		{
			// Mark all forums read (index page)
			// Mark all topic notifications read for this user
			$this->notification_manager->mark_notifications([
				'notification.type.topic',
				'notification.type.mention',
				'notification.type.quote',
				'notification.type.bookmark',
				'notification.type.post',
				'notification.type.approve_topic',
				'notification.type.approve_post',
				'notification.type.forum',
			], false, $this->user->data['user_id'], $post_time);

			if ($this->config['load_db_lastread'] && $this->user->data['is_registered'])
			{
				// Mark all forums read (index page)
				$tables = [TOPICS_TRACK_TABLE, FORUMS_TRACK_TABLE];
				foreach ($tables as $table)
				{
					$sql = 'DELETE FROM ' . $table . "
						WHERE user_id = {$this->user->data['user_id']}
							AND mark_time < $post_time";
					$this->db->sql_query($sql);
				}

				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_lastmark = $post_time
					WHERE user_id = {$this->user->data['user_id']}
						AND user_lastmark < $post_time";
				$this->db->sql_query($sql);
			}
			else if ($this->config['load_anon_lastread'] || $this->user->data['is_registered'])
			{
				$tracking_topics = $this->request->variable($this->config['cookie_name'] . '_track', '', true, request_interface::COOKIE);
				$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : [];

				unset($tracking_topics['tf']);
				unset($tracking_topics['t']);
				unset($tracking_topics['f']);
				$tracking_topics['l'] = base_convert($post_time - $this->config['board_startdate'], 10, 36);

				$this->user->set_cookie('track', tracking_serialize($tracking_topics), $post_time + 31536000);
				$this->request->overwrite($this->config['cookie_name'] . '_track', tracking_serialize($tracking_topics), request_interface::COOKIE);

				unset($tracking_topics);

				if ($this->user->data['is_registered'])
				{
					$sql = 'UPDATE ' . USERS_TABLE . "
						SET user_lastmark = $post_time
						WHERE user_id = {$this->user->data['user_id']}
							AND user_lastmark < $post_time";
					$this->db->sql_query($sql);
				}
			}
		}
	}

	public function markread_topics($forum_id, $post_time): void
	{
		$post_time = ($post_time === 0 || $post_time > time()) ? time() : (int) $post_time;

		// Mark all topics in forums read
		if (!is_array($forum_id))
		{
			$forum_id = [$forum_id];
		}
		else
		{
			$forum_id = array_unique($forum_id);
		}

		$this->notification_manager->mark_notifications_by_parent([
			'notification.type.topic',
			'notification.type.approve_topic',
		], $forum_id, $this->user->data['user_id'], $post_time);

		// Mark all post/quote notifications read for this user in this forum
		$topic_ids = [];
		$sql = 'SELECT topic_id
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $this->db->sql_in_set('forum_id', $forum_id);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$topic_ids[] = $row['topic_id'];
		}
		$this->db->sql_freeresult($result);

		$this->notification_manager->mark_notifications_by_parent([
			'notification.type.mention',
			'notification.type.quote',
			'notification.type.bookmark',
			'notification.type.post',
			'notification.type.approve_post',
			'notification.type.forum',
		], $topic_ids, $this->user->data['user_id'], $post_time);

		// Add 0 to forums array to mark global announcements correctly
		// $forum_id[] = 0;

		if ($this->config['load_db_lastread'] && $this->user->data['is_registered'])
		{
			$sql = 'DELETE FROM ' . TOPICS_TRACK_TABLE . "
				WHERE user_id = {$this->user->data['user_id']}
					AND mark_time < $post_time
					AND " . $this->db->sql_in_set('forum_id', $forum_id);
			$this->db->sql_query($sql);

			$sql = "SELECT forum_id
				FROM " . FORUMS_TRACK_TABLE . "
				WHERE user_id = {$this->user->data['user_id']}
					AND " . $this->db->sql_in_set('forum_id', $forum_id);
			$result = $this->db->sql_query($sql);

			$sql_update = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$sql_update[] = (int) $row['forum_id'];
			}
			$this->db->sql_freeresult($result);

			if (count($sql_update))
			{
				$sql = 'UPDATE ' . FORUMS_TRACK_TABLE . "
					SET mark_time = $post_time
					WHERE user_id = {$this->user->data['user_id']}
						AND mark_time < $post_time
						AND " . $this->db->sql_in_set('forum_id', $sql_update);
				$this->db->sql_query($sql);
			}

			if ($sql_insert = array_diff($forum_id, $sql_update))
			{
				$sql_ary = [];
				foreach ($sql_insert as $f_id)
				{
					$sql_ary[] = [
						'user_id'	=> (int) $this->user->data['user_id'],
						'forum_id'	=> (int) $f_id,
						'mark_time'	=> $post_time,
					];
				}

				$this->db->sql_multi_insert(FORUMS_TRACK_TABLE, $sql_ary);
			}
		}
		else if ($this->config['load_anon_lastread'] || $this->user->data['is_registered'])
		{
			$tracking = $this->request->variable($this->config['cookie_name'] . '_track', '', true, request_interface::COOKIE);
			$tracking = ($tracking) ? tracking_unserialize($tracking) : [];

			foreach ($forum_id as $f_id)
			{
				$topic_ids36 = (isset($tracking['tf'][$f_id])) ? $tracking['tf'][$f_id] : [];

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

				$tracking['f'][$f_id] = base_convert($post_time - $this->config['board_startdate'], 10, 36);
			}

			if (isset($tracking['tf']) && empty($tracking['tf']))
			{
				unset($tracking['tf']);
			}
			$this->user->set_cookie('track', tracking_serialize($tracking), $post_time + 31536000);
			$this->request->overwrite($this->config['cookie_name'] . '_track', tracking_serialize($tracking), request_interface::COOKIE);

			unset($tracking);
		}
	}

	public function markread_topic($forum_id, $topic_id, $post_time): void
	{
		$post_time = ($post_time === 0 || $post_time > time()) ? time() : (int) $post_time;

		if ($topic_id === false || $forum_id === false)
		{
			return;
		}

		// Mark post notifications read for this user in this topic
		$this->notification_manager->mark_notifications([
			'notification.type.topic',
			'notification.type.approve_topic',
		], $topic_id, $this->user->data['user_id'], $post_time);

		$this->notification_manager->mark_notifications_by_parent([
			'notification.type.mention',
			'notification.type.quote',
			'notification.type.bookmark',
			'notification.type.post',
			'notification.type.approve_post',
			'notification.type.forum',
		], $topic_id, $this->user->data['user_id'], $post_time);

		if ($this->config['load_db_lastread'] && $this->user->data['is_registered'])
		{
			$sql = 'UPDATE ' . TOPICS_TRACK_TABLE . "
				SET mark_time = $post_time
				WHERE user_id = {$this->user->data['user_id']}
					AND mark_time < $post_time
					AND topic_id = $topic_id";
			$this->db->sql_query($sql);

			// insert row
			if (!$this->db->sql_affectedrows())
			{
				$this->db->sql_return_on_error(true);

				$sql_ary = [
					'user_id'		=> (int) $this->user->data['user_id'],
					'topic_id'		=> (int) $topic_id,
					'forum_id'		=> (int) $forum_id,
					'mark_time'		=> $post_time,
				];

				$this->db->sql_query('INSERT INTO ' . TOPICS_TRACK_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));

				$this->db->sql_return_on_error(false);
			}
		}
		else if ($this->config['load_anon_lastread'] || $this->user->data['is_registered'])
		{
			$tracking = $this->request->variable($this->config['cookie_name'] . '_track', '', true, request_interface::COOKIE);
			$tracking = ($tracking) ? tracking_unserialize($tracking) : [];

			$topic_id36 = base_convert($topic_id, 10, 36);

			if (!isset($tracking['t'][$topic_id36]))
			{
				$tracking['tf'][$forum_id][$topic_id36] = true;
			}

			$tracking['t'][$topic_id36] = base_convert($post_time - (int) $this->config['board_startdate'], 10, 36);

			// If the cookie grows larger than 10000 characters we will remove the smallest value
			// This can result in old topics being unread - but most of the time it should be accurate...
			if (strlen($this->request->variable($this->config['cookie_name'] . '_track', '', true, request_interface::COOKIE)) > 10000)
			{
				//echo 'Cookie grown too large' . print_r($tracking, true);

				// We get the ten most minimum stored time offsets and its associated topic ids
				$time_keys = [];
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

				if ($this->user->data['is_registered'])
				{
					$this->user->data['user_lastmark'] = intval(base_convert(max($time_keys) + $this->config['board_startdate'], 36, 10));

					$sql = 'UPDATE ' . USERS_TABLE . "
						SET user_lastmark = $post_time
						WHERE user_id = {$this->user->data['user_id']}
							AND mark_time < $post_time";
					$this->db->sql_query($sql);
				}
				else
				{
					$tracking['l'] = max($time_keys);
				}
			}
			$this->user->set_cookie('track', tracking_serialize($tracking), $post_time + 31536000);
			$this->request->overwrite($this->config['cookie_name'] . '_track', tracking_serialize($tracking), request_interface::COOKIE);
		}
	}

	public function markread_post($topic_id, $user_id): void
	{
		if ($topic_id === false)
		{
			return;
		}

		$use_user_id = (!$user_id) ? $this->user->data['user_id'] : $user_id;

		if ($this->config['load_db_track'] && $use_user_id != ANONYMOUS)
		{
			$this->db->sql_return_on_error(true);

			$sql_ary = [
				'user_id'		=> (int) $use_user_id,
				'topic_id'		=> (int) $topic_id,
				'topic_posted'	=> 1,
			];

			$this->db->sql_query('INSERT INTO ' . TOPICS_POSTED_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));

			$this->db->sql_return_on_error(false);
		}
	}

}
