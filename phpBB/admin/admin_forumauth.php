<?php
/***************************************************************************  
 *                            admin_forumauth.php 
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

if($setmodules == 1)
{
	$filename = basename(__FILE__);
	$module['Auth']['Forums']   = $filename;

	return;
}

$phpbb_root_path = "./../";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
init_userprefs($userdata);
//
// End session management
//

//
// Check user permissions
//
if( !$userdata['session_logged_in'] )
{
	header("Location: ../login.$phpEx?forward_page=/admin");
}
else if( $userdata['user_level'] != ADMIN )
{
	message_die(GENERAL_MESSAGE, "You are not authorised to administer this board");
}

//
// Start program - define vars
//
$simple_auth_ary = array(
	0  => array(0, 0, 0, 0, 1, 0, 3, 3), 
	1  => array(0, 0, 0, 0, 3, 3, 3, 3), 
	2  => array(0, 0, 1, 1, 1, 1, 3, 3), 
	3  => array(1, 1, 1, 1, 1, 1, 3, 3), 
	4  => array(0, 2, 2, 2, 2, 2, 2, 3),
	5  => array(2, 2, 2, 2, 2, 2, 2, 3),
	6  => array(0, 3, 3, 3, 3, 3, 3, 3),
	7  => array(3, 3, 3, 3, 3, 3, 3, 3), 
	8  => array(0, 0, 3, 0, 0, 0, 3, 3), 
	9  => array(0, 0, 3, 1, 0, 0, 3, 3)
);

$simple_auth_types = array("Public", "Test Restricted", "Registered", "Registered [Hidden]", "Private", "Private [Hidden]", "Moderators", "Moderators [Hidden]", "Moderator Post + All Reply", "Moderator Post + Reg Reply");

$forum_auth_fields = array("auth_view", "auth_read", "auth_post", "auth_reply", "auth_edit", "auth_delete", "auth_sticky", "auth_announce");
$forum_auth_levels = array("ALL", "REG", "ACL", "MOD", "ADMIN");
$forum_auth_const = array(AUTH_ALL, AUTH_REG, AUTH_ACL, AUTH_MOD, AUTH_ADMIN);

// Future Stuff
/*$simple_auth_ary = array(
	0  => array(0, 0, 0, 0, 1, 0, 3, 3, 0, 0, 0), 
	1  => array(0, 0, 0, 0, 3, 3, 3, 3, 3, 3, 3), 
	2  => array(0, 0, 1, 1, 1, 1, 3, 3, 1, 1, 1), 
	3  => array(1, 1, 1, 1, 1, 1, 3, 3, 1, 1, 1), 
	4  => array(0, 2, 2, 2, 2, 2, 2, 3, 2, 2, 2),
	5  => array(2, 2, 2, 2, 2, 2, 2, 3, 2, 2, 2),
	6  => array(0, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3),
	7  => array(3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3), 
	8  => array(0, 0, 3, 0, 0, 0, 3, 3, 3, 3, 3), 
	9  => array(0, 0, 3, 1, 0, 0, 3, 3, 3, 3, 3)
);*/
//, "auth_votecreate", "auth_vote", "auth_attachments"


if(isset($HTTP_GET_VARS[POST_FORUM_URL]) || isset($HTTP_POST_VARS[POST_FORUM_URL]))
{
	$forum_id = (isset($HTTP_POST_VARS[POST_FORUM_URL])) ? $HTTP_POST_VARS[POST_FORUM_URL] : $HTTP_GET_VARS[POST_FORUM_URL];

	$forum_sql = "AND forum_id = $forum_id";
}
else
{
	unset($forum_id);
	$forum_sql = "";
}

if(isset($HTTP_GET_VARS['adv']))
{
	$adv = $HTTP_GET_VARS['adv'];
}
else
{
	$adv = -1;
}

if(isset($HTTP_POST_VARS['submit']))
{
	$sql = "";

	if(!empty($forum_id))
	{
		$sql = "UPDATE " . FORUMS_TABLE . " SET ";

		if(isset($HTTP_POST_VARS['simpleauth']))
		{
			$simple_ary = $simple_auth_ary[$HTTP_POST_VARS['simpleauth']];

			for($i = 0; $i < count($simple_ary); $i++)
			{
				$sql .= $forum_auth_fields[$i] . " = " . $simple_ary[$i];
				if($i < count($simple_ary) - 1)
				{
					$sql .= ", ";
				}
			}

			$sql .= " WHERE forum_id = $forum_id";
		}
		else
		{
			for($i = 0; $i < count($forum_auth_fields); $i++)
			{
				$value = $HTTP_POST_VARS[$forum_auth_fields[$i]];

				if($forum_auth_fields[$i] != 'auth_view')
				{
					if($HTTP_POST_VARS['auth_view'] > $value)
					{
						$value = $HTTP_POST_VARS['auth_view'];
					}
				}
				$sql .= $forum_auth_fields[$i] . " = " . $value;
				if($i < count($forum_auth_fields) - 1)
				{
					$sql .= ", ";
				}
			}

			$sql .= " WHERE forum_id = $forum_id";

		}

		if($sql != "")
		{
			if(!$db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Couldn't update auth table!", "", __LINE__, __FILE__, $sql);
			}
		}

		unset($forum_id);
		$forum_sql = "";
		$adv = 0;

	}
}

