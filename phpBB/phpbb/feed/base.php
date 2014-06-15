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

namespace phpbb\feed;

/**
* Base class with some generic functions and settings.
*/
abstract class base
{
	/**
	* Feed helper object
	* @var \phpbb\feed\helper
	*/
	protected $helper;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var string */
	protected $phpEx;

	/**
	* SQL Query to be executed to get feed items
	*/
	var $sql = array();

	/**
	* Keys specified for retrieval of title, content, etc.
	*/
	var $keys = array();

	/**
	* Number of items to fetch. Usually overwritten by $config['feed_something']
	*/
	var $num_items = 15;

	/**
	* Separator for title elements to separate items (for example forum / topic)
	*/
	var $separator = "\xE2\x80\xA2"; // &bull;

	/**
	* Separator for the statistics row (Posted by, post date, replies, etc.)
	*/
	var $separator_stats = "\xE2\x80\x94"; // &mdash;

	/**
	* Constructor
	*
	* @param \phpbb\feed\helper					$helper		Feed helper
	* @param \phpbb\config\config				$config		Config object
	* @param \phpbb\db\driver\driver_interface	$db			Database connection
	* @param \phpbb\cache\driver\driver_interface	$cache	Cache object
	* @param \phpbb\user						$user		User object
	* @param \phpbb\auth\auth					$auth		Auth object
	* @param \phpbb\content_visibility			$content_visibility		Auth object
	* @param string								$phpEx		php file extension
	*/
	function __construct(\phpbb\feed\helper $helper, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\cache\driver\driver_interface $cache, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\content_visibility $content_visibility, $phpEx)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->db = $db;
		$this->cache = $cache;
		$this->user = $user;
		$this->auth = $auth;
		$this->content_visibility = $content_visibility;
		$this->phpEx = $phpEx;

		$this->set_keys();

		// Allow num_items to be string
		if (is_string($this->num_items))
		{
			$this->num_items = (int) $this->config[$this->num_items];

			// A precaution
			if (!$this->num_items)
			{
				$this->num_items = 10;
			}
		}
	}

	/**
	* Set keys.
	*/
	function set_keys()
	{
	}

	/**
	* Open feed
	*/
	function open()
	{
	}

	/**
	* Close feed
	*/
	function close()
	{
		if (!empty($this->result))
		{
			$this->db->sql_freeresult($this->result);
		}
	}

	/**
	* Set key
	*
	* @param string $key Key
	* @param mixed $value Value
	*/
	function set($key, $value)
	{
		$this->keys[$key] = $value;
	}

	/**
	* Get key
	*
	* @param string $key Key
	* @return mixed
	*/
	function get($key)
	{
		return (isset($this->keys[$key])) ? $this->keys[$key] : null;
	}

	function get_readable_forums()
	{
		static $forum_ids;

		if (!isset($forum_ids))
		{
			$forum_ids = array_keys($this->auth->acl_getf('f_read', true));
		}

		return $forum_ids;
	}

	function get_moderator_approve_forums()
	{
		static $forum_ids;

		if (!isset($forum_ids))
		{
			$forum_ids = array_keys($this->auth->acl_getf('m_approve', true));
		}

		return $forum_ids;
	}

	function is_moderator_approve_forum($forum_id)
	{
		static $forum_ids;

		if (!isset($forum_ids))
		{
			$forum_ids = array_flip($this->get_moderator_approve_forums());
		}

		return (isset($forum_ids[$forum_id])) ? true : false;
	}

	function get_excluded_forums()
	{
		static $forum_ids;

		// Matches acp/acp_board.php
		$cache_name	= 'feed_excluded_forum_ids';

		if (!isset($forum_ids) && ($forum_ids = $this->cache->get('_' . $cache_name)) === false)
		{
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE ' . $this->db->sql_bit_and('forum_options', FORUM_OPTION_FEED_EXCLUDE, '<> 0');
			$result = $this->db->sql_query($sql);

			$forum_ids = array();
			while ($forum_id = (int) $this->db->sql_fetchfield('forum_id'))
			{
				$forum_ids[$forum_id] = $forum_id;
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('_' . $cache_name, $forum_ids);
		}

		return $forum_ids;
	}

	function is_excluded_forum($forum_id)
	{
		$forum_ids = $this->get_excluded_forums();

		return isset($forum_ids[$forum_id]) ? true : false;
	}

	function get_passworded_forums()
	{
		return $this->user->get_passworded_forums();
	}

	function get_item()
	{
		static $result;

		if (!isset($result))
		{
			if (!$this->get_sql())
			{
				return false;
			}

			// Query database
			$sql = $this->db->sql_build_query('SELECT', $this->sql);
			$result = $this->db->sql_query_limit($sql, $this->num_items);
		}

		return $this->db->sql_fetchrow($result);
	}

	function user_viewprofile($row)
	{
		$author_id = (int) $row[$this->get('author_id')];

		if ($author_id == ANONYMOUS)
		{
			// Since we cannot link to a profile, we just return GUEST
			// instead of $row['username']
			return $this->user->lang['GUEST'];
		}

		return '<a href="' . $this->helper->append_sid('memberlist.' . $this->phpEx, 'mode=viewprofile&amp;u=' . $author_id) . '">' . $row[$this->get('creator')] . '</a>';
	}
}
