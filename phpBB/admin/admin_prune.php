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

if ( !empty($setmodules) )
{
	if ( !$auth->get_acl_admin('forum') )
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['Forums']['Prune']   = $filename . $SID . '&amp;mode=forums';

	return;
}

define('IN_PHPBB', 1);
//
// Include files
//
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);
require($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

//
// Do we have forum admin permissions?
//
if ( !$auth->get_acl_admin('forum') )
{
	message_die(MESSAGE, $lang['No_admin']);
}

//
// Get the forum ID for pruning
//
if ( isset($HTTP_GET_VARS['f']) || isset($HTTP_POST_VARS['f']) )
{
	$forum_id = ( isset($HTTP_POST_VARS['f']) ) ? intval($HTTP_POST_VARS['f']) : intval($HTTP_GET_VARS['f']);
	$forum_sql = ( $forum_id == -1 ) ? '' : "AND forum_id = $forum_id";
}
else
{
	$forum_id = '';
	$forum_sql = '';
}
//
// Get a list of forum's or the data for the forum that we are pruning.
//
$sql = "SELECT f.*
	FROM " . FORUMS_TABLE . " f, " . CATEGORIES_TABLE . " c
	WHERE c.cat_id = f.cat_id
	$forum_sql
	ORDER BY c.cat_order ASC, f.forum_order ASC";
$result = $db->sql_query($sql);

$forum_rows = array();
while( $row = $db->sql_fetchrow($result) )
{
	$forum_rows[] = $row;
}

//
// Check for submit to be equal to Prune. If so then proceed with the pruning.
//
if ( isset($HTTP_POST_VARS['doprune']) )
{
	$prunedays = ( isset($HTTP_POST_VARS['prunedays']) ) ? intval($HTTP_POST_VARS['prunedays']) : 0;

	// Convert days to seconds for timestamp functions...
	$prunedate = time() - ( $prunedays * 86400 );

	$template->set_filenames(array(
		'body' => 'admin/forum_prune_result_body.tpl')
	);

	$log_data = '';
	for($i = 0; $i < count($forum_rows); $i++)
	{
		$p_result = prune($forum_rows[$i]['forum_id'], $prunedate);
		sync('forum', $forum_rows[$i]['forum_id']);

		$template->assign_block_vars('prune_results', array(
			'ROW_COLOR' => '#' . $row_color,
			'ROW_CLASS' => $row_class,
			'FORUM_NAME' => $forum_rows[$i]['forum_name'],
			'FORUM_TOPICS' => $p_result['topics'],
			'FORUM_POSTS' => $p_result['posts'])
		);

		$log_data .= ( ( $log_data != '' ) ? ', ' : '' ) . $forum_rows[$i]['forum_name'];
	}

	$template->assign_vars(array(
		'L_FORUM_PRUNE' => $lang['Forum_Prune'],
		'L_FORUM' => $lang['Forum'],
		'L_TOPICS_PRUNED' => $lang['Topics_pruned'],
		'L_POSTS_PRUNED' => $lang['Posts_pruned'],
		'L_PRUNE_RESULT' => $lang['Prune_success'])
	);

	add_admin_log('log_prune', $log_data);

}
else
{
	page_header($lang['Prune']);

	//
	// If they haven't selected a forum for pruning yet then
	// display a select box to use for pruning.
	//
	if ( empty($forum_id) )
	{
		//
		// Output a selection table if no forum id has been specified.
		//
		$select_list .= '<option value="-1">' . $lang['All_Forums'] . '</option>';
		for($i = 0; $i < count($forum_rows); $i++)
		{
			$select_list .= '<option value="' . $forum_rows[$i]['forum_id'] . '">' . $forum_rows[$i]['forum_name'] . '</option>';
		}

?>

<h1><?php echo $lang['Prune']; ?></h1>

<p><?php echo $lang['Forum_Prune_explain']; ?></p>

<form method="post" action="<?php echo "admin_prune.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th align="center"><?php echo $lang['Select_a_Forum']; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><?php echo $s_hidden_fields; ?>&nbsp;<select name="f"><?php echo $select_list; ?></select>&nbsp;&nbsp;<input type="submit" value="<?php echo $lang['Look_up_Forum']; ?>" class="mainoption" />&nbsp;</td>
	</tr>
</table></form>

<?php

	}
	else
	{
		$forum_name = ( $forum_id == -1 ) ? $lang['All_Forums'] : $forum_rows[0]['forum_name'];

		$prune_data = $lang['Prune_topics_not_posted'] . " ";
		$prune_data .= '<input type="text" name="prunedays" size="4"> ' . $lang['Days'];

		$s_hidden_fields = '<input type="hidden" name="f" value="' . $forum_id . '">';

?>

<h1><?php echo $lang['Prune']; ?></h1>

<p><?php echo $lang['Forum_Prune_explain']; ?></p>

<h2><?php echo $lang['Forum'] . ': ' . $forum_name; ?></h2>

<form method="post"	action="<?php echo "admin_prune.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th class="th"><?php echo $lang['Forum_Prune']; ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo $prune_data; ?></td>
	</tr>
	<tr>
		<td class="cat" align="center"><?php echo $s_hidden_fields; ?><input type="submit" name="doprune" value="<?php echo $lang['Do_Prune']; ?>" class="mainoption"></td>
	</tr>
</table></form>

<?php

	}
}

page_footer();

?>