<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb;

/**
* Session class
*/
class session
{
	var $cookie_data = array();
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
	 * Extract current session page
	 *
	 * @param string $root_path current root path (phpbb_root_path)
	 * @return array
	 */
	static function extract_current_page($root_path)
	{
		global $request, $symfony_request, $phpbb_filesystem;

		$page_array = array();

		// First of all, get the request uri...
		$script_name = $request->escape($symfony_request->getScriptName(), true);
		$args = $request->escape(explode('&', $symfony_request->getQueryString()), true);

		// If we are unable to get the script name we use REQUEST_URI as a failover and note it within the page array for easier support...
		if (!$script_name)
		{
			$script_name = html_entity_decode($request->server('REQUEST_URI'), ENT_COMPAT);
			$script_name = (($pos = strpos($script_name, '?')) !== false) ? substr($script_name, 0, $pos) : $script_name;
			$page_array['failover'] = 1;
		}

		// Replace backslashes and doubled slashes (could happen on some proxy setups)
		$script_name = str_replace(array('\\', '//'), '/', $script_name);

		// Now, remove the sid and let us get a clean query string...
		$use_args = array();

		// Since some browser do not encode correctly we need to do this with some "special" characters...
		// " -> %22, ' => %27, < -> %3C, > -> %3E
		$find = array('"', "'", '<', '>', '&quot;', '&lt;', '&gt;');
		$replace = array('%22', '%27', '%3C', '%3E', '%22', '%3C', '%3E');

		foreach ($args as $key => $argument)
		{
			if (strpos($argument, 'sid=') === 0)
			{
				continue;
			}

			$use_args[] = str_replace($find, $replace, $argument);
		}
		unset($args);

		// The following examples given are for an request uri of {path to the phpbb directory}/adm/index.php?i=10&b=2

		// The current query string
		$query_string = trim(implode('&', $use_args));

		// basenamed page name (for example: index.php)
		$page_name = (substr($script_name, -1, 1) == '/') ? '' : basename($script_name);
		$page_name = urlencode(htmlspecialchars($page_name, ENT_COMPAT));

		$symfony_request_path = $phpbb_filesystem->clean_path($symfony_request->getPathInfo());
		if ($symfony_request_path !== '/')
		{
			$page_name .= str_replace('%2F', '/', urlencode($symfony_request_path));
		}

		if (substr($root_path, 0, 2) === './' && strpos($root_path, '..') === false)
		{
			$root_dirs = explode('/', str_replace('\\', '/', rtrim($root_path, '/')));
			$page_dirs = explode('/', str_replace('\\', '/', '.'));
		}
		else
		{
			// current directory within the phpBB root (for example: adm)
			$root_dirs = explode('/', str_replace('\\', '/', $phpbb_filesystem->realpath($root_path)));
			$page_dirs = explode('/', str_replace('\\', '/', $phpbb_filesystem->realpath('./')));
		}

		$intersection = array_intersect_assoc($root_dirs, $page_dirs);

		$root_dirs = array_diff_assoc($root_dirs, $intersection);
		$page_dirs = array_diff_assoc($page_dirs, $intersection);

		$page_dir = str_repeat('../', count($root_dirs)) . implode('/', $page_dirs);

		if ($page_dir && substr($page_dir, -1, 1) == '/')
		{
			$page_dir = substr($page_dir, 0, -1);
		}

		// Current page from phpBB root (for example: adm/index.php?i=10&b=2)
		$page = (($page_dir) ? $page_dir . '/' : '') . $page_name;
		if ($query_string)
		{
			$page .= '?' . $query_string;
		}

		// The script path from the webroot to the current directory (for example: /phpBB3/adm/) : always prefixed with / and ends in /
		$script_path = $symfony_request->getBasePath();

		// The script path from the webroot to the phpBB root (for example: /phpBB3/)
		$script_dirs = explode('/', $script_path);
		array_splice($script_dirs, -count($page_dirs));
		$root_script_path = implode('/', $script_dirs) . (count($root_dirs) ? '/' . implode('/', $root_dirs) : '');

		// We are on the base level (phpBB root == webroot), lets adjust the variables a bit...
		if (!$root_script_path)
		{
			$root_script_path = ($page_dir) ? str_replace($page_dir, '', $script_path) : $script_path;
		}

		$script_path .= (substr($script_path, -1, 1) == '/') ? '' : '/';
		$root_script_path .= (substr($root_script_path, -1, 1) == '/') ? '' : '/';

		$forum_id = $request->variable('f', 0);
		// maximum forum id value is maximum value of mediumint unsigned column
		$forum_id = ($forum_id > 0 && $forum_id < 16777215) ? $forum_id : 0;

		$page_array += array(
			'page_name'			=> $page_name,
			'page_dir'			=> $page_dir,

			'query_string'		=> $query_string,
			'script_path'		=> str_replace(' ', '%20', htmlspecialchars($script_path, ENT_COMPAT)),
			'root_script_path'	=> str_replace(' ', '%20', htmlspecialchars($root_script_path, ENT_COMPAT)),

			'page'				=> $page,
			'forum'				=> $forum_id,
		);

		return $page_array;
	}

	/**
	* Get valid hostname/port. HTTP_HOST is used, SERVER_NAME if HTTP_HOST not present.
	*/
	function extract_current_hostname()
	{
		global $config, $request;

		// Get hostname
		$host = html_entity_decode($request->header('Host', $request->server('SERVER_NAME')), ENT_COMPAT);

		// Should be a string and lowered
		$host = (string) strtolower($host);

		// If host is equal the cookie domain or the server name (if config is set), then we assume it is valid
		if ((isset($config['cookie_domain']) && $host === $config['cookie_domain']) || (isset($config['server_name']) && $host === $config['server_name']))
		{
			return $host;
		}

		// Is the host actually a IP? If so, we use the IP... (IPv4)
		if (long2ip(ip2long($host)) === $host)
		{
			return $host;
		}

		// Now return the hostname (this also removes any port definition). The http:// is prepended to construct a valid URL, hosts never have a scheme assigned
		$host = @parse_url('http://' . $host);
		$host = (!empty($host['host'])) ? $host['host'] : '';

		// Remove any portions not removed by parse_url (#)
		$host = str_replace('#', '', $host);

		// If, by any means, the host is now empty, we will use a "best approach" way to guess one
		if (empty($host))
		{
			if (!empty($config['server_name']))
			{
				$host = $config['server_name'];
			}
			else if (!empty($config['cookie_domain']))
			{
				$host = (strpos($config['cookie_domain'], '.') === 0) ? substr($config['cookie_domain'], 1) : $config['cookie_domain'];
			}
			else
			{
				// Set to OS hostname or localhost
				$host = (function_exists('php_uname')) ? php_uname('n') : 'localhost';
			}
		}

		// It may be still no valid host, but for sure only a hostname (we may further expand on the cookie domain... if set)
		return $host;
	}

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
		global $phpEx, $SID, $_SID, $_EXTRA_URL, $db, $config, $phpbb_root_path;
		global $request, $phpbb_container, $user, $phpbb_log, $phpbb_dispatcher;

