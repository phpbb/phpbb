<?php
/***************************************************************************
 *                              admin_words.php
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
	$module['General']['Word_Censor'] = "$file";
	return;
}

//
// Load default header
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
		$word_id = ( isset($HTTP_GET_VARS['id']) ) ? $HTTP_GET_VARS['id'] : 0;

		$template->set_filenames(array(
			"body" => "admin/words_edit_body.tpl")
		);

		$s_hidden_fields = '';

		if( $mode == "edit" )
		{
			if( $word_id )
			{
				$sql = "SELECT * 
					FROM " . WORDS_TABLE . " 
					WHERE word_id = $word_id";
				if(!$result = $db->sql_query($sql))
				{
					message_die(GENERAL_ERROR, "Could not query words table", "Error", __LINE__, __FILE__, $sql);
				}

				$word_info = $db->sql_fetchrow($result);
				$s_hidden_fields .= '<input type="hidden" name="id" value="' . $word_id . '" />';
			}
			else
			{
				message_die(GENERAL_MESSAGE, $lang['No_word_selected']);
			}
		}

		$template->assign_vars(array(
			"WORD" => $word_info['word'],
			"REPLACEMENT" => $word_info['replacement'],

			"L_WORDS_TITLE" => $lang['Words_title'],
			"L_WORDS_TEXT" => $lang['Words_explain'],
			"L_WORD_CENSOR" => $lang['Edit_word_censor'],
			"L_WORD" => $lang['Word'],
			"L_REPLACEMENT" => $lang['Replacement'],
			"L_SUBMIT" => $lang['Submit'],

			"S_WORDS_ACTION" => append_sid("admin_words.$phpEx"),
			"S_HIDDEN_FIELDS" => $s_hidden_fields)
		);

		$template->pparse("body");

		include('page_footer_admin.'.$phpEx);
	}
	else if( $mode == "save" )
	{
		$word_id = ( isset($HTTP_POST_VARS['id']) ) ? $HTTP_POST_VARS['id'] : 0;
		$word = ( isset($HTTP_POST_VARS['word']) ) ? trim($HTTP_POST_VARS['word']) : "";
		$replacement = ( isset($HTTP_POST_VARS['replacement']) ) ? trim($HTTP_POST_VARS['replacement']) : "";

		if($word == "" || $replacement == "")
		{
			message_die(GENERAL_MESSAGE, $lang['Must_enter_word']);
		}

		if( $word_id )
		{
			$sql = "UPDATE " . WORDS_TABLE . " 
				SET word = '$word', replacement = '$replacement' 
				WHERE word_id = $word_id";
			$message = $lang['Word_updated'];
		}
		else
		{
			$sql = "INSERT INTO " . WORDS_TABLE . " (word, replacement) 
				VALUES ('$word', '$replacement')";
			$message = $lang['Word_added'];
		}

		if(!$result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Could not insert data into words table", $lang['Error'], __LINE__, __FILE__, $sql);
		}

		$message .= "<br /><br />" . sprintf($lang['Click_return_wordadmin'], "<a href=\"" . append_sid("admin_words.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

		message_die(GENERAL_MESSAGE, $message);
	}
	else if( $mode == "delete" )
	{
		if( isset($HTTP_POST_VARS['id']) ||  isset($HTTP_GET_VARS['id']) )
		{
			$word_id = ( isset($HTTP_POST_VARS['id']) ) ? $HTTP_POST_VARS['id'] : $HTTP_GET_VARS['id'];
		}
		else
		{
			$word_id = 0;
		}

		if( $word_id )
		{
			$sql = "DELETE FROM " . WORDS_TABLE . " 
				WHERE word_id = $word_id";

			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not remove data from words table", $lang['Error'], __LINE__, __FILE__, $sql);
			}

			$message = $lang['Word_removed'] . "<br /><br />" . sprintf($lang['Click_return_wordadmin'], "<a href=\"" . append_sid("admin_words.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);
		}
		else
		{
			message_die(GENERAL_MESSAGE, $lang['Must_specify_word']);
		}
	}
}
else
{
	$template->set_filenames(array(
		"body" => "admin/words_list_body.tpl")
	);

	$sql = "SELECT * 
		FROM " . WORDS_TABLE . " 
		ORDER BY word";
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Could not query words table", $lang['Error'], __LINE__, __FILE__, $sql);
	}

	$word_rows = $db->sql_fetchrowset($result);
	$word_count = count($word_rows);

	$template->assign_vars(array(
		"L_WORDS_TITLE" => $lang['Words_title'],
		"L_WORDS_TEXT" => $lang['Words_explain'],
		"L_WORD" => $lang['Word'],
		"L_REPLACEMENT" => $lang['Replacement'],
		"L_EDIT" => $lang['Edit'],
		"L_DELETE" => $lang['Delete'],
		"L_ADD_WORD" => $lang['Add_new_word'],
		"L_ACTION" => $lang['Action'],

		"S_WORDS_ACTION" => append_sid("admin_words.$phpEx"),
		"S_HIDDEN_FIELDS" => '')
	);

	for($i = 0; $i < $word_count; $i++)
	{
		$word = $word_rows[$i]['word'];
		$replacement = $word_rows[$i]['replacement'];
		$word_id = $word_rows[$i]['word_id'];

		$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
		$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

		$template->assign_block_vars("words", array(
			"ROW_COLOR" => "#" . $row_color,
			"ROW_CLASS" => $row_class,
			"WORD" => $word,
			"REPLACEMENT" => $replacement,

			"U_WORD_EDIT" => append_sid("admin_words.$phpEx?mode=edit&amp;id=$word_id"),
			"U_WORD_DELETE" => append_sid("admin_words.$phpEx?mode=delete&amp;id=$word_id"))
		);
	}
}

$template->pparse("body");

include('page_footer_admin.'.$phpEx);

?>