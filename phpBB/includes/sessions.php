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
function session_begin($user_id, $user_ip, $page_id, $session_length, $login = 0, $autologin = 0)
{

	global $db, $lang, $board_config, $phpEx;
	global $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $SID;

	$cookiename = $board_config['cookie_name'];
	$cookiepath = $board_config['cookie_path'];
	$cookiedomain = $board_config['cookie_domain'];
	$cookiesecure = $board_config['cookie_secure'];

	if( isset($HTTP_COOKIE_VARS[$cookiename . '_sid']) || isset($HTTP_COOKIE_VARS[$cookiename]) )
	{
		$sessiondata = isset($HTTP_COOKIE_VARS[$cookiename]) ? unserialize(stripslashes($HTTP_COOKIE_VARS[$cookiename])) : "";
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
	// Initial ban check against IP and userid
	//
	ereg("(..)(..)(..)(..)", $user_ip, $user_ip_parts);

	$sql = "SELECT ban_ip, ban_userid
		FROM " . BANLIST_TABLE . "
		WHERE ban_ip = '" . $user_ip_parts[1] . $user_ip_parts[2] . $user_ip_parts[3] . $user_ip_parts[4] . "'
			OR ban_ip = '" . $user_ip_parts[1] . $user_ip_parts[2] . $user_ip_parts[3] . "ff'
			OR ban_ip = '" . $user_ip_parts[1] . $user_ip_parts[2] . "ffff'
			OR ban_ip = '" . $user_ip_parts[1] . "ffffff'
			OR ban_userid = $user_id";
	$result = $db->sql_query($sql);
	if (!$result)
	{
		message_die(CRITICAL_ERROR, "Couldn't obtain ban information.", __LINE__, __FILE__, $sql);
	}

	$ban_info = $db->sql_fetchrow($result);

	//
	// Check for user and ip ban ...
	//
	if($ban_info['ban_ip'] || $ban_info['ban_userid'])
	{
		include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.'.$phpEx);
		message_die(CRITICAL_MESSAGE, $lang['You_been_banned']);
	}
	else
	{
		/*
		$sql = "SELECT COUNT(*)
			FROM " . SESSIONS_TABLE . "
			WHERE session_ip = '$user_ip'";
		if($result = $db->sql_query($sql))
		{
			if( $db->sql_numrows($result) > $board_config['session_max'] )
			{
				message_die(CRITICAL_MESSAGE, "Sorry but " . $board_config['sessions_max'] ." live sessions already exist for your IP. If you are browsing this site using multiple windows you should close one and visit later. If you are browsing from a single window or if this problem persists please contact the board administrator");
			}
		}
		*/

		if($user_id == ANONYMOUS)
		{
			$login = 0;
			$autologin = 0;
		}

		//
		// Try and pull the last time stored
		// in a cookie, if it exists
		//
		$sessiondata['lastvisit'] = (!empty($sessiondata['sessiontime'])) ? $sessiondata['sessiontime'] : $current_time;

		$sql_update = "UPDATE " . SESSIONS_TABLE . "
			SET session_user_id = $user_id, session_start = $current_time, session_time = $current_time, session_page = $page_id, session_logged_in = $login
			WHERE (session_id = '" . $session_id . "')
				AND (session_ip = '$user_ip')";
		$result = $db->sql_query($sql_update, END_TRANSACTION);

		if(!$result || !$db->sql_affectedrows())
		{
			$session_id = md5(uniqid($user_ip));

			$sql_insert = "INSERT INTO " . SESSIONS_TABLE . "
				(session_id, session_user_id, session_start, session_time, session_last_visit, session_ip, session_page, session_logged_in)
				VALUES ('$session_id', $user_id, $current_time, $current_time, " . $sessiondata['lastvisit'] . ", '$user_ip', $page_id, $login)";
			$result = $db->sql_query($sql_insert);
			if(!$result)
			{
				message_die(CRITICAL_ERROR, "Error creating new session : session_begin", __LINE__, __FILE__, $sql);
			}

		}

		if($autologin)
		{
			mt_srand( (double) microtime() * 1000000);
			$autologin_key = md5(uniqid(mt_rand()));

			$sql_auto = "UPDATE " . USERS_TABLE . "
				SET user_autologin_key = '$autologin_key'
				WHERE user_id = $user_id";
			$result = $db->sql_query($sql_auto, END_TRANSACTION);
			if(!$result)
			{
				message_die(CRITICAL_ERROR, "Couldn't update users autologin key : session_begin", __LINE__, __FILE__, $sql);
			}
			$sessiondata['autologinid'] = $autologin_key;
		}

		$sessiondata['userid'] = $user_id;
		$sessiondata['sessionstart'] = $current_time;
		$sessiondata['sessiontime'] = $current_time;

		$serialised_cookiedata = serialize($sessiondata);
		setcookie($cookiename, $serialised_cookiedata, ($current_time + 31536000), $cookiepath, $cookiedomain, $cookiesecure);
		// The session cookie may well change to last just this session soon ...
		setcookie($cookiename . '_sid', $session_id, 0, $cookiepath, $cookiedomain, $cookiesecure);

		$SID = ($sessionmethod == SESSION_METHOD_GET) ? "sid=" . $session_id : "";
	}

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

	if( isset($HTTP_COOKIE_VARS[$cookiename . '_sid']) || isset($HTTP_COOKIE_VARS[$cookiename]) )
	{
		$sessiondata = isset( $HTTP_COOKIE_VARS[$cookiename] ) ? unserialize(stripslashes($HTTP_COOKIE_VARS[$cookiename])) : "";
		$session_id = isset( $HTTP_COOKIE_VARS[$cookiename . '_sid'] ) ? stripslashes($HTTP_COOKIE_VARS[$cookiename . '_sid']) : "";

		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$session_id = (isset($HTTP_GET_VARS['sid'])) ? $HTTP_GET_VARS['sid'] : "";

		$sessionmethod = SESSION_METHOD_GET;
	}
	$current_time = time();
	unset($userdata);

	//
	// Delete expired sessions
	//
	$expiry_time = $current_time - $board_config['session_length'];
	$sql = "DELETE FROM " . SESSIONS_TABLE . "
		WHERE session_time < $expiry_time";
	$result = $db->sql_query($sql);
	if(!$result)
	{
		message_die(CRITICAL_ERROR, "Error clearing sessions table : session_pagestart", __LINE__, __FILE__, $sql);
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
				AND s.session_ip = '$user_ip'
				AND u.user_id = s.session_user_id";
		$result = $db->sql_query($sql);
		if (!$result)
		{
			message_die(CRITICAL_ERROR, "Error doing DB query userdata row fetch : session_pagestart", __LINE__, __FILE__, $sql);
		}

		$userdata = $db->sql_fetchrow($result);

		//
		// Did the session exist in the DB?
		//
		if( isset($userdata['user_id']) )
		{
			$SID = ($sessionmethod == SESSION_METHOD_GET) ? "sid=" . $session_id : "";

			$sessiondata['sessiontime'] = $current_time;
			$serialised_cookiedata = serialize($sessiondata);
			setcookie($cookiename, $serialised_cookiedata, ($current_time + 31536000), $cookiepath, $cookiedomain, $cookiesecure);

			//
			// Only update session DB a minute or so after last update
			//
			if($current_time - $userdata['session_time'] > 60)
			{
				$sql = "UPDATE " . SESSIONS_TABLE . "
					SET session_time = $current_time, session_page = $thispage_id
					WHERE (session_id = '" . $userdata['session_id'] . "')
						AND (session_ip = '$user_ip')
						AND (session_user_id = " . $userdata['user_id'] . ")";
				$result = $db->sql_query($sql);
				if(!$result)
				{
					message_die(CRITICAL_ERROR, "Error updating sessions table : session_pagestart", __LINE__, __FILE__, $sql);
				}
				else
				{
					$userdata['session_time'] = $current_time;

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
	// If we reach here then no (valid) session exists. So we'll create a new one,
	// using the cookie user_id if available to pull basic user prefs.
	//

	$login = 0;
	$autologin = 0;

	if( isset($sessiondata['userid']) && isset($sessiondata['autologinid']) )
	{
		$sql = "SELECT user_id, user_autologin_key
			FROM " . USERS_TABLE . "
			WHERE user_id = " . $sessiondata['userid'];
		$result = $db->sql_query($sql);
		if (!$result)
		{
			message_die(CRITICAL_ERROR, "Error doing DB query userdata row fetch (non-session) : session_pagestart", __LINE__, __FILE__, $sql);
		}

		$userdata = $db->sql_fetchrow($result);

		if($userdata['user_autologin_key'])
		{
			if($userdata['user_autologin_key'] == $sessiondata['autologinid'])
			{
				//
				// We have a match, and not the kind you light ...
				//
				$login = 1;
				$autologin = 1;
				$user_id = $sessiondata['userid'];
			}
			else
			{
				unset($userdata);
				$user_id = ANONYMOUS;
			}
		}
		else
		{
			unset($userdata);
			$user_id = ANONYMOUS;
		}
	}
	else
	{
		unset($userdata);
		$user_id = ANONYMOUS;
	}

	$result_id = session_begin($user_id, $user_ip, $thispage_id, $session_length, $login, $autologin);
	if(!$result)
	{
		message_die(CRITICAL_ERROR, "Error creating user session : session_pagestart", __LINE__, __FILE__, $sql);
	}
	else
	{
		$sql = "SELECT u.*, s.*
			FROM " . SESSIONS_TABLE . " s, " . USERS_TABLE . " u
			WHERE s.session_id = '$result_id'
				AND s.session_ip = '$user_ip'
				AND u.user_id = s.session_user_id";
		$result = $db->sql_query($sql);
		if (!$result)
		{
			message_die(CRITICAL_ERROR, "Error doing DB query userdata row fetch : session_pagestart new user", __LINE__, __FILE__, $sql);
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
	global $db, $lang, $board_config;
	global $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $SID;

	$cookiename = $board_config['cookie_name'];
	$cookiepath = $board_config['cookie_path'];
	$cookiedomain = $board_config['cookie_domain'];
	$cookiesecure = $board_config['cookie_secure'];

	if( isset($HTTP_COOKIE_VARS[$cookiename . '_sid']) || isset($HTTP_COOKIE_VARS[$cookiename]) )
	{
		$sessiondata = isset( $HTTP_COOKIE_VARS[$cookiename] ) ? unserialize(stripslashes($HTTP_COOKIE_VARS[$cookiename])) : "";
		$session_id = isset( $HTTP_COOKIE_VARS[$cookiename . '_sid'] ) ? stripslashes($HTTP_COOKIE_VARS[$cookiename . '_sid']) : "";

		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$session_id = ( isset($HTTP_GET_VARS['sid']) ) ? $HTTP_GET_VARS['sid'] : "";

		$sessionmethod = SESSION_METHOD_GET;
	}
	$current_time = time();

	$sql = "UPDATE  " . SESSIONS_TABLE . "
		SET session_logged_in = 0, session_user_id = -1, session_time = $current_time
		WHERE (session_id = '" . $session_id . "')
			AND (session_user_id = $user_id)";

	$result = $db->sql_query($sql, BEGIN_TRANSACTION);
	if (!$result)
	{
		message_die(CRITICAL_ERROR, "Couldn't delete user session : session_end", __LINE__, __FILE__, $sql);
	}

	if( isset($sessiondata['autologinid']) )
	{
		$sql = "UPDATE " . USERS_TABLE . "
			SET user_autologin_key = ''
			WHERE user_id = $user_id";

		$result = $db->sql_query($sql, END_TRANSACTION);
		if (!$result)
		{
			message_die(CRITICAL_ERROR, "Couldn't reset user autologin key : session_end", __LINE__, __FILE__, $sql);
		}
		$sessiondata['autologinid'] = "";
	}

	$sessiondata['sessionend'] = $current_time;

	$serialised_cookiedata = serialize($sessiondata);
	setcookie($cookiename, $serialised_cookiedata, ($current_time + 31536000), $cookiepath, $cookiedomain, $cookiesecure);
	// The session cookie may well change to last just this session soon ...
	setcookie($cookiename . '_sid', $session_id, 0, $cookiepath, $cookiedomain, $cookiesecure);

	$SID = ($sessionmethod == SESSION_METHOD_GET) ? "sid=" . $session_id : "";

	return TRUE;

} // session_end()

//
// Append $SID to a url. Borrowed from phplib and modified. This is an
// extra routine utilised by the session code above and acts as a wrapper
// around every single URL and form action. If you replace the session
// code you must include this routine, even if it's empty.
//
function append_sid($url, $non_html_amp = false)
{
	global $SID;

	if(!empty($SID) && !eregi("sid=", $url))
	{
		$url .= ( (strpos($url, "?") != false) ?  ( ( $non_html_amp ) ? "&" : "&amp;" ) : "?" ) . $SID;
	}

	return($url);
}

?>