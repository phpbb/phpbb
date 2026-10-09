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

use phpbb\captcha\factory;
use phpbb\captcha\plugins\captcha_abstract;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\passwords\manager;
use phpbb\user;

/**
 * Database authentication provider for phpBB
 * This is for authentication via the integrated user table
 */
class db extends base
{
	/** @var factory CAPTCHA factory */
	protected $captcha_factory;

	/** @var config phpBB config */
	protected $config;

	/** @var driver_interface DBAL driver instance */
	protected $db;

	/** @var user User object */
	protected $user;

	/**
	* phpBB passwords manager
	*
	* @var manager
	*/
	protected $passwords_manager;

	/**
	 * Database Authentication Constructor
	 *
	 * @param factory $captcha_factory
	 * @param	config 		$config
	 * @param	driver_interface		$db
	 * @param	manager	$passwords_manager
	 * @param	user			$user
	 */
	public function __construct(factory $captcha_factory, config $config, driver_interface $db, manager $passwords_manager, user $user)
	{
		$this->captcha_factory = $captcha_factory;
		$this->config = $config;
		$this->db = $db;
		$this->passwords_manager = $passwords_manager;
		$this->user = $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function login($username, $password)
	{
		// Auth plugins get the password untrimmed.
		// For compatibility we trim() here.
		$password = trim($password);

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

		$username_clean = utf8_clean_string($username);

		$sql = 'SELECT *
			FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . $this->db->sql_escape($username_clean) . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$user_login_attempts = (is_array($row) && $this->config['max_login_attempts'] && $row['user_login_attempts'] >= $this->config['max_login_attempts']);

		if (($this->user->ip && !$this->config['ip_login_limit_use_forwarded']) ||
			($this->user->forwarded_for && $this->config['ip_login_limit_use_forwarded']))
		{
			$sql = 'SELECT COUNT(*) AS attempts
				FROM ' . LOGIN_ATTEMPT_TABLE . '
				WHERE attempt_time > ' . (time() - (int) $this->config['ip_login_limit_time']);
			if ($this->config['ip_login_limit_use_forwarded'])
			{
				$sql .= " AND attempt_forwarded_for = '" . $this->db->sql_escape($this->user->forwarded_for) . "'";
			}
			else
			{
				$sql .= " AND attempt_ip = '" . $this->db->sql_escape($this->user->ip) . "' ";
			}

			$result = $this->db->sql_query($sql);
			$attempts = (int) $this->db->sql_fetchfield('attempts');
			$this->db->sql_freeresult($result);

			$ip_login_attempts = ($this->config['ip_login_limit_max'] && $attempts >= $this->config['ip_login_limit_max']);

			// Check the login cooldown before recording the current attempt,
			// so attempts rejected by the cooldown do not extend it.
			if (($user_login_attempts || $ip_login_attempts) && (int) $this->config['login_cooldown_min'])
			{
				$cooldown_time = $this->get_login_cooldown_remaining($row, $username_clean, $attempts);

				if ($cooldown_time > 0)
				{
					return array(
						'status'		=> LOGIN_ERROR_COOLDOWN,
						'error_msg'		=> 'LOGIN_ERROR_COOLDOWN',
						'user_row'		=> (is_array($row)) ? $row : array('user_id' => ANONYMOUS),
						'cooldown_time'	=> $cooldown_time,
					);
				}
			}

			$attempt_data = array(
				'attempt_ip'			=> $this->user->ip,
				'attempt_browser'		=> trim(substr($this->user->browser, 0, 149)),
				'attempt_forwarded_for'	=> $this->user->forwarded_for,
				'attempt_time'			=> time(),
				'user_id'				=> ($row) ? (int) $row['user_id'] : 0,
				'username'				=> $username,
				'username_clean'		=> $username_clean,
			);
			$sql = 'INSERT INTO ' . LOGIN_ATTEMPT_TABLE . $this->db->sql_build_array('INSERT', $attempt_data);
			$this->db->sql_query($sql);
		}
		else
		{
			$attempts = 0;
			$ip_login_attempts = false;
		}

		$login_error_attempts = 'LOGIN_ERROR_ATTEMPTS';

		$show_captcha = $user_login_attempts || $ip_login_attempts;

		if ($show_captcha)
		{
			$captcha = $this->captcha_factory->get_instance($this->config['captcha_plugin']);

			// Get custom message for login error when exceeding maximum number of attempts
			if ($captcha instanceof captcha_abstract)
			{
				$login_error_attempts = $captcha->get_login_error_attempts();
			}
		}

		if (!$row)
		{
			if ($this->config['ip_login_limit_max'] && $attempts >= $this->config['ip_login_limit_max'])
			{
				return array(
					'status'		=> LOGIN_ERROR_ATTEMPTS,
					'error_msg'		=> $login_error_attempts,
					'user_row'		=> array('user_id' => ANONYMOUS),
				);
			}

			return array(
				'status'	=> LOGIN_ERROR_USERNAME,
				'error_msg'	=> 'LOGIN_ERROR_USERNAME',
				'user_row'	=> array('user_id' => ANONYMOUS),
			);
		}

		// If there are too many login attempts, we need to check for a confirm image
		// Every auth module is able to define what to do by itself...
		if ($show_captcha)
		{
			$captcha->init(\phpbb\captcha\plugins\confirm_type::LOGIN);
			if ($captcha->validate() !== true)
			{
				return array(
					'status'		=> LOGIN_ERROR_ATTEMPTS,
					'error_msg'		=> $login_error_attempts,
					'user_row'		=> $row,
				);
			}
			else
			{
				$captcha->reset();
			}

		}

		// Check password ...
		if ($this->passwords_manager->check($password, $row['user_password'], $row))
		{
			// Check for old password hash...
			if ($this->passwords_manager->convert_flag || strlen($row['user_password']) == 32)
			{
				$hash = $this->passwords_manager->hash($password);

				// Update the password in the users table to the new format
				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_password = '" . $this->db->sql_escape($hash) . "'
					WHERE user_id = {$row['user_id']}";
				$this->db->sql_query($sql);

				$row['user_password'] = $hash;
			}

			$sql = 'DELETE FROM ' . LOGIN_ATTEMPT_TABLE . '
				WHERE user_id = ' . $row['user_id'];
			$this->db->sql_query($sql);

			if ($row['user_login_attempts'] != 0)
			{
				// Successful, reset login attempts (the user passed all stages)
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_login_attempts = 0
					WHERE user_id = ' . $row['user_id'];
				$this->db->sql_query($sql);
			}

			// User inactive...
			if ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE)
			{
				return array(
					'status'		=> LOGIN_ERROR_ACTIVE,
					'error_msg'		=> 'ACTIVE_ERROR',
					'user_row'		=> $row,
				);
			}

			// Successful login... set user_login_attempts to zero...
			return array(
				'status'		=> LOGIN_SUCCESS,
				'error_msg'		=> false,
				'user_row'		=> $row,
			);
		}

		// Password incorrect - increase login attempts
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_login_attempts = user_login_attempts + 1
			WHERE user_id = ' . (int) $row['user_id'] . '
				AND user_login_attempts < ' . LOGIN_ATTEMPTS_MAX;
		$this->db->sql_query($sql);

		// Give status about wrong password...
		return array(
			'status'		=> ($show_captcha) ? LOGIN_ERROR_ATTEMPTS : LOGIN_ERROR_PASSWORD,
			'error_msg'		=> 'LOGIN_ERROR_PASSWORD',
			'user_row'		=> $row,
		);
	}

