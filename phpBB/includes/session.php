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

	var $session_id = '';
	var $browser = '';
	var $page = '';
	var $load;

	// Called at each page start ... checks for, updates and/or creates a session
	function start($update = true)
	{
		global $SID, $db, $board_config, $user_ip;

		$current_time = time();
		$this->browser = ( !empty($_SERVER['HTTP_USER_AGENT']) ) ? $_SERVER['HTTP_USER_AGENT'] : $_ENV['HTTP_USER_AGENT'];
		$this->page = ( !empty($_SERVER['PHP_SELF']) ) ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF'];
		$this->page .= '&' . ( ( !empty($_SERVER['QUERY_STRING']) ) ? $_SERVER['QUERY_STRING'] : $_ENV['QUERY_STRING'] );

		if ( isset($_COOKIE[$board_config['cookie_name'] . '_sid']) || isset($_COOKIE[$board_config['cookie_name'] . '_data']) )
		{
			$sessiondata = ( isset($_COOKIE[$board_config['cookie_name'] . '_data']) ) ? unserialize(stripslashes($_COOKIE[$board_config['cookie_name'] . '_data'])) : '';
			$this->session_id = ( isset($_COOKIE[$board_config['cookie_name'] . '_sid']) ) ? $_COOKIE[$board_config['cookie_name'] . '_sid'] : '';
			$SID = '?sid=';
		}
		else
		{
			$sessiondata = '';
			$this->session_id = ( isset($_GET['sid']) ) ? $_GET['sid'] : '';
			$SID = '?sid=' . $this->session_id;
		}

		// Load limit check (if applicable)
		if ( $board_config['limit_load'] && file_exists('/proc/loadavg') )
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

		// session_id exists so go ahead and attempt to grab all data in preparation
		if ( !empty($this->session_id) )
		{
			$sql = "SELECT u.*, s.*
				FROM " . SESSIONS_TABLE . " s, " . USERS_TABLE . " u
				WHERE s.session_id = '" . $this->session_id . "'
					AND u.user_id = s.session_user_id";
			$result = $db->sql_query($sql);

			$userdata = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			// Did the session exist in the DB?
			if ( isset($userdata['user_id']) )
			{
				// Validate IP length according to admin ... has no effect on IPv6
				$s_ip = implode('.', array_slice(explode('.', $userdata['session_ip']), 0, $board_config['ip_check']));
				$u_ip = implode('.', array_slice(explode('.', $user_ip), 0, $board_config['ip_check']));

				if ( $u_ip == $s_ip )
				{
					// Only update session DB a minute or so after last update or if page changes
					if ( ( $current_time - $userdata['session_time'] > 60 || $userdata['session_page'] != $user_page ) && $update )
					{
						$sql = "UPDATE " . SESSIONS_TABLE . "
							SET session_time = $current_time, session_page = '$this->page'
							WHERE session_id = '" . $this->session_id . "'";
						$db->sql_query($sql);
					}

					return $userdata;
				}
			}
		}

		// If we reach here then no (valid) session exists. So we'll create a new one,
		// using the cookie user_id if available to pull basic user prefs.
		$autologin = ( isset($sessiondata['autologinid']) ) ? $sessiondata['autologinid'] : '';
		$user_id = ( isset($sessiondata['userid']) ) ? intval($sessiondata['userid']) : ANONYMOUS;

		return $this->create($user_id, $autologin);
	}

	// Create a new session
	function create(&$user_id, &$autologin)
	{
		global $SID, $db, $board_config, $user_ip;

		$sessiondata = array();
		$current_time = time();

		// Limit sessions in 1 minute period
		$sql = "SELECT COUNT(*) AS sessions
			FROM " . SESSIONS_TABLE . "
			WHERE session_time >= " . ( $current_time - 60 );
		$result = $db->sql_query($sql);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ( intval($board_config['active_sessions']) && intval($row['sessions']) > intval($board_config['active_sessions']) )
		{
			message_die(MESSAGE, 'Board_unavailable');
		}

		// Garbage collection ... remove old sessions updating user information
		// if necessary. It means (potentially) 22 queries but only infrequently
		if ( $current_time - $board_config['session_gc'] > $board_config['session_last_gc'] )
		{
			$this->gc($current_time);
		}

		// Grab user data ... join on session if it exists for session time
		$sql = "SELECT u.*, s.session_time
			FROM ( " . USERS_TABLE . " u
			LEFT JOIN " . SESSIONS_TABLE . " s ON s.session_user_id = u.user_id )
			WHERE u.user_id = $user_id
			ORDER BY s.session_time DESC";
		$result = $db->sql_query($sql);

		$userdata = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// Check autologin request, is it valid?
		if ( $userdata['user_password'] != $autologin || !$userdata['user_active'] || !$user_id )
		{
			$autologin = '';
			$userdata['user_id'] = $user_id = ANONYMOUS;
		}

		$sql = "SELECT ban_ip, ban_userid, ban_email
			FROM " . BANLIST_TABLE . "
			WHERE ban_end >= $current_time
				OR ban_end = 0";
		$result = $db->sql_query($sql);

		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				if ( ( $row['user_id'] == $userdata['user_id'] ||
					( $row['ban_ip'] && preg_match('#^' . str_replace('*', '.*?', $row['ban_ip']) . '$#i', $user_ip) ) ||
					( $row['ban_email'] && preg_match('#^' . str_replace('*', '.*?', $row['ban_email']) . '$#i', $userdata['user_email']) ) )
					&& !$userdata['user_founder'] )
				{
					message_die(MESSAGE, 'You_been_banned');
				}
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

		// Is there an existing session? If so, grab last visit time from that
		$userdata['session_last_visit'] = ( $userdata['session_time'] ) ? $userdata['session_time'] : ( ( $userdata['user_lastvisit'] ) ? $userdata['user_lastvisit'] : time() );

		// Create or update the session
		$db->sql_return_on_error(true);

		$sql = "UPDATE " . SESSIONS_TABLE . "
			SET session_user_id = $user_id, session_last_visit = " . $userdata['session_last_visit'] . ", session_start = $current_time, session_time = $current_time, session_browser = '$this->browser', session_page = '$this->page'
			WHERE session_id = '" . $this->session_id . "'";
		if ( !$db->sql_query($sql) || !$db->sql_affectedrows() )
		{
			$db->sql_return_on_error(false);
			$this->session_id = md5(uniqid($user_ip));

			$sql = "INSERT INTO " . SESSIONS_TABLE . "
				(session_id, session_user_id, session_last_visit, session_start, session_time, session_ip, session_browser, session_page)
				VALUES ('" . $this->session_id . "', $user_id, " . $userdata['session_last_visit'] . ", $current_time, $current_time, '$user_ip', '$this->browser', '$this->page')";
			$db->sql_query($sql);
		}
		$db->sql_return_on_error(false);

		$userdata['session_id'] = $this->session_id;

		$sessiondata['autologinid'] = ( $autologin && $user_id ) ? $autologin : '';
		$sessiondata['userid'] = $user_id;

		$this->set_cookie('data', serialize($sessiondata), $current_time + 31536000);
		$this->set_cookie('sid', $this->session_id, 0);
		$SID = '?sid=' . $this->session_id;

		// Events ...
		if ( $userdata['user_id'] )
		{
//			do_events();
		}

		return $userdata;
	}

	// Destroy a session
	function destroy(&$userdata)
	{
		global $SID, $db, $board_config;

		$current_time = time();

		$this->set_cookie('data', '', $current_time - 31536000);
		$this->set_cookie('sid', '', $current_time - 31536000);
		$SID = '?sid=';

		// Delete existing session, update last visit info first!
		$sql = "UPDATE " . USERS_TABLE . "
			SET user_lastvisit = " . intval($userdata['session_time']) . "
			WHERE user_id = " . $userdata['user_id'];
		$db->sql_query($sql);

		$sql = "DELETE FROM " . SESSIONS_TABLE . "
			WHERE session_id = '" . $this->session_id . "'
				AND session_user_id = " . $userdata['user_id'];
		$db->sql_query($sql);

		$this->session_id = '';

		return true;
	}

	// Garbage collection
	function gc(&$current_time)
	{
		global $db, $board_config, $user_ip;

		// Get expired sessions, only most recent for each user
		$sql = "SELECT session_user_id, MAX(session_time) AS recent_time
			FROM " . SESSIONS_TABLE . "
			WHERE session_time < " . ( $current_time - $board_config['session_length'] ) . "
			GROUP BY session_user_id
			LIMIT 10";
		$result = $db->sql_query($sql);

		$del_user_id = '';
		$del_sessions = 0;
		while ( $row = $db->sql_fetchrow($result) )
		{
			if ( $row['session_user_id'] )
			{
				$sql = "UPDATE " . USERS_TABLE . "
					SET user_lastvisit = " . $row['recent_time'] . "
					WHERE user_id = " . $row['session_user_id'];
				$db->sql_query($sql);
			}

			$del_user_id .= ( ( $del_user_id != '' ) ? ', ' : '' ) . ' \'' . $row['session_user_id'] . '\'';
			$del_sessions++;
		}

		if ( $del_user_id != '' )
		{
			// Delete expired sessions
			$sql = "DELETE FROM " . SESSIONS_TABLE . "
				WHERE session_user_id IN ($del_user_id)
					AND session_time < " . ( $current_time - $board_config['session_length'] );
			$db->sql_query($sql);
		}

		if ( $del_sessions < 10 )
		{
			// Less than 10 sessions, update gc timer ... else we want gc
			// called again to delete other sessions
			$sql = "UPDATE " . CONFIG_TABLE . "
				SET config_value = '$current_time'
				WHERE config_name = 'session_last_gc'";
			$db->sql_query($sql);
		}

		return;
	}

	// Set a cookie
	function set_cookie($name, $cookiedata, $cookietime)
	{
		global $board_config;

		setcookie($board_config['cookie_name'] . '_' . $name, $cookiedata, $cookietime, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
	}

	// Taken over by user class ... for now at least
	function configure($userdata, $lang_set = false)
	{
		global $db, $template, $lang, $board_config, $theme, $images;
		global $phpEx, $phpbb_root_path;

		if ( $userdata['user_id'] )
		{
			$board_config['default_lang'] = ( file_exists($phpbb_root_path . 'language/lang_' . $userdata['user_lang']) ) ? $userdata['user_lang'] : $board_config['default_lang'];
			$board_config['default_dateformat'] = $userdata['user_dateformat'];
			$board_config['board_timezone'] = $userdata['user_timezone'];
		}

		include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.' . $phpEx);
		if ( defined('IN_ADMIN') )
		{
			include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_admin.' . $phpEx);
		}

		// Set up style
		$style = ( !$board_config['override_user_style'] && $userdata['user_id'] ) ? $userdata['user_style'] : $board_config['default_style'];

		$sql = "SELECT t.template_path, t.poll_length, t.pm_box_length, c.css_data, c.css_external, i.*
			FROM " . STYLES_TABLE . " s, " . STYLES_TPL_TABLE . " t, " . STYLES_CSS_TABLE . " c, " . STYLES_IMAGE_TABLE . " i
			WHERE s.style_id = $style
				AND t.template_id = s.template_id
				AND c.theme_id = s.style_id
				AND i.imageset_id = s.imageset_id";
		$result = $db->sql_query($sql);

		if ( !($theme = $db->sql_fetchrow($result)) )
		{
			message_die(ERROR, 'Could not get style data');
		}

		$template->set_template($theme['template_path']);

		$img_lang = ( file_exists('imageset/' . $theme['imageset_path'] . '/lang_' . $board_config['default_lang']) ) ? $board_config['default_lang'] : 'english';

		$i10n = array('post_new', 'post_locked', 'post_pm', 'reply_new', 'reply_pm', 'reply_locked', 'icon_quote', 'icon_edit', 'icon_search', 'icon_profile', 'icon_pm', 'icon_email', 'icon_www', 'icon_icq', 'icon_aim', 'icon_yim', 'icon_msnm', 'icon_delete', 'icon_ip', 'icon_no_email', 'icon_no_www', 'icon_no_icq', 'icon_no_aim', 'icon_no_yim', 'icon_no_msnm');

		foreach ( $i10n as $icon )
		{
			$theme[$icon] = str_replace('{LANG}', 'lang_' . $img_lang, $theme[$icon]);
		}

		return;
	}
}

