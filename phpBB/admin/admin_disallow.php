<?php
/***************************************************************************
 *                            admin_disallow.php
 *                            -------------------
 *   begin                : Tuesday, Oct 05, 2001
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
	if ( !$auth->acl_get('a_user') )
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['Users']['Disallow'] = $filename . $SID;

	return;
}

define('IN_PHPBB', 1);
//
// Include files
//
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

//
// Do we have user admin permissions?
//
if ( !$auth->acl_get('a_user') )
{
	return;
}

if( isset($_POST['add_name']) )
{
	include($phpbb_root_path . 'includes/functions_validate.'.$phpEx);

	$disallowed_user = ( isset($_POST['disallowed_user']) ) ? $_POST['disallowed_user'] : $_GET['disallowed_user'];
	$disallowed_user = str_replace('*', '%', $disallowed_user);

	if ( !validate_username($disallowed_user) )
	{
		$message = $user->lang['Disallowed_already'];
	}
	else
	{
		$sql = "INSERT INTO " . DISALLOW_TABLE . " (disallow_username)
			VALUES('" . str_replace("\'", "''", $disallowed_user) . "')";
		$result = $db->sql_query( $sql );

		$message = $user->lang['Disallow_successful'];
	}

	$message .= '<br /><br />' . sprintf($user->lang['Click_return_disallowadmin'], '<a href="' . "admin_disallow.$phpEx$SID" . '">', '</a>') . '<br /><br />' . sprintf($user->lang['Click_return_admin_index'], '<a href="' . "index.$phpEx$SID&amp;pane=right" . '">', '</a>');

	add_admin_log('log_disallow_add', str_replace('%', '*', $disallowed_user));

	message_die(MESSAGE, $message);
}
else if( isset($_POST['delete_name']) )
{
	$disallowed_id = ( isset($_POST['disallowed_id']) ) ? intval( $_POST['disallowed_id'] ) : intval( $_GET['disallowed_id'] );

	$sql = "DELETE FROM " . DISALLOW_TABLE . "
		WHERE disallow_id = $disallowed_id";
	$db->sql_query($sql);

	$message .= $user->lang['Disallowed_deleted'] . '<br /><br />' . sprintf($user->lang['Click_return_disallowadmin'], '<a href="' . "admin_disallow.$phpEx$SID" . '">', '</a>') . '<br /><br />' . sprintf($user->lang['Click_return_admin_index'], '<a href="' . "index.$phpEx$SID&amp;pane=right" . '">', '</a>');

	add_admin_log('log_disallow_delete');

	message_die(MESSAGE, $message);

}

//
// Grab the current list of disallowed usernames...
//
$sql = "SELECT *
	FROM " . DISALLOW_TABLE;
$result = $db->sql_query($sql);

$disallow_select = '';
if ( $row = $db->sql_fetchrow($result) )
{
	do
	{
		$disallow_select .= '<option value="' . $row['disallow_id'] . '">' . str_replace('%', '*', $row['disallow_username']) . '</option>';
	}
	while ( $row = $db->sql_fetchrow($result) );
}

//
// Output page
//
page_header($user->lang['Users']);

?>

<h1><?php echo $user->lang['Disallow_control']; ?></h1>

<p><?php echo $user->lang['Disallow_explain']; ?></p>

<form method="post" action="<?php echo "admin_disallow.$phpEx$SID"; ?>"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['Add_disallow_title']; ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Username']; ?><br /><span class="gensmall"><?php echo $user->lang['Add_disallow_explain']; ?></span></td>
		<td class="row2"><input type="text" name="disallowed_user" size="30" />&nbsp;<input type="submit" name="add_name" value="<?php echo $user->lang['Add_disallow']; ?>" class="mainoption" /></td>
	</tr>
	<tr>
		<th colspan="2"><?php echo $user->lang['Delete_disallow_title']; ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Username']; ?><br /><span class="gensmall"><?php echo $user->lang['Delete_disallow_explain']; ?></span></td>
		<td class="row2"><?php if ( $disallow_select != '' ) { ?><select name="disallowed_id"><?php echo $disallow_select; ?></select>&nbsp;<input type="submit" name="delete_name" value="<?php echo $user->lang['Delete']; ?>" class="liteoption" /><?php } else { echo $user->lang['No_disallowed']; } ?></td>
	</tr>
</table></form>

<?php

page_footer();

?>