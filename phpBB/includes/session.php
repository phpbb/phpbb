<?php
/** 
*
* @package phpBB3
* @version $Id$ 
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package phpBB3
* Session class
*/
class session
{
	var $session_id = '';
	var $data = array();
	var $browser = '';
	var $ip = '';
	var $page = '';
	var $current_page_filename = '';
	var $load;

	// Called at each page start ... checks for, updates and/or creates a session
	function start()
	{
		global $phpEx, $SID, $db, $config;

		$current_time = time();
		$this->browser = (!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : $_ENV['HTTP_USER_AGENT'];
		$this->page = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : $_ENV['REQUEST_URI'];
		$this->page = preg_replace('#^.*?\/?(\/adm\/)?([a-z]+?\.' . $phpEx . '\?)sid=[a-z0-9]*&?(.*?)$#i', '\1\2\3', $this->page);
		$this->page .= (isset($_POST['f'])) ? 'f=' . intval($_POST['f']) : '';

		if (isset($_COOKIE[$config['cookie_name'] . '_sid']) || isset($_COOKIE[$config['cookie_name'] . '_data']))
		{
			$sessiondata = (!empty($_COOKIE[$config['cookie_name'] . '_data'])) ? unserialize(stripslashes($_COOKIE[$config['cookie_name'] . '_data'])) : array();
			$this->session_id = request_var($config['cookie_name'] . '_sid', '');
			$SID = (defined('NEED_SID')) ? '?sid=' . $this->session_id : '?sid=';
		}
		else
		{
			$sessiondata = array();
			$this->session_id = request_var('sid', '');
			$SID = '?sid=' . $this->session_id;
		}

		// Obtain users IP
		$this->ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : getenv('REMOTE_ADDR');

		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$private_ip = array('#^0\.#', '#^127\.0\.0\.1#', '#^192\.168\.#', '#^172\.16\.#', '#^10\.#', '#^224\.#', '#^240\.#');
			foreach (explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']) as $x_ip)
			{
				if (preg_match('#([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)#', $x_ip, $ip_list))
				{
					if (($this->ip = trim(preg_replace($private_ip, $this->ip, $ip_list[1]))) == trim($ip_list[1]))
					{
						break;
					}
				}
			}
		}

		// Load limit check (if applicable)
		if (@file_exists('/proc/loadavg'))
		{
			if ($load = @file('/proc/loadavg'))
			{
				list($this->load) = explode(' ', $load[0]);

				if ($config['limit_load'] && $this->load > doubleval($config['limit_load']))
				{
					trigger_error('BOARD_UNAVAILABLE');
				}
			}
		}

		// session_id exists so go ahead and attempt to grab all data in preparation
		if (!empty($this->session_id) && (!defined('NEED_SID') || (isset($_GET['sid']) && $this->session_id == $_GET['sid'])))
		{
			$sql = 'SELECT u.*, s.*
				FROM ' . SESSIONS_TABLE . ' s, ' . USERS_TABLE . " u
				WHERE s.session_id = '" . $db->sql_escape($this->session_id) . "'
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
					if ($current_time - $this->data['session_time'] > 60 || $this->data['session_page'] != $this->page)
					{
						$sql = 'UPDATE ' . SESSIONS_TABLE . "
							SET session_time = $current_time, session_page = '" . $db->sql_escape($this->page) . "'
							WHERE session_id = '" . $db->sql_escape($this->session_id) . "'";
						$db->sql_query($sql);
					}

					return true;
				}
			}
		}

		// If we reach here then no (valid) session exists. So we'll create a new one,
		// using the cookie user_id if available to pull basic user prefs.
		$autologin = (isset($sessiondata['autologinid'])) ? $sessiondata['autologinid'] : '';
		$user_id = (isset($sessiondata['userid'])) ? intval($sessiondata['userid']) : ANONYMOUS;

