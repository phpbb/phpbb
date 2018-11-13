<?php
/**
*
* @package Icy Phoenix
* @version $Id$
* @copyright (c) 2008 Icy Phoenix
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_ICYPHOENIX'))
{
	exit;
}

/**
* Session class
* @package phpBB3 / Icy Phoenix
*/
class session
{
	var $cookie_data = array();
	var $cookie_expire = 0;
	var $page = array();
	var $data = array();
	var $browser = '';
	var $forwarded_for = '';
	var $host = '';
	var $session_id = '';
	var $ip = '';
	var $load = 0;
	var $time_now = 0;
	var $update_session_page = true;

	/**
	* Start session management
	*
	* This is where all session activity begins. We gather various pieces of
	* information from the client and server. We test to see if a session already
	* exists. If it does, fine and dandy. If it doesn't we'll go on to create a
	* new one ... pretty logical heh? We also examine the system load (if we're
	* running on a system which makes such information readily available) and
	* halt if it's above an admin definable limit.
	*
	* @param bool $update_session_page if true the session page gets updated.
	*			This can be set to circumvent certain scripts to update the users last visited page.
	*/
	function session_begin($update_session_page = true)
	{
		global $SID, $_SID, $_EXTRA_URL, $db, $config;
		// ICY PHOENIX - BEGIN
		global $lang;
		// ICY PHOENIX - END

		// Give us some basic information
		$this->time_now = time();
		$this->cookie_data = array('u' => 0, 'k' => '');
		$this->cookie_expire = $this->time_now + (($config['max_autologin_time']) ? 86400 * (int) $config['max_autologin_time'] : 31536000);
		$this->update_session_page = (empty($update_session_page) || defined('IMG_THUMB')) ? false : true;
		//$this->browser = (!empty($_SERVER['HTTP_USER_AGENT'])) ? htmlspecialchars((string) $_SERVER['HTTP_USER_AGENT']) : '';
		$this->browser = (!empty($_SERVER['HTTP_USER_AGENT'])) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
		$this->referer = (!empty($_SERVER['HTTP_REFERER'])) ? htmlspecialchars((string) $_SERVER['HTTP_REFERER']) : '';
		$this->forwarded_for = (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? htmlspecialchars((string) $_SERVER['HTTP_X_FORWARDED_FOR']) : '';

		$this->host = extract_current_hostname();
		$this->page = extract_current_page(IP_ROOT_PATH);

		$session_cookie_empty = empty($_COOKIE[$config['cookie_name'] . '_sid']) ? true : false;
		$session_get_empty = empty($_GET['sid']) ? true : false;
		$session_empty = true;
		if (isset($_COOKIE[$config['cookie_name'] . '_sid']) || isset($_COOKIE[$config['cookie_name'] . '_u']))
		{
			$this->cookie_data['u'] = request_var($config['cookie_name'] . '_u', 0, false, true);
			$this->cookie_data['k'] = request_var($config['cookie_name'] . '_k', '', false, true);
			$this->session_id = request_var($config['cookie_name'] . '_sid', '', false, true);

			// Mighty Gorgon: I'm still not sure if I want to keep 'sid=' in Icy Phoenix as well... maybe better removing it!!!
			//$SID = (defined('NEED_SID')) ? ('sid=' . $this->session_id) : 'sid=';
			$SID = (defined('NEED_SID')) ? ('sid=' . $this->session_id) : '';
			$_SID = (defined('NEED_SID')) ? $this->session_id : '';

			$session_empty = empty($this->session_id) ? true : false;
		}

		// Mighty Gorgon: moved here this IF block... why it was so down in the code???
		// if no session id is set, redirect to index.php
		//if (defined('NEED_SID') && ($cookie_empty || (!isset($_GET['sid']) || ($this->session_id !== $_GET['sid']))))
		if (defined('NEED_SID') && !defined('IN_LOGIN') && ($session_cookie_empty || $session_empty || !isset($_GET['sid']) || ((isset($_GET['sid']) && ($this->session_id !== $_GET['sid'])))))
		{
			// Mighty Gorgon: I don't know why it isn't working properly, returning blank page!!!
			//send_status_line(401, 'Not authorized');
			// Mighty Gorgon: removed append_sid as it seems the user doesn't have a valid SID!
			redirect(IP_ROOT_PATH . 'index.' . PHP_EXT);
		}

		if ($session_empty)
		{
			$this->session_id = request_var('sid', '');
			$_SID = $this->session_id;
			$SID = 'sid=' . $this->session_id;
			$this->cookie_data = array('u' => 0, 'k' => '');
		}

		$_EXTRA_URL = array();

		// Why no forwarded_for et al? Well, too easily spoofed. With the results of my recent requests
		// it's pretty clear that in the majority of cases you'll at least be left with a proxy/cache ip.
		$this->ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : getenv('REMOTE_ADDR'));
		$this->ip = preg_replace('#[ ]{2,}#', ' ', str_replace(array(',', ' '), ' ', $this->ip));

		// split the list of IPs
		$ips = explode(' ', $this->ip);

		// Default IP if REMOTE_ADDR is invalid
		$this->ip = '127.0.0.1';

		$format_ipv4 = get_preg_expression('ipv4');
		$format_ipv6 = get_preg_expression('ipv6');
		foreach ($ips as $ip)
		{
			if (preg_match($format_ipv4, $ip))
			{
				$this->ip = $ip;
			}
			elseif (preg_match($format_ipv6, $ip))
			{
				// Quick check for IPv4-mapped address in IPv6
				if (stripos($ip, '::ffff:') === 0)
				{
					$ipv4 = substr($ip, 7);

					if (preg_match($format_ipv4, $ipv4))
					{
						$ip = $ipv4;
					}
				}

				$this->ip = $ip;
			}
			else
			{
				// We want to use the last valid address in the chain
				// Leave foreach loop when address is invalid
				break;
			}
		}

		$this->load = false;

		// Load limit check (if applicable)
		if ($config['limit_load'] || $config['limit_search_load'])
		{
			if ((function_exists('sys_getloadavg') && ($load = sys_getloadavg())) || ($load = explode(' ', @file_get_contents('/proc/loadavg'))))
			{
				$this->load = array_slice($load, 0, 1);
				$this->load = floatval($this->load[0]);
			}
			else
			{
				set_config('limit_load', '0');
				set_config('limit_search_load', '0');
			}
		}

		// if session id is set
		if (!empty($this->session_id))
		{
			$sql = "SELECT u.*, s.*
				FROM " . SESSIONS_TABLE . " s, " . USERS_TABLE . " u
				WHERE s.session_id = '" . $db->sql_escape($this->session_id) . "'
					AND u.user_id = s.session_user_id";
			$result = $db->sql_query($sql);
			$this->data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			// Did the session exist in the DB?
			if (isset($this->data['user_id']))
			{
				if ((strpos($this->ip, ':') !== false) && (strpos($this->data['session_ip'], ':') !== false))
				{
					$s_ip = short_ipv6($this->data['session_ip'], $config['ip_check']);
					$u_ip = short_ipv6($this->ip, $config['ip_check']);
				}
				else
				{
					$s_ip = implode('.', array_slice(explode('.', $this->data['session_ip']), 0, $config['ip_check']));
					$u_ip = implode('.', array_slice(explode('.', $this->ip), 0, $config['ip_check']));
				}

				$s_browser = ($config['browser_check']) ? trim(strtolower(substr($this->data['session_browser'], 0, 254))) : '';
				$u_browser = ($config['browser_check']) ? trim(strtolower(substr($this->browser, 0, 254))) : '';

				// referer checks
				// The @ before $config['referer_validation'] suppresses notices present while running the updater
				$check_referer_path = (@$config['referer_validation'] == REFERER_VALIDATE_PATH);
				$referer_valid = true;

				// we assume HEAD and TRACE to be foul play and thus only whitelist GET
				if (@$config['referer_validation'] && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) !== 'get')
				{
					$referer_valid = $this->validate_referer($check_referer_path);
				}

				if (($u_ip === $s_ip) && ($s_browser === $u_browser) && $referer_valid)
				{
					// Some useful boolean checks... defined here for future easy of use
					$session_expired = false;
					$session_refresh_time = (int) SESSION_REFRESH;
					$autologin_expired = (!empty($config['max_autologin_time']) && ($this->data['session_time'] < ($this->time_now - (86400 * (int) $config['max_autologin_time']) + $session_refresh_time))) ? true : false;
					$session_time_expired = ($this->data['session_time'] < ($this->time_now - ((int) $config['session_length'] + $session_refresh_time))) ? true : false;
					$session_refresh = ($this->data['session_time'] < ($this->time_now - $session_refresh_time)) ? true : false;

					if (!$session_expired)
					{
						// Check the session length timeframe if autologin is not enabled.
						// Else check the autologin length... and also removing those having autologin enabled but no longer allowed site-wide.
						if (empty($this->data['session_autologin']))
						{
							if ($session_time_expired)
							{
								$session_expired = true;
							}
						}
						elseif (empty($config['allow_autologin']) || $autologin_expired)
						{
							$session_expired = true;
						}
					}

					// ICY PHOENIX - BEGIN
					// This portion of code needs to stay here (after isset($this->data['user_id']) )... otherwise we are potentially going to instantiate some $user->data even if $user->data is still empty
					$this->bots_process();
					if (isset($this->data['user_id']) && ($this->data['user_id'] != ANONYMOUS) && isset($this->data['user_level']) && ($this->data['user_level'] == JUNIOR_ADMIN))
					{
						define('IS_JUNIOR_ADMIN', true);
						$this->data['user_level'] = (!defined('IN_ADMIN') && !defined('IN_CMS')) ? ADMIN : MOD;
					}

					// Refresh last visit time for those users having autologin enabled or those users with session time expired (only if config for this has been set)
					if (($this->data['user_id'] != ANONYMOUS) && ((!empty($config['session_last_visit_reset']) && $session_time_expired) || (!empty($config['allow_autologin']) && $autologin_expired) || empty($this->data['user_lastvisit'])))
					{
						$sql = "UPDATE " . USERS_TABLE . "
							SET user_lastvisit = " . (int) $this->data['session_time'] . "
							WHERE user_id = " . (int) $this->data['user_id'];
						$db->sql_query($sql);
					}
					// ICY PHOENIX - END

					if (!$session_expired)
					{
						// Only update session DB a minute or so after last update or if page changes
						// Mighty Gorgon: in Icy Phoenix we give maximum priority to $this->update_session_page, because we don't want the session to be updated for thumbnails or other special features!
						if ($this->update_session_page && ($session_refresh || ($this->data['session_page'] != $this->page['page'])) && empty($_REQUEST['explain']))
						{
							$sql_ary = array();

							// ICY PHOENIX - BEGIN
							// Update $user->data
							$this->data['user_session_time'] = $this->time_now;
							$this->data['user_session_page'] = (string) substr($this->page['page'], 0, 254);
							$this->data['user_browser'] = (string) substr($this->browser, 0, 254);
							$this->data['user_totalpages'] = (int) $this->data['user_totalpages'] + 1;
							$this->data['user_totaltime'] = (int) $this->data['user_totaltime'] + $this->time_now - $this->data['session_time'];
							// ICY PHOENIX - END

							// A little trick to reset session_admin on session re-usage
							if (!defined('IN_ADMIN') && !defined('IN_CMS') && $session_time_expired)
							{
								$sql_ary['session_admin'] = 0;
							}
							$sql_ary['session_time'] = $this->time_now;
							$sql_ary['session_page'] = $this->data['user_session_page'];
							$sql_ary['session_browser'] = $this->data['user_browser'];
							$sql_ary['session_forum_id'] = $this->page['forum'];
							$sql_ary['session_topic_id'] = $this->page['topic'];

							$db->sql_return_on_error(true);

							$sql = "UPDATE " . SESSIONS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_ary) . "
								WHERE session_id = '" . $db->sql_escape($this->session_id) . "'";
							$result = $db->sql_query($sql);

							// ICY PHOENIX - BEGIN
							if ($this->data['user_id'] != ANONYMOUS)
							{
								$sql_ary = array();
								$sql_ary['user_ip'] = $this->ip;
								$sql_ary['user_session_time'] = $this->data['user_session_time'];
								$sql_ary['user_session_page'] = $this->data['user_session_page'];
								$sql_ary['user_browser'] = $this->data['user_browser'];
								$sql_ary['user_totalpages'] = $this->data['user_totalpages'];
								$sql_ary['user_totaltime'] = $this->data['user_totaltime'];

								$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_ary) . "
									WHERE user_id = " . $this->data['user_id'];
								$result = $db->sql_query($sql);
							}
							// ICY PHOENIX - END

							$db->sql_return_on_error(false);
						}

						$this->data['is_registered'] = (empty($this->data['is_bot']) && ($this->data['user_id'] != ANONYMOUS) && !empty($this->data['user_active'])) ? true : false;
						$this->data['session_logged_in'] = $this->data['is_registered'];
						$this->data['user_lang'] = basename($this->data['user_lang']);

						$this->upi2db();

						return true;
					}
				}
				else
				{
					// Added logging temporarily to help debug bugs...
					if (defined('DEBUG_EXTRA') && ($this->data['user_id'] != ANONYMOUS))
					{
						if ($referer_valid)
						{
							add_log('critical', 'LOG_IP_BROWSER_FORWARDED_CHECK', $u_ip, $s_ip, $u_browser, $s_browser);
						}
						else
						{
							add_log('critical', 'LOG_REFERER_INVALID', $this->referer);
						}
					}
				}
			}
		}

		// If we reach here then no (valid) session exists. So we'll create a new one
		return $this->session_create();
	}

	/**
	* Create a new session
	*
	* If upon trying to start a session we discover there is nothing existing we
	* jump here. Additionally this method is called directly during login to regenerate
	* the session for the specific user. In this method we carry out a number of tasks;
	* garbage collection, (search)bot checking, banned user comparison. Basically
	* though this method will result in a new session for a specific user.
	*/
	function session_create($user_id = false, $set_admin = false, $persist_login = false, $viewonline = true)
	{
		global $SID, $_SID, $db, $config, $cache;

		$this->data = array();

		$config['session_gc'] = (int) $config['cron_sessions_interval'];
		$config['session_last_gc'] = (int) $config['cron_sessions_last_run'];

		// Garbage collection ... remove old sessions updating user information if necessary. It means (potentially) 11 queries but only infrequently
		if ($this->time_now > ($config['session_last_gc'] + $config['session_gc']))
		{
			$this->session_gc();
		}

		// Do we allow autologin on this site? No? Then override anything that may be requested here
		if (!$config['allow_autologin'])
		{
			$this->cookie_data['k'] = false;
			$persist_login = false;
		}

		$user_logged_in = false;

		// If we're presented with an autologin key we'll join against it.
		// Else if we've been passed a user_id we'll grab data based on that
		if (isset($this->cookie_data['k']) && $this->cookie_data['k'] && $this->cookie_data['u'] && !sizeof($this->data))
		{
			$sql = "SELECT u.*
				FROM " . USERS_TABLE . " u, " . SESSIONS_KEYS_TABLE . " k
				WHERE u.user_id = " . (int) $this->cookie_data['u'] . "
					AND u.user_active = 1
					AND k.user_id = u.user_id
					AND k.key_id = '" . $db->sql_escape(md5($this->cookie_data['k'])) . "'";
			$result = $db->sql_query($sql);
			$this->data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			$user_logged_in = true;
		}
		elseif (($user_id !== false) && !sizeof($this->data))
		{
			$this->cookie_data['k'] = '';
			$this->cookie_data['u'] = $user_id;

			$sql = "SELECT *
				FROM " . USERS_TABLE . "
				WHERE user_id = " . (int) $this->cookie_data['u'] . "
					AND user_active = 1";
			$result = $db->sql_query($sql);
			$this->data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			$user_logged_in = true;
		}

		// If no data was returned one or more of the following occurred:
		// Key didn't match one in the DB
		// User does not exist
		// User is inactive
		if (!sizeof($this->data) || !is_array($this->data))
		{
			$this->cookie_data['k'] = '';
			$this->cookie_data['u'] = ANONYMOUS;

			$sql = "SELECT *
				FROM " . USERS_TABLE . "
				WHERE user_id = " . (int) $this->cookie_data['u'];
			$result = $db->sql_query($sql);
			$this->data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}

		// ICY PHOENIX - BEGIN
		$this->bots_process();
		// ICY PHOENIX - END

		if ($this->data['user_id'] != ANONYMOUS)
		{
			$this->data['session_last_visit'] = !empty($this->data['user_lastvisit']) ? $this->data['user_lastvisit'] : $this->time_now;
		}
		else
		{
			// Bot user, if they have a SID in the Request URI we need to get rid of it otherwise they'll index this page with the SID, duplicate content oh my!
			if (isset($_GET['sid']) && !empty($this->data['is_bot']))
			{
				send_status_line(301, 'Moved Permanently');
				redirect(build_url(array('sid')));
			}
			$this->data['session_last_visit'] = $this->time_now;
		}

		// Force user id to be integer...
		$this->data['user_id'] = (int) $this->data['user_id'];

		// At this stage we should have a filled data array, defined cookie u and k data.
		// data array should contain recent session info if we have a real user and a recent session exists in which case session_id will also be set

		// Is user banned? Are they excluded? Won't return on ban, exists within method
		if ($this->data['user_level'] != ADMIN)
		{
			$ban_email = (($this->data['user_id'] != ANONYMOUS) && !empty($this->data['user_email'])) ? $this->data['user_email'] : false;
			$this->check_ban($this->data['user_id'], $this->ip, $ban_email);
		}

		// Mighty Gorgon: add to referers only if the user doesn't have a session... this is why this code is in session_create and not in session_begin
		if (empty($config['disable_referers']) && !empty($this->referer))
		{
			$this->process_referer();
		}

		$this->data['is_registered'] = (empty($this->data['is_bot']) && ($this->data['user_id'] != ANONYMOUS) && !empty($this->data['user_active'])) ? true : false;
		$this->data['session_logged_in'] = $this->data['is_registered'];

		// If our friend is a bot, we re-assign a previously assigned session
		if ($this->data['is_bot'])
		{
			// ICY PHOENIX - BEGIN
			// We give bots always the same session if it is not yet expired.
			$sql_fields = array();
			$sql_extra = '';
			if (!empty($this->browser))
			{
				$u_browser = trim(strtolower(substr($this->browser, 0, 254)));
				$sql_fields[] = "s.session_browser = '" . $db->sql_escape($u_browser) . "'";
			}
			if (!empty($this->ip))
			{
				$u_ip = $this->ip;
				$sql_fields[] = "s.session_ip = '" . $db->sql_escape($u_ip) . "'";
			}
			if (!empty($sql_fields))
			{
				foreach ($sql_fields as $sql_field)
				{
					$sql_extra .= (empty($sql_extra) ? " WHERE " : " AND ") . $sql_field;
				}
				if (!empty($sql_extra))
				{
					$bot_data = array();
					$sql = "SELECT s.* FROM " . SESSIONS_TABLE . " s " . $sql_extra . " AND s.session_time > " . ($this->time_now - ((int) $config['session_length'] + (int) SESSION_REFRESH));
					$result = $db->sql_query($sql);
					$bot_data = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);
					if (!empty($bot_data))
					{
						$this->data = array_merge($this->data, $bot_data);
					}
				}
			}
			// ICY PHOENIX - END

			if (!empty($this->data['session_id']))
			{
				$this->session_id = $this->data['session_id'];

				// Only update session DB a minute or so after last update or if page changes
				if ((($this->time_now - $this->data['session_time']) > SESSION_REFRESH) || ($this->update_session_page && ($this->data['session_page'] != $this->page['page'])))
				{
					$this->data['session_time'] = $this->time_now;
					$this->data['session_last_visit'] = $this->time_now;
					$this->data['is_registered'] = false;
					$this->data['session_logged_in'] = $this->data['is_registered'];
					$this->bots_session_gc(false);
				}

				// Mighty Gorgon: I'm still not sure if I want to keep 'sid=' in Icy Phoenix as well... maybe better removing it!!!
				//$SID = 'sid=';
				$SID = '';
				$_SID = '';
				return true;
			}
			else
			{
				$this->bots_session_gc(true);
			}
		}

		$session_autologin = (($this->cookie_data['k'] || $persist_login) && $this->data['is_registered']) ? true : false;
		$set_admin = ($set_admin && $this->data['is_registered']) ? true : false;

		// Create or update the session
		$sql_ary = array(
			'session_user_id' => (int) $this->data['user_id'],
			'session_logged_in' => ($this->data['session_logged_in']) ? 1 : 0,
			'session_start' => (int) $this->time_now,
			'session_last_visit' => (int) $this->data['session_last_visit'],
			'session_time' => (int) $this->time_now,
			'session_browser' => (string) trim(substr($this->browser, 0, 254)),
			'session_forwarded_for' => (string) $this->forwarded_for,
			'session_ip' => (string) $this->ip,
			'session_autologin' => ($session_autologin) ? 1 : 0,
			'session_admin' => ($set_admin) ? 1 : 0,
			'session_viewonline' => ($viewonline) ? 1 : 0,
		);

		if ($this->update_session_page)
		{
			$sql_ary['session_page'] = (string) substr($this->page['page'], 0, 254);
			$sql_ary['session_forum_id'] = $this->page['forum'];
			$sql_ary['session_topic_id'] = $this->page['topic'];
		}

		$db->sql_return_on_error(true);

		$sql = "DELETE
			FROM " . SESSIONS_TABLE . "
			WHERE session_id = '" . $db->sql_escape($this->session_id) . "'
				AND session_user_id = " . ANONYMOUS;

		if (!defined('IN_ERROR_HANDLER') && (!$this->session_id || !$db->sql_query($sql) || !$db->sql_affectedrows()))
		{
			// Limit new sessions in 1 minute period (if required)
			if (empty($this->data['session_time']) && !empty($config['active_sessions']))
			{
				//$db->sql_return_on_error(false);
				$sessions_limit = (int) $config['active_sessions'];
				$sessions_limit = ($sessions_limit < 100) ? 100 : $sessions_limit;

				$sql = "SELECT COUNT(session_id) AS sessions
					FROM " . SESSIONS_TABLE . "
					WHERE session_time >= " . ($this->time_now - SESSION_REFRESH);
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if ((int) $row['sessions'] > $sessions_limit)
				{
					send_status_line(503, 'Service Unavailable');
					trigger_error('Service Unavailable');
				}
			}
		}

		// Since we re-create the session id here, the inserted row must be unique. Therefore, we display potential errors.
		// Commented out because it will not allow forums to update correctly
		//		$db->sql_return_on_error(false);

		// Something quite important: session_page always holds the *last* page visited, except for the *first* visit.
		// We are not able to simply have an empty session_page btw, therefore we need to detect this special case.
		// If the session id is empty, we have a completely new one and will set an "identifier" that we can check later if needed.
		if (empty($this->data['session_id']))
		{
			// This is a temporary variable, only set for the very first visit
			$this->data['session_created'] = true;
		}

		$this->session_id = md5(unique_id());
		$this->data['session_id'] = $this->session_id;

		$sql_ary['session_id'] = (string) $this->session_id;
		$sql_ary['session_page'] = (string) substr($this->page['page'], 0, 254);
		$sql_ary['session_browser'] = (string) substr($this->browser, 0, 254);
		$sql_ary['session_forum_id'] = $this->page['forum'];
		$sql_ary['session_topic_id'] = $this->page['topic'];

		$sql = "INSERT INTO " . SESSIONS_TABLE . " " . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		$db->sql_return_on_error(false);

		// Regenerate autologin/persistent login key
		if ($session_autologin)
		{
			$this->set_login_key();
		}

		// refresh data
		$SID = 'sid=' . $this->session_id;
		$_SID = $this->session_id;
		$this->data = array_merge($this->data, $sql_ary);

		if (empty($this->data['is_bot']))
		{
			$this->set_cookie('u', $this->cookie_data['u'], $this->cookie_expire);
			$this->set_cookie('k', $this->cookie_data['k'], $this->cookie_expire);
			$this->set_cookie('sid', $this->session_id, $this->cookie_expire);

			$sql = "SELECT COUNT(session_id) AS sessions
					FROM " . SESSIONS_TABLE . "
					WHERE session_user_id = " . (int) $this->data['user_id'] . "
					AND session_time >= " . (int) ($this->time_now - (max($config['session_length'], $config['form_token_lifetime'])));
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			// ICY PHOENIX - BEGIN
			$sql_ary = array();
			if ($this->data['user_id'] != ANONYMOUS)
			{
				$this->data['user_totallogon'] = (int) $this->data['user_totallogon'] + 1;
				$sql_ary['user_totallogon'] = $this->data['user_totallogon'];
			}
			if (((int) $row['sessions'] <= 1) || empty($this->data['user_form_salt']))
			{
				$this->data['user_form_salt'] = unique_id();
				$sql_ary['user_form_salt'] = $this->data['user_form_salt'];
			}

			if (sizeof($sql_ary))
			{
				$sql = "UPDATE " . USERS_TABLE . " SET " . $db->sql_build_array('UPDATE', $sql_ary) . "
					WHERE user_id = " . $this->data['user_id'];
				$result = $db->sql_query($sql);
			}
			// ICY PHOENIX - END

			// Start Advanced IP Tools Pack MOD
			if (empty($config['disable_logins']) && !empty($this->data['session_logged_in']))
			{
				$sql_logins_ary = array(
					'login_userid' => $this->data['user_id'],
					'login_ip' => $this->ip,
					'login_user_agent' => substr($this->browser, 0, 254),
					'login_time' => $this->time_now,
				);
				$db->sql_return_on_error(true);
				$sql = "INSERT INTO " . LOGINS_TABLE . " " . $db->sql_build_insert_update($sql_logins_ary, true);
				$db->sql_query($sql);
				$db->sql_return_on_error(false);

				$max_logins = (int) $config['last_logins_n'];
				$limit_sql = (!empty($max_logins) && ($max_logins > 0)) ? (" LIMIT 0, " . $max_logins . " ") : "";
				$sql = "SELECT login_id FROM " . LOGINS_TABLE . "
								WHERE login_userid = " . $this->data['user_id'] . "
								ORDER BY login_id DESC" .
								$limit_sql;
				$result = $db->sql_query($sql);
				$user_logins = $db->sql_numrows($result);
				$last_logins = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);

				if (!empty($user_logins) && ($user_logins > $max_logins))
				{
					$logins_to_keep = array();
					foreach ($last_logins as $login_row)
					{
						$logins_to_keep[] = $login_row['login_id'];
					}
					$db->sql_return_on_error(true);
					$sql = "DELETE FROM " . LOGINS_TABLE . "
									WHERE login_id NOT IN (" . implode(',', $logins_to_keep) . ")
										AND login_userid = " . $this->data['user_id'];
					$db->sql_query($sql);
					$db->sql_return_on_error(false);
				}
			}
			// End Advanced IP Tools Pack MOD
		}
		else
		{
			$this->data['session_time'] = $this->time_now;
			$this->data['session_last_visit'] = $this->time_now;

			// Mighty Gorgon: I'm still not sure if I want to keep 'sid=' in Icy Phoenix as well... maybe better removing it!!!
			//$SID = 'sid=';
			$SID = '';
			$_SID = '';
		}

		return true;
	}

	/**
	* Kills a session
	*
	* This method does what it says on the tin. It will delete a pre-existing session.
	* It resets cookie information (destroying any autologin key within that cookie data)
	* and update the users information from the relevant session data. It will then
	* grab guest user information.
	*/
	function session_kill($new_session = true)
	{
		global $SID, $_SID, $db, $config;

		$sql = "DELETE FROM " . SESSIONS_TABLE . "
			WHERE session_id = '" . $db->sql_escape($this->session_id) . "'
				AND session_user_id = " . (int) $this->data['user_id'];
		$db->sql_query($sql);

		if ($this->data['user_id'] != ANONYMOUS)
		{
			// Delete existing session, update last visit info first!
			if (!isset($this->data['session_time']))
			{
				$this->data['session_time'] = time();
			}

			$sql = "UPDATE " . USERS_TABLE . "
				SET user_lastvisit = " . (int) $this->data['session_time'] . ", user_private_chat_alert = ''
				WHERE user_id = " . (int) $this->data['user_id'];
			$db->sql_query($sql);

			if ($this->cookie_data['k'])
			{
				$sql = "DELETE FROM " . SESSIONS_KEYS_TABLE . "
					WHERE user_id = " . (int) $this->data['user_id'] . "
						AND key_id = '" . $db->sql_escape(md5($this->cookie_data['k'])) . "'";
				$db->sql_query($sql);
			}

			// Reset the data array
			$this->data = array();

			$sql = "SELECT *
				FROM " . USERS_TABLE . "
				WHERE user_id = " . ANONYMOUS;
			$result = $db->sql_query($sql);
			$this->data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}

		$cookie_expire = $this->time_now - 31536000;
		$this->set_cookie('u', '', $cookie_expire);
		$this->set_cookie('k', '', $cookie_expire);
		$this->set_cookie('sid', '', $cookie_expire);
		unset($cookie_expire);

		// Mighty Gorgon: I'm still not sure if I want to keep 'sid=' in Icy Phoenix as well... maybe better removing it!!!
		//$SID = 'sid=';
		$SID = '';
		$_SID = '';
		$this->session_id = '';

		// To make sure a valid session is created we create one for the anonymous user
		if ($new_session)
		{
			$this->session_create(ANONYMOUS);
		}

		return true;
	}

	/**
	* Session garbage collection
	*
	* This looks a lot more complex than it really is. Effectively we are
	* deleting any sessions older than an admin definable limit. Due to the
	* way in which we maintain session data we have to ensure we update user
	* data before those sessions are destroyed. In addition this method
	* removes autologin key information that is older than an admin defined
	* limit.
	*/
	function session_gc()
	{
		global $db, $cache, $config;

		$batch_size = 10;

		if (!$this->time_now)
		{
			$this->time_now = time();
		}

		// Set here the desired time you would like to keep sessions...
		$session_remove_limit = 86400 * 2;
		$session_length = (int) $config['session_length'];

		// Remove old keys for users not logging in so frequently... 30 days should be fine!!!
		$max_autologin_time = !empty($config['max_autologin_time']) ? $config['max_autologin_time'] : 30;

		$sql = "DELETE FROM " . SESSIONS_KEYS_TABLE . "
			WHERE last_login < " . ($this->time_now - (86400 * (int) $max_autologin_time));
		$db->sql_query($sql);

		// Remove all sessions which are X days old from AJAX Chat table
		$sql = "DELETE FROM " . AJAX_SHOUTBOX_SESSIONS_TABLE . "
			WHERE session_time < " . (int) ($this->time_now - $session_remove_limit);
		$db->sql_query($sql);

		// Remove all sessions which are X days old from search table
		$sql = "DELETE FROM " . SEARCH_TABLE . "
			WHERE search_time < " . (int) ($this->time_now - $session_remove_limit);
		$db->sql_query($sql);

		// Delete Guest sessions which are at least X days old from sessions table (at least one day is needed for statistics)
		$sql = "DELETE FROM " . SESSIONS_TABLE . "
			WHERE session_user_id = " . ANONYMOUS . "
				AND session_time < " . (int) ($this->time_now - $session_length - $session_remove_limit);
		$db->sql_query($sql);

		// Get expired sessions, only most recent for each user
		$sql = "SELECT session_user_id, session_page, MAX(session_time) AS recent_time
			FROM " . SESSIONS_TABLE . "
			WHERE session_time < " . (int) ($this->time_now - $session_length) . "
			GROUP BY session_user_id, session_page";
		$result = $db->sql_query_limit($sql, $batch_size);

		$del_user_id = array();
		$del_sessions = 0;

		while ($row = $db->sql_fetchrow($result))
		{
			$sql = "UPDATE " . USERS_TABLE . "
				SET user_lastvisit = " . (int) $row['recent_time'] . ", user_session_page = '" . $db->sql_escape($row['session_page']) . "'
				WHERE user_id = " . (int) $row['session_user_id'];
			$db->sql_query($sql);

			$del_user_id[] = (int) $row['session_user_id'];
			$del_sessions++;
		}
		$db->sql_freeresult($result);

		if (sizeof($del_user_id))
		{
			// Delete expired sessions from more than 2 days (at least one day is needed for statistics)
			$sql = "DELETE FROM " . SESSIONS_TABLE . "
				WHERE " . $db->sql_in_set('session_user_id', $del_user_id) . "
					AND session_time < " . (int) ($this->time_now - $session_length - $session_remove_limit);
			$db->sql_query($sql);
		}

		if ($del_sessions < $batch_size)
		{
			// Less than 10 users, update gc timer ... else we want gc called again to delete other sessions
			set_config('session_last_gc', $this->time_now, true);
			set_config('cron_sessions_last_run', $this->time_now, true);

			if ($config['max_autologin_time'])
			{
				$sql = "DELETE FROM " . SESSIONS_KEYS_TABLE . "
					WHERE last_login < " . (time() - (86400 * (int) $config['max_autologin_time']));
				$db->sql_query($sql);
			}

			// only called from CRON; should be a safe workaround until the infrastructure gets going
			/*
			if (!class_exists('phpbb_captcha_factory'))
			{
				include(IP_ROOT_PATH . 'includes/captcha/captcha_factory.' . PHP_EXT);
			}
			phpbb_captcha_factory::garbage_collect($config['captcha_plugin']);
			*/
		}

		return true;
	}

	/**
	* Bots session garbage collection
	*
	* This is needed to avoid bots filling up the whole sessions table due to SID removal... this is needed because in Icy Phoenix bots don't have USER_ID but are guests!
	*/
	function bots_session_gc($clear_all = false)
	{
		global $db, $cache, $config;

		if (!$this->time_now)
		{
			$this->time_now = time();
		}

		$sql_extra = empty($clear_all) ? (" AND session_id <> '" . $db->sql_escape($this->session_id) . "' ") : '';

		if (!empty($this->browser))
		{
			$u_browser = trim(strtolower(substr($this->browser, 0, 254)));
			$sql = "DELETE FROM " . SESSIONS_TABLE . "
							WHERE session_browser = '" . $db->sql_escape($u_browser) . "'"
								. $sql_extra . "
								AND session_user_id = " . ANONYMOUS . "
								AND session_time < " . ($this->time_now - ONLINE_REFRESH);
			$db->sql_query($sql);
		}
		if (!empty($this->ip))
		{
			$sql = "DELETE FROM " . SESSIONS_TABLE . "
							WHERE session_ip = '" . $db->sql_escape($this->ip) . "'"
								. $sql_extra . "
								AND session_user_id = " . ANONYMOUS . "
								AND session_time < " . ($this->time_now - ONLINE_REFRESH);
			$db->sql_query($sql);
		}
	}

	/**
	* Confirm table garbage collection
	*/
	function confirm_gc()
	{
		global $db, $cache, $config;

		// Clean some old sessions first!
		$this->session_gc();

		// We need to limit this SQL or we may have issue when sessions table has many records
		$limit = 2000;
		// We also query only those sessions in the last two hours... if a user didn't use its code, maybe he didn't need anymore... ;-)
		$sql = "SELECT session_id FROM " . SESSIONS_TABLE . " WHERE session_time > " . (int) (time() - 7200) . " ORDER BY session_time DESC LIMIT " . (int) $limit;
		$result = $db->sql_query($sql);
		$sessions_ids = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		if (!empty($sessions_ids))
		{
			$confirm_sql = '';
			foreach ($sessions_ids as $session_id)
			{
				$confirm_sql .= (!empty($confirm_sql) ? ', ' : '') . "'" . $session_id . "'";
			}
			$sql = "DELETE FROM " . CONFIRM_TABLE . "
				WHERE session_id NOT IN (" . $confirm_sql . ")";
			$db->sql_query($sql);
		}

		return true;
	}

	/**
	* Sets a cookie
	*
	* Sets a cookie of the given name with the specified data for the given length of time. If no time is specified, a session cookie will be set.
	*
	* @param string $name Name of the cookie, will be automatically prefixed with the phpBB cookie name. track becomes [cookie_name]_track then.
	* @param string $cookiedata The data to hold within the cookie
	* @param int $cookietime The expiration time as UNIX timestamp. If 0 is provided, a session cookie is set.
	*/
	function set_cookie($name, $cookiedata, $cookietime)
	{
		global $config;

		// Old setcookie version...
		//setcookie($config['cookie_name'] . '_' . $name, $cookiedata, $cookietime, $config['cookie_path'], $config['cookie_domain'], $config['cookie_secure']);

		$name_data = rawurlencode($config['cookie_name'] . '_' . $name) . '=' . rawurlencode($cookiedata);
		$expire = gmdate('D, d-M-Y H:i:s \\G\\M\\T', $cookietime);
		$domain = (!$config['cookie_domain'] || ($config['cookie_domain'] == 'localhost') || ($config['cookie_domain'] == '127.0.0.1')) ? '' : '; domain=' . $config['cookie_domain'];

		header('Set-Cookie: ' . $name_data . (($cookietime) ? '; expires=' . $expire : '') . '; path=' . $config['cookie_path'] . $domain . ((!$config['cookie_secure']) ? '' : '; secure') . '; HttpOnly', false);
	}

	/**
	* Check for banned user
	*
	* Checks whether the supplied user is banned by id, ip or email. If no parameters
	* are passed to the method pre-existing session data is used. If $return is false
	* this routine does not return on finding a banned user, it outputs a relevant
	* message and stops execution.
	*
	* @param string|array $user_ips Can contain a string with one IP or an array of multiple IPs
	*/
	function check_ban($user_id = false, $user_ips = false, $user_email = false, $return = false)
	{
		global $config, $cache, $db, $lang;

		if (defined('IN_CHECK_BAN'))
		{
			return;
		}

		$banned = false;
		$cache_ttl = 0;
		$where_sql = array();

		$sql = "SELECT *
			FROM " . BANLIST_TABLE . "
			WHERE ";

		// Determine which entries to check, only return those
		if ($user_email === false)
		{
			$where_sql[] = "(ban_email = '')";
		}

		if ($user_ips === false)
		{
			$where_sql[] = "(ban_ip = '')";
		}

		if ($user_id === false)
		{
			$where_sql[] = "(ban_userid = 0)";
		}
		else
		{
			$cache_ttl = ($user_id == ANONYMOUS) ? 86400 : 0;
			$_sql = "(ban_userid = " . $user_id;

			if ($user_email !== false)
			{
				$_sql .= " OR ban_email <> ''";
			}

			if ($user_ips !== false)
			{
				$_sql .= " OR ban_ip <> ''";
			}

			$_sql .= ")";

			$where_sql[] = $_sql;
		}

		$sql .= (sizeof($where_sql)) ? implode(" AND ", $where_sql) : "";
		$result = ((defined('CACHE_BAN_INFO') && CACHE_BAN_INFO) || !empty($cache_ttl)) ? $db->sql_query($sql, $cache_ttl, 'ban_', USERS_CACHE_FOLDER) : $db->sql_query($sql);

		$ban_triggered_by = 'user';
		while ($row = $db->sql_fetchrow($result))
		{
			if (($row['ban_userid'] == ANONYMOUS) && ($row['ban_ip'] == '') && ($row['ban_email'] == null))
			{
				$sql = "DELETE FROM " . BANLIST_TABLE . " WHERE ban_userid = '" . ANONYMOUS . "'";
				$db->sql_query($sql);
				$db->clear_cache('ban_', USERS_CACHE_FOLDER);
				continue;
			}

			if (!empty($row['ban_end']) && ($row['ban_end'] <= time()))
			{
				$sql = "DELETE FROM " . BANLIST_TABLE . " WHERE ban_id = '" . $row['ban_id'] . "'";
				$db->sql_query($sql);
				$db->clear_cache('ban_', USERS_CACHE_FOLDER);
				continue;
			}

			$ip_banned = false;
			if (!empty($row['ban_ip']))
			{
				if (!is_array($user_ips))
				{
					$ip_banned = preg_match('#^' . str_replace('\*', '.*?', preg_quote($row['ban_ip'], '#')) . '$#i', $user_ips);
				}
				else
				{
					foreach ($user_ips as $user_ip)
					{
						if (preg_match('#^' . str_replace('\*', '.*?', preg_quote($row['ban_ip'], '#')) . '$#i', $user_ip))
						{
							$ip_banned = true;
							break;
						}
					}
				}
			}

			if ((!empty($row['ban_userid']) && (intval($row['ban_userid']) == $user_id)) || $ip_banned || (!empty($row['ban_email']) && preg_match('#^' . str_replace('\*', '.*?', preg_quote($row['ban_email'], '#')) . '$#i', $user_email)))
			{
				$banned = true;
				$ban_row = $row;

				if (!empty($row['ban_userid']) && (intval($row['ban_userid']) == $user_id))
				{
					$ban_triggered_by = 'user';
				}
				elseif ($ip_banned)
				{
					$ban_triggered_by = 'ip';
				}
				else
				{
					$ban_triggered_by = 'email';
				}
				break;

			}
		}
		$db->sql_freeresult($result);

		if ($banned && !$return)
		{
			global $template;

			// The false here is needed, else the user is able to circumvent the ban.
			$this->session_kill(false);

			// We need to make sure we have at least the basic lang files included...
			if (empty($lang))
			{
				setup_basic_lang();
			}

			// A very special case... we are within the cron script which is not supposed to print out the ban message... show blank page
			if (defined('IN_CRON'))
			{
				garbage_collection();
				exit_handler();
				exit;
			}

			if (($ban_info['ban_pub_reason_mode'] == '0') || !isset($ban_info['ban_pub_reason_mode']))
			{
				$reason = $lang['You_been_banned'];
			}
			elseif ($ban_info['ban_pub_reason_mode'] == '1')
			{
				$reason = str_replace("\n", '<br />', $ban_info['ban_priv_reason']);
			}
			elseif ($ban_info['ban_pub_reason_mode'] == '2')
			{
				$reason = str_replace("\n", '<br />', $ban_info['ban_pub_reason']);
			}

			$reason = empty($reason) ? $lang['You_been_banned'] : $reason;
			message_die(CRITICAL_MESSAGE, $reason);
		}

		return ($banned && !empty($reason)) ? $reason : $banned;
	}

	/**
	* Check if ip is blacklisted
	* This should be called only where absolutly necessary
	*
	* Only IPv4 (rbldns does not support AAAA records/IPv6 lookups)
	*
	* @author satmd (from the php manual)
	* @param string $mode register/post - spamcop for example is ommitted for posting
	* @return false if ip is not blacklisted, else an array([checked server], [lookup])
	*/
	function check_dnsbl($mode, $ip = false)
	{
		global $config;

		if ($ip === false)
		{
			$ip = $this->ip;
		}

		$dnsbl_check = array(
			'sbl.spamhaus.org' => 'http://www.spamhaus.org/query/bl?ip=',
		);

		if ($mode == 'register')
		{
			$dnsbl_check['bl.spamcop.net'] = 'http://spamcop.net/bl.shtml?';
		}

		if ($ip)
		{
			$quads = explode('.', $ip);
			$reverse_ip = $quads[3] . '.' . $quads[2] . '.' . $quads[1] . '.' . $quads[0];

			// Need to be listed on all servers...
			$listed = true;
			$info = array();

			foreach ($dnsbl_check as $dnsbl => $lookup)
			{
				if (phpbb_checkdnsrr($reverse_ip . '.' . $dnsbl . '.', 'A') === true)
				{
					$info = array($dnsbl, $lookup . $ip);
				}
				else
				{
					$listed = false;
				}
			}

			if ($listed)
			{
				return $info;
			}
		}

		return false;
	}

	/**
	* Set/Update a persistent login key
	*
	* This method creates or updates a persistent session key. When a user makes
	* use of persistent (formerly auto-) logins a key is generated and stored in the
	* DB. When they revisit with the same key it's automatically updated in both the
	* DB and cookie. Multiple keys may exist for each user representing different
	* browsers or locations. As with _any_ non-secure-socket no passphrase login this
	* remains vulnerable to exploit.
	*/
	function set_login_key($user_id = false, $key = false, $user_ip = false)
	{
		global $config, $db;

		$user_id = ($user_id === false) ? $this->data['user_id'] : $user_id;
		$user_ip = ($user_ip === false) ? $this->ip : $user_ip;
		$key = ($key === false) ? (($this->cookie_data['k']) ? $this->cookie_data['k'] : false) : $key;

		$key_id = unique_id(hexdec(substr($this->session_id, 0, 8)));

		$sql_ary = array(
			'key_id' => (string) md5($key_id),
			'last_ip' => (string) $this->ip,
			'last_login' => (int) time()
		);

		if (!$key)
		{
			$sql_ary += array(
				'user_id' => (int) $user_id
			);
		}

		if ($key)
		{
			$sql = "UPDATE " . SESSIONS_KEYS_TABLE . "
				SET " . $db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE user_id = " . (int) $user_id . "
					AND key_id = '" . $db->sql_escape(md5($key)) . "'";
		}
		else
		{
			$sql = "INSERT INTO " . SESSIONS_KEYS_TABLE . " " . $db->sql_build_array('INSERT', $sql_ary);
		}
		$db->sql_query($sql);

		$this->cookie_data['k'] = $key_id;

		return false;
	}

	/**
	* Reset all login keys for the specified user
	*
	* This method removes all current login keys for a specified (or the current)
	* user. It will be called on password change to render old keys unusable
	*/
	function reset_login_keys($user_id = false)
	{
		global $config, $db;

		$user_id = ($user_id === false) ? (int) $this->data['user_id'] : (int) $user_id;

		$sql = "DELETE FROM " . SESSIONS_KEYS_TABLE . "
			WHERE user_id = " . (int) $user_id;
		$db->sql_query($sql);

		// If the user is logged in, update last visit info first before deleting sessions
		$sql = "SELECT session_time, session_page
			FROM " . SESSIONS_TABLE . "
			WHERE session_user_id = " . (int) $user_id . "
			ORDER BY session_time DESC";
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			$sql = "UPDATE " . USERS_TABLE . "
				SET user_lastvisit = " . (int) $row['session_time'] . ", user_session_page = '" . $db->sql_escape($row['session_page']) . "'
				WHERE user_id = " . (int) $user_id;
			$db->sql_query($sql);
		}

		// Let's also clear any current sessions for the specified user_id
		// If it's the current user then we'll leave this session intact
		$sql_where = 'session_user_id = ' . (int) $user_id;
		$sql_where .= ($user_id === (int) $this->data['user_id']) ? " AND session_id <> '" . $db->sql_escape($this->session_id) . "'" : '';

		$sql = "DELETE FROM " . SESSIONS_TABLE . "
			WHERE $sql_where";
		$db->sql_query($sql);

		// We're changing the password of the current user and they have a key
		// Lets regenerate it to be safe
		if ($user_id === (int) $this->data['user_id'] && $this->cookie_data['k'])
		{
			$this->set_login_key($user_id);
		}
	}

	/**
	* Check if the request originated from the same page.
	* @param bool $check_script_path If true, the path will be checked as well
	*/
	function validate_referer($check_script_path = false)
	{
		global $config;

		// no referer - nothing to validate, user's fault for turning it off (we only check on POST; so meta can't be the reason)
		if (empty($this->referer) || empty($this->host))
		{
			return true;
		}

		$host = htmlspecialchars($this->host);
		$ref = substr($this->referer, strpos($this->referer, '://') + 3);

		if (!(stripos($ref, $host) === 0) && (!$config['force_server_vars'] || !(stripos($ref, $config['server_name']) === 0)))
		{
			return false;
		}
		elseif ($check_script_path && (rtrim($this->page['root_script_path'], '/') !== ''))
		{
			$ref = substr($ref, strlen($host));
			$server_port = (!empty($_SERVER['SERVER_PORT'])) ? (int) $_SERVER['SERVER_PORT'] : (int) getenv('SERVER_PORT');

			if (($server_port !== 80) && ($server_port !== 443) && (stripos($ref, ":$server_port") === 0))
			{
				$ref = substr($ref, strlen(":$server_port"));
			}

			if (!(stripos(rtrim($ref, '/'), rtrim($this->page['root_script_path'], '/')) === 0))
			{
				return false;
			}
		}

		return true;
	}

	function unset_admin()
	{
		global $db;
		$sql = "UPDATE " . SESSIONS_TABLE . "
			SET session_admin = 0
			WHERE session_id = '" . $db->sql_escape($this->session_id) . "'";
		$db->sql_query($sql);
	}


	/**
	* Bots check...
	*/
	function bots_process()
	{
		global $config;

		if (!empty($this->data))
		{
			$this->data['is_bot'] = false;
			$this->data['bot_id'] = false;
			if ($this->data['user_id'] == ANONYMOUS)
			{
				$bot_name_tmp = bots_parse($this->ip, $config['bots_color'], $this->browser, true);
				$this->data['bot_id'] = $bot_name_tmp['name'];
				if ($this->data['bot_id'] !== false)
				{
					$this->data['is_bot'] = true;
					bots_table_update($bot_name_tmp['id']);
				}
			}
		}
	}

	/**
	* Process referers
	*/
	function process_referer()
	{
		global $db, $cache, $config;

		if (!empty($this->referer))
		{
			$this_page = $this->page;
			$this_page_url = preg_replace('/(\?)?(&amp;|&)?sid=[a-z0-9]+/', '', $this_page['page_full']);

			$ref_url = $this->referer;
			$ref_url_array = parse_url($ref_url);
			$ref_host = $ref_url_array['host'];

			$ref_process = true;

			if (strpos(strtolower($ref_url), strtolower($this->host . $config['script_path'])) !== false)
			{
				$ref_process = false;
			}

			if (strpos(strtolower($ref_host), str_replace('/', '', strtolower($config['server_name']))) !== false)
			{
				$ref_process = false;
			}

			if (!empty($ref_process))
			{
				include(IP_ROOT_PATH . 'includes/blacklist.' . PHP_EXT);
				if (!empty($blacklist['host']))
				{
					foreach ($blacklist['host'] as $blacklist_entry)
					{
						if (strpos(strtolower($ref_host), strtolower($blacklist_entry)) !== false)
						{
							$ref_process = false;
							break;
						}
					}
				}

				if (!empty($ref_process))
				{
					$sql_where_extra = !empty($this_page_url) ? (" AND t_url = '" . $db->sql_escape($this_page_url) . "' ") : "";
					$sql = "SELECT url FROM " . REFERERS_TABLE . " WHERE url = '" . $db->sql_escape($ref_url) . "'" . $sql_where_extra . " LIMIT 1";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);

					if (empty($row))
					{
						$ref_insert_array = array(
							'host' => $ref_host,
							'url' => $ref_url,
							't_url' => $this_page_url,
							'ip' => $this->ip,
							'hits' => 1,
							'firstvisit' => time(),
							'lastvisit' => time(),
						);

						$sql = "INSERT INTO " . REFERERS_TABLE . " " . $db->sql_build_insert_update($ref_insert_array, true);
						$result = $db->sql_query($sql);
					}
					else
					{
						$sql = "UPDATE " . REFERERS_TABLE . "
							SET hits = hits + 1, lastvisit = " . time() . ", ip = '" . $db->sql_escape($user_ip) . "'
							WHERE url = '" . $db->sql_escape($ref_url) . "'" . $sql_where_extra;
						$result = $db->sql_query($sql);
					}
				}
			}
		}
	}

