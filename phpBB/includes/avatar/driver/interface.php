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
* Interface for avatar drivers
* @package avatars
*/
interface phpbb_avatar_driver_interface
{
	/**
	* Get the avatar url and dimensions
	*
	* @param $ignore_config Whether this function should respect the users prefs
	*        and board configuration configuration option, or should just render
	*        the avatar anyways. Useful for the ACP.
	* @return array Avatar data, must have keys src, width and height, e.g.
	*         ['src' => '', 'width' => 0, 'height' => 0]
	*/
	public function get_data($row, $ignore_config = false);

	/**
	* Returns custom html for displaying this avatar.
	* Only called if $custom_html is true.
	*
	* @param $ignore_config Whether this function should respect the users prefs
	*        and board configuration configuration option, or should just render
	*        the avatar anyways. Useful for the ACP.
	* @return string HTML
	*/
	public function get_custom_html($row, $ignore_config = false);

	/**
	* @TODO
	**/
	public function prepare_form($template, $row, &$error);

	/**
	* @TODO
	**/
	public function process_form($template, $row, &$error);

	/**
	* @TODO
	**/
	public function delete($row);

	/**
	* @TODO
	**/
	public function is_enabled();

	/**
	* @TODO
	**/
	public function get_template_name();
}