		return $this->create($user_id, $autologin);
	}

	// Create a new session
	function create(&$user_id, &$autologin, $set_autologin = false, $viewonline = 1, $admin = 0)
	{
		global $SID, $db, $config;

		$sessiondata = array();
		$current_time = time();
		$current_user = $user_id;
		$bot = false;

		// Pull bot information from DB and loop through it
		$sql = 'SELECT user_id, bot_agent, bot_ip
			FROM ' . BOTS_TABLE . '
			WHERE bot_active = 1';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['bot_agent'] && preg_match('#' . preg_quote($row['bot_agent'], '#') . '#i', $this->browser))
			{
				$bot = $row['user_id'];
			}
			
			if ($row['bot_ip'] && (!$row['bot_agent'] || $bot))
			{
				foreach (explode(',', $row['bot_ip']) as $bot_ip)
				{
					if (strpos($this->ip, $bot_ip) === 0)
					{
						$bot = $row['user_id'];
						break;
					}
				}
			}

			if ($bot)
			{
				$user_id = $bot;
				break;
			}
		}
		$db->sql_freeresult($result);

		// Garbage collection ... remove old sessions updating user information
		// if necessary. It means (potentially) 11 queries but only infrequently
		if ($current_time > $config['session_last_gc'] + $config['session_gc'])
		{
			$this->gc($current_time);
		}

		// Grab user data ... join on session if it exists for session time
		$sql = 'SELECT u.*, s.session_time, s.session_id
			FROM (' . USERS_TABLE . ' u
			LEFT JOIN ' . SESSIONS_TABLE . " s ON s.session_user_id = u.user_id)
			WHERE u.user_id = $user_id
			ORDER BY s.session_time DESC";
		$result = $db->sql_query_limit($sql, 1);
		$this->data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// Check autologin request, is it valid?
		if ($this->data === false || ($this->data['user_password'] !== $autologin && !$set_autologin) || ($this->data['user_type'] == USER_INACTIVE && !$bot))
		{
			$autologin = '';
			$this->data['user_id'] = $user_id = ANONYMOUS;

			$sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . ANONYMOUS;
			$result = $db->sql_query($sql);
	
			$this->data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$this->data['session_time'] = 0;
		}

		// If we're a bot then we'll re-use an existing id if available
		if ($bot && $this->data['session_id'])
		{
			$this->session_id = $this->data['session_id'];
		}

		if (!$this->data['session_time'] && $config['active_sessions'])
		{
			// Limit sessions in 1 minute period
			$sql = 'SELECT COUNT(*) AS sessions
				FROM ' . SESSIONS_TABLE . '
				WHERE session_time >= ' . ($current_time - 60);
			$result = $db->sql_query($sql);

			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (intval($row['sessions']) > intval($config['active_sessions']))
			{
				trigger_error('BOARD_UNAVAILABLE');
			}
		}

		// Is user banned? Are they excluded?
		if ($this->data['user_type'] != USER_FOUNDER && !$bot)
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

		$sql_ary = array(
			'session_user_id'		=> (int) $user_id,
			'session_start'			=> (int) $current_time,
			'session_last_visit'	=> (int) $this->data['session_last_visit'],
			'session_time'			=> (int) $current_time,
			'session_browser'		=> (string) $this->browser,
			'session_page'			=> (string) $this->page,
			'session_ip'			=> (string) $this->ip,
			'session_viewonline'	=> (int) $viewonline,
			'session_admin'			=> (int) $admin,
		);

		$sql = 'UPDATE ' . SESSIONS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
			WHERE session_id = '" . $db->sql_escape($this->session_id) . "'";
		if ($this->session_id == '' || !$db->sql_query($sql) || !$db->sql_affectedrows())
		{
			$db->sql_return_on_error(false);
			$this->session_id = md5(uniqid($this->ip));

			$sql_ary['session_id'] = (string) $this->session_id;

			$db->sql_query('INSERT INTO ' . SESSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
		}

		$db->sql_return_on_error(false);

		if (!$bot)
		{
			$this->data['session_id'] = $this->session_id;

			// Don't set cookies if we're an admin re-authenticating
			if (!$admin || ($admin && $current_user == ANONYMOUS))
			{
				$sessiondata['userid'] = $user_id;
				$sessiondata['autologinid'] = ($autologin && $user_id != ANONYMOUS) ? $autologin : '';

				$this->set_cookie('data', serialize($sessiondata), $current_time + 31536000);
				$this->set_cookie('sid', $this->session_id, 0);
			}

			$SID = '?sid=' . $this->session_id;

			if ($this->data['user_id'] != ANONYMOUS)
			{
				// Trigger EVT_NEW_SESSION
			}
		}
		else
		{
			$SID = '?sid=';
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
			WHERE session_id = '" . $db->sql_escape($this->session_id) . "'
				AND session_user_id = " . $this->data['user_id'];
		$db->sql_query($sql);

		// Reset some basic data immediately
		$this->session_id = $this->data['username'] = $this->data['user_permissions'] = '';
		$this->data['user_id'] = ANONYMOUS;
		$this->data['session_admin'] = 0;

		// Trigger EVENT_END_SESSION

		return true;
	}

	// Garbage collection
	function gc(&$current_time)
	{
		global $db, $config;

		switch (SQL_LAYER)
		{
			case 'mysql4':
				// Firstly, delete guest sessions
				$sql = 'DELETE FROM ' . SESSIONS_TABLE . '
					WHERE session_user_id = ' . ANONYMOUS . '
						AND session_time < ' . ($current_time - $config['session_length']);
				$db->sql_query($sql);

				// Keep only the most recent session for each user
				// Note: if the user is currently browsing the board, his
				// last_visit field won't be updated, which I believe should be
				// the normal behavior anyway
				$db->sql_return_on_error(TRUE);

				$sql = 'DELETE FROM ' . SESSIONS_TABLE . '
					USING ' . SESSIONS_TABLE . ' s1, ' . SESSIONS_TABLE . ' s2
					WHERE s1.session_user_id = s2.session_user_id
						AND s1.session_time < s2.session_time';
				$db->sql_query($sql);

				$db->sql_return_on_error(FALSE);

				// Update last visit time
				$sql = 'UPDATE ' . USERS_TABLE. ' u, ' . SESSIONS_TABLE . ' s
					SET u.user_lastvisit = s.session_time, u.user_lastpage = s.session_page
					WHERE s.session_time < ' . ($current_time - $config['session_length']) . '
						AND u.user_id = s.session_user_id';
				$db->sql_query($sql);

				// Delete everything else now
				$sql = 'DELETE FROM ' . SESSIONS_TABLE . '
					WHERE session_time < ' . ($current_time - $config['session_length']);
				$db->sql_query($sql);

				set_config('session_last_gc', $current_time);
				break;

			default:

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

						$del_user_id .= (($del_user_id != '') ? ', ' : '') . $row['session_user_id'];
						$del_sessions++;
					}
					while ($row = $db->sql_fetchrow($result));
				}

				if ($del_user_id)
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
				break;
		}

		return;
	}

	// Set a cookie
	function set_cookie($name, $cookiedata, $cookietime)
	{
		global $config;

		if ($config['cookie_domain'] == 'localhost' || $config['cookie_domain'] == '127.0.0.1')
		{
			setcookie($config['cookie_name'] . '_' . $name, $cookiedata, $cookietime, $config['cookie_path']);
		}
		else
		{
			setcookie($config['cookie_name'] . '_' . $name, $cookiedata, $cookietime, $config['cookie_path'], $config['cookie_domain'], $config['cookie_secure']);
		}
	}
}

