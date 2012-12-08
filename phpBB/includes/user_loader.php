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
* storing in them in a temporary cache so we do not
* have to query the same user multiple times in
* different services.
*/
class phpbb_user_loader
{
	/** @var dbal */
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
	* @param dbal $db A database connection
	* @param string $phpbb_root_path Path to the phpbb includes directory.
	* @param string $php_ext php file extension
	* @param string $users_table The name of the database table (phpbb_users)
	*/
	public function __construct(dbal $db, $phpbb_root_path, $php_ext, $users_table)
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
	*/
	public function load_users(array $user_ids)
	{
		$user_ids[] = ANONYMOUS;

		// Load the users
		$user_ids = array_unique($user_ids);

		// Do not load users we already have in $this->users
		$user_ids = array_diff($user_ids, array_keys($this->users));

		if (sizeof($user_ids))
		{
			$sql = 'SELECT *
				FROM ' . $this->users_table . '
				WHERE ' . $this->db->sql_in_set('user_id', $user_ids);
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->users[$row['user_id']] = $row;
			}
			$this->db->sql_freeresult($result);
		}
	}

	/**
	* Get a user row from our users cache
	*
	* @param int $user_id
	* @return array|bool Row from the database of the user or Anonymous if the user wasn't loaded/does not exist
	* 						or bool False if the anonymous user was not loaded
	*/
	public function get_user($user_id)
	{
		return (isset($this->users[$user_id])) ? $this->users[$user_id] : (isset($this->users[ANONYMOUS]) ? $this->users[ANONYMOUS] : false);
	}

	/**
	* Get avatar
	*
	* @param int $user_id
	* @return string
	*/
	public function get_avatar($user_id)
	{
		if (!($user = $this->get_user($user_id)))
		{
			return '';
		}

		if (!function_exists('get_user_avatar'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		}

		return get_user_avatar($user['user_avatar'], $user['user_avatar_type'], $user['user_avatar_width'], $user['user_avatar_height']);
	}
}
