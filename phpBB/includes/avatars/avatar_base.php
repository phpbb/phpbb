<?php
/**
*
* @package avatars
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
* Base class for avatar modules
* @package avatars
*/
class phpbb_avatar_base
{
	/**
	* User data this avatar may use to generate a url or html
	* @type array
	*/
	private $user_row;
	
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
	public function __construct(&$user_row)
	{
		$this->user_row = $user_row;
	}

	/**
	* Get the avatar url and dimensions
	*
	* @param $ignore_config Whether $user or global avatar visibility settings
	*        should be ignored
	* @return array Avatar data
	*/
	public function get_data($ignore_config = false)
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
	public function get_custom_html($ignore_config = false)
	{
		return '';
	}
}
