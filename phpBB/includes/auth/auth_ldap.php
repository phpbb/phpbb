<?php

//
// Authentication plug-ins is largely down to
// Sergey Kanareykin, our thanks to him. 
//
function login_ldap(&$username, &$password)
{
	global $board_config;

	if ( !extension_loaded('ldap') )
	{
		return 'LDAP extension not available';
	}

	if ( !($ldap = @ldap_connect($board_config['ldap_server'])) ) 
	{
		return 'Could not connect to LDAP server';
	}

	$search = @ldap_search($ldap, $board_config['ldap_base_dn'], $board_config['ldap_uid'] . '=' . $username, array($board_config['ldap_uid']));
	$result = @ldap_get_entries($ldap, $search);

	if ( is_array($result) && count($result) > 1 ) 
	{
		if ( @ldap_bind($ldap, $result[0]['dn'], $password) ) 
		{
			@ldap_close($ldap);

			$sql = "SELECT user_id, username, user_password, user_email, user_active  
				FROM " . USERS_TABLE . "
				WHERE username = '" . str_replace("\'", "''", $username) . "'";
			$result = $db->sql_query($sql);

			return ( $row = $db->sql_fetchrow($result) ) ? $row : false;
		}
	} 
	
	@ldap_close($ldap);
	
	return false;
}

function admin_ldap(&$new)
{
	global $lang;

?>
	<tr>
		<td class="row1"><?php echo $lang['LDAP_server']; ?>:<br /><span class="gensmall"><?php echo $lang['LDAP_server_explain']; ?></span></td>
		<td class="row2"><input type="text" size="40" name="ldap_server" value="<?php echo $new['ldap_server']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['LDAP_dn']; ?>:<br /><span class="gensmall"><?php echo $lang['LDAP_dn_explain']; ?></span></td>
		<td class="row2"><input type="text" size="40" name="ldap_base_dn" value="<?php echo $new['ldap_base_dn']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['LDAP_uid']; ?>:<br /><span class="gensmall"><?php echo $lang['LDAP_uid_explain']; ?></span></td>
		<td class="row2"><input type="text" size="40" name="ldap_uid" value="<?php echo $new['ldap_uid']; ?>" /></td>
	</tr>
<?php
	
	//
	// These are fields required in the config table
	//
	return array('ldap_server', 'ldap_base_dn', 'ldap_uid');

}

?>