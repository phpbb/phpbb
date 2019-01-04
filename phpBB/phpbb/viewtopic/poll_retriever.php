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

namespace phpbb\viewtopic;

/**
 * Class for retrieving poll metadata and results.
 */
class poll_retriever
{
	/**
	 * @var \phpbb\auth\auth
	 */
	protected $auth;

	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\event\dispatcher
	 */
	protected $dispatcher;

	/**
	 * @var \phpbb\request\request_interface
	 */
	protected $request;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $poll_options_table;

	/**
	 * @var string
	 */
	protected $poll_vote_table;

	/**
	 * @var string
	 */
	protected $post_table;

	/**
	 * @var int
	 */
	protected $first_post_id;

	/**
	 * @var int
	 */
	protected $forum_id;

	/**
	 * @var array
	 */
	protected $poll_data;

	/**
	 * @var array
	 */
	protected $topic_data;

	/**
	 * @var int
	 */
	protected $topic_id;

	/**
	 * @var string
	 */
	protected $topics_table;

	/*
	 * @todo
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\request\request_interface $request,
		\phpbb\user $user,
		string $poll_options_table,
		string $poll_vote_table,
		string $post_table,
		string $topics_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->request = $request;
		$this->user = $user;

		$this->poll_options_table = $poll_options_table;
		$this->poll_vote_table = $poll_vote_table;
		$this->post_table = $post_table;
		$this->topics_table = $topics_table;

		$this->first_post_id = 0;
		$this->forum_id = 0;
		$this->poll_data = [];
		$this->topic_data = [];
		$this->topic_id = 0;
	}

	public function get_poll(array &$topic_data, $voted_id, $should_update, $force_show_results = false)
	{
		$this->poll_data = [];

		if (empty($topic_data['poll_start']))
		{
			return $this->poll_data;
		}

		$this->poll_data['voted_id'] = $voted_id;

		$this->topic_data = $topic_data;
		$this->topic_id = (int) $topic_data['topic_id'];
		$this->first_post_id = (int) $topic_data['topic_first_post_id'];
		$this->forum_id = (int) $topic_data['forum_id'];

		$this->query_poll_metadata();
		$this->get_votes();
		$this->can_vote();

		$this->trigger_manipulate_poll_options_event($force_show_results);

		if ($should_update && $this->poll_data['user_can_vote'])
		{
			$this->update_votes();
		}

		$topic_data = $this->topic_data;

		return $this->poll_data;
	}

	protected function query_poll_metadata()
	{
		$sql = 'SELECT o.*, p.bbcode_bitfield, p.bbcode_uid
			FROM ' . $this->poll_options_table . ' o, ' . $this->post_table . ' p
			WHERE o.topic_id = ' . $this->topic_id . '
				AND p.post_id = ' . $this->first_post_id . '
				AND p.topic_id = o.topic_id
			ORDER BY o.poll_option_id';
		$result = $this->db->sql_query($sql);

		$this->poll_data['poll_info'] = $this->poll_data['vote_counts'] = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->poll_data['poll_info'][] = $row;
			$option_id = (int) $row['poll_option_id'];
			$this->poll_data['vote_counts'][$option_id] = (int) $row['poll_option_total'];
		}
		$this->db->sql_freeresult($result);
	}

	protected function get_votes()
	{
		$this->poll_data['current_vote_id'] = [];
		if ($this->user->data['is_registered'])
		{
			$sql = 'SELECT poll_option_id
				FROM ' . $this->poll_vote_table . '
				WHERE topic_id = ' . $this->topic_id . '
					AND vote_user_id = ' . (int) $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->poll_data['current_vote_id'][] = $row['poll_option_id'];
			}
			$this->db->sql_freeresult($result);
		}
		else
		{
			$vote_cookie_name = $this->config['cookie_name'] . '_poll_' . $this->topic_id;

			// Cookie based guest tracking ... I don't like this but hum ho
			// it's oft requested. This relies on "nice" users who don't feel
			// the need to delete cookies to mess with results.
			if ($this->request->is_set($vote_cookie_name, \phpbb\request\request_interface::COOKIE))
			{
				$this->poll_data['current_vote_id'] = explode(
					',',
					$this->request->variable(
						$vote_cookie_name,
						'',
						true,
						\phpbb\request\request_interface::COOKIE
					)
				);
				$this->poll_data['current_vote_id'] = array_map('intval', $this->poll_data['current_vote_id']);
			}
		}
	}

	protected function can_vote()
	{
		$s_can_vote = $this->auth->acl_get('f_vote', $this->forum_id);
		$s_can_vote = $s_can_vote && ($this->topic_data['poll_length'] != 0);
		$s_can_vote = $s_can_vote && ((
			$this->topic_data['poll_length'] != 0 &&
			($this->topic_data['poll_start'] + $this->topic_data['poll_length']) > time()) ||
			$this->topic_data['poll_length'] == 0
		);
		$s_can_vote = $s_can_vote && ($this->topic_data['topic_status'] != ITEM_LOCKED);
		$s_can_vote = $s_can_vote && ($this->topic_data['forum_status'] != ITEM_LOCKED);
		$s_can_vote = $s_can_vote && ($this->topic_data['forum_status'] != ITEM_LOCKED);
		$s_can_vote = $s_can_vote && (
			!count($this->poll_data['current_vote_id']) ||
			($this->auth->acl_get('f_votechg', $this->forum_id) && $this->topic_data['poll_vote_change'])
		);

		$this->poll_data['user_can_vote'] = $s_can_vote;
		$this->poll_data['should_display_poll_results'] = !$s_can_vote ||
			($s_can_vote && count($this->poll_data['current_vote_id']));
	}

	protected function update_votes()
	{
		foreach ($this->poll_data['voted_id'] as $option)
		{
			if (in_array($option, $this->poll_data['current_vote_id']))
			{
				continue;
			}

			$sql = 'UPDATE ' . $this->poll_options_table . '
				SET poll_option_total = poll_option_total + 1
				WHERE poll_option_id = ' . ((int) $option) . '
					AND topic_id = ' . (int) $this->topic_id;
			$this->db->sql_query($sql);

			$this->poll_data['vote_counts'][$option]++;

			if ($this->user->data['is_registered'])
			{
				$sql_ary = [
					'topic_id'			=> (int) $this->topic_id,
					'poll_option_id'	=> (int) $option,
					'vote_user_id'		=> (int) $this->user->data['user_id'],
					'vote_user_ip'		=> (string) $this->user->ip
				];

				$sql = 'INSERT INTO ' . $this->poll_vote_table . ' '
					. $this->db->sql_build_array('INSERT', $sql_ary);
				$this->db->sql_query($sql);
			}
		}

		foreach ($this->poll_data['current_vote_id'] as $option)
		{
			if (!in_array($option, $this->poll_data['voted_id']))
			{
				$sql = 'UPDATE ' . $this->poll_options_table . '
					SET poll_option_total = poll_option_total - 1
					WHERE poll_option_id = ' . (int) $option . '
						AND topic_id = ' . (int) $this->topic_id;
				$this->db->sql_query($sql);

				$this->poll_data['vote_counts'][$option]--;

				if ($this->user->data['is_registered'])
				{
					$sql = 'DELETE FROM ' . $this->poll_vote_table . '
						WHERE topic_id = ' . (int) $this->topic_id . '
							AND poll_option_id = ' . (int) $option . '
							AND vote_user_id = ' . (int) $this->user->data['user_id'];
					$this->db->sql_query($sql);
				}
			}
		}

		if (((int) $this->user->data['user_id']) === ANONYMOUS && !$this->user->data['is_bot'])
		{
			$this->user->set_cookie(
				'poll_' . $this->topic_id,
				implode(',', $this->poll_data['voted_id']),
				time() + 31536000
			);
		}

		$sql = 'UPDATE ' . $this->topics_table . '
			SET poll_last_vote = ' . time() . '
			WHERE topic_id = ' . (int) $this->topic_id;
		$this->db->sql_query($sql);

		// @todo: redirect this shit.
//		$redirect_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start"));
//		$message = $user->lang['VOTE_SUBMITTED'] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $redirect_url . '">', '</a>');

	}

	protected function trigger_manipulate_poll_options_event($force_showing_poll_results)
	{
		$cur_voted_id = $this->poll_data['current_vote_id'];
		$forum_id = $this->forum_id;
		$poll_info = $this->poll_data['poll_info'];
		$s_can_vote = $this->poll_data['user_can_vote'];
		$s_display_results = $this->poll_data['should_display_poll_results'] || $force_showing_poll_results;
		$topic_id = $this->topic_id;
		$topic_data = $this->topic_data;
		$viewtopic_url = $this->topic_data['viewtopic_url'];
		$vote_counts = $this->poll_data['vote_counts'];
		$voted_id = $this->poll_data['voted_id'];

		/**
		 * Event to manipulate the poll data
		 *
		 * @event core.viewtopic_modify_poll_data
		 * @var	array	cur_voted_id				Array with options' IDs current user has voted for
		 * @var	int		forum_id					The topic's forum id
		 * @var	array	poll_info					Array with the poll information
		 * @var	bool	s_can_vote					Flag indicating if a user can vote
		 * @var	bool	s_display_results			Flag indicating if results or poll options should be displayed
		 * @var	int		topic_id					The id of the topic the user tries to access
		 * @var	array	topic_data					All the information from the topic and forum tables for this topic
		 * @var	string	viewtopic_url				URL to the topic page
		 * @var	array	vote_counts					Array with the vote counts for every poll option
		 * @var	array	voted_id					Array with updated options' IDs current user is voting for
		 * @since 3.1.5-RC1
		 */
		$vars = array(
			'cur_voted_id',
			'forum_id',
			'poll_info',
			's_can_vote',
			's_display_results',
			'topic_id',
			'topic_data',
			'viewtopic_url',
			'vote_counts',
			'voted_id',
		);
		extract($this->dispatcher->trigger_event('core.viewtopic_modify_poll_data', compact($vars)));

		$this->topic_data = $topic_data;
		$this->poll_data['current_vote_id'] = $cur_voted_id;
		$this->forum_id = $this->topic_data['forum_id'] = (int) $forum_id;
		$this->poll_data['poll_info'] = $poll_info;
		$this->poll_data['user_can_vote'] = $s_can_vote;
		$this->poll_data['should_display_poll_results'] = $s_display_results;
		$this->topic_id = $this->topic_data['topic_id'] = (int) $topic_id;
		$this->topic_data['viewtopic_url'] = $viewtopic_url;
		$this->poll_data['vote_counts'] = $vote_counts;
		$this->poll_data['voted_id'] = $voted_id;
	}
}
