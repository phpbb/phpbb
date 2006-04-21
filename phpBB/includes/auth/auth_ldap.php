<?php
/** 
*
* LDAP auth plug-in for phpBB3
*
* Authentication plug-ins is largely down to Sergey Kanareykin, our thanks to him.
*
* This is for initial authentication via an LDAP server, user information is then
* obtained from the integrated user table
*
* You can do any kind of checking you like here ... the return data format is
* either the resulting row of user information, an integer zero (indicating an
* inactive user) or some error string
*
* @package login
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* Only allow changing authentication to ldap if we can connect to the ldap server
*/
function init_ldap()
{
	global $config, $user;

	if (!extension_loaded('ldap'))
	{
		return $user->lang['LDAP_NO_LDAP_EXTENSION'];
	}

	if (!($ldap = @ldap_connect($config['ldap_server'])))
	{
		return $user->lang['LDAP_NO_SERVER_CONNECTION'];
	}

	@ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

	// We'll get a notice here that we don't want, if we cannot connect to the server.
	// ldap_connect only checks whether the specified server is valid, so the connection might still fail
	ob_start();

	$search = @ldap_search($ldap, $config['ldap_base_dn'], $config['ldap_uid'] . '=' . $user->data['username'], array($config['ldap_uid']));

	if (ob_get_clean())
	{
		return $user->lang['LDAP_NO_SERVER_CONNECTION'];
	}

	$result = @ldap_get_entries($ldap, $search);

	@ldap_close($ldap);

	if (is_array($result) && sizeof($result) > 1)
	{
		return false;
	}

	return sprintf($user->lang['LDAP_NO_IDENTITY'], $user->data['username']);
}

/**
* Login function
*/
function login_ldap(&$username, &$password)
{
	global $db, $config;

	if (!extension_loaded('ldap'))
	{
		return array(
			'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
			'error_msg'		=> 'LDAP_NO_LDAP_EXTENSION',
			'user_row'		=> array('user_id' => ANONYMOUS),
		);
	}

	if (!($ldap = @ldap_connect($config['ldap_server'])))
	{
		return array(
			'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
			'error_msg'		=> 'LDAP_NO_SERVER_CONNECTION',
			'user_row'		=> array('user_id' => ANONYMOUS),
		);
	}

	@ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

	$search = @ldap_search($ldap, $config['ldap_base_dn'], $config['ldap_uid'] . '=' . $username, array($config['ldap_uid']));
	$result = @ldap_get_entries($ldap, $search);

	if (is_array($result) && sizeof($result) > 1)
	{
		if (@ldap_bind($ldap, $result[0]['dn'], $password))
		{
			@ldap_close($ldap);

			$sql ='SELECT user_id, username, user_password, user_passchg, user_email, user_type
				FROM ' . USERS_TABLE . "
				WHERE username = '" . $db->sql_escape($username) . "'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

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
		
				// Successful login... set user_login_attempts to zero...
				return array(
					'status'		=> LOGIN_SUCCESS,
					'error_msg'		=> false,
					'user_row'		=> $row,
				);
			}
		}
		else
		{
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
* This function is used to output any required fields in the authentication
* admin panel. It also defines any required configuration table fields.
*/
function admin_ldap(&$new)
{
	global $user;

	/**
	* @todo Using same approach with cfg_build_template?
	*/

	$tpl = '

	<dl>
		<dt><label for="ldap_server">' . $user->lang['LDAP_SERVER'] . ':</label><br /><span>' . $user->lang['LDAP_SERVER_EXPLAIN'] . '</span></dt>
		<dd><input type="text" id="ldap_server" size="40" name="config[ldap_server]" value="' . $new['ldap_server'] . '" /></dd>
	</dl>
	<dl>
		<dt><label for="ldap_dn">' . $user->lang['LDAP_DN'] . ':</label><br /><span>' . $user->lang['LDAP_DN_EXPLAIN'] . '</span></dt>
		<dd><input type="text" id="ldap_dn" size="40" name="config[ldap_base_dn]" value="' . $new['ldap_base_dn'] . '" /></dd>
	</dl>
	<dl>
		<dt><label for="ldap_uid">' . $user->lang['LDAP_UID'] . ':</label><br /><span>' . $user->lang['LDAP_UID_EXPLAIN'] . '</span></dt>
		<dd><input type="text" id="ldap_uid" size="40" name="config[ldap_uid]" value="' . $new['ldap_uid'] . '" /></dd>
	</dl>
	';

	// These are fields required in the config table
	return array(
		'tpl'		=> $tpl,
		'config'	=> array('ldap_server', 'ldap_base_dn', 'ldap_uid')
	);
}

/**
* Would be nice to allow syncing of 'appropriate' data when user updates
* their username, password, etc. ... should be up to the plugin what data
* is updated.
*
* @todo implement this functionality (probably 3.2)
*
* @param new|update|delete $mode defining the action to take on user updates
*/
function usercp_ldap($mode)
{
	global $db, $config;
}

?>