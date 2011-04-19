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
	* @param $ignore_config Whether this function should respect the users/board
	*        configuration option, or should just render the avatar anyways.
	*        Useful for the ACP.
	* @return array Avatar data
	*/
	public function get_data($user_row, $ignore_config = false)
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
	* @param $ignore_config Whether this function should respect the users/board
	*        configuration option, or should just render the avatar anyways.
	*        Useful for the ACP.
	* @return string HTML
	*/
	public function get_custom_html($user_row, $ignore_config = false)
	{
		return '';
	}

	/**
	* @TODO
	**/
	public function handle_form($template, $user_row, &$error, $submitted = false)
	{
		return false;
	}
}
