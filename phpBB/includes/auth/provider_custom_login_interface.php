<?php
/**
*
* @package auth
* @copyright (c) 2012 phpBB Group
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
* This interface defines the functions for a custom login box
*
* @package auth
*/
interface phpbb_auth_provider_custom_login_interface {
	/**
	 * Generates a rendered template for use in login.
	 *
	 * @param phpbb_template $template
	 * @param string $redirect The location where the script should redirect the user to following execution.
	 * @param boolean $admin Whether reauthentication is the goal or not.
	 * @param boolean $s_display Whether this is a full login box or not.
	 * @return string|null On success, returns the rendered template $tpl; on failure, returns null.
	 */
	public function generate_login_box(phpbb_template $template, $redirect, $admin, $s_display);
}
