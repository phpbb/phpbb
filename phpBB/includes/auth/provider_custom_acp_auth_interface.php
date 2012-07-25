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
interface phpbb_auth_provider_custom_acp_auth_interface
{
	/**
	 * $submit and $err should be used to check if config values were changed.
	 *
	 * @param phpbb_template $template
	 * @param phpbb_config $new_config The new configuration that is to be modified during the course of acp_auth
	 * @param boolean $submit Whether the user has submitted the acp_auth form
	 * @param boolean $err Whether there is an error on the acp_auth page
	 * @return string|boolean Template string on success|false on failure
	 */
	public function generate_acp_options(phpbb_template $template, phpbb_config $new_config, $submit, $err);
}
