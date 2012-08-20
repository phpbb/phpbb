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
 * This interface defines functions for providers that require initialization
 * when set via acp_auth
 *
 * @package auth
 */
interface phpbb_auth_interface_provider_acp_init
{
	/**
	 * Checks whether the user is identified to apache
	 * Called in acp_board while setting authentication plugins
	 *
	 * @throws phpbb_auth_exception On failure
	 */
	public function init();
}
