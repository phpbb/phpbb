<?php
/***************************************************************************
 *                           admin_permissions.php
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
	if ( !$acl->get_acl_admin('forums') )
	{
		return;
	}
	
	$filename = basename(__FILE__);
	$module['Forums']['Permissions']   = $filename . $SID . '&amp;mode=forums';
	$module['General']['Set_Administrators']   = $filename . $SID . '&amp;mode=admins';

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
// Do we have forum admin permissions?
//
if ( !$acl->get_acl_admin('forums') )
{
	message_die(MESSAGE, $lang['No_admin']);
}

//
// Define some vars
//
if ( isset($HTTP_GET_VARS['f']) || isset($HTTP_POST_VARS['f']) )
{
	$forum_id = ( isset($HTTP_POST_VARS['f']) ) ? intval($HTTP_POST_VARS['f']) : intval($HTTP_GET_VARS['f']);

	$forum_sql = " WHERE forum_id = $forum_id";
}
else
{
	unset($forum_id);
	$forum_sql = '';
}

//
// Start program proper
//

//
// Get required information, either all forums if
// no id was specified or just the requsted if it
// was
//
if ( !empty($forum_id) )
{
	//
	// Output the selection table if no forum id was
	// specified
	//
	$template->set_filenames(array(
		"body" => "admin/auth_select_body.tpl")
	);

	$select_list = '<select name=f">';
	for($i = 0; $i < count($forum_rows); $i++)
	{
		$select_list .= '<option value="' . $forum_rows[$i]['forum_id'] . '">' . $forum_rows[$i]['forum_name'] . '</option>';
	}
	$select_list .= '</select>';
}
else
{
	$sql = "SELECT forum_id, forum_name 
		FROM " . FORUMS_TABLE . "  
		ORDER BY cat_id ASC, forum_order ASC";
	$result = $db->sql_query($sql);

	$select_list = '';
	while ( $row = $db->sql_fetchrow($result) )
	{
		$select_list .= '<option value="' . $row['forum_id'] . '">' . $row['forum_name'] . '</option>';
	}
	$db->sql_freeresult($result);

	page_header($lang['Forums']);

?>

<h1><?php echo $lang['Permissions']; ?></h1>

<p><?php echo $lang['Permissions_explain']; ?></p>

<form method="post" action="<?php echo "admin_permissions.$phpEx$SID"; ?>"><table cellspacing="1" cellpadding="4" border="0" align="center" bgcolor="#98AAB1">
	<tr>
		<th align="center"><?php echo $lang['Select_a_Forum']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center">&nbsp;<select name="f"><?php echo $select_list; ?></select>&nbsp;&nbsp;<input type="submit" value="<?php echo $lang['Look_up_Forum']; ?>" class="mainoption" />&nbsp;</td>
	</tr>
</table></form>

<?php

}

page_footer();

?>