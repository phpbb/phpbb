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

if (!empty($setmodules))
{
	if (!$auth->acl_get('a_names'))
	{
		return;
	}

	$module['USER']['DISALLOW'] = basename(__FILE__) . $SID;

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

// Check permissions
if (!$auth->acl_get('a_names'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

if (isset($_POST['disallow']))
{
	$disallowed_user = (isset($_REQUEST['disallowed_user'])) ? $_REQUEST['disallowed_user'] : '';
	$disallowed_user = str_replace('*', '%', $disallowed_user);

	if (validate_username($disallowed_user))
	{
		$message = $user->lang['Disallowed_already'];
	}
	else
	{
		$sql = "INSERT INTO " . DISALLOW_TABLE . " (disallow_username)
			VALUES('" . str_replace("\'", "''", $disallowed_user) . "')";
		$result = $db->sql_query($sql);

		$message = $user->lang['Disallow_successful'];
	}

	add_admin_log('log_disallow_add', str_replace('%', '*', $disallowed_user));

	trigger_error($message);
}
else if (isset($_POST['allow']))
{
	$disallowed_id = (isset($_REQUEST['disallowed_id'])) ? intval($_REQUEST['disallowed_id']) : '';

	if (empty($disallowed_id))
	{
		trigger_error($user->lang['No_user_selected']);
	}

	$sql = "DELETE FROM " . DISALLOW_TABLE . "
		WHERE disallow_id = $disallowed_id";
	$db->sql_query($sql);

	add_admin_log('log_disallow_delete');

	trigger_error($user->lang['Disallowed_deleted']);
}

// Grab the current list of disallowed usernames...
$sql = "SELECT *
	FROM " . DISALLOW_TABLE;
$result = $db->sql_query($sql);

$disallow_select = '';
if ($row = $db->sql_fetchrow($result))
{
	do
	{
		$disallow_select .= '<option value="' . $row['disallow_id'] . '">' . str_replace('%', '*', $row['disallow_username']) . '</option>';
	}
	while ($row = $db->sql_fetchrow($result));
}

// Output page
page_header($user->lang['DISALLOW']);

?>

<h1><?php echo $user->lang['DISALLOW']; ?></h1>

<p><?php echo $user->lang['Disallow_explain']; ?></p>

<form method="post" action="<?php echo "admin_disallow.$phpEx$SID"; ?>"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['Add_disallow_title']; ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['USERNAME']; ?><br /><span class="gensmall"><?php echo $user->lang['Add_disallow_explain']; ?></span></td>
		<td class="row2"><input type="text" name="disallowed_user" size="30" />&nbsp;</td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="mainoption" type="submit" name="disallow" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="liteoption" type="reset" value="<?php echo $user->lang['RESET']; ?>" />
	</tr>
</table>

<h1><?php echo $user->lang['Delete_disallow_title']; ?></h1>

<p><?php echo $user->lang['Delete_disallow_explain']; ?></p>

<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['Delete_disallow_title']; ?></th>
	</tr>
<?php

	if ($disallow_select != '')
	{

?>
	<tr>
		<td class="row1"><?php echo $user->lang['USERNAME']; ?></td>
		<td class="row2"><select name="disallowed_id"><?php echo $disallow_select; ?></select></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="mainoption" type="submit" name="allow" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="liteoption" type="reset" value="<?php echo $user->lang['RESET']; ?>" />
	</tr>
<?php

	}
	else
	{

?>
	<tr>
		<td class="row1" colspan="2" align="center"><?php echo $user->lang['No_disallowed']; ?></td>
	</tr>
<?php

	}

?>
</table></form>

<?php

page_footer();

?>