// UPI2DB - BEGIN
	/**
	* UPI2DB
	*/
	function upi2db()
	{
		global $config;

		$this->data['upi2db_access'] = false;
		if (!$config['board_disable'] && $this->data['session_logged_in'] && $config['upi2db_on'])
		{
			$this->data['upi2db_access'] = check_upi2db_on($this->data);
			if ($this->data['upi2db_access'] != false)
			{
				$this->data['always_read'] = select_always_read($this->data);
				$this->data['auth_forum_id'] = auth_forum_read($this->data);
				sync_database($this->data);
			}
		}
	}
// UPI2DB - END

}


/**
* Base user class
*
* This is the overarching class which contains (through session extend)
* all methods utilised for user functionality during a session.
*
* @package phpBB3
*/
class user extends session
{
	var $lang = array();
	var $help = array();
	var $theme = array();
	var $date_format;
	var $timezone;
	var $dst;

	var $lang_name = false;
	var $lang_id = false;
	var $lang_path;
	var $img_lang;
	var $img_array = array();

	// Able to add new options (up to id 31)
	var $keyoptions = array(
		'viewimg' => 0,
		'viewflash' => 1,
		'viewsmilies' => 2,
		'viewsigs' => 3,
		'viewavatars' => 4,
		'viewcensors' => 5,
		'attachsig' => 6,
		'bbcode' => 8,
		'smilies' => 9,
		'popuppm' => 10,
		'sig_bbcode' => 15,
		'sig_smilies' => 16,
		'sig_links' => 17
	);

