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
* Google OAuth service
*
* @package auth
*/
class phpbb_auth_provider_oauth_service_google extends phpbb_auth_provider_oauth_service_base
{
	/**
	* {@inheritdoc}
	*/
	public function get_auth_scope()
	{
		return array(
			'userinfo_email',
			'userinfo_profile',
		);
	}
}
