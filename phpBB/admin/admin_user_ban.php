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

if($setmodules == 1)
{
	$filename = basename(__FILE__);
	$module['Users']['Ban'] = $filename . "?mode=ban";
	$module['Users']['Un-ban'] = $filename . "?mode=unban";

	return;
}

$phpbb_root_path = "./../";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

$mode = (isset($HTTP_GET_VARS['mode'])) ? $HTTP_GET_VARS['mode'] : "unban";

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


if( isset($HTTP_POST_VARS['submit']) )
{


}
else
{
	$template_header = "admin/page_header.tpl";
	include('page_header_admin.'.$phpEx);

	if( $mode == "ban" )
	{
		$template->set_filenames(array(
			"body" => "admin/user_ban_body.tpl")
		);

		$template->assign_vars(array(
			"L_BAN_TITLE" => $lang['Ban_control'], 
			"L_BAN_EXPLAIN" => $lang['Ban_explain'], 
			"L_BAN_USER" => $lang['Ban_username'],
			"L_BAN_IP" => $lang['Ban_IP'],
			"L_IP_OR_HOSTNAME" => $lang['Ban_IP'], 
			"L_BAN_IP_EXPLAIN" => $lang['Ban_IP_explain'], 
			"L_BAN_EMAIL" => $lang['Ban_email'], 
			"L_EMAIL_ADDRESS" => $lang['Email_address'],
			"L_BAN_EMAIL_EXPLAIN" => $lang['Ban_email_explain'], 
			"L_SUBMIT" => $lang['Submit'], 
			"L_RESET" => $lang['Reset'], 
			
			"S_BAN_ACTION" => append_sid("admin_user_ban.$phpEx"))
		);
	}
	else if( $mode == "unban" )
	{

	}

}


$template->pparse("body");

include('page_footer_admin.'.$phpEx);

?>