		// Give us some basic information
		$this->time_now				= time();
		$this->cookie_data			= array('u' => 0, 'k' => '');
		$this->update_session_page	= $update_session_page;
		$this->browser				= $request->header('User-Agent');
		$this->referer				= $request->header('Referer');
		$this->forwarded_for		= $request->header('X-Forwarded-For');

		$this->host					= $this->extract_current_hostname();
		$this->page					= $this->extract_current_page($phpbb_root_path);

		// if the forwarded for header shall be checked we have to validate its contents
		if ($config['forwarded_for_check'])
		{
			$this->forwarded_for = preg_replace('# {2,}#', ' ', str_replace(',', ' ', $this->forwarded_for));

			// split the list of IPs
			$ips = explode(' ', $this->forwarded_for);
			foreach ($ips as $ip)
			{
				if (!filter_var($ip, FILTER_VALIDATE_IP))
				{
					// contains invalid data, don't use the forwarded for header
					$this->forwarded_for = '';
					break;
				}
			}
		}
		else
		{
			$this->forwarded_for = '';
		}

		if ($request->is_set($config['cookie_name'] . '_sid', \phpbb\request\request_interface::COOKIE) || $request->is_set($config['cookie_name'] . '_u', \phpbb\request\request_interface::COOKIE))
		{
			$this->cookie_data['u'] = $request->variable($config['cookie_name'] . '_u', 0, false, \phpbb\request\request_interface::COOKIE);
			$this->cookie_data['k'] = $request->variable($config['cookie_name'] . '_k', '', false, \phpbb\request\request_interface::COOKIE);
			$this->session_id 		= $request->variable($config['cookie_name'] . '_sid', '', false, \phpbb\request\request_interface::COOKIE);

			$SID = (defined('NEED_SID')) ? '?sid=' . $this->session_id : '?sid=';
			$_SID = (defined('NEED_SID')) ? $this->session_id : '';

			if (empty($this->session_id))
			{
				$this->session_id = $_SID = $request->variable('sid', '');
				$SID = '?sid=' . $this->session_id;
				$this->cookie_data = array('u' => 0, 'k' => '');
			}
		}
		else
		{
			$this->session_id = $_SID = $request->variable('sid', '');
			$SID = '?sid=' . $this->session_id;
		}

		$_EXTRA_URL = array();

		// Why no forwarded_for et al? Well, too easily spoofed. With the results of my recent requests
		// it's pretty clear that in the majority of cases you'll at least be left with a proxy/cache ip.
		$ip = html_entity_decode($request->server('REMOTE_ADDR'), ENT_COMPAT);
		$ip = preg_replace('# {2,}#', ' ', str_replace(',', ' ', $ip));

		/**
		* Event to alter user IP address
		*
		* @event core.session_ip_after
		* @var	string	ip	REMOTE_ADDR
		* @since 3.1.10-RC1
		*/
		$vars = array('ip');
		extract($phpbb_dispatcher->trigger_event('core.session_ip_after', compact($vars)));

		// split the list of IPs
		$ips = explode(' ', trim($ip));

		// Default IP if REMOTE_ADDR is invalid
		$this->ip = '127.0.0.1';

		foreach ($ips as $ip)
		{
			// Normalise IP address
			$ip = phpbb_ip_normalise($ip);

			if ($ip === false)
			{
				// IP address is invalid.
				break;
			}

			// IP address is valid.
			$this->ip = $ip;
		}

		$this->load = false;

		// Load limit check (if applicable)
		if ($config['limit_load'] || $config['limit_search_load'])
		{
			if ((function_exists('sys_getloadavg') && $load = sys_getloadavg()) || ($load = explode(' ', @file_get_contents('/proc/loadavg'))))
			{
				$this->load = array_slice($load, 0, 1);
				$this->load = floatval($this->load[0]);
			}
			else
			{
				$config->set('limit_load', '0');
				$config->set('limit_search_load', '0');
			}
		}

		// if no session id is set, redirect to index.php
		$session_id = $request->variable('sid', '');
		if (defined('NEED_SID') && (empty($session_id) || $this->session_id !== $session_id))
		{
			send_status_line(401, 'Unauthorized');
			redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
		}

		// if session id is set
		if (!empty($this->session_id))
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
				// Validate IP length according to admin ... enforces an IP
				// check on bots if admin requires this
//				$quadcheck = ($config['ip_check_bot'] && $this->data['user_type'] & USER_BOT) ? 4 : $config['ip_check'];

				if (strpos($this->ip, ':') !== false && strpos($this->data['session_ip'], ':') !== false)
				{
					$s_ip = short_ipv6($this->data['session_ip'], $config['ip_check']);
					$u_ip = short_ipv6($this->ip, $config['ip_check']);
				}
				else
				{
					$s_ip = implode('.', array_slice(explode('.', $this->data['session_ip']), 0, $config['ip_check']));
					$u_ip = implode('.', array_slice(explode('.', $this->ip), 0, $config['ip_check']));
				}

				$s_browser = ($config['browser_check']) ? trim(strtolower(substr($this->data['session_browser'], 0, 149))) : '';
				$u_browser = ($config['browser_check']) ? trim(strtolower(substr($this->browser, 0, 149))) : '';

				$s_forwarded_for = ($config['forwarded_for_check']) ? substr($this->data['session_forwarded_for'], 0, 254) : '';
				$u_forwarded_for = ($config['forwarded_for_check']) ? substr($this->forwarded_for, 0, 254) : '';

				// referer checks
				// The @ before $config['referer_validation'] suppresses notices present while running the updater
				$check_referer_path = (@$config['referer_validation'] == REFERER_VALIDATE_PATH);
				$referer_valid = true;

				// we assume HEAD and TRACE to be foul play and thus only whitelist GET
				if (@$config['referer_validation'] && strtolower($request->server('REQUEST_METHOD')) !== 'get')
				{
					$referer_valid = $this->validate_referer($check_referer_path);
				}

