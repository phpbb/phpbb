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
* Base class for avatar drivers
* @package avatars
*/
abstract class phpbb_avatar_driver
{
	/**
	* Current board configuration
	* @type phpbb_config
	*/
	protected $config;
	
	/**
	* Current $phpbb_root_path
	* @type string
	*/
	protected $phpbb_root_path;
	
	/**
	* Current $phpEx
	* @type string
	*/
	protected $phpEx;
	
	/**
	* A cache driver
	* @type phpbb_cache_driver_interface
	*/
	protected $cache;

	/**
	* @TODO
	*/
	const FROM_USER = 0;
	const FROM_GROUP = 1;
	
	/**
	* This flag should be set to true if the avatar requires a nonstandard image
	* tag, and will generate the html itself.
	* @type boolean
	*/
	public $custom_html = false;

	/**
	* Construct an driver object
	*
	* @param $config The phpBB configuration
	* @param $phpbb_root_path The path to the phpBB root
	* @param $phpEx The php file extension
	* @param $cache A cache driver
	*/
	public function __construct(phpbb_config $config, $phpbb_root_path, $phpEx, phpbb_cache_driver_interface $cache = null)
	{
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->cache = $cache;
	}

	/**
	* Get the avatar url and dimensions
	*
	* @param $ignore_config Whether this function should respect the users prefs
	*        and board configuration configuration option, or should just render
	*        the avatar anyways. Useful for the ACP.
	* @return array Avatar data
	*/
	public function get_data($row, $ignore_config = false)
	{
		return array(
			'src' => '',
			'width' => 0,
			'height' => 0,
		);
	}

	/**
	* Returns custom html for displaying this avatar.
	* Only called if $custom_html is true.
	*
	* @param $ignore_config Whether this function should respect the users prefs
	*        and board configuration configuration option, or should just render
	*        the avatar anyways. Useful for the ACP.
	* @return string HTML
	*/
	public function get_custom_html($row, $ignore_config = false)
	{
		return '';
	}

	/**
	* @TODO
	**/
	public function prepare_form($template, $row, &$error, &$override_focus)
	{
		return false;
	}

	/**
	* @TODO
	**/
	public function process_form($template, $row, &$error)
	{
		return false;
	}

	/**
	* @TODO
	**/
	public function delete($row)
	{
		return true;
	}

	/**
	* @TODO
	**/
	public static function clean_row($row, $src = phpbb_avatar_driver::FROM_USER)
	{
		$return = array();
		$prefix = false;
		
		if ($src == phpbb_avatar_driver::FROM_USER)
		{
			$prefix = 'user_';
		}
		else if ($src == phpbb_avatar_driver::FROM_GROUP)
		{
			$prefix = 'group_';
		}

		if ($prefix)
		{
			$len = strlen($prefix);
			foreach ($row as $key => $val)
			{
				$sub = substr($key, 0, $len);
				if ($sub == $prefix)
				{
					$return[substr($key, $len)] = $val;
				}
				else
				{
					$return[$key] = $val;
				}
			}
		}
		else
		{
			$return = $row;
		}

		return $return;
	}
}
