<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
* Base class with some generic functions and settings.
*
* @package phpBB3
*/
abstract class phpbb_feed_base
{
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
	*/
	function __construct()
	{
		global $config;

		$this->set_keys();

		// Allow num_items to be string
		if (is_string($this->num_items))
		{
			$this->num_items = (int) $config[$this->num_items];

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
		global $db;

		if (!empty($this->result))
		{
			$db->sql_freeresult($this->result);
		}
	}

	/**
	* Set key
	*/
	function set($key, $value)
	{
		$this->keys[$key] = $value;
	}

	/**
	* Get key
	*/
	function get($key)
	{
		return (isset($this->keys[$key])) ? $this->keys[$key] : NULL;
	}

	function get_readable_forums()
	{
		global $auth;
		static $forum_ids;

		if (!isset($forum_ids))
		{
			$forum_ids = array_keys($auth->acl_getf('f_read', true));
		}

		return $forum_ids;
	}

	function get_moderator_approve_forums()
	{
		global $auth;
		static $forum_ids;

		if (!isset($forum_ids))
		{
			$forum_ids = array_keys($auth->acl_getf('m_approve', true));
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
		global $db, $cache;
		static $forum_ids;

		// Matches acp/acp_board.php
		$cache_name	= 'feed_excluded_forum_ids';

		if (!isset($forum_ids) && ($forum_ids = $cache->get('_' . $cache_name)) === false)
		{
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE ' . $db->sql_bit_and('forum_options', FORUM_OPTION_FEED_EXCLUDE, '<> 0');
			$result = $db->sql_query($sql);

			$forum_ids = array();
			while ($forum_id = (int) $db->sql_fetchfield('forum_id'))
			{
				$forum_ids[$forum_id] = $forum_id;
			}
			$db->sql_freeresult($result);

			$cache->put('_' . $cache_name, $forum_ids);
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
		global $user;

		return $user->get_passworded_forums();
	}

	function get_item()
	{
		global $db, $cache;
		static $result;

		if (!isset($result))
		{
			if (!$this->get_sql())
			{
				return false;
			}

			// Query database
			$sql = $db->sql_build_query('SELECT', $this->sql);
			$result = $db->sql_query_limit($sql, $this->num_items);
		}

		return $db->sql_fetchrow($result);
	}

	function user_viewprofile($row)
	{
		global $phpEx, $user;

		$author_id = (int) $row[$this->get('author_id')];

		if ($author_id == ANONYMOUS)
		{
			// Since we cannot link to a profile, we just return GUEST
			// instead of $row['username']
			return $user->lang['GUEST'];
		}

		return '<a href="' . feed_append_sid('/memberlist.' . $phpEx, 'mode=viewprofile&amp;u=' . $author_id) . '">' . $row[$this->get('creator')] . '</a>';
	}
}