				if ($u_ip === $s_ip && $s_browser === $u_browser && $s_forwarded_for === $u_forwarded_for && $referer_valid)
				{
					$session_expired = false;

					// Check whether the session is still valid if we have one
					/* @var $provider_collection \phpbb\auth\provider_collection */
					$provider_collection = $phpbb_container->get('auth.provider_collection');
					$provider = $provider_collection->get_provider();

					if (!($provider instanceof \phpbb\auth\provider\provider_interface))
					{
						throw new \RuntimeException($provider . ' must implement \phpbb\auth\provider\provider_interface');
					}

					$ret = $provider->validate_session($this->data);
					if ($ret !== null && !$ret)
					{
						$session_expired = true;
					}

					if (!$session_expired)
					{
						// Check the session length timeframe if autologin is not enabled.
						// Else check the autologin length... and also removing those having autologin enabled but no longer allowed board-wide.
						if (!$this->data['session_autologin'])
						{
							if ($this->data['session_time'] < $this->time_now - ($config['session_length'] + 60))
							{
								$session_expired = true;
							}
						}
						else if (!$config['allow_autologin'] || ($config['max_autologin_time'] && $this->data['session_time'] < $this->time_now - (86400 * (int) $config['max_autologin_time']) + 60))
						{
							$session_expired = true;
						}
					}

					if (!$session_expired)
					{
						$this->data['is_registered'] = ($this->data['user_id'] != ANONYMOUS && ($this->data['user_type'] == USER_NORMAL || $this->data['user_type'] == USER_FOUNDER)) ? true : false;
						$this->data['is_bot'] = (!$this->data['is_registered'] && $this->data['user_id'] != ANONYMOUS) ? true : false;
						$this->data['user_lang'] = basename($this->data['user_lang']);

						// Is user banned? Are they excluded? Won't return on ban, exists within method
						$this->check_ban_for_current_session($config);

						return true;
					}
				}
				else
				{
					// Added logging temporarily to help debug bugs...
					if ($phpbb_container->getParameter('session.log_errors') && $this->data['user_id'] != ANONYMOUS)
					{
						if ($referer_valid)
						{
							$phpbb_log->add('critical', $user->data['user_id'], $user->ip, 'LOG_IP_BROWSER_FORWARDED_CHECK', false, array(
								$u_ip,
								$s_ip,
								$u_browser,
								$s_browser,
								htmlspecialchars($u_forwarded_for, ENT_COMPAT),
								htmlspecialchars($s_forwarded_for, ENT_COMPAT)
							));
						}
						else
						{
							$phpbb_log->add('critical', $user->data['user_id'], $user->ip, 'LOG_REFERER_INVALID', false, array($this->referer));
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
		global $SID, $_SID, $db, $config, $cache, $phpbb_container, $phpbb_dispatcher;

		$this->data = array();

		/* Garbage collection ... remove old sessions updating user information
		// if necessary. It means (potentially) 11 queries but only infrequently
		if ($this->time_now > $config['session_last_gc'] + $config['session_gc'])
		{
			$this->session_gc();
		}*/

		// Do we allow autologin on this board? No? Then override anything
		// that may be requested here
		if (!$config['allow_autologin'])
		{
			$this->cookie_data['k'] = $persist_login = false;
		}

		/**
		* Here we do a bot check, oh er saucy! No, not that kind of bot
		* check. We loop through the list of bots defined by the admin and
		* see if we have any useragent and/or IP matches. If we do, this is a
		* bot, act accordingly
		*/
		$bot = false;
		$active_bots = $cache->obtain_bots();

		foreach ($active_bots as $row)
		{
			if ($row['bot_agent'] && preg_match('#' . str_replace('\*', '.*?', preg_quote($row['bot_agent'], '#')) . '#i', $this->browser))
			{
				$bot = $row['user_id'];
			}

			// If ip is supplied, we will make sure the ip is matching too...
			if ($row['bot_ip'] && ($bot || !$row['bot_agent']))
			{
				// Set bot to false, then we only have to set it to true if it is matching
				$bot = false;

				foreach (explode(',', $row['bot_ip']) as $bot_ip)
				{
					$bot_ip = trim($bot_ip);

					if (!$bot_ip)
					{
						continue;
					}

					if (strpos($this->ip, $bot_ip) === 0)
					{
						$bot = (int) $row['user_id'];
						break;
					}
				}
			}

			if ($bot)
			{
				break;
			}
		}

		/* @var $provider_collection \phpbb\auth\provider_collection */
		$provider_collection = $phpbb_container->get('auth.provider_collection');
		$provider = $provider_collection->get_provider();
		$this->data = $provider->autologin();

		if ($user_id !== false && isset($this->data['user_id']) && $this->data['user_id'] != $user_id)
		{
			$this->data = array();
		}

		if (isset($this->data['user_id']))
		{
			$this->cookie_data['k'] = '';
			$this->cookie_data['u'] = $this->data['user_id'];
		}

		// If we're presented with an autologin key we'll join against it.
		// Else if we've been passed a user_id we'll grab data based on that
		if (isset($this->cookie_data['k']) && $this->cookie_data['k'] && $this->cookie_data['u'] && empty($this->data))
		{
			$sql = 'SELECT u.*
				FROM ' . USERS_TABLE . ' u, ' . SESSIONS_KEYS_TABLE . ' k
				WHERE u.user_id = ' . (int) $this->cookie_data['u'] . '
					AND u.user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ")
					AND k.user_id = u.user_id
					AND k.key_id = '" . $db->sql_escape(md5($this->cookie_data['k'])) . "'";
			$result = $db->sql_query($sql);
			$user_data = $db->sql_fetchrow($result);

			if ($user_id === false || (isset($user_data['user_id']) && $user_id == $user_data['user_id']))
			{
				$this->data = $user_data;
				$bot = false;
			}

			$db->sql_freeresult($result);
		}

		if ($user_id !== false && empty($this->data))
		{
			$this->cookie_data['k'] = '';
			$this->cookie_data['u'] = $user_id;

			$sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $this->cookie_data['u'] . '
					AND user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')';
			$result = $db->sql_query($sql);
			$this->data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			$bot = false;
		}

		// Bot user, if they have a SID in the Request URI we need to get rid of it
		// otherwise they'll index this page with the SID, duplicate content oh my!
		if ($bot && isset($_GET['sid']))
		{
			send_status_line(301, 'Moved Permanently');
			redirect(build_url(array('sid')));
		}

		// If no data was returned one or more of the following occurred:
		// Key didn't match one in the DB
		// User does not exist
		// User is inactive
		// User is bot
		if (!is_array($this->data) || !count($this->data))
		{
			$this->cookie_data['k'] = '';
			$this->cookie_data['u'] = ($bot) ? $bot : ANONYMOUS;

			if (!$bot)
			{
				$sql = 'SELECT *
					FROM ' . USERS_TABLE . '
					WHERE user_id = ' . (int) $this->cookie_data['u'];
			}
			else
			{
				// We give bots always the same session if it is not yet expired.
				$sql = 'SELECT u.*, s.*
					FROM ' . USERS_TABLE . ' u
					LEFT JOIN ' . SESSIONS_TABLE . ' s ON (s.session_user_id = u.user_id)
					WHERE u.user_id = ' . (int) $bot;
			}

			$result = $db->sql_query($sql);
			$this->data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}

		if ($this->data['user_id'] != ANONYMOUS && !$bot)
		{
			$this->data['session_last_visit'] = (isset($this->data['session_time']) && $this->data['session_time']) ? $this->data['session_time'] : (($this->data['user_lastvisit']) ? $this->data['user_lastvisit'] : time());
		}
		else
		{
			$this->data['session_last_visit'] = $this->time_now;
		}

		// Force user id to be integer...
		$this->data['user_id'] = (int) $this->data['user_id'];

		// At this stage we should have a filled data array, defined cookie u and k data.
		// data array should contain recent session info if we're a real user and a recent
		// session exists in which case session_id will also be set

		// Is user banned? Are they excluded? Won't return on ban, exists within method
		$this->check_ban_for_current_session($config);

		$this->data['is_registered'] = (!$bot && $this->data['user_id'] != ANONYMOUS && ($this->data['user_type'] == USER_NORMAL || $this->data['user_type'] == USER_FOUNDER)) ? true : false;
		$this->data['is_bot'] = ($bot) ? true : false;

		// If our friend is a bot, we re-assign a previously assigned session
		if ($this->data['is_bot'] && $bot == $this->data['user_id'] && $this->data['session_id'])
		{
			// Only assign the current session if the ip, browser and forwarded_for match...
			if (strpos($this->ip, ':') !== false && strpos($this->data['session_ip'], ':') !== false)
			{
				$s_ip = short_ipv6($this->data['session_ip'], $config['ip_check']);
				$u_ip = short_ipv6($this->ip, $config['ip_check']);
			}
			else
			{
				$s_ip = implode('.', array_slice(explode('.', $this->data['session_ip']), 0, $config['ip_check']));
				$u_ip = implode('.', array_slice(explode('.', $this->ip), 0, $config['ip_check']));
			}

			$s_browser = ($config['browser_check']) ? trim(strtolower(substr($this->data['session_browser'], 0, 149))) : '';
			$u_browser = ($config['browser_check']) ? trim(strtolower(substr($this->browser, 0, 149))) : '';

			$s_forwarded_for = ($config['forwarded_for_check']) ? substr($this->data['session_forwarded_for'], 0, 254) : '';
			$u_forwarded_for = ($config['forwarded_for_check']) ? substr($this->forwarded_for, 0, 254) : '';

			if ($u_ip === $s_ip && $s_browser === $u_browser && $s_forwarded_for === $u_forwarded_for)
			{
				$this->session_id = $this->data['session_id'];

				// Only update session DB a minute or so after last update or if page changes
				if ($this->time_now - $this->data['session_time'] > 60 || ($this->update_session_page && $this->data['session_page'] != $this->page['page']))
				{
					// Update the last visit time
					$sql = 'UPDATE ' . USERS_TABLE . '
						SET user_lastvisit = ' . (int) $this->data['session_time'] . '
						WHERE user_id = ' . (int) $this->data['user_id'];
					$db->sql_query($sql);
				}

				$SID = '?sid=';
				$_SID = '';
				return true;
			}
			else
			{
				// If the ip and browser does not match make sure we only have one bot assigned to one session
				$db->sql_query('DELETE FROM ' . SESSIONS_TABLE . ' WHERE session_user_id = ' . $this->data['user_id']);
			}
		}

		$session_autologin = (($this->cookie_data['k'] || $persist_login) && $this->data['is_registered']) ? true : false;
		$set_admin = ($set_admin && $this->data['is_registered']) ? true : false;

		// Create or update the session
		$sql_ary = array(
			'session_user_id'		=> (int) $this->data['user_id'],
			'session_start'			=> (int) $this->time_now,
			'session_last_visit'	=> (int) $this->data['session_last_visit'],
			'session_time'			=> (int) $this->time_now,
			'session_browser'		=> (string) trim(substr($this->browser, 0, 149)),
			'session_forwarded_for'	=> (string) $this->forwarded_for,
			'session_ip'			=> (string) $this->ip,
			'session_autologin'		=> ($session_autologin) ? 1 : 0,
			'session_admin'			=> ($set_admin) ? 1 : 0,
			'session_viewonline'	=> ($viewonline) ? 1 : 0,
		);

		if ($this->update_session_page)
		{
			$sql_ary['session_page'] = (string) substr($this->page['page'], 0, 199);
			$sql_ary['session_forum_id'] = $this->page['forum'];
		}

		$db->sql_return_on_error(true);

		$sql = 'DELETE
			FROM ' . SESSIONS_TABLE . '
			WHERE session_id = \'' . $db->sql_escape($this->session_id) . '\'
				AND session_user_id = ' . ANONYMOUS;

		if (!defined('IN_ERROR_HANDLER') && (!$this->session_id || !$db->sql_query($sql) || !$db->sql_affectedrows()))
		{
			// Limit new sessions in 1 minute period (if required)
			if (empty($this->data['session_time']) && $config['active_sessions'])
			{
//				$db->sql_return_on_error(false);

				$sql = 'SELECT COUNT(session_id) AS sessions
					FROM ' . SESSIONS_TABLE . '
					WHERE session_time >= ' . ($this->time_now - 60);
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if ((int) $row['sessions'] > (int) $config['active_sessions'])
				{
					send_status_line(503, 'Service Unavailable');
					trigger_error('BOARD_UNAVAILABLE');
				}
			}
		}

		// Since we re-create the session id here, the inserted row must be unique. Therefore, we display potential errors.
		// Commented out because it will not allow forums to update correctly
//		$db->sql_return_on_error(false);

		// Something quite important: session_page always holds the *last* page visited, except for the *first* visit.
		// We are not able to simply have an empty session_page btw, therefore we need to tell phpBB how to detect this special case.
		// If the session id is empty, we have a completely new one and will set an "identifier" here. This identifier is able to be checked later.
		if (empty($this->data['session_id']))
		{
			// This is a temporary variable, only set for the very first visit
			$this->data['session_created'] = true;
		}

		$this->session_id = $this->data['session_id'] = md5(unique_id());

		$sql_ary['session_id'] = (string) $this->session_id;
		$sql_ary['session_page'] = (string) substr($this->page['page'], 0, 199);
		$sql_ary['session_forum_id'] = $this->page['forum'];

		$sql = 'INSERT INTO ' . SESSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		$db->sql_return_on_error(false);

		// Regenerate autologin/persistent login key
		if ($session_autologin)
		{
			$this->set_login_key();
		}

		// refresh data
		$SID = '?sid=' . $this->session_id;
		$_SID = $this->session_id;
		$this->data = array_merge($this->data, $sql_ary);

		if (!$bot)
		{
			$cookie_expire = $this->time_now + (($config['max_autologin_time']) ? 86400 * (int) $config['max_autologin_time'] : 31536000);

			$this->set_cookie('u', $this->cookie_data['u'], $cookie_expire);
			$this->set_cookie('k', $this->cookie_data['k'], $cookie_expire);
			$this->set_cookie('sid', $this->session_id, $cookie_expire);

			unset($cookie_expire);

			$sql = 'SELECT COUNT(session_id) AS sessions
					FROM ' . SESSIONS_TABLE . '
					WHERE session_user_id = ' . (int) $this->data['user_id'] . '
					AND session_time >= ' . (int) ($this->time_now - (max((int) $config['session_length'], (int) $config['form_token_lifetime'])));
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ((int) $row['sessions'] <= 1 || empty($this->data['user_form_salt']))
			{
				$this->data['user_form_salt'] = unique_id();
				// Update the form key
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_form_salt = \'' . $db->sql_escape($this->data['user_form_salt']) . '\'
					WHERE user_id = ' . (int) $this->data['user_id'];
				$db->sql_query($sql);
			}
		}
		else
		{
			$this->data['session_time'] = $this->data['session_last_visit'] = $this->time_now;

			// Update the last visit time
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_lastvisit = ' . (int) $this->data['session_time'] . '
				WHERE user_id = ' . (int) $this->data['user_id'];
			$db->sql_query($sql);

			$SID = '?sid=';
			$_SID = '';
		}

		$session_data = $sql_ary;
		/**
		* Event to send new session data to extension
		* Read-only event
		*
		* @event core.session_create_after
		* @var	array		session_data				Associative array of session keys to be updated
		* @since 3.1.6-RC1
		*/
		$vars = array('session_data');
		extract($phpbb_dispatcher->trigger_event('core.session_create_after', compact($vars)));
		unset($session_data);

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
		global $SID, $_SID, $db, $phpbb_container, $phpbb_dispatcher;

		$sql = 'DELETE FROM ' . SESSIONS_TABLE . "
			WHERE session_id = '" . $db->sql_escape($this->session_id) . "'
				AND session_user_id = " . (int) $this->data['user_id'];
		$db->sql_query($sql);

		$user_id = (int) $this->data['user_id'];
		$session_id = $this->session_id;
		/**
		* Event to send session kill information to extension
		* Read-only event
		*
		* @event core.session_kill_after
		* @var	int		user_id				user_id of the session user.
		* @var	string		session_id				current user's session_id
		* @var	bool	new_session 	should we create new session for user
		* @since 3.1.6-RC1
		*/
		$vars = array('user_id', 'session_id', 'new_session');
		extract($phpbb_dispatcher->trigger_event('core.session_kill_after', compact($vars)));
		unset($user_id);
		unset($session_id);

		// Allow connecting logout with external auth method logout
		/* @var $provider_collection \phpbb\auth\provider_collection */
		$provider_collection = $phpbb_container->get('auth.provider_collection');
		$provider = $provider_collection->get_provider();
		$provider->logout($this->data, $new_session);

		if ($this->data['user_id'] != ANONYMOUS)
		{
			// Delete existing session, update last visit info first!
			if (!isset($this->data['session_time']))
			{
				$this->data['session_time'] = time();
			}

			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_lastvisit = ' . (int) $this->data['session_time'] . '
				WHERE user_id = ' . (int) $this->data['user_id'];
			$db->sql_query($sql);

			if ($this->cookie_data['k'])
			{
				$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
					WHERE user_id = ' . (int) $this->data['user_id'] . "
						AND key_id = '" . $db->sql_escape(md5($this->cookie_data['k'])) . "'";
				$db->sql_query($sql);
			}

			// Reset the data array
			$this->data = array();

			$sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . ANONYMOUS;
			$result = $db->sql_query($sql);
			$this->data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}

		$cookie_expire = $this->time_now - 31536000;
		$this->set_cookie('u', '', $cookie_expire);
		$this->set_cookie('k', '', $cookie_expire);
		$this->set_cookie('sid', '', $cookie_expire);
		unset($cookie_expire);

		$SID = '?sid=';
		$this->session_id = $_SID = '';

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
		global $db, $config, $phpbb_container, $phpbb_dispatcher;

		if (!$this->time_now)
		{
			$this->time_now = time();
		}

		/**
		 * Get expired sessions for registered users, only most recent for each user
		 * Inner SELECT gets most recent expired sessions for unique session_user_id
		 * Outer SELECT gets data for them
		 */
		$sql_select = 'SELECT s1.session_page, s1.session_user_id, s1.session_time AS recent_time
			FROM ' . SESSIONS_TABLE . ' AS s1
			INNER JOIN (
				SELECT session_user_id, MAX(session_time) AS recent_time
				FROM ' . SESSIONS_TABLE . '
				WHERE session_time < ' . ($this->time_now - (int) $config['session_length']) . '
					AND session_user_id <> ' . ANONYMOUS . '
				GROUP BY session_user_id
			) AS s2
			ON s1.session_user_id = s2.session_user_id
				AND s1.session_time = s2.recent_time';

		switch ($db->get_sql_layer())
		{
			case 'sqlite3':
				if (phpbb_version_compare($db->sql_server_info(true), '3.8.3', '>='))
				{
					// For SQLite versions 3.8.3+ which support Common Table Expressions (CTE)
					$sql = "WITH s3 (session_page, session_user_id, session_time) AS ($sql_select)
						UPDATE " . USERS_TABLE . '
						SET (user_lastpage, user_lastvisit) = (SELECT session_page, session_time FROM s3 WHERE session_user_id = user_id)
						WHERE EXISTS (SELECT session_user_id FROM s3 WHERE session_user_id = user_id)';
					$db->sql_query($sql);

					break;
				}

			// No break, for SQLite versions prior to 3.8.3 and Oracle
			case 'oracle':
				$result = $db->sql_query($sql_select);
				while ($row = $db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . USERS_TABLE . '
						SET user_lastvisit = ' . (int) $row['recent_time'] . ", user_lastpage = '" . $db->sql_escape($row['session_page']) . "'
						WHERE user_id = " . (int) $row['session_user_id'];
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);
			break;

			case 'mysqli':
				$sql = 'UPDATE ' . USERS_TABLE . " u,
					($sql_select) s3
					SET u.user_lastvisit = s3.recent_time, u.user_lastpage = s3.session_page
					WHERE u.user_id = s3.session_user_id";
				$db->sql_query($sql);
			break;

			default:
				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_lastvisit = s3.recent_time, user_lastpage = s3.session_page
					FROM ($sql_select) s3
					WHERE user_id = s3.session_user_id";
				$db->sql_query($sql);
			break;
		}

		// Delete all expired sessions
		$sql = 'DELETE FROM ' . SESSIONS_TABLE . '
			WHERE session_time < ' . ($this->time_now - (int) $config['session_length']);
		$db->sql_query($sql);

		// Update gc timer
		$config->set('session_last_gc', $this->time_now, false);

		if ($config['max_autologin_time'])
		{
			$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
				WHERE last_login < ' . (time() - (86400 * (int) $config['max_autologin_time']));
			$db->sql_query($sql);
		}

		// only called from CRON; should be a safe workaround until the infrastructure gets going
		/* @var \phpbb\captcha\factory $captcha_factory */
		$captcha_factory = $phpbb_container->get('captcha.factory');
		$captcha_factory->garbage_collect($config['captcha_plugin']);

		$sql = 'DELETE FROM ' . LOGIN_ATTEMPT_TABLE . '
			WHERE attempt_time < ' . (time() - (int) $config['ip_login_limit_time']);
		$db->sql_query($sql);

		/**
		* Event to trigger extension on session_gc
		*
		* @event core.session_gc_after
		* @since 3.1.6-RC1
		*/
		$phpbb_dispatcher->dispatch('core.session_gc_after');

		return;
	}

	/**
	* Sets a cookie
	*
	* Sets a cookie of the given name with the specified data for the given length of time. If no time is specified, a session cookie will be set.
	*
	* @param string $name		Name of the cookie, will be automatically prefixed with the phpBB cookie name. track becomes [cookie_name]_track then.
	* @param string $cookiedata	The data to hold within the cookie
	* @param int $cookietime	The expiration time as UNIX timestamp. If 0 is provided, a session cookie is set.
	* @param bool $httponly		Use HttpOnly. Defaults to true. Use false to make cookie accessible by client-side scripts.
	*/
	function set_cookie($name, $cookiedata, $cookietime, $httponly = true)
	{
		global $config, $phpbb_dispatcher;

		// If headers are already set, we just return
		if (headers_sent())
		{
			return;
		}

		$disable_cookie = false;
		/**
		* Event to modify or disable setting cookies
		*
		* @event core.set_cookie
		* @var	bool		disable_cookie	Set to true to disable setting this cookie
		* @var	string		name			Name of the cookie
		* @var	string		cookiedata		The data to hold within the cookie
		* @var	int			cookietime		The expiration time as UNIX timestamp
		* @var	bool		httponly		Use HttpOnly?
		* @since 3.2.9-RC1
		*/
		$vars = array(
			'disable_cookie',
			'name',
			'cookiedata',
			'cookietime',
			'httponly',
		);
		extract($phpbb_dispatcher->trigger_event('core.set_cookie', compact($vars)));

		if ($disable_cookie)
		{
			return;
		}

		$name_data = rawurlencode($config['cookie_name'] . '_' . $name) . '=' . rawurlencode($cookiedata);
		$expire = gmdate('D, d-M-Y H:i:s \\G\\M\\T', $cookietime);
		$domain = (!$config['cookie_domain'] || $config['cookie_domain'] == '127.0.0.1' || strpos($config['cookie_domain'], '.') === false) ? '' : '; domain=' . $config['cookie_domain'];

		header('Set-Cookie: ' . $name_data . (($cookietime) ? '; expires=' . $expire : '') . '; path=' . $config['cookie_path'] . $domain . ((!$config['cookie_secure']) ? '' : '; secure') . ';' . (($httponly) ? ' HttpOnly' : ''), false);
	}

	/**
	* Check for banned user
	*
	* Checks whether the supplied user is banned by id, ip or email. If no parameters
	* are passed to the method pre-existing session data is used.
	*
	* @param int|false		$user_id		The user id
	* @param mixed			$user_ips		Can contain a string with one IP or an array of multiple IPs
	* @param string|false	$user_email		The user email
	* @param bool			$return			If $return is false this routine does not return on finding a banned user,
	*	it outputs a relevant message and stops execution.
	*/
	function check_ban($user_id = false, $user_ips = false, $user_email = false, $return = false)
	{
		global $config, $db, $phpbb_dispatcher;

		if (defined('IN_CHECK_BAN') || defined('SKIP_CHECK_BAN'))
		{
			return;
		}

		$banned = false;
		$cache_ttl = 3600;
		$where_sql = array();

		$sql = 'SELECT ban_ip, ban_userid, ban_email, ban_exclude, ban_give_reason, ban_end
			FROM ' . BANLIST_TABLE . '
			WHERE ';

		// Determine which entries to check, only return those
		if ($user_email === false)
		{
			$where_sql[] = "ban_email = ''";
		}

		if ($user_ips === false)
		{
			$where_sql[] = "(ban_ip = '' OR ban_exclude = 1)";
		}

		if ($user_id === false)
		{
			$where_sql[] = '(ban_userid = 0 OR ban_exclude = 1)';
		}
		else
		{
			$cache_ttl = ($user_id == ANONYMOUS) ? 3600 : 0;
			$_sql = '(ban_userid = ' . $user_id;

			if ($user_email !== false)
			{
				$_sql .= " OR ban_email <> ''";
			}

			if ($user_ips !== false)
			{
				$_sql .= " OR ban_ip <> ''";
			}

			$_sql .= ')';

			$where_sql[] = $_sql;
		}

		$sql .= (count($where_sql)) ? implode(' AND ', $where_sql) : '';
		$result = $db->sql_query($sql, $cache_ttl);

		$ban_triggered_by = 'user';
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['ban_end'] && $row['ban_end'] < time())
			{
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

			if ((!empty($row['ban_userid']) && intval($row['ban_userid']) == $user_id) ||
				$ip_banned ||
				(!empty($row['ban_email']) && preg_match('#^' . str_replace('\*', '.*?', preg_quote($row['ban_email'], '#')) . '$#i', $user_email)))
			{
				if (!empty($row['ban_exclude']))
				{
					$banned = false;
					break;
				}
				else
				{
					$banned = true;
					$ban_row = $row;

					if (!empty($row['ban_userid']) && intval($row['ban_userid']) == $user_id)
					{
						$ban_triggered_by = 'user';
					}
					else if ($ip_banned)
					{
						$ban_triggered_by = 'ip';
					}
					else
					{
						$ban_triggered_by = 'email';
					}

					// Don't break. Check if there is an exclude rule for this user
				}
			}
		}
		$db->sql_freeresult($result);

		/**
		* Event to set custom ban type
		*
		* @event core.session_set_custom_ban
		* @var	bool		return				If $return is false this routine does not return on finding a banned user, it outputs a relevant message and stops execution
		* @var	bool		banned				Check if user already banned
		* @var	array|false	ban_row				Ban data
		* @var	string		ban_triggered_by	Method that caused ban, can be your custom method
		* @since 3.1.3-RC1
		*/
		$ban_row = isset($ban_row) ? $ban_row : false;
		$vars = array('return', 'banned', 'ban_row', 'ban_triggered_by');
		extract($phpbb_dispatcher->trigger_event('core.session_set_custom_ban', compact($vars)));

		if ($banned && !$return)
		{
			global $phpbb_root_path, $phpEx;

			// If the session is empty we need to create a valid one...
			if (empty($this->session_id))
			{
				// This seems to be no longer needed? - #14971
//				$this->session_create(ANONYMOUS);
			}

			// Initiate environment ... since it won't be set at this stage
			$this->setup();

			// Logout the user, banned users are unable to use the normal 'logout' link
			if ($this->data['user_id'] != ANONYMOUS)
			{
				$this->session_kill();
			}

			// We show a login box here to allow founders accessing the board if banned by IP
			if (defined('IN_LOGIN') && $this->data['user_id'] == ANONYMOUS)
			{
				$this->setup('ucp');
				$this->data['is_registered'] = $this->data['is_bot'] = false;

				// Set as a precaution to allow login_box() handling this case correctly as well as this function not being executed again.
				define('IN_CHECK_BAN', 1);

				login_box("index.$phpEx");

				// The false here is needed, else the user is able to circumvent the ban.
				$this->session_kill(false);
			}

			// Ok, we catch the case of an empty session id for the anonymous user...
			// This can happen if the user is logging in, banned by username and the login_box() being called "again".
			if (empty($this->session_id) && defined('IN_CHECK_BAN'))
			{
				$this->session_create(ANONYMOUS);
			}

			// Determine which message to output
			$till_date = ($ban_row['ban_end']) ? $this->format_date($ban_row['ban_end']) : '';
			$message = ($ban_row['ban_end']) ? 'BOARD_BAN_TIME' : 'BOARD_BAN_PERM';

			$contact_link = phpbb_get_board_contact_link($config, $phpbb_root_path, $phpEx);
			$message = sprintf($this->lang[$message], $till_date, '<a href="' . $contact_link . '">', '</a>');
			$message .= ($ban_row['ban_give_reason']) ? '<br /><br />' . sprintf($this->lang['BOARD_BAN_REASON'], $ban_row['ban_give_reason']) : '';
			$message .= '<br /><br /><em>' . $this->lang['BAN_TRIGGERED_BY_' . strtoupper($ban_triggered_by)] . '</em>';

			// A very special case... we are within the cron script which is not supposed to print out the ban message... show blank page
			if (defined('IN_CRON'))
			{
				garbage_collection();
				exit_handler();
				exit;
			}

			// To circumvent session_begin returning a valid value and the check_ban() not called on second page view, we kill the session again
			$this->session_kill(false);

			trigger_error($message);
		}

		if (!empty($ban_row))
		{
			$ban_row['ban_triggered_by'] = $ban_triggered_by;
		}

		return ($banned && $ban_row) ? $ban_row : $banned;
	}

	/**
	 * Check the current session for bans
	 *
	 * @return true if session user is banned.
	 */
	protected function check_ban_for_current_session($config)
	{
		if (!defined('SKIP_CHECK_BAN') && $this->data['user_type'] != USER_FOUNDER)
		{
			if (!$config['forwarded_for_check'])
			{
				$this->check_ban($this->data['user_id'], $this->ip);
			}
			else
			{
				$ips = explode(' ', $this->forwarded_for);
				$ips[] = $this->ip;
				$this->check_ban($this->data['user_id'], $ips);
			}
		}
	}

	/**
	* Check if ip is blacklisted
	* This should be called only where absolutely necessary
	*
	* Only IPv4 (rbldns does not support AAAA records/IPv6 lookups)
	*
	* @author satmd (from the php manual)
	* @param string 		$mode	register/post - spamcop for example is omitted for posting
	* @param string|false	$ip		the IPv4 address to check
	*
	* @return false if ip is not blacklisted, else an array([checked server], [lookup])
	*/
	function check_dnsbl($mode, $ip = false)
	{
		if ($ip === false)
		{
			$ip = $this->ip;
		}

		// Neither Spamhaus nor Spamcop supports IPv6 addresses.
		if (strpos($ip, ':') !== false)
		{
			return false;
		}

		$dnsbl_check = array(
			'sbl.spamhaus.org'	=> 'http://www.spamhaus.org/query/bl?ip=',
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
				if (checkdnsrr($reverse_ip . '.' . $dnsbl . '.', 'A') === true)
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
	* Check if URI is blacklisted
	* This should be called only where absolutely necessary, for example on the submitted website field
	* This function is not in use at the moment and is only included for testing purposes, it may not work at all!
	* This means it is untested at the moment and therefore commented out
	*
	* @param string $uri URI to check
	* @return true if uri is on blacklist, else false. Only blacklist is checked (~zero FP), no grey lists
	function check_uribl($uri)
	{
		// Normally parse_url() is not intended to parse uris
		// We need to get the top-level domain name anyway... change.
		$uri = parse_url($uri);

		if ($uri === false || empty($uri['host']))
		{
			return false;
		}

		$uri = trim($uri['host']);

		if ($uri)
		{
			// One problem here... the return parameter for the "windows" method is different from what
			// we expect... this may render this check useless...
			if (checkdnsrr($uri . '.multi.uribl.com.', 'A') === true)
			{
				return true;
			}
		}

		return false;
	}
	*/

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
		global $db, $phpbb_dispatcher;

		$user_id = ($user_id === false) ? $this->data['user_id'] : $user_id;
		$user_ip = ($user_ip === false) ? $this->ip : $user_ip;
		$key = ($key === false) ? (($this->cookie_data['k']) ? $this->cookie_data['k'] : false) : $key;

		$key_id = unique_id(hexdec(substr($this->session_id, 0, 8)));

		$sql_ary = array(
			'key_id'		=> (string) md5($key_id),
			'last_ip'		=> (string) $user_ip,
			'last_login'	=> (int) time()
		);

		if (!$key)
		{
			$sql_ary += array(
				'user_id'	=> (int) $user_id
			);
		}

		if ($key)
		{
			$sql = 'UPDATE ' . SESSIONS_KEYS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE user_id = ' . (int) $user_id . "
					AND key_id = '" . $db->sql_escape(md5($key)) . "'";
		}
		else
		{
			$sql = 'INSERT INTO ' . SESSIONS_KEYS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		}

		/**
		 * Event to adjust autologin keys process
		 *
		 * @event core.set_login_key
		 * @var	string|false	key			Current autologin key if exists, false otherwise
		 * @var	string			key_id		New autologin key
		 * @var	string			sql			SQL query to update/insert autologin key
		 * @var	array			sql_ary		Aray with autologin key data
		 * @var	int				user_id		Current user's ID
		 * @var	string			user_ip		Current user's IP address
		 * @since 3.3.2-RC1
		 */
		$vars = [
			'key',
			'key_id',
			'sql',
			'sql_ary',
			'user_id',
			'user_ip',
		];
		extract($phpbb_dispatcher->trigger_event('core.set_login_key', compact($vars)));

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
		global $db;

		$user_id = ($user_id === false) ? (int) $this->data['user_id'] : (int) $user_id;

		$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
			WHERE user_id = ' . (int) $user_id;
		$db->sql_query($sql);

		// If the user is logged in, update last visit info first before deleting sessions
		$sql = 'SELECT session_time, session_page
			FROM ' . SESSIONS_TABLE . '
			WHERE session_user_id = ' . (int) $user_id . '
			ORDER BY session_time DESC';
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_lastvisit = ' . (int) $row['session_time'] . ", user_lastpage = '" . $db->sql_escape($row['session_page']) . "'
				WHERE user_id = " . (int) $user_id;
			$db->sql_query($sql);
		}

		// Let's also clear any current sessions for the specified user_id
		// If it's the current user then we'll leave this session intact
		$sql_where = 'session_user_id = ' . (int) $user_id;
		$sql_where .= ($user_id === (int) $this->data['user_id']) ? " AND session_id <> '" . $db->sql_escape($this->session_id) . "'" : '';

		$sql = 'DELETE FROM ' . SESSIONS_TABLE . "
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
		global $config, $request;

		// no referer - nothing to validate, user's fault for turning it off (we only check on POST; so meta can't be the reason)
		if (empty($this->referer) || empty($this->host))
		{
			return true;
		}

		$host = htmlspecialchars($this->host, ENT_COMPAT);
		$ref = substr($this->referer, strpos($this->referer, '://') + 3);

		if (!(stripos($ref, $host) === 0) && (!$config['force_server_vars'] || !(stripos($ref, $config['server_name']) === 0)))
		{
			return false;
		}
		else if ($check_script_path && rtrim($this->page['root_script_path'], '/') !== '')
		{
			$ref = substr($ref, strlen($host));
			$server_port = $request->server('SERVER_PORT', 0);

			if ($server_port !== 80 && $server_port !== 443 && stripos($ref, ":$server_port") === 0)
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
		$sql = 'UPDATE ' . SESSIONS_TABLE . '
			SET session_admin = 0
			WHERE session_id = \'' . $db->sql_escape($this->session_id) . '\'';
		$db->sql_query($sql);
	}

	/**
	* Update the session data
	*
	* @param array $session_data associative array of session keys to be updated
	* @param string $session_id optional session_id, defaults to current user's session_id
	*/
	public function update_session($session_data, $session_id = null)
	{
		global $db, $phpbb_dispatcher;

		$session_id = ($session_id) ? $session_id : $this->session_id;

		$sql = 'UPDATE ' . SESSIONS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $session_data) . "
			WHERE session_id = '" . $db->sql_escape($session_id) . "'";
		$db->sql_query($sql);

		/**
		* Event to send update session information to extension
		* Read-only event
		*
		* @event core.update_session_after
		* @var	array		session_data				Associative array of session keys to be updated
		* @var	string		session_id				current user's session_id
		* @since 3.1.6-RC1
		*/
		$vars = array('session_data', 'session_id');
		extract($phpbb_dispatcher->trigger_event('core.update_session_after', compact($vars)));
	}

	public function update_session_infos()
	{
		global $config, $db, $request;

		// No need to update if it's a new session. Informations are already inserted by session_create()
		if (isset($this->data['session_created']) && $this->data['session_created'])
		{
			return;
		}

		// Do not update the session page for ajax requests, so the view online still works as intended
		$page_changed = $this->update_session_page && (!isset($this->data['session_page']) || $this->data['session_page'] != $this->page['page']) && !$request->is_ajax();

		// Only update session DB a minute or so after last update or if page changes
		if ($this->time_now - (isset($this->data['session_time']) ? $this->data['session_time'] : 0) > 60 || $page_changed)
		{
			$sql_ary = array('session_time' => $this->time_now);

			if ($page_changed)
			{
				$sql_ary['session_page'] = substr($this->page['page'], 0, 199);
				$sql_ary['session_forum_id'] = $this->page['forum'];
			}

			$db->sql_return_on_error(true);

			$this->update_session($sql_ary);

			$db->sql_return_on_error(false);

			$this->data = array_merge($this->data, $sql_ary);

			if ($this->data['user_id'] != ANONYMOUS && isset($config['new_member_post_limit']) && $this->data['user_new'] && $config['new_member_post_limit'] <= $this->data['user_posts'])
			{
				$this->leave_newly_registered();
			}
		}
	}

	/**
	 * Get user ID
	 *
	 * @return int User ID
	 */
	public function id() : int
	{
		return isset($this->data['user_id']) ? (int) $this->data['user_id'] : ANONYMOUS;
	}
}
