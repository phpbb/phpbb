<?php
/***************************************************************************
 *
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
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

//
// This function will prepare a posted message for
// entry into the database.
//
function prepare_message($message, $html_on, $bbcode_on, $smile_on, $bbcode_uid = 0)
{
	global $board_config;

	$allowed_html_tags = split(",", $board_config['allow_html_tags']);

	//
	// Clean up the message
	//
	$message = trim($message);

	if( $html_on )
	{
		$html_entities_match = array("#&#", "#<#", "#>#");
		$html_entities_replace = array("&amp;", "&lt;", "&gt;");

		$message = preg_replace("#&([a-z0-9]+?);#i", "&amp;\\1;", $message);

		$start_html = 1;

		$message = " " . $message;
		while( $start_html = strpos($message, "<", $start_html) )
		{
			if( $end_html = strpos($message, ">", $start_html) )
			{
				$length = $end_html - $start_html + 1;

				$tagallowed = 0;
				for($i = 0; $i < sizeof($allowed_html_tags); $i++)
				{
					$match_tag = trim($allowed_html_tags[$i]);

					if( preg_match("/^[\/]?" . $match_tag . "( .*?)*$/i", trim(substr($message, $start_html + 1, $length - 2))) )
					{
						if( !preg_match("/(^\?)|(\?$)/", trim(substr($message, $start_html + 1, $length - 2))) )
						{
							$tagallowed = 1;
						}
					}
				}

				if( $length && !$tagallowed )
				{
					$message = str_replace(substr($message, $start_html, $length), preg_replace($html_entities_match, $html_entities_replace, substr($message, $start_html, $length)), $message);
				}

				$start_html += $length;
			}
			else
			{
				$message = str_replace(substr($message, $start_html, 1), preg_replace($html_entities_match, $html_entities_replace, substr($message, $start_html, 1)), $message);

				$start_html = strlen($message);
			}
		}
		$message = trim($message);
	}
	else
	{
		$html_entities_match = array("#&#", "#<#", "#>#");
		$html_entities_replace = array("&amp;", "&lt;", "&gt;");
		$message = preg_replace($html_entities_match, $html_entities_replace, $message);
	}

	if( $bbcode_on && $bbcode_uid != "" )
	{
		$message = bbencode_first_pass($message, $bbcode_uid);
	}

	return($message);
}


//
// Fill smiley templates (or just the variables) with smileys
// Either in a window or inline
//
function generate_smilies($mode)
{
	global $db, $board_config, $template, $lang, $images, $theme, $phpEx;
	global $user_ip, $forum_id, $session_length;
	global $userdata;

	if( $mode == 'window' )
	{
		$userdata = session_pagestart($user_ip, $forum_id, $session_length);
		init_userprefs($userdata);

		$gen_simple_header = TRUE;

		$page_title = $lang['Review_topic'] ." - $topic_title";
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			"smiliesbody" => "posting_smilies.tpl")
		);
	}

	$sql = "SELECT emoticon, code, smile_url   
		FROM " . SMILIES_TABLE . " 
		ORDER BY smilies_id";
	if( $result = $db->sql_query($sql) )
	{
		if( $db->sql_numrows($result) )
		{
			$rowset = array();
			while( $row = $db->sql_fetchrow($result) )
			{
				if( empty($rowset[$row['smile_url']]) )
				{
					$rowset[$row['smile_url']]['code'] = $row['code'];
					$rowset[$row['smile_url']]['emoticon'] = $row['emoticon'];
				}
			}

			$num_smilies = count($rowset);

			$smilies_count = ( $mode == 'inline' ) ? min(19, $num_smilies) : $num_smilies;
			$smilies_split_row = ( $mode == 'inline' ) ? 3 : 7;

			$s_colspan = 0;
			$row = 0;
			$col = 0;

			while( list($smile_url, $data) = @each($rowset) )
			{
				if( !$col )
				{
					$template->assign_block_vars("smilies_row", array());
				}

				$template->assign_block_vars("smilies_row.smilies_col", array(
					"SMILEY_CODE" => $data['code'],
					"SMILEY_IMG" => $board_config['smilies_path'] . "/" . $smile_url,
					"SMILEY_DESC" => $data['emoticon'])
				);

				$s_colspan = max($s_colspan, $col + 1);

				if( $col == $smilies_split_row )
				{
					if( $mode == 'inline' && $row == 4 )
					{
						break;
					}
					$col = 0;
					$row++;
				}
				else
				{
					$col++;
				}
			}

			if( $mode == 'inline' && $num_smilies > 20)
			{
				$template->assign_block_vars("switch_smilies_extra", array());

				$template->assign_vars(array(
					"L_MORE_SMILIES" => $lang['More_emoticons'], 
					"U_MORE_SMILIES" => append_sid("posting.$phpEx?mode=smilies"))
				);
			}

			$template->assign_vars(array(
				"L_EMOTICONS" => $lang['Emoticons'], 
				"L_CLOSE_WINDOW" => $lang['Close_window'], 
				"S_SMILIES_COLSPAN" => $s_colspan)
			);
		}
	}

	if( $mode == 'window' )
	{
		$template->pparse("smiliesbody");

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
}
?>
