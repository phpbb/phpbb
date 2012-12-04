<?php
/**
*
* @package avatar
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* @package avatar
*/
class phpbb_avatar_manager
{
	protected $config;
	protected static $valid_drivers = false;
	protected $avatar_drivers;
	protected $container;

	/**
	* Construct an avatar manager object
	*
	* @param string $phpbb_root_path Path to the phpBB root
	* @param string $phpEx PHP file extension
	* @param phpbb_config $config phpBB configuration
	* @param phpbb_request $request Request object
	* @param phpbb_cache_driver_interface $cache Cache driver
	* @param array $avatar_drivers Avatar drivers passed via the service container
	* @param object $container Container object
	*/
	public function __construct(phpbb_config $config, $avatar_drivers, $container)
	{
		$this->config = $config;
		$this->avatar_drivers = $avatar_drivers;
		$this->container = $container;
	}

	/**
	* Get the driver object specified by the avatar type
	*
	* @param string $avatar_type Avatar type; by default an avatar's service container name
	* @param bool $load_valid Load only valid avatars
	*
	* @return object Avatar driver object
	*/
	public function get_driver($avatar_type, $load_valid = true)
	{
		if (self::$valid_drivers === false)
		{
			$this->load_valid_drivers();
		}

		$avatar_drivers = ($load_valid) ? self::$valid_drivers : $this->get_all_drivers();

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
		$driver = $this->container->get($avatar_type);

		return $driver;
	}

	/**
	* Load the list of valid drivers
	* This is executed once and fills self::$valid_drivers
	*/
	protected function load_valid_drivers()
	{
		if (!empty($this->avatar_drivers))
		{
			self::$valid_drivers = array();
			foreach ($this->avatar_drivers as $driver)
			{
				if ($this->is_enabled($driver))
				{
					self::$valid_drivers[$driver->get_name()] = $driver->get_name();
				}
			}
			asort(self::$valid_drivers);
		}
	}

	/**
	* Get a list of all avatar drivers
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
	* Get a list of valid avatar drivers
	*
	* @return array Array containing a list of the valid avatar drivers
	*/
	public function get_valid_drivers()
	{
		if (self::$valid_drivers === false)
		{
			$this->load_valid_drivers();
		}

		return self::$valid_drivers;
	}

	/**
	* Strip out user_ and group_ prefixes from keys
	*
	* @param array	$row User data or group data
	*
	* @return array User data or group data with keys that have been
	*        stripped from the preceding "user_" or "group_"
	*/
	public static function clean_row($row)
	{
		$keys = array_keys($row);
		$values = array_values($row);

		$keys = array_map(
			function ($key)
			{
				return preg_replace('#^(?:user_|group_)#', '', $key);
			},
			$keys
		);

		return array_combine($keys, $values);
	}

	/**
	* Clean driver names that are returned from template files
	* Underscores are replaced with dots
	*
	* @param string $name Driver name
	*
	* @return string Cleaned driver name
	*/
	public static function clean_driver_name($name)
	{
		return str_replace('_', '.', $name);
	}

	/**
	* Prepare driver names for use in template files
	* Dots are replaced with underscores
	*
	* @param string $name Clean driver name
	*
	* @return string Prepared driver name
	*/
	public static function prepare_driver_name($name)
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
		$config_name = $this->get_driver_config_name($driver);

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
		$config_name = $this->get_driver_config_name($driver);

		return array(
			'allow_avatar_' . $config_name	=> array('lang' => 'ALLOW_' . strtoupper($config_name),		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
		);
	}

	/**
	* Get the config name of an avatar driver
	*
	* @param object $driver Avatar driver object
	*
	* @return string Avatar driver config name
	*/
	public function get_driver_config_name($driver)
	{
		return preg_replace('#^phpbb_avatar_driver_#', '', get_class($driver));
	}
}
