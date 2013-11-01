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
	public function get_login_data()
	{
		return;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_auth_link_data()
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

	/**
	* {@inheritdoc}
	*/
	public function login_link_has_necessary_data($login_link_data)
	{
		return;
	}

	/**
	* {@inheritdoc}
	*/
	public function link_account(array $link_data)
	{
		return;
	}

	/**
	* {@inheritdoc}
	*/
	public function unlink_account(array $link_data)
	{
		return;
	}
}
