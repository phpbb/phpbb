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

namespace phpbb\avatar;

class manager
{
	/**
	* phpBB configuration
	* @var \phpbb\config\config
	*/
	protected $config;

	/**
	* Array that contains a list of enabled drivers
	* @var array
	*/
	static protected $enabled_drivers = false;

	/**
	* Array that contains all available avatar drivers which are passed via the
	* service container
	* @var array
	*/
	protected $avatar_drivers;

	/**
	* Default avatar data row
	* @var array
	*/
	static protected $default_row = array(
		'avatar'		=> '',
		'avatar_type'	=> '',
		'avatar_width'	=> 0,
		'avatar_height'	=> 0,
	);

	/**
	* Construct an avatar manager object
	*
	* @param \phpbb\config\config $config phpBB configuration
	* @param array $avatar_drivers Avatar drivers passed via the service container
	*/
	public function __construct(\phpbb\config\config $config, $avatar_drivers)
	{
		$this->config = $config;
		$this->register_avatar_drivers($avatar_drivers);
	}

	/**
	* Register avatar drivers
	*
	* @param array $avatar_drivers Service collection of avatar drivers
	*/
	protected function register_avatar_drivers($avatar_drivers)
	{
		if (!empty($avatar_drivers))
		{
			foreach ($avatar_drivers as $driver)
			{
				$this->avatar_drivers[$driver->get_name()] = $driver;
			}
		}
	}

	/**
	* Get the driver object specified by the avatar type
	*
	* @param string $avatar_type Avatar type; by default an avatar's service container name
	* @param bool $load_enabled Load only enabled avatars
	*
	* @return object Avatar driver object
	*/
	public function get_driver($avatar_type, $load_enabled = true)
	{
		if (self::$enabled_drivers === false)
		{
			$this->load_enabled_drivers();
		}

		$avatar_drivers = ($load_enabled) ? self::$enabled_drivers : $this->get_all_drivers();

		// Legacy stuff...
		switch ($avatar_type)
		{
			case AVATAR_GALLERY:
				$avatar_type = 'avatar.driver.local';
			break;
			case AVATAR_UPLOAD:
				$avatar_type = 'avatar.driver.upload';
			break;
			case AVATAR_REMOTE:
				$avatar_type = 'avatar.driver.remote';
			break;
		}

		if (!isset($avatar_drivers[$avatar_type]))
		{
			return null;
		}

		/*
		* There is no need to handle invalid avatar types as the following code
		* will cause a ServiceNotFoundException if the type does not exist
		*/
		$driver = $this->avatar_drivers[$avatar_type];

		return $driver;
	}

	/**
	* Load the list of enabled drivers
	* This is executed once and fills self::$enabled_drivers
	*/
	protected function load_enabled_drivers()
	{
		if (!empty($this->avatar_drivers))
		{
			self::$enabled_drivers = array();
			foreach ($this->avatar_drivers as $driver)
			{
				if ($this->is_enabled($driver))
				{
					self::$enabled_drivers[$driver->get_name()] = $driver->get_name();
				}
			}
			asort(self::$enabled_drivers);
		}
	}

	/**
	* Get a list of all avatar drivers
	*
	* As this function will only be called in the ACP avatar settings page, it
	* doesn't make much sense to cache the list of all avatar drivers like the
	* list of the enabled drivers.
	*
	* @return array Array containing a list of all avatar drivers
	*/
	public function get_all_drivers()
	{
		$drivers = array();

		if (!empty($this->avatar_drivers))
		{
			foreach ($this->avatar_drivers as $driver)
			{
				$drivers[$driver->get_name()] = $driver->get_name();
			}
			asort($drivers);
		}

		return $drivers;
	}

	/**
	* Get a list of enabled avatar drivers
	*
	* @return array Array containing a list of the enabled avatar drivers
	*/
	public function get_enabled_drivers()
	{
		if (self::$enabled_drivers === false)
		{
			$this->load_enabled_drivers();
		}

		return self::$enabled_drivers;
	}

