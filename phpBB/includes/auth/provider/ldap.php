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
 * Database authentication provider for phpBB3
 *
 * This is for authentication via the integrated user table
 *
 * @package auth
 */
class phpbb_auth_provider_ldap extends phpbb_auth_provider_base
{
	/**
	 * LDAP Authentication Constructor
	 *
	 * @param 	phpbb_db_driver	$db
	 * @param 	phpbb_config	$config
	 * @param 	phpbb_user		$user
	 * @param 	phpbb_template 	$template
	 */
	public function __construct(phpbb_db_driver $db, phpbb_config $config, phpbb_user $user, phpbb_template $template)
	{
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function init()
	{
		if (!@extension_loaded('ldap'))
		{
			return $this->user->lang['LDAP_NO_LDAP_EXTENSION'];
		}

		$this->config['ldap_port'] = (int) $this->config['ldap_port'];
		if ($this->config['ldap_port'])
		{
			$ldap = @ldap_connect($this->config['ldap_server'], $this->config['ldap_port']);
		}
		else
		{
			$ldap = @ldap_connect($this->config['ldap_server']);
		}

		if (!$ldap)
		{
			return $this->user->lang['LDAP_NO_SERVER_CONNECTION'];
		}

		@ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		@ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

		if ($this->config['ldap_user'] || $this->config['ldap_password'])
		{
			if (!@ldap_bind($ldap, htmlspecialchars_decode($this->config['ldap_user']), htmlspecialchars_decode($this->config['ldap_password'])))
			{
				return $this->user->lang['LDAP_INCORRECT_USER_PASSWORD'];
			}
		}

		// ldap_connect only checks whether the specified server is valid, so the connection might still fail
		$search = @ldap_search(
			$ldap,
			htmlspecialchars_decode($this->config['ldap_base_dn']),
			$this->ldap_user_filter($this->user->data['username']),
			(empty($this->config['ldap_email'])) ?
				array(htmlspecialchars_decode($this->config['ldap_uid'])) :
				array(htmlspecialchars_decode($this->config['ldap_uid']), htmlspecialchars_decode($this->config['ldap_email'])),
			0,
			1
		);

		if ($search === false)
		{
			return $this->user->lang['LDAP_SEARCH_FAILED'];
		}

		$result = @ldap_get_entries($ldap, $search);

		@ldap_close($ldap);


		if (!is_array($result) || sizeof($result) < 2)
		{
			return sprintf($this->user->lang['LDAP_NO_IDENTITY'], $this->user->data['username']);
		}

		if (!empty($this->config['ldap_email']) && !isset($result[0][htmlspecialchars_decode($this->config['ldap_email'])]))
		{
			return $this->user->lang['LDAP_NO_EMAIL'];
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

		if (!@extension_loaded('ldap'))
		{
			return array(
				'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
				'error_msg'		=> 'LDAP_NO_LDAP_EXTENSION',
				'user_row'		=> array('user_id' => ANONYMOUS),
			);
		}

		$this->config['ldap_port'] = (int) $this->config['ldap_port'];
		if ($this->config['ldap_port'])
		{
			$ldap = @ldap_connect($this->config['ldap_server'], $this->config['ldap_port']);
		}
		else
		{
			$ldap = @ldap_connect($this->config['ldap_server']);
		}

		if (!$ldap)
		{
			return array(
				'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
				'error_msg'		=> 'LDAP_NO_SERVER_CONNECTION',
				'user_row'		=> array('user_id' => ANONYMOUS),
			);
		}

		@ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		@ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

		if ($this->config['ldap_user'] || $this->config['ldap_password'])
		{
			if (!@ldap_bind($ldap, htmlspecialchars_decode($this->config['ldap_user']), htmlspecialchars_decode($this->config['ldap_password'])))
			{
				return array(
					'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
					'error_msg'		=> 'LDAP_NO_SERVER_CONNECTION',
					'user_row'		=> array('user_id' => ANONYMOUS),
				);
			}
		}

		$search = @ldap_search(
			$ldap,
			htmlspecialchars_decode($this->config['ldap_base_dn']),
			$this->ldap_user_filter($username),
			(empty($this->config['ldap_email'])) ?
				array(htmlspecialchars_decode($this->config['ldap_uid'])) :
				array(htmlspecialchars_decode($this->config['ldap_uid']), htmlspecialchars_decode($this->config['ldap_email'])),
			0,
			1
		);

		$ldap_result = @ldap_get_entries($ldap, $search);

		if (is_array($ldap_result) && sizeof($ldap_result) > 1)
		{
			if (@ldap_bind($ldap, $ldap_result[0]['dn'], htmlspecialchars_decode($password)))
			{
				@ldap_close($ldap);

				$sql ='SELECT user_id, username, user_password, user_passchg, user_email, user_type
					FROM ' . USERS_TABLE . "
					WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row)
				{
					unset($ldap_result);

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
				else
				{
					// retrieve default group id
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
					$ldap_user_row = array(
						'username'		=> $username,
						'user_password'	=> phpbb_hash($password),
						'user_email'	=> (!empty($this->config['ldap_email'])) ? utf8_htmlspecialchars($ldap_result[0][htmlspecialchars_decode($this->config['ldap_email'])][0]) : '',
						'group_id'		=> (int) $row['group_id'],
						'user_type'		=> USER_NORMAL,
						'user_ip'		=> $this->user->ip,
						'user_new'		=> ($this->config['new_member_post_limit']) ? 1 : 0,
					);

					unset($ldap_result);

					// this is the user's first login so create an empty profile
					return array(
						'status'		=> LOGIN_SUCCESS_CREATE_PROFILE,
						'error_msg'		=> false,
						'user_row'		=> $ldap_user_row,
					);
				}
			}
			else
			{
				unset($ldap_result);
				@ldap_close($ldap);

				// Give status about wrong password...
				return array(
					'status'		=> LOGIN_ERROR_PASSWORD,
					'error_msg'		=> 'LOGIN_ERROR_PASSWORD',
					'user_row'		=> array('user_id' => ANONYMOUS),
				);
			}
		}

		@ldap_close($ldap);

		return array(
			'status'	=> LOGIN_ERROR_USERNAME,
			'error_msg'	=> 'LOGIN_ERROR_USERNAME',
			'user_row'	=> array('user_id' => ANONYMOUS),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function acp($new)
	{
		$tpl = '

		<dl>
			<dt><label for="ldap_server">' . $this->user->lang['LDAP_SERVER'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['LDAP_SERVER_EXPLAIN'] . '</span></dt>
			<dd><input type="text" id="ldap_server" size="40" name="config[ldap_server]" value="' . $new['ldap_server'] . '" /></dd>
		</dl>
		<dl>
			<dt><label for="ldap_port">' . $this->user->lang['LDAP_PORT'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['LDAP_PORT_EXPLAIN'] . '</span></dt>
			<dd><input type="text" id="ldap_port" size="40" name="config[ldap_port]" value="' . $new['ldap_port'] . '" /></dd>
		</dl>
		<dl>
			<dt><label for="ldap_dn">' . $this->user->lang['LDAP_DN'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['LDAP_DN_EXPLAIN'] . '</span></dt>
			<dd><input type="text" id="ldap_dn" size="40" name="config[ldap_base_dn]" value="' . $new['ldap_base_dn'] . '" /></dd>
		</dl>
		<dl>
			<dt><label for="ldap_uid">' . $this->user->lang['LDAP_UID'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['LDAP_UID_EXPLAIN'] . '</span></dt>
			<dd><input type="text" id="ldap_uid" size="40" name="config[ldap_uid]" value="' . $new['ldap_uid'] . '" /></dd>
		</dl>
		<dl>
			<dt><label for="ldap_user_filter">' . $this->user->lang['LDAP_USER_FILTER'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['LDAP_USER_FILTER_EXPLAIN'] . '</span></dt>
			<dd><input type="text" id="ldap_user_filter" size="40" name="config[ldap_user_filter]" value="' . $new['ldap_user_filter'] . '" /></dd>
		</dl>
		<dl>
			<dt><label for="ldap_email">' . $this->user->lang['LDAP_EMAIL'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['LDAP_EMAIL_EXPLAIN'] . '</span></dt>
			<dd><input type="email" id="ldap_email" size="40" name="config[ldap_email]" value="' . $new['ldap_email'] . '" /></dd>
		</dl>
		<dl>
			<dt><label for="ldap_user">' . $this->user->lang['LDAP_USER'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['LDAP_USER_EXPLAIN'] . '</span></dt>
			<dd><input type="text" id="ldap_user" size="40" name="config[ldap_user]" value="' . $new['ldap_user'] . '" /></dd>
		</dl>
		<dl>
			<dt><label for="ldap_password">' . $this->user->lang['LDAP_PASSWORD'] . $this->user->lang['COLON'] . '</label><br /><span>' . $this->user->lang['LDAP_PASSWORD_EXPLAIN'] . '</span></dt>
			<dd><input type="password" id="ldap_password" size="40" name="config[ldap_password]" value="' . $new['ldap_password'] . '" autocomplete="off" /></dd>
		</dl>
		';

		// These are fields required in the config table
		return array(
			'tpl'		=> $tpl,
			'config'	=> array('ldap_server', 'ldap_port', 'ldap_base_dn', 'ldap_uid', 'ldap_user_filter', 'ldap_email', 'ldap_user', 'ldap_password')
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_acp_template($new_config)
	{
		$this->template->assign_vars(array(
			'AUTH_LDAP_DN'			=> $new_config['ldap_base_dn'],
			'AUTH_LDAP_EMAIL'		=> $new_config['ldap_email'],
			'AUTH_LDAP_PASSORD'		=> $new_config['ldap_password'],
			'AUTH_LDAP_PORT'		=> $new_config['ldap_port'],
			'AUTH_LDAP_SERVER'		=> $new_config['ldap_server'],
			'AUTH_LDAP_UID'			=> $new_config['ldap_uid'],
			'AUTH_LDAP_USER'		=> $new_config['ldap_user'],
			'AUTH_LDAP_USER_FILTER'	=> $new_config['ldap_user_filter'],
		));
	}

	/**
	 * Generates a filter string for ldap_search to find a user
	 *
	 * @param	$username	string	Username identifying the searched user
	 *
	 * @return				string	A filter string for ldap_search
	 */
	private function ldap_user_filter($username)
	{
		$filter = '(' . $this->config['ldap_uid'] . '=' . $this->ldap_escape(htmlspecialchars_decode($username)) . ')';
		if ($this->config['ldap_user_filter'])
		{
			$_filter = ($this->config['ldap_user_filter'][0] == '(' && substr($this->config['ldap_user_filter'], -1) == ')') ? $this->config['ldap_user_filter'] : "({$this->config['ldap_user_filter']})";
			$filter = "(&{$filter}{$_filter})";
		}
		return $filter;
	}

	/**
	 * Escapes an LDAP AttributeValue
	 *
	 * @param	string	$string	The string to be escaped
	 * @return	string	The escaped string
	 */
	private function ldap_escape($string)
	{
		return str_replace(array('*', '\\', '(', ')'), array('\\*', '\\\\', '\\(', '\\)'), $string);
	}
}
