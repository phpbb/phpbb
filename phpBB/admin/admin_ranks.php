<?php
/***************************************************************************
 *                              admin_ranks.php
 *                            -------------------
 *   begin                : Thursday, Jul 12, 2001
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

	$file = basename(__FILE__);
	$module['Users']['Ranks'] = "$file$SID";
	return;
}

//
// Let's set the root dir for phpBB
//
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

//
//
//
if ( !$auth->acl_get('a_user') )
{
	return;
}

//
//
//
if ( isset($_GET['mode']) || isset($_POST['mode']) )
{
	$mode = ( isset($_POST['mode']) ) ? $_POST['mode'] : $_GET['mode'];
}
else
{
	//
	// These could be entered via a form button
	//
	if ( isset($_POST['add']) )
	{
		$mode = 'add';
	}
	else if ( isset($_POST['save']) )
	{
		$mode = 'save';
	}
	else
	{
		$mode = '';
	}
}

//
//
//
if ( $mode != '' )
{
	if ( $mode == 'edit' || $mode == 'add' )
	{
		//
		// They want to add a new rank, show the form.
		//
		$rank_id = ( isset($_GET['id']) ) ? intval($_GET['id']) : 0;

		$s_hidden_fields = '<input type="hidden" name="mode" value="save" />';

		if ( $mode == 'edit' )
		{
			if ( empty($rank_id) )
			{
				message_die(MESSAGE, $lang['Must_select_rank']);
			}

			$sql = "SELECT * FROM " . RANKS_TABLE . "
				WHERE rank_id = $rank_id";
			$result = $db->sql_query($sql);

			$rank_info = $db->sql_fetchrow($result);
			$s_hidden_fields .= '<input type="hidden" name="id" value="' . $rank_id . '" />';

		}
		else
		{
			$rank_info['rank_special'] = 0;
		}

		page_header($lang['Ranks']);

?>

<h1><?php echo $lang['Ranks']; ?></h1>

<p><?php echo $lang['Ranks_explain']; ?></p>

<form method="post" action="<?php echo "admin_ranks.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $lang['Ranks']; ?></th>
	</tr>
	<tr>
		<td class="row1" width="40%"><?php echo $lang['Rank_title']; ?>: </td>
		<td class="row2"><input type="text" name="title" size="35" maxlength="40" value="<?php echo $rank_info['rank_title']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><?php echo $lang['Rank_special']; ?>: </td>
		<td class="row2"><input type="radio" name="special_rank" value="1"<?php echo ( $rank_info['rank_special'] ) ? ' checked="checked"' : ''; ?> /><?php echo $lang['Yes']; ?> &nbsp;&nbsp;<input type="radio" name="special_rank" value="0"<?php echo ( !$rank_info['rank_special'] ) ? ' checked="checked"' : ''; ?> /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><?php echo $lang['Rank_minimum']; ?>: </td>
		<td class="row2"><input type="text" name="min_posts" size="5" maxlength="10" value="<?php echo ( $rank_info['rank_special'] ) ? '' : $rank_info['rank_min']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="40%"><?php echo $lang['Rank_image']; ?>: <br /><span class="gensmall"><?php echo $lang['Rank_image_explain']; ?></span></td>
		<td class="row2"><input type="text" name="rank_image" size="40" maxlength="255" value="<?php echo ( $rank_info['rank_image'] != '' ) ? $rank_info['rank_image'] : ''; ?>" /><br /><?php echo ( $rank_info['rank_image'] != '' ) ? '<img src="../' . $rank_info['rank_image'] . '" />' : ''; ?></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><?php echo $s_hidden_fields; ?><input type="submit" name="submit" value="<?php echo $lang['Submit']; ?>" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="<?php echo $lang['Reset']; ?>" class="liteoption" /></td>
	</tr>
</table></form>

<?php

		page_footer();

	}
	else if ( $mode == 'save' )
	{
		//
		// Ok, they sent us our info, let's update it.
		//

		$rank_id = ( isset($_POST['id']) ) ? intval($_POST['id']) : 0;
		$rank_title = ( isset($_POST['title']) ) ? trim($_POST['title']) : '';
		$special_rank = ( $_POST['special_rank'] == 1 ) ? TRUE : 0;
		$min_posts = ( isset($_POST['min_posts']) ) ? intval($_POST['min_posts']) : -1;
		$rank_image = ( (isset($_POST['rank_image'])) ) ? trim($_POST['rank_image']) : '';

		if ( $rank_title == '' )
		{
			message_die(MESSAGE, $lang['Must_select_rank']);
		}

		if ( $special_rank == 1 )
		{
			$min_posts = -1;
		}

		//
		// The rank image has to be a jpg, gif or png
		//
		if ( $rank_image != '' )
		{
			if ( !preg_match('/(\.gif|\.png|\.jpg|\.jpeg)$/is', $rank_image))
			{
				$rank_image = '';
			}
		}

		if ( $rank_id )
		{
			$sql = "UPDATE " . RANKS_TABLE . "
				SET rank_title = '" . str_replace("\'", "''", $rank_title) . "', rank_special = $special_rank, rank_min = $min_posts, rank_image = '" . str_replace("\'", "''", $rank_image) . "'
				WHERE rank_id = $rank_id";

			$message = $lang['Rank_updated'];
		}
		else
		{
			$sql = "INSERT INTO " . RANKS_TABLE . " (rank_title, rank_special, rank_min, rank_image)
				VALUES ('" . str_replace("\'", "''", $rank_title) . "', $special_rank, $min_posts, '" . str_replace("\'", "''", $rank_image) . "')";

			$message = $lang['Rank_added'];
		}

		$db->sql_query($sql);

		$message .= '<br /><br />' . sprintf($lang['Click_return_rankadmin'], '<a href="' . "admin_ranks.$phpEx$SID" . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_admin_index'], '<a href="' . "index.$phpEx$SID&amp;pane=right" . '">', '</a>');

		message_die(MESSAGE, $message);

	}
	else if ( $mode == 'delete' )
	{
		//
		// Ok, they want to delete their rank
		//

		if ( isset($_POST['id']) || isset($_GET['id']) )
		{
			$rank_id = ( isset($_POST['id']) ) ? intval($_POST['id']) : intval($_GET['id']);
		}
		else
		{
			$rank_id = 0;
		}

		if ( $rank_id )
		{
			$sql = "DELETE FROM " . RANKS_TABLE . "
				WHERE rank_id = $rank_id";
			$db->sql_query($sql);

			$sql = "UPDATE " . USERS_TABLE . "
				SET user_rank = 0
				WHERE user_rank = $rank_id";
			$db->sql_query($sql);

			$message = $lang['Rank_removed'] . '<br /><br />' . sprintf($lang['Click_return_rankadmin'], '<a href="' . "admin_ranks.$phpEx$SID" . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_admin_index'], '<a href="' . "index.$phpEx$SID&amp;pane=right" . '">', '</a>');

			message_die(MESSAGE, $message);

		}
		else
		{
			message_die(MESSAGE, $lang['Must_select_rank']);
		}
	}
}

page_header($lang['Ranks']);

?>

<h1><?php echo $lang['Ranks']; ?></h1>

<p><?php echo $lang['Ranks_explain']; ?></p>

<form method="post" action="<?php echo "admin_ranks.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $lang['Rank_title']; ?></th>
        <th><?php echo $lang['Rank_minimum']; ?></th>
		<th><?php echo $lang['Rank_special']; ?></th>
		<th><?php echo $lang['Edit']; ?></th>
		<th><?php echo $lang['Delete']; ?></th>
	</tr>
<?php

//
// Show the default page
//
$sql = "SELECT * FROM " . RANKS_TABLE . "
	ORDER BY rank_min ASC, rank_special ASC";
$result = $db->sql_query($sql);

if ( $row = $db->sql_fetchrow($result) )
{
	do
	{
		$row_class = ( $row_class != 'row1' ) ? 'row1' : 'row2';
?>
	<tr>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['rank_title']; ?></td>
        <td class="<?php echo $row_class; ?>" align="center"><?php echo ( $row['rank_special'] ) ? '-' : $row['rank_min']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo ( $row['rank_special'] ) ? $lang['Yes'] : $lang['No']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><a href="<?php echo "admin_ranks.$phpEx$SID&amp;mode=edit&amp;id=" . $row['rank_id']; ?>"><?php echo $lang['Edit']; ?></a></td>
		<td class="<?php echo $row_class; ?>" align="center"><a href="<?php echo "admin_ranks.$phpEx$SID&amp;mode=delete&amp;id=" . $row['rank_id']; ?>"><?php echo $lang['Delete']; ?></a></td>
	</tr>
<?php

	}
	while ( $row = $db->sql_fetchrow($result) );
}

?>
	<tr>
		<td class="cat" colspan="6" align="center"><input type="submit" class="mainoption" name="add" value="<?php echo $lang['Add_new_rank']; ?>" /></td>
	</tr>
</table></form>

<?php

page_footer();

?>