// Contains (at present) basic user methods such as configuration
// creating date/time ... keep this?
class user
{
	var $lang_name;
	var $lang_path;

	var $date_format;
	var $timezone;
	var $dst;

	function user(&$userdata, $lang_set = false, $style = false)
	{
		global $db, $template, $lang, $board_config, $theme, $images;
		global $phpEx, $phpbb_root_path;

		if ( $userdata['user_id'] )
		{
			$this->lang_name = ( file_exists($phpbb_root_path . 'language/' . $userdata['user_lang']) ) ? $userdata['user_lang'] : $board_config['default_lang'];
			$this->lang_path = $phpbb_root_path . 'language/' . $this->lang_name;

			$this->date_format = $userdata['user_dateformat'];
			$this->timezone = $userdata['user_timezone'];
			$this->dst = $userdata['user_dst'] * 3600;
		}
		else if ( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) )
		{
			$accept_lang_ary = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			foreach ( $accept_lang_ary as $accept_lang )
			{
				// Set correct format ... guess full xx_YY form
				$accept_lang = substr($accept_lang, 0, 2) . '_' . strtoupper(substr($accept_lang, 3, 2));
				if ( file_exists($phpbb_root_path . 'language/' . $accept_lang) )
				{
					$this->lang_name = $accept_lang;
					$this->lang_path = $phpbb_root_path . 'language/' . $accept_lang;
					break;
				}
				else
				{
					// No match on xx_YY so try xx
					$accept_lang = substr($accept_lang, 0, 2);
					if ( file_exists($phpbb_root_path . 'language/' . $accept_lang) )
					{
						$this->lang_name = $accept_lang;
						$this->lang_path = $phpbb_root_path . 'language/' . $accept_lang;
						break;
					}
				}
			}

			$this->date_format = $board_config['default_dateformat'];
			$this->timezone = $board_config['board_timezone'];
			$this->dst = 0;
		}

