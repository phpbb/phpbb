<?php
/***************************************************************************  
 *                                 auth.php
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

/* Notes:
 * auth() is going to become a very complex function and can take in a LARGE number of arguments. 
 * The currently included argements should be enough to handle any situation, however, if you need access to another
 * the best option would be to create a global variable and access it that way if you can.
 * 
 * auth() returns: 
 * TRUE if the user authorized
 * FALSE if the user is not
 */
function auth($type, $id = "", $user_ip = "")
{
	global $db, $userdata;

	switch($type) 
	{
	// Empty for the moment.
	}	
}


/*
 * The following functions are used for getting user information. They are not related directly to auth()
 */

function get_userdata_from_id($userid) 
{
	global $db;

	$sql = "SELECT * FROM ".USERS_TABLE." WHERE user_id = $userid";
	if(!$result = $db->sql_query($sql)) 
	{
		$userdata = array("error" => "1");
		return ($userdata);
	}
	if($db->sql_numrows($result))
	{
		$myrow = $db->sql_fetchrowset($result);
		return($myrow[0]);
	}
	else
	{
		$userdata = array("error" => "1");
		return ($userdata);
	}
}

function get_userdata($username) {

	global $db;

	$sql = "SELECT * FROM ".USERS_TABLE." WHERE username = '$username' AND user_level != ".DELETED;
	if(!$result = $db->sql_query($sql))
	{
		$userdata = array("error" => "1");
	}

	if($db->sql_numrows($result))
	{
		$myrow = $db->sql_fetchrowset($result);
		return($myrow[0]);
	}
	else
	{
		$userdata = array("error" => "1");
		return ($userdata);
	}
}

?>
