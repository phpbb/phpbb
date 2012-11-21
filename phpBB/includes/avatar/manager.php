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
	protected $phpbb_root_path;
	protected $phpEx;
	protected $config;
	protected $request;
	protected $cache;
	protected static $valid_drivers = false;
	protected $avatar_drivers;
	protected $container;

	/**
	* Construct an avatar manager object
	*
	* @param $phpbb_root_path The path to the phpBB root
	* @param $phpEx The php file extension
	* @param $config The phpBB configuration
	* @param $request The request object
	* @param $cache A cache driver
	* @param $avatar_drivers The avatars drivers passed via the service container
	* @param $container The container object
	**/
	public function __construct($phpbb_root_path, $phpEx, phpbb_config $config, phpbb_request $request, phpbb_cache_driver_interface $cache, $avatar_drivers, $container)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->config = $config;
		$this->request = $request;
		$this->cache = $cache;
		$this->avatar_drivers = $avatar_drivers;
		$this->container = $container;
	}

	/**
	* Get the driver object specified by the avatar type
	*
	* @param string The avatar type; by default an avatar's service container name
	*
	* @return object The avatar driver object
	**/
	public function get_driver($avatar_type)
	{
		if (self::$valid_drivers === false)
		{
			$this->load_valid_drivers();
		}

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

		if (!isset(self::$valid_drivers[$avatar_type]))
		{
			return null;
		}

		$driver = $this->container->get($avatar_type);
		if ($driver !== false)
		{
			return $driver;
		}
		else
		{
			$message = "Invalid avatar driver class name '%s' provided.";
			trigger_error(sprintf($message, $avatar_type));
		}

		return $driver;
	}

	/**
	* Load the list of valid drivers
	* This is executed once and fills self::$valid_drivers
	**/
	protected function load_valid_drivers()
	{
		if (!empty($this->avatar_drivers))
		{
			self::$valid_drivers = array();
			foreach ($this->avatar_drivers as $driver)
			{
				self::$valid_drivers[$driver->get_name()] = $driver->get_name();
			}
		}
	}

	/**
	* Get a list of valid avatar drivers
	*
	* @return array An array containing a list of the valid avatar drivers
	**/
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
	* @param array	$row The user data or group data
	*
	* @return array The user data or group data with keys that have been
	*        stripped from the preceding "user_" or "group_"
	**/
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
}
