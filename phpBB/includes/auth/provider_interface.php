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
* This interface defines authentication providers.
*
* @package auth
*/
interface phpbb_auth_provider_interface
{
	/**
	 * Stores the request and db variable for later use.
	 *
	 * @param phpbb_request $request
	 * @param dbal $db
	 * @param phpbb_config_db $config
	 * @param phpbb_user $user
	 */
	public function __construct(phpbb_request $request, dbal $db, phpbb_config_db $config, phpbb_user $user);

	/**
	 * Gets the current configuration of the provider.
	 */
	public function get_configuration();

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

	/**
	 * This function as implemented, should process the initial login stage if
	 * more than one exists.
	 *
	 * @param boolean $admin Whether reauthentication is being requested for administrative login. This can be prevented by disabling admin reauthentication on the ACP.
	 */
	public function process($admin);
}
