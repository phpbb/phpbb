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
// Now include the relevant files.
//
$phpbb_root_path = "./../";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/prune.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
init_userprefs($userdata);
// 
// End sessionmanagement
//

//
// Check user permissions
//
if( !$userdata['session_logged_in'] )
{
	header("Location: ../login.$phpEx?forward_page=/admin/");
}
else if( $userdata['user_level'] != ADMIN )
{
	message_die(GENERAL_MESSAGE, "You are not authorised to administer this board");
}

// 
// Get the forum ID for pruning 
//
if(isset($HTTP_GET_VARS[POST_FORUM_URL]) || isset($HTTP_POST_VARS[POST_FORUM_URL]))
{
	$forum_id = (isset($HTTP_POST_VARS[POST_FORUM_URL])) ? $HTTP_POST_VARS[POST_FORUM_URL] : $HTTP_GET_VARS[POST_FORUM_URL];
	if($forum_id == "ALL") 
	{
		$forum_sql = "";
	}
	else
	{
		$forum_sql = "AND forum_id = $forum_id";
	}
}
else
{
	unset($forum_id);
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
// Check for the submit variable.
//
if(isset($HTTP_GET_VARS['submit']) || isset($HTTP_POST_VARS['submit']))
{
	$submit = (isset($HTTP_POST_VARS['submit'])) ? $HTTP_POST_VARS['submit'] : $HTTP_GET_VARS['submit'];
}
else 
{
	unset($submit);
}

//
// Check for submit to be equal to Prune. If so then proceed with the pruning.
//
if($submit == "Prune")
{
	$prunedays = $HTTP_POST_VARS['prunedays'];

	// Convert days to seconds for timestamp functions...
	$prunesecs = $prunedays * 1440 * 60;
	$prunedate = time() - $prunesecs;

	include('page_header_admin.'.$phpEx);

	$template->set_filenames(array(
		"body" => "admin/forum_prune_result_body.tpl")
	);

	reset($forum_rows);
	while(list(, $forum_data) = each ($forum_rows))
	{
		$p_result = prune($forum_data['forum_id'], $prunedate);

		$template->assign_block_vars("prune_results", array(
			"FORUM_NAME" => $forum_data['forum_name'],
			"FORUM_TOPICS" => $p_result['topics'],
			"FORUM_POSTS" => $p_result['posts'])
		);
	}

	$template->assign_vars(array(
		"PRUNE_MSG" => "Pruning of forums was successful")
	);
}
else
{
	//
	// If they haven't selected a forum for pruning yet then 
	// display a select box to use for pruning.
	//
	if(empty($forum_id))
	{
		//
		// Output a selection table if no forum id has been specified.
		//
		include('page_header_admin.'.$phpEx);

		$template->set_filenames(array(
			"body" => "admin/forum_prune_select_body.tpl")
		);

		$select_list = "<select name=\"" . POST_FORUM_URL . "\">\n";
		$select_list .= "<option value=\"ALL\">All Forums</option>\n";

		for($i = 0; $i < count($forum_rows); $i++)
		{
			$select_list .= "<option value=\"" . $forum_rows[$i]['forum_id'] . "\">" . $forum_rows[$i]['forum_name'] . "</option>\n";
		}
		$select_list .= "</select>\n";

		//
		// Assign the template variables.
		//
		$template->assign_vars(array(
			"S_FORUMPRUNE_ACTION" => append_sid("admin_forum_prune.$phpEx"), 
			"S_FORUMS_SELECT" => $select_list)
		);
	}
	else 
	{
		//
		// Output the form to retrieve Prune information.
		//
		include('page_header_admin.'.$phpEx);

		$template->set_filenames(array(
			"body" => "admin/forum_prune_body.tpl")
		);
		
		$forum_name = ($forum_id == "ALL") ? 'All Forums' : $forum_rows[0]['forum_name'];

		$prune_data = "Prune Topics that haven't been posted to in the last ";
		$prune_data .= "<input type=\"text\" name=\"prunedays\" size=\"4\"> Days.";

		$hidden_input = "<input type=\"hidden\" name=\"" . POST_FORUM_URL . "\" value=\"$forum_id\">";

		//
		// Assign the template variables.
		//
		$template->assign_vars(array(
			"S_FORUMPRUNE_ACTION" => append_sid("admin_forum_prune.$phpEx"),
			"FORUM_NAME" => $forum_name,
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