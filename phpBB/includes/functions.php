<?php
/***************************************************************************
 *                               functions.php
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
 *
 ***************************************************************************/

function get_db_stat($mode)
{
	global $db;

	switch($mode){
		case 'postcount':
			$sql = 'SELECT count(*) AS total FROM '.POSTS_TABLE;
		break;

		case 'usercount':
			$sql = 'SELECT count(*) AS total
						FROM '. USERS_TABLE .'
						WHERE user_id != '.ANONYMOUS.'
						AND user_level != '.DELETED;
		break;

		case 'newestuser':
			$sql = 'SELECT user_id, username
						FROM '.USERS_TABLE.'
						WHERE user_id != ' . ANONYMOUS. '
						AND user_level != '. DELETED .'
						ORDER BY user_id DESC LIMIT 1';
		break;

		case 'usersonline':
			$sql = "SELECT COUNT(*) AS online FROM ".SESSIONS_TABLE;
			break;
	}


	if(!$result = $db->sql_query($sql))
	{
		return 'ERROR';
	}
	else
	{
		$row = $db->sql_fetchrow($result);
		if($mode == 'newestuser')
		{
			return($row);
		}
		else if($mode == "usersonline")
		{
			return ($row['online']);
		}
		else
		{
			return($row['total']);
		}
	}
}


function make_jumpbox()
{
	global $db;
	global $l_jumpto, $l_noforums, $l_nocategories;

	$sql = "SELECT c.*
		FROM ".CATEGORIES_TABLE." c, ".FORUMS_TABLE." f
		WHERE f.cat_id = c.cat_id
		GROUP BY c.cat_id, c.cat_title, c.cat_order
		ORDER BY c.cat_order";
	if(!$q_categories = $db->sql_query($sql))
	{
		$db_error = $db->sql_error();
		error_die(SQL_QUERY, "Couldn't obtain category list.", __LINE__, __FILE__);
	}

	$total_categories = $db->sql_numrows();
	if($total_categories)
	{
		$category_rows = $db->sql_fetchrowset($q_categories);

		$limit_forums = "";

		$sql = "SELECT *
			FROM ".FORUMS_TABLE."
			ORDER BY cat_id, forum_order";
		if(!$q_forums = $db->sql_query($sql))
		{
			error_die(SQL_QUERY, "Couldn't obtain forums information.", __LINE__, __FILE__);
		}
		$total_forums = $db->sql_numrows($q_forums);
		$forum_rows = $db->sql_fetchrowset($q_forums);

		$boxstring = '';
		for($i = 0; $i < $total_categories; $i++)
		{
			$boxstring .= "<option value=\"-1\">&nbsp;</option>\n";
			$boxstring .= "<option value=\"-1\">".stripslashes($category_rows[$i]["cat_title"])."</OPTION>\n";
			$boxstring .= "<option value=\"-1\">----------------</OPTION>\n";

			if($total_forums)
			{
				for($y = 0; $y < $total_forums; $y++)
				{
					if(  $forum_rows[$y]["cat_id"] == $category_rows[$i]["cat_id"] )
					{
						$name = stripslashes($forum_rows[$y]["forum_name"]);
						$boxstring .=  "<option value=\"".$forum_rows[$y]["forum_id"]."\">$name</OPTION>\n";
					}
				}
			}
			else
			{
				$boxstring .= "<option value=\"-1\">-- ! No Forums ! --</option>\n";
			}
		}
	}
	else
	{
		$boxstring .= "<option value=\"-1\">-- ! No Categories ! --</option>\n";
	}

	return($boxstring);
}

// NOTE: This function should check is_dir($file), however the is_dir function seems to be buggy on my
// system so its not currently implemented that way
// 			- James
function template_select($default)
{
	$dir = opendir("templates");
	$template_select = "<select name=\"selected_template\">\n";
	while($file = readdir($dir))
	{
		unset($selected);

		if($file != "." && $file != ".." && $file != "CVS")
		{
			if($file == $default)
			{
				$selected = " SELECTED";
			}
			$template_select .= "<option value=\"$file\"$selected>$file</option>\n";
		}
	}
	$template_select .= "</select>";
	closedir($dir);
	return($template_select);
}

function language_select($default, $name="language", $dirname="language/")
{
	global $phpEx;
	$dir = opendir($dirname);
	$lang_select = "<select name=\"$name\">\n";
	while ($file = readdir($dir))
	{
		if (ereg("^lang_", $file))
		{
			$file = str_replace("lang_", "", $file);
			$file = str_replace(".$phpEx", "", $file);
			$file == $default ? $selected = " SELECTED" : $selected = "";
			$lang_select .= "  <option$selected>$file\n";
		}
	}
	$lang_select .= "</select>\n";
	closedir($dir);
	return $lang_select;
}