		include($this->lang_path . '/lang_main.' . $phpEx);
		if ( defined('IN_ADMIN') )
		{
			include($this->lang_path . '/lang_admin.' . $phpEx);
		}
/*
		if ( is_array($lang_set) )
		{
			include($this->lang_path . '/common.' . $phpEx);

			$lang_set = explode(',', $lang_set);
			foreach ( $lang_set as $lang_file )
			{
				include($this->lang_path . '/' . trim($lang_file) . '.' . $phpEx);
			}
			unset($lang_set);
		}
		else
		{
			include($this->lang_path . '/common.' . $phpEx);
			include($this->lang_path . '/' . trim($lang_set) . '.' . $phpEx);
		}
*/
		// Set up style
		$style = ( $style ) ? $style : ( ( !$board_config['override_user_style'] && $userdata['user_id'] ) ? $userdata['user_style'] : $board_config['default_style'] );

		$sql = "SELECT t.template_path, t.poll_length, t.pm_box_length, c.css_data, c.css_external, i.*
			FROM " . STYLES_TABLE . " s, " . STYLES_TPL_TABLE . " t, " . STYLES_CSS_TABLE . " c, " . STYLES_IMAGE_TABLE . " i
			WHERE s.style_id = $style
				AND t.template_id = s.template_id
				AND c.theme_id = s.style_id
				AND i.imageset_id = s.imageset_id";
		$result = $db->sql_query($sql);

