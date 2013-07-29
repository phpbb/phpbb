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
	 *					A fourth key of the array may be present 'redirect_data'
	 *					This key is only used when 'status' is equal to
	 *					LOGIN_SUCCESS_LINK_PROFILE and it's value is an
	 *					associative array that is turned into GET variables on
	 *					the redirect url.
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
	 * @return	array|null	Returns null if not implemented or an array of the
	 *						configuration fields of the provider.
	 */
	public function acp();

	/**
	 * This function updates the template with variables related to the acp
	 * options with whatever configuraton values are passed to it as an array.
	 * It then returns the name of the acp file related to this authentication
	 * provider.
	 * @param	array	$new_config Contains the new configuration values that
	 *								have been set in acp_board.
	 * @return	array|null		Returns null if not implemented or an array with
	 *							the template file name and an array of the vars
	 *							that the template needs that must conform to the
	 *							following example:
	 *							array(
	 *								'TEMPLATE_FILE'	=> string,
	 *								'TEMPLATE_VARS'	=> array(...),
	 *							)
	 *							An optional third element may be added to this
	 *							array: 'BLOCK_VAR_NAME'. If this is present,
	 *							then it's value should be a string that is used
	 *							to designate the name of the loop used in the
	 *							ACP template file. In addition to this, an
	 *							additional key named 'BLOCK_VARS' is required.
	 *							This must be an array containing at least one
	 *							array of variables that will be assigned during
	 *							the loop in the template. An example of this is
	 *							presented below:
	 *							array(
	 *								'BLOCK_VAR_NAME'	=> string,
	 *								'BLOCK_VARS'		=> array(
	 *									'KEY IS UNIMPORTANT' => array(...),
	 *								),
	 *								'TEMPLATE_FILE'	=> string,
	 *								'TEMPLATE_VARS'	=> array(...),
	 *							)
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

	/**
	* Checks to see if $login_link_data contains all information except for the
	* user_id of an account needed to successfully link an external account to
	* a forum account.
	*
	* @param	array	$link_data	Any data needed to link a phpBB account to
	*								an external account.
	* @return	string|null	Returns a string with a language constant if there
	*						is data missing or null if there is no error.
	*/
	public function login_link_has_necessary_data($login_link_data);

	/**
	* Links an external account to a phpBB account.
	*
	* @param	array	$link_data	Any data needed to link a phpBB account to
	*								an external account.
	*/
	public function link_account(array $link_data);
}
