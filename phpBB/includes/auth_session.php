<?php
/***************************************************************************
 *                                sessions.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2002 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
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

class session {

	var $userdata;

	function start($update = true)
	{
		global $db, $board_config, $user_ip;
		global $HTTP_SERVER_VARS, $HTTP_ENV_VARS, $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $SID;

		$current_time = time();
		$session_browser = ( !empty($HTTP_SERVER_VARS['HTTP_USER_AGENT']) ) ? $HTTP_SERVER_VARS['HTTP_USER_AGENT'] : $HTTP_ENV_VARS['HTTP_USER_AGENT'];

		if ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid']) || isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_data']) )
		{
			$sessiondata = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_data']) ) ? unserialize(stripslashes($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_data'])) : '';
			$session_id = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid']) ) ? $HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid'] : '';
			$sessionmethod = SESSION_METHOD_COOKIE;
		}
		else
		{
			$session_data = '';
			$session_id = ( isset($HTTP_GET_VARS['sid']) ) ? $HTTP_GET_VARS['sid'] : '';
			$sessionmethod = SESSION_METHOD_GET;
		}

		//
		// Load limit check (if applicable)
		//
		if ( !empty($board_config['limit_load']) && file_exists('/proc/loadavg') )
		{
			if ( $load = file('/proc/loadvg') )
			{
				$load = explode(' ', $load[0]);

				if ( intval($load[0]) > $board_config['limit_load'] )
				{
					message_die(GENERAL_MESSAGE, 'Board_unavailable', 'Information');
				}
			}
		}

		if ( !empty($session_id) )
		{
			//
			// session_id exists so go ahead and attempt to grab all data in preparation
			//
			$sql = "SELECT u.*, s.*
				FROM " . SESSIONS_TABLE . " s, " . USERS_TABLE . " u
				WHERE s.session_id = '$session_id'
					AND u.user_id = s.session_user_id";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(CRITICAL_ERROR, 'Error doing DB query userdata row fetch', '', __LINE__, __FILE__, $sql);
			}

			$this->userdata = $db->sql_fetchrow($result);

			//
			// Did the session exist in the DB?
			//
			if ( $update && isset($this->userdata['user_id']) )
			{
				//
				// Do not check IP assuming equivalence, if IPv4 we'll check only first 24
				// bits ... I've been told (by vHiker) this should alleviate problems with 
				// load balanced et al proxies while retaining some reliance on IP security.
				//
				$ip_check_s = explode('.', $this->userdata['session_ip']);
				$ip_check_u = explode('.', $user_ip);

				if ( $ip_check_s[0].'.'.$ip_check_s[1].'.'.$ip_check_s[2] == $ip_check_u[0].'.'.$ip_check_u[1].'.'.$ip_check_u[2] )
				{
					$SID .= '?sid=' . ( ( $sessionmethod == SESSION_METHOD_GET ) ? $session_id : '' );

					//
					// Only update session DB a minute or so after last update or if page changes
					//
					if ( $current_time - $this->userdata['session_time'] > 60 || $this->userdata['session_page'] != $thispage_id )
					{
						$this_page = ( !empty($HTTP_SERVER_VARS['PHP_SELF']) ) ? $HTTP_SERVER_VARS['PHP_SELF'] : $HTTP_ENV_VARS['PHP_SELF'];
						$this_query = ( !empty($HTTP_SERVER_VARS['QUERY_STRING']) ) ? explode('&', $HTTP_SERVER_VARS['QUERY_STRING']) : explode('&', $HTTP_ENV_VARS['QUERY_STRING']);
						array_shift($this_query);

						$sql = "UPDATE " . SESSIONS_TABLE . " 
							SET session_time = $current_time, session_page = '" . $this_page . "#" . implode('#', $this_query) . "' 
							WHERE session_id = '" . $this->userdata['session_id'] . "'";
						if ( !$db->sql_query($sql) )
						{
							message_die(CRITICAL_ERROR, 'Error updating sessions table', '', __LINE__, __FILE__, $sql);
						}

						//
						// Garbage collection ... remove old sessions updating user information
						// if necessary
						//
						if ( $current_time - $board_config['session_gc'] > $board_config['session_last_gc'] )
						{
							$this->gc($current_time);
						}

						setcookie($board_config['cookie_name'] . '_data', serialize($sessiondata), $current_time + 31536000, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
						setcookie($board_config['cookie_name'] . '_sid', $session_id, 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
					}

					$this->config();

					return $this->userdata;
				}
			}
		}

		//
		// If we reach here then no (valid) session exists. So we'll create a new one,
		// using the cookie user_id if available to pull basic user prefs.
		//
		$user_id = ( isset($sessiondata['userid']) ) ? $sessiondata['userid'] : ANONYMOUS;
		$autologin = ( isset($HTTP_POST_VARS['autologin']) ) ? $password : '';

		if ( !($this->userdata = $this->create($user_id, $autologin)) )
		{
			message_die(CRITICAL_ERROR, 'Error creating user session', '', __LINE__, __FILE__, $sql);
		}

		return $this->userdata;
	}

	function create($user_id, $user_password)
	{
		global $db, $board_config, $user_ip;
		global $HTTP_SERVER_VARS, $HTTP_ENV_VARS, $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $SID;

		if ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid']) || isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_data']) )
		{
			$sessiondata = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_data']) ) ? unserialize(stripslashes($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_data'])) : '';
			$session_id = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid']) ) ? $HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid'] : '';
			$sessionmethod = SESSION_METHOD_COOKIE;
		}
		else
		{
			$session_data = '';
			$session_id = ( isset($HTTP_GET_VARS['sid']) ) ? $HTTP_GET_VARS['sid'] : '';
			$sessionmethod = SESSION_METHOD_GET;
		}
		
		$current_time = time();
		$session_browser = ( !empty($HTTP_SERVER_VARS['HTTP_USER_AGENT']) ) ? $HTTP_SERVER_VARS['HTTP_USER_AGENT'] : $HTTP_ENV_VARS['HTTP_USER_AGENT'];
		$this_page = ( !empty($HTTP_SERVER_VARS['PHP_SELF']) ) ? $HTTP_SERVER_VARS['PHP_SELF'] : $HTTP_ENV_VARS['PHP_SELF'];
		$this_query = ( !empty($HTTP_SERVER_VARS['QUERY_STRING']) ) ? explode('&', $HTTP_SERVER_VARS['QUERY_STRING']) : explode('&', $HTTP_ENV_VARS['QUERY_STRING']);
		array_shift($this_query);

		$sql = "SELECT * 
			FROM " . USERS_TABLE . " 
			WHERE user_id = $user_id";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(CRITICAL_ERROR, 'Could not obtain lastvisit data from user table', '', __LINE__, __FILE__, $sql);
		}

		$this->userdata = $db->sql_fetchrow($result);

		$user_ip_parts = explode('.', $user_ip);

		$sql = "SELECT ban_ip, ban_userid, ban_email 
			FROM " . BANLIST_TABLE . " 
			WHERE ban_ip IN (
					'" . $user_ip_parts[1] . ".', 
					'" . $user_ip_parts[1] . "." . $user_ip_parts[2] . ".',
					'" . $user_ip_parts[1] . "." . $user_ip_parts[2] . "." . $user_ip_parts[3] . ".', 
					'" . $user_ip_parts[1] . "." . $user_ip_parts[2] . "." . $user_ip_parts[3] . "." . $user_ip_parts[4] . "') 
				OR ban_userid = $user_id";
		if ( $user_id != ANONYMOUS )
		{
			$sql .= " OR ban_email LIKE '" . str_replace('\\\'', '\\\'\\\'', $this->userdata['user_email']) . "' 
				OR ban_email LIKE '" . substr(str_replace('\\\'', '\\\'\\\'', $this->userdata['user_email']), strpos(str_replace('\\\'', '\\\'\\\'', $this->userdata['user_email']), '@')) . "'";
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
		// Limit 5 minute sessions
		//
		$sql = "SELECT COUNT(*) AS sessions 
			FROM " . SESSIONS_TABLE . " 
			WHERE session_time >= " . ( $current_time - 3600 );
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(CRITICAL_ERROR, 'Could not obtain ban information', '', __LINE__, __FILE__, $sql);
		}
		
		$row = $db->sql_fetchrow[$result];

		if ( $row['sessions'] >= $board_config['active_sessions'] )
		{
			message_die(GENERAL_MESSAGE, 'Board_unavailable', 'Information');
		}

		//
		// Create or update the session
		//
		$sql = "UPDATE " . SESSIONS_TABLE . "
			SET session_user_id = $user_id, session_start = $current_time, session_time = $current_time, session_browser = '$session_browser', session_page = '" . $this_page . "#" . implode('#', $this_query) . "'
			WHERE session_id = '" . $session_id . "'";
		if ( !$db->sql_query($sql) || !$db->sql_affectedrows() )
		{
			$session_id = md5(uniqid($user_ip));

			$sql = "INSERT INTO " . SESSIONS_TABLE . "
				(session_id, session_user_id, session_start, session_time, session_ip, session_browser, session_page)
				VALUES ('$session_id', $user_id, $current_time, $current_time, '$user_ip', '$session_browser', '" . $this_page . "#" . implode('#', $this_query) . "')";
			if ( !$db->sql_query($sql) )
			{
				message_die(CRITICAL_ERROR, 'Error creating new session', '', __LINE__, __FILE__, $sql);
			}
		}

		$SID .= '?sid=' . ( ( $sessionmethod == SESSION_METHOD_GET ) ? $session_id : '' );

		$sessiondata['autologinid'] = ( $user_password && $sessionmethod == SESSION_METHOD_COOKIE ) ? $user_password : '';
		$sessiondata['userid'] = $user_id;

		setcookie($board_config['cookie_name'] . '_data', serialize($sessiondata), $current_time + 31536000, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
		setcookie($board_config['cookie_name'] . '_sid', $session_id, 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);

		$this->userdata['session_id'] = $session_id;

		return $this->userdata;
	}

	function gc($current_time)
	{
		global $db, $board_config, $user_ip;

		$sql = "SELECT * 
			FROM " . SESSIONS_TABLE . " 
			WHERE session_time < " . ( $current_time - $board_config['session_length'] );
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(CRITICAL_ERROR, 'Could not obtain expired session list', '', __LINE__, __FILE__, $sql);
		}

		$del_session_id = '';
		while ( $row = $db->sql_fetchrow($result) )
		{
			if ( $row['session_logged_in'] )
			{
				$sql = "UPDATE " . USERS_TABLE . " 
					SET user_lastvisit = " . $row['session_time'] . ", user_session_page = " . $row['session_page'] . " 
					WHERE user_id = " . $row['session_user_id'];
				if ( !$db->sql_query($sql) )
				{
					message_die(CRITICAL_ERROR, 'Could not update user session info', '', __LINE__, __FILE__, $sql);
				}
			}

			$del_session_id .= ( ( $del_session_id != '' ) ? ', ' : '' ) . '\'' . $row['session_id'] . '\'';
		}

		if ( $del_session_id != '' )
		{
			//
			// Delete expired sessions
			//
			$sql = "DELETE FROM " . SESSIONS_TABLE . " 
				WHERE session_id IN ($del_session_id)";
			if ( !$db->sql_query($sql) )
			{
				message_die(CRITICAL_ERROR, 'Error clearing sessions table', '', __LINE__, __FILE__, $sql);
			}
		}

		$sql = "UPDATE " . CONFIG_TABLE . " 
			SET config_value = '$current_time' 
			WHERE config_name = 'session_last_gc'";
		if ( !$db->sql_query($sql) )
		{
			message_die(CRITICAL_ERROR, 'Could not update session gc time', '', __LINE__, __FILE__, $sql);
		}

		return;
	}

	function destroy($userdata)
	{
		global $db, $board_config, $user_ip;
		global $HTTP_SERVER_VARS, $HTTP_ENV_VARS, $HTTP_COOKIE_VARS, $HTTP_GET_VARS, $SID;

		if ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid']) )
		{
			$session_id = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid']) ) ? $HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid'] : '';
		}
		else
		{
			$session_id = ( isset($HTTP_GET_VARS['sid']) ) ? $HTTP_GET_VARS['sid'] : '';
		}

		//
		// Delete existing session, update last visit info first!
		//
		$sql = "UPDATE " . USERS_TABLE . " 
			SET user_lastvisit = " . $userdata['session_time'] . ", user_session_page = " . $userdata['session_page'] . " 
			WHERE user_id = " . $userdata['user_id'];
		if ( !$db->sql_query($sql) )
		{
			message_die(CRITICAL_ERROR, 'Could not update user session info', '', __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . SESSIONS_TABLE . " 
			WHERE session_id = '" . $userdata['session_id'] . "' 
				AND session_user_id = " . $userdata['user_id'];
		if ( !$db->sql_query($sql) )
		{
			message_die(CRITICAL_ERROR, 'Error removing user session', '', __LINE__, __FILE__, $sql);
		}

		$SID .= '?sid=';

		setcookie($board_config['cookie_name'] . '_data', '', $current_time - 31536000, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
		setcookie($board_config['cookie_name'] . '_sid', '', $current_time - 31536000, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);

		return true;
	}

	function config()
	{
		global $board_config, $theme, $images, $user_ip;
		global $template, $lang, $phpEx, $phpbb_root_path;

		if ( $this->userdata['user_id'] != ANONYMOUS )
		{
			if ( !empty($this->userdata['user_lang']))
			{
				$board_config['default_lang'] = $this->userdata['user_lang'];
			}

			if ( !empty($this->userdata['user_dateformat']) )
			{
				$board_config['default_dateformat'] = $this->userdata['user_dateformat'];
			}

			if ( isset($this->userdata['user_timezone']) )
			{
				$board_config['board_timezone'] = $this->userdata['user_timezone'];
			}
		}

		if ( !file_exists($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.'.$phpEx) )
		{
			$board_config['default_lang'] = 'english';
		}

		include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.' . $phpEx);

		if ( defined('IN_ADMIN') )
		{
			if( !file_exists($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_admin.'.$phpEx) )
			{
				$board_config['default_lang'] = 'english';
			}

			include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_admin.' . $phpEx);
		}

		//
		// Set up style
		//
		if ( !$board_config['override_user_style'] )
		{
			if ( $this->userdata['user_id'] != ANONYMOUS && $this->userdata['user_style'] > 0 )
			{
				if ( $theme = setup_style($this->userdata['user_style']) )
				{
					return;
				}
			}
		}

		$theme = setup_style($board_config['default_style']);

		return;
	}
}

//
// Note this doesn't use the prefetch at present
//
class auth {

	var $acl;

	function auth($userdata)
	{
		global $db;

		$sql = "SELECT ag.forum_id, ag.auth_allow_deny, ao.auth_option  
			FROM phpbb_user_group ug, phpbb_auth_groups ag, phpbb_auth_options ao  
			WHERE ug.user_id = " . $userdata['user_id'] . " 
				AND ag.group_id = ug.group_id 
				AND ao.auth_option_id = ag.auth_option_id";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Failed obtaining forum access control lists', '', __LINE__, __FILE__, $sql);
		}

		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				list($option_main, $option_type) = explode('_', $row['auth_option']);
				$this->acl[$row['forum_id']][$option_main][$option_type] = $row['auth_allow_deny'];
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

		$sql = "SELECT au.forum_id, au.auth_allow_deny, ao.auth_option  
			FROM phpbb_auth_users au, phpbb_auth_options ao  
			WHERE au.user_id = " . $userdata['user_id'] . " 
				AND ao.auth_option_id = au.auth_option_id";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Failed obtaining forum access control lists', '', __LINE__, __FILE__, $sql);
		}

		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				list($option_main, $option_type) = explode('_', $row['auth_option']);
				$this->acl[$row['forum_id']][$option_main][$option_type] = ( isset($this->acl[$row['forum_id']][$option_main][$option_type]) ) ? $this->acl[$row['forum_id']][$option_main][$option_type] && $row['auth_allow_deny'] : $row['auth_allow_deny'];
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

		return;
	}

	function get_acl($forum_id = false, $auth_main = false, $auth_type = false)
	{
		if ( !$forum_id )
		{
			return $this->acl;
		}
		else if ( $auth_main && $auth_type )
		{
			return $this->acl[$forum_id][$auth_main][$auth_type];
		}
		else if ( !$auth_type && is_array($this->acl[$forum_id][$auth_main]) )
		{
			@reset($this->acl);
			while ( list(, $value) = @each($this->acl[$forum_id][$auth_main]) )
			{
				if ( $value )
				{
					return true;
				}
			}
			return false;
		}
		else if ( !$auth_main && is_array($this->acl[$forum_id]) )
		{
			@reset($this->acl);
			while ( list(, $value) = each($this->acl[$forum_id]) )
			{
				if ( $value[$auth_type] )
				{
					return true;
				}
			}
			return false;
		}
		else
		{
			return $this->acl[$forum_id];
		}
	}

	function set_acl($ug_data, $forum_id = false, $auth_list = false, $dependencies = false)
	{
		global $db;

		$dependencies = array_merge($dependencies, array(
			'admin' => 'mod', 
			'mod' => 'forum')
		); 
	}
}

//
// Append $SID to a url. Borrowed from phplib and modified. 6
//
// This routine is doomed I think, instead we just set a URL$SID for
// appropriate URLs rather than this append stuff. For the time being
// this change will break URL based session propagation
//
function append_sid($url, $non_html_amp = false)
{
	return $url;
}

?>