function theme_select($default)
{
	global $db;

	$sql = "SELECT theme_id, theme_name FROM ".THEMES_TABLE." ORDER BY theme_name";
	if($result = $db->sql_query($sql))
	{
		$num = $db->sql_numrows($result);
		$rowset = $db->sql_fetchrowset($result);
		$theme_select = "<select name=\"theme\">\n";
		for($i = 0; $i < $num; $i++)
		{
			if((stripslashes($rowset[$i]["theme_name"]) == $default) || ($rowset[$i]["theme_id"] == $default))
			{
				$selected = " SELECTED";
			}
			else
			{
				$selected = "";
			}
			$theme_select .= "\t<option value=\"".$rowset[$i]["theme_id"]."\"$selected>".stripslashes($rowset[$i]["theme_name"])."</option>\n";
		}
		$theme_select .= "</select>\n";
	}
	else
	{
		$theme_select = "<select name=\"theme\"><option value=\"-1\">Error in theme_select</option></select>";
	}
	return($theme_select);
}

//
// Initialise user settings on page load
//
function init_userprefs($userdata)
{

	global $override_user_theme, $template, $sys_template;
	global $bgcolor, $table_bgcolor, $textcolor, $category_title, $table_header;
	global $color1, $color2, $header_image, $newtopic_image;
	global $reply_locked_image, $reply_image, $linkcolor, $vlinkcolor;
	global $default_lang, $date_format, $sys_timezone;

	if(!$override_user_theme)
	{
		if($userdata['user_id'] != ANONYMOUS || $userdata['user_id'] != DELETED)
		{
			$theme = setuptheme($userdata["user_theme"]);
		}
		else
		{
			$theme = setuptheme($default_theme);
		}
	}
	else
	{
		$theme = setuptheme($override_user_theme);
	}
	if($theme)
	{
		$bgcolor = $theme["bgcolor"];
		$table_bgcolor = $theme["table_bgcolor"];
		$textcolor = $theme["textcolor"];
		$category_title = $theme["category_title"];
		$table_header = $theme["table_header"];
		$color1 = $theme["color1"];
		$color2 = $theme["color2"];
		$header_image = $theme["header_image"];
		$newtopic_image = $theme["newtopic_image"];
		$reply_locked_image = $theme["reply_locked_image"];
		$reply_image = $theme["reply_image"];
		$linkcolor = $theme["linkcolor"];
		$vlinkcolor = $theme["vlinkcolor"];
	}
	if($userdata["user_lang"] != "")
	{
		$default_lang = $userdata["user_lang"];
	}
	if($userdata["user_dateformat"] != "")
	{
		$date_format = $userdata["user_dateformat"];
	}
	if($userdata["user_timezone"])
	{
		$sys_timezone = $userdata["user_timezone"];
	}

	// Setup user's Template
	if($userdata['user_template'] != '')
	{
		$template = new Template("templates/".$userdata['user_template']);
	}
	else
	{
		$template = new Template("templates/".$sys_template);
	}

	// Include the appropriate language file ... if it exists.
	if(!strstr($PHP_SELF, "admin"))
	{
		if(file_exists('language/lang_'.$default_lang.'.'.$phpEx))
		{
			include('language/lang_'.$default_lang.'.'.$phpEx);
		}
	}
	else
	{
		if(strstr($PHP_SELF, "topicadmin"))
		{
			include('language/lang_'.$default_lang.'.'.$phpEx);
		}
		else
		{
			include('../language/lang_'.$default_lang.'.'.$phpEx);
		}
	}

	return;

}
function setuptheme($theme)
{
	global $db;

	$sql = "SELECT *
		FROM ".THEMES_TABLE."
		WHERE theme_id = '$theme'";
	if(!$result = $db->sql_query($sql))
		return(0);

	if(!$myrow = $db->sql_fetchrow($result))
		return(0);

	return($myrow);
}

