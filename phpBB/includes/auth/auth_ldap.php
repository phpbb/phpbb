<?php
/**
*
* LDAP auth plug-in for phpBB3
*
* Authentication plug-ins is largely down to Sergey Kanareykin, our thanks to him.
*
* @package login
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* Connect to ldap server
* Only allow changing authentication to ldap if we can connect to the ldap server
* Called in acp_board while setting authentication plugins
*/
function init_ldap()
{
	global $config, $user;

	if (!@extension_loaded('ldap'))
	{
		return $user->lang['LDAP_NO_LDAP_EXTENSION'];
	}

	$config['ldap_port'] = (int) $config['ldap_port'];
	if ($config['ldap_port'])
	{
		$ldap = @ldap_connect($config['ldap_server'], $config['ldap_port']);
	}
	else
	{
		$ldap = @ldap_connect($config['ldap_server']);
	}

	if (!$ldap)
	{
		return $user->lang['LDAP_NO_SERVER_CONNECTION'];
	}

	@ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
	@ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

	if ($config['ldap_user'] || $config['ldap_password'])
	{
		if (!@ldap_bind($ldap, htmlspecialchars_decode($config['ldap_user']), htmlspecialchars_decode($config['ldap_password'])))
		{
			return $user->lang['LDAP_INCORRECT_USER_PASSWORD'];
		}
	}

	// ldap_connect only checks whether the specified server is valid, so the connection might still fail
	$search = @ldap_search(
		$ldap,
		htmlspecialchars_decode($config['ldap_base_dn']),
		ldap_user_filter($user->data['username']),
		(empty($config['ldap_email'])) ?
			array(htmlspecialchars_decode($config['ldap_uid'])) :
			array(htmlspecialchars_decode($config['ldap_uid']), htmlspecialchars_decode($config['ldap_email'])),
		0,
		1
	);

	if ($search === false)
	{
		return $user->lang['LDAP_SEARCH_FAILED'];
	}

	$result = @ldap_get_entries($ldap, $search);

	@ldap_close($ldap);


	if (!is_array($result) || sizeof($result) < 2)
	{
		return sprintf($user->lang['LDAP_NO_IDENTITY'], $user->data['username']);
	}

	if (!empty($config['ldap_email']) && !isset($result[0][htmlspecialchars_decode($config['ldap_email'])]))
	{
		return $user->lang['LDAP_NO_EMAIL'];
	}

	return false;
}

