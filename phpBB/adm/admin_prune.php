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
$forum_id = (isset($_REQUEST['f'])) ? intval($_REQUEST['f']) : 0;

// Check for submit to be equal to Prune. If so then proceed with the pruning.
if (isset($_POST['doprune']))
{
	$prunedays = (isset($_POST['prunedays'])) ? intval($_POST['prunedays']) : 0;

	// Convert days to seconds for timestamp functions...
	$prunedate = time() - ($prunedays * 86400);

	adm_page_header($user->lang['PRUNE']);

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
	// NOTE: this query will conceal all forum names, even those the user isn't authed for
	$sql = 'SELECT forum_id, forum_name 
		FROM ' . FORUMS_TABLE . '
		WHERE forum_type = ' . FORUM_POST . ' ' . (($forum_id) ? ' AND forum_id = ' . $forum_id : '') . '
		ORDER BY left_id ASC';
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$prune_ids = array();
		$log_data = '';
		do
		{
			$prune_ids[] = $row['forum_id'];
			$p_result = prune($row['forum_id'], $prunedate, FALSE);
			$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['forum_name']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $p_result['topics']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $p_result['posts']; ?></td>
	</tr>
<?php
	
			$log_data .= (($log_data != '') ? ', ' : '') . $row['forum_name'];
		}
		while ($row = $db->sql_fetchrow($result));

		add_log('admin', 'log_prune', $log_data);

		// Sync all pruned forums at once
		sync('forum', 'forum_id', $prune_ids, TRUE);
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

	adm_page_footer();

}

adm_page_header($user->lang['PRUNE']);

?>

<h1><?php echo $user->lang['PRUNE']; ?></h1>

<p><?php echo $user->lang['FORUM_PRUNE_EXPLAIN']; ?></p>

<?php

// If they haven't selected a forum for pruning yet then
// display a select box to use for pruning.
if (!$forum_id)
{

	// Output a selection table if no forum id has been specified.
	$select_list = make_forum_select(false, false, false);

?>

<form method="post" action="<?php echo "admin_prune.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th align="center"><?php echo $user->lang['SELECT_FORUM']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><select name="f[]" multiple="true" size="5"><?php echo $select_list; ?></select></td>
	</tr>
	<tr>
		<td class="cat" align="center"><input class="mainoption" type="submit" value="<?php echo $user->lang['LOOK_UP_FORUM']; ?>" /></td>
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

<form method="post"	action="<?php echo "admin_prune.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['FORUM_PRUNE']; ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['PRUNE_NOT_POSTED']; ?></td>
		<td class="row2"><input type="text" name="prune_days" size="4" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['PRUNE_OLD_POLLS'] ?>: <br /><span class="gensmall"><?php echo $user->lang['PRUNE_OLD_POLLS_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="prune_old_polls" value="1" /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="prune_old_polls" value="0" checked="checked" /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['PRUNE_ANNOUNCEMENTS'] ?>: </td>
		<td class="row2"><input type="radio" name="prune_announce" value="1" /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="prune_announce" value="0" checked="checked" /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['PRUNE_STICKY'] ?>: </td>
		<td class="row2"><input type="radio" name="prune_sticky" value="1" /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="prune_sticky" value="0" checked="checked" /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="hidden" name="f" value="<?php echo $forum_id; ?>" /><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="mainoption"></td>
	</tr>
</table></form>

<?php

}

adm_page_footer();

?>