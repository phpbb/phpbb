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

class session
{
	var $session_id = '';
	var $data = array();
	var $browser = '';
	var $ip = '';
	var $page = '';
	var $load;

	// Called at each page start ... checks for, updates and/or creates a session
	function start($update = true)
	{
		global $phpEx, $SID, $db, $config;

		$current_time = time();
		$this->browser = (!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : $_ENV['HTTP_USER_AGENT'];
		$this->page = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : $_ENV['REQUEST_URI'];
		$this->page = preg_replace('#^.*?/?([a-z]+)\.' . $phpEx . '\?sid=.*?(&.*)?$#', '\1\2', $this->page);

		if (isset($_COOKIE[$config['cookie_name'] . '_sid']) || isset($_COOKIE[$config['cookie_name'] . '_data']))
		{
			$sessiondata = (isset($_COOKIE[$config['cookie_name'] . '_data'])) ? unserialize(stripslashes($_COOKIE[$config['cookie_name'] . '_data'])) : '';
			$this->session_id = (isset($_COOKIE[$config['cookie_name'] . '_sid'])) ? $_COOKIE[$config['cookie_name'] . '_sid'] : '';
			$SID = (defined('NEED_SID')) ? '?sid=' . $this->session_id : '?sid=';
		}
		else
		{
			$sessiondata = '';
			$this->session_id = (isset($_GET['sid'])) ? $_GET['sid'] : '';
			$SID = '?sid=' . $this->session_id;
		}

		// Obtain users IP
		$this->ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : $REMOTE_ADDR;

		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			if (preg_match('#^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)#', $_SERVER['HTTP_X_FORWARDED_FOR'], $ip_list))
			{
				$private_ip = array('#^0\.#', '#^127\.0\.0\.1#', '#^192\.168\.#', '#^172\.16\.#', '#^10\.#', '#^224\.#', '#^240\.#');
				$this->ip = preg_replace($private_ip, $this->ip, $ip_list[1]);
			}
		}

		// Load limit check (if applicable)
		if (doubleval($config['limit_load']) && file_exists('/proc/loadavg'))
		{
			if ($load = @file('/proc/loadavg'))
			{
				list($this->load) = explode(' ', $load[0]);

				if ($this->load > doubleval($config['limit_load']))
				{
					trigger_error('BOARD_UNAVAILABLE');
				}
			}
		}

		// session_id exists so go ahead and attempt to grab all data in preparation
		// Added session check
		if (!empty($this->session_id) && (!defined('NEED_SID') || $this->session_id == $_GET['sid']))
		{
			$sql = "SELECT u.*, s.*
				FROM " . SESSIONS_TABLE . " s, " . USERS_TABLE . " u
				WHERE s.session_id = '" . $this->session_id . "'
					AND u.user_id = s.session_user_id";
			$result = $db->sql_query($sql);

			$this->data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			// Did the session exist in the DB?
			if (isset($this->data['user_id']))
			{
				// Validate IP length according to admin ... has no effect on IPv6
				$s_ip = implode('.', array_slice(explode('.', $this->data['session_ip']), 0, $config['ip_check']));
				$u_ip = implode('.', array_slice(explode('.', $this->ip), 0, $config['ip_check']));

				$s_browser = ($config['browser_check']) ? $this->data['session_browser'] : '';
				$u_browser = ($config['browser_check']) ? $this->browser : '';

				if ($u_ip == $s_ip && $s_browser == $u_browser)
				{
					// Only update session DB a minute or so after last update or if page changes
					if (($current_time - $this->data['session_time'] > 60 || $this->data['session_page'] != $this->page) && $update)
					{
						$sql = "UPDATE " . SESSIONS_TABLE . "
							SET session_time = $current_time, session_page = '" . $db->sql_escape($this->page) . "'
							WHERE session_id = '" . $this->session_id . "'";
						$db->sql_query($sql);
					}

					return true;
				}
			}
		}

		// Session check failed, redirect the user to the index page
		// TODO: we could delay it until we grab user's data and display a localised error message
		if (defined('NEED_SID'))
		{
			// NOTE: disabled until we decide how to deal with this
			//redirect("index.$phpEx$SID");
		}

		// If we reach here then no (valid) session exists. So we'll create a new one,
		// using the cookie user_id if available to pull basic user prefs.
		$autologin = (isset($sessiondata['autologinid'])) ? $sessiondata['autologinid'] : '';
		$user_id = (isset($sessiondata['userid'])) ? intval($sessiondata['userid']) : ANONYMOUS;

		return $this->create($user_id, $autologin);
	}

