<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* User loader class
*
* This handles loading users from the database and
* storing them in a temporary cache so we do not
* have to query the same user multiple times in
* different services.
*/
class phpbb_user_loader
{
	/** @var phpbb_db_driver */
	protected $db;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $users_table;

	/**
	* Users loaded from the DB
	*
	* @var array Array of user data that we've loaded from the DB
	*/
	protected $users = array();

	/**
	* User loader constructor
	*
	* @param phpbb_db_driver $db A database connection
	* @param string $phpbb_root_path Path to the phpbb includes directory.
	* @param string $php_ext php file extension
	* @param string $users_table The name of the database table (phpbb_users)
	*/
	public function __construct(phpbb_db_driver $db, $phpbb_root_path, $php_ext, $users_table)
	{
		$this->db = $db;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->users_table = $users_table;
	}

	/**
	* Load user helper
	*
	* Loads all users by user_id that are not in the current user cache
	* Always loads the anonymous user as a fall-back if any user requested does not exist
	*
	* @param array $user_ids
	*/
	public function load_users(array $user_ids)
	{
		$user_ids[] = ANONYMOUS;

		// Load the users
		$user_ids = array_unique($user_ids);

		// Do not load users we already have in $this->users
		$user_ids = array_diff($user_ids, array_keys($this->users));

		if (!empty($user_ids))
		{
			$sql = $this->build_query($this->db->sql_in_set('u.user_id', $user_ids));
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->users[$row['user_id']] = $row;
			}
			$this->db->sql_freeresult($result);
		}

		// If the user does not exist, set their row to false so we do not query them again
		foreach ($user_ids as $user_id)
		{
			if (!isset($this->users[$user_id]))
			{
				$this->users[$user_id] = false;
			}
		}
	}

	/**
	* Load a user by username
	*
	* Stores the full data in the user cache so they do not need to be loaded again
	* Returns the user id so you may use get_user() from the returned value
	*
	* @param string $username Raw username to load (will be cleaned)
	* @return int User ID for the username
	*/
	public function load_user_by_username($username)
	{
		$sql = $this->build_query("username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'");
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row)
		{
			$this->users[$row['user_id']] = $row;

			return $row['user_id'];
		}

		return ANONYMOUS;
	}

	/**
	* Get a user row from our users cache
	*
	* @param int $user_id User ID of the user you want to retreive
	* @param bool $query Should we query the database if this user has not yet been loaded?
	* 			Typically this should be left as false and you should make sure
	* 			you load users ahead of time with load_users()
	* @return array Row from the database of the user or Anonymous if the user wasn't loaded/does not exist
	*/
	public function get_user($user_id, $query = false)
	{
		if (isset($this->users[$user_id]))
		{
			if ($this->users[$user_id] === false)
			{
				return $this->get_user(ANONYMOUS);
			}

			return $this->users[$user_id];
		}

		if ($query || $user_id == ANONYMOUS)
		{
			// Query them if we must (if ANONYMOUS is sent as the user_id and we have not loaded Anonymous yet, we must load Anonymous as a last resort)
			$this->load_users(array($user_id));

			return $this->get_user($user_id);
		}

		return $this->get_user(ANONYMOUS);
	}

	/**
	* Get a formatted username (for output to users)
	*
	* @param int $user_id User ID of the user you want to retreive the username for
	* @param string $mode The mode to load (same as get_username_string). One of the following:
	* 			profile (for getting an url to the profile)
	* 			username (for obtaining the username)
	* 			colour (for obtaining the user colour)
	* 			full (for obtaining a html string representing a coloured link to the users profile)
	* 			no_profile (the same as full but forcing no profile link)
	* @param string $guest_username Optional parameter to specify the guest username.
	* 			It will be used in favor of the GUEST language variable then.
	* @param string $custom_profile_url Optional parameter to specify a profile url.
	* 			The user id get appended to this url as &amp;u={user_id}
	* @param bool $query Should we query the database if this user has not yet been loaded?
	* 			Typically this should be left as false and you should make sure
	* 			you load users ahead of time with load_users()
	* @return string A formatted username based on what mode was desired
	*/
	public function get_username($user_id, $mode, $guest_username = false, $custom_profile_url = false, $query = false)
	{
		$user = $this->get_user($user_id, $query);
		if (!$user)
		{
			return '';
		}

		return get_username_string($mode,
			$user['user_id'],
			$user['username'],
			$user['user_colour'],
			$guest_username,
			$custom_profile_url);
	}

	/**
	* Get HTML code to output the user's avatar
	*
	* @param int $user_id User ID of the user you want to retreive the avatar for
	* @param bool $query Should we query the database if this user has not yet been loaded?
	* 			Typically this should be left as false and you should make sure
	* 			you load users ahead of time with load_users()
	* @return string HTML to output the avatar image
	*/
	public function get_avatar($user_id, $query = false)
	{
		$user = $this->get_user($user_id, $query);
		if (!$user)
		{
			return '';
		}

		if (!function_exists('get_user_avatar'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		}

		return get_user_avatar($user['user_avatar'],
			$user['user_avatar_type'],
			$user['user_avatar_width'],
			$user['user_avatar_height']);
	}

	/**
	* Get user rank title and image
	*
	* @param int $user_id User ID of the user you want to retreive the rank for
	* @param bool $query Should we query the database if this user has not yet been loaded?
	* 			Typically this should be left as false and you should make sure
	* 			you load users ahead of time with load_users()
	* @return array Array with keys 'rank_title', 'rank_img', and 'rank_img_src'
	*/
	public function get_rank($user_id, $query = false)
	{
		$user = $this->get_user($user_id, $query);
		if (!$user)
		{
			return '';
		}

		if (!function_exists('get_user_rank'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		}

		$rank = array(
			'rank_title' => '',
			'rank_img' => '',
			'rank_img_src' => '',
		);

		get_user_rank($user['user_rank'],
			(($user['user_id'] == ANONYMOUS) ? false : $user['user_posts']),
			$rank['rank_title'],
			$rank['rank_img'],
			$rank['rank_img_src']);

		return $rank;
	}

	/**
	* Flush the temporary users cache.
	*
	* This should only be used if the user data in the table was altered
	* and it is absolutely necessary to reload the users
	*/
	public function flush()
	{
		$this->users = array();
	}

	/**
	* Build a user selection query
	*
	* @param string $where Where statement
	*/
	protected function build_query($where)
	{
		$sql_array = array(
			'SELECT'	=> implode(', ', array(
				'u.user_id',
				'u.user_type',
				'u.username',
				'u.username_clean',
				'u.user_email',
				'u.user_posts',
				'u.user_lang',
				'u.user_rank',
				'u.user_colour',
				'u.user_avatar',
				'u.user_avatar_type',
				'u.user_avatar_width',
				'u.user_avatar_height',
			)),
			'FROM'		=> array(
				$this->users_table	=> 'u',
			),
			'WHERE'		=> $where,
		);

		/**
		* Alter what is loaded by the user loader
		*
		* @event core.user_loader.build_query
		* @var array sql_array SQL array to be sent to sql_build_array
		* @since 3.1-A1
		*/
		$vars = array($username, $sql_array);
		extract($phpbb_dispatcher->trigger_event('core.user_loader.build_query', compact($vars)));

		return $this->db->sql_build_query('SELECT', $sql_array);
	}
}
