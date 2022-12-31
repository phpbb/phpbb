<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\auth\provider;

/**
* Base authentication provider class that all other providers should implement
*/
abstract class base implements provider_interface
{
	/**
	* {@inheritdoc}
	*/
	public function init()
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function autologin()
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function acp()
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function get_acp_template($new_config)
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function get_login_data()
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function get_auth_link_data($user_id = 0)
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function logout($data, $new_session)
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function validate_session($user)
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function login_link_has_necessary_data(array $login_link_data)
	{
		return null;
	}

	/**
	* {@inheritdoc}
	*/
	public function link_account(array $link_data)
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function unlink_account(array $link_data)
	{
	}
}