	/**
	* Constructor to set the lang path
	*/
	function user()
	{
		$this->lang_path = IP_ROOT_PATH . 'language/';
	}

	/**
	* Function to set custom language path (able to use directory outside of phpBB)
	*
	* @param string $lang_path New language path used.
	* @access public
	*/
	function set_custom_lang_path($lang_path)
	{
		$this->lang_path = $lang_path;

		if (substr($this->lang_path, -1) != '/')
		{
			$this->lang_path .= '/';
		}
	}

	/**
	* Setup basic user-specific items (style, language, ...)
	*/
	function setup($lang_set = false, $style = false)
	{
		global $db, $cache, $config, $auth, $template;
		// We need $lang declared as global to make sure we do not miss extra $lang vars added using this function
		global $theme, $images, $lang, $nav_separator;
		global $class_settings, $tree;

		// Get all settings
		$class_settings->setup_settings();

		// Mighty Gorgon - Change Lang - BEGIN
		$test_language = request_var(LANG_URL, '');
		if (!empty($test_language))
		{
			$test_language = str_replace(array('.', '/'), '', urldecode($test_language));
			$config['default_lang'] = file_exists(@phpbb_realpath($this->lang_path . 'lang_' . basename($test_language) . '/lang_main.' . PHP_EXT)) ? $test_language : $config['default_lang'];
			$this->set_cookie('lang', $config['default_lang'], $user->cookie_expire);
		}
		else
		{
			if (isset($_COOKIE[$config['cookie_name'] . '_lang']) && file_exists(@phpbb_realpath($this->lang_path . 'lang_' . basename($_COOKIE[$config['cookie_name'] . '_lang']) . '/lang_main.' . PHP_EXT)))
			{
				$config['default_lang'] = $_COOKIE[$config['cookie_name'] . '_lang'];
			}
		}
		// Mighty Gorgon - Change Lang - END

		if ($this->data['user_id'] != ANONYMOUS)
		{
			$this->lang_name = ((file_exists($this->lang_path . 'lang_' . basename($this->data['user_lang']) . '/lang_main.' . PHP_EXT)) ? basename($this->data['user_lang']) : basename($config['default_lang']));
			$this->date_format = $this->data['user_dateformat'];
			$this->timezone = $this->data['user_timezone'] * 3600;
			$this->dst = $this->data['user_dst'] * 3600;

			$config['board_timezone'] = !empty($this->data['user_timezone']) ? $this->data['user_timezone'] : $config['board_timezone'];
			$config['default_dateformat'] = !empty($this->data['user_dateformat']) ? $this->data['user_dateformat'] : $config['default_dateformat'];

			$config['topics_per_page'] = !empty($this->data['user_topics_per_page']) ? $this->data['user_topics_per_page'] : $config['topics_per_page'];
			$config['posts_per_page'] = !empty($this->data['user_posts_per_page']) ? $this->data['user_posts_per_page'] : $config['posts_per_page'];
			$config['hot_threshold'] = !empty($this->data['user_hot_threshold']) ? $this->data['user_hot_threshold'] : $config['hot_threshold'];

			// Store CMS AUTH - BEGIN
			if (empty($this->data['user_cms_auth']))
			{
				$auth_array = array();
				$auth_to_get_array = array('cmsl_admin', 'cmss_admin', 'cmsb_admin');
				foreach ($auth_to_get_array as $auth_to_get)
				{
					$auth_getf = $auth->acl_getf($auth_to_get, true);
					foreach ($auth_getf as $auth_id => $auth_value)
					{
						$auth_array[$auth_to_get][$auth_id] = $auth_value[$auth_to_get];
					}
				}

				$this->data['user_cms_auth'] = $auth_array;
				$sql = "UPDATE " . USERS_TABLE . "
					SET user_cms_auth = '" . $db->sql_escape(serialize($this->data['user_cms_auth'])) . "'
					WHERE user_id = " . $this->data['user_id'];
				$db->sql_query($sql);
			}
			else
			{
				$this->data['user_cms_auth'] = unserialize($this->data['user_cms_auth']);
			}
			// Store CMS AUTH - END
		}
		else
		{
			$this->lang_name = basename($config['default_lang']);
			$this->date_format = $config['default_dateformat'];
			$this->timezone = $config['board_timezone'] * 3600;
			$this->dst = $config['board_dst'] * 3600;
		}

		// If we've had to change the value in any way then let's write it back to the database before we go any further since it means there is something wrong with it
		if (($this->data['user_id'] != ANONYMOUS) && ($this->data['user_lang'] !== $this->lang_name) && file_exists($this->lang_path . 'lang_' . basename($this->lang_name) . '/lang_main.' . PHP_EXT))
		{
			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_lang = '" . $db->sql_escape($this->lang_name) . "'
				WHERE user_lang = '" . $this->data['user_lang'] . "'";
			$result = $db->sql_query($sql);
			$this->data['user_lang'] = $this->lang_name;
		}
		elseif (($this->data['user_id'] === ANONYMOUS) && ($config['default_lang'] !== $this->lang_name) && file_exists($this->lang_path . 'lang_' . basename($this->lang_name) . '/lang_main.' . PHP_EXT))
		{
			$sql = 'UPDATE ' . CONFIG_TABLE . "
				SET config_value = '" . $db->sql_escape($this->lang_name) . "'
				WHERE config_name = 'default_lang'";
			$result = $db->sql_query($sql);
		}
		$config['default_lang'] = $this->lang_name;

		// We include common language file here to not load it every time a custom language file is included
		$lang = &$this->lang;

		setup_basic_lang();
		$this->add_lang($lang_set);
		unset($lang_set);

		$nav_separator = empty($nav_separator) ? (empty($lang['Nav_Separator']) ? '&nbsp;&raquo;&nbsp;' : $lang['Nav_Separator']) : $nav_separator;

		if (empty($tree['auth']))
		{
			get_user_tree($this->data);
		}

		// MG Logs - BEGIN
		if ($config['mg_log_actions'] || $config['db_log_actions'])
		{
			include(IP_ROOT_PATH . 'includes/log_http_cmd.' . PHP_EXT);
		}
		// MG Logs - END

		// UPI2DB - BEGIN
		if (!defined('IN_CMS') && $this->data['upi2db_access'])
		{
			if (!defined('UPI2DB_UNREAD'))
			{
				$this->data['upi2db_unread'] = upi2db_unread();
			}
		}
		else
		{
			$this->data['upi2db_unread'] = array();
		}
		// UPI2DB - END

		// Mighty Gorgon Edit
		// DISABLED BY MG
		/*
		//if (!empty($_GET['style']) && $auth->acl_get('a_styles') && !defined('IN_ADMIN') && !defined('IN_CMS'))
		if (!empty($_GET['style']) && !defined('IN_ADMIN') && !defined('IN_CMS'))
		{
			global $SID, $_EXTRA_URL;

			$style = request_var(STYLE_URL, 0);
			$SID .= '&amp;' . STYLE_URL . '=' . $style;
			$_EXTRA_URL = array(STYLE_URL . '=' . $style);
		}
		else
		{
			// Set up style
			$style = ($style) ? $style : ((!$config['override_user_style']) ? $this->data['user_style'] : $config['default_style']);
		}
		*/

		// Call phpbb_user_session_handler() in case external application want to "bend" some variables or replace classes...
		// After calling it we continue script execution...
		phpbb_user_session_handler();

		// If this function got called from the error handler we are finished here.
		if (defined('IN_ERROR_HANDLER'))
		{
			return;
		}

		// Disable board if the install/ directory is still present
		// For the brave development army we do not care about this, else we need to comment out this everytime we develop locally
		// DISABLED BY MG
		/*
		if (!defined('DEBUG_EXTRA') && !defined('IN_ADMIN') && !defined('IN_CMS') && !defined('IN_INSTALL') && !defined('IN_LOGIN') && file_exists(IP_ROOT_PATH . 'install') && !is_file(IP_ROOT_PATH . 'install'))
		{
			// Adjust the message slightly according to the permissions
			if ($auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_'))
			{
				$message = 'REMOVE_INSTALL';
			}
			else
			{
				$message = (!empty($config['board_disable_msg'])) ? $config['board_disable_msg'] : 'BOARD_DISABLE';
			}
			trigger_error($message);
		}
		*/

		// Is board disabled and user not an admin or moderator?
		// DISABLED BY MG
		/*
		if ($config['board_disable'] && !defined('IN_LOGIN') && !$auth->acl_gets('a_', 'm_') && !$auth->acl_getf_global('m_'))
		{
			if ($this->data['is_bot'])
			{
				send_status_line(503, 'Service Unavailable');
			}

			$message = (!empty($config['board_disable_msg'])) ? $config['board_disable_msg'] : 'BOARD_DISABLE';
			trigger_error($message);
		}
		*/

		// Is load exceeded?
		// DISABLED BY MG
		/*
		if ($config['limit_load'] && $this->load !== false)
		{
			if ($this->load > floatval($config['limit_load']) && !defined('IN_LOGIN') && !defined('IN_ADMIN'))
			{
				// Set board disabled to true to let the admins/mods get the proper notification
				$config['board_disable'] = '1';

				if (!$auth->acl_gets('a_', 'm_') && !$auth->acl_getf_global('m_'))
				{
					if ($this->data['is_bot'])
					{
						send_status_line(503, 'Service Unavailable');
					}
					trigger_error('BOARD_UNAVAILABLE');
				}
			}
		}
		*/

		// DISABLED BY MG
		/*
		if (isset($this->data['session_viewonline']))
		{
			// Make sure the user is able to hide his session
			if (!$this->data['session_viewonline'])
			{
				// Reset online status if not allowed to hide the session...
				if (!$auth->acl_get('u_hideonline'))
				{
					$sql = 'UPDATE ' . SESSIONS_TABLE . '
						SET session_viewonline = 1
						WHERE session_user_id = ' . $this->data['user_id'];
					$db->sql_query($sql);
					$this->data['session_viewonline'] = 1;
				}
			}
			elseif (!$this->data['user_allow_viewonline'])
			{
				// the user wants to hide and is allowed to -> cloaking device on.
				if ($auth->acl_get('u_hideonline'))
				{
					$sql = 'UPDATE ' . SESSIONS_TABLE . '
						SET session_viewonline = 0
						WHERE session_user_id = ' . $this->data['user_id'];
					$db->sql_query($sql);
					$this->data['session_viewonline'] = 0;
				}
			}
		}
		*/

		// Set up style
		$current_default_style = $config['default_style'];
		$change_style = false;

		$is_mobile = is_mobile();
		// For debugging purpose you can force this to true
		//$this->data['is_mobile'] = true;

		// We need to store somewhere if the user has the mobile style enabled... so we can output a link to switch between mobile style and norma style
		$this->data['mobile_style'] = false;
		$disable_mobile_style = false;

		// MOBILE STYLE DISABLING - BEGIN
		// Let's check if the user wants to disable the mobile style
		if(isset($_GET['mob']))
		{
			$mob_get = (isset($_GET['mob']) && (intval($_GET['mob']) == 0)) ? 0 : 1;
			$_GET['mob'] = $mob_get;
			$_COOKIE[$config['cookie_name'] . '_mob'] = $mob_get;
			$this->set_cookie('mob', $mob_get, $user->cookie_expire);

			if (empty($mob_get))
			{
				$disable_mobile_style = true;
			}
		}

		$mob_cok = (isset($_COOKIE[$config['cookie_name'] . '_mob']) && (intval($_COOKIE[$config['cookie_name'] . '_mob']) == 0)) ? false : true;
		if (empty($mob_cok))
		{
			$disable_mobile_style = true;
		}
		// MOBILE STYLE DISABLING - END

		if (empty($disable_mobile_style) && !empty($this->data['is_mobile']) && !defined('IN_CMS') && !defined('IN_ADMIN'))
		{
			$this->data['mobile_style'] = true;
			$_COOKIE[$config['cookie_name'] . '_mob'] = 1;
			$this->set_cookie('mob', 1, $user->cookie_expire);
			$theme = setup_mobile_style();
		}
		else
		{
			if (empty($config['override_user_style']))
			{
				// Mighty Gorgon - Change Style - BEGIN
				// Check cookie as well!!!
				$test_style = request_var(STYLE_URL, 0);
				if ($test_style > 0)
				{
					$config['default_style'] = urldecode($test_style);
					$config['default_style'] = (check_style_exists($config['default_style']) == false) ? $current_default_style : $config['default_style'];
					$this->set_cookie('style', $config['default_style'], $user->cookie_expire);
					$change_style = true;
				}
				else
				{
					if (isset($_COOKIE[$config['cookie_name'] . '_style']) && (check_style_exists($_COOKIE[$config['cookie_name'] . '_style']) != false))
					{
						$config['default_style'] = $_COOKIE[$config['cookie_name'] . '_style'];
					}
				}
				// Mighty Gorgon - Change Style - END

				$style = (($this->data['user_id'] != ANONYMOUS) && ($this->data['user_style'] > 0) && empty($change_style)) ? $this->data['user_style'] : $config['default_style'];

				if ($theme = setup_style($style, $current_default_style))
				{
					if (($this->data['user_id'] != ANONYMOUS) && !empty($change_style))
					{
						// user logged in --> save new style ID in user profile
						$sql = "UPDATE " . USERS_TABLE . "
							SET user_style = " . $theme['themes_id'] . "
							WHERE user_id = " . $this->data['user_id'];
						$db->sql_query($sql);
						$this->data['user_style'] = $theme['themes_id'];
					}
					return;
				}
			}

			$theme = setup_style($config['default_style'], $current_default_style);
		}

		return;
	}