		if ( !($theme = $db->sql_fetchrow($result)) )
		{
			message_die(ERROR, 'Could not get style data');
		}

		$template->set_template($theme['template_path']);

		$img_lang = ( file_exists('imageset/' . $theme['imageset_path'] . '/' . $this->lang_name) ) ? $this->lang_name : $board_config['default_lang'];

		$i10n = array('post_new', 'post_locked', 'post_pm', 'reply_new', 'reply_pm', 'reply_locked', 'icon_quote', 'icon_edit', 'icon_search', 'icon_profile', 'icon_pm', 'icon_email', 'icon_www', 'icon_icq', 'icon_aim', 'icon_yim', 'icon_msnm', 'icon_delete', 'icon_ip', 'icon_no_email', 'icon_no_www', 'icon_no_icq', 'icon_no_aim', 'icon_no_yim', 'icon_no_msnm');

		foreach ( $i10n as $icon )
		{
			$theme[$icon] = str_replace('{LANG}', $img_lang, $theme[$icon]);
		}

		return;
	}

	function format_date($gmepoch)
	{
		global $lang;
		static $lang_dates;

		if ( empty($lang_dates) )
		{
			foreach ( $lang['datetime'] as $match => $replace )
			{
				$lang_dates[$match] = $replace;
			}
		}

		return strtr(@gmdate($this->date_format, $gmepoch + (3600 * $this->timezone) + $this->dst), $lang_dates);
	}
}

