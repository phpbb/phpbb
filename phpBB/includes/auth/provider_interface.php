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
	 * This function as implemented, should process the start of a login request
	 * if providing third party support, it should have redirects sent to
	 * check_auth_{provider}.php in the root of the phpbb installation.
	 *
	 * @param phpbb_request $request
	 */
	public function process(phpbb_request $request);

	/**
	 * Links data from a providers auth_{provder} tables to the auth_links for
	 * easy indexing upon successful authentication.
	 */
	public function link();
}
