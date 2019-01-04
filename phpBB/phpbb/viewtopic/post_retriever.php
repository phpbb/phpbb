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

use phpbb\viewtopic\exception\no_posts_found_exception;
use phpbb\viewtopic\exception\topic_not_found_exception;

class post_retriever
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
	 * @var \phpbb\event\dispatcher_interface
	 */
	protected $dispatcher;

	/**
	 * @var \phpbb\pagination
	 */
	protected $pagination;

	/**
	 * @var array
	 */
	protected $parameters;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * @var \phpbb\content_visibility
	 */
	protected $visibility;

	/**
	 * @var string
	 */
	protected $posts_table;

	/**
	 * @var string
	 */
	protected $users_table;

	/**
	 * @var string
	 */
	protected $zebra_table;

	/**
	 * @var array
	 */
	protected $topic_data;

	/**
	 * @var integer
	 */
	protected $start;

	/**
	 * @var bool
	 */
	protected $store_reverse;

	/**
	 * @var integer
	 */
	protected $post_count;

	/**
	 * @var array
	 */
	protected $post_list;

	/**
	 * @var array
	 */
	protected $posts;

	/**
	 * @var integer
	 */
	protected $sql_limit;

	/**
	 * @var string
	 */
	protected $sql_limit_post_time;

	/**
	 * @var integer
	 */
	protected $sql_start;

	/**
	 * @var string
	 */
	protected $sql_sort_order;

	/**
	 * @var string
	 */
	protected $sort_direction;

	/**
	 * @var array
	 */
	protected $user_cache;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $phpEx;

	/*
	 * @todo
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\content_visibility $visibility,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\pagination $pagination,
		\phpbb\user $user,
		string $posts_table,
		string $users_table,
		string $zebra_table,
		string $phpbb_root_path,
		string $phpEx)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->pagination = $pagination;
		$this->user = $user;
		$this->visibility = $visibility;

		$this->posts_table = $posts_table;
		$this->users_table = $users_table;
		$this->zebra_table = $zebra_table;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
	}

	public function get_posts(array &$topic_data, array $parameters)
	{
		$this->parameters = $parameters;
		$this->topic_data = $topic_data;

		$this->store_reverse = false;
		$this->sql_limit = $this->config['posts_per_page'];
		$this->sql_sort_order = '';
		$this->sort_direction = '';
		$this->post_count = 0;

		$this->calculate_post_count();
		$this->calculate_starting_post();
		$this->calculate_sort_order();
		$this->query_post_list();
		$this->query_posts();

		$topic_data = $this->topic_data;

		return $this->posts;
	}

	/**
	 * Query posts from the database.
	 */
	protected function query_posts()
	{
		$this->posts = [];
		$this->user_cache = [];

		// Holding maximum post time for marking topic read
		// We need to grab it because we do reverse ordering sometimes
		$max_post_time = 0;

		$sql_ary = [
			'SELECT'	=> 'u.*, z.friend, z.foe, p.*',

			'FROM'		=> [
				$this->users_table	=> 'u',
				$this->posts_table	=> 'p',
			],

			'LEFT_JOIN'	=> [
				[
					'FROM'	=> array($this->zebra_table => 'z'),
					'ON'	=> 'z.user_id = ' . (int) $this->user->data['user_id'] . ' AND z.zebra_id = p.poster_id',
				],
			],

			'WHERE'		=> $this->db->sql_in_set('p.post_id', $this->post_list) . ' AND u.user_id = p.poster_id',
		];

		$forum_id = (int) $this->topic_data['forum_id'];
		$topic_id = (int) $this->topic_data['topic_id'];
		$topic_data = $this->topic_data;
		$post_list = $this->post_list;
		$sort_days = $this->parameters['show_days'];
		$sort_key = $this->parameters['sort_by'];
		$sort_dir = $this->parameters['sort_order'];
		$start = $this->start;

		/**
		 * Event to modify the SQL query before the post and poster data is retrieved
		 *
		 * @event core.viewtopic_get_post_data
		 * @var	int		forum_id	Forum ID
		 * @var	int		topic_id	Topic ID
		 * @var	array	topic_data	Array with topic data
		 * @var	array	post_list	Array with post_ids we are going to retrieve
		 * @var	int		sort_days	Display posts of previous x days
		 * @var	string	sort_key	Key the posts are sorted by
		 * @var	string	sort_dir	Direction the posts are sorted by
		 * @var	int		start		Pagination information
		 * @var	array	sql_ary		The SQL array to get the data of posts and posters
		 * @since 3.1.0-a1
		 * @changed 3.1.0-a2 Added vars forum_id, topic_id, topic_data, post_list, sort_days, sort_key, sort_dir, start
		 */
		$vars = array(
			'forum_id',
			'topic_id',
			'topic_data',
			'post_list',
			'sort_days',
			'sort_key',
			'sort_dir',
			'start',
			'sql_ary',
		);
		extract($this->dispatcher->trigger_event('core.viewtopic_get_post_data', compact($vars)));

		$this->topic_data = $topic_data;
		$this->topic_data['forum_id'] = (int) $forum_id;
		$this->topic_data['topic_id'] = (int) $topic_data;
		$this->post_list = $post_list;
		$this->parameters['show_days'] = $sort_days;
		$this->parameters['sort_by'] = $sort_key;

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Set max_post_time
			if ($row['post_time'] > $max_post_time)
			{
				$max_post_time = $row['post_time'];
			}

			$poster_id = (int) $row['poster_id'];

			// Does post have an attachment? If so, add it to the list
			if ($row['post_attachment'] && $this->config['allow_attachments'])
			{
				$attach_list[] = (int) $row['post_id'];

				if ($row['post_visibility'] == ITEM_UNAPPROVED || $row['post_visibility'] == ITEM_REAPPROVE)
				{
					$has_unapproved_attachments = true;
				}
				else if ($row['post_visibility'] == ITEM_APPROVED)
				{
					$has_approved_attachments = true;
				}
			}

			$rowset_data = [
				// @todo: view && $post_id: wtf? please?
				//'hide_post'			=> (&& ($view != 'show' || $post_id != $row['post_id'])),
				'hide_post'			=> ($row['foe'] || $row['post_visibility'] == ITEM_DELETED),

				'post_id'			=> $row['post_id'],
				'post_time'			=> $row['post_time'],
				'user_id'			=> $row['user_id'],
				'username'			=> $row['username'],
				'user_colour'		=> $row['user_colour'],
				'topic_id'			=> $row['topic_id'],
				'forum_id'			=> $row['forum_id'],
				'post_subject'		=> $row['post_subject'],
				'post_edit_count'	=> $row['post_edit_count'],
				'post_edit_time'	=> $row['post_edit_time'],
				'post_edit_reason'	=> $row['post_edit_reason'],
				'post_edit_user'	=> $row['post_edit_user'],
				'post_edit_locked'	=> $row['post_edit_locked'],
				'post_delete_time'	=> $row['post_delete_time'],
				'post_delete_reason'=> $row['post_delete_reason'],
				'post_delete_user'	=> $row['post_delete_user'],

				// Make sure the icon actually exists
				'icon_id'			=> (isset($icons[$row['icon_id']]['img'], $icons[$row['icon_id']]['height'], $icons[$row['icon_id']]['width'])) ? $row['icon_id'] : 0,
				'post_attachment'	=> $row['post_attachment'],
				'post_visibility'	=> $row['post_visibility'],
				'post_reported'		=> $row['post_reported'],
				'post_username'		=> $row['post_username'],
				'post_text'			=> $row['post_text'],
				'bbcode_uid'		=> $row['bbcode_uid'],
				'bbcode_bitfield'	=> $row['bbcode_bitfield'],
				'enable_smilies'	=> $row['enable_smilies'],
				'enable_sig'		=> $row['enable_sig'],
				'friend'			=> $row['friend'],
				'foe'				=> $row['foe'],
			];

			/**
			 * Modify the post rowset containing data to be displayed with posts
			 *
			 * @event core.viewtopic_post_rowset_data
			 * @var	array	rowset_data	Array with the rowset data for this post
			 * @var	array	row			Array with original user and post data
			 * @since 3.1.0-a1
			 */
			$vars = array('rowset_data', 'row');
			extract($this->dispatcher->trigger_event('core.viewtopic_post_rowset_data', compact($vars)));

			$this->posts[((int) $row['post_id'])] = $rowset_data;

			if (!$this->has_user_data_cached($poster_id))
			{
				$this->compute_user_data($row);
			}
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Returns whether the user data has already been computed.
	 *
	 * @param int $user_id
	 *
	 * @return bool True if the user data has been already computed, false otherwise.
	 */
	protected function has_user_data_cached($user_id)
	{
		return isset($this->user_cache[$user_id]);
	}

	protected function compute_user_data($row)
	{
		$now = $this->user->create_datetime();
		$now = phpbb_gmgetdate($now->getTimestamp() + $now->getOffset());

		$poster_id = (int) $row['poster_id'];

		if ($poster_id === ANONYMOUS)
		{
			$user_cache_data = array(
				'user_type'		=> USER_IGNORE,
				'joined'		=> '',
				'posts'			=> '',

				'sig'					=> '',
				'sig_bbcode_uid'		=> '',
				'sig_bbcode_bitfield'	=> '',

				'online'			=> false,
				'avatar'			=> ($this->user->optionget('viewavatars')) ? phpbb_get_user_avatar($row) : '',
				'rank_title'		=> '',
				'rank_image'		=> '',
				'rank_image_src'	=> '',
				'pm'				=> '',
				'email'				=> '',
				'jabber'			=> '',
				'search'			=> '',
				'age'				=> '',

				'username'			=> $row['username'],
				'user_colour'		=> $row['user_colour'],
				'contact_user'		=> '',

				'warnings'			=> 0,
				'allow_pm'			=> 0,
			);

			/**
			 * Modify the guest user's data displayed with the posts
			 *
			 * @event core.viewtopic_cache_guest_data
			 * @var	array	user_cache_data	Array with the user's data
			 * @var	int		poster_id		Poster's user id
			 * @var	array	row				Array with original user and post data
			 * @since 3.1.0-a1
			 */
			$vars = array('user_cache_data', 'poster_id', 'row');
			extract($this->dispatcher->trigger_event('core.viewtopic_cache_guest_data', compact($vars)));

			$this->user_cache[$poster_id] = $user_cache_data;

			$user_rank_data = phpbb_get_user_rank($row, false);
			$this->user_cache[$poster_id]['rank_title'] = $user_rank_data['title'];
			$this->user_cache[$poster_id]['rank_image'] = $user_rank_data['img'];
			$this->user_cache[$poster_id]['rank_image_src'] = $user_rank_data['img_src'];
		}
		else
		{
			$user_sig = '';

			// We add the signature to every posters entry because enable_sig is post dependent
			if ($row['user_sig'] && $this->config['allow_sig'] && $this->user->optionget('viewsigs'))
			{
				$user_sig = $row['user_sig'];
			}

			$id_cache[] = $poster_id;

			$user_cache_data = array(
				'user_type'					=> $row['user_type'],
				'user_inactive_reason'		=> $row['user_inactive_reason'],

				'joined'		=> $this->user->format_date($row['user_regdate']),
				'posts'			=> $row['user_posts'],
				'warnings'		=> (isset($row['user_warnings'])) ? $row['user_warnings'] : 0,

				'sig'					=> $user_sig,
				'sig_bbcode_uid'		=> (!empty($row['user_sig_bbcode_uid'])) ? $row['user_sig_bbcode_uid'] : '',
				'sig_bbcode_bitfield'	=> (!empty($row['user_sig_bbcode_bitfield'])) ? $row['user_sig_bbcode_bitfield'] : '',

				'viewonline'	=> $row['user_allow_viewonline'],
				'allow_pm'		=> $row['user_allow_pm'],

				'avatar'		=> ($this->user->optionget('viewavatars')) ? phpbb_get_user_avatar($row) : '',
				'age'			=> '',

				'rank_title'		=> '',
				'rank_image'		=> '',
				'rank_image_src'	=> '',

				'username'			=> $row['username'],
				'user_colour'		=> $row['user_colour'],
				'contact_user' 		=> $this->user->lang('CONTACT_USER', get_username_string('username', $poster_id, $row['username'], $row['user_colour'], $row['username'])),

				'online'		=> false,
				'jabber'		=> ($this->config['jab_enable'] && $row['user_jabber'] && $this->auth->acl_get('u_sendim')) ? append_sid("{$this->phpbb_root_path}memberlist.$this->phpEx", "mode=contact&amp;action=jabber&amp;u=$poster_id") : '',
				'search'		=> ($this->config['load_search'] && $this->auth->acl_get('u_search')) ? append_sid("{$this->phpbb_root_path}search.$this->phpEx", "author_id=$poster_id&amp;sr=posts") : '',

				'author_full'		=> get_username_string('full', $poster_id, $row['username'], $row['user_colour']),
				'author_colour'		=> get_username_string('colour', $poster_id, $row['username'], $row['user_colour']),
				'author_username'	=> get_username_string('username', $poster_id, $row['username'], $row['user_colour']),
				'author_profile'	=> get_username_string('profile', $poster_id, $row['username'], $row['user_colour']),
			);

			/**
			 * Modify the users' data displayed with their posts
			 *
			 * @event core.viewtopic_cache_user_data
			 * @var	array	user_cache_data	Array with the user's data
			 * @var	int		poster_id		Poster's user id
			 * @var	array	row				Array with original user and post data
			 * @since 3.1.0-a1
			 */
			$vars = array('user_cache_data', 'poster_id', 'row');
			extract($this->dispatcher->trigger_event('core.viewtopic_cache_user_data', compact($vars)));

			$this->user_cache[$poster_id] = $user_cache_data;

			$user_rank_data = phpbb_get_user_rank($row, $row['user_posts']);
			$this->user_cache[$poster_id]['rank_title'] = $user_rank_data['title'];
			$this->user_cache[$poster_id]['rank_image'] = $user_rank_data['img'];
			$this->user_cache[$poster_id]['rank_image_src'] = $user_rank_data['img_src'];

			if ((!empty($row['user_allow_viewemail']) && $this->auth->acl_get('u_sendemail')) || $this->auth->acl_get('a_email'))
			{
				$this->user_cache[$poster_id]['email'] = ($this->config['board_email_form'] && $this->config['email_enable']) ? append_sid("{$this->phpbb_root_path}memberlist.$this->phpEx", "mode=email&amp;u=$poster_id") : (($this->config['board_hide_emails'] && !$this->auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email']);
			}
			else
			{
				$this->user_cache[$poster_id]['email'] = '';
			}

			if ($this->config['allow_birthdays'] && !empty($row['user_birthday']))
			{
				list($bday_day, $bday_month, $bday_year) = array_map('intval', explode('-', $row['user_birthday']));

				if ($bday_year)
				{
					$diff = $now['mon'] - $bday_month;
					if ($diff == 0)
					{
						$diff = ($now['mday'] - $bday_day < 0) ? 1 : 0;
					}
					else
					{
						$diff = ($diff < 0) ? 1 : 0;
					}

					$this->user_cache[$poster_id]['age'] = (int) ($now['year'] - $bday_year - $diff);
				}
			}
		}
	}

	/**
	 * Query post IDs from the database which should be queried.
	 */
	protected function query_post_list()
	{
		$join_user_sql = [
			'a' => true,
			't' => false,
			's' => false
		];

		$sort_key = $this->parameters['sort_by'];
		$sql = 'SELECT p.post_id
			FROM ' . $this->posts_table . ' p' . (($join_user_sql[$sort_key]) ? ', ' . $this->users_table . ' u': '') . '
			WHERE p.topic_id = ' . (int) $this->topic_data['topic_id'] . '
				AND ' . $this->visibility->get_visibility_sql('post', (int) $this->topic_data['forum_id'], 'p.') . '
				' . (($join_user_sql[$sort_key]) ? 'AND u.user_id = p.poster_id': '') . '
				' . $this->sql_limit_post_time . '
			ORDER BY ' . $this->sql_sort_order;

		$sql_limit = $this->sql_limit;
		$sql_start = $this->sql_start;
		$sort_days = $this->parameters['show_days'];
		$forum_id = (int) $this->topic_data['forum_id'];

		/**
		 * Event to modify the SQL query that gets post_list
		 *
		 * @event core.viewtopic_modify_post_list_sql
		 * @var	string	sql			The SQL query to generate the post_list
		 * @var	int		sql_limit	The number of posts the query fetches
		 * @var	int		sql_start	The index the query starts to fetch from
		 * @var	string	sort_key	Key the posts are sorted by
		 * @var	string	sort_days	Display posts of previous x days
		 * @var	int		forum_id	Forum ID
		 * @since 3.2.4-RC1
		 */
		$vars = array(
			'sql',
			'sql_limit',
			'sql_start',
			'sort_key',
			'sort_days',
			'forum_id',
		);
		extract($this->dispatcher->trigger_event('core.viewtopic_modify_post_list_sql', compact($vars)));

		$this->sql_limit = $sql_limit;
		$this->sql_start = $sql_start;
		$this->parameters['show_days'] = $sort_days;
		$this->topic_data['forum_id'] = (int) $forum_id;

		$result = $this->db->sql_query_limit($sql, $this->sql_limit, $this->sql_start);

		$i = ($this->store_reverse) ? $this->sql_limit - 1 : 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->post_list[$i] = (int) $row['post_id'];
			($this->store_reverse) ? $i-- : $i++;
		}
		$this->db->sql_freeresult($result);

		if (empty($this->post_list))
		{
			if ($sort_days)
			{
				throw new no_posts_found_exception('NO_POSTS_TIME_FRAME');
			}
			else
			{
				throw new topic_not_found_exception();
			}
		}
	}

	/**
	 * Calculate how many posts the topic has.
	 */
	protected function calculate_post_count()
	{
		if ($this->parameters['show_days'] !== 0)
		{
			$min_post_time = time() - ($this->parameters['show_days'] * 86400);

			$sql = 'SELECT COUNT(post_id) AS num_posts
				FROM ' . $this->posts_table . '
				WHERE topic_id = ' . (int) $this->topic_data['topic_id'] . '
					AND post_time >= ' . $min_post_time . '
					AND ' . $this->visibility->get_visibility_sql('post', (int) $this->topic_data['forum_id']);
			$result = $this->db->sql_query($sql);
			$this->post_count = (int) $this->db->sql_fetchfield('num_posts');
			$this->db->sql_freeresult($result);

			$this->sql_limit_post_time = 'AND p.post_time >= ' . $min_post_time . ' ';

			// @todo: this should be in a controller. Outrageous!
//			if (isset($_POST['sort']))
//			{
//				$start = 0;
//			}
		}
		else
		{
			$this->post_count = $this->visibility->get_count(
				'topic_posts',
				$this->topic_data,
				(int) $this->topic_data['forum_id']
			);

			$this->sql_limit_post_time = '';
		}
	}

	/**
	 * Calculates the starting post for the current page.
	 */
	protected function calculate_starting_post()
	{
		if (array_key_exists('post_id', $this->topic_data))
		{
			$this->start = floor($this->topic_data['prev_posts'] / $this->sql_limit) * $this->sql_limit;
		}
		else
		{
			$this->start = ($this->parameters['page'] - 1) * $this->sql_limit;
		}

		$this->start = $this->pagination->validate_start($this->start, $this->sql_limit, $this->post_count);
	}

	/**
	 * Calculate the SQL sorting and limit parameters.
	 */
	protected function calculate_sort_order()
	{
		if ($this->start > ($this->post_count / 2))
		{
			$this->store_reverse = true;
			$this->sort_direction = ($this->parameters['sort_order'] === 'd') ? 'ASC' : 'DESC';
			$this->sql_limit = $this->pagination->reverse_limit($this->start, $this->sql_limit, $this->post_count);
			$this->sql_start = $this->pagination->reverse_start($this->start, $this->sql_limit, $this->post_count);
		}
		else
		{
			$this->sql_start = $this->start;
			$this->sort_direction = ($this->parameters['sort_order'] === 'd') ? 'DESC' : 'ASC';
		}

		$sort_by_sql = [
			'a' => ['u.username_clean', 'p.post_id'],
			't' => ['p.post_time', 'p.post_id'],
			's' => ['p.post_subject', 'p.post_id']
		];

		$sort_by = $this->parameters['sort_by'];
		if (is_array($sort_by_sql[$sort_by]))
		{
			$this->sql_sort_order = implode(' ' . $this->sort_direction . ', ', $sort_by_sql[$sort_by])
				. ' ' . $this->sort_direction;
		}
		else
		{
			$this->sql_sort_order = $sort_by_sql[$sort_by] . ' ' . $this->sort_direction;
		}
	}
}
