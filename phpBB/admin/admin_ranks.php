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
		$rank_id = ( isset($HTTP_GET_VARS['id']) ) ? intval($HTTP_GET_VARS['id']) : 0;
		
		$s_hidden_fields = "";
		
		if( $mode == "edit" )
		{
			if( empty($rank_id) )
			{
				message_die(GENERAL_MESSAGE, $lang['Must_select_rank']);
			}

			$sql = "SELECT * FROM " . RANKS_TABLE . "
				WHERE rank_id = $rank_id";
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Couldn't obtain rank data", "", __LINE__, __FILE__, $sql);
			}
			
			$rank_info = $db->sql_fetchrow($result);
			$s_hidden_fields .= '<input type="hidden" name="id" value="' . $rank_id . '" />';

		}
		else
		{
			$rank_info['rank_special'] = 0;
		}

		$s_hidden_fields .= '<input type="hidden" name="mode" value="save" />';

		$rank_is_special = ( $rank_info['rank_special'] ) ? "checked=\"checked\"" : "";
		$rank_is_not_special = ( !$rank_info['rank_special'] ) ? "checked=\"checked\"" : "";
		
		$template->set_filenames(array(
			"body" => "admin/ranks_edit_body.tpl")
		);

		$template->assign_vars(array(
			"RANK" => $rank_info['rank_title'],
			"SPECIAL_RANK" => $rank_is_special,
			"NOT_SPECIAL_RANK" => $rank_is_not_special,
			"MINIMUM" => $rank_info['rank_min'],
			"MAXIMUM" => $rank_info['rank_max'],
			"IMAGE" => ( $rank_info['rank_image'] != "" ) ? $rank_info['rank_image'] : "",
			"IMAGE_DISPLAY" => ( $rank_info['rank_image'] != "" ) ? '<img src="' . $rank_info['rank_image'] . '" />' : "",
			
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
		
		$rank_id = ( isset($HTTP_POST_VARS['id']) ) ? intval($HTTP_POST_VARS['id']) : 0;
		$rank_title = ( isset($HTTP_POST_VARS['title']) ) ? $HTTP_POST_VARS['title'] : "";
		$special_rank = ( $HTTP_POST_VARS['special_rank'] == 1 ) ? TRUE : 0;
		$max_posts = ( isset($HTTP_POST_VARS['max_posts']) ) ? intval($HTTP_POST_VARS['max_posts']) : -1;
		$min_posts = ( isset($HTTP_POST_VARS['min_posts']) ) ? intval($HTTP_POST_VARS['min_posts']) : -1;
		$rank_image = ( (isset($HTTP_POST_VARS['rank_image'])) ) ? $HTTP_POST_VARS['rank_image'] : "";

		if( $rank_title == "" )
		{
			message_die(GENERAML_MESSAGE, $lang['Must_select_rank']);
		}

		if( $special_rank == 1 )
		{
			$max_posts = -1;
			$min_posts = -1;
		}

		//
		// The rank image has to be a jpg, gif or png
		//
		if($rank_image != "")
		{
			if ( !preg_match("/(\.gif|\.png|\.jpg)$/is", $rank_image))
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

			$message = $lang['Rank_updated'];
		}
		else
		{
			$sql = "INSERT INTO " . RANKS_TABLE . "
					(rank_title, rank_special, rank_max, rank_min, rank_image)
				VALUES
					('$rank_title', '$special_rank', '$max_posts', '$min_posts', '$rank_image')";

			$message = $lang['Rank_added'];
		}
		
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't update/insert into ranks table", "", __LINE__, __FILE__, $sql);
		}

		$message .= "<br /><br />" . sprintf($lang['Click_return_rankadmin'], "<a href=\"" . append_sid("admin_ranks.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

		message_die(GENERAL_MESSAGE, $message);

	}
	else if( $mode == "delete" )
	{
		//
		// Ok, they want to delete their rank
		//
		
		if( isset($HTTP_POST_VARS['id']) || isset($HTTP_GET_VARS['id']) )
		{
			$rank_id = ( isset($HTTP_POST_VARS['id']) ) ? intval($HTTP_POST_VARS['id']) : intval($HTTP_GET_VARS['id']);
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
				message_die(GENERAL_ERROR, "Couldn't delete rank data", "", __LINE__, __FILE__, $sql);
			}

			$message .= "<br /><br />" . sprintf($lang['Click_return_rankadmin'], "<a href=\"" . append_sid("admin_ranks.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);

		}
		else
		{
			message_die(GENERAL_MESSAGE, $lang['Must_select_rank']);
		}
	}
	else
	{
		//
		// They didn't feel like giving us any information. Oh, too bad, we'll just display the
		// list then...
		//
		$template->set_filenames(array(
			"body" => "admin/ranks_list_body.tpl")
		);
		
		$sql = "SELECT * FROM " . RANKS_TABLE . "
			ORDER BY rank_min ASC, rank_special ASC";
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain ranks data", "", __LINE__, __FILE__, $sql);
		}
		
		$rank_rows = $db->sql_fetchrowset($result);
		$rank_count = count($rank_rows);
		
		$template->assign_vars(array(
			"L_RANKS_TITLE" => $lang['Ranks_title'],
			"L_RANKS_TEXT" => $lang['Ranks_explain'],
			"L_RANK" => $lang['Rank'],
			"L_RANK_MINIMUM" => $lang['Rank_minimum'],
			"L_RANK_MAXIMUM" => $lang['Rank_maximum'],
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
			$rank_min = $rank_rows[$i]['rank_min'];
			$rank_max = $rank_rows[$i]['rank_max'];

			if($special_rank)
			{
				$rank_min = $rank_max = "-";
			}
			
			$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];
	
			$template->assign_block_vars("ranks", array(
				"ROW_COLOR" => "#" . $row_color,
				"ROW_CLASS" => $row_class,
				"RANK" => $rank,
				"RANK_MIN" => $rank_min,
				"RANK_MAX" => $rank_max,

				"SPECIAL_RANK" => ( $special_rank == 1 ) ? $lang['Yes'] : $lang['No'],

				"U_RANK_EDIT" => append_sid("admin_ranks.$phpEx?mode=edit&amp;id=$rank_id"),
				"U_RANK_DELETE" => append_sid("admin_ranks.$phpEx?mode=delete&amp;id=$rank_id"))
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
		ORDER BY rank_min ASC, rank_special ASC";
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain ranks data", "", __LINE__, __FILE__, $sql);
	}
	$rank_count = $db->sql_numrows($result);

	$rank_rows = $db->sql_fetchrowset($result);
	
	$template->assign_vars(array(
		"L_RANKS_TITLE" => $lang['Ranks_title'],
		"L_RANKS_TEXT" => $lang['Ranks_explain'],
		"L_RANK" => $lang['Rank_title'],
		"L_RANK_MINIMUM" => $lang['Rank_minimum'],
		"L_RANK_MAXIMUM" => $lang['Rank_maximum'],
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
		$rank_min = $rank_rows[$i]['rank_min'];
		$rank_max = $rank_rows[$i]['rank_max'];
		
		if($special_rank == "1")
		{
			$rank_min = $rank_max = "-";
		}

		$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
		$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

		$rank_is_special = ( $special_rank ) ? $lang['Yes'] : $lang['No'];
		
		$template->assign_block_vars("ranks", array(
			"ROW_COLOR" => "#" . $row_color,
			"ROW_CLASS" => $row_class,
			"RANK" => $rank,
			"SPECIAL_RANK" => $rank_is_special,
			"RANK_MIN" => $rank_min,
			"RANK_MAX" => $rank_max,

			"U_RANK_EDIT" => append_sid("admin_ranks.$phpEx?mode=edit&amp;id=$rank_id"),
			"U_RANK_DELETE" => append_sid("admin_ranks.$phpEx?mode=delete&amp;id=$rank_id"))
		);
	}
}

$template->pparse("body");

include('page_footer_admin.'.$phpEx);

?>