// Will be keeping my eye of 'other products' to ensure these things don't
// mysteriously appear elsewhere, think up your own solutions!
class auth
{
	var $founder = false;
	var $acl = false;

	function acl(&$userdata, $forum_id = false, $extra_options = false)
	{
		global $db;

		if ( !($this->founder = $userdata['user_founder']) )
		{
			$and_sql = "ao.auth_value LIKE 'forum_list'";

			if ( $extra_options )
			{
				$tmp_ary = explode(',', $extra_options);
				foreach ( $tmp_ary as $option )
				{
					$and_sql .= " OR ao.auth_value LIKE '" . trim($option) . "'";
				}
			}

			$and_sql = ( !$forum_id ) ? $and_sql : "( a.forum_id = $forum_id ) OR ( a.forum_id <> $forum_id AND ( ao.auth_value LIKE 'forum_list' OR ao.auth_value LIKE 'mod_%' ) )";
			$and_sql .= " OR ao.auth_value LIKE 'admin_%'";

			$sql = "SELECT a.forum_id, a.auth_allow_deny, ao.auth_value
				FROM " . ACL_GROUPS_TABLE . " a, " . ACL_OPTIONS_TABLE . " ao, " . USER_GROUP_TABLE . " ug
				WHERE ug.user_id = " . $userdata['user_id'] . "
					AND a.group_id = ug.group_id
					AND ao.auth_option_id = a.auth_option_id
					AND ( $and_sql )";
			$result = $db->sql_query($sql);

			if ( $row = $db->sql_fetchrow($result) )
			{
				do
				{
					list($type, $option) = explode('_', $row['auth_value']);

					switch ( $this->acl[$row['forum_id']][$type][$option] )
					{
						case ACL_PERMIT:
						case ACL_DENY:
						case ACL_PREVENT:
							break;
						default:
							$this->acl[$row['forum_id']][$type][$option] = $row['auth_allow_deny'];
					}
				}
				while ( $row = $db->sql_fetchrow($result) );
			}
			$db->sql_freeresult($result);

			$sql = "SELECT a.forum_id, a.auth_allow_deny, ao.auth_value
				FROM " . ACL_USERS_TABLE . " a, " . ACL_OPTIONS_TABLE . " ao
				WHERE a.user_id = " . $userdata['user_id'] . "
					AND ao.auth_option_id = a.auth_option_id
					AND ( $and_sql )";
			$result = $db->sql_query($sql);

			if ( $row = $db->sql_fetchrow($result) )
			{
				do
				{
					list($type, $option) = explode('_', $row['auth_value']);

					switch ( $this->acl[$row['forum_id']][$type][$option] )
					{
						case ACL_PERMIT:
						case ACL_PREVENT:
							break;
						default:
							$this->acl[$row['forum_id']][$type][$option] = $row['auth_allow_deny'];
							break;
					}
				}
				while ( $row = $db->sql_fetchrow($result) );
			}
			$db->sql_freeresult($result);

			if ( is_array($this->acl) )
			{
				foreach ( $this->acl as $forum_id => $auth_ary )
				{
					foreach ( $auth_ary as $type => $option_ary )
					{
						foreach ( $option_ary as $option => $value )
						{
							switch ( $value )
							{
								case ACL_ALLOW:
								case ACL_PERMIT:
									$this->acl[$forum_id][$type][$option] = 1;
									break;
								case ACL_DENY:
								case ACL_PREVENT:
									$this->acl[$forum_id][$type][$option] = 0;
									break;
							}
						}

						//
						// Store max result for type ... used later ... saves time
						//
						$this->acl[$forum_id][$type][0] = max($this->acl[$forum_id][$type]);
					}
				}
			}
		}

		return;
	}