	// Create a new session
	function create(&$user_id, &$autologin, $set_autologin = false, $viewonline = 1)
	{
		global $SID, $db, $config;

		$sessiondata = array();
		$current_time = time();

		if (intval($config['active_sessions']))
		{
			// Limit sessions in 1 minute period
			$sql = "SELECT COUNT(*) AS sessions
				FROM " . SESSIONS_TABLE . "
				WHERE session_time >= " . ($current_time - 60);
			$result = $db->sql_query($sql);

			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (intval($row['sessions']) > intval($config['active_sessions']))
			{
				trigger_error('BOARD_UNAVAILABLE');
			}
		}

		// Garbage collection ... remove old sessions updating user information
		// if necessary. It means (potentially) 11 queries but only infrequently
		if ($current_time - $config['session_gc'] > $config['session_last_gc'])
		{
			$this->gc($current_time);
		}

		// Grab user data ... join on session if it exists for session time
		$sql = "SELECT u.*, s.session_time
			FROM (" . USERS_TABLE . " u
			LEFT JOIN " . SESSIONS_TABLE . " s ON s.session_user_id = u.user_id)
			WHERE u.user_id = $user_id
			ORDER BY s.session_time DESC";
		$result = $db->sql_query($sql);

		$this->data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// Check autologin request, is it valid?
		if (empty($this->data) || ($this->data['user_password'] != $autologin && !$set_autologin) || !$this->data['user_active'])
		{
			$autologin = '';
			$this->data['user_id'] = $user_id = ANONYMOUS;
		}

		// Is user banned? Are they excluded?
		if (!$this->data['user_founder'])
		{
			$banned = false;

			$sql = 'SELECT ban_ip, ban_userid, ban_email, ban_exclude, ban_give_reason, ban_end  
				FROM ' . BANLIST_TABLE . '
				WHERE ban_end >= ' . time() . '
					OR ban_end = 0';
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				do
				{
					if ((!empty($row['ban_userid']) && intval($row['ban_userid']) == $this->data['user_id']) || 
						(!empty($row['ban_ip']) && preg_match('#^' . str_replace('*', '.*?', $row['ban_ip']) . '$#i', $this->ip)) || 
						(!empty($row['ban_email']) && preg_match('#^' . str_replace('*', '.*?', $row['ban_email']) . '$#i', $this->data['user_email'])))
					{
						if (!empty($row['ban_exclude']))
						{
							$banned = false;
							break;
						}
						else
						{
							$banned = true; 
						}
					}
				}
				while ($row = $db->sql_fetchrow($result));
			}
			$db->sql_freeresult($result);

			if ($banned)
			{
				// Initiate environment ... since it won't be set at this stage
				$this->setup();

				// Determine which message to output 
				$till_date = (!empty($row['ban_end'])) ? $this->format_date($row['ban_end']) : '';
				$message = (!empty($row['ban_end'])) ? 'BOARD_BAN_TIME' : 'BOARD_BAN_PERM';
	
				$message = sprintf($this->lang[$message], $till_date, '<a href="mailto:' . $config['board_contact'] . '">', '</a>');
				// More internal HTML ... :D
				$message .= (!empty($row['ban_show_reason'])) ? '<br /><br />' . sprintf($this->lang['BOARD_BAN_REASON'], $row['ban_show_reason']) : '';
				trigger_error($message);
			}
		}

		// Is there an existing session? If so, grab last visit time from that
		$this->data['session_last_visit'] = ($this->data['session_time']) ? $this->data['session_time'] : (($this->data['user_lastvisit']) ? $this->data['user_lastvisit'] : time());

		// Create or update the session
		$db->sql_return_on_error(true);

