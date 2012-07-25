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
	protected $phpbb_root_path;
	protected $phpEx;
	protected static $captcha = null;

	/**
	 * @global string $phpbb_root_path
	 * @global string $phpEx
	 * @param dbal $db
	 * @param phpbb_config_db $config
	 * @param phpbb_user $user
	 */
	public function __construct(dbal $db, phpbb_config_db $config, phpbb_user $user)
	{
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;

		global $phpbb_root_path, $phpEx;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
	}

	/**
	 * Prepares captcha for use and stores it for later use as only one captcha
	 * object may be called per script execution.
	 *
	 * @param int $init_type
	 * @return void
	 */
	protected function get_captcha($init_type)
	{
		if (self::$captcha !== null)
		{
			return;
		}

		if (!class_exists('phpbb_captcha_factory', false))
		{
			include ($this->phpbb_root_path . 'includes/captcha/captcha_factory.' . $this->phpEx);
		}

		$captcha = phpbb_captcha_factory::get_instance($this->config['captcha_plugin']);
		$captcha->init($init_type);

		self::$captcha = $captcha;
		return;
	}

	/**
	 * Determines whether captcha needs to be shown or not. Used by default in
	 * common login.
	 *
	 * @param int $user_login_attempts
	 * @return boolean
	 */
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

	/**
	 * Confirms whether or not the visual captcha is correct or not.
	 *
	 * @param array $row
	 * @return boolean
	 */
	public function confirm_visual_login_captcha($row)
	{
		$this->get_captcha(CONFIRM_LOGIN);
		$vc_response = self::$captcha->validate($row);
		if ($vc_response)
		{
			return false;
		}
		else
		{
			self::$captcha->reset();
			return true;
		}
	}

	/**
	 * Validates the captcha on registration if requested by a provider.
	 *
	 * @param array $data
	 * @return array|boolean Return an array of errors on failure or true on success
	 */
	public function confirm_visual_registration_captcha($data)
	{
		$this->get_captcha(CONFIRM_REG);
		$vc_response = self::$captcha->validate($data);
		$error = array();
		if ($vc_response !== false)
		{
			$error[] = $vc_response;
		}

		if ($this->config['max_reg_attempts'] && self::$captcha->get_attempt_count() > $this->config['max_reg_attempts'])
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

	/**
	 * Resets registration captcha.
	 */
	public function reset_registration_captcha()
	{
		$this->get_captcha(CONFIRM_REG);
		self::$captcha->reset();
	}

	/**
	 * Returns an array of the hidden fields for the active captcha.
	 *
	 * @param int $init_type
	 * @return array
	 */
	public function get_hidden_fields($init_type)
	{
		$this->get_captcha($init_type);
		return self::$captcha->get_hidden_fields();
	}

	/**
	 * Returns the template code for a given captcha.
	 *
	 * @param int $init_type
	 * @return string
	 */
	public function get_template($init_type)
	{
		$this->get_captcha($init_type);
		return self::$captcha->get_template();
	}
}
