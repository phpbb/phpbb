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
* SoundCloud OAuth service
*
* @package auth
*/
class phpbb_auth_provider_oauth_service_soundcloud extends phpbb_auth_provider_oauth_service_base
{
	/**
	* {@inheritdoc}
	*/
	public function get_service_credentials()
	{
		return array(
			'key'		=> $this->config['auth_oauth_soundcloud_key'],
			'secret'	=> $this->config['auth_oauth_soundcloud_secret'],
		);
	}
}