/**
* @package phpBB3
* Contains (at present) basic user methods such as configuration
* creating date/time ... keep this?
*/
class user extends session
{
	var $lang = array();
	var $help = array();
	var $theme = array();
	var $date_format;
	var $timezone;
	var $dst;

	var $lang_name;
	var $lang_path;
	var $img_lang;

	var $keyoptions = array('viewimg' => 0, 'viewflash' => 1, 'viewsmilies' => 2, 'viewsigs' => 3, 'viewavatars' => 4, 'viewcensors' => 5, 'attachsig' => 6, 'html' => 7, 'bbcode' => 8, 'smilies' => 9, 'popuppm' => 10, 'report_pm_notify' => 11);
	var $keyvalues = array();

	function setup($lang_set = false, $style = false)
	{
		global $db, $template, $config, $auth, $phpEx, $phpbb_root_path;

		if ($this->data['user_id'] != ANONYMOUS)
		{
			$this->lang_name = (file_exists($phpbb_root_path . 'language/' . $this->data['user_lang'] . "/common.$phpEx")) ? $this->data['user_lang'] : $config['default_lang'];
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
					if (file_exists($phpbb_root_path . 'language/' . $accept_lang . "/common.$phpEx"))
					{
						$this->lang_name = $config['default_lang'] = $accept_lang;
						$this->lang_path = $phpbb_root_path . 'language/' . $accept_lang . '/';
						break;
					}
					else
					{
						// No match on xx_YY so try xx
						$accept_lang = substr($accept_lang, 0, 2);
						if (file_exists($phpbb_root_path . 'language/' . $accept_lang . "/common.$phpEx"))
						{
							$this->lang_name = $config['default_lang'] = $accept_lang;
							$this->lang_path = $phpbb_root_path . 'language/' . $accept_lang . '/';
							break;
						}
					}
				}
			}
		}

		// We include common language file here to not load it every time a custom language file is included
		$lang = &$this->lang;
		if ((@include $this->lang_path . "common.$phpEx") === FALSE)
		{
			die("Language file " . $this->lang_path . "common.$phpEx" . " couldn't be opened.");
		}


		$this->add_lang($lang_set);
		unset($lang_set);

		if (!empty($_GET['style']) && $auth->acl_get('a_styles'))
		{
			global $SID;

			$style = request_var('style', 0);
			$SID .= '&amp;style=' . $style;
		}
		else
		{
			// Set up style
			$style = ($style) ? $style : ((!$config['override_user_style'] && $this->data['user_id'] != ANONYMOUS) ? $this->data['user_style'] : $config['default_style']);
		}

		// TODO: DISTINCT making problems with DBMS not able to distinct TEXT fields
		$sql = 'SELECT DISTINCT s.style_id, t.*, c.*, i.*
			FROM ' . STYLES_TABLE . ' s, ' . STYLES_TPL_TABLE . ' t, ' . STYLES_CSS_TABLE . ' c, ' . STYLES_IMAGE_TABLE . " i
			WHERE s.style_id IN ($style, " . $config['default_style'] . ')
				AND t.template_id = s.template_id
				AND c.theme_id = s.theme_id
				AND i.imageset_id = s.imageset_id';
		$result = $db->sql_query($sql, 3600);

		if (!($row = $db->sql_fetchrow($result)))
		{
			trigger_error('Could not get style data');
		}

		$this->theme = ($row2 = $db->sql_fetchrow($result)) ? array(
			($style == $row['style_id']) ? 'primary' : 'secondary'	=> $row,
			($style == $row2['style_id']) ? 'primary' : 'secondary'	=> $row2) : array('primary'	=> $row);
		$db->sql_freeresult($result);

		unset($row);
		unset($row2);

		// Add to template database
		foreach (array_keys($this->theme) as $style_priority)
		{
			$this->theme[$style_priority]['pagination_sep'] = ', ';
		}

		// TEMP
		$this->theme['primary']['parse_css_file'] = false;
		if (!$this->theme['primary']['theme_storedb'] && $this->theme['primary']['parse_css_file'])
		{
			$this->theme['primary']['theme_storedb'] = 1;
			
			$sql_ary = array(
				'theme_data'	=> implode('', file("{$phpbb_root_path}styles/" . $this->theme['primary']['theme_path'] . '/theme/stylesheet.css')),
				'theme_mtime'	=> time(),
				'theme_storedb'	=> 1
			);

			$db->sql_query('UPDATE ' . STYLES_CSS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE theme_id = ' . $style);
			
			unset($sql_ary);
		}

		$template->set_template();

		$this->img_lang = (file_exists($phpbb_root_path . 'styles/' . $this->theme['primary']['imageset_path'] . '/imageset/' . $this->lang_name)) ? $this->lang_name : $config['default_lang'];

		// Is board disabled and user not an admin or moderator?
		// TODO
		// New ACL enabling board access while offline?
		if ($config['board_disable'] && !defined('IN_LOGIN') && !$auth->acl_gets('a_', 'm_'))
		{
			$message = (!empty($config['board_disable_msg'])) ? $config['board_disable_msg'] : 'BOARD_DISABLE';
			trigger_error($message);
		}

		// Does the user need to change their password? If so, redirect to the
		// ucp profile reg_details page ... of course do not redirect if we're
		// already in the ucp
		if (!defined('IN_ADMIN') && $config['chg_passforce'] && $this->data['user_passchg'] < time() - ($config['chg_passforce'] * 86400))
		{
			global $SID;

			if (!preg_match('#' . preg_quote("ucp.$phpEx$SID") . '&i\=[a-z0-9]+?&mode\=reg_details#', $_SERVER['REQUEST_URI']))
			{
				redirect("ucp.$phpEx$SID&i=profile&mode=reg_details");
			}
		}

		return;
	}

	// Add Language Items - use_db and use_help are assigned where needed (only use them to force inclusion)
	//
	// $lang_set = array('posting', 'help' => 'faq');
	// $lang_set = array('posting', 'viewtopic', 'help' => array('bbcode', 'faq'))
	// $lang_set = array(array('posting', 'viewtopic'), 'help' => array('bbcode', 'faq'))
	// $lang_set = 'posting'
	// $lang_set = array('help' => 'faq', 'db' => array('help:faq', 'posting'))
	function add_lang($lang_set, $use_db = false, $use_help = false)
	{
		global $phpEx;

		if (is_array($lang_set))
		{
			foreach ($lang_set as $key => $lang_file)
			{
				// Please do not delete this line.
				// We have to force the type here, else [array] language inclusion will not work
				$key = (string) $key;

				if ($key == 'db')
				{
					$this->add_lang($lang_file, true, $use_help);
				}
				else if ($key == 'help')
				{
					$this->add_lang($lang_file, $use_db, true);
				}
				else if (!is_array($lang_file))
				{
					$this->set_lang($this->lang, $this->help, $lang_file, $use_db, $use_help);
				}
				else
				{
					$this->add_lang($lang_file, $use_db, $use_help);
				}
			}
			unset($lang_set);
		}
		else if ($lang_set)
		{
			$this->set_lang($this->lang, $this->help, $lang_set, $use_db, $use_help);
		}
	}

	function set_lang(&$lang, &$help, $lang_file, $use_db = false, $use_help = false)
	{
		global $phpEx;

		// $lang == $this->lang
		// $help == $this->help
		// - add appropiate variables here, name them as they are used within the language file...
		if (!$use_db)
		{
			if ( (@include $this->lang_path . (($use_help) ? 'help_' : '') . "$lang_file.$phpEx") === FALSE )
			{
				trigger_error("Language file " . $this->lang_path . (($use_help) ? 'help_' : '') . "$lang_file.$phpEx" . " couldn't be opened.");
			}
		}
		else if ($use_db)
		{
			// Get Database Language Strings
			// Put them into $lang if nothing is prefixed, put them into $help if help: is prefixed
			// For example: help:faq, posting
		}
	}

	function format_date($gmepoch, $format = false, $forcedate = false)
	{
		static $lang_dates, $midnight;

		if (empty($lang_dates))
		{
			foreach ($this->lang['datetime'] as $match => $replace)
			{
				$lang_dates[$match] = $replace;
			}
		}

		$format = (!$format) ? $this->date_format : $format;

		if (!$midnight)
		{
			list($d, $m, $y) = explode(' ', gmdate('j n Y', time() + $this->timezone + $this->dst));
			$midnight = gmmktime(0, 0, 0, $m, $d, $y) - $this->timezone - $this->dst;
		}

		if (strpos($format, '|') === false || (!($gmepoch > $midnight && !$forcedate) && !($gmepoch > $midnight - 86400 && !$forcedate)))
		{
			return strtr(@gmdate(str_replace('|', '', $format), $gmepoch + $this->timezone + $this->dst), $lang_dates);
		}
		
		if ($gmepoch > $midnight && !$forcedate)
		{
			$format = substr($format, 0, strpos($format, '|')) . '||' . substr(strrchr($format, '|'), 1);
			return str_replace('||', $this->lang['datetime']['TODAY'], strtr(@gmdate($format, $gmepoch + $this->timezone + $this->dst), $lang_dates));
		}
		else if ($gmepoch > $midnight - 86400 && !$forcedate)
		{
			$format = substr($format, 0, strpos($format, '|')) . '||' . substr(strrchr($format, '|'), 1);
			return str_replace('||', $this->lang['datetime']['YESTERDAY'], strtr(@gmdate($format, $gmepoch + $this->timezone + $this->dst), $lang_dates));
		}
	}

	function get_iso_lang_id()
	{
		global $config, $db;

		if (isset($this->lang_id))
		{
			return $this->lang_id;
		}

		if (!$this->lang_name)
		{
			$this->lang_name = $config['default_lang'];
		}

		$sql = 'SELECT lang_id
			FROM ' . LANG_TABLE . "
			WHERE lang_iso = '{$this->lang_name}'";
		$result = $db->sql_query($sql);

		return (int) $db->sql_fetchfield('lang_id', 0, $result);
	}

	// Get profile fields for user
	function get_profile_fields($user_id)
	{
		global $user, $db;

		if (isset($user->profile_fields))
		{
			return;
		}

		// TODO: think about adding this to the session code too?
		// Grabbing all user specific options (all without the need of special complicate adding to the sql query) might be useful...
		$sql = 'SELECT * FROM ' . PROFILE_DATA_TABLE . "
			WHERE user_id = $user_id";
		$result = $db->sql_query_limit($sql, 1);

		$user->profile_fields = (!($row = $db->sql_fetchrow($result))) ? array() : $row;
		$db->sql_freeresult($result);
	}

	function img($img, $alt = '', $width = false, $suffix = '')
	{
		static $imgs;
		global $phpbb_root_path;

		if (empty($imgs[$img . $suffix]) || $width !== false)
		{
			if (!isset($this->theme['primary'][$img]) || !$this->theme['primary'][$img])
			{
				// Do not fill the image to let designers decide what to do if the image is empty
				$imgs[$img . $suffix] = '';
				return $imgs[$img . $suffix];
			}

			if ($width === false)
			{
				list($imgsrc, $height, $width) = explode('*', $this->theme['primary'][$img]);
			}
			else
			{
				list($imgsrc, $height) = explode('*', $this->theme['primary'][$img]);
			}

			if ($suffix !== '')
			{
				$imgsrc = str_replace('{SUFFIX}', $suffix, $imgsrc);
			}

			$imgsrc = '"' . $phpbb_root_path . 'styles/' . $this->theme['primary']['imageset_path'] . '/imageset/' . str_replace('{LANG}', $this->img_lang, $imgsrc) . '"';
			$width = ($width) ? ' width="' . $width . '"' : '';
			$height = ($height) ? ' height="' . $height . '"' : '';

			$imgs[$img . $suffix] = $imgsrc . $width . $height;
		}

		$alt = (!empty($this->lang[$alt])) ? $this->lang[$alt] : $alt;
		return '<img src=' . $imgs[$img . $suffix] . ' alt="' . $alt . '" title="' . $alt . '" name="' . $img . '" />';
	}

	// Start code for checking/setting option bit field for user table
	function optionget($key, $data = false)
	{
		if (!isset($this->keyvalues[$key]))
		{
			$var = ($data) ? $data : $this->data['user_options'];
			$this->keyvalues[$key] = ($var & 1 << $this->keyoptions[$key]) ? true : false;
		}
		return $this->keyvalues[$key];
	}

	function optionset($key, $value, $data = false)
	{
		$var = ($data) ? $data : $this->data['user_options'];

		if ($value && !($var & 1 << $this->keyoptions[$key]))
		{
			$var += 1 << $this->keyoptions[$key];
		}
		else if (!$value && ($var & 1 << $this->keyoptions[$key]))
		{
			$var -= 1 << $this->keyoptions[$key];
		}
		else
		{
			return ($data) ? $var : false;
		}

		if (!$data)
		{
			$this->data['user_options'] = $var;
			return true;
		}
		else
		{
			return $var;
		}
	}
}