	/**
	* More advanced language substitution
	* Function to mimic sprintf() with the possibility of using phpBB's language system to substitute nullar/singular/plural forms.
	* Params are the language key and the parameters to be substituted.
	* This function/functionality is inspired by SHS` and Ashe.
	*
	* Example call: <samp>$user->lang('NUM_POSTS_IN_QUEUE', 1);</samp>
	*/
	function lang()
	{
		$args = func_get_args();
		$key = $args[0];

		if (is_array($key))
		{
			$lang = &$this->lang[array_shift($key)];

			foreach ($key as $_key)
			{
				$lang = &$lang[$_key];
			}
		}
		else
		{
			$lang = &$this->lang[$key];
		}

		// Return if language string does not exist
		if (!isset($lang) || (!is_string($lang) && !is_array($lang)))
		{
			return $key;
		}

		// If the language entry is a string, we simply mimic sprintf() behaviour
		if (is_string($lang))
		{
			if (sizeof($args) == 1)
			{
				return $lang;
			}

			// Replace key with language entry and simply pass along...
			$args[0] = $lang;
			return call_user_func_array('sprintf', $args);
		}

		// It is an array... now handle different nullar/singular/plural forms
		$key_found = false;

		// We now get the first number passed and will select the key based upon this number
		for ($i = 1, $num_args = sizeof($args); $i < $num_args; $i++)
		{
			if (is_int($args[$i]))
			{
				$numbers = array_keys($lang);

				foreach ($numbers as $num)
				{
					if ($num > $args[$i])
					{
						break;
					}

					$key_found = $num;
				}
			}
		}

		// Ok, let's check if the key was found, else use the last entry (because it is mostly the plural form)
		if ($key_found === false)
		{
			$numbers = array_keys($lang);
			$key_found = end($numbers);
		}

		// Use the language string we determined and pass it to sprintf()
		$args[0] = $lang[$key_found];
		return call_user_func_array('sprintf', $args);
	}

