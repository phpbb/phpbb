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
 * 
 ***************************************************************************/ 

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
// Is user logged in? If yes are they an admin?
//
if( !$userdata['session_logged_in'] )
{
	header("Location: ../login.$phpEx?forward_page=/admin/");
}
else if( $userdata['user_level'] != ADMIN )
{
	message_die(GENERAL_MESSAGE, "You are not authorised to administer this board");
}

//
// Generate relevant output
//
if( $HTTP_GET_VARS['pane'] == 'top' )
{

	$template_header = "admin/overall_header.tpl";
	include('page_header_admin.'.$phpEx);

}
elseif( $HTTP_GET_VARS['pane'] == 'left' )
{
	$dir = opendir(".");

	$setmodules = 1;
	while($file = readdir($dir))
	{
		if(preg_match("/^admin_.*/", $file))
		{
			include($file);
		}
	}

	$template_header = "admin/page_header.tpl";
	include('page_header_admin.'.$phpEx);

	$template->set_filenames(array(
		"body" => "admin/navigate.tpl")
	);
	
	while( list($cat, $action_array) = each($module) )
	{
		$template->assign_block_vars("catrow", array(
			"CATNAME" => $cat)
		);
		while( list($action, $file)	= each($action_array) )
		{
			$template->assign_block_vars("catrow.actionrow", array(
				"ACTIONNAME" => $action,
				"FILE" => $file)
			);
		}
	}
	//var_dump($module);

	$template->pparse("body");

	$setmodules = 0;
}
elseif( $HTTP_GET_VARS['pane'] == 'right' )
{

	echo "This a right pane ;)";

}
else
{ 
	//
	// Generate frameset
	//
	$template->set_filenames(array(
		"body" => "admin/index_frameset.tpl")
	);

	$template->assign_vars(array(
		"S_FRAME_HEADER" => "index.$phpEx?pane=top",
		"S_FRAME_NAV" => "index.$phpEx?pane=left",
		"S_FRAME_MAIN" => "index.$phpEx?pane=right")
	);

	$template->pparse("body");
	
	exit;

}

?>