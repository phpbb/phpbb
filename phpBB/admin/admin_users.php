<?php
/***************************************************************************
 *                              admin_users.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if ( !empty($setmodules) )
{
	if ( !$auth->acl_get('a_user') )
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['Users']['Manage'] = $filename . $SID;

	return;
}

define('IN_PHPBB', 1);

// Include files
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);
require($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
require($phpbb_root_path . 'includes/functions_validate.'.$phpEx);

// Do we have forum admin permissions?
if ( !$auth->acl_get('a_user') )
{
	trigger_error($user->lang['No_admin']);
}

echo $mode;

// Set mode
if( isset( $_POST['mode'] ) || isset( $_GET['mode'] ) )
{
	$mode = ( isset( $_POST['mode']) ) ? $_POST['mode'] : $_GET['mode'];
}
else
{
	$mode = 'main';
}

// Begin program
if (  isset($_POST['username']) || isset($_GET['u']) || isset( $_POST['u']) )
{

	// Grab relevant userdata
	if( isset( $_GET['u']) || isset( $_POST['u']) )
	{
		$user_id = ( isset( $_POST['u']) ) ? intval( $_POST['u']) : intval( $_GET['u']);

		if( !($userdata = get_userdata($user_id)) )
		{
			trigger_error($user->lang['No_user_id_specified'] );
		}
	}
	else
	{
		if( !$userdata = get_userdata( $_POST['username'] ) )
		{
			trigger_error($user->lang['No_user_id_specified'] );
		}
	}

	// Update entry in DB
	if( $_POST['deleteuser'] && !$userdata['user_founder'] && $auth->acl_get('a_userdel') )
	{
		$db->sql_transaction();

		$sql = "UPDATE " . POSTS_TABLE . "
			SET poster_id = " . ANONYMOUS . ", post_username = '$username'
			WHERE poster_id = $user_id";
		$db->sql_query($sql);

		$sql = "UPDATE " . TOPICS_TABLE . "
			SET topic_poster = " . ANONYMOUS . "
			WHERE topic_poster = $user_id";
		$db->sql_query($sql);

		$sql = "DELETE FROM " . USERS_TABLE . "
			WHERE user_id = $user_id";
		$db->sql_query($sql);

		$sql = "DELETE FROM " . USER_GROUP_TABLE . "
			WHERE user_id = $user_id";
		$db->sql_query($sql);

		$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . "
			WHERE user_id = $user_id";
		$db->sql_query($sql);

		$sql = "DELETE FROM " . ACL_USERS_TABLE . "
			WHERE user_id = $user_id";
		$db->sql_query($sql);

		$db->sql_transaction('commit');

		trigger_error($user->lang['User_deleted']);
	}


	// Output relevant page
	page_header($user->lang['Manage']);

?>

<form method="post" action="admin_users.<?php echo $phpEx . $SID; ?>&amp;mode=<?php echo $mode; ?>&amp;u=<?php echo $userdata['user_id']; ?>"><table width="90%" cellspacing="3" cellpadding="0" border="0" align="center">
	<tr>
		<td align="right"><b>Main</b> | <a href="admin_users.<?php echo $phpEx . $SID; ?>&amp;u=<?php echo $userdata['user_id']; ?>&amp;mode=profile">Profile</a> | <a href="admin_users.<?php echo $phpEx . $SID; ?>&amp;u=<?php echo $userdata['user_id']; ?>&amp;mode=pref">Preferences</a> | <a href="admin_users.<?php echo $phpEx . $SID; ?>&amp;u=<?php echo $userdata['user_id']; ?>&amp;mode=avatar">Avatar</a> | <a href="admin_users.<?php echo $phpEx . $SID; ?>&amp;u=<?php echo $userdata['user_id']; ?>&amp;mode=permissions">Permissions</a></td>
	</tr>
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<th colspan="2"><?php echo $user->lang[$mode]; ?></td>
			</tr>
<?php

	switch ($mode)
	{
		case 'main':

?>
			<tr>
				<td class="row1">Username: <br /><span class="gensmall">Click profile to edit</span></td>
				<td class="row2"><?php echo $userdata['username']; ?> [ <a href="admin_ban.<?php echo $phpEx . $SID; ?>&amp;mode=user&amp;ban=<?php echo $userdata['username']; ?>&amp;bansubmit=true">Ban</a> ]</td>
			</tr>
			<tr>
				<td class="row1">Registered: </td>
				<td class="row2"><?php echo $user->format_date($userdata['user_regdate']); ?></td>
			</tr>
			<tr>
				<td class="row1">Registered from IP: </td>
				<td class="row2"><?php if ( $userdata['user_ip'] ) { echo $userdata['user_ip']; ?> [ <a href="admin_users.<?php echo $phpEx . $SID; ?>&amp;u=<?php echo $userdata['user_id']; ?>&amp;mode=main&amp;do=iplookup">Lookup</a> | <a href="admin_ban.<?php echo $phpEx . $SID; ?>&amp;mode=ip&amp;ban=<?php echo $userdata['user_ip']; ?>&amp;bansubmit=true">Ban</a> ] <?php } else { echo 'Unknown'; } ?></td>
			</tr>
<?php

			if ( isset($_GET['do']) && $_GET['do'] == 'iplookup' )
			{
				if ( $userdata['user_ip'] != '' && $domain = gethostbyaddr($userdata['user_ip']) )
				{
?>
			<tr>
				<th colspan="2">IP whois for <?php echo $domain; ?></th>
			</tr>
			<tr>
				<td class="row1" colspan="2"><?php

					if ( $ipwhois = ipwhois($userdata['user_ip']) )
					{
						echo '<br /><pre align="left">' . trim($ipwhois) . '</pre>';
					}
?></td>
			</tr>
<?php

				}
			}

?>
			<tr>
				<td class="row1">Total/Average posts by this user: </td>
				<td class="row2"></td>
			</tr>
			<tr>
				<td class="row1"></td>
				<td class="row2"></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<?php

			break;

		case 'permissions':

			$userauth = new auth();
			$userauth->acl($userdata);

			$sql = "SELECT forum_id, forum_name
				FROM " . FORUMS_TABLE . "
				ORDER BY left_id";
			$result = $db->sql_query($sql);

			foreach ($acl_options['global'] as $option_name => $option_id)
			{
				$global[$option_name] = $userauth->acl_get($option_name);
			}

			$permissions = array();
			while( $row = $db->sql_fetchrow($result) )
			{
				$forum_data[$row['forum_id']] = $row['forum_name'];

				foreach ($acl_options['local'] as $option_name => $option_id)
				{
					$local[$row['forum_id']][$option_name] = $userauth->acl_get($option_name, $row['forum_id']);
				}
			}

?>
			<tr>
				<td>Board-wide options</td>
				<td><table cellspacing="1" cellpadding="0" border="0">
<?php

			foreach ($global as $option => $allow)
			{
				echo '<tr><td>' . $option . ' => ' . ( ( $allow ) ? 'Allowed' : 'Denied' ) . '</td></tr>';
			}

?>
				</table></td>
			</tr>
			<tr>
<?php

			foreach ($local as $forum_id => $auth_ary)
			{

?>
				<td class="row1"><?php echo $forum_data[$forum_id]; ?></td>
				<td><table cellspacing="1" cellpadding="0" border="0">
<?php

				foreach ($auth_ary as $option => $allow)
				{
					echo '<tr><td>' . $option . ' => ' . ( ( $allow ) ? 'Allowed' : 'Denied' ) . '</td></tr>';
				}

?>
				</table></td>
			</tr>
<?php

			}

			break;

	}

	page_footer();

}
else
{

	page_header($user->lang['Manage']);

?>

<h1><?php echo $user->lang['User_admin']; ?></h1>

<p><?php echo $user->lang['User_admin_explain']; ?></p>

<form method="post" name="post" action="<?php echo "admin_users.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th align="center"><?php echo $user->lang['Select_a_User']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><input type="text" class="post" name="username" maxlength="50" size="20" /> <input type="submit" name="submituser" value="<?php echo $user->lang['Look_up_user']; ?>" class="mainoption" /> <input type="submit" name="usersubmit" value="<?php echo $user->lang['Find_username']; ?>" class="liteoption" onClick="window.open('<?php echo "../memberslist.$phpEx$SID&amp;mode=searchuser&amp;field=username"; ?>', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=650');return false;" /></td>
	</tr>
</table></form>

<?php

}

page_footer();

//
//
function ipwhois($ip)
{
	$ipwhois = '';

	$match = array(
		'#RIPE\.NET#is' => 'whois.ripe.net',
		'#whois\.apnic\.net#is' => 'whois.ripe.net',
		'#nic\.ad\.jp#is' => 'whois.ripe.net',
		'#whois\.registro\.br#is' => 'whois.ripe.net'
	);

	if ( ($fsk = fsockopen('whois.arin.net', 43)) )
	{
		@fputs($fsk, "$ip\n");
		while (!feof($fsk) )
		{
			$ipwhois .= fgets($fsk, 1024);
		}
		fclose($fsk);
	}

	foreach ( array_keys($match) as $server )
	{
		if ( preg_match($server, $ipwhois) )
		{
			$ipwhois = '';
			if ( ($fsk = fsockopen($match[$server], 43)) )
			{
				@fputs($fsk, "$ip\n");
				while (!feof($fsk) )
				{
					$ipwhois .= fgets($fsk, 1024);
				}
				fclose($fsk);
			}
			break;
		}
	}

	return $ipwhois;
}
//
//

?>