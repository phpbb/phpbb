<?php
/***************************************************************************
 *                              admin_ban.php
 *                            -------------------
 *   begin                : Tuesday, Jul 31, 2001
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

define('IN_PHPBB', 1);

if( !empty($setmodules) )
{
	if ( !$acl->get_acl_admin('ban') )
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['Users']['Ban_users'] = $filename . "$SID&amp;mode=user";
	$module['Users']['Ban_emails'] = $filename . "$SID&amp;mode=email";
	$module['Users']['Ban_ips'] = $filename . "$SID&amp;mode=ip";

	return;
}

//
// Load default header
//
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

//
// Do we have ban permissions?
//
if ( !$acl->get_acl_admin('ban') )
{
	return;
}

//
// Mode setting
//
if ( isset($HTTP_POST_VARS['mode']) || isset($HTTP_GET_VARS['mode']) )
{
	$mode = ( isset($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	$mode = '';
}

$current_time = time();

//
// Start program
//
if ( isset($HTTP_POST_VARS['bansubmit']) )
{
	$ban_end = ( !empty($HTTP_POST_VARS['banlength']) ) ? $current_time + ( intval($HTTP_POST_VARS['banlength']) * 60 ) : 0;
	$ban_reason = ( isset($HTTP_POST_VARS['banreason']) ) ? $HTTP_POST_VARS['banreason'] : '';
	$ban_list = array_unique(explode("\n", $HTTP_POST_VARS['ban']));
	$ban_list_log = implode(', ', $ban_list);

	$banlist = array();

	switch ( $mode )
	{
		case 'user':
			$type = 'ban_userid';

			$banlist_tmp = array();
			for($i = 0; $i < count($ban_list); $i++)
			{
				if ( trim($ban_list[$i]) != '' )
				{
					$banlist_tmp[] = '\'' . trim($ban_list[$i]) . '\'';
				}
			}

			$sql = "SELECT user_id 
				FROM " . USERS_TABLE . " 
				WHERE username IN (" . implode(', ', $banlist_tmp) . ")";
			$result = $db->sql_query($sql);

			if ( $row = $db->sql_fetchrow($result) )
			{
				do
				{
					$banlist[] = $row['user_id'];
				}
				while ( $row = $db->sql_fetchrow($result) );
			}
			unset($banlist_tmp);
			break;

		case 'ip':
			$type = 'ban_ip';

			for($i = 0; $i < count($ban_list); $i++)
			{
				if ( preg_match('/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})[ ]*\-[ ]*([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/', trim($ban_list[$i]), $ip_range_explode) )
				{
					//
					// Don't ask about all this, just don't ask ... !
					//
					$ip_1_counter = $ip_range_explode[1];
					$ip_1_end = $ip_range_explode[5];

					while ( $ip_1_counter <= $ip_1_end )
					{
						$ip_2_counter = ( $ip_1_counter == $ip_range_explode[1] ) ? $ip_range_explode[2] : 0;
						$ip_2_end = ($ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[6];

						if( $ip_2_counter == 0 && $ip_2_end == 254 )
						{
							$ip_2_counter = 256;
							$ip_2_fragment = 256;

							$banlist[] = "'$ip_1_counter.'";
						}

						while ( $ip_2_counter <= $ip_2_end )
						{
							$ip_3_counter = ( $ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1] ) ? $ip_range_explode[3] : 0;
							$ip_3_end = ($ip_2_counter < $ip_2_end || $ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[7];

							if ( $ip_3_counter == 0 && $ip_3_end == 254 )
							{
								$ip_3_counter = 256;
								$ip_3_fragment = 256;

								$banlist[] = "'$ip_1_counter.$ip_2_counter.'";
							}

							while ( $ip_3_counter <= $ip_3_end )
							{
								$ip_4_counter = ( $ip_3_counter == $ip_range_explode[3] && $ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1] ) ? $ip_range_explode[4] : 0;
								$ip_4_end = ($ip_3_counter < $ip_3_end || $ip_2_counter < $ip_2_end) ? 254 : $ip_range_explode[8];

								if ( $ip_4_counter == 0 && $ip_4_end == 254 )
								{
									$ip_4_counter = 256;
									$ip_4_fragment = 256;

									$banlist[] = "'$ip_1_counter.$ip_2_counter.$ip_3_counter.'";
								}

								while ( $ip_4_counter <= $ip_4_end )
								{
									$banlist[] = "'$ip_1_counter.$ip_2_counter.$ip_3_counter.$ip_4_counter'";
									$ip_4_counter++;
								}
								$ip_3_counter++;
							}
							$ip_2_counter++;
						}
						$ip_1_counter++;
					}
				}
				else if ( preg_match('/^([\w\-_]\.?){2,}$/is', trim($ban_list[$i])) )
				{
					$ip = gethostbynamel(trim($ban_list[$i]));

					for($j = 0; $j < count($ip); $j++)
					{
						if ( !empty($ip[$j]) )
						{
							$banlist[] = '\'' . $ip[$j] . '\'';
						}
					}
				}
				else if ( preg_match('/^([0-9]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})$/', trim($ban_list[$i])) )
				{
					$banlist[] = '\'' . str_replace('*', '', trim($ban_list[$i])) . '\'';
				}
			}
			break;

		case 'email':
			$type = 'ban_email';

			for($i = 0; $i < count($ban_list); $i++)
			{
				//
				// This ereg match is based on one by php@unreelpro.com
				// contained in the annotated php manual at php.com (ereg
				// section)
				//
				if ( eregi('^(([[:alnum:]\*]+([-_.][[:alnum:]\*]+)*\.?)|(\*))@([[:alnum:]]+([-_]?[[:alnum:]]+)*\.){1,3}([[:alnum:]]{2,6})$', trim($ban_list[$i])) )
				{
					$banlist[] = '\'' . trim($ban_list[$i]) . '\'';
				}
			}
			break;
	}

	$sql = "SELECT $type 
		FROM " . BANLIST_TABLE . " 
		WHERE $type <> ''";
	$result = $db->sql_query($sql);

	if ( $row = $db->sql_fetchrow($result) )
	{
		$banlist_tmp = array();
		do
		{
			switch ( $mode )
			{
				case 'user':
					$banlist_tmp[] = $row['ban_userid'];
					break;

				case 'ip':
					$banlist_tmp[] = '\'' . $row['ban_ip'] . '\'';
					break;

				case 'email':
					$banlist_tmp[] = '\'' . $row['ban_email'] . '\'';
					break;
			}
		}
		while ( $row = $db->sql_fetchrow($result) );

		$banlist = array_unique(array_diff($banlist, $banlist_tmp));
		unset($banlist_tmp);
	}	

	if ( sizeof($banlist) )
	{
		for($i = 0; $i < count($banlist); $i++)
		{
			$sql = "INSERT INTO " . BANLIST_TABLE . " ($type, ban_start, ban_end, ban_reason) 
				VALUES (" . $banlist[$i] . ", $current_time, $ban_end, '$ban_reason')";
			$db->sql_query($sql);
		}

		$sql = '';
		switch ( $mode )
		{
			case 'user':
				$sql = "WHERE session_user_id IN (" . implode(', ', $banlist) . ")";
				break;

			case 'ip':
				$sql = "WHERE session_ip IN (" . implode(', ', $banlist) . ")";
				break;

			case 'email':
				$sql = "SELECT user_id 
					FROM " . USERS_TABLE . " 
					WHERE user_email IN (" . implode(', ', $banlist) . ")";
				$result = $db->sql_query($sql);

				$sql = '';
				if ( $row = $db->sql_fetchrow($result) )
				{
					do
					{
						$sql .= ( ( $sql != '' ) ? ', ' : '' ) . $row['user_id'];
					}
					while ( $row = $db->sql_fetchrow($result) );

					$sql = "WHERE session_user_id IN (" . str_replace('*', '%', $sql) . ")";
				}
				break;
		}

		if ( $sql != '' )
		{
			$sql = "DELETE FROM " . SESSIONS_TABLE . " 
				$sql";
			$db->sql_query($sql);
		}

		//
		// Update log
		//
		add_admin_log('log_ban_' . $mode, $ban_list_log, $ban_reason);
	}

	$message = $lang['Ban_update_sucessful'] . '<br /><br />' . sprintf($lang['Click_return_banadmin'], '<a href="' . "admin_ban.$phpEx$SID&amp;mode=$mode" . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_admin_index'], '<a href="' . "index.$phpEx$SID&amp;pane=right" . '">', '</a>');
	message_die(MESSAGE, $message);

}
else if ( isset($HTTP_POST_VARS['unbansubmit']) )
{
	$unban_sql = '';
	for($i = 0; $i < count($HTTP_POST_VARS['unban']); $i++ )
	{
		$unban_sql .= ( ( $unban_sql != '' ) ? ', ' : '' ) . intval($HTTP_POST_VARS['unban'][$i]);
	}

	if ( $unban_sql != '' )
	{
		$sql = "DELETE FROM " . BANLIST_TABLE . " 
			WHERE ban_id IN ($unban_sql)";
		$db->sql_query($sql);

		add_admin_log('log_unban_' . $mode, sizeof($HTTP_POST_VARS['unban']));
	}

	$message = $lang['Ban_update_sucessful'] . '<br /><br />' . sprintf($lang['Click_return_banadmin'], '<a href="' . "admin_ban.$phpEx$SID&amp;mode=$mode" . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_admin_index'], '<a href="' . "index.$phpEx$SID&amp;pane=right" . '">', '</a>');
	message_die(MESSAGE, $message);
}

//
// Output relevant entry page
//

//
// Remove timed out bans
//
$sql = "DELETE FROM " . BANLIST_TABLE . " 
	WHERE ban_end < " . time() . " 
		AND ban_end <> 0";
$db->sql_query($sql);

//
// Ban length options
//
$ban_end_text = array(0 => $lang['Permanent'], 30 => $lang['30_Mins'], 60 => $lang['1_Hour'], 360 => $lang['6_Hours'], 1440 => $lang['1_Day'], 10080 => $lang['7_Days'], 20160 => $lang['2_Weeks'], 40320 => $lang['1_Month']);

$ban_end_options = '';
foreach ( $ban_end_text as $length => $text )
{
	$ban_end_options .= '<option value="' . $length . '">' . $text . '</option>';
}

//
// Title
//
switch ( $mode )
{
	case 'user':
		$l_title = $lang['Ban_users'];
		break;
	case 'email':
		$l_title = $lang['Ban_emails'];
		break;
	case 'ip':
		$l_title = $lang['Ban_ips'];
		break;
}

//
// Output page
//
page_header($l_title);

?>

<p><?php echo $lang['Ban_explain']; ?></p>

<?php

switch ( $mode )
{
	case 'user':
		$sql = "SELECT b.*, u.user_id, u.username
			FROM " . BANLIST_TABLE . " b, " . USERS_TABLE . " u
			WHERE ( b.ban_end >= " . time() . " 
					OR b.ban_end = 0 )
				AND u.user_id = b.ban_userid
				AND b.ban_userid <> 0
				AND u.user_id <> " . ANONYMOUS . "
			ORDER BY u.user_id ASC";
		$result = $db->sql_query($sql);

		$banned_options = '';
		$banned_length = '';
		$banned_options = '';
		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$banned_options .= '<option value="' . $row['ban_id'] . '">' . $row['username'] . '</option>';
				$banned_length .= ( ( $banned_length != '' ) ? ', ' : '' ) . '\'' . ( $ban_end_text[(($row['ban_end'] - $row['ban_start']) / 60)] ) . '\'';
				$banned_reasons .= ( ( $banned_reasons != '' ) ? ', ' : '' ) . '\'' . addslashes($row['ban_reason']) . '\'';
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

		$l_ban_title = $lang['Ban_users'];
		$l_ban_explain = $lang['Ban_username_explain'];
		$l_unban_title = $lang['Unban_username'];
		$l_unban_explain = $lang['Unban_username_explain'];
		$l_ban_cell = $lang['Username'];
		$l_no_ban_cell = $lang['No_banned_users'];
		$s_submit_extra = '<input type="submit" name="usersubmit" value="' . $lang['Find_username'] . '" class="liteoption" onClick="window.open(\'../search.' . $phpEx . $SID . '&amp;mode=searchuser\', \'_phpbbsearch\', \'HEIGHT=250,resizable=yes,WIDTH=400\');return false;" />';

		break;

	case 'ip':

		$sql = "SELECT *  
			FROM " . BANLIST_TABLE . " 
			WHERE ( ban_end >= " . time() . " 
					OR ban_end = 0 ) 
				AND ban_ip <> ''";
		$result = $db->sql_query($sql);

		$banned_reasons = '';
		$banned_length = '';
		$banned_options = '';
		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$banned_options .= '<option value="' . $row['ban_id'] . '">' . $row['ban_ip'] . '</option>';
				$banned_length .= ( ( $banned_length != '' ) ? ', ' : '' ) . '\'' . ( $ban_end_text[(($row['ban_end'] - $row['ban_start']) / 60)] ) . '\'';
				$banned_reasons .= ( ( $banned_reasons != '' ) ? ', ' : '' ) . '\'' . addslashes($row['ban_reason']) . '\'';
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

		$l_ban_title = $lang['Ban_ips'];
		$l_ban_explain = $lang['Ban_IP_explain'];
		$l_unban_title = $lang['Unban_IP'];
		$l_unban_explain = $lang['Unban_IP_explain'];
		$l_ban_cell = $lang['IP_hostname'];
		$l_no_ban_cell = $lang['No_banned_ip'];
		$s_submit_extra = '';

		break;

	case 'email':

		$sql = "SELECT * 
			FROM " . BANLIST_TABLE . " 
			WHERE ( ban_end >= " . time() . " 
					OR ban_end = 0 ) 
				AND ban_email <> ''";
		$result = $db->sql_query($sql);

		$banned_options = '';
		$banned_length = '';
		$banned_options = '';
		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$banned_options .= '<option value="' . $row['ban_id'] . '">' . $row['ban_email'] . '</option>';
				$banned_length .= ( ( $banned_length != '' ) ? ', ' : '' ) . '\'' . ( $ban_end_text[(($row['ban_end'] - $row['ban_start']) / 60)] ) . '\'';
				$banned_reasons .= ( ( $banned_reasons != '' ) ? ', ' : '' ) . '\'' . addslashes($row['ban_reason']) . '\'';
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

		$l_ban_title = $lang['Ban_emails'];
		$l_ban_explain = $lang['Ban_email_explain'];
		$l_unban_title = $lang['Unban_email'];
		$l_unban_explain = $lang['Unban_email_explain'];
		$l_ban_cell = $lang['Email_address'];
		$l_no_ban_cell = $lang['No_banned_email'];
		$s_submit_extra = '';

		break;
}

?>

<h1><?php echo $l_ban_title; ?></h1>

<p><?php echo $l_ban_explain; ?></p>

<script language="Javascript" type="text/javascript">
<!-- 
	var ban_length = new Array(<?php echo $banned_length; ?>);
	var ban_reasons = new Array(<?php echo $banned_reasons; ?>);
//-->
</script>

<form method="post" action="<?php echo "admin_ban.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr> 
		<th colspan="2"><?php echo $l_ban_title; ?></th>
	</tr>
	<tr> 
		<td class="row1" width="45%"><?php echo $l_ban_cell; ?>: </td>
		<td class="row1"><textarea cols="40" rows="3" name="ban"></textarea></td>
	</tr>
	<tr>
		<td class="row2" width="45%"><?php echo $lang['Ban_length']; ?>:</td>
		<td class="row2"><select name="banlength"><?php echo $ban_end_options; ?></select></td>
	</tr>
	<tr>
		<td class="row2" width="45%"><?php echo $lang['Ban_reason']; ?>:</td>
		<td class="row2"><input type="text" name="banreason" maxlength="255" size="40" /></td>
	</tr>
	<tr> 
		<td class="cat" colspan="2" align="center"> <input type="submit" name="bansubmit" value="<?php echo $lang['Submit']; ?>" class="mainoption" />&nbsp; <input type="reset" value="<?php echo $lang['Reset']; ?>" class="liteoption" />&nbsp; <?php echo $s_submit_extra; ?></td>
	</tr>
</table>

<h1><?php echo $l_unban_title; ?></h1>

<p><?php echo $l_unban_explain; ?></p>

<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr> 
		<th colspan="2"><?php echo $l_unban_title; ?></th>
	</tr>
	<tr> 
		<td class="row1" width="45%"><?php echo $lang['Email_address']; ?>: <br /></td>
		<td class="row1"> <?php if ( $banned_options != '' ) { ?><select name="unban[]" multiple="multiple" size="5"><?php echo $banned_options; ?></select><?php } else { echo $l_no_ban_cell;  } ?></td>
	</tr>
	<tr> 
		<td class="cat" colspan="2" align="center"><input type="submit" name="unbansubmit" value="<?php echo $lang['Submit']; ?>" class="mainoption" /></td>
	</tr>
</table></form>

<?php

page_footer();

?>