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
function session_begin($user_id, $user_ip, $page_id, $session_length, $login = 0, $autologin = 0) 
{

	global $db;
	global $cookiename, $cookiedomain, $cookiepath, $cookiesecure, $cookielife;
	global $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $SID;

	if(isset($HTTP_COOKIE_VARS[$cookiename]))
	{
		$sessiondata = unserialize(stripslashes($HTTP_COOKIE_VARS[$cookiename]));
		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$sessiondata['sessionid'] = (isset($HTTP_GET_VARS['sid'])) ? $HTTP_GET_VARS['sid'] : "";
		$sessionmethod = SESSION_METHOD_GET;
	}
	$current_time = time();
	$expiry_time = $current_time - $session_length;

	//
	// Initial ban check against IP and userid
	//
	$sql = "SELECT ban_ip, ban_userid
		FROM ".BANLIST_TABLE."
		WHERE ban_ip = '$user_ip' 
			OR ban_userid = $user_id";
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
			$autologin = 0;
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
				WHERE session_ip <> '$user_ip'
					AND session_user_id = $user_id
					AND session_logged_in = 1";
			$result = $db->sql_query($sql_delete_same_user);
		}
	
		//
		// Try and pull the last time stored
		// in a cookie, if it exists
		//
		$sessiondata['lastvisit'] = (!empty($sessiondata['sessiontime'])) ? $sessiondata['sessiontime'] : $current_time;

		$sql_update = "UPDATE ".SESSIONS_TABLE."
			SET session_user_id = $user_id, session_start = $current_time, session_time = $current_time, session_page = $page_id, session_logged_in = $login
			WHERE (session_id = '".$sessiondata['sessionid']."')
				AND (session_ip = '$user_ip')";
		$result = $db->sql_query($sql_update);

		if(!$result || !$db->sql_affectedrows())
		{
			mt_srand( (double) microtime() * 1000000);
//			$session_id = md5(mt_rand(uniqid)); // This is a superior but more intensive creation method
			$session_id = mt_rand();
			
			$sql_insert = "INSERT INTO ".SESSIONS_TABLE."
				(session_id, session_user_id, session_start, session_time, session_last_visit, session_ip, session_page, session_logged_in)
				VALUES
				('$session_id', $user_id, $current_time, $current_time, ".$sessiondata['lastvisit'].", '$user_ip', $page_id, $login)";
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

			$sessiondata['sessionid'] = $session_id;
		}
		else
		{
			$session_id = $sessiondata['sessionid'];
		}

		if($autologin)
		{
			$autologin_key = md5(uniqid(mt_rand()));

			$sql_auto = "UPDATE ".USERS_TABLE."
				SET user_autologin_key = '$autologin_key'
				WHERE user_id = $user_id";
			$result = $db->sql_query($sql_auto);
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
			$sessiondata['autologinid'] = $autologin_key;
		}

		$sessiondata['userid'] = $user_id;
		$sessiondata['sessionstart'] = $current_time;
		$sessiondata['sessiontime'] = $current_time;
		$serialised_cookiedata = serialize($sessiondata);
		setcookie($cookiename, $serialised_cookiedata, ($current_time+$cookielife), $cookiepath, $cookiedomain, $cookiesecure);

		$SID = ($sessionmethod == SESSION_METHOD_GET) ? "sid=".$sessiondata['sessionid'] : "";
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
	global $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $SID;

	if(isset($HTTP_COOKIE_VARS[$cookiename]))
	{
		$sessiondata = unserialize(stripslashes($HTTP_COOKIE_VARS[$cookiename]));
		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$sessiondata['sessionid'] = (isset($HTTP_GET_VARS['sid'])) ? $HTTP_GET_VARS['sid'] : "";
		$sessionmethod = SESSION_METHOD_GET;
	}
	$current_time = time();
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
	// Redo without initial user_id check?
	// ie. check sessionid, then pull from DB
	// based on sessionid and sessionip only?
	// is this secure enough? probably, since
	// the DB is cleared every 'sessiontime' mins
	// (or when a user visits, whichever sooner)
	// and a user is logged out 
	// 
	if(isset($sessiondata['sessionid']))
	{
		//
		// session_id exists so go ahead and attempt
		// to grab all data in preparation
		//
		$sql = "SELECT u.*, s.*
			FROM ".SESSIONS_TABLE." s, ".USERS_TABLE." u
			WHERE s.session_id = '".$sessiondata['sessionid']."'
				AND s.session_ip = '$user_ip'
				AND u.user_id = s.session_user_id";
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
		
		//
		// Did the session exist in the DB?
		// 
		if(isset($userdata['user_id']))
		{

			$SID = ($sessionmethod == SESSION_METHOD_GET) ? "sid=".$sessiondata['sessionid'] : "";

			//
			// Only update session DB a minute or so after last update
			//
			if($current_time - $userdata['session_time'] > 60)
			{
				$sql = "UPDATE ".SESSIONS_TABLE."
					SET session_time = $current_time, session_page = $thispage_id
					WHERE (session_id = '".$userdata['session_id']."')
						AND (session_ip = '$user_ip')
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
					$userdata['session_time'] = $current_time;
					$sessiondata['sessiontime'] = $current_time;
					$serialised_cookiedata = serialize($sessiondata);
					setcookie($cookiename, $serialised_cookiedata, ($current_time+$cookielife), $cookiepath, $cookiedomain, $cookiesecure);

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

	$login = 0;
	$autologin = 0;

	if(isset($sessiondata['userid']) && isset($sessiondata['autologinid']))
	{
		$sql = "SELECT u.*
			FROM ".USERS_TABLE." u
			WHERE u.user_id = ".$sessiondata['userid'];
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

		if($userdata['user_autologin_key'])
		{
			if($userdata['user_autologin_key'] == $sessiondata['autologinid'])
			{
				//
				// We have a match, and not the kind you light ... 
				//
				$userdata['session_logged_in'] = 1;
				$login = 1;
				$autologin = 1;
			}
			$user_id = $sessiondata['userid'];
		}
		else
		{
			$user_id = ANONYMOUS;
		}
	}
	else
	{
		$user_id = ANONYMOUS;
	}

	$result_id = session_begin($user_id, $user_ip, $thispage_id, $session_length, $login, $autologin);
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
	else
	{
		$sql = "SELECT u.*, s.*
			FROM ".SESSIONS_TABLE." s, ".USERS_TABLE." u
			WHERE s.session_id = '$result_id'
				AND s.session_ip = '$user_ip'
				AND u.user_id = s.session_user_id";
		$result = $db->sql_query($sql);
		if (!$result) 
		{
			if(DEBUG)
			{
				error_die(SQL_QUERY, "Error doing DB query userdata row fetch : session_pagestart new user", __LINE__, __FILE__);
			}
			else
			{
				error_die(SESSION_CREATE);
			}
		}

		$userdata = $db->sql_fetchrow($result);
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
	global $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $SID;

	if(isset($HTTP_COOKIE_VARS[$cookiename]))
	{
		$sessiondata = unserialize(stripslashes($HTTP_COOKIE_VARS[$cookiename]));
		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$sessiondata['sessionid'] = (isset($HTTP_GET_VARS['sid'])) ? $HTTP_GET_VARS['sid'] : "";
		$sessionmethod = SESSION_METHOD_GET;
	}
	$current_time = time();

	$sql = "UPDATE  ".SESSIONS_TABLE."
		SET session_logged_in = 0, session_user_id = -1, session_time = $current_time
		WHERE (session_id = '$session_id')
			AND (session_user_id = $user_id)";
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

	if($sessiondata['autologinid'])
	{
		$sql = "UPDATE ".USERS_TABLE."
			SET user_autologin_key = ''
			WHERE user_id = $user_id";
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
		$sessiondata['autologinid'] = "";
	}

	$sessiondata['sessionend'] = $current_time;

	$serialised_cookiedata = serialize($sessiondata);
	setcookie($cookiename, $serialised_cookiedata, ($current_time+$cookielife), $cookiepath, $cookiedomain, $cookiesecure);

	$SID = ($sessionmethod == SESSION_METHOD_GET) ? "sid=".$sessiondata['sessionid'] : "";

	return 1;

} // session_end()

//
// Append $SID to a url
// Borrowed from phplib and modified. This is an
// extra routine utilised by the session
// code above and acts as a wrapper
// around every single URL and form action. If
// you replace the session code you must
// include this routine, even if it's empty.
//
function append_sid($url)
{
	global $SID;

	if(!empty($SID) && !eregi("^http:", $url) && !eregi("sid=", $url))
	{
		$url = ereg_replace("[&?]+$", "", $url);
		$url .= ( (strpos($url, "?") != false) ?  "&" : "?" ) . $SID;
	}

	return($url);

}

?>