	/**
	 * Get the remaining login cooldown time in seconds for the current attempt.
	 *
	 * The cooldown is keyed to the source of the failed attempts: exceeding
	 * the per-IP threshold enforces a cooldown for the IP, while exceeding
	 * the per-user threshold enforces it for the username and IP combination
	 * only, so failed attempts by a third party do not lock the account owner
	 * out. The cooldown length starts at login_cooldown_min seconds and grows
	 * by the same amount for every further failed attempt beyond the
	 * threshold, capped at login_cooldown_max seconds.
	 *
	 * @param array|false $row User row of the attempted username, false if the username does not exist
	 * @param string $username_clean Clean version of the attempted username
	 * @param int $attempts Number of recorded login attempts from the current IP
	 * @return int Remaining cooldown time in seconds, 0 if no cooldown applies
	 */
	protected function get_login_cooldown_remaining($row, $username_clean, $attempts)
	{
		$cooldown_min = (int) $this->config['login_cooldown_min'];
		$cooldown_max = max($cooldown_min, (int) $this->config['login_cooldown_max']);

		if ($this->config['ip_login_limit_use_forwarded'])
		{
			$sql_ip_where = "attempt_forwarded_for = '" . $this->db->sql_escape($this->user->forwarded_for) . "'";
		}
		else
		{
			$sql_ip_where = "attempt_ip = '" . $this->db->sql_escape($this->user->ip) . "'";
		}

		$cooldown_end = 0;

		if (is_array($row) && $this->config['max_login_attempts'] && $row['user_login_attempts'] >= $this->config['max_login_attempts'])
		{
			$sql = 'SELECT MAX(attempt_time) AS last_attempt
				FROM ' . LOGIN_ATTEMPT_TABLE . "
				WHERE username_clean = '" . $this->db->sql_escape($username_clean) . "'
					AND $sql_ip_where";
			$result = $this->db->sql_query($sql);
			$last_attempt = (int) $this->db->sql_fetchfield('last_attempt');
			$this->db->sql_freeresult($result);

			if ($last_attempt)
			{
				$excess = (int) $row['user_login_attempts'] - (int) $this->config['max_login_attempts'];
				$cooldown_end = $last_attempt + min($cooldown_max, $cooldown_min * ($excess + 1));
			}
		}

		if ($this->config['ip_login_limit_max'] && $attempts >= $this->config['ip_login_limit_max'])
		{
			$sql = 'SELECT MAX(attempt_time) AS last_attempt
				FROM ' . LOGIN_ATTEMPT_TABLE . "
				WHERE $sql_ip_where";
			$result = $this->db->sql_query($sql);
			$last_attempt = (int) $this->db->sql_fetchfield('last_attempt');
			$this->db->sql_freeresult($result);

			if ($last_attempt)
			{
				$excess = $attempts - (int) $this->config['ip_login_limit_max'];
				$cooldown_end = max($cooldown_end, $last_attempt + min($cooldown_max, $cooldown_min * ($excess + 1)));
			}
		}

		return max(0, $cooldown_end - time());
	}
}
