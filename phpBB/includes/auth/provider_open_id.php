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

	public function process($request)
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
		$return_to = 'check_auth_openid.php';

		// Enable super globals so Zend Framework does not throw errors.
		$request->enable_super_globals();

		if (!$consumer->check($identifier, $return_to))
		{
			if($consumer->getError() != '')
			{
				throw new phpbb_auth_exception($consumer->getError());
			}
			else
			{
				if(!$consumer->login($identifier, $return_to)) {
					throw new phpbb_auth_exception($consumer->getError());
				}
			}
		}
		$request->disable_super_globals();
	}
}