	/**
	* Strip out user_, group_, or other prefixes from array keys
	*
	* @param array	$row			User data or group data
	* @param string $prefix			Prefix of data keys (e.g. user), should not include the trailing underscore
	*
	* @return array	User or group data with keys that have been
	*			stripped from the preceding "user_" or "group_"
	*			Also the group id is prefixed with g, when the prefix group is removed.
	*/
	static public function clean_row($row, $prefix = '')
	{
		// Upon creation of a user/group $row might be empty
		if (empty($row))
		{
			return self::$default_row;
		}

		$output = array();
		foreach ($row as $key => $value)
		{
			$key = preg_replace("#^(?:{$prefix}_)#", '', $key);
			$output[$key] = $value;
		}

		if ($prefix === 'group' && isset($output['id']))
		{
			$output['id'] = 'g' . $output['id'];
		}

		return $output;
	}

	/**
	* Clean driver names that are returned from template files
	* Underscores are replaced with dots
	*
	* @param string $name Driver name
	*
	* @return string Cleaned driver name
	*/
	static public function clean_driver_name($name)
	{
		return str_replace(array('\\', '_'), '.', $name);
	}

	/**
	* Prepare driver names for use in template files
	* Dots are replaced with underscores
	*
	* @param string $name Clean driver name
	*
	* @return string Prepared driver name
	*/
	static public function prepare_driver_name($name)
	{
		return str_replace('.', '_', $name);
	}

	/**
	* Check if avatar is enabled
	*
	* @param object $driver Avatar driver object
	*
	* @return bool True if avatar is enabled, false if it's disabled
	*/
	public function is_enabled($driver)
	{
		$config_name = $driver->get_config_name();

		return $this->config["allow_avatar_{$config_name}"];
	}

	/**
	* Get the settings array for enabling/disabling an avatar driver
	*
	* @param object $driver Avatar driver object
	*
	* @return array Array of configuration options as consumed by acp_board
	*/
	public function get_avatar_settings($driver)
	{
		$config_name = $driver->get_config_name();

		return array(
			'allow_avatar_' . $config_name	=> array('lang' => 'ALLOW_' . strtoupper(str_replace('\\', '_', $config_name)),		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
		);
	}

	/**
	* Replace "error" strings with their real, localized form
	*
	* @param \phpbb\user phpBB User object
	* @param array	$error Array containing error strings
	*        Key values can either be a string with a language key or an array
	*        that will be passed to vsprintf() with the language key in the
	*        first array key.
	*
	* @return array Array containing the localized error strings
	*/
	public function localize_errors(\phpbb\user $user, $error)
	{
		foreach ($error as $key => $lang)
		{
			if (is_array($lang))
			{
				$lang_key = array_shift($lang);
				$error[$key] = vsprintf($user->lang($lang_key), $lang);
			}
			else
			{
				$error[$key] = $user->lang("$lang");
			}
		}

		return $error;
	}

	/**
	* Handle deleting avatars
	*
	* @param \phpbb\db\driver\driver_interface $db phpBB dbal
	* @param \phpbb\user    $user phpBB user object
	* @param array          $avatar_data Cleaned user data containing the user's
	*                               avatar data
	* @param string         $table Database table from which the avatar should be deleted
	* @param string         $prefix Prefix of user data columns in database
	* @return null
	*/
	public function handle_avatar_delete(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $avatar_data, $table, $prefix)
	{
		if ($driver = $this->get_driver($avatar_data['avatar_type']))
		{
			$driver->delete($avatar_data);
		}

		$result = $this->prefix_avatar_columns($prefix, self::$default_row);

		$sql = 'UPDATE ' . $table . '
			SET ' . $db->sql_build_array('UPDATE', $result) . '
			WHERE ' . $prefix . 'id = ' . (int) $avatar_data['id'];
		$db->sql_query($sql);

		// Make sure we also delete this avatar from the users
		if ($prefix === 'group_')
		{
			$result = $this->prefix_avatar_columns('user_', self::$default_row);

			$sql = 'UPDATE ' . USERS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $result) . "
				WHERE user_avatar = '" . $db->sql_escape($avatar_data['avatar']) . "'";
			$db->sql_query($sql);
		}
	}

	/**
	 * Prefix avatar columns
	 *
	 * @param string $prefix Column prefix
	 * @param array $data Column data
	 *
	 * @return array Column data with prefixed column names
	 */
	public function prefix_avatar_columns($prefix, $data)
	{
		foreach ($data as $key => $value)
		{
			$data[$prefix . $key] = $value;
			unset($data[$key]);
		}

		return $data;
	}
}