		$sql = "UPDATE " . SESSIONS_TABLE . "
			SET session_user_id = $user_id, session_last_visit = " . $this->data['session_last_visit'] . ", session_start = $current_time, session_time = $current_time, session_browser = '$this->browser', session_page = '$this->page', session_allow_viewonline = $viewonline 
			WHERE session_id = '" . $this->session_id . "'";
		if ($this->session_id == '' || !$db->sql_query($sql) || !$db->sql_affectedrows())
		{
			$db->sql_return_on_error(false);
			$this->session_id = md5(uniqid($this->ip));

			$sql = "INSERT INTO " . SESSIONS_TABLE . "
				(session_id, session_user_id, session_last_visit, session_start, session_time, session_ip, session_browser, session_page, session_allow_viewonline)
				VALUES ('" . $this->session_id . "', $user_id, " . $this->data['session_last_visit'] . ", $current_time, $current_time, '$this->ip', '$this->browser', '$this->page', $viewonline)";
			$db->sql_query($sql);
		}
		$db->sql_return_on_error(false);

		$this->data['session_id'] = $this->session_id;

		$sessiondata['autologinid'] = ($autologin && $user_id != ANONYMOUS) ? $autologin : '';
		$sessiondata['userid'] = $user_id;

		$this->set_cookie('data', serialize($sessiondata), $current_time + 31536000);
		$this->set_cookie('sid', $this->session_id, 0);
		$SID = '?sid=' . $this->session_id;

		if ($this->data['user_id'] != ANONYMOUS)
		{
			// Events ... ?
//			do_events('days');

			// First page ... ?
//			if (!empty($this->data['user_firstpage']))
//			{
//				redirect($userdata['user_firstpage']);
//			}
		}

		return true;
	}

	// Destroy a session
	function destroy()
	{
		global $SID, $db, $config;

		$current_time = time();

		$this->set_cookie('data', '', $current_time - 31536000);
		$this->set_cookie('sid', '', $current_time - 31536000);
		$SID = '?sid=';

		// Delete existing session, update last visit info first!
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_lastvisit = ' . $this->data['session_time'] . '
			WHERE user_id = ' . $this->data['user_id'];
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . SESSIONS_TABLE . "
			WHERE session_id = '" . $this->session_id . "'
				AND session_user_id = " . $this->data['user_id'];
		$db->sql_query($sql);

		$this->session_id = '';

		return true;
	}

	// Garbage collection
	function gc(&$current_time)
	{
		global $db, $config;

		// Get expired sessions, only most recent for each user
		$sql = 'SELECT session_user_id, session_page, MAX(session_time) AS recent_time
			FROM ' . SESSIONS_TABLE . '
			WHERE session_time < ' . ($current_time - $config['session_length']) . '
			GROUP BY session_user_id, session_page';
		$result = $db->sql_query_limit($sql, 5);

		$del_user_id = '';
		$del_sessions = 0;
		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				if ($row['session_user_id'] != ANONYMOUS)
				{
					$sql = 'UPDATE ' . USERS_TABLE . '
						SET user_lastvisit = ' . $row['recent_time'] . ", user_lastpage = '" . $db->sql_escape($row['session_page']) . "' 
						WHERE user_id = " . $row['session_user_id'];
					$db->sql_query($sql);
				}

				$del_user_id .= (($del_user_id != '') ? ', ' : '') . " '" . $row['session_user_id'] . "'";
				$del_sessions++;
			}
			while ($row = $db->sql_fetchrow($result));
		}

		if ($del_user_id != '')
		{
			// Delete expired sessions
			$sql = 'DELETE FROM ' . SESSIONS_TABLE . "
				WHERE session_user_id IN ($del_user_id)
					AND session_time < " . ($current_time - $config['session_length']);
			$db->sql_query($sql);
		}

		if ($del_sessions < 5)
		{
			// Less than 5 sessions, update gc timer ... else we want gc
			// called again to delete other sessions
			set_config('session_last_gc', $current_time);
		}

		return;
	}

	// Set a cookie
	function set_cookie($name, $cookiedata, $cookietime)
	{
		global $config;

		setcookie($config['cookie_name'] . '_' . $name, $cookiedata, $cookietime, $config['cookie_path'], $config['cookie_domain'], $config['cookie_secure']);
	}
}

// Contains (at present) basic user methods such as configuration
// creating date/time ... keep this?
class user extends session
{
	var $lang = array();
	var $theme = array();
	var $date_format;
	var $timezone;
	var $dst;

	var $lang_name;
	var $lang_path;
	var $img_lang;