	/**
	* Add Language Items - use_db and use_help are assigned where needed (only use them to force inclusion)
	*
	* @param mixed $lang_set specifies the language entries to include
	* @param bool $use_db internal variable for recursion, do not use
	* @param bool $use_help internal variable for recursion, do not use
	*
	* Examples:
	* <code>
	* $lang_set = array('posting', 'help' => 'faq');
	* $lang_set = array('posting', 'viewtopic', 'help' => array('bbcode', 'faq'))
	* $lang_set = array(array('posting', 'viewtopic'), 'help' => array('bbcode', 'faq'))
	* $lang_set = 'posting'
	* $lang_set = array('help' => 'faq', 'db' => array('help:faq', 'posting'))
	* </code>
	*/
	function add_lang($lang_set, $use_db = false, $use_help = false)
	{
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
				elseif ($key == 'help')
				{
					$this->add_lang($lang_file, $use_db, true);
				}
				elseif (!is_array($lang_file))
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
		elseif ($lang_set)
		{
			$this->set_lang($this->lang, $this->help, $lang_set, $use_db, $use_help);
		}
	}

	/**
	* Set language entry (called by add_lang)
	* @access private
	*/
	function set_lang(&$lang, &$help, $lang_file, $use_db = false, $use_help = false)
	{
		// In Icy Phoenix we still need to keep this global assignment for backward compatibility
		global $lang;

		// Make sure the language name is set (if the user setup did not happen it is not set)
		if (!$this->lang_name)
		{
			global $config;
			$this->lang_name = basename($config['default_lang']);
		}

		// $lang == $this->lang
		// $help == $this->help
		// - add appropriate variables here, name them as they are used within the language file...
		if (!$use_db)
		{
			if ($use_help && (strpos($lang_file, '/') !== false))
			{
				$language_filename = $this->lang_path . 'lang_' . $this->lang_name . '/' . substr($lang_file, 0, stripos($lang_file, '/') + 1) . 'help_' . substr($lang_file, stripos($lang_file, '/') + 1) . '.' . PHP_EXT;
			}
			else
			{
				$language_filename = $this->lang_path . 'lang_' . $this->lang_name . '/' . (($use_help) ? 'help_' : '') . $lang_file . '.' . PHP_EXT;
			}

			if (!file_exists($language_filename))
			{
				global $config;

				if ($this->lang_name == 'english')
				{
					// The user's selected language is missing the file, the board default's language is missing the file, and the file doesn't exist in /en.
					$language_filename = str_replace($this->lang_path . 'lang_' . 'english', $this->lang_path . 'lang_' . $this->data['user_lang'], $language_filename);
					trigger_error('Language file ' . $language_filename . ' couldn\'t be opened.', E_USER_ERROR);
				}
				elseif ($this->lang_name == basename($config['default_lang']))
				{
					// Fall back to the English Language
					$this->lang_name = 'english';
					$this->set_lang($lang, $help, $lang_file, $use_db, $use_help);
				}
				elseif ($this->lang_name == $this->data['user_lang'])
				{
					// Fall back to the board default language
					$this->lang_name = basename($config['default_lang']);
					$this->set_lang($lang, $help, $lang_file, $use_db, $use_help);
				}

				// Reset the lang name
				$this->lang_name = (file_exists($this->lang_path . 'lang_' . $this->data['user_lang'] . '/lang_main.' . PHP_EXT)) ? $this->data['user_lang'] : basename($config['default_lang']);
				return;
			}

			// Do not suppress error if in DEBUG_EXTRA mode
			$include_result = (defined('DEBUG_EXTRA')) ? (include($language_filename)) : (@include($language_filename));

			if ($include_result === false)
			{
				trigger_error('Language file ' . $language_filename . ' couldn\'t be opened.', E_USER_ERROR);
			}
		}
		elseif ($use_db)
		{
			// Get Database Language Strings
			// Put them into $lang if nothing is prefixed, put them into $help if help: is prefixed
			// For example: help:faq, posting
		}
	}

	/**
	* Format user date
	*
	* @param int $gmepoch unix timestamp
	* @param string $format date format in date() notation. | used to indicate relative dates, for example |d m Y|, h:i is translated to Today, h:i.
	* @param bool $forcedate force non-relative date format.
	*
	* @return mixed translated date
	*/
	function format_date($gmepoch, $format = false, $forcedate = false)
	{
		static $midnight;
		static $date_cache;

		$format = (!$format) ? $this->date_format : $format;
		$now = time();
		$delta = $now - $gmepoch;

		if (!isset($date_cache[$format]))
		{
			// Is the user requesting a friendly date format (i.e. 'Today 12:42')?
			$date_cache[$format] = array(
				'is_short' => strpos($format, '|'),
				'format_short' => substr($format, 0, strpos($format, '|')) . '||' . substr(strrchr($format, '|'), 1),
				'format_long' => str_replace('|', '', $format),
				'lang' => $this->lang['datetime'],
			);

			// Short representation of month in format? Some languages use different terms for the long and short format of May
			if (((strpos($format, '\M') === false) && (strpos($format, 'M') !== false)) || ((strpos($format, '\r') === false) && (strpos($format, 'r') !== false)))
			{
				$months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
				foreach ($months as $month)
				{
					$date_cache[$format]['lang'][$month] = $this->lang['datetime'][$month . '_short'];
				}
			}
		}

		// Zone offset
		$zone_offset = $this->timezone + $this->dst;

		// Show date <= 1 hour ago as 'xx min ago' but not greater than 60 seconds in the future
		// A small tolerence is given for times in the future but in the same minute are displayed as '< than a minute ago'
		if (($delta <= 3600) && ($delta > -60) && (($delta >= -5) || (($now / 60) % 60) == (($gmepoch / 60) % 60)) && ($date_cache[$format]['is_short'] !== false) && !$forcedate && isset($this->lang['datetime']['AGO']))
		{
			return $this->lang(array('datetime', 'AGO'), max(0, (int) floor($delta / 60)));
		}

		if (!$midnight)
		{
			list($d, $m, $y) = explode(' ', gmdate('j n Y', time() + $zone_offset));
			$midnight = gmmktime(0, 0, 0, $m, $d, $y) - $zone_offset;
		}

		if (($date_cache[$format]['is_short'] !== false) && !$forcedate && !(($gmepoch < ($midnight - 86400)) || ($gmepoch > ($midnight + 172800))))
		{
			$day = false;

			if ($gmepoch > ($midnight + 86400))
			{
				$day = 'TOMORROW';
			}
			elseif ($gmepoch > $midnight)
			{
				$day = 'TODAY';
			}
			elseif ($gmepoch > ($midnight - 86400))
			{
				$day = 'YESTERDAY';
			}

			if ($day !== false)
			{
				return str_replace('||', $this->lang['datetime'][$day], strtr(@gmdate($date_cache[$format]['format_short'], $gmepoch + $zone_offset), $date_cache[$format]['lang']));
			}
		}

		return strtr(@gmdate($date_cache[$format]['format_long'], $gmepoch + $zone_offset), $date_cache[$format]['lang']);
	}

	/**
	* Get option bit field from user options.
	*
	* @param int $key option key, as defined in $keyoptions property.
	* @param int $data bit field value to use, or false to use $this->data['user_options']
	* @return bool true if the option is set in the bit field, false otherwise
	*/
	function optionget($key, $data = false)
	{
		$var = ($data !== false) ? $data : $this->data['user_options'];
		return phpbb_optionget($this->keyoptions[$key], $var);
	}

	/**
	* Set option bit field for user options.
	*
	* @param int $key Option key, as defined in $keyoptions property.
	* @param bool $value True to set the option, false to clear the option.
	* @param int $data Current bit field value, or false to use $this->data['user_options']
	* @return int|bool If $data is false, the bit field is modified and
	*                  written back to $this->data['user_options'], and
	*                  return value is true if the bit field changed and
	*                  false otherwise. If $data is not false, the new
	*                  bitfield value is returned.
	*/
	function optionset($key, $value, $data = false)
	{
		$var = ($data !== false) ? $data : $this->data['user_options'];

		$new_var = phpbb_optionset($this->keyoptions[$key], $value, $var);

		if ($data === false)
		{
			if ($new_var != $var)
			{
				$this->data['user_options'] = $new_var;
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $new_var;
		}
	}
}

?>