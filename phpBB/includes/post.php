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

?>