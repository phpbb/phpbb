<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
* @package phpBB3
*/
interface phpbb_avatar_driver_interface
{
	/**
	* Returns the name of the driver.
	*
	* @return string	Name of driver.
	*/
	public function get_name();

	/**
	* Get the avatar url and dimensions
	*
	* @param array	$row User data or group data that has been cleaned with 
	*        phpbb_avatar_manager::clean_row
	* @return array Avatar data, must have keys src, width and height, e.g.
	*        ['src' => '', 'width' => 0, 'height' => 0]
	*/
	public function get_data($row);

	/**
	* Returns custom html if it is needed for displaying this avatar
	*
	* @param phpbb_user $user phpBB user object
	* @param array	$row User data or group data that has been cleaned with 
	*        phpbb_avatar_manager::clean_row
	* @param string $alt Alternate text for avatar image
	*
	* @return string HTML
	*/
	public function get_custom_html($user, $row, $alt = '');

	/**
	* Prepare form for changing the settings of this avatar
	*
	* @param phpbb_request $request Request object
	* @param phpbb_template	$template Template object
	* @param phpbb_user $user User object
	* @param array	$row User data or group data that has been cleaned with 
	*        phpbb_avatar_manager::clean_row
	* @param array	&$error Reference to an error array that is filled by this
	*        function. Key values can either be a string with a language key or
	*        an array that will be passed to vsprintf() with the language key in
	*        the first array key.
	*
	* @return bool True if form has been successfully prepared
	*/
	public function prepare_form($request, $template, $user, $row, &$error);

	/**
	* Prepare form for changing the acp settings of this avatar
	*
	* @param phpbb_user $user phpBB user object
	*
	* @return array Array of configuration options as consumed by acp_board.
	*        The setting for enabling/disabling the avatar will be handled by
	*        the avatar manager.
	*/
	public function prepare_form_acp($user);

	/**
	* Process form data
	*
	* @param phpbb_request $request Request object
	* @param phpbb_template	$template Template object
	* @param phpbb_user $user User object
	* @param array	$row User data or group data that has been cleaned with 
	*        phpbb_avatar_manager::clean_row
	* @param array	&$error Reference to an error array that is filled by this
	*        function. Key values can either be a string with a language key or
	*        an array that will be passed to vsprintf() with the language key in
	*        the first array key.
	*
	* @return array Array containing the avatar data as follows:
	*        ['avatar'], ['avatar_width'], ['avatar_height']
	*/
	public function process_form($request, $template, $user, $row, &$error);

	/**
	* Delete avatar
	*
	* @param array $row User data or group data that has been cleaned with 
	*        phpbb_avatar_manager::clean_row
	*
	* @return bool True if avatar has been deleted or there is no need to delete,
	*        i.e. when the avatar is not hosted locally.
	*/
	public function delete($row);

	/**
	* Get the avatar driver's template name
	*
	* @return string Avatar driver's template name
	*/
	public function get_template_name();
}
