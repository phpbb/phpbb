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
	private $cache;
	private static $valid_drivers = false;

	/**
	* @TODO
	**/
	public function __construct($phpbb_root_path, $phpEx, phpbb_config $config, phpbb_cache_driver_interface $cache = null)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->config = $config;
		$this->cache = $cache;
	}

	/**
	* @TODO
	**/
	public function get_driver($avatar_type, $new = false)
	{
		if (self::$valid_drivers === false)
		{
			$this->load_valid_drivers();
		}

		// Legacy stuff...
		switch ($avatar_type)
		{
			case AVATAR_GALLERY:
				$avatar_type = 'local';
				break;
			case AVATAR_UPLOAD:
				$avatar_type = 'upload';
				break;
			case AVATAR_REMOTE:
				$avatar_type = 'remote';
				break;
		}

		if (isset(self::$valid_drivers[$avatar_type]))
		{
			if ($new || !is_object(self::$valid_drivers[$avatar_type]))
			{
				$class_name = 'phpbb_avatar_driver_' . $avatar_type;
				self::$valid_drivers[$avatar_type] = new $class_name($this->config, $this->phpbb_root_path, $this->phpEx, $this->cache);
			}

			return self::$valid_drivers[$avatar_type];
		}
		else
		{
			return null;
		}
	}

	/**
	* @TODO
	**/
	private function load_valid_drivers()
	{
		require_once($this->phpbb_root_path . 'includes/avatar/driver.' . $this->phpEx);

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
				// Match all files that appear to be php files
				if (preg_match("/^(.*)\.{$this->phpEx}$/", $file, $match))
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

	/**
	* @TODO
	**/
	public function get_valid_drivers() {
		if (self::$valid_drivers === false)
		{
			$this->load_valid_drivers();
		}

		return array_keys(self::$valid_drivers);
	}
}
