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

//
// This function will prepare a posted message for 
// entry into the database.
//
function prepare_message($message, $html_on, $bbcode_on, $smile_on, $bbcode_uid = 0)
{
	global $board_config;

	//
	// Clean up the message
	//
	$message = trim($message);

	if( $html_on )
	{
		$start = -1;
		$end = 0;

		for($h = 0; $h < strlen($message); $h++)
		{
			$start = strpos($message, "<", $h);

			if($start > -1)
			{
				$end = strpos($message, ">", $start);

				if($end)
				{
					$length = $end - $start + 1;
					$tagallowed = 0;

					for($i = 0; $i < sizeof($board_config['allow_html_tags']); $i++)
					{
						$match_tag = trim($board_config['allow_html_tags'][$i]);
						list($match_tag_split) = explode(" ", $match_tag);

						if( preg_match("/^((\/" . $match_tag_split . ")|(" . $match_tag . "))[ \=]+/i", trim(substr($message, $start + 1, $length - 2)) . " ") )
						{
							$tagallowed = 1;
						}
					}

					if($length && !$tagallowed) 
					{
						$message = str_replace(substr($message, $start, $length), htmlspecialchars(substr($message, $start, $length)), $message);
					}
				}
				$start = -1;
			}
		}
	}

	if($bbcode_on)
	{
		$message = bbencode_first_pass($message, $bbcode_uid);
	}

	$message = addslashes($message);

	return($message);
}

?>