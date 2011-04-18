<?php
/**
*
* @package avatar
* @copyright (c) 2005, 2009 phpBB Group
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
	protected $php_ext;
	
	/**
	* This flag should be set to true if the avatar requires a nonstandard image
	* tag, and will generate the html itself.
	* @type boolean
	*/
	public $custom_html = false;

	/**
	* Construct an avatar object
	*
	* @param $user_row User data to base the avatar url/html on
	*/
	public function __construct(phpbb_config $config, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Get the avatar url and dimensions
	*
	* @param $ignore_config Whether $user or global avatar visibility settings
	*        should be ignored
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
	* @param $ignore_config Whether $user or global avatar visibility settings
	*        should be ignored
	* @return string HTML
	*/
	public function get_custom_html($user_row, $ignore_config = false)
	{
		return '';
	}
}