	function get_acl($forum_id, $auth_main, $auth_type = false)
	{
		return ( $auth_main && $auth_type ) ? ( ( $this->founder || $this->acl[0]['admin'][0] ) ? true : $this->acl[$forum_id][$auth_main][$auth_type] ) : $this->acl[$forum_id][$auth_main][0];
	}

	function get_acl_admin($auth_type = false)
	{
		return ( $this->founder ) ? true : $this->get_acl(0, 'admin', $auth_type);
	}

	function set_acl_user(&$forum_id, &$user_id, &$auth, $dependencies = false)
	{
		global $db;

		$forum_sql = ( $forum_id ) ? "AND a.forum_id IN ($forum_id, 0)" : '';

		$sql = "SELECT o.auth_option_id, a.auth_allow_deny FROM " . ACL_USERS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o, " . USERS_TABLE . " u WHERE a.auth_option_id = o.auth_option_id $forum_sql AND u.user_id = a.user_id AND a.user_id = $user_id";
		$result = $db->sql_query($sql);

		$user_auth = array();
		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$user_auth[$user_id][$row['auth_option_id']] = $row['auth_allow_deny'];
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

		foreach ( $auth as $auth_option_id => $allow )
		{
			if ( !empty($user_auth) )
			{
				foreach ( $user_auth as $user => $user_auth_ary )
				{
					$sql_ary[] = ( !isset($user_auth_ary[$auth_option_id]) ) ? "INSERT INTO " . ACL_USERS_TABLE . " (user_id, forum_id, auth_option_id, auth_allow_deny) VALUES ($user_id, $forum_id, $auth_option_id, $allow)" : ( ( $user_auth_ary[$auth_option_id] != $allow ) ? "UPDATE " . ACL_USERS_TABLE . " SET auth_allow_deny = $allow WHERE user_id = $user_id AND forum_id = $forum_id AND auth_option_id = $auth_option_id" : '' );
				}
			}
			else
			{
				$sql_ary[] = "INSERT INTO " . ACL_USERS_TABLE . " (user_id, forum_id, auth_option_id, auth_allow_deny) VALUES ($user_id, $forum_id, $auth_option_id, $allow)";
			}
		}

		foreach ( $sql_ary as $sql )
		{
			$db->sql_query($sql);
		}

		unset($user_auth);
		unset($sql_ary);
	}

