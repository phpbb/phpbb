<?php
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
interface phpbb_auth_provider_interface
{
	/**
	 * Checks whether the user is currently identified to the authentication
	 * provider.
	 * Called in acp_board while setting authentication plugins.
	 * Changing to an authentication provider will not be permitted in acp_board
	 * if there is an error.
	 *
	 * @return 	boolean|string 	False if the user is identified, otherwise an
	 *							error message, or null if not implemented.
	 */
	public function init();

	/**
	 * Performs login.
	 *
	 * @param	string	$username 	The name of the user being authenticated.
	 * @param	string	$password	The password of the user.
	 * @return	array	An associative array of the format:
	 *						array(
	 *							'status' => status constant
	 *							'error_msg' => string
	 *							'user_row' => array
	 *						)
	 */
	public function login($username, $password);

	/**
	 * Autologin function
	 *
	 * @return 	array|null	containing the user row, empty if no auto login
	 * 						should take place, or null if not impletmented.
	 */
	public function autologin();

	/**
	 * This function is used to output any required fields in the authentication
	 * admin panel. It also defines any required configuration table fields.
	 *
	 * @param 	array 	$new 	Contains the new configuration values that have
	 * 							been set in acp_board.
	 * @return	array|null	Returns null if not implemented or an array of the
	 *						form:
	 *							array(
	 *								'tpl'		=> string
	 *								'config' 	=> array
	 *							)
	 */
	public function acp($new);

	/**
	 * This function updates the template with variables related to the acp
	 * options with whatever configuraton values are passed to it as an array.
	 * It then returns the name of the acp file related to this authentication
	 * provider.
	 * @param	array	$new_config Contains the new configuration values that
	 *								have been set in acp_board.
	 * @return	string|null		Returns null if not implemented or a string
	 *							containing the name of the acp tempalte file for
	 *							the authentication provider.
	 */
	public function get_acp_template($new_config);

	/**
	 * Performs additional actions during logout.
	 *
	 * @param 	array	$data			An array corresponding to
	 *									phpbb_session::data
	 * @param 	boolean	$new_session	True for a new session, false for no new
	 *									session.
	 */
	public function logout($data, $new_session);

	/**
	 * The session validation function checks whether the user is still logged
	 * into phpBB.
	 *
	 * @param 	array 	$user
	 * @return 	boolean	true if the given user is authenticated, false if the 
	 * 					session should be closed, or null if not implemented.
	 */
	public function validate_session($user);
}
