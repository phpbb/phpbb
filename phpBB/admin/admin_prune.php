<?php
/***************************************************************************
*                                admin_prune.php
*                              -------------------
*     begin                : Mon Jul 31, 2001
*     copyright            : (C) 2001 The phpBB Group
*     email                : support@phpbb.com
*
*     $Id$
*
****************************************************************************/

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
	if (!$auth->acl_get('a_prune'))
	{
		return;
	}

	$module['FORUM']['PRUNE']   = basename(__FILE__) . $SID . '&amp;mode=forums';

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

// Do we have permission?
if (!$auth->acl_get('a_prune'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Get the forum ID for pruning
if (isset($_REQUEST['f']))
{
	$forum_id = intval($_REQUEST['f']);
	$forum_sql = ($forum_id == -1) ? '' : "AND forum_id = $forum_id";
}
else
{
	$forum_id = '';
	$forum_sql = '';
}


// Check for submit to be equal to Prune. If so then proceed with the pruning.
if (isset($_POST['doprune']))
{
	$prunedays = (isset($_POST['prunedays'])) ? intval($_POST['prunedays']) : 0;

	// Convert days to seconds for timestamp functions...
	$prunedate = time() - ($prunedays * 86400);

	page_header($user->lang['PRUNE']);

?>

<h1><?php echo $user->lang['PRUNE']; ?></h1>

<p><?php echo $user->lang['PRUNE_SUCCESS']; ?></p>

<table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $user->lang['FORUM']; ?></th>
		<th><?php echo $user->lang['TOPICS_PRUNED']; ?></th>
		<th><?php echo $user->lang['POSTS_PRUNED']; ?></th>
	</tr>
<?php

	// Get a list of forum's or the data for the forum that we are pruning.
	$sql = "SELECT forum_id, forum_name 
		FROM " . FORUMS_TABLE . "
		ORDER BY left_id ASC";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$log_data = '';
		do
		{
			$p_result = prune($forum_rows[$i]['forum_id'], $prunedate);
			sync('forum', $forum_rows[$i]['forum_id']);

			$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['forum_name']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $p_result['topics']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $p_result['posts']; ?></td>
	</tr>
<?php
	
			$log_data .= (($log_data != '') ? ', ' : '') . $forum_rows[$i]['forum_name'];
		}
		while($row = $db->sql_fetchrow($result));

		add_log('admin', 'log_prune', $log_data);

	}
	else
	{

?>
	<tr>
		<td class="row1" align="center"><?php echo $user->lang['NO_PRUNE']; ?></td>
	</tr>
<?php

	}
	$db->sql_freeresult($result);

?>
</table>

<br clear="all" />

<?php

	page_footer();

}

page_header($user->lang['PRUNE']);

?>

<h1><?php echo $user->lang['PRUNE']; ?></h1>

<p><?php echo $user->lang['FORUM_PRUNE_EXPLAIN']; ?></p>

<?php

// If they haven't selected a forum for pruning yet then
// display a select box to use for pruning.
if (empty($forum_id))
{

	// Output a selection table if no forum id has been specified.
	$select_list = '<option value="-1">' . $user->lang['ALL_FORUMS'] . '</option>' . make_forum_select(false, false, false);

?>

<form method="post" action="admin_prune.<?php echo $phpEx . $SID; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th align="center"><?php echo $user->lang['SELECT_FORUM']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center">&nbsp;<select name="f"><?php echo $select_list; ?></select>&nbsp;&nbsp;<input type="submit" value="<?php echo $user->lang['LOOK_UP_FORUM']; ?>" class="mainoption" />&nbsp;</td>
	</tr>
</table></form>

<?php

}
else
{
	$sql = "SELECT forum_name 
		FROM " . FORUMS_TABLE . " 
		WHERE forum_id = $forum_id";
	$result = $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$forum_name = ($forum_id == -1) ? $user->lang['ALL_FORUMS'] : $row['forum_name'];

?>

<h2><?php echo $user->lang['FORUM'] . ': <i>' . $forum_name; ?></i></h2>

<form method="post"	action="admin_prune.<?php echo $phpEx . $SID; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th class="th"><?php echo $user->lang['FORUM_PRUNE']; ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo sprintf($user->lang['PRUNE_NOT_POSTED'], '<input type="text" name="prunedays" size="4" />'); ?></td>
	</tr>
	<tr>
		<td class="cat" align="center"><input type="hidden" name="f" value="<?php echo $forum_id; ?>" /><input type="submit" name="doprune" value="<?php echo $user->lang['DO_PRUNE']; ?>" class="mainoption"></td>
	</tr>
</table></form>

<?php

}

page_footer();

?>