	function setup($lang_set = false, $style = false)
	{
		global $db, $template, $config, $phpEx, $phpbb_root_path;

		if ($this->data['user_id'] != ANONYMOUS)
		{
			$this->lang_name = (file_exists($phpbb_root_path . 'language/' . $this->data['user_lang'])) ? $this->data['user_lang'] : $config['default_lang'];
			$this->lang_path = $phpbb_root_path . 'language/' . $this->lang_name . '/';

			$this->date_format = $this->data['user_dateformat'];
			$this->timezone = $this->data['user_timezone'] * 3600;
			$this->dst = $this->data['user_dst'] * 3600;
		}
		else
		{
			$this->lang_name = $config['default_lang'];
			$this->lang_path = $phpbb_root_path . 'language/' . $this->lang_name . '/';
			$this->date_format = $config['default_dateformat'];
			$this->timezone = $config['board_timezone'] * 3600;
			$this->dst = $config['board_dst'] * 3600;

			if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			{
				$accept_lang_ary = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
				foreach ($accept_lang_ary as $accept_lang)
				{
					// Set correct format ... guess full xx_YY form
					$accept_lang = substr($accept_lang, 0, 2) . '_' . strtoupper(substr($accept_lang, 3, 2));
					if (file_exists($phpbb_root_path . 'language/' . $accept_lang))
					{
						$this->lang_name = $accept_lang;
						$this->lang_path = $phpbb_root_path . 'language/' . $accept_lang . '/';
						break;
					}
					else
					{
						// No match on xx_YY so try xx
						$accept_lang = substr($accept_lang, 0, 2);
						if (file_exists($phpbb_root_path . 'language/' . $accept_lang))
						{
							$this->lang_name = $accept_lang;
							$this->lang_path = $phpbb_root_path . 'language/' . $accept_lang . '/';
							break;
						}
					}
				}
			}
		}

		include($this->lang_path . 'lang_main.' . $phpEx);
		if (defined('IN_ADMIN'))
		{
			include($this->lang_path . 'lang_admin.' . $phpEx);
		}
		$this->lang = &$lang;

/*		if (is_array($lang_set))
		{
			include($this->lang_path . '/common.' . $phpEx);

			foreach ($lang_set as $lang_file)
			{
				include($this->lang_path . '/' . $lang_file . '.' . $phpEx);
			}
			unset($lang_set);
		}
		else
		{
			include($this->lang_path . '/common.' . $phpEx);
			include($this->lang_path . '/' . $lang_set . '.' . $phpEx);
		}*/

		// Set up style
		$style = ($style) ? $style : ((!$config['override_user_style'] && $this->data['user_id'] != ANONYMOUS) ? $this->data['user_style'] : $config['default_style']);

		$sql = "SELECT DISTINCT t.template_path, t.poll_length, t.pm_box_length, c.css_data, c.css_external, i.*
			FROM " . STYLES_TABLE . " s, " . STYLES_TPL_TABLE . " t, " . STYLES_CSS_TABLE . " c, " . STYLES_IMAGE_TABLE . " i
			WHERE s.style_id IN ($style, " . $config['default_style'] . ") 
				AND t.template_id = s.template_id
				AND c.theme_id = s.style_id
				AND i.imageset_id = s.imageset_id";
		$result = $db->sql_query($sql, 600);

		if (!($this->theme = $db->sql_fetchrow($result)))
		{
			trigger_error('Could not get style data');
		}

		$template->set_template($this->theme['template_path']);

		$this->img_lang = (file_exists($phpbb_root_path . 'imagesets/' . $this->theme['imageset_path'] . '/' . $this->lang_name)) ? $this->lang_name : $config['default_lang'];

		return;
	}

	function format_date($gmepoch, $format = false)
	{
		static $lang_dates;

		if (empty($lang_dates))
		{
			foreach ($this->lang['datetime'] as $match => $replace)
			{
				$lang_dates[$match] = $replace;
			}
		}
		$format = (!$format) ? $this->date_format : $format;
		return strtr(@gmdate($format, $gmepoch + $this->timezone + $this->dst), $lang_dates);
	}

	function img($img, $alt = '', $width = false, $no_cache = false)
	{
		static $imgs;

		if (empty($imgs[$img]) || $no_cache)
		{
			$alt = (!empty($this->lang[$alt])) ? $this->lang[$alt] : '';

			$width = ($width) ? 'width="' . $width . '" ' : '';
			$imgs[$img] = '<img src=' . str_replace('{LANG}', $this->img_lang, $this->theme[$img]) . ' ' . $width . 'alt="' . $alt . '" title="' . $alt . '" />';
		}
		return $imgs[$img];
	}
}

// Will be keeping my eye of 'other products' to ensure these things don't
// mysteriously appear elsewhere, think up your own solutions!
class auth
{
	var $founder = false;
	var $acl = array('global' => '', 'local' => '');
	var $option = array();
	var $acl_options = array();

	function acl(&$userdata)
	{
		global $db, $cache;

		$this->founder = ($userdata['user_founder']) ? true : false;

		if (!($this->acl_options = $cache->get('acl_options')))
		{
			$sql = "SELECT auth_option, is_global, is_local
				FROM " . ACL_OPTIONS_TABLE . "
				ORDER BY auth_option_id";
			$result = $db->sql_query($sql);

			$global = $local = 0;
			while ($row = $db->sql_fetchrow($result))
			{
				if (!empty($row['is_global']))
				{
					$this->acl_options['global'][$row['auth_option']] = $global++;
				}
				if (!empty($row['is_local']))
				{
					$this->acl_options['local'][$row['auth_option']] = $local++;
				}
			}
			$db->sql_freeresult($result);

			$cache->put('acl_options', $this->acl_options);
			$this->acl_clear_prefetch();
			$this->acl_cache($userdata);
		}
		else if (trim($userdata['user_permissions']) == '')
		{
			$this->acl_cache($userdata);
		}

		$global_chars = ceil(sizeof($this->acl_options['global']) / 8);
		$local_chars = ceil(sizeof($this->acl_options['local']) / 8) + 2;

		for($i = 0; $i < $global_chars; $i++)
		{
			$this->acl['global'] .= str_pad(decbin(ord($userdata['user_permissions']{$i})), 8, 0, STR_PAD_LEFT);
		}

		for ($i = $global_chars; $i < strlen($userdata['user_permissions']); $i += $local_chars)
		{
			$forum_id = (ord($userdata['user_permissions']{$i}) << 8) + ord($userdata['user_permissions']{$i + 1});
			$this->acl['local'][$forum_id] = '';
			for ($j = $i + 2; $j < $i + $local_chars; $j++)
			{
				$this->acl['local'][$forum_id] .= str_pad(decbin(ord($userdata['user_permissions']{$j})), 8, 0, STR_PAD_LEFT);
			}
		}
		unset($forums);

		return;
	}

	// Look up an option
	function acl_get($opt, $f = 0)
	{
		static $cache;

		if (!isset($cache[$f][$opt]))
		{
			$cache[$f][$opt] = FALSE;
			if (isset($this->acl_options['global'][$opt]))
			{
				$cache[$f][$opt] = $this->acl['global']{$this->acl_options['global'][$opt]};
			}
			if (isset($this->acl_options['local'][$opt]))
			{
				$cache[$f][$opt] |= $this->acl['local'][$f]{$this->acl_options['local'][$opt]};
			}
		}

		return ($this->founder) ? true : $cache[$f][$opt];
	}

	function acl_gets()
	{
		$args = func_get_args();
		$f = array_pop($args);

		if (!is_numeric($f))
		{
			$args[] = $f;
			$f = 0;
		}

		// alternate syntax: acl_gets(array('m_', 'a_'), $forum_id)
		if (is_array($args[0]))
		{
			$args = $args[0];
		}

		$acl = 0;
		foreach ($args as $opt)
		{
			$acl |= $this->acl_get($opt, $f);
		}

		return $acl;
	}

	// Cache data
	function acl_cache(&$userdata)
	{
		global $db;

		// First grab user settings ... each user has only one setting for each
		// option ... so we shouldn't need any ACL_NO checks ... he says ...
		$sql = "SELECT ao.auth_option, a.forum_id, a.auth_setting
			FROM " . ACL_OPTIONS_TABLE . " ao, " . ACL_USERS_TABLE . " a 
			WHERE a.user_id = " . $userdata['user_id'] . "
				AND ao.auth_option_id = a.auth_option_id";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$this->acl[$row['forum_id']][$row['auth_option']] = $row['auth_setting']; 
		}
		$db->sql_freeresult($result);

