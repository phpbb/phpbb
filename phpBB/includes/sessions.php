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

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
}

//
// session_begin()
//
// Adds/updates a new session to the database for the given userid.
// Returns the new session ID on success.
//
function session_begin($user_id, $user_ip, $page_id, $auto_create = 0, $enable_autologin = 0)
{
	global $db, $board_config;
	global $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $SID;

	$cookiename = $board_config['cookie_name'];
	$cookiepath = $board_config['cookie_path'];
	$cookiedomain = $board_config['cookie_domain'];
	$cookiesecure = $board_config['cookie_secure'];

	if ( isset($HTTP_COOKIE_VARS[$cookiename . '_sid']) || isset($HTTP_COOKIE_VARS[$cookiename . '_data']) )
	{
		$session_id = isset($HTTP_COOKIE_VARS[$cookiename . '_sid']) ? stripslashes($HTTP_COOKIE_VARS[$cookiename . '_sid']) : '';
		$sessiondata = isset($HTTP_COOKIE_VARS[$cookiename . '_data']) ? unserialize(stripslashes($HTTP_COOKIE_VARS[$cookiename . '_data'])) : '';
		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$sessiondata = '';
		$session_id = ( isset($HTTP_GET_VARS['sid']) ) ? $HTTP_GET_VARS['sid'] : '';
		$sessionmethod = SESSION_METHOD_GET;
	}

	$last_visit = 0;
	$current_time = time();
	$expiry_time = $current_time - $board_config['session_length'];

	//
	// Try and pull the last time stored
	// in a cookie, if it exists
	//
	$sql = "SELECT * 
		FROM " . USERS_TABLE . " 
		WHERE user_id = $user_id";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(CRITICAL_ERROR, 'Could not obtain lastvisit data from user table', '', __LINE__, __FILE__, $sql);
	}

	$userdata = $db->sql_fetchrow($result);

	if ( $user_id != ANONYMOUS )
	{
		$auto_login_key = $userdata['user_password'];

		if ( $auto_create )
		{
			if ( isset($sessiondata['autologinid']) && $userdata['user_active'] )
			{
				// We have to login automagically
				if( $sessiondata['autologinid'] == $auto_login_key )
				{
					// autologinid matches password
					$login = 1;
					$enable_autologin = 1;

					$last_visit = ( $userdata['user_session_time'] > 0 ) ? $userdata['user_session_time'] : $current_time;
				}
				else
				{
					// No match; don't login, set as anonymous user
					$login = 0; 
					$enable_autologin = 0; 
					$user_id = ANONYMOUS;
				}
			}
			else
			{
				// Autologin is not set. Don't login, set as anonymous user
				$login = 0;
				$enable_autologin = 0;
				$user_id = ANONYMOUS;
			}
		}
		else
		{
			$last_visit = ( $userdata['user_session_time'] > 0 ) ? $userdata['user_session_time'] : $current_time;
			$login = 1;
		}
	}
	else
	{
		$login = 0;
		$enable_autologin = 0;
	}

	//
	// Initial ban check against user id, IP and email address
	//
	preg_match('/(..)(..)(..)(..)/', $user_ip, $user_ip_parts);

	$sql = "SELECT ban_ip, ban_userid, ban_email 
		FROM " . BANLIST_TABLE . " 
		WHERE ban_ip IN ('" . $user_ip_parts[1] . $user_ip_parts[2] . $user_ip_parts[3] . $user_ip_parts[4] . "', '" . $user_ip_parts[1] . $user_ip_parts[2] . $user_ip_parts[3] . "ff', '" . $user_ip_parts[1] . $user_ip_parts[2] . "ffff', '" . $user_ip_parts[1] . "ffffff')
			OR ban_userid = $user_id";
	if ( $user_id != ANONYMOUS )
	{
		$sql .= " OR ban_email LIKE '" . str_replace("\'", "''", $row['user_email']) . "' 
			OR ban_email LIKE '" . substr(str_replace("\'", "''", $row['user_email']), strpos(str_replace("\'", "''", $row['user_email']), "@")) . "'";
	}
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(CRITICAL_ERROR, 'Could not obtain ban information', '', __LINE__, __FILE__, $sql);
	}

	if ( $ban_info = $db->sql_fetchrow($result) )
	{
		if ( $ban_info['ban_ip'] || $ban_info['ban_userid'] || $ban_info['ban_email'] )
		{
			message_die(CRITICAL_MESSAGE, 'You_been_banned');
		}
	}

	//
	// Create or update the session
	//
	$sql = "UPDATE " . SESSIONS_TABLE . "
		SET session_user_id = $user_id, session_start = $current_time, session_time = $current_time, session_page = $page_id, session_logged_in = $login
		WHERE session_id = '" . $session_id . "' 
			AND session_ip = '$user_ip'";
	if ( !($result = $db->sql_query($sql)) || !$db->sql_affectedrows() )
	{
		$session_id = md5(uniqid($user_ip));

		$sql = "INSERT INTO " . SESSIONS_TABLE . "
			(session_id, session_user_id, session_start, session_time, session_ip, session_page, session_logged_in)
			VALUES ('$session_id', $user_id, $current_time, $current_time, '$user_ip', $page_id, $login)";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(CRITICAL_ERROR, 'Error creating new session : session_begin', '', __LINE__, __FILE__, $sql);
		}
	}

	if ( $user_id != ANONYMOUS )
	{
		$sql = "UPDATE " . USERS_TABLE . " 
			SET user_session_time = $current_time, user_session_page = $page_id, user_lastvisit = $last_visit
			WHERE user_id = $user_id";
		if ( !$db->sql_query($sql) )
		{
			message_die(CRITICAL_ERROR, 'Error updating last visit time : session_begin', '', __LINE__, __FILE__, $sql);
		}

		$userdata['user_lastvisit'] = $last_visit;

		$sessiondata['autologinid'] = ( $enable_autologin && $sessionmethod == SESSION_METHOD_COOKIE ) ? $auto_login_key : '';
		$sessiondata['userid'] = $user_id;
	}

	$userdata['session_id'] = $session_id;
	$userdata['session_ip'] = $user_ip;
	$userdata['session_user_id'] = $user_id;
	$userdata['session_logged_in'] = $login;
	$userdata['session_page'] = $page_id;
	$userdata['session_start'] = $current_time;
	$userdata['session_time'] = $current_time;

	setcookie($cookiename . '_data', serialize($sessiondata), $current_time + 31536000, $cookiepath, $cookiedomain, $cookiesecure);
	setcookie($cookiename . '_sid', $session_id, 0, $cookiepath, $cookiedomain, $cookiesecure);
