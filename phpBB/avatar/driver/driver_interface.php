<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\avatar\driver;

/**
* Interface for avatar drivers
*/
interface driver_interface
{
	/**
	* Returns the name of the driver.
	*
	* @return string	Name of driver.
	*/
	public function get_name();

	/**
	* Returns the config name of the driver. To be used in accessing the CONFIG variables.
	*
	* @return string	Config name of driver.
	*/
	public function get_config_name();

	/**
	* Get the avatar url and dimensions
	*
	* @param array	$row User data or group data that has been cleaned with
	*        \phpbb\avatar\manager::clean_row
	* @return array Avatar data, must have keys src, width and height, e.g.
	*        ['src' => '', 'width' => 0, 'height' => 0]
	*/
	public function get_data($row);

	/**
	* Returns custom html if it is needed for displaying this avatar
	*
	* @param \phpbb\user $user phpBB user object
	* @param array	$row User data or group data that has been cleaned with
	*        \phpbb\avatar\manager::clean_row
	* @param string $alt Alternate text for avatar image
	*
	* @return string HTML
	*/
	public function get_custom_html($user, $row, $alt = '');

	/**
	* Prepare form for changing the settings of this avatar
	*
	* @param \phpbb\request\request $request Request object
	* @param \phpbb\template\template	$template Template object
	* @param \phpbb\user $user User object
	* @param array	$row User data or group data that has been cleaned with
	*        \phpbb\avatar\manager::clean_row
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
	* @param \phpbb\user $user phpBB user object
	*
	* @return array Array of configuration options as consumed by acp_board.
	*        The setting for enabling/disabling the avatar will be handled by
	*        the avatar manager.
	*/
	public function prepare_form_acp($user);

	/**
	* Process form data
	*
	* @param \phpbb\request\request $request Request object
	* @param \phpbb\template\template	$template Template object
	* @param \phpbb\user $user User object
	* @param array	$row User data or group data that has been cleaned with
	*        \phpbb\avatar\manager::clean_row
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
	*        \phpbb\avatar\manager::clean_row
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

	/**
	* Get the avatar driver's template name (ACP)
	*
	* @return string Avatar driver's template name
	*/
	public function get_acp_template_name();
}