/**
* @package phpBB3
* Will be keeping my eye of 'other products' to ensure these things don't
* mysteriously appear elsewhere, think up your own solutions!
*/
class auth
{
	var $founder = false;
	var $acl = array();
	var $option = array();
	var $acl_options = array();

	function acl(&$userdata)
	{
		global $db, $cache;

		if (!($this->acl_options = $cache->get('acl_options')))
		{
			$sql = 'SELECT auth_option, is_global, is_local
				FROM ' . ACL_OPTIONS_TABLE . '
				ORDER BY auth_option_id';
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
		else if (!$userdata['user_permissions'])
		{
			$this->acl_cache($userdata);
		}

		foreach (explode("\n", $userdata['user_permissions']) as $f => $seq)
		{
			if ($seq)
			{
				$i = 0;
				while ($subseq = substr($seq, $i, 6))
				{
					if (!isset($this->acl[$f]))
					{
						$this->acl[$f] = '';
					}
					$this->acl[$f] .= str_pad(base_convert($subseq, 36, 2), 31, 0, STR_PAD_LEFT);
					$i += 6;
				}
			}
		}
		return;
	}

	// Look up an option
	function acl_get($opt, $f = 0)
	{
		static $cache;

		if (!isset($cache[$f][$opt]))
		{
			$cache[$f][$opt] = false;
			if (isset($this->acl_options['global'][$opt]))
			{
				if (isset($this->acl[0]))
				{
					$cache[$f][$opt] = $this->acl[0]{$this->acl_options['global'][$opt]};
				}
			}
			if (isset($this->acl_options['local'][$opt]))
			{
				if (isset($this->acl[$f]))
				{
					$cache[$f][$opt] |= $this->acl[$f]{$this->acl_options['local'][$opt]};
				}
			}
		}

		// Needs to change ... check founder status when updating cache?
		return $cache[$f][$opt];
	}

	function acl_getf($opt)
	{
		static $cache;

		if (isset($this->acl_options['local'][$opt]))
		{
			foreach ($this->acl as $f => $bitstring)
			{
				if (!isset($cache[$f][$opt]))
				{
					$cache[$f][$opt] = false;

					$cache[$f][$opt] = $bitstring{$this->acl_options['local'][$opt]};
					if (isset($this->acl_options['global'][$opt]))
					{
						$cache[$f][$opt] |= $this->acl[0]{$this->acl_options['global'][$opt]};
					}
				}
			}
		}

		return $cache;
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

	function acl_get_list($user_id = false, $opts = false, $forum_id = false)
	{
		$hold_ary = $this->acl_raw_data($user_id, $opts, $forum_id);

		$auth_ary = array();
		foreach ($hold_ary as $user_id => $forum_ary)
		{
			foreach ($forum_ary as $forum_id => $auth_option_ary)
			{
				foreach ($auth_option_ary as $auth_option => $auth_setting)
				{
					if ($auth_setting == ACL_YES)
					{
						$auth_ary[$forum_id][$auth_option][] = $user_id;
					}
				}
			}
		}

		return $auth_ary;
	}

	// Cache data
	function acl_cache(&$userdata)
	{
		global $db;

		$hold_ary = $this->acl_raw_data($userdata['user_id'], false, false);
		$hold_ary = $hold_ary[$userdata['user_id']];

		// If this user is founder we're going to force fill the admin options ...
		if ($userdata['user_type'] == USER_FOUNDER)
		{
			foreach ($this->acl_options['global'] as $opt => $id)
			{
				if (strpos($opt, 'a_') !== false)
				{
					$hold_ary[0][$opt] = 1;
				}
			}
		}

		$hold_str = $userdata['user_permissions'];
		if (is_array($hold_ary))
		{
			ksort($hold_ary);

			$last_f = 0;
			foreach ($hold_ary as $f => $auth_ary)
			{
				$ary_key = (!$f) ? 'global' : 'local';

				$bitstring = array();
				foreach ($this->acl_options[$ary_key] as $opt => $id)
				{
					if (!empty($auth_ary[$opt]))
					{
						$bitstring[$id] = 1;

						$option_key = substr($opt, 0, strpos($opt, '_') + 1);
						if (empty($holding[$this->acl_options[$ary_key][$option_key]]))
						{
							$bitstring[$this->acl_options[$ary_key][$option_key]] = 1;
						}
					}
					else
					{
						$bitstring[$id] = 0;
					}
				}

				$bitstring = implode('', $bitstring);

				$hold_str .= str_repeat("\n", $f - $last_f);

				for ($i = 0; $i < strlen($bitstring); $i += 31)
				{
					$hold_str .= str_pad(base_convert(str_pad(substr($bitstring, $i, 31), 31, 0, STR_PAD_RIGHT), 2, 36), 6, 0, STR_PAD_LEFT);
				}

				$last_f = $f;
			}
			unset($bitstring);

			$userdata['user_permissions'] = rtrim($hold_str);

			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_permissions = '" . $db->sql_escape($userdata['user_permissions']) . "'
				WHERE user_id = " . $userdata['user_id'];
			$db->sql_query($sql);
		}
		unset($hold_ary);

		return;
	}

	function acl_raw_data($user_id = false, $opts = false, $forum_id = false)
	{
		global $db;

		$sql_user = ($user_id) ? ((!is_array($user_id)) ? "user_id = $user_id" : 'user_id IN (' . implode(', ', $user_id) . ')') : '';
		$sql_forum = ($forum_id) ? ((!is_array($forum_id)) ? "AND a.forum_id = $forum_id" : 'AND a.forum_id IN (' . implode(', ', $forum_id) . ')') : '';
		$sql_opts = ($opts) ? ((!is_array($opts)) ? "AND ao.auth_option = '$opts'" : 'AND ao.auth_option IN (' . implode(', ', preg_replace('#^[\s]*?(.*?)[\s]*?$#e', "\"'\" . \$db->sql_escape('\\1') . \"'\"", $opts)) . ')') : '';

		$hold_ary = array();
		// First grab user settings ... each user has only one setting for each
		// option ... so we shouldn't need any ACL_NO checks ... he says ...
		$sql = 'SELECT ao.auth_option, a.user_id, a.forum_id, a.auth_setting
			FROM ' . ACL_OPTIONS_TABLE . ' ao, ' . ACL_USERS_TABLE . ' a
			WHERE ao.auth_option_id = a.auth_option_id
				' . (($sql_user) ? 'AND a.' . $sql_user : '') . "
				$sql_forum
				$sql_opts
			ORDER BY a.forum_id, ao.auth_option";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$hold_ary[$row['user_id']][$row['forum_id']][$row['auth_option']] = $row['auth_setting'];
		}
		$db->sql_freeresult($result);

		// Now grab group settings ... ACL_NO overrides ACL_YES so act appropriatley
		$sql = 'SELECT ug.user_id, ao.auth_option, a.forum_id, a.auth_setting
			FROM ' . USER_GROUP_TABLE . ' ug, ' . ACL_OPTIONS_TABLE . ' ao, ' . ACL_GROUPS_TABLE . ' a
			WHERE ao.auth_option_id = a.auth_option_id
				AND a.group_id = ug.group_id
				' . (($sql_user) ? 'AND ug.' . $sql_user : '') . "
				$sql_forum
				$sql_opts
			ORDER BY a.forum_id, ao.auth_option";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if (!isset($hold_ary[$row['user_id']][$row['forum_id']][$row['auth_option']]) || (isset($hold_ary[$row['user_id']][$row['forum_id']][$row['auth_option']]) && $hold_ary[$row['user_id']][$row['forum_id']][$row['auth_option']] != ACL_NO))
			{
				$hold_ary[$row['user_id']][$row['forum_id']][$row['auth_option']] = $row['auth_setting'];
			}
		}
		$db->sql_freeresult($result);

		return $hold_ary;
	}

