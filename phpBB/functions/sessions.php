<?php
/***************************************************************************  
 *                                 sessions.php
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


/**
 * new_session()
 * Adds a new session to the database for the given userid.
 * Returns the new session ID.
 * Also deletes all expired sessions from the database, based on the given session lifespan.
 */
function new_session($userid, $remote_ip, $lifespan, $db) 
{
   
   mt_srand( (double) microtime() * 1000000);
   $sessid = mt_rand();
   
   $currtime = (string) (time());
   $expirytime = (string) (time() - $lifespan);
   
   $deleteSQL = "DELETE FROM sessions WHERE (start_time < $expirytime)";
   $delresult = $db->sql_query($deleteSQL);
   
   if (!$delresult) 
     {
	error_die($db, SESSION_CREATE);
     }
   
   $sql = "INSERT INTO sessions (sess_id, user_id, start_time, remote_ip) VALUES ($sessid, $userid, $currtime, '$remote_ip')";
   
   $result = $db->sql_query($sql);
   
   if ($result) 
     {
	return $sessid;
     } 
   else 
     {
	error_die($db, SESSION_CREATE);
     } // if/else
   
} // new_session()

/*
 * Sets the sessID cookie for the given session ID. the $cookietime parameter
 * is no longer used, but just hasn't been removed yet. It'll break all the modules
 * (just login) that call this code when it gets removed.
 * Sets a cookie with no specified expiry time. This makes the cookie last until the
 * user's browser is closed. (at last that's the case in IE5 and NS4.7.. Haven't tried
 * it with anything else.)
 */
function set_session_cookie($sessid, $cookietime, $cookiename, $cookiepath, $cookiedomain, $cookiesecure) 
{
   // This sets a cookie that will persist until the user closes their browser window.
   // since session expiry is handled on the server-side, cookie expiry time isn't a big deal.
   setcookie($cookiename, $sessid, '', $cookiepath, $cookiedomain, $cookiesecure);

} // set_session_cookie()

/*
 * Returns the userID associated with the given session, based on
 * the given session lifespan $cookietime and the given remote IP
 * address. If no match found, returns 0.
 */
function get_userid_from_session($sessid, $cookietime, $remote_ip, $db) 
{
   $mintime = time() - $cookietime;
   $sql = "SELECT user_id 
	    FROM sessions 
	    WHERE (sess_id = $sessid) 
	      AND (start_time > $mintime) 
	      AND (remote_ip = '$remote_ip')";
   $result = $db->sql_query($sql);
   if (!$result) 
     {
	error_die($db, "Error doing DB query in get_userid_from_session()");
     }
   $rowset = $db->sql_fetchrowset();
   $num_rows = $db->sql_numrows();
   if ($num_rows == 0) 
     {
	return 0;
     } 
   else 
     {
	return $rowset[0]["user_id"];
     }
   
} // get_userid_from_session()


function update_session_time($sessid, $db) 
{

   $newtime = (string) time();
   $sql = "UPDATE sessions SET start_time=$newtime WHERE (sess_id = $sessid)";
   $result = $db->sql_query($sql);
   if (!$result) 
     {
	$db_error = $db->sql_error();
	error_die($db, "Error doing DB update in update_session_time(). Reason: " . $db_error["message"]);
     }
   return 1;

} // update_session_time()

function end_user_session($userid, $db) 
{
   $sql = "DELETE FROM sessions WHERE (user_id = $userid)";
   $result = $db->sql_query($sql, $db);
   if (!$result) 
     {
	$db_error = $db->sql_error();
	error_die($db, "Delete failed in end_user_session(). Reason: " . $db_error["message"]);
     }
   return 1;

} // end_session()

?>
