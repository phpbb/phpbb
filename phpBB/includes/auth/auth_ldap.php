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
* Login function
*/
function login_ldap(&$username, &$password)
{
	global $db, $config;

	if (!extension_loaded('ldap'))
	{
		return 'LDAP extension not available';
	}

	if (!($ldap = @ldap_connect($config['ldap_server'])))
	{
		return 'Could not connect to LDAP server';
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

			if ($row = $db->sql_fetchrow($result))
			{
				$db->sql_freeresult($result);
				return ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE) ? 0 : $row;
			}
		}
	}

	@ldap_close($ldap);

	return false;
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
* @param new|update|delete $mode defining the action to take on user updates
*/
function usercp_ldap($mode)
{
	global $db, $config;

}

?>