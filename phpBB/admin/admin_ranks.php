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

if($setmodules == 1)
{
	$file = basename(__FILE__);
	$module['Users']['Ranks'] = "$file";
	return;
}

//
// Let's set the root dir for phpBB
//
$phpbb_root_dir = "./../";

//
// Include required files, get $phpEx and check permissions
//
require('pagestart.inc');

if( isset($HTTP_GET_VARS['mode']) || isset($HTTP_POST_VARS['mode']) )
{
	$mode = ($HTTP_GET_VARS['mode']) ? $HTTP_GET_VARS['mode'] : $HTTP_POST_VARS['mode'];
}
else 
{
	//
	// These could be entered via a form button
	//
	if( isset($HTTP_POST_VARS['add']) )
	{
		$mode = "add";
	}
	else if( isset($HTTP_POST_VARS['save']) )
	{
		$mode = "save";
	}
	else
	{
		$mode = "";
	}
}


if( $mode != "" )
{
	if( $mode == "edit" || $mode == "add" )
	{
		//
		// They want to add a new rank, show the form.
		//
		
		$rank_id = ( isset($HTTP_GET_VARS['id']) ) ? $HTTP_GET_VARS['id'] : 0;
		
		$template->set_filenames(array(
			"body" => "admin/ranks_edit_body.tpl")
		);
		
		$s_hidden_fields = '';
		
		if( $mode == "edit" )
		{
			if(	$rank_id )
			{
				$sql = "SELECT * FROM " . RANKS_TABLE . "
					WHERE rank_id = $rank_id";
				if(!$result = $db->sql_query($sql))
				{
				
					$template->set_filenames(array(
						"body" => "admin/admin_message_body.tpl")
					);
						
					$template->assign_vars(array(
						"MESSAGE_TITLE" => $lang['Error'],
						"MESSAGE_TEXT" => "Error querying ranks table")
					);
				}
				
				$rank_info = $db->sql_fetchrow($result);
				$s_hidden_fields .= '<input type="hidden" name="id" value="' . $rank_id . '" />';
			}
			else
			{
				$template->set_filenames(array(
					"body" => "admin/admin_message_body.tpl")
				);
				
				$template->assign_vars(array(
					"MESSAGE_TITLE" => $lang['Error'],
					"MESSAGE_TEXT" => $lang['Must_select_rank'])
				);
					
			}
		}
		else
		{
			$rank_info['rank_special'] = 0;
		}
		$s_hidden_fields .= '<input type="hidden" name="mode" value="save" />';
		$rank_is_special = ( $rank_info['rank_special'] == 1 ) ? "checked=\"checked\"" : "";
		$rank_is_not_special = ( !($rank_info['rank_special'] == 1) ) ? "checked=\"checked\"" : "";
		
		$template->assign_vars(array(
			"RANK" => $rank_info['rank_title'],
			"SPECIAL_RANK" => $rank_is_special,
			"NOT_SPECIAL_RANK" => $rank_is_not_special,
			"MINIMUM" => $rank_info['rank_min'],
			"MAXIMUM" => $rank_info['rank_max'],
			"IMAGE" => ( $rank_info['rank_image'] != "" ) ? $rank_info['rank_image'] : "http://",
			"IMAGE_DISPLAY" => ( $rank_info['rank_image'] != "" ) ? '<img src="'.$rank_info['rank_image'].'" />' : "",
			
			"L_RANKS_TITLE" => $lang['Ranks_title'],
			"L_RANKS_TEXT" => $lang['Ranks_explain'],
			"L_RANK_TITLE" => $lang['Rank_title'],
			"L_RANK_SPECIAL" => $lang['Rank_special'],
			"L_RANK_MINIMUM" => $lang['Rank_minimum'],
			"L_RANK_MAXIMUM" => $lang['Rank_maximum'],
			"L_RANK_IMAGE" => $lang['Rank_image'],
			"L_RANK_IMAGE_EXPLAIN" => $lang['Rank_image_explain'],
			"L_SUBMIT" => $lang['Submit'],
			"L_RESET" => $lang['Reset'],
			"L_YES" => $lang['Yes'],
			"L_NO" => $lang['No'],
			
			"S_RANK_ACTION" => append_sid("admin_ranks.$phpEx"),
			"S_HIDDEN_FIELDS" => $s_hidden_fields)
		);
		
	}
	else if( $mode == "save" )
	{
		//
		// Ok, they sent us our info, let's update it.
		//
		
		$rank_id = ( isset($HTTP_POST_VARS['id']) ) ? $HTTP_POST_VARS['id'] : 0;
		$rank_title = ( isset($HTTP_POST_VARS['title']) ) ? $HTTP_POST_VARS['title'] : "";
		$special_rank = ( $HTTP_POST_VARS['special_rank'] == 1 ) ? 1 : 0;
		$max_posts = ( isset($HTTP_POST_VARS['max_posts']) ) ? $HTTP_POST_VARS['max_posts'] : -1;
		$min_posts = ( isset($HTTP_POST_VARS['min_posts']) ) ? $HTTP_POST_VARS['min_posts'] : -1;
		$rank_image = ( (isset($HTTP_POST_VARS['rank_image'])) || $HTTP_POST_VARS['rank_image'] != "http://" ) ? $HTTP_POST_VARS['rank_image'] : "";
		if( $rank_title == "" )
		{
				$template->set_filenames(array(
					"body" => "admin/admin_message_body.tpl")
				);
				
				$template->assign_vars(array(
					"MESSAGE_TITLE" => $lang['Error'],
					"MESSAGE_TEXT" => $lang['Must_select_rank'])
				);
		}
		if( $special_rank == 1 )
		{
			$max_posts = -1;
			$min_posts = -1;
		}
		// The rank image has to start with http://, followed by something with length at least 3 that
		// contains at least one dot.
		if($rank_image != "")
		{
			if( !ereg("^http\:\/\/", $rank_image) )
			{
				$rank_image = "http://" . $rank_image;
			}
	
			if (!preg_match("#^http\\:\\/\\/[a-z0-9\-]+\.[a-z0-9\-]+#i", $rank_image))
			{
				$rank_image = "";
			}
		}
		if( $rank_id )
		{
			$sql = "UPDATE " . RANKS_TABLE . "
				SET 
					rank_title = '$rank_title', 
					rank_special = '$special_rank',
					rank_max = '$max_posts',
					rank_min = '$min_posts',
					rank_image = '$rank_image'
				WHERE rank_id = $rank_id";
			$message_success = $lang['Rank_updated'];
		}
		else
		{
			$sql = "INSERT INTO " . RANKS_TABLE . "
					(rank_title, rank_special, rank_max, rank_min, rank_image)
				VALUES
					('$rank_title', '$special_rank', '$max_posts', '$min_posts', '$rank_image')";
			$message_success = $lang['Rank_added'];
		}
		
		if(!$result = $db->sql_query($sql))
		{
				$template->set_filenames(array(
					"body" => "admin/admin_message_body.tpl")
				);
				
				$template->assign_vars(array(
					"MESSAGE_TITLE" => $lang['Error'],
					"MESSAGE_TEXT" => "Couldn't update ranks table<br>SQL: ".$sql)
				);
		}
		else
		{
				$template->set_filenames(array(
					"body" => "admin/admin_message_body.tpl")
				);
				
				$template->assign_vars(array(
					"MESSAGE_TITLE" => $lang['Success'],
					"MESSAGE_TEXT" => $message_success)
				);
		}
	}
	else if( $mode == "delete" )
	{
		//
		// Ok, they want to delete their rank
		//
		
		if( isset($HTTP_POST_VARS['id']) || isset($HTTP_GET_VARS['id']) )
		{
			$rank_id = ( isset($HTTP_POST_VARS['id']) ) ? $HTTP_POST_VARS['id'] : $HTTP_GET_VARS['id'];
		}
		else
		{
			$rank_id = 0;
		}
		
		if( $rank_id )
		{
			$sql = "DELETE FROM " . RANKS_TABLE . "
				WHERE rank_id = $rank_id";
			
			if( !$result = $db->sql_query($sql))
			{
				$template->set_filenames(array(
					"body" => "admin/admin_message_body.tpl")
				);
				
				$template->assign_vars(array(
					"MESSAGE_TITLE" => $lang['Error'],
					"MESSAGE_TEXT" => "Could not remove data from ranks table.")
				);
			}
			else
			{
				$template->set_filenames(array(
					"body" => "admin/admin_message_body.tpl")
				);
				
				$template->assign_vars(array(
					"MESSAGE_TITLE" => $lang['Success'],
					"MESSAGE_TEXT" => $lang['Rank_removed'])
				);
			}
		}
		else
		{
			$template->set_filenames(array(
				"body" => "admin/admin_message_body.tpl")
			);
			
			$template->assign_vars(array(
				"MESSAGE_TITLE" => $lang['Error'],
				"MESSAGE_TEXT" => $lang['Must_select_rank'])
			);
		}
	}
	else
	{
		//
		// They didn't feel like giving us any information. Oh, too bad, we'll just display the
		// list then...
		
		$template->set_filenames(array(
			"body" => "admin/ranks_list_body.tpl")
		);
		
		$sql = "SELECT * FROM " . RANKS_TABLE . "
			ORDER BY rank_title";
		if( !$result = $db->sql_query($sql) )
		{
			$template->set_filenames(array(
				"body" => "admin/admin_message_body.tpl")
			);
			
			$template->assign_vars(array(
				"MESSAGE_TITLE" => $lang['Error'],
				"MESSAGE_TEXT" => "Could not query ranks table")
			);
		}
		
		$rank_rows = $db->sql_fetchrowset($result);
		$rank_count = count($rank_rows);
		
		$template->assign_vars(array(
			"L_RANKS_TITLE" => $lang['Ranks_title'],
			"L_RANKS_TEXT" => $lang['Ranks_explain'],
			"L_RANK" => $lang['Rank'],
			"L_SPECIAL_RANK" => $lang['Special_rank'],
			"L_EDIT" => $lang['Edit'],
			"L_DELETE" => $lang['Delete'],
			"L_ADD_RANK" => $lang['Add_new_rank'],
			"L_ACTION" => $lang['Action'],
			
			"S_RANKS_ACTION" => append_sid("admin_ranks.$phpEx"))
		);
		
		for( $i = 0; $i < $rank_count; $i++)
		{
			$rank = $rank_rows[$i]['rank_title'];
			$special_rank = $rank_rows[$i]['rank_special'];
			$rank_id = $rank_rows[$i]['rank_id'];
			
			$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];
	
			$template->assign_block_vars("ranks", array(
				"ROW_COLOR" => "#" . $row_color,
				"ROW_CLASS" => $row_class,
				"RANK" => $rank,
				"SPECIAL_RANK" => ( $special_rank == 1 ) ? "Yes" : "No",
				"U_RANK_EDIT" => append_sid("admin_ranks.$phpEx?mode=edit&id=$rank_id"),
				"U_RANK_DELETE" => append_sid("admin_ranks.$phpEx?mode=delete&id=$rank_id"))
			);
		}
	}
}
else
{
	//
	// Show the default page
	//
	
	$template->set_filenames(array(
		"body" => "admin/ranks_list_body.tpl")
	);
	
	$sql = "SELECT * FROM " . RANKS_TABLE . "
		ORDER BY rank_title";
	if( !$result = $db->sql_query($sql) )
	{
		$template->set_filenames(array(
			"body" => "admin/admin_message_body.tpl")
		);
		
		$template->assign_vars(array(
			"MESSAGE_TITLE" => $lang['Error'],
			"MESSAGE_TEXT" => "Could not query ranks table.")
		);
	}

	$rank_rows = $db->sql_fetchrowset($result);
	$rank_count = count($rank_rows);
	
	$template->assign_vars(array(
		"L_RANKS_TITLE" => $lang['Ranks_title'],
		"L_RANKS_TEXT" => $lang['Ranks_explain'],
		"L_RANK" => $lang['Rank_title'],
		"L_SPECIAL_RANK" => $lang['Rank_special'],
		"L_EDIT" => $lang['Edit'],
		"L_DELETE" => $lang['Delete'],
		"L_ADD_RANK" => $lang['Add_new_rank'],
		"L_ACTION" => $lang['Action'],
		
		"S_RANKS_ACTION" => append_sid("admin_ranks.$phpEx"))
	);
	
	for($i = 0; $i < $rank_count; $i++)
	{
		$rank = $rank_rows[$i]['rank_title'];
		$special_rank = $rank_rows[$i]['rank_special'];
		$rank_id = $rank_rows[$i]['rank_id'];
		
		$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
		$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];
		$rank_is_special = ( $special_rank == 1 ) ? "Yes" : "No";
		
		$template->assign_block_vars("ranks", array(
			"ROW_COLOR" => "#" . $row_color,
			"ROW_CLASS" => $row_class,
			"RANK" => $rank,
			"SPECIAL_RANK" => $rank_is_special,
			"U_RANK_EDIT" => append_sid("admin_ranks.$phpEx?mode=edit&id=$rank_id"),
			"U_RANK_DELETE" => append_sid("admin_ranks.$phpEx?mode=delete&id=$rank_id"))
		);
	}
}

$template->pparse("body");

include('page_footer_admin.'.$phpEx);

?>
