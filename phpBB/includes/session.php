<?php
/***************************************************************************
 *                                session.php
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
	var $load;

	function start($update = true)
	{
		global $SID, $db, $board_config, $user_ip;
		global $HTTP_SERVER_VARS, $HTTP_ENV_VARS, $HTTP_COOKIE_VARS, $HTTP_GET_VARS;

		$current_time = time();
		$session_browser = ( !empty($HTTP_SERVER_VARS['HTTP_USER_AGENT']) ) ? $HTTP_SERVER_VARS['HTTP_USER_AGENT'] : $HTTP_ENV_VARS['HTTP_USER_AGENT'];
		$this_page = ( !empty($HTTP_SERVER_VARS['PHP_SELF']) ) ? $HTTP_SERVER_VARS['PHP_SELF'] : $HTTP_ENV_VARS['PHP_SELF'];
		$this_page .= '&' . ( ( !empty($HTTP_SERVER_VARS['QUERY_STRING']) ) ? $HTTP_SERVER_VARS['QUERY_STRING'] : $HTTP_ENV_VARS['QUERY_STRING'] );

		if ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid']) || isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_data']) )
		{
			$sessiondata = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_data']) ) ? unserialize(stripslashes($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_data'])) : '';
			$session_id = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid']) ) ? $HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid'] : '';
			$sessionmethod = SESSION_METHOD_COOKIE;
		}
		else
		{
			$sessiondata = '';
			$session_id = ( isset($HTTP_GET_VARS['sid']) ) ? $HTTP_GET_VARS['sid'] : '';
			$sessionmethod = SESSION_METHOD_GET;
		}

		//
		// Load limit check (if applicable)
		//
		if ( !empty($board_config['limit_load']) && file_exists('/proc/loadavg') )
		{
			if ( $load = @file('/proc/loadavg') )
			{
				list($this->load) = explode(' ', $load[0]);

				if ( $this->load > $board_config['limit_load'] )
				{
					message_die(MESSAGE, 'Board_unavailable');
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
			$result = $db->sql_query($sql);

			$this->userdata = $db->sql_fetchrow($result);

			//
			// Did the session exist in the DB?
			//
			if ( isset($this->userdata['user_id']) )
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
					$SID = '?sid=' . ( ( $sessionmethod == SESSION_METHOD_GET ) ? $session_id : '' );

					//
					// Only update session DB a minute or so after last update or if page changes
					//
					if ( ( $current_time - $this->userdata['session_time'] > 60 || $this->userdata['session_page'] != $this_page )  && $update )
					{
						$sql = "UPDATE " . SESSIONS_TABLE . " 
							SET session_time = $current_time, session_page = '$this_page' 
							WHERE session_id = '" . $this->userdata['session_id'] . "'";
						$db->sql_query($sql);

						//
						// Garbage collection ... remove old sessions updating user information
						// if necessary
						//
						if ( $current_time - $board_config['session_gc'] > $board_config['session_last_gc'] )
						{
							$this->gc($current_time);
						}
					}

					setcookie($board_config['cookie_name'] . '_data', serialize($sessiondata), $current_time + 31536000, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
					setcookie($board_config['cookie_name'] . '_sid', $session_id, 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);

					return $this->userdata;
				}
			}
		}

		//
		// If we reach here then no (valid) session exists. So we'll create a new one,
		// using the cookie user_id if available to pull basic user prefs.
		//
		$autologin = ( isset($sessiondata['autologinid']) ) ? $sessiondata['autologinid'] : '';
		$user_id = ( isset($sessiondata['userid']) ) ? intval($sessiondata['userid']) : ANONYMOUS;

		$this->userdata = $this->create($session_id, $user_id, $autologin, $this_page, $session_browser);

		return $this->userdata;
	}

	function create(&$session_id, &$user_id, &$autologin, &$this_page, &$session_browser)
	{
		global $SID, $db, $board_config, $user_ip;

		$sessiondata = array();
		$current_time = time();

		//
		// Limit connections (for MySQL) or 5 minute sessions (for other DB's)
		//
		switch ( SQL_LAYER )
		{
			case 'mysql': 
			case 'mysql4': 
				$sql = "SHOW PROCESSLIST";
				break;
			default: 
				$sql = "SELECT COUNT(*) AS sessions 
					FROM " . SESSIONS_TABLE . " 
					WHERE session_time >= " . ( $current_time - 3600 );
		}
		$result = $db->sql_query($sql);

		switch ( SQL_LAYER )
		{
			case 'mysql':
			case 'mysql4':
				$current_sessions = 0;
				while ( $db->sql_fetchrow($result) ) $current_sessions++;
				break;
			default:
				$row = $db->sql_fetchrow[$result];
				$current_sessions = ( isset($row['sessions']) ) ? $row['sessions'] : 0;
		}

		if ( intval($board_config['active_sessions']) && $current_sessions > intval($board_config['active_sessions']) )
		{
			message_die(GENERAL_MESSAGE, 'Board_unavailable', 'Information');
		}

		$sql = "SELECT * 
			FROM " . USERS_TABLE . " 
			WHERE user_id = $user_id";
		$result = $db->sql_query($sql);

		$this->userdata = $db->sql_fetchrow($result);

		//
		// Check autologin request, is it valid?
		//
		if ( $this->userdata['user_password'] != $autologin || !$this->userdata['user_active'] || $user_id == ANONYMOUS )
		{
			$autologin = '';
			$this->userdata['user_id'] = $user_id = ANONYMOUS; 
		}

		$user_ip_parts = explode('.', $user_ip);

		$sql = "SELECT ban_ip, ban_userid, ban_email 
			FROM " . BANLIST_TABLE . " 
			WHERE ban_ip IN (
				'" . $user_ip_parts[0] . ".', 
				'" . $user_ip_parts[0] . "." . $user_ip_parts[1] . ".',
				'" . $user_ip_parts[0] . "." . $user_ip_parts[1] . "." . $user_ip_parts[2] . ".', 
				'" . $user_ip_parts[0] . "." . $user_ip_parts[1] . "." . $user_ip_parts[2] . "." . $user_ip_parts[3] . "') 
			OR ban_userid = " . $this->userdata['user_id'];
		if ( $user_id != ANONYMOUS )
		{
			$sql .= " OR ban_email LIKE '" . str_replace('\\\'', '\\\'\\\'', $this->userdata['user_email']) . "' 
				OR ban_email LIKE '" . substr(str_replace('\\\'', '\\\'\\\'', $this->userdata['user_email']), strpos(str_replace('\\\'', '\\\'\\\'', $this->userdata['user_email']), '@')) . "'";
		}
		$result = $db->sql_query($sql);

		if ( $ban_info = $db->sql_fetchrow($result) )
		{
			if ( $ban_info['ban_ip'] || $ban_info['ban_userid'] || $ban_info['ban_email'] )
			{
				message_die(MESSAGE, 'You_been_banned');
			}
		}

		//
		// Create or update the session
		//
		$db->sql_return_on_error(true);

		$sql = "UPDATE " . SESSIONS_TABLE . "
			SET session_user_id = $user_id, session_start = $current_time, session_time = $current_time, session_browser = '$session_browser', session_page = '$this_page'
			WHERE session_id = '$session_id'";
		if ( !$db->sql_query($sql) || !$db->sql_affectedrows() )
		{
			$db->sql_return_on_error(false);
			$session_id = md5(uniqid($user_ip));

			$sql = "INSERT INTO " . SESSIONS_TABLE . "
				(session_id, session_user_id, session_start, session_time, session_ip, session_browser, session_page)
				VALUES ('$session_id', $user_id, $current_time, $current_time, '$user_ip', '$session_browser', '$this_page')";
			$db->sql_query($sql);
		}
		$db->sql_return_on_error(false);

		$SID = '?sid=' . $session_id;

		$sessiondata['autologinid'] = ( $autologin && $user_id != ANONYMOUS ) ? $autologin : '';
		$sessiondata['userid'] = $user_id;

		setcookie($board_config['cookie_name'] . '_data', serialize($sessiondata), $current_time + 31536000, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
		setcookie($board_config['cookie_name'] . '_sid', $session_id, 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);

		$this->userdata['session_id'] = $session_id;

		return $this->userdata;
	}

	function destroy(&$userdata)
	{
		global $SID, $db, $board_config, $user_ip;
		global $HTTP_SERVER_VARS, $HTTP_ENV_VARS, $HTTP_COOKIE_VARS, $HTTP_GET_VARS;

		if ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid']) )
		{
			$session_id = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid']) ) ? $HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid'] : '';
		}
		else
		{
			$session_id = ( isset($HTTP_GET_VARS['sid']) ) ? $HTTP_GET_VARS['sid'] : '';
		}

		$sessiondata = array();
		$current_time = time();

		//
		// Delete existing session, update last visit info first!
		//
		$sql = "UPDATE " . USERS_TABLE . " 
			SET user_lastvisit = " . $userdata['session_time'] . ", user_session_page = '" . $userdata['session_page'] . "' 
			WHERE user_id = " . $userdata['user_id'];
		$db->sql_query($sql);

		$sql = "DELETE FROM " . SESSIONS_TABLE . " 
			WHERE session_id = '" . $userdata['session_id'] . "' 
				AND session_user_id = " . $userdata['user_id'];
		$db->sql_query($sql);

		$SID = '?sid=';

		setcookie($board_config['cookie_name'] . '_data', '', $current_time - 31536000, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
		setcookie($board_config['cookie_name'] . '_sid', '', $current_time - 31536000, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);

		return true;
	}

	function gc(&$current_time)
	{
		global $db, $board_config, $user_ip;

		$sql = "SELECT * 
			FROM " . SESSIONS_TABLE . " 
			WHERE session_time < " . ( $current_time - $board_config['session_length'] );
		$result = $db->sql_query($sql);

		$del_session_id = '';
		while ( $row = $db->sql_fetchrow($result) )
		{
			if ( $row['user_id'] != ANONYMOUS )
			{
				$sql = "UPDATE " . USERS_TABLE . " 
					SET user_lastvisit = " . $row['session_time'] . ", user_session_page = '" . $row['session_page'] . "' 
					WHERE user_id = " . $row['session_user_id'];
				$db->sql_query($sql);
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
			$db->sql_query($sql);
		}

		$sql = "UPDATE " . CONFIG_TABLE . " 
			SET config_value = '$current_time' 
			WHERE config_name = 'session_last_gc'";
		$db->sql_query($sql);

		return;
	}

	function get_cookies()
	{
	}

	function set_cookies()
	{
	}

	function configure($userdata, $lang_set = false)
	{
		global $db, $template, $lang, $board_config, $theme, $images;
		global $phpEx, $phpbb_root_path;

		if ( $userdata['user_id'] != ANONYMOUS )
		{
			$board_config['default_lang'] = $userdata['user_lang'];
			$board_config['default_dateformat'] = $userdata['user_dateformat'];
			$board_config['board_timezone'] = $userdata['user_timezone'];
		}

		if ( !file_exists($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.' . $phpEx) )
		{
			$board_config['default_lang'] = 'english';
		}

		include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.' . $phpEx);

		if ( defined('IN_ADMIN') )
		{
			if ( !file_exists($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_admin.'.$phpEx) )
			{
				$board_config['default_lang'] = 'english';
			}

			include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_admin.' . $phpEx);
		}

		//
		// Set up style
		//
		$style = ( !$board_config['override_user_style'] && $userdata['user_id'] != ANONYMOUS ) ? $userdata['user_style'] : $board_config['default_style'];

		$sql = "SELECT t.template_path, t.poll_length, t.pm_box_length, c.css_data, c.css_external, i.* 
			FROM " . STYLES_TABLE . " s, " . STYLES_TPL_TABLE . " t, " . STYLES_CSS_TABLE . " c, " . STYLES_IMAGE_TABLE . " i 
			WHERE s.style_id = $style 
				AND t.template_id = s.template_id 
				AND c.theme_id = s.style_id 
				AND i.imageset_id = s.imageset_id";
		$result = $db->sql_query($sql);

		if ( !($theme = $db->sql_fetchrow($result)) )
		{
			message_die(ERROR, 'Could not get style data [ id ' . $style . ' ]');
		}

		if ( $template = new Template($theme['template_path']) )
		{
			$img_lang = ( file_exists('imageset/' . $theme['imageset_path'] . '/lang_' . $board_config['default_lang']) ) ? $board_config['default_lang'] : 'english';

			$i10n = array('post_new', 'post_locked', 'post_pm', 'reply_new', 'reply_pm', 'reply_locked', 'icon_quote', 'icon_edit', 'icon_search', 'icon_profile', 'icon_pm', 'icon_email', 'icon_www', 'icon_icq', 'icon_aim', 'icon_yim', 'icon_msnm', 'icon_delete', 'icon_ip', 'icon_no_email', 'icon_no_www', 'icon_no_icq', 'icon_no_aim', 'icon_no_yim', 'icon_no_msnm');

			for($i = 0; $i < sizeof($i10n); $i++)
			{
				$theme[$i10n[$i]] = str_replace('{LANG}', 'lang_' . $img_lang, $theme[$i10n[$i]]);
			}
		}

		return;
	}
}

//
// Note this doesn't use the prefetch at present and is very
// incomplete ... purely for testing ... will be keeping my
// eye of 'other products' to ensure these things don't
// mysteriously appear elsewhere, think up your own solutions!
//
class auth {

	var $acl;
	var $where_sql = '';

	function auth($mode, $userdata, $forum_id = false)
	{
		global $db;

		switch( $mode )
		{
			case 'list':
				$and_sql =  "AND ( ao.auth_option LIKE 'list' OR ao.auth_type LIKE 'admin' )";
				break;
			case 'forum':
				$and_sql =  "AND ( ( au.forum_id = $forum_id ) OR ( au.forum_id <> $forum_id AND ( ao.auth_option LIKE 'list' OR ao.auth_type LIKE 'mod' OR ao.auth_type LIKE 'admin' ) ) )";
				break;
			case 'admin':
				$and_sql =  "AND ( ao.auth_type LIKE 'admin' )";
				break;
			case 'listmod':
				$and_sql =  "AND ( ao.auth_option LIKE 'list' OR ao.auth_type LIKE 'mod' OR ao.auth_type LIKE 'admin' )";
				break;
			case 'all':
				$and_sql =  '';
				break;
		}

		$sql = "SELECT au.forum_id, au.auth_allow_deny, ao.auth_type, ao.auth_option  
			FROM " . ACL_PREFETCH_TABLE . " au, " . ACL_OPTIONS_TABLE . " ao  
			WHERE au.user_id = " . $userdata['user_id'] . " 
				AND ao.auth_option_id = au.auth_option_id 
				$and_sql";
		$result = $db->sql_query($sql);

		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$this->acl[$row['forum_id']][$row['auth_type']][$row['auth_option']] = $row['auth_allow_deny'];
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

		return;
	}

	function get_acl($forum_id = false, $auth_main = false, $auth_type = false)
	{
		if ( $auth_main && $auth_type )
		{
			return $this->acl[$forum_id][$auth_main][$auth_type];
		}
		else if ( !$auth_type && is_array($this->acl[$forum_id][$auth_main]) )
		{
			return ( array_sum($this->acl[$forum_id][$auth_main]) ) ? true : false;
		}

		return $this->acl[$forum_id];
	}

	function get_acl_admin($auth_type = false)
	{
		if ( $auth_type )
		{
			return $this->acl[0]['admin'][$auth_type];
		}
		else if ( !$auth_type && is_array($this->acl[0]['admin']) )
		{
			return ( array_sum($this->acl[0]['admin']) ) ? true : false;
		}

		return false;
	}

	function set_acl($ug_data, $forum_id, $auth_list = false, $dependencies = false)
	{
		global $db;

		$dependencies = array_merge($dependencies, array(
			'admin' => 'mod', 
			'mod' => 'forum')
		); 
	}
}

//
// Centralised login? May stay, may not ... depends if needed
//
function login($username, $password, $autologin = false)
{
	global $SID, $db, $board_config, $lang, $user_ip, $userdata;
	global $HTTP_SERVER_VARS, $HTTP_ENV_VARS;

	$this_page = ( !empty($HTTP_SERVER_VARS['PHP_SELF']) ) ? $HTTP_SERVER_VARS['PHP_SELF'] : $HTTP_ENV_VARS['PHP_SELF'];
	$this_page .= '&' . ( ( !empty($HTTP_SERVER_VARS['QUERY_STRING']) ) ? $HTTP_SERVER_VARS['QUERY_STRING'] : $HTTP_ENV_VARS['QUERY_STRING'] );
	$session_browser = ( !empty($HTTP_SERVER_VARS['HTTP_USER_AGENT']) ) ? $HTTP_SERVER_VARS['HTTP_USER_AGENT'] : $HTTP_ENV_VARS['HTTP_USER_AGENT'];

	$sql = "SELECT user_id, username, user_password, user_email, user_active, user_level 
		FROM " . USERS_TABLE . "
		WHERE username = '" . str_replace("\'", "''", $username) . "'";
	$result = $db->sql_query($sql);

	if ( $row = $db->sql_fetchrow($result) )
	{
		if ( $board_config['ldap_enable'] && extension_loaded('ldap') )
		{
			if ( !($ldap_id = @ldap_connect($board_config['ldap_hostname'])) )
			{
				@ldap_unbind($ldap_id);
			}
		}
		else
		{
			if ( md5($password) == $row['user_password'] && $row['user_active'] )
			{
				$autologin = ( isset($autologin) ) ? md5($password) : '';
				$userdata = $session->create($session_id, $user_id, $autologin, $this_page, $session_browser);
			}
		}
	}

	return $result;
}

?>