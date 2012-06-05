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
* This auth provider uses the OpenID form of login.
*
* @package auth
*/
class phpbb_auth_provider_open_id implements phpbb_auth_provider_interface
{
	public $id;

	/**
	 * Performs the login request on the external server specified by
	 * open_id_identifier. Red
	 *
	 * {@inheritDoc}
	 */
	public function process(phpbb_request $request)
	{
		global $db;
		$storage = new phpbb_auth_zend_storage($db);
		$this->consumer = $consumer = new Zend\OpenId\Consumer\GenericConsumer($storage);

		if($request->variable('open_id_identifier', '') == '')
		{
			throw new phpbb_auth_exception('No OpenID identifier supplied.');
		}
		else
		{
			$identifier = $request->variable('open_id_identifier', '');
		}
		$return_to = 'phpBB/check_auth_openid.php';

		// Enable super globals so Zend Framework does not throw errors.
		$request->enable_super_globals();

		if (!$consumer->login($identifier, $return_to, 'http://192.168.0.112/'))
		{
			throw new phpbb_auth_exception($consumer->getError());
		}
		$request->disable_super_globals();
	}

	/**
	 * Verifies the returned values from the external server.
	 *
	 * @param phpbb_request $request
	 */
	public function verify(phpbb_request $request)
	{
		global $db;
		$storage = new phpbb_auth_zend_storage($db);
		$consumer = new Zend\OpenId\Consumer\GenericConsumer($storage);

		// Enable super globals so Zend Framework does not throw errors.
		$request->enable_super_globals();
		$id = '';
		return $consumer->verify($_GET, $id);
		$request->disable_super_globals();

	}
}
