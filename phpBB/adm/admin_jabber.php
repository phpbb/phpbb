<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : viewtopic.php 
// STARTED   : Sat Feb 13, 2001
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// TODO
// Server name 
// Username 
// Password 
// Resource 
// Create new user on server
// Advise what transports on server
// Set cruise control time
// Register transport usernames/passwords (links to online reg systems)

if (!empty($setmodules))
{
	if (!$auth->acl_get('a_server'))
	{
		return;
	}

	$module['GENERAL']['IM'] = basename(__FILE__) . $SID;

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);

// Do we have general permissions?
if (!$auth->acl_get('a_server'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Grab some basic parameters
$submit = (isset($_POST['submit'])) ? true : false;

/*
				if ($result = $this->jabber->AccountRegistration($config['contact_email'], str_replace('.', '_', $config['server_name'])))
				{
					break;
				}
			}
			if (!$result)
			{
				trigger_error('Could not create new user on Jabber server', E_USER_ERROR);
			}
*/

// Pull all config data
$sql = "SELECT *
	FROM " . CONFIG_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$config_name = $row['config_name'];
	$config_value = $row['config_value'];

	$default_config[$config_name] = $config_value;
	$new[$config_name] = (isset($_POST[$config_name])) ? $_POST[$config_name] : $default_config[$config_name];

	if ($submit)
	{
		set_config($config_name, $new[$config_name]);
	}
}

if ($submit)
{
	add_log('admin', 'LOG_' . strtoupper($mode) . '_CONFIG');
	trigger_error($user->lang['CONFIG_UPDATED']);
}



// Output the page
adm_page_header($user->lang['IM']);

$jab_enable_yes		= ($new['jab_enable']) ? 'checked="checked"' : '';
$jab_enable_no		= (!$new['jab_enable']) ? 'checked="checked"' : '';
$jab_aim_enable_yes = ($new['jab_aim_enable']) ? 'checked="checked"' : '';
$jab_aim_enable_no	= (!$new['jab_aim_enable']) ? 'checked="checked"' : '';
$jab_icq_enable_yes = ($new['jab_icq_enable']) ? 'checked="checked"' : '';
$jab_icq_enable_no	= (!$new['jab_icq_enable']) ? 'checked="checked"' : '';
$jab_msn_enable_yes = ($new['jab_msn_enable']) ? 'checked="checked"' : '';
$jab_msn_enable_no	= (!$new['jab_msn_enable']) ? 'checked="checked"' : '';
$jab_yim_enable_yes = ($new['jab_yim_enable']) ? 'checked="checked"' : '';
$jab_yim_enable_no	= (!$new['jab_yim_enable']) ? 'checked="checked"' : '';

?>
<h1><?php echo $user->lang['IM']; ?></h1>

<p><?php echo $user->lang['IM_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_jabber.$phpEx$SID"; ?>"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['IM']; ?></th>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['JAB_ENABLE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['JAB_ENABLE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="jab_enable" value="1"<?php echo $jab_enable_yes; ?> /><?php echo $user->lang['ENABLED']; ?>&nbsp; &nbsp;<input type="radio" name="jab_enable" value="0"<?php echo $jab_enable_no; ?> /><?php echo $user->lang['DISABLED']; ?></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['JAB_SERVER']; ?>: </b><br /><span class="gensmall"><?php echo sprintf($user->lang['JAB_SERVER_EXPLAIN'], '<a href="http://www.jabber.org/user/publicservers.php" target="_blank">', '</a>'); ?></span></td>
		<td class="row2"><input class="post" type="text" name="jab_host" value="<?php echo $new['jab_host']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['JAB_PORT']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="jab_port" value="<?php echo $new['jab_port']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['JAB_USERNAME']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['JAB_USERNAME_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="jab_username" value="<?php echo $new['jab_username']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['JAB_PASSWORD']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="jab_password" value="<?php echo $new['jab_password']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['JAB_RESOURCE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['JAB_RESOURCE_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="jab_resource" value="<?php echo $new['jab_resource']; ?>" /></td>
	</tr>
	<tr>
		<th colspan="2"><?php echo $user->lang['JAB_TRANSPORTS']; ?></th>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['JAB_AIM_ENABLE']; ?>: </b></span></td>
		<td class="row2"><input type="radio" name="jab_aim_enable" value="1"<?php echo $jab_aim_enable_yes; ?> /><?php echo $user->lang['ENABLED']; ?>&nbsp; &nbsp;<input type="radio" name="jab_aim_enable" value="0"<?php echo $jab_aim_enable_no; ?> /><?php echo $user->lang['DISABLED']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['AIM_USERNAME']; ?>: </b><br /><span class="gensmall"><?php echo sprintf($user->lang['AIM_USERNAME_EXPLAIN'], '<a href="http://my.screenname.aol.com/_cqr/homePg/hpController/controller.psp?siteId=snshomepage" target="_blank">', '</a>'); ?></span></td>
		<td class="row2"><input class="post" type="text" name="jab_aim_user" value="<?php echo $new['jab_aim_user']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['AIM_PASSWORD']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="jab_aim_pass" value="<?php echo $new['jab_aim_pass']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['JAB_ICQ_ENABLE']; ?>: </b></span></td>
		<td class="row2"><input type="radio" name="jab_icq_enable" value="1"<?php echo $jab_icq_enable_yes; ?> /><?php echo $user->lang['ENABLED']; ?>&nbsp; &nbsp;<input type="radio" name="jab_icq_enable" value="0"<?php echo $jab_icq_enable_no; ?> /><?php echo $user->lang['DISABLED']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ICQ_USERNAME']; ?>: </b><br /><span class="gensmall"><?php echo sprintf($user->lang['ICQ_USERNAME_EXPLAIN'], '<a href="http://go.icq.com/register/" target="_blank">', '</a>'); ?></span></td>
		<td class="row2"><input class="post" type="text" name="jab_icq_user" value="<?php echo $new['jab_icq_user']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ICQ_PASSWORD']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="jab_icq_pass" value="<?php echo $new['jab_icq_pass']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['JAB_MSN_ENABLE']; ?>: </b></span></td>
		<td class="row2"><input type="radio" name="jab_msn_enable" value="1"<?php echo $jab_msn_enable_yes; ?> /><?php echo $user->lang['ENABLED']; ?>&nbsp; &nbsp;<input type="radio" name="jab_msn_enable" value="0"<?php echo $jab_msn_enable_no; ?> /><?php echo $user->lang['DISABLED']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MSN_USERNAME']; ?>: </b><br /><span class="gensmall"><?php echo sprintf($user->lang['MSN_USERNAME_EXPLAIN'], '<a href="http://www.passport.net/Consumer/default.asp?lc=1033" target="_blank">', '</a>'); ?></span></td>
		<td class="row2"><input class="post" type="text" name="jab_msn_user" value="<?php echo $new['jab_msn_user']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MSN_PASSWORD']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="jab_msn_pass" value="<?php echo $new['jab_msn_pass']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['JAB_YIM_ENABLE']; ?>: </b></span></td>
		<td class="row2"><input type="radio" name="jab_yim_enable" value="1"<?php echo $jab_yim_enable_yes; ?> /><?php echo $user->lang['ENABLED']; ?>&nbsp; &nbsp;<input type="radio" name="jab_yim_enable" value="0"<?php echo $jab_yim_enable_no; ?> /><?php echo $user->lang['DISABLED']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YIM_USERNAME']; ?>: </b><br /><span class="gensmall"><?php echo sprintf($user->lang['YIM_USERNAME_EXPLAIN'], '<a href="http://edit.yahoo.com/config/eval_register?.src=pg&.done=http://messenger.yahoo.com" target="_blank">', '</a>'); ?></span></td>
		<td class="row2"><input class="post" type="text" name="jab_yim_user" value="<?php echo $new['jab_yim_user']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YIM_PASSWORD']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="jab_yim_pass" value="<?php echo $new['jab_yim_pass']; ?>" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
	</tr>
</table></form>

<?php

		adm_page_footer();

?>