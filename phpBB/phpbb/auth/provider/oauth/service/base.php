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
* Bitly OAuth service
*
* @package auth
*/
abstract class phpbb_auth_provider_oauth_service_base implements phpbb_auth_provider_oauth_service_interface
{
	/**
	* {@inheritdoc}
	*/
	public function get_auth_scope()
	{
		return array();
	}
}
