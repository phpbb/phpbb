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
	private $extension_manager;
	private $cache;
	private static $valid_drivers = false;

	/**
	* @TODO
	**/
	public function __construct($phpbb_root_path, $phpEx, phpbb_config $config, phpbb_request $request, phpbb_extension_manager $extension_manager, phpbb_cache_driver_interface $cache = null)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->config = $config;
		$this->request = $request;
		$this->extension_manager = $extension_manager;
		$this->cache = $cache;
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
				$avatar_type = 'phpbb_avatar_driver_local';
				break;
			case AVATAR_UPLOAD:
				$avatar_type = 'phpbb_avatar_driver_upload';
				break;
			case AVATAR_REMOTE:
				$avatar_type = 'phpbb_avatar_driver_remote';
				break;
		}

		if (false === array_search($avatar_type, self::$valid_drivers))
		{
			return null;
		}

		$r = new ReflectionClass($avatar_type);

		if ($r->isSubClassOf('phpbb_avatar_driver')) {
			$driver = new $avatar_type($this->config, $this->request, $this->phpbb_root_path, $this->phpEx, $this->cache);
		} else if ($r->implementsInterface('phpbb_avatar_driver')) {
			$driver = new $avatar_type();
		} else {
			$message = "Invalid avatar driver class name '%s' provided. It must implement phpbb_avatar_driver_interface.";
			trigger_error(sprintf($message, $avatar_type));
		}

		return $driver;
	}

	/**
	* @TODO
	**/
	private function load_valid_drivers()
	{
		if ($this->cache)
		{
			self::$valid_drivers = $this->cache->get('avatar_drivers');
		}

		if (empty($this->valid_drivers))
		{
			self::$valid_drivers = array();

			$finder = $this->extension_manager->get_finder();

			self::$valid_drivers = $finder
				->extension_directory('/avatar/driver/')
				->core_path('includes/avatar/driver/core/')
				->get_classes();

			if ($this->cache)
			{
				$this->cache->put('avatar_drivers', self::$valid_drivers);
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