/**
* Login function
*/
function login_ldap(&$username, &$password)
{
	global $db, $config, $user;

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

	$config['ldap_port'] = (int) $config['ldap_port'];
	if ($config['ldap_port'])
	{
		$ldap = @ldap_connect($config['ldap_server'], $config['ldap_port']);
	}
	else
	{
		$ldap = @ldap_connect($config['ldap_server']);
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

	if ($config['ldap_user'] || $config['ldap_password'])
	{
		if (!@ldap_bind($ldap, htmlspecialchars_decode($config['ldap_user']), htmlspecialchars_decode($config['ldap_password'])))
		{
			return $user->lang['LDAP_NO_SERVER_CONNECTION'];
		}
	}

	$search = @ldap_search(
		$ldap,
		htmlspecialchars_decode($config['ldap_base_dn']),
		ldap_user_filter($username),
		(empty($config['ldap_email'])) ?
			array(htmlspecialchars_decode($config['ldap_uid'])) :
			array(htmlspecialchars_decode($config['ldap_uid']), htmlspecialchars_decode($config['ldap_email'])),
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
				WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

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
					WHERE group_name = '" . $db->sql_escape('REGISTERED') . "'
						AND group_type = " . GROUP_SPECIAL;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error('NO_GROUP');
				}

				// generate user account data
				$ldap_user_row = array(
					'username'		=> $username,
					'user_password'	=> phpbb_hash($password),
					'user_email'	=> (!empty($config['ldap_email'])) ? utf8_htmlspecialchars($ldap_result[0][htmlspecialchars_decode($config['ldap_email'])][0]) : '',
					'group_id'		=> (int) $row['group_id'],
					'user_type'		=> USER_NORMAL,
					'user_ip'		=> $user->ip,
					'user_new'		=> ($config['new_member_post_limit']) ? 1 : 0,
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
* Generates a filter string for ldap_search to find a user
*
* @param	$username	string	Username identifying the searched user
*
* @return				string	A filter string for ldap_search
*/
function ldap_user_filter($username)
{
	global $config;

	$filter = '(' . $config['ldap_uid'] . '=' . ldap_escape(htmlspecialchars_decode($username)) . ')';
	if ($config['ldap_user_filter'])
	{
		$_filter = ($config['ldap_user_filter'][0] == '(' && substr($config['ldap_user_filter'], -1) == ')') ? $config['ldap_user_filter'] : "({$config['ldap_user_filter']})";
		$filter = "(&{$filter}{$_filter})";
	}
	return $filter;
}

/**
* Escapes an LDAP AttributeValue
*/
function ldap_escape($string)
{
	return str_replace(array('*', '\\', '(', ')'), array('\\*', '\\\\', '\\(', '\\)'), $string);
}

/**
* This function is used to output any required fields in the authentication
* admin panel. It also defines any required configuration table fields.
*/
function acp_ldap(&$new)
{
	global $user;

	$tpl = '

	<dl>
		<dt><label for="ldap_server">' . $user->lang['LDAP_SERVER'] . ':</label><br /><span>' . $user->lang['LDAP_SERVER_EXPLAIN'] . '</span></dt>
		<dd><input type="text" id="ldap_server" size="40" name="config[ldap_server]" value="' . $new['ldap_server'] . '" /></dd>
	</dl>
	<dl>
		<dt><label for="ldap_port">' . $user->lang['LDAP_PORT'] . ':</label><br /><span>' . $user->lang['LDAP_PORT_EXPLAIN'] . '</span></dt>
		<dd><input type="text" id="ldap_port" size="40" name="config[ldap_port]" value="' . $new['ldap_port'] . '" /></dd>
	</dl>
	<dl>
		<dt><label for="ldap_dn">' . $user->lang['LDAP_DN'] . ':</label><br /><span>' . $user->lang['LDAP_DN_EXPLAIN'] . '</span></dt>
		<dd><input type="text" id="ldap_dn" size="40" name="config[ldap_base_dn]" value="' . $new['ldap_base_dn'] . '" /></dd>
	</dl>
	<dl>
		<dt><label for="ldap_uid">' . $user->lang['LDAP_UID'] . ':</label><br /><span>' . $user->lang['LDAP_UID_EXPLAIN'] . '</span></dt>
		<dd><input type="text" id="ldap_uid" size="40" name="config[ldap_uid]" value="' . $new['ldap_uid'] . '" /></dd>
	</dl>
	<dl>
		<dt><label for="ldap_user_filter">' . $user->lang['LDAP_USER_FILTER'] . ':</label><br /><span>' . $user->lang['LDAP_USER_FILTER_EXPLAIN'] . '</span></dt>
		<dd><input type="text" id="ldap_user_filter" size="40" name="config[ldap_user_filter]" value="' . $new['ldap_user_filter'] . '" /></dd>
	</dl>
	<dl>
		<dt><label for="ldap_email">' . $user->lang['LDAP_EMAIL'] . ':</label><br /><span>' . $user->lang['LDAP_EMAIL_EXPLAIN'] . '</span></dt>
		<dd><input type="text" id="ldap_email" size="40" name="config[ldap_email]" value="' . $new['ldap_email'] . '" /></dd>
	</dl>
	<dl>
		<dt><label for="ldap_user">' . $user->lang['LDAP_USER'] . ':</label><br /><span>' . $user->lang['LDAP_USER_EXPLAIN'] . '</span></dt>
		<dd><input type="text" id="ldap_user" size="40" name="config[ldap_user]" value="' . $new['ldap_user'] . '" /></dd>
	</dl>
	<dl>
		<dt><label for="ldap_password">' . $user->lang['LDAP_PASSWORD'] . ':</label><br /><span>' . $user->lang['LDAP_PASSWORD_EXPLAIN'] . '</span></dt>
		<dd><input type="password" id="ldap_password" size="40" name="config[ldap_password]" value="' . $new['ldap_password'] . '" /></dd>
	</dl>
	';

	// These are fields required in the config table
	return array(
		'tpl'		=> $tpl,
		'config'	=> array('ldap_server', 'ldap_port', 'ldap_base_dn', 'ldap_uid', 'ldap_user_filter', 'ldap_email', 'ldap_user', 'ldap_password')
	);
}

?>