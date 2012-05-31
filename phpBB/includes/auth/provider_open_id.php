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
		$storage = new phpbb_auth_zend_storage();
		$consumer = new Zend\OpenId\Consumer\GenericConsumer($storage);
		$consumer->check($request->variable('id', ''), $request->server('PHP_SELF'), 'https://www.google.com/accounts/o8/id');
		if ($consumer->getError())
		{
			die($consumer->getError());
		}
		else
		{
			return true;
		}
	}
}