function tz_select($default)
{
	global $board_tz;
	if(!isset($default))
	{
		$default == $board_tz;
	}
	$tz_select = "<select name=\"timezone\">";
	$tz_array = array(
			"-12" => "(GMT -12:00 hours) Eniwetok, Kwajalein",
			"-11" => "(GMT -11:00 hours) Midway Island, Samoa",
			"-10" => "(GMT -10:00 hours) Hawaii",
			"-9" => "(GMT -9:00 hours) Alaska",
			"-8" => "(GMT -8:00 hours) Pacific Time (US & Canada)",
			"-7" => "(GMT -7:00 hours) Mountain Time (US & Canada)",
			"-6" => "(GMT -6:00 hours) Central Time (US & Canada), Mexico City",
			"-5" => "(GMT -5:00 hours) Eastern Time (US & Canada), Bogota, Lima, Quito",
			"-4" => "(GMT -4:00 hours) Atlantic Time (Canada), Caracas, La Paz",
			"-3.5" => "(GMT -3:30 hours) Newfoundland",
			"-3" => "(GMT -3:00 hours) Brazil, Buenos Aires, Georgetown",
			"-2" => "(GMT -2:00 hours) Mid-Atlantic, Ascension Is., St. Helena, ",
			"-1" => "(GMT -1:00 hours) Azores, Cape Verde Islands",
			"0"  => "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia",
			"+1" => "(GMT +1:00 hours) CET, Berlin, Brussels, Copenhagen, Madrid, Paris, Rome",
			"+2" => "(GMT +2:00 hours) EET, Kaliningrad, South Africa, Warsaw",
			"+3" => "(GMT +3:00 hours) Baghdad, Kuwait, Riyadh, Moscow, St. Petersburg, Volgograd, Nairobi",
			"+3.5" => "(GMT +3:30 hours) Tehran",
			"+4" => "(GMT +4:00 hours) Abu Dhabi, Baku, Muscat, Tbilisi",
			"+4.5" => "(GMT +4:30 hours) Kabul",
			"+5" => "(GMT +5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent",
			"+5.5" => "(GMT +5:30 hours) Bombay, Calcutta, Madras, New Delhi",
			"+6" => "(GMT +6:00 hours) Almaty, Colombo, Dhaka",
			"+7" => "(GMT +7:00 hours) Bangkok, Hanoi, Jakarta",
			"+8" => "(GMT +8:00 hours) Beijing, Perth, Singapore, Hong Kong, Chongqing, Urumqi, Taipei",
			"+9" => "(GMT +9:00 hours) Tokyo, Seoul, Osaka, Sapporo, Yakutsk",
			"+9.5" => "(GMT +9:30 hours) Adelaide, Darwin",
			"+10" => "(GMT +10:00 hours) EAST (East Australian Standard), Guam, Papua New Guinea, Vladivostok",
			"+11" => "(GMT +11:00 hours) Magadan, Solomon Islands, New Caledonia",
			"+12" => "(GMT +12:00 hours) Auckland, Wellington, Fiji, Kamchatka, Marshall Island");

	while(list($offset, $zone) = each($tz_array))
	{
		if($offset == $default)
		{
			$selected = " SELECTED";
		}
		else
		{
			$selected = "";
		}
		$tz_select .= "\t<option value=\"$offset\"$selected>$zone</option>\n";
	}
	$tz_select .= "</select>\n";
	return($tz_select);
}

function validate_username(&$username)
{

	global $db;

	$username = trim($username);
	$username = strip_tags($username);
	$username = htmlspecialchars($username);
	if(empty($username))
	{
		return(FALSE);
	}

	$valid_name = TRUE;
	$sql = "SELECT LOWER(username) FROM ".USERS_TABLE." WHERE username = '$username'";
	if($result = $db->sql_query($sql))
	{
		if( ($numrows = $db->sql_numrows($result) ) > 0)
		{
			$valid_name = FALSE;
		}
	}

	$sql = "SELECT disallow_username FROM ".DISALLOW_TABLE." WHERE disallow_username = '$username'";
	if($result = $db->sql_query($sql))
	{
		if(($numrows = $db->sql_numrows($result)) > 0)
		{
			$valid_name = FALSE;
		}
	}

	return($valid_name);
}
function generate_activation_key()
{
	$chars = array(
		  "a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J",
		  "k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T",
		  "u","U","v","V","w","W","x","X","y","Y","z","Z","1","2","3","4","5","6","7","8",
		  "9","0"
		  );
   $max_elements = count($chars) - 1;
   srand((double)microtime()*1000000);
   $act_key = $chars[rand(0,$max_elements)];
   $act_key .= $chars[rand(0,$max_elements)];
   $act_key .= $chars[rand(0,$max_elements)];
   $act_key .= $chars[rand(0,$max_elements)];
   $act_key .= $chars[rand(0,$max_elements)];
   $act_key .= $chars[rand(0,$max_elements)];
   $act_key .= $chars[rand(0,$max_elements)];
   $act_key .= $chars[rand(0,$max_elements)];
   $act_key_md = md5($act_key);

   return($act_key_md);
}

function encode_ip($dotquad_ip)
{
	$ip_sep = explode(".", $dotquad_ip);
	return (sprintf("%02x%02x%02x%02x", $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]));

	//return (( $ip_sep[0] * 0xFFFFFF + $ip_sep[0] ) + ( $ip_sep[1] *   0xFFFF + $ip_sep[1] ) + ( $ip_sep[2] *     0xFF + $ip_sep[2] ) + ( $ip_sep[3] ) );
}

function decode_ip($int_ip)
{
	$hexipbang = explode(".",chunk_split($int_ip, 2, "."));
	return hexdec($hexipbang[0]).".".hexdec($hexipbang[1]).".".hexdec($hexipbang[2]).".".hexdec($hexipbang[3]);

	//return sprintf( "%d.%d.%d.%d", ( ( $int_ip >> 24 ) & 0xFF ), ( ( $int_ip >> 16 ) & 0xFF ), ( ( $int_ip >>  8 ) & 0xFF ), ( ( $int_ip       ) & 0xFF ) );

}

//
// Create date/time from format and timezone
//
function create_date($format, $gmepoch, $tz)
{
	return (gmdate($format, $gmepoch + (3600 * $tz)));
}
?>