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
function session_begin($user_id, $user_ip, $page_id, $session_length, $login = FALSE, $autologin = FALSE) 
{

	global $db;
	global $cookiename, $cookiedomain, $cookiepath, $cookiesecure, $cookielife;
	global $HTTP_COOKIE_VARS;

	$cookiedata = unserialize(stripslashes($HTTP_COOKIE_VARS[$cookiename]));
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
			$login = FALSE;
			$autologin = FALSE;
		}
		//
		// Remove duplicate user_id from session table
		// if IP is different ... stops same user
		// logging in from different PC's at same time 
		// Do we want this ???
		//
		if( ( $login || $autologin ) && $user_id != ANONYMOUS && $user_id != DELETED )
		{
			$sql_delete_same_user = "DELETE FROM ".SESSIONS_TABLE."
				WHERE session_user_id = '$user_id'
					AND session_ip != '$int_ip'
					AND session_logged_in = '1'";
			$result = $db->sql_query($sql_delete_same_user);
		}
	
		$sql_update = "UPDATE ".SESSIONS_TABLE."
			SET session_user_id = '$user_id', session_start = '$current_time', session_time = '$current_time', session_page = '$page_id', session_logged_in = '$login'
			WHERE (session_id = '".$cookiedata['sessionid']."')
				AND (session_ip = '$int_ip')";
		$result = $db->sql_query($sql_update);

		if(!$result || !$db->sql_affectedrows())
		{
			mt_srand( (double) microtime() * 1000000);
			$session_id = mt_rand();
	
			$sql_insert = "INSERT INTO ".SESSIONS_TABLE."
				(session_id, session_user_id, session_start, session_time, session_ip, session_page, session_logged_in)
				VALUES
				('$session_id', '$user_id', '$current_time', '$current_time', '$int_ip', '$page_id', '$login')";
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

			$cookiedata['sessionid'] = $session_id;
		}
		else
		{
			$session_id = $cookiedata['sessionid'];
		}

		if($autologin)
		{
			$autologin_key = md5(uniqid(mt_rand()));

			$sql_update = "UPDATE ".USERS_TABLE."
				SET user_autologin_key = '$autologin_key'
				WHERE user_id = '$user_id'";
			$result = $db->sql_query($sql_update);
			if(!$result)
			{
				if(DEBUG)
				{
					error_die(GENERAL_ERROR, "Couldn't update users autologin key : session_begin", __LINE__, __FILE__);
				}
				else
				{
					error_die(SQL_QUERY, "Error creating new session", __LINE__ , __FILE__);
				}
			}
			$cookiedata['autologinid'] = $autologin_key;
		}

		$cookiedata['userid'] = $user_id;
		$cookiedata['sessionstart'] = $current_time;
		$cookiedata['sessiontime'] = $current_time;
		$serialised_cookiedata = serialize($cookiedata);

		setcookie($cookiename, $serialised_cookiedata, $session_length, $cookiepath, $cookiedomain, $cookiesecure);
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

	$cookiedata = unserialize(stripslashes($HTTP_COOKIE_VARS[$cookiename]));
	$current_time = time();
	$int_ip = encode_ip($user_ip);
	unset($userdata);

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
	
	//
	// Does a session exist?
	//
	if(isset($cookiedata['sessionid']) && isset($cookiedata['userid']))
	{
		//
		// session_id & and userid exist so go ahead and attempt
		// to grab all data in preparation
		//
		$sql = "SELECT u.*, s.*, b.ban_ip, b.ban_userid
			FROM ".SESSIONS_TABLE." s
			LEFT JOIN ".BANLIST_TABLE." b ON ( (b.ban_ip = '$int_ip' OR b.ban_userid = u.user_id)
				AND ( b.ban_start < $current_time AND b.ban_end > $current_time ) )
			LEFT JOIN ".USERS_TABLE." u ON ( u.user_id = s.session_user_id)
			WHERE s.session_id = '".$cookiedata['sessionid']."'
				AND s.session_user_id = '".$cookiedata['userid']."'
				AND s.session_ip = '$int_ip'";
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

		if($userdata['ban_ip'] || $userdata['ban_userid'])
		{
			error_die(BANNED);
		}
		//
		// Did the session exist in the DB?
		// 
		if(isset($userdata['user_id']))
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
					$cookiedata['sessiontime'] = $current_time;
					$serialised_cookiedata = serialize($cookiedata);
					setcookie($cookiename, $serialised_cookiedata, $session_length, $cookiepath, $cookiedomain, $cookiesecure);

					return $userdata;
				}

			}
			//
			// We didn't need to update session
			// so just return userdata
			//
			return $userdata;
		}
	}
	//
	// If we reach here then no (valid) session
	// exists. So we'll create a new one,
	// using the cookie user_id if available to
	// pull basic user prefs.
	//

	$login = FALSE;
	$autologin = FALSE;
	$userdata['session_logged_in'] = 0;

	if(isset($cookiedata['userid']))
	{
		$sql = "SELECT u.*
			FROM ".USERS_TABLE." u
			WHERE u.user_id = '".$cookiedata['userid']."'";
		$result = $db->sql_query($sql);
		if (!$result) 
		{
			if(DEBUG)
			{
				error_die(SQL_QUERY, "Error doing DB query userdata row fetch (non-session) : session_pagestart", __LINE__, __FILE__);
			}
			else
			{
				error_die(SESSION_CREATE);
			}
		}
		$userdata = $db->sql_fetchrow($result);

		if($userdata['user_autologin_key'] && isset($cookiedata['autologinid']))
		{
			if($userdata['user_autologin_key'] == $cookiedata['autologinid'])
			{
				//
				// We have a match, and not the kind you light ... 
				//
				$userdata['session_logged_in'] = 1;
				$login = TRUE;
				$autologin = TRUE;
			}
		}
		$userdata['user_id'] = $cookiedata['userid'];
	}
	else
	{
		$userdata['user_id'] = ANONYMOUS;
	}


	$result = session_begin($userdata['user_id'], $user_ip, $thispage_id, $session_length, $login, $autologin);
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
	$userdata['session_id'] = $result;
	$userdata['session_ip'] = $user_ip;

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
	global $HTTP_COOKIE_VARS;

	$cookiedata = unserialize(stripslashes($HTTP_COOKIE_VARS[$cookiename]));
	$current_time = time();

	$sql = "UPDATE  ".SESSIONS_TABLE."
		SET session_logged_in = '0'
		WHERE (session_user_id = $user_id)
			AND (session_id = $session_id)";
	$result = $db->sql_query($sql, $db);
	if (!$result) 
	{
		if(DEBUG)
		{
			error_die(SQL_QUERY, "Couldn't delete user session : session_end", __LINE__, __FILE__);
		}
		else
		{
			error_die(SESSION_CREATE);
		}
	}

	if($cookiedata['autologinid'])
	{
		$sql = "UPDATE ".USERS_TABLE."
			SET user_autologin_key = ''
			WHERE user_id = '$user_id'";
		$result = $db->sql_query($sql, $db);
		if (!$result) 
		{
			if(DEBUG)
			{
				error_die(SQL_QUERY, "Couldn't reset user autologin key : session_end", __LINE__, __FILE__);
			}
			else
			{
				error_die(SESSION_CREATE);
			}
		}
		$cookiedata['autologinid'] = "";
	}

	$cookiedata['sessionend'] = $current_time;
	$serialised_cookiedata = serialize($cookiedata);

	setcookie($cookiename, $serialised_cookiedata, $cookielife, $cookiepath, $cookiedomain, $cookiesecure);

	return true;

} // session_end()

?>