//	header('Set-cookie: ' . $cookiename . '_data=' . urlencode(serialize($sessiondata)) . '; expires=' . gmdate("l, d-M-Y H:i:s", $current_time + 31536000) . ' GMT; domain=' . $cookiedomain . '; path=' . $cookiepath . $cookiesecure);
//	header('Set-cookie: ' . $cookiename . '_sid=' . $session_id . '; domain=' . $cookiedomain . '; path=' . $cookiepath . $cookiesecure);

	$SID = ( $sessionmethod == SESSION_METHOD_GET ) ? 'sid=' . $session_id : '';

	return $userdata;
}

//
// Checks for a given user session, tidies session
// table and updates user sessions at each page refresh
//
function session_pagestart($user_ip, $thispage_id)
{
	global $db, $lang, $board_config;
	global $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $SID;

	$cookiename = $board_config['cookie_name'];
	$cookiepath = $board_config['cookie_path'];
	$cookiedomain = $board_config['cookie_domain'];
	$cookiesecure = $board_config['cookie_secure'];
	$cookiesecure = ( $board_config['cookie_secure'] ) ? '; secure' : '';

	$current_time = time();
	unset($userdata);

	if ( isset($HTTP_COOKIE_VARS[$cookiename . '_sid']) || isset($HTTP_COOKIE_VARS[$cookiename . '_data']) )
	{
		$sessiondata = isset( $HTTP_COOKIE_VARS[$cookiename . '_data'] ) ? unserialize(stripslashes($HTTP_COOKIE_VARS[$cookiename . '_data'])) : '';
		$session_id = isset( $HTTP_COOKIE_VARS[$cookiename . '_sid'] ) ? stripslashes($HTTP_COOKIE_VARS[$cookiename . '_sid']) : '';
		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$session_data = '';
		$session_id = ( isset($HTTP_GET_VARS['sid']) ) ? $HTTP_GET_VARS['sid'] : '';
		$sessionmethod = SESSION_METHOD_GET;
	}

	//
	// Does a session exist?
	//
	if ( !empty($session_id) )
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
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(CRITICAL_ERROR, 'Error doing DB query userdata row fetch : session_pagestart', '', __LINE__, __FILE__, $sql);
		}

		$userdata = $db->sql_fetchrow($result);

		//
		// Did the session exist in the DB?
		//
		if ( isset($userdata['user_id']) )
		{
			$SID = ( $sessionmethod == SESSION_METHOD_GET ) ? 'sid=' . $session_id : '';

			//
			// Only update session DB a minute or so after last update
			//
			$last_update = ( $userdata['user_id'] == ANONYMOUS ) ? $userdata['session_time'] : $userdata['user_session_time'];

			if ( $current_time - $last_update > 60 )
			{ // || $userdata['user_session_page'] != $thispage_id
				$sql = ( $userdata['user_id'] == ANONYMOUS ) ? "UPDATE " . SESSIONS_TABLE . " SET session_time = $current_time, session_page = $thispage_id WHERE session_id = '" . $userdata['session_id'] . "' AND session_ip = '$user_ip'" : "UPDATE " . USERS_TABLE . " SET user_session_time = $current_time, user_session_page = $thispage_id WHERE user_id = " . $userdata['user_id'];
				if ( !$db->sql_query($sql) )
				{
					message_die(CRITICAL_ERROR, 'Error updating sessions table : session_pagestart', '', __LINE__, __FILE__, $sql);
				}

				//
				// Delete expired sessions
				//
				$expiry_time = $current_time - $board_config['session_length'];
				$sql = "DELETE FROM " . SESSIONS_TABLE . " 
					WHERE session_time < $expiry_time 
						AND session_id <> '$session_id'";
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(CRITICAL_ERROR, 'Error clearing sessions table : session_pagestart', '', __LINE__, __FILE__, $sql);
				}

				setcookie($cookiename . '_data', serialize($sessiondata), $current_time + 31536000, $cookiepath, $cookiedomain, $cookiesecure);
				setcookie($cookiename . '_sid', $session_id, 0, $cookiepath, $cookiedomain, $cookiesecure);
//				header('Set-cookie: ' . $cookiename . '_data=' . urlencode(serialize($sessiondata)) . '; expires=' . gmdate("l, d-M-Y H:i:s", $current_time + 31536000) . ' GMT; domain=' . $cookiedomain . '; path=' . $cookiepath . $cookiesecure);
//				header('Set-cookie: ' . $cookiename . '_sid=' . $session_id . '; domain=' . $cookiedomain . '; path=' . $cookiepath . $cookiesecure);
			}

			return $userdata;
		}
	}

	//
	// If we reach here then no (valid) session exists. So we'll create a new one,
	// using the cookie user_id if available to pull basic user prefs.
	//
	$user_id = ( isset($sessiondata['userid']) ) ? $sessiondata['userid'] : ANONYMOUS;

	if ( !($userdata = session_begin($user_id, $user_ip, $thispage_id, TRUE)) )
	{
		message_die(CRITICAL_ERROR, 'Error creating user session : session_pagestart', '', __LINE__, __FILE__, $sql);
	}

	return $userdata;

}

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
//	$cookiesecure = ( $board_config['cookie_secure'] ) ? '; secure' : '';

	//
	// Pull cookiedata or grab the URI propagated sid
	//
	if ( isset($HTTP_COOKIE_VARS[$cookiename . '_sid']) )
	{
		$session_id = isset( $HTTP_COOKIE_VARS[$cookiename . '_sid'] ) ? $HTTP_COOKIE_VARS[$cookiename . '_sid'] : '';
		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$session_id = ( isset($HTTP_GET_VARS['sid']) ) ? $HTTP_GET_VARS['sid'] : '';
		$sessionmethod = SESSION_METHOD_GET;
	}

	//
	// Delete existing session
	//
	$sql = "DELETE FROM " . SESSIONS_TABLE . " 
		WHERE session_id = '$session_id' 
			AND session_user_id = $user_id";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(CRITICAL_ERROR, 'Error removing user session : session_end', '', __LINE__, __FILE__, $sql);
	}

	setcookie($cookiename . '_data', '', $current_time - 31536000, $cookiepath, $cookiedomain, $cookiesecure);
	setcookie($cookiename . '_sid', '', $current_time - 31536000, $cookiepath, $cookiedomain, $cookiesecure);
//	header('Set-cookie: ' . $cookiename . '_data=0; expires=' . gmdate("l, d-M-Y H:i:s", 0) . ' GMT; domain=' . $cookiedomain . '; path=' . $cookiepath. $cookiesecure);
//	header('Set-cookie: ' . $cookiename . '_sid=0; expires=' . gmdate("l, d-M-Y H:i:s", 0) . ' GMT; domain=' . $cookiedomain . '; path=' . $cookiepath . $cookiesecure);

	$SID = ( $sessionmethod == SESSION_METHOD_GET ) ? 'sid=' . $session_id : '';

	return TRUE;

}

//
// Append $SID to a url. Borrowed from phplib and modified. This is an
// extra routine utilised by the session code above and acts as a wrapper
// around every single URL and form action. If you replace the session
// code you must include this routine, even if it's empty.
//
function append_sid($url, $non_html_amp = false)
{
	global $SID;

	if ( !empty($SID) && !eregi('sid=', $url) )
	{
		$url .= ( ( strpos($url, '?') != false ) ?  ( ( $non_html_amp ) ? '&' : '&amp;' ) : '?' ) . $SID;
	}

	return($url);
}

?>