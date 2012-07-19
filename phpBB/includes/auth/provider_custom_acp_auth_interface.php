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
interface phpbb_auth_provider_custom_acp_auth_interface {
	/**
	 * $submit and $err should be used to check if config values were changed.
	 *
	 * @param phpbb_template $template
	 * @param phpbb_config $new_config
	 * @param boolean $submit
	 * @param boolean $err
	 * @return string|boolean
	 */
	public function generate_acp_options(phpbb_template $template, phpbb_config $new_config, $submit, $err);
}