	function acl_group_raw_data($group_id = false, $opts = false, $forum_id = false)
	{
		global $db;

		$sql_group = ($group_id) ? ((!is_array($group_id)) ? "group_id = $group_id" : 'group_id IN (' . implode(', ', $group_id) . ')') : '';
		$sql_forum = ($forum_id) ? ((!is_array($forum_id)) ? "AND a.forum_id = $forum_id" : 'AND a.forum_id IN (' . implode(', ', $forum_id) . ')') : '';
		$sql_opts = ($opts) ? ((!is_array($opts)) ? "AND ao.auth_option = '$opts'" : 'AND ao.auth_option IN (' . implode(', ', preg_replace('#^[\s]*?(.*?)[\s]*?$#e', "\"'\" . \$db->sql_escape('\\1') . \"'\"", $opts)) . ')') : '';

		$hold_ary = array();

		// Grab group settings ... ACL_NO overrides ACL_YES so act appropriatley
		$sql = 'SELECT a.group_id, ao.auth_option, a.forum_id, a.auth_setting
			FROM ' . ACL_OPTIONS_TABLE . ' ao, ' . ACL_GROUPS_TABLE . ' a
			WHERE ao.auth_option_id = a.auth_option_id
				' . (($sql_group) ? 'AND a.' . $sql_group : '') . "
				$sql_forum
				$sql_opts
			ORDER BY a.forum_id, ao.auth_option";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$hold_ary[$row['group_id']][$row['forum_id']][$row['auth_option']] = $row['auth_setting'];
		}
		$db->sql_freeresult($result);

		return $hold_ary;
	}

	// Clear one or all users cached permission settings
	function acl_clear_prefetch($user_id = false)
	{
		global $db;

		$where_sql = ($user_id) ? ' WHERE user_id ' . ((is_array($user_id)) ? ' IN (' . implode(', ', array_map('intval', $user_id)) . ')' : " = $user_id") : '';

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_permissions = ''
			$where_sql";
		$db->sql_query($sql);

		return;
	}

	// Authentication plug-ins is largely down to Sergey Kanareykin, our thanks to him.
	function login($username, $password, $autologin = false, $viewonline = 1, $admin = 0)
	{
		global $config, $db, $user, $phpbb_root_path, $phpEx;

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

				return $user->create($login['user_id'], $autologin, true, $viewonline, $admin);
			}
		}

		trigger_error('Authentication method not found', E_USER_ERROR);
	}
}

?>