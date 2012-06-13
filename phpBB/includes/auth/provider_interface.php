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
	 * This function as implemented, should process the start of a login or
	 * registration request if providing third party support, it should have
	 * redirects sent to check_auth_{provider}.php in the root of the phpbb
	 * installation.
	 */
	public function process();

	/**
	 * This function should verify any data that is submitted to the provider
	 * either from a user or from an external source. It should then ask for
	 * more information, redirect the user to an appropriate page, display an
	 * appropriate error message, or login, register or link an account to a
	 * verified provider source.
	 */
	public function verify();

	/**
	 * This function should perform a phpBB login including the creation of a
	 * new session. After this function runs, a redirect should occur to
	 * wherever the login action originated from.
	 */
	function login();

	/**
	 * This function should perform a phpBB account registration with the data
	 * provider by an external (or in the case of Olympus, internal)
	 * authentication provider. Upon completion, it should redirect the user
	 * according to board preferences.
	 */
	function register();

	/**
	 * This function should link an existing account to a third party provider
	 * that a user wishes to use in order to login at a later date. Security
	 * of this request should not be handled in this function but rather should
	 * be delegated to process() or verify() to confirm that the user is indeed
	 * starting the action. Re-authentication before the linking of an account
	 * to a provider is reccomended.
	 */
	function link();
}
