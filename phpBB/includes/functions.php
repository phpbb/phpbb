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

//
// Initialise user settings on page load
function init_userprefs($userdata)
{
	global $override_user_theme, $template, $sys_template;
	global $default_lang, $default_theme, $date_format, $sys_timezone;
	global $theme;

	if(!$override_user_themes)
	{
		if(($userdata['user_id'] != ANONYMOUS || $userdata['user_id'] != DELETED) && $userdata['user_theme'])
		{
			$theme = setuptheme($userdata['user_theme']);
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
	if($userdata['user_lang'] != '')
	{
		$default_lang = $userdata['user_lang'];
	}
	if($userdata['user_dateformat'] != '')
	{
		$date_format = $userdata['user_dateformat'];
	}
	if(isset($userdata['user_timezone']))
	{
		$sys_timezone = $userdata['user_timezone'];
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
	$sql = "SELECT * FROM ".THEMES_TABLE." WHERE themes_id = '$theme'";
	
	if(!$result = $db->sql_query($sql))
	{
		return(0);
	}
	if(!$myrow = $db->sql_fetchrow($result))
	{
		return(0);
	}
	return($myrow);
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
	
	$act_key = '';
	for($i = 0; $i < 8; $i++)
	{
		$act_key .= $chars[rand(0,$max_elements)];
	}
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
