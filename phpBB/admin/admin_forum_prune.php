<?php
/***************************************************************************
*                             admin_forum_prune.php
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

/***************************************************************************
*	This file is for the setup of the auto_pruning and also will allow for
*	immediate forum pruning as well.
***************************************************************************/
//
// 	Warning: Parts of this code were shamelessly stolen verbatim from Paul's
//	work on the Auth admin stuff :) JLH
//

//
//First we through in the modules stuff :)
//

if( $setmodules == 1 )
{
	$filename = basename(__FILE__);
	$module['Forums']['Prune'] = $filename;

	return;
}

//
// Load default header
//
$phpbb_root_dir = "./../";
require('pagestart.inc');
include($phpbb_root_path . 'includes/prune.php');

//
// Get the forum ID for pruning
//
if( isset($HTTP_GET_VARS[POST_FORUM_URL]) || isset($HTTP_POST_VARS[POST_FORUM_URL]) )
{
	$forum_id = ( isset($HTTP_POST_VARS[POST_FORUM_URL]) ) ? $HTTP_POST_VARS[POST_FORUM_URL] : $HTTP_GET_VARS[POST_FORUM_URL];

	if( $forum_id == "ALL" )
	{
		$forum_sql = "";
	}
	else
	{
		$forum_id = intval($forum_id);
		$forum_sql = "AND forum_id = $forum_id";
	}
}
else
{
	$forum_id = "";
	$forum_sql = "";
}
//
// Get a list of forum's or the data for the forum that we are pruning.
//
$sql = "SELECT f.*
	FROM " . FORUMS_TABLE . " f, " . CATEGORIES_TABLE . " c
	WHERE c.cat_id = f.cat_id
	$forum_sql
	ORDER BY c.cat_order ASC, f.forum_order ASC";
$f_result = $db->sql_query($sql);

$forum_rows = $db->sql_fetchrowset($f_result);

//
// Check for submit to be equal to Prune. If so then proceed with the pruning.
//
if( isset($HTTP_POST_VARS['doprune']) )
{
	$prunedays = ( isset($HTTP_POST_VARS['prunedays']) ) ? $HTTP_POST_VARS['prunedays'] : 0;

	// Convert days to seconds for timestamp functions...
	$prunesecs = $prunedays * 1440 * 60;
	$prunedate = time() - $prunesecs;

	$template->set_filenames(array(
		"body" => "admin/forum_prune_result_body.tpl")
	);

	$i = 0;
	reset($forum_rows);
	while(list(, $forum_data) = each ($forum_rows))
	{
		$p_result = prune($forum_data['forum_id'], $prunedate);
		sync("forum", $forum_data['forum_id']);

		$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
		$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

		$i++;

		$template->assign_block_vars("prune_results", array(
			"ROW_COLOR" => "#" . $row_color, 
			"ROW_CLASS" => $row_class, 
			"FORUM_NAME" => $forum_data['forum_name'],
			"FORUM_TOPICS" => $p_result['topics'],
			"FORUM_POSTS" => $p_result['posts'])
		);
	}

	$template->assign_vars(array(
		"L_FORUM" => $lang['Forum'],
		"L_TOPICS_PRUNED" => $lang['Topics_pruned'],
		"L_POSTS_PRUNED" => $lang['Posts_pruned'],
		"L_PRUNE_RESULT" => $lang['Prune_success'])
	);
}
else
{
	//
	// If they haven't selected a forum for pruning yet then
	// display a select box to use for pruning.
	//
	if( empty($HTTP_POST_VARS[POST_FORUM_URL]) )
	{
		//
		// Output a selection table if no forum id has been specified.
		//
		$template->set_filenames(array(
			"body" => "admin/forum_prune_select_body.tpl")
		);

		$select_list = "<select name=\"" . POST_FORUM_URL . "\">\n";
		$select_list .= "<option value=\"ALL\">" . $lang['All_Forums'] . "</option>\n";

		for($i = 0; $i < count($forum_rows); $i++)
		{
			$select_list .= "<option value=\"" . $forum_rows[$i]['forum_id'] . "\">" . $forum_rows[$i]['forum_name'] . "</option>\n";
		}
		$select_list .= "</select>\n";

		//
		// Assign the template variables.
		//
		$template->assign_vars(array(
			"L_FORUM_PRUNE" => $lang['Forum_Prune'],
			"L_SELECT_FORUM" => $lang['Select_a'] . " " . $lang['Forum'], 
			"L_LOOK_UP" => $lang['Look_up'] . " " . $lang['Forum'],

			"S_FORUMPRUNE_ACTION" => append_sid("admin_forum_prune.$phpEx"),
			"S_FORUMS_SELECT" => $select_list)
		);
	}
	else
	{
		$forum_id = intval($HTTP_POST_VARS[POST_FORUM_URL]);

		//
		// Output the form to retrieve Prune information.
		//
		$template->set_filenames(array(
			"body" => "admin/forum_prune_body.tpl")
		);

		$forum_name = ( $forum_id == "ALL" ) ? $lang['All_Forums'] : $forum_rows[0]['forum_name'];

		$prune_data = $lang['Prune_topics_not_posted'] . " "; 
		$prune_data .= "<input type=\"text\" name=\"prunedays\" size=\"4\"> " . $lang['Days'];

		$hidden_input = "<input type=\"hidden\" name=\"" . POST_FORUM_URL . "\" value=\"$forum_id\">";

		//
		// Assign the template variables.
		//
		$template->assign_vars(array(
			"FORUM_NAME" => $forum_name,

			"L_FORUM_PRUNE" => $lang['Forum_Prune'], 
			"L_FORUM_PRUNE_EXPLAIN" => $lang['Forum_Prune_explain'], 
			"L_DO_PRUNE" => $lang['Do_Prune'],

			"S_FORUMPRUNE_ACTION" => append_sid("admin_forum_prune.$phpEx"),
			"S_PRUNE_DATA" => $prune_data,
			"S_HIDDEN_VARS" => $hidden_input)
		);
	}
}
//
// Actually output the page here.
//
$template->pparse("body");

include('page_footer_admin.'.$phpEx);

?>