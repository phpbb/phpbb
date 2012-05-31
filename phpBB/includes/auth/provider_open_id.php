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
		$consumer = new Zend\OpenId\Consumer\GenericConsumer($storage);
		if (!$consumer->login($request->variable('open_id_identifier', ''), 'index.php'))
		{
			throw new phpbb_auth_exception($consumer->getError());
		}
		/*$consumer->check($request->variable('id', ''), $request->server('PHP_SELF'), 'https://www.google.com/accounts/o8/id');
		if ($consumer->getError())
		{
			throw new phpbb_auth_exception($consumer->getError());
		}
		else
		{
			return true;
		}*/
	}
}
