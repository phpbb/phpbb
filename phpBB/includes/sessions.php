<?php
/***************************************************************************
 *                                sessions.php
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
 ***************************************************************************/

//
// session_begin()
//
// Adds/updates a new session to the database for the given userid.
// Returns the new session ID on success.
//
function session_begin($user_id, $user_ip, $page_id, $session_length, $auto_create = 0, $enable_autologin = 0)
{
	global $db, $board_config;
	global $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $SID;

	$cookiename = $board_config['cookie_name'];
	$cookiepath = $board_config['cookie_path'];
	$cookiedomain = $board_config['cookie_domain'];
	$cookiesecure = $board_config['cookie_secure'];

	if( isset($HTTP_COOKIE_VARS[$cookiename . '_sid']) || isset($HTTP_COOKIE_VARS[$cookiename . '_data']) )
	{
		$sessiondata = isset($HTTP_COOKIE_VARS[$cookiename . '_data']) ? unserialize(stripslashes($HTTP_COOKIE_VARS[$cookiename . '_data'])) : "";
		$session_id = isset($HTTP_COOKIE_VARS[$cookiename . '_sid']) ? stripslashes($HTTP_COOKIE_VARS[$cookiename . '_sid']) : "";

		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$session_id = ( isset($HTTP_GET_VARS['sid']) ) ? $HTTP_GET_VARS['sid'] : "";

		$sessionmethod = SESSION_METHOD_GET;
	}

	$current_time = time();
	$expiry_time = $current_time - $session_length;

	//
	// Try and pull the last time stored
	// in a cookie, if it exists
	//
	if( $user_id != ANONYMOUS )
	{
		//
		// This is a 'work-around' since I managed to 
		// freeze the schema without re-visiting sessions,
		// what's needed is a session timer in the user table
		// + the user_lastvisit ... damn damn damn damn and blast
		//
		$sql = "SELECT user_password, user_session_time, user_email    
			FROM " . USERS_TABLE . " 
			WHERE user_id = $user_id";
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(CRITICAL_ERROR, "Couldn't obtain lastvisit data from user table", "", __LINE__, __FILE__, $sql);
		}

		$row = $db->sql_fetchrow($result);

		$auto_login_key = $row['user_password'];

		if( $auto_create )
		{
			if( isset($sessiondata['autologinid']) )
			{
				if( $sessiondata['autologinid'] == $auto_login_key )
				{
					$login = 1;
					$enable_autologin = 1;

					$sessiondata['lastvisit'] = ( $row['user_session_time'] > 0 ) ? $row['user_session_time'] : $current_time;
				}
				else
				{
					$login = 0; 
					$enable_autologin = 0; 
					$user_id = ANONYMOUS;

					$sessiondata['lastvisit'] = ( !empty($sessiondata['lastvisit']) ) ? $sessiondata['lastvisit'] : $current_time;
				}
			}
			else
			{
				$login = 0;
				$enable_autologin = 0;
				$user_id = ANONYMOUS;

				$sessiondata['lastvisit'] = ( !empty($sessiondata['lastvisit']) ) ? $sessiondata['lastvisit'] : $current_time;
			}
		}
		else
		{
			$login = 1;
		}
	}
	else
	{
		$login = 0;
		$enable_autologin = 0;

		$sessiondata['lastvisit'] = ( !empty($sessiondata['lastvisit']) ) ? $sessiondata['lastvisit'] : $current_time;
	}

	//
	// Initial ban check against user id, IP and email address
	//
	ereg("(..)(..)(..)(..)", $user_ip, $user_ip_parts);

	$sql = "SELECT ban_ip, ban_userid, ban_email 
		FROM " . BANLIST_TABLE . "
		WHERE ban_ip = '" . $user_ip_parts[1] . $user_ip_parts[2] . $user_ip_parts[3] . $user_ip_parts[4] . "'
			OR ban_ip = '" . $user_ip_parts[1] . $user_ip_parts[2] . $user_ip_parts[3] . "ff'
			OR ban_ip = '" . $user_ip_parts[1] . $user_ip_parts[2] . "ffff'
			OR ban_ip = '" . $user_ip_parts[1] . "ffffff'
			OR ban_userid = $user_id";
	if( $user_id != ANONYMOUS )
	{
		$sql .= " OR ban_email LIKE '" . $row['user_email'] . "' 
			OR ban_email LIKE '" . substr($row['user_email'], strpos($row['user_email'], "@")) . "'";
	}
	$result = $db->sql_query($sql);
	if( !$result )
	{
		message_die(CRITICAL_ERROR, "Couldn't obtain ban information.", "", __LINE__, __FILE__, $sql);
	}

	$ban_info = $db->sql_fetchrow($result);

	if( $ban_info['ban_ip'] || $ban_info['ban_userid'] || $ban_info['ban_email'] )
	{
		message_die(CRITICAL_MESSAGE, 'You_been_banned');
	}

	//
	// Create or update the session
	//
	if( !$auto_create )
	{
		$sql = "UPDATE " . SESSIONS_TABLE . "
			SET session_user_id = $user_id, session_start = $current_time, session_last_visit = " . $sessiondata['lastvisit'] . ", session_time = $current_time, session_page = $page_id, session_logged_in = $login 
			WHERE session_id = '" . $session_id . "' 
				AND session_ip = '$user_ip'";
		$result = $db->sql_query($sql);
		if(!$result)
		{
			message_die(CRITICAL_ERROR, "Error updating current session : session_begin", "", __LINE__, __FILE__, $sql);
		}
	}
	else
	{
		mt_srand( (double) microtime() * 1000000);
		$session_id = md5(uniqid(mt_rand()));

		$sql = "INSERT INTO " . SESSIONS_TABLE . "
			(session_id, session_user_id, session_start, session_time, session_last_visit, session_ip, session_page, session_logged_in)
			VALUES ('$session_id', $user_id, $current_time, $current_time, " . $sessiondata['lastvisit'] . ", '$user_ip', $page_id, $login)";
		$result = $db->sql_query($sql);
		if(!$result)
		{
			message_die(CRITICAL_ERROR, "Error creating new session : session_begin", "", __LINE__, __FILE__, $sql);
		}
	}

	if( $user_id != ANONYMOUS )
	{
		$sessiondata['autologinid'] = ( $enable_autologin && $sessionmethod == SESSION_METHOD_COOKIE ) ? $auto_login_key : "";
	}

	$sessiondata['userid'] = $user_id;

	$serialised_cookiedata = serialize($sessiondata);
	setcookie($cookiename . '_data', $serialised_cookiedata, ($current_time + 31536000), $cookiepath, $cookiedomain, $cookiesecure);
	setcookie($cookiename . '_sid', $session_id, 0, $cookiepath, $cookiedomain, $cookiesecure);

	$SID = ( $sessionmethod == SESSION_METHOD_GET ) ? "sid=" . $session_id : "";

	return $session_id;

} // session_begin