		// Now grab group settings ... users can belong to multiple groups so we grab
		// the minimum setting for all options. ACL_NO overrides ACL_YES so act appropriatley
		$sql = "SELECT ao.auth_option, a.forum_id, MIN(a.auth_setting) as min_setting
			FROM " . USER_GROUP_TABLE . " ug, " . ACL_OPTIONS_TABLE . " ao, " . ACL_GROUPS_TABLE . " a 
			WHERE ug.user_id = " . $userdata['user_id'] . "
				AND a.group_id = ug.group_id
				AND ao.auth_option_id = a.auth_option_id 
			GROUP BY ao.auth_option, a.forum_id";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if (!isset($this->acl[$row['forum_id']][$row['auth_option']]) || (isset($this->acl[$row['forum_id']][$row['auth_option']]) && $this->acl[$row['forum_id']][$row['auth_option']] !== ACL_NO))
			{
				$this->acl[$row['forum_id']][$row['auth_option']] = $row['min_setting']; 
			}
		}
		$db->sql_freeresult($result);

		if (is_array($this->acl))
		{
			$global_bits = 8 * ceil(sizeof($this->acl_options['global']) / 8);
			$local_bits = 8 * ceil(sizeof($this->acl_options['local']) / 8);
			$local_hold = $global_hold = '';

			foreach ($this->acl as $f => $auth_ary)
			{
				if (!is_array($auth_ary))
				{
					continue;
				}

				$holding = array();
				$option_set = array();

				if (!$f)
				{
					$len = $global_bits;
					$ary_key = 'global';
					$hold_str = 'global_hold';
				}
				else
				{
					$len = $local_bits;
					$ary_key = 'local';
					$hold_str = 'local_hold';
				}


				foreach ($this->acl_options[$ary_key] as $opt => $id)
				{
					if (!empty($auth_ary[$opt]))
					{
						$holding[$id] = 1;

						$option_key = substr($opt, 0, strpos($opt, '_') + 1);
						if (empty($holding[$this->acl_options[$ary_key][$option_key]]))
						{
							$holding[$this->acl_options[$ary_key][$option_key]] = 1;
						}
					}
					else
					{
						$holding[$id] = 0;
					}
				}

				$$hold_str .= ($f) ? pack('C2', $f >> 8, $f) : '';
				$bitstring = str_pad(implode('', $holding), $len, 0, STR_PAD_RIGHT);

				for ($i = 0; $i < $len; $i += 8)
				{
					$$hold_str .= chr(bindec(substr($bitstring, $i, 8)));
				}
			}
			unset($holding);

			if ($global_hold == '')
			{
				for($i = 0; $i < $global_bits; $i += 8)
				{
					$global_hold .= chr(0);
				}
			}

			$userdata['user_permissions'] .= $global_hold . $local_hold;
			unset($global_hold);
			unset($local_hold);

			$sql = "UPDATE " . USERS_TABLE . "
				SET user_permissions = '" . addslashes($userdata['user_permissions']) . "'
				WHERE user_id = " . $userdata['user_id'];
			$db->sql_query($sql);
		}

		return;
	}

	// Clear one or all users cached permission settings
	function acl_clear_prefetch($user_id = false)
	{
		global $db;

		$where_sql = ($user_id) ? ' WHERE user_id = ' . intval($user_id) : '';

		$sql = "UPDATE " . USERS_TABLE . "
			SET user_permissions = ''
			$where_sql";
		$db->sql_query($sql);

		return;
	}

	// Authentication plug-ins is largely down to Sergey Kanareykin, our thanks to him.
	function login($username, $password, $autologin = false, $viewonline = 1)
	{
		global $config, $user, $phpbb_root_path, $phpEx;

		$method = trim($config['auth_method']);

		if (file_exists($phpbb_root_path . 'includes/auth/auth_' . $method . '.' . $phpEx))
		{
			include_once($phpbb_root_path . 'includes/auth/auth_' . $method . '.' . $phpEx);

			$method = 'login_' . $method;
			if (function_exists($method))
			{
				$login = $method($username, $password);

				// If login returned anything other than an array there was an error
				if (!is_array($login))
				{
					return $login;
				}

				$autologin = (!empty($autologin)) ? md5($password) : '';
				return ($login['user_active']) ? $user->create($login['user_id'], $autologin, true, $viewonline) : false;
			}
		}

		trigger_error('Authentication method not found', E_USER_ERROR);
	}
}

?>