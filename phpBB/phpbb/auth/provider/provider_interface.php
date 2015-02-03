<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\auth\provider;

/**
* The interface authentication provider classes have to implement.
*/
interface provider_interface
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
	 *					A fourth key of the array may be present:
	 *					'redirect_data'	This key is only used when 'status' is
	 *					equal to LOGIN_SUCCESS_LINK_PROFILE and its value is an
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
	 *							then its value should be a string that is used
	 *							to designate the name of the loop used in the
	 *							ACP template file. When this is present, an
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
	* Returns an array of data necessary to build custom elements on the login
	* form.
	*
	* @return	array|null	If this function is not implemented on an auth
	*						provider then it returns null. If it is implemented
	*						it will return an array of up to four elements of
	*						which only 'TEMPLATE_FILE'. If 'BLOCK_VAR_NAME' is
	*						present then 'BLOCK_VARS' must also be present in
	*						the array. The fourth element 'VARS' is also
	*						optional. The array, with all four elements present
	*						looks like the following:
	*						array(
	*							'TEMPLATE_FILE'		=> string,
	*							'BLOCK_VAR_NAME'	=> string,
	*							'BLOCK_VARS'		=> array(...),
	*							'VARS'				=> array(...),
	*						)
	*/
	public function get_login_data();

	/**
	 * Performs additional actions during logout.
	 *
	 * @param 	array	$data			An array corresponding to
	 *									\phpbb\session::data
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
	* @param	array	$login_link_data	Any data needed to link a phpBB account to
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

	/**
	* Returns an array of data necessary to build the ucp_auth_link page
	*
	* @param int $user_id User ID for whom the data should be retrieved.
	*						defaults to 0, which is not a valid ID. The method
	*						should fall back to the current user's ID in this
	*						case.
	* @return	array|null	If this function is not implemented on an auth
	*						provider then it returns null. If it is implemented
	*						it will return an array of up to four elements of
	*						which only 'TEMPLATE_FILE'. If 'BLOCK_VAR_NAME' is
	*						present then 'BLOCK_VARS' must also be present in
	*						the array. The fourth element 'VARS' is also
	*						optional. The array, with all four elements present
	*						looks like the following:
	*						array(
	*							'TEMPLATE_FILE'		=> string,
	*							'BLOCK_VAR_NAME'	=> string,
	*							'BLOCK_VARS'		=> array(...),
	*							'VARS'				=> array(...),
	*						)
	*/
	public function get_auth_link_data($user_id = 0);

	/**
	* Unlinks an external account from a phpBB account.
	*
	* @param	array	$link_data	Any data needed to unlink a phpBB account
	*								from a phpbb account.
	*/
	public function unlink_account(array $link_data);
}
