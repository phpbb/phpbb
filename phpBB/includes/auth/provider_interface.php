<?
/**
*
* @package auth
* @copyright (c) 2013 phpBB Group
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
 * The interface authentication provider classes have to implement.
 *
 * @package auth
 */
class phpbb_auth_provider_interface
{
	public function init();

	public function login();

	public function autologin();

	public function acp();
}
