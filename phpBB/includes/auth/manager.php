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
* This class handles the selection of authentication provider to use.
*
* @package auth
*/
class phpbb_auth_manager
{
	public function get_authenticator($authType)
	{
		$authenticator = 'phpbb_auth_provider_'.$authType;
		if(class_exists($authenticator))
		{
			return new $authenticator();
		}
		else
		{
			// Throw error
		}
	}
}
