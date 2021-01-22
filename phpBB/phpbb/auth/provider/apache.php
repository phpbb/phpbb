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

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\request\type_cast_helper;
use phpbb\user;

/**
* Apache authentication provider for phpBB3
*/
class apache extends base
{
	/** @var config phpBB config */
	protected $config;

	/** @var driver_interface Database object */
	protected $db;

	/** @var language Language object */
	protected $language;

	/** @var request_interface Request object */
	protected $request;

	/** @var user User object */
	protected $user;

	/** @var string Relative path to phpBB root */
	protected $phpbb_root_path;

	/** @var string PHP file extension */
	protected $php_ext;

	/**
	 * Apache Authentication Constructor
	 *
	 * @param	config 				$config		Config object
	 * @param	driver_interface 	$db		Database object
	 * @param	language			$language Language object
	 * @param	request_interface 	$request		Request object
	 * @param	user 				$user		User object
	 * @param	string 				$phpbb_root_path		Relative path to phpBB root
	 * @param	string 				$php_ext		PHP file extension
	 */
	public function __construct(config $config, driver_interface $db, language $language, request_interface $request, user $user, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
		$this->db = $db;
		$this->language = $language;
		$this->request = $request;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * {@inheritdoc}
	 */
	public function init()
	{
		if (!$this->request->is_set('PHP_AUTH_USER', request_interface::SERVER) || $this->user->data['username'] !== htmlspecialchars_decode($this->request->server('PHP_AUTH_USER'), ENT_COMPAT))
		{
			return $this->language->lang('APACHE_SETUP_BEFORE_USE');
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function login($username, $password)
	{
		// do not allow empty password
		if (!$password)
		{
			return array(
				'status'	=> LOGIN_ERROR_PASSWORD,
				'error_msg'	=> 'NO_PASSWORD_SUPPLIED',
				'user_row'	=> array('user_id' => ANONYMOUS),
			);
		}

		if (!$username)
		{
			return array(
				'status'	=> LOGIN_ERROR_USERNAME,
				'error_msg'	=> 'LOGIN_ERROR_USERNAME',
				'user_row'	=> array('user_id' => ANONYMOUS),
			);
		}

		if (!$this->request->is_set('PHP_AUTH_USER', request_interface::SERVER))
		{
			return array(
				'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
				'error_msg'		=> 'LOGIN_ERROR_EXTERNAL_AUTH_APACHE',
				'user_row'		=> array('user_id' => ANONYMOUS),
			);
		}

		$php_auth_user = htmlspecialchars_decode($this->request->server('PHP_AUTH_USER'), ENT_COMPAT);
		$php_auth_pw = htmlspecialchars_decode($this->request->server('PHP_AUTH_PW'), ENT_COMPAT);

		if (!empty($php_auth_user) && !empty($php_auth_pw))
		{
			if ($php_auth_user !== $username)
			{
				return array(
					'status'	=> LOGIN_ERROR_USERNAME,
					'error_msg'	=> 'LOGIN_ERROR_USERNAME',
					'user_row'	=> array('user_id' => ANONYMOUS),
				);
			}

			$sql = 'SELECT user_id, username, user_password, user_passchg, user_email, user_type
				FROM ' . USERS_TABLE . "
				WHERE username = '" . $this->db->sql_escape($php_auth_user) . "'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				// User inactive...
				if ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE)
				{
					return array(
						'status'		=> LOGIN_ERROR_ACTIVE,
						'error_msg'		=> 'ACTIVE_ERROR',
						'user_row'		=> $row,
					);
				}

				// Successful login...
				return array(
					'status'		=> LOGIN_SUCCESS,
					'error_msg'		=> false,
					'user_row'		=> $row,
				);
			}

			// this is the user's first login so create an empty profile
			return array(
				'status'		=> LOGIN_SUCCESS_CREATE_PROFILE,
				'error_msg'		=> false,
				'user_row'		=> $this->user_row($php_auth_user),
			);
		}

		// Not logged into apache
		return array(
			'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
			'error_msg'		=> 'LOGIN_ERROR_EXTERNAL_AUTH_APACHE',
			'user_row'		=> array('user_id' => ANONYMOUS),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function autologin()
	{
		if (!$this->request->is_set('PHP_AUTH_USER', request_interface::SERVER))
		{
			return array();
		}

		$php_auth_user = htmlspecialchars_decode($this->request->server('PHP_AUTH_USER'), ENT_COMPAT);
		$php_auth_pw = htmlspecialchars_decode($this->request->server('PHP_AUTH_PW'), ENT_COMPAT);

		if (!empty($php_auth_user) && !empty($php_auth_pw))
		{
			$type_cast_helper = new type_cast_helper();
			$type_cast_helper->set_var($php_auth_user, $php_auth_user, 'string', true);

			$sql = 'SELECT *
				FROM ' . USERS_TABLE . "
				WHERE username = '" . $this->db->sql_escape($php_auth_user) . "'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				return ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE) ? array() : $row;
			}

			if (!function_exists('user_add'))
			{
				include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
			}

			// create the user if he does not exist yet
			user_add($this->user_row($php_auth_user));

			$sql = 'SELECT *
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($php_auth_user)) . "'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				return $row;
			}
		}

		return array();
	}

	/**
	 * This function generates an array which can be passed to the user_add
	 * function in order to create a user
	 *
	 * @param 	string	$username 	The username of the new user.
	 *
	 * @return 	array 				Contains data that can be passed directly to
	 *								the user_add function.
	 */
	private function user_row($username)
	{
		// first retrieve default group id
		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . "
			WHERE group_name = '" . $this->db->sql_escape('REGISTERED') . "'
				AND group_type = " . GROUP_SPECIAL;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error('NO_GROUP');
		}

		// generate user account data
		return array(
			'username'		=> $username,
			'user_password'	=> '',
			'user_email'	=> '',
			'group_id'		=> (int) $row['group_id'],
			'user_type'		=> USER_NORMAL,
			'user_ip'		=> $this->user->ip,
			'user_new'		=> ($this->config['new_member_post_limit']) ? 1 : 0,
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate_session($user)
	{
		// Check if PHP_AUTH_USER is set and handle this case
		if ($this->request->is_set('PHP_AUTH_USER', request_interface::SERVER))
		{
			$php_auth_user = $this->request->server('PHP_AUTH_USER');

			return ($php_auth_user === $user['username']) ? true : false;
		}

		// PHP_AUTH_USER is not set. A valid session is now determined by the user type (anonymous/bot or not)
		if ($user['user_type'] == USER_IGNORE)
		{
			return true;
		}

		return false;
	}
}