	function set_acl_group(&$forum_id, &$group_id, &$auth, $dependencies = false)
	{
		global $db;

		$forum_sql = "AND a.forum_id IN ($forum_id, 0)";

		$sql = "SELECT o.auth_option_id, a.auth_allow_deny FROM " . ACL_GROUPS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o  WHERE a.auth_option_id = o.auth_option_id $forum_sql AND a.group_id = $group_id";
		$result = $db->sql_query($sql);

		$group_auth = array();
		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$group_auth[$group_id][$row['auth_option_id']] = $row['auth_allow_deny'];
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

		foreach ( $auth as $auth_option_id => $allow )
		{
			if ( !empty($group_auth) )
			{
				foreach ( $group_auth as $group => $group_auth_ary )
				{
					$sql_ary[] = ( !isset($group_auth_ary[$auth_option_id]) ) ? "INSERT INTO " . ACL_GROUPS_TABLE . " (group_id, forum_id, auth_option_id, auth_allow_deny) VALUES ($group_id, $forum_id, $auth_option_id, $allow)" : ( ( $group_auth_ary[$auth_option_id] != $allow ) ? "UPDATE " . ACL_GROUPS_TABLE . " SET auth_allow_deny = $allow WHERE group_id = $group_id AND forum_id = $forum_id and auth_option_id = $auth_option_id" : '' );
				}
			}
			else
			{
				$sql_ary[] = "INSERT INTO " . ACL_GROUPS_TABLE . " (group_id, forum_id, auth_option_id, auth_allow_deny) VALUES ($group_id, $forum_id, $auth_option_id, $allow)";
			}
		}

		foreach ( $sql_ary as $sql )
		{
			$db->sql_query($sql);
		}

		unset($group_auth);
		unset($sql_ary);
	}

	function delete_acl_user($forum_id, $user_id, $auth_ids = false)
	{
		global $db;

		$auth_sql = '';
		if ( $auth_ids )
		{
			for($i = 0; $i < count($auth_ids); $i++)
			{
				$auth_sql .= ( ( $auth_sql != '' ) ? ', ' : '' ) . $auth_ids[$i];
			}
			$auth_sql = " AND auth_option_id IN ($auth_sql)";
		}

		$sql = "DELETE FROM " . ACL_USERS_TABLE . "
			WHERE user_id = $user_id
				AND forum_id = $forum_id
				$auth_sql";
		$db->sql_query($sql);
	}

	function delete_acl_group($forum_id, $group_id, $auth_type = false)
	{
		global $db;

		$auth_sql = '';
		if ( $auth_ids )
		{
			for($i = 0; $i < count($auth_ids); $i++)
			{
				$auth_sql .= ( ( $auth_sql != '' ) ? ', ' : '' ) . $auth_ids[$i];
			}
			$auth_sql = " AND auth_option_id IN ($auth_sql)";
		}

		$sql = "DELETE FROM " . ACL_GROUPS_TABLE . "
			WHERE group_id = $group_id
				AND forum_id = $forum_id
				$auth_sql";
		$db->sql_query($sql);
	}

	// Authentication plug-ins is largely down to Sergey Kanareykin, our thanks to him.
	function login($username, $password, $autologin = false)
	{
		global $board_config, $session, $phpEx;

		$method = trim($board_config['auth_method']);

		if ( file_exists('includes/auth/auth_' . $method . '.' . $phpEx) )
		{
			include_once('includes/auth/auth_' . $method . '.' . $phpEx);

			$method = 'login_' . $method;
			if ( function_exists($method) )
			{
				if ( !($user = $method($username, $password)) )
				{
					return false;
				}

				$autologin = ( isset($autologin) ) ? md5($password) : '';

				return ( $user['user_active'] ) ?  $session->create($user['user_id'], $autologin) : false;
			}
		}

		message_die(ERROR, 'Authentication method not found');
	}
}

?>