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

namespace phpbb;

/**
* User loader class
*
* This handles loading users from the database and
* storing in them in a temporary cache so we do not
* have to query the same user multiple times in
* different services.
*/
class user_loader
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db = null;

	/** @var string */
	protected $phpbb_root_path = null;

	/** @var string */
	protected $php_ext = null;

	/** @var string */
	protected $users_table = null;

	/**
	* Users loaded from the DB
	*
	* @var array Array of user data that we've loaded from the DB
	*/
	protected $users = array();

	/**
	* User loader constructor
	*
	* @param \phpbb\db\driver\driver_interface $db A database connection
	* @param string $phpbb_root_path Path to the phpbb includes directory.
	* @param string $php_ext php file extension
	* @param string $users_table The name of the database table (phpbb_users)
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $phpbb_root_path, $php_ext, $users_table)
	{
		$this->db = $db;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->users_table = $users_table;
	}

	/**
	* Load user helper
	*
	* @param array $user_ids
	* @param array $ignore_types user types to ignore
	*/
	public function load_users(array $user_ids, array $ignore_types = array())
	{
		$user_ids[] = ANONYMOUS;

		// Make user_ids unique and convert to integer.
		$user_ids = array_map('intval', array_unique($user_ids));

		// Do not load users we already have in $this->users
		$user_ids = array_diff($user_ids, array_keys($this->users));

		if (count($user_ids))
		{
			$sql = 'SELECT *
				FROM ' . $this->users_table . '
				WHERE ' . $this->db->sql_in_set('user_id', $user_ids) . '
					AND ' . $this->db->sql_in_set('user_type', $ignore_types, true, true);
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->users[$row['user_id']] = $row;
			}
			$this->db->sql_freeresult($result);
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
		$sql = 'SELECT *
			FROM ' . $this->users_table . "
			WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
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
	* 						Typically this should be left as false and you should make sure
	* 						you load users ahead of time with load_users()
	* @return array|bool Row from the database of the user or Anonymous if the user wasn't loaded/does not exist
	* 						or bool False if the anonymous user was not loaded
	*/
	public function get_user($user_id, $query = false)
	{
		if (isset($this->users[$user_id]))
		{
			return $this->users[$user_id];
		}
		// Query them if we must (if ANONYMOUS is sent as the user_id and we have not loaded Anonymous yet, we must load Anonymous as a last resort)
		else if ($query || $user_id == ANONYMOUS)
		{
			$this->load_users(array($user_id));

			return $this->get_user($user_id);
		}

		return $this->get_user(ANONYMOUS);
	}

	/**
	* Get username
	*
	* @param int $user_id User ID of the user you want to retreive the username for
	* @param string $mode The mode to load (same as get_username_string). One of the following:
	* 			profile (for getting an url to the profile)
	* 			username (for obtaining the username)
	* 			colour (for obtaining the user colour)
	* 			full (for obtaining a html string representing a coloured link to the users profile)
	* 			no_profile (the same as full but forcing no profile link)
	* @param string $guest_username Optional parameter to specify the guest username. It will be used in favor of the GUEST language variable then.
	* @param string $custom_profile_url Optional parameter to specify a profile url. The user id get appended to this url as &amp;u={user_id}
	* @param bool $query Should we query the database if this user has not yet been loaded?
	* 						Typically this should be left as false and you should make sure
	* 						you load users ahead of time with load_users()
	* @return string
	*/
	public function get_username($user_id, $mode, $guest_username = false, $custom_profile_url = false, $query = false)
	{
		if (!($user = $this->get_user($user_id, $query)))
		{
			return '';
		}

		return get_username_string($mode, $user['user_id'], $user['username'], $user['user_colour'], $guest_username, $custom_profile_url);
	}

	/**
	* Get avatar
	*
	* @param int $user_id User ID of the user you want to retrieve the avatar for
	* @param bool $query Should we query the database if this user has not yet been loaded?
	* 						Typically this should be left as false and you should make sure
	* 						you load users ahead of time with load_users()
	* @param bool @lazy If true, will be lazy loaded (requires JS)
	* @return string
	*/
	public function get_avatar($user_id, $query = false, $lazy = false)
	{
		if (!($user = $this->get_user($user_id, $query)))
		{
			return '';
		}

		$row = array(
			'avatar'		=> $user['user_avatar'],
			'avatar_type'	=> $user['user_avatar_type'],
			'avatar_width'	=> $user['user_avatar_width'],
			'avatar_height'	=> $user['user_avatar_height'],
		);

		return phpbb_get_avatar($row, 'USER_AVATAR', false, $lazy);
	}

	/**
	* Get rank
	*
	* @param int $user_id User ID of the user you want to retreive the rank for
	* @param bool $query Should we query the database if this user has not yet been loaded?
	* 						Typically this should be left as false and you should make sure
	* 						you load users ahead of time with load_users()
	* @return array Array with keys 'rank_title', 'rank_img', and 'rank_img_src'
	*/
	public function get_rank($user_id, $query = false)
	{
		if (!($user = $this->get_user($user_id, $query)))
		{
			return '';
		}

		if (!function_exists('phpbb_get_user_rank'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		}

		$rank = array(
			'rank_title',
			'rank_img',
			'rank_img_src',
		);

		$user_rank_data = phpbb_get_user_rank($user, (($user['user_id'] == ANONYMOUS) ? false : $user['user_posts']));
		$rank['rank_title'] = $user_rank_data['title'];
		$rank['rank_img'] = $user_rank_data['img'];
		$rank['rank_img_src'] = $user_rank_data['img_src'];

		return $rank;
	}
}