//
// Checks for a given user session, tidies session
// table and updates user sessions at each page refresh
//
function session_pagestart($user_ip, $thispage_id, $session_length)
{
	global $db, $lang, $board_config;
	global $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $SID;

	$cookiename = $board_config['cookie_name'];
	$cookiepath = $board_config['cookie_path'];
	$cookiedomain = $board_config['cookie_domain'];
	$cookiesecure = $board_config['cookie_secure'];

	$current_time = time();
	unset($userdata);

	if( isset($HTTP_COOKIE_VARS[$cookiename . '_sid']) || isset($HTTP_COOKIE_VARS[$cookiename . '_data']) )
	{
		$sessiondata = isset( $HTTP_COOKIE_VARS[$cookiename . '_data'] ) ? unserialize(stripslashes($HTTP_COOKIE_VARS[$cookiename . '_data'])) : "";
		$session_id = isset( $HTTP_COOKIE_VARS[$cookiename . '_sid'] ) ? stripslashes($HTTP_COOKIE_VARS[$cookiename . '_sid']) : "";

		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$session_id = ( isset($HTTP_GET_VARS['sid']) ) ? $HTTP_GET_VARS['sid'] : "";

		$sessionmethod = SESSION_METHOD_GET;
	}

	//
	// Does a session exist?
	//
	if( !empty($session_id) )
	{
		//
		// session_id exists so go ahead and attempt to grab all
		// data in preparation
		//
		$sql = "SELECT u.*, s.*
			FROM " . SESSIONS_TABLE . " s, " . USERS_TABLE . " u
			WHERE s.session_id = '$session_id'
				AND u.user_id = s.session_user_id 
				AND s.session_ip = '$user_ip'";
		$result = $db->sql_query($sql);
		if( !$result )
		{
			message_die(CRITICAL_ERROR, "Error doing DB query userdata row fetch : session_pagestart", "", __LINE__, __FILE__, $sql);
		}

		$userdata = $db->sql_fetchrow($result);

		//
		// Did the session exist in the DB?
		//
		if( isset($userdata['user_id']) )
		{
			$SID = ( $sessionmethod == SESSION_METHOD_GET ) ? "sid=" . $session_id : "";

			//
			// Only update session DB a minute or so after last update
			//
			$last_update = ( $userdata['user_id'] == ANONYMOUS ) ? $userdata['session_time'] : $userdata['user_session_time'];

			if( $current_time - $last_update > 60 )
			{
				if( $userdata['user_id'] == ANONYMOUS )
				{
					$sessiondata['lastvisit'] = $current_time;

					$sql = "UPDATE " . SESSIONS_TABLE . " 
						SET session_time = $current_time, session_page = $thispage_id 
						WHERE session_id = '" . $userdata['session_id'] . "' 
							AND session_user_id = " . $userdata['user_id'] . " 
							AND session_ip = '$user_ip'";
				}
				else
				{
					$sql = "UPDATE " . USERS_TABLE . " 
						SET user_session_time = $current_time, user_session_page = $thispage_id 
						WHERE user_id = " . $userdata['user_id'];
				}
				$result = $db->sql_query($sql);
				if( !$result )
				{
					message_die(CRITICAL_ERROR, "Error updating sessions table : session_pagestart", "", __LINE__, __FILE__, $sql);
				}

				//
				// Delete expired sessions
				//
				$expiry_time = $current_time - $board_config['session_length'];
				$sql = "DELETE FROM " . SESSIONS_TABLE . " 
					WHERE session_time < $expiry_time 
						AND session_id <> '$session_id'";
				$result = $db->sql_query($sql);
				if( !$result )
				{
					message_die(CRITICAL_ERROR, "Error clearing sessions table : session_pagestart", "", __LINE__, __FILE__, $sql);
				}
			}

			setcookie($board_config['cookie_name'] . '_data', serialize($sessiondata), ($current_time + 31536000), $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
			setcookie($board_config['cookie_name'] . '_sid', $session_id, 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);

			return $userdata;
		}
	}
	//
	// If we reach here then no (valid) session exists. So we'll create a new one,
	// using the cookie user_id if available to pull basic user prefs.
	//

	$user_id = ( isset($sessiondata['userid']) ) ? $sessiondata['userid'] : ANONYMOUS;

	$result_id = session_begin($user_id, $user_ip, $thispage_id, $board_config['session_length'], TRUE);
	if( !$result_id )
	{
		message_die(CRITICAL_ERROR, "Error creating user session : session_pagestart", "", __LINE__, __FILE__, $sql);
	}
	else
	{
		$sql = "SELECT u.*, s.*
			FROM " . SESSIONS_TABLE . " s, " . USERS_TABLE . " u
			WHERE s.session_id = '$result_id'
				AND u.user_id = s.session_user_id 
				AND s.session_ip = '$user_ip'";
		$result = $db->sql_query($sql);
		if ( !$result )
		{
			message_die(CRITICAL_ERROR, "Error doing DB query userdata row fetch : session_pagestart new user", "", __LINE__, __FILE__, $sql);
		}

		$userdata = $db->sql_fetchrow($result);
	}

	return $userdata;

} // session_pagestart()

//
// session_end closes out a session
// deleting the corresponding entry
// in the sessions table
//
function session_end($session_id, $user_id)
{
	global $db, $lang, $board_config;
	global $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $SID;

	$cookiename = $board_config['cookie_name'];
	$cookiepath = $board_config['cookie_path'];
	$cookiedomain = $board_config['cookie_domain'];
	$cookiesecure = $board_config['cookie_secure'];

	$current_time = time();

	//
	// Pull cookiedata or grab the URI propagated sid
	//
	if( isset($HTTP_COOKIE_VARS[$cookiename . '_sid']) || isset($HTTP_COOKIE_VARS[$cookiename . '_data']) )
	{
		$sessiondata = isset( $HTTP_COOKIE_VARS[$cookiename . '_data'] ) ? unserialize(stripslashes($HTTP_COOKIE_VARS[$cookiename . '_data'])) : "";
		$session_id = isset( $HTTP_COOKIE_VARS[$cookiename . '_sid'] ) ? $HTTP_COOKIE_VARS[$cookiename . '_sid'] : "";

		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$session_id = ( isset($HTTP_GET_VARS['sid']) ) ? $HTTP_GET_VARS['sid'] : "";

		$sessionmethod = SESSION_METHOD_GET;
	}

	//
	// Delete existing session
	//
	$sql = "DELETE FROM " . SESSIONS_TABLE . " 
		WHERE session_id = '$session_id' 
			AND session_user_id = $user_id";
	$result = $db->sql_query($sql);
	if(!$result)
	{
		message_die(CRITICAL_ERROR, "Error removing user session : session_end", "", __LINE__, __FILE__, $sql);
	}

	//
	// If a registered user then update their last visit
	// and autologin (if necessary) details
	//
	if( $user_id != ANONYMOUS  )
	{
		if( isset($sessiondata['autologinid']) && $sessionmethod == SESSION_METHOD_COOKIE )
		{
			unset($sessiondata['autologinid']);
		}

		$sql = "UPDATE " . USERS_TABLE . "
			SET user_lastvisit = " . time() . " 
			WHERE user_id = $user_id";
		$result = $db->sql_query($sql);
		if (!$result)
		{
			message_die(CRITICAL_ERROR, "Couldn't reset user autologin key : session_end", "", __LINE__, __FILE__, $sql);
		}

	}

	$sessiondata['userid'] = ANONYMOUS;
	$sessiondata['lastvisit'] = $current_time;

	$serialised_cookiedata = serialize($sessiondata);
	setcookie($cookiename . '_data', $serialised_cookiedata, ($current_time + 31536000), $cookiepath, $cookiedomain, $cookiesecure);
	setcookie($cookiename . '_sid', '', 0, $cookiepath, $cookiedomain, $cookiesecure);

	$SID = ($sessionmethod == SESSION_METHOD_GET) ? "sid=" . $session_id : "";

	return TRUE;

} // session_end()

//
//
// Append $SID to a url. Borrowed from phplib and modified. This is an
// extra routine utilised by the session code above and acts as a wrapper
// around every single URL and form action. If you replace the session
// code you must include this routine, even if it's empty.
//
function append_sid($url, $non_html_amp = false)
{
	global $SID;

	if( !empty($SID) && !eregi("sid=", $url) )
	{
		$url .= ( ( strpos($url, "?") != false ) ?  ( ( $non_html_amp ) ? "&" : "&amp;" ) : "?" ) . $SID;
	}

	return($url);
}

?>