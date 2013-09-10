<?php
/**
*
* @package auth
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\auth\provider;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Base authentication provider class that all other providers should implement
*
* @package auth
*/
abstract class base implements \phpbb\auth\provider\provider_interface
{
	/**
	* {@inheritdoc}
	*/
	public function init()
	{
		return;
	}

	/**
	* {@inheritdoc}
	*/
	public function autologin()
	{
		return;
	}

	/**
	* {@inheritdoc}
	*/
	public function acp()
	{
		return;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_acp_template($new_config)
	{
		return;
	}

	/**
	* {@inheritdoc}
	*/
	public function logout($data, $new_session)
	{
		return;
	}

	/**
	* {@inheritdoc}
	*/
	public function validate_session($user)
	{
		return;
	}
}