//
// Get required information, either all forums if
// no id was specified or just the requsted if it
// was
//
$sql = "SELECT f.*
	FROM " . FORUMS_TABLE . " f, " . CATEGORIES_TABLE . " c 
	WHERE c.cat_id = f.cat_id 
	$forum_sql 
	ORDER BY c.cat_order ASC, f.forum_order ASC";
$f_result = $db->sql_query($sql);

$forum_rows = $db->sql_fetchrowset($f_result);

//
// Page header
//
$template_header = "admin/page_header.tpl";
include('page_header_admin.'.$phpEx);

if(empty($forum_id))
{
	//
	// Output the selection table if no forum id was
	// specified
	//
	$template->set_filenames(array(
		"body" => "admin/forum_auth_select_body.tpl")
	);

	$select_list = "<select name=\"" . POST_FORUM_URL . "\">";
	for($i = 0; $i < count($forum_rows); $i++)
	{
		$select_list .= "<option value=\"" . $forum_rows[$i]['forum_id'] . "\">" . $forum_rows[$i]['forum_name'] . "</option>";
	}
	$select_list .= "</select>";

	$template->assign_vars(array(
		"S_FORUMAUTH_ACTION" => append_sid("admin_forumauth.$phpEx"), 
		"S_FORUMS_SELECT" => $select_list)
	);

}
else
{
	//
	// Output the authorisation details if an id was
	// specified
	//
	$template->set_filenames(array(
		"body" => "admin/forum_auth_body.tpl")
	);

	$forum_name = $forum_rows[0]['forum_name'];

	reset($simple_auth_ary);
	while(list($key, $auth_levels) = each($simple_auth_ary))
	{
		$matched = 1;
		for($k = 0; $k < count($auth_levels); $k++)
		{
			$matched_type = $key;

			if($forum_rows[0][$forum_auth_fields[$k]] != $auth_levels[$k])
			{
				$matched = 0;
			}
		}
		if($matched)
			break;
	}

	//
	// If we didn't get a match above then we
	// automatically switch into 'advanced' mode
	//
	if($adv == -1 && !$matched)
	{
		$adv = 1;
	}

	$s_column_span == 0;

	if( $adv <= 0 )
	{
		$simple_auth = "&nbsp;<select name=\"simpleauth\">";

		for($j = 0; $j < count($simple_auth_types); $j++)
		{
			if($matched_type == $j)
			{
				$simple_auth .= "<option value=\"$j\" selected>";
				$simple_auth .= $simple_auth_types[$j];
				$simple_auth .= "</option>";
			}
			else 
			{
				$simple_auth .= "<option value=\"$j\">" . $simple_auth_types[$j] . "</option>";
			}
		}

		$simple_auth .= "</select>&nbsp;";

		$template->assign_block_vars("forum_auth_titles", array(
			"CELL_TITLE" => "Simple Mode")
		);
		$template->assign_block_vars("forum_auth_data", array(
			"S_AUTH_LEVELS_SELECT" => $simple_auth)
		);

		$s_column_span++;
	}
	else
	{
		//
		// Output values of individual 
		// fields
		//
		for($j = 0; $j < count($forum_auth_fields); $j++)
		{
			$custom_auth[$j] = "&nbsp;<select name=\"" . $forum_auth_fields[$j] . "\">";

			for($k = 0; $k < count($forum_auth_levels); $k++)
			{
				if($forum_rows[0][$forum_auth_fields[$j]] == $forum_auth_const[$k])
				{
					$custom_auth[$j] .= "<option value=\"" . $forum_auth_const[$k] . "\" selected>";
					$custom_auth[$j] .= $forum_auth_levels[$k];
					$custom_auth[$j] .= "</option>";
				}
				else 
				{
					$custom_auth[$j] .= "<option value=\"" . $forum_auth_const[$k] . "\">". $forum_auth_levels[$k] . "</option>";
				}
			}
			$custom_auth[$j] .= "</select>&nbsp;";

			$template->assign_block_vars("forum_auth_titles", array(
				"CELL_TITLE" => ucfirst(preg_replace("/auth_/", "", $forum_auth_fields[$j])))
			);
			$template->assign_block_vars("forum_auth_data", array(
				"S_AUTH_LEVELS_SELECT" => $custom_auth[$j])
			);

			$s_column_span++;
		}
	}

	$switch_mode = "admin_forumauth.$phpEx?" . POST_FORUM_URL . "=" . $forum_id . "&adv=";
	$switch_mode .= ($adv <= 0 ) ? "1" : "0";
	$switch_mode_text = ($adv <= 0 ) ? "Advanced Mode" : "Simple Mode";
	$u_switch_mode = '<a href="' . $switch_mode . '">' . $switch_mode_text . '</a>';

	$s_hidden_fields = '<input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '">';

	$template->assign_vars(array(
		"FORUM_NAME" => $forum_name, 

		"U_FORUMAUTH_ACTION" => append_sid("admin_forumauth.$phpEx?" . POST_FORUM_URL . "=$forum_id"), 
		"U_SWITCH_MODE" => $u_switch_mode,

		"S_COLUMN_SPAN" => $s_column_span, 
		"S_HIDDEN_FIELDS" => $s_hidden_fields)
	);

}

$template->pparse("body");

include('page_footer_admin.'.$phpEx);

?>