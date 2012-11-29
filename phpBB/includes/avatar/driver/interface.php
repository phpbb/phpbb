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
	* Returns the name of the driver.
	*
	* @return string	Name of wrapped driver.
	*/
	public function get_name();

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
	* Returns custom html if it is needed for displaying this avatar
	*
	* @param bool $ignore_config Whether this function should respect the users prefs
	*        and board configuration configuration option, or should just render
	*        the avatar anyways. Useful for the ACP.
	* @return string HTML
	*/
	public function get_custom_html($row, $ignore_config = false, $alt = '');

	/**
	* Prepare form for changing the settings of this avatar
	*
	* @param object	$template Template object
	* @param array	$row User data or group data that has been cleaned with 
	*        phpbb_avatar_manager::clean_row
	* @param array	&$error Reference to an error array
	*
	* @return bool True if form has been successfully prepared
	*/
	public function prepare_form($template, $row, &$error);

	/**
	* Prepare form for changing the acp settings of this avatar
	*
	* @return array Array containing the acp settings
	*/
	public function prepare_form_acp();

	/**
	* Process form data
	*
	* @param object	$template Template object
	* @param array	$row User data or group data that has been cleaned with 
	*        phpbb_avatar_manager::clean_row
	* @param array	&$error Reference to an error array
	*
	* @return array Array containing the avatar data as follows:
	*        ['avatar'], ['avatar_width'], ['avatar_height']
	*/
	public function process_form($template, $row, &$error);

	/**
	* Delete avatar
	*
	* @param array $row User data or group data that has been cleaned with 
	*        phpbb_avatar_manager::clean_row
	*
	* @return bool True if avatar has been deleted or there is no need to delete
	*/
	public function delete($row);

	/**
	* Check if avatar is enabled
	*
	* @return bool True if avatar is enabled, false if it's disabled
	*/
	public function is_enabled();

	/**
	* Get the avatars template name
	*
	* @return string Avatar's template name
	*/
	public function get_template_name();
}
