<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
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
* @package avatar
*/
class phpbb_avatar_manager
{
	/**
	* phpBB configuration
	* @var phpbb_config
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
	* Service container object
	* @var object
	*/
	protected $container;

	/**
	* Construct an avatar manager object
	*
	* @param phpbb_config $config phpBB configuration
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
		$driver = $this->container->get($avatar_type);

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
	* Strip out user_ and group_ prefixes from keys
	*
	* @param array	$row User data or group data
	*
	* @return array User data or group data with keys that have been
	*        stripped from the preceding "user_" or "group_"
	*/
	static public function clean_row($row)
	{
		$keys = array_keys($row);
		$values = array_values($row);

		$keys = array_map(array('phpbb_avatar_manager', 'strip_prefix'), $keys);

		return array_combine($keys, $values);
	}

	/**
	* Strip prepending user_ or group_ prefix from key
	*
	* @param string Array key
	* @return string Key that has been stripped from its prefix
	*/
	static protected function strip_prefix($key)
	{
		return preg_replace('#^(?:user_|group_)#', '', $key);
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

	/**
	* Replace "error" strings with their real, localized form
	*
	* @param phpbb_user phpBB User object
	* @param array	$error Array containing error strings
	*        Key values can either be a string with a language key or an array
	*        that will be passed to vsprintf() with the language key in the
	*        first array key.
	*
	* @return array Array containing the localized error strings
	*/
	public function localize_errors(phpbb_user $user, $error)
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
}
