<?php
/***************************************************************************  
 *                             admin_groups.php
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
	$module['Groups']['Manage'] = $filename;

	return;
}

//
// Include required files, get $phpEx and check permissions
//
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
if( !$userdata['session_logged_in'] )
{
	header("Location: ../login.$phpEx?forward_page=admin/");
}
else if( $userdata['user_level'] != ADMIN )
{
	message_die(GENERAL_MESSAGE, $lang['Not_admin']);
}

if( isset($mode) )
{

	//
	// Ok they are editing a group or creating a new group
	//

	
}
else if( isset($updategroup) )
{
	//
	// Ok, they are submitting a group, let's save the data based on if it's new or editing
	//
	switch($mode)
	{
		case 'update':
		
		break;
		
		case 'new':
		
		break;
		
		case 'delete':
		
		break;
		
		case 'default':
		message_die(GENERAL_ERROR, $lang['Group_mode_not_selected']);
		break;
	}
}
else
{
	//
	// Default group selection box
	//
	// This should be altered on the final system 
	//

	$sql = "SELECT group_id, group_name
		FROM " . GROUPS_TABLE . " 
		WHERE group_single_user <> " . TRUE . "
		ORCER BY group_name";
	if(!$result = $db->sql_query($sql))
	{
//		message_die(GENERAL_ERROR, "Error getting group information", "", __LINE__, __FILE__, $sql);
	}
	if( !$db->sql_numrows($result) )
	{
//		message_die(GENERAL_MESSAGE, "No groups exist.");
	}
	
	$select_list = "<select name=\"group\">";
	for($i = 0; $i < count($user_list); $i++)
	{
		$select_list .= "<option value=\"" . $group_list[$i]['group_id'] . "\">" . $group_list[$i]['group_name'] . "</option>";
	}
	$select_list .= "</select>";

	include('page_header_admin.'.$phpEx);

	$template->set_filenames(array(
		"body" => "admin/group_select_body.tpl")
	);

	$template->assign_vars(array(
		"L_GROUP_TITLE" => $lang['Group'] . " " . $lang['Administration'], 
		"L_GROUP_EXPLAIN" => $lang['Group_admin_explain'], 
		"L_GROUP_SELECT" => $lang['Select_a'] . " " . $lang['Group'], 
		"L_LOOK_UP" => $lang['Look_up'] . " " . $lang['Group'], 

		"S_GROUP_ACTION" => append_sid("admin_groups.$phpEx"), 
		"S_GROUP_SELECT" => $select_list)
	);

}
include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>
