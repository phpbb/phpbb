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
	 */
	public function __construct(phpbb_request $request, dbal $db);

	/**
	 * This function as implemented, should process the start of a login or
	 * registration request if providing third party support, it should have
	 * redirects sent to check_auth_{provider}.php in the root of the phpbb
	 * installation.
	 */
	public function process();
}
