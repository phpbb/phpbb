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

//
// session_begin()
//
// Adds/updates a new session to the database for the given userid.
// Returns the new session ID on success.
//
function session_begin($user_id, $user_ip, $page_id, $session_length, $login = 0, $password = "") 
{

	global $db;
	global $cookiename, $cookiedomain, $cookiepath, $cookiesecure, $cookielife;
	global $HTTP_COOKIE_VARS;

	$current_time = time();
	$expiry_time = $current_time - $session_length;
	$int_ip = encode_ip($user_ip);

	//
	// Initial ban check against IP and userid
	//
	$sql = "SELECT ban_ip, ban_userid
		FROM ".BANLIST_TABLE."
		WHERE (ban_ip = '$int_ip' OR ban_userid = '$user_id')
			AND (ban_start < $current_time AND ban_end > $current_time )";
	$result = $db->sql_query($sql);
	if (!$result) 
	{
		error_die(SQL_QUERY, "Couldn't obtain ban information.", __LINE__, __FILE__);
	}
	$ban_info = $db->sql_fetchrow($result);

	//
	// Check for user and ip ban ...
	// 
	if($ban_info['ban_ip'] || $ban_info['ban_userid'])
	{
		error_die(AUTH_BANNED);
	}
	else
	{
		if($user_id == ANONYMOUS)
		{
			$login = 0;
		}
	
		$sql_update = "UPDATE ".SESSIONS_TABLE."
			SET session_user_id = $user_id, session_time = $current_time, session_page = $page_id, session_logged_in = $login
			WHERE (session_id = ".$HTTP_COOKIE_VARS[$cookiename]['sessionid'].")
				AND (session_ip = '$int_ip')";
	
		$result = $db->sql_query($sql_update);

		if(!$result || !$db->sql_affectedrows())
		{
			mt_srand( (double) microtime() * 1000000);
			$session_id = mt_rand();
	
			$sql_insert = "INSERT INTO ".SESSIONS_TABLE."
					(session_id, session_user_id, session_time, session_ip, session_page, session_logged_in)
					VALUES
					($session_id, $user_id, $current_time, '$int_ip', $page_id, $login)";
			$result = $db->sql_query($sql_insert);
			if(!$result)
			{
				if(DEBUG)
				{
					error_die(SQL_QUERY, "Error creating new session : session_begin", __LINE__, __FILE__);
				}
				else
				{
					error_die(SESSION_CREATE);
				}
			}

			setcookie($cookiename."[sessionid]", $session_id, $session_length, $cookiepath, $cookiedomain, $cookiesecure);
		}
		else
		{
			$session_id = $HTTP_COOKIE_VARS[$cookiename]['sessionid'];
		}

		if(!empty($password) && AUTOLOGON)
		{
			setcookie($cookiename."[useridref]", $password, $cookielife, $cookiepath, $cookiedomain, $cookiesecure);
		}
		setcookie($cookiename."[userid]", $user_id, $cookielife, $cookiepath, $cookiedomain, $cookiesecure);
		setcookie($cookiename."[sessionstart]", $current_time, $cookielife, $cookiepath, $cookiedomain, $cookiesecure);
		setcookie($cookiename."[sessiontime]", $current_time, $session_length, $cookiepath, $cookiedomain, $cookiesecure);

//		echo $sql_update."<BR><BR>".$sql_insert."<BR><BR>";

	}

	return $session_id;
   
} // session_begin


