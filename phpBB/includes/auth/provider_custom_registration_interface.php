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
* This interface defines the functions for a custom registration form.
*
* @package auth
*/
interface phpbb_auth_provider_custom_registration_interface
{
	/**
	 * Generates the registration box.
	 *
	 * @param phpbb_template $template
	 */
	public function generate_registration(phpbb_template $template);
}
