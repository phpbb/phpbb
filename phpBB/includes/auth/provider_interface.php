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
	 */
	public function __construct(phpbb_request $request, dbal $db, phpbb_config_db $config);

	/**
	 * Gets the current configuration of the provider.
	 */
	public function get_configuration();

	/**
	 * This function as implemented, should process the initial login stage if
	 * more than one exists.
	 *
	 * @param boolean $admin Whether reauthentication is being requested for administrative login. This can be prevented by disabling admin reauthentication on the ACP.
	 */
	public function process($admin);
}