//
// Checks for a given user session, tidies session
// table and updates user sessions at each page refresh
//
function session_pagestart($user_ip, $thispage_id, $session_length)
{
	global $db;
	global $cookiename, $cookiedomain, $cookiepath, $cookiesecure, $cookielife;
	global $HTTP_COOKIE_VARS;

	unset($userdata);
	$current_time = time();
	$int_ip = encode_ip($user_ip);

	//
	// Delete expired sessions
	//
	$expiry_time = $current_time - $session_length;
	$sql = "DELETE FROM ".SESSIONS_TABLE."
		WHERE session_time < $expiry_time";
	$result = $db->sql_query($sql);
	if(!$result)
	{
		if(DEBUG)
		{
			error_die(SQL_QUERY, "Error clearing sessions table : session_pagestart", __LINE__, __FILE__);
		}
		else
		{
			error_die(SESSION_CREATE);
		}
	}
	
	if(isset($HTTP_COOKIE_VARS[$cookiename]['userid'])) 
	{
		//
		// userid exists so go ahead and grab all
		// data in preparation
		//
		$userid = $HTTP_COOKIE_VARS[$cookiename]['userid'];
		$sql = "SELECT u.*, s.session_id, s.session_time, s.session_logged_in, b.ban_ip, b.ban_userid
			FROM ".USERS_TABLE." u
			LEFT JOIN ".BANLIST_TABLE." b ON ( (b.ban_ip = '$int_ip' OR b.ban_userid = u.user_id)
				AND ( b.ban_start < $current_time AND b.ban_end > $current_time ) )
			LEFT JOIN ".SESSIONS_TABLE." s ON ( u.user_id = s.session_user_id  AND s.session_ip = '$int_ip' )
			WHERE u.user_id = $userid";
		$result = $db->sql_query($sql);
		if (!$result) 
		{
			if(DEBUG)
			{
				error_die(SQL_QUERY, "Error doing DB query userdata row fetch : session_pagestart", __LINE__, __FILE__);
			}
			else
			{
				error_die(SESSION_CREATE);
			}
		}
		$userdata = $db->sql_fetchrow($result);
	}

	if($userdata['user_id'] != ''){  // The ID in the cookie was really in the DB.
		//
		// Check for user and ip ban ...
		// 
		if($userdata['ban_ip'] || $userdata['ban_userid'])
		{
			error_die(BANNED);
		}
	
		//
		// Now, check to see if a session exists.
		// If it does then update it, if it doesn't
		// then create one.
		//
		if(isset($HTTP_COOKIE_VARS[$cookiename]['sessionid'])) 
		{

			//
			// Is the id the same as that in the cookie?
			// If it is then we see if it needs updating
			//
			if($HTTP_COOKIE_VARS[$cookiename]['sessionid'] == $userdata['session_id'])
			{

				//
				// Only update session DB a minute or so after last update
				//
				if($current_time - $userdata['session_time'] > 60)
				{

					$sql = "UPDATE ".SESSIONS_TABLE."
						SET session_time = '$current_time', session_page = '$thispage_id'
						WHERE (session_id = ".$userdata['session_id'].")
							AND (session_ip = '$int_ip')
							AND (session_user_id = ".$userdata['user_id'].")";
					$result = $db->sql_query($sql);
					if(!$result)
					{
						if(DEBUG)
						{
							error_die(SQL_QUERY, "Error updating sessions table : session_pagestart", __LINE__, __FILE__);
						}
						else
						{
							error_die(SESSION_CREATE);
						}
					}
					else
					{
						//
						// Update was success, send current time to cookie
						// and return userdata
						//
						setcookie($cookiename."[sessiontime]", $current_time, $session_length, $cookiepath, $cookiedomain, $cookiesecure);

						return $userdata;
					} // if (affectedrows)

				} // if (current_time)

				//
				// We didn't need to update session
				// so just return userdata
				//
				return $userdata;

			} // if (cookie session_id = DB session id)

		} // if session_id cookie set
			
		//
		// If we reach here then we have a valid
		// user_id set in the cookie but no
		// active session. So, try and create
		// new session (uses AUTOLOGON to determine
		// if user should be logged back on automatically)
		//
		if(AUTOLOGON && isset($HTTP_COOKIE_VARS[$cookiename]['useridref']))
		{
			if($HTTP_COOKIE_VARS[$cookiename]['useridref'] == $userdata['user_password'])
			{
				$autologon = 1;
				$password = $userdata['user_password'];
				$userdata['session_logged_in'] = 1;
			}
			else
			{
				$autologon = 0;
				$password = "";
				$userdata['session_logged_in'] = 0;
			}
		}
		else
		{
			$autologon = 0;
			$password = "";
			$userdata['session_logged_in'] = 0;
		}
		$result = session_begin($userdata['user_id'], $user_ip, $thispage_id, $session_length, $autologon, $password);
		if(!$result)
		{
			if(DEBUG)
			{
				error_die(SQL_QUERY, "Error creating ".$userdata['user_id']." session : session_pagestart", __LINE__, __FILE__);
			}
			else
			{
				error_die(SESSION_CREATE);
			}
		}

	}
	else
	{

		//
		// No userid cookie exists so we'll
		// set up a new anonymous session
		//
		$result = session_begin(ANONYMOUS, $user_ip, $thispage_id, $session_length, 0);
		if(!$result)
		{
			if(DEBUG)
			{
				error_die(SQL_QUERY, "Error creating anonymous session : session_pagestart", __LINE__, __FILE__);
			}
			else
			{
				error_die(SESSION_CREATE);
			}
		}
		$userdata['session_logged_in'] = 0;
	}

	return $userdata;

} // session_check()

//
// session_end closes out a session
// deleting the corresponding entry
// in the sessions table
//
function session_end($session_id, $user_id) 
{

	global $db;
	global $cookiename, $cookiedomain, $cookiepath, $cookiesecure, $cookielife;

	$current_time = time();

	$sql = "DELETE FROM ".SESSIONS_TABLE."
		WHERE (session_user_id = $user_id)
			AND (session_id = $session_id)";
	$result = $db->sql_query($sql, $db);
	if (!$result) 
	{
		if(DEBUG)
		{
			error_die(SQL_QUERY, "Couldn't delete user session : session_eng()", __LINE__, __FILE__);
		}
		else
		{
			error_die(SESSION_CREATE);
		}
	}

	setcookie($cookiename."[sessionid]", "");
	setcookie($cookiename."[sessionend]", $current_time, $cookielife, $cookiepath, $cookiedomain, $cookiesecure);

	return true;

} // session_end()

?>
