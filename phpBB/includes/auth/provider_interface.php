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
	/**
	 * Checks whether the user is currently identified to the authentication
	 * provider.
	 * Called in acp_board while setting authentication plugins.
	 *
	 * @return 	boolean|string 	False if the user is identified, otherwise an
	 *							error message.
	 */
	public function init();

	/**
	 * Performs login.
	 *
	 * @param 	$username 	string 	The name of the user being authenticated.
	 * @param 	$password 	string 	The password of the user.
	 * @return 	array 		An associative array of the format:
	 *							array(
	 *								'status' => status constant
	 *								'error_msg' => string
	 *								'user_row' => array
	 *							)
	 */
	public function login($username, $password);

	/**
	 * Autologin function
	 *
	 * @return 	array 	containing the user row or empty if no auto login should
	 * 					take place
	 */
	public function autologin();

	/**
	 * This function is used to output any required fields in the authentication
	 * admin panel. It also defines any required configuration table fields.
	 */
	public function acp();
}
