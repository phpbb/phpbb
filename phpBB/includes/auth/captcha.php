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
 * Provides for a way for any authentication process to check captcha using uniform
 * code.
 *
 * @package auth
 */
class phpbb_auth_captcha
{
	protected $db;
	protected $config;
	protected $user;

	public function __construct(dbal $db, phpbb_config_db $config, phpbb_user $user)
	{
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
	}

	public function need_captcha($user_login_attempts = 0)
	{
		// Get number of login attempts
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
		}
		else
		{
			$attempts = 0;
		}

		return ($this->config['max_login_attempts'] && $user_login_attempts >= $this->config['max_login_attempts']) ||
			($this->config['ip_login_limit_max'] && $attempts >= $this->config['ip_login_limit_max']);
	}

	public function confirm_visual_login_captcha($row)
	{
		// Visual Confirmation handling
		if (!class_exists('phpbb_captcha_factory', false))
		{
			global $phpbb_root_path, $phpEx;
			include ($phpbb_root_path . 'includes/captcha/captcha_factory.' . $phpEx);
		}

		$captcha = phpbb_captcha_factory::get_instance($this->config['captcha_plugin']);
		$captcha->init(CONFIRM_LOGIN);
		$vc_response = $captcha->validate($row);
		if ($vc_response)
		{
			return false;
		}
		else
		{
			$captcha->reset();
			return true;
		}
	}

	public function confirm_visual_registration_captcha($data)
	{
		if (!class_exists('phpbb_captcha_factory', false))
		{
			global $phpbb_root_path, $phpEx;
			include ($phpbb_root_path . 'includes/captcha/captcha_factory.' . $phpEx);
		}

		$captcha = phpbb_captcha_factory::get_instance($this->config['captcha_plugin']);
		$captcha->init(CONFIRM_REG);

		$vc_response = $captcha->validate($data);
		$error = array();
		if ($vc_response !== false)
		{
			$error[] = $vc_response;
		}

		if ($this->config['max_reg_attempts'] && $captcha->get_attempt_count() > $this->config['max_reg_attempts'])
		{
			$error[] = $this->user->lang['TOO_MANY_REGISTERS'];
		}

		if (sizeof($error))
		{
			return $error;
		}
		else
		{
			return true;
		}
	}

	public function reset_registration_captcha()
	{
		if (!class_exists('phpbb_captcha_factory', false))
		{
			global $phpbb_root_path, $phpEx;
			include ($phpbb_root_path . 'includes/captcha/captcha_factory.' . $phpEx);
		}

		$captcha = phpbb_captcha_factory::get_instance($this->config['captcha_plugin']);
		$captcha->init(CONFIRM_REG);
		$captcha->reset();
	}
}
