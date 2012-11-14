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
	private $phpbb_root_path;
	private $phpEx;
	private $config;
	private $request;
	private $cache;
	private static $valid_drivers = false;
	private $tasks;
	private $container;

	/**
	* @TODO
	**/
	public function __construct($phpbb_root_path, $phpEx, phpbb_config $config, phpbb_request $request, phpbb_cache_driver_interface $cache, $tasks, $container)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->config = $config;
		$this->request = $request;
		$this->cache = $cache;
		$this->tasks = $tasks;
		$this->container = $container;
	}

	/**
	* @TODO
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
				$avatar_type = 'avatar.driver.core.local';
				break;
			case AVATAR_UPLOAD:
				$avatar_type = 'avatar.driver.core.upload';
				break;
			case AVATAR_REMOTE:
				$avatar_type = 'avatar.driver.core.remote';
				break;
		}

		if (false === array_search($avatar_type, self::$valid_drivers))
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
	* @TODO
	**/
	private function load_valid_drivers()
	{
		if (!empty($this->tasks))
		{
			self::$valid_drivers = array();
			foreach ($this->tasks as $driver)
			{
				self::$valid_drivers[] = $driver->get_name();
			}
		}
	}

	/**
	* @TODO
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
