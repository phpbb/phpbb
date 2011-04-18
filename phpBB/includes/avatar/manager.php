<?php
/**
*
* @package avatar
* @copyright (c) 2010 phpBB Group
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
* @package acm
*/
class phpbb_avatar_manager
{
	private $phpbb_root_path;
	private $php_ext;
	private $config;
	private $cache;
	private static $valid_drivers = false;

	public function __construct($phpbb_root_path, $php_ext = '.php', phpbb_config $config, phpbb_cache_driver_interface $cache = null)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->config = $config;
		$this->cache = $cache;
	}

	public function get_singleton($avatar_type)
	{
		if (self::$valid_drivers === false)
		{
			$this->load_valid_drivers();
		}

		if (isset(self::$valid_drivers[$avatar_type]))
		{
			if (!is_object(self::$valid_drivers[$avatar_type]))
			{
				$class_name = 'phpbb_avatar_driver_' . $avatar_type;
				self::$valid_drivers[$avatar_type] = new $class_name($this->config, $this->phpbb_root_path, $this->php_ext);
			}

			return self::$valid_drivers[$avatar_type];
		}
		else
		{
			return null;
		}
	}

	private function load_valid_drivers()
	{
		require_once($this->phpbb_root_path . 'includes/avatar/driver.' . $this->php_ext);

		if ($this->cache)
		{
			self::$valid_drivers = $this->cache->get('avatar_drivers');
		}

		if (empty($this->valid_drivers))
		{
			self::$valid_drivers = array();

			$iterator = new DirectoryIterator($this->phpbb_root_path . 'includes/avatar/driver');

			foreach ($iterator as $file)
			{
				if (preg_match("/^(.*)\.{$this->php_ext}$/", $file, $match))
				{
					self::$valid_drivers[] = $match[1];
				}
			}

			self::$valid_drivers = array_flip(self::$valid_drivers);

			if ($this->cache)
			{
				$this->cache->put('avatar_drivers', self::$valid_drivers);
			}
		}
	}
}
