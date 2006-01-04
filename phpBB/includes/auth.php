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
* Permission/Auth class
*/
class auth
{
	var $acl = array();
	var $acl_options = array();

	/**
	* Init permissions
	*/
	function acl(&$userdata)
	{
		global $db, $cache;

		$this->acl = array();

		if (!($this->acl_options = $cache->get('acl_options')))
		{
			$sql = 'SELECT auth_option, is_global, is_local
				FROM ' . ACL_OPTIONS_TABLE . '
				ORDER BY auth_option_id';
			$result = $db->sql_query($sql);

			$global = $local = 0;
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['is_global'])
				{
					$this->acl_options['global'][$row['auth_option']] = $global++;
				}

				if ($row['is_local'])
				{
					$this->acl_options['local'][$row['auth_option']] = $local++;
				}
			}
			$db->sql_freeresult($result);

			$cache->put('acl_options', $this->acl_options);
			$this->acl_clear_prefetch();
			$this->acl_cache($userdata);
		}
		else if (!trim($userdata['user_permissions']))
		{
			$this->acl_cache($userdata);
		}

		$user_permissions = explode("\n", $userdata['user_permissions']);

		foreach ($user_permissions as $f => $seq)
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
					
					// We put the original bitstring into the acl array
					$this->acl[$f] .= str_pad(base_convert($subseq, 36, 2), 31, 0, STR_PAD_LEFT);
					$i += 6;
				}
			}
		}

		return;
	}

	/**
	* Look up an option
	* if the option is prefixed with !, then the result becomes nagated
	*/
	function acl_get($opt, $f = 0)
	{
		static $cache;

		if (!isset($cache))
		{
			$cache = array();
		}

		$negate = false;

		if (strpos($opt, '!') === 0)
		{
			$negate = true;
			$opt = substr($opt, 1);
		}

		if (!isset($cache[$f][$opt]))
		{
			// We combine the global/local option with an OR because some options are global and local.
			// If the user has the global permission the local one is true too and vice versa
			$cache[$f][$opt] = false;

			// Is this option a global permission setting?
			if (isset($this->acl_options['global'][$opt]))
			{
				if (isset($this->acl[0]))
				{
					$cache[$f][$opt] = $this->acl[0]{$this->acl_options['global'][$opt]};
				}
			}

			// Is this option a local permission setting?
			if (isset($this->acl_options['local'][$opt]))
			{
				if (isset($this->acl[$f]))
				{
					$cache[$f][$opt] |= $this->acl[$f]{$this->acl_options['local'][$opt]};
				}
			}
		}

		// Founder always has all global options set to true...
		return ($negate) ? !$cache[$f][$opt] : $cache[$f][$opt];
	}

	/**
	* Get forums with the specified permission setting
	* if the option is prefixed with !, then the result becomes nagated
	*
	* @param clean true|false set to true if only values needs to be returned which are set/unset
	*/
	function acl_getf($opt, $clean = false)
	{
		static $cache;

		$acl_f = array();

		if (!isset($cache))
		{
			$cache = array();
		}

		$negate = false;

		if (strpos($opt, '!') === 0)
		{
			$negate = true;
			$opt = substr($opt, 1);
		}

		if (isset($this->acl_options['local'][$opt]))
		{
			foreach ($this->acl as $f => $bitstring)
			{
				// Skip global settings
				if (!$f)
				{
					continue;
				}

				$allowed = (!isset($cache[$f][$opt])) ? $this->acl_get($opt, $f) : $cache[$f][$opt];

				if (!$clean)
				{
					$acl_f[$f][$opt] = ($negate) ? !$allowed : $allowed;
				}
				else
				{
					if (($negate && !$allowed) || (!$negate && $allowed))
					{
						$acl_f[$f][$opt] = 1;
					}
				}
			}
		}

		return $acl_f;
	}

	/**
	* Get permission settings (more than one)
	*/
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

	/**
	* Get permission listing based on user_id/options/forum_ids
	*/
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
					if ($auth_setting)
					{
						$auth_ary[$forum_id][$auth_option][] = $user_id;
					}
				}
			}
		}

		return $auth_ary;
	}

	/**
	* Get raw group based permission settings
	function acl_group_raw_data($group_id = false, $opts = false, $forum_id = false)
	{
		global $db;

		$sql_group = ($group_id !== false) ? ((!is_array($group_id)) ? "group_id = $group_id" : 'group_id IN (' . implode(', ', $group_id) . ')') : '';
		$sql_forum = ($forum_id !== false) ? ((!is_array($forum_id)) ? "AND a.forum_id = $forum_id" : 'AND a.forum_id IN (' . implode(', ', $forum_id) . ')') : '';
		$sql_opts = ($opts !== false) ? ((!is_array($opts)) ? "AND ao.auth_option = '$opts'" : 'AND ao.auth_option IN (' . implode(', ', preg_replace('#^\s*(.*)\s*$#e', "\"'\" . \$db->sql_escape('\\1') . \"'\"", $opts)) . ')') : '';

		$hold_ary = array();

		// Grab group settings... 
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
*/

	/**
	* Cache data to user_permissions row
	*/
	function acl_cache(&$userdata)
	{
		global $db;
		
		// Empty user_permissions
		$userdata['user_permissions'] = '';
		
		$hold_ary = $this->acl_raw_data($userdata['user_id'], false, false);
		
		if (isset($hold_ary[$userdata['user_id']]))
		{
			$hold_ary = $hold_ary[$userdata['user_id']];
		}
		
		// Key 0 in $hold_ary are global options, all others are forum_ids

		// If this user is founder we're going to force fill the admin options ...
		if ($userdata['user_type'] == USER_FOUNDER)
		{
			foreach ($this->acl_options['global'] as $opt => $id)
			{
				if (strpos($opt, 'a_') === 0)
				{
					$hold_ary[0][$opt] = 1;
				}
			}
		}

		$hold_str = '';
		if (sizeof($hold_ary))
		{
			ksort($hold_ary);

			$last_f = 0;

			foreach ($hold_ary as $f => $auth_ary)
			{
				$ary_key = (!$f) ? 'global' : 'local';

				$bitstring = array();
				foreach ($this->acl_options[$ary_key] as $opt => $id)
				{
					if (isset($auth_ary[$opt]))
					{
						$bitstring[$id] = 1;

						$option_key = substr($opt, 0, strpos($opt, '_') + 1);

						// If one option is allowed, the global permission for this option has to be allowed too
						// example: if the user has the a_ permission this means he has one or more a_* permissions
						if (!isset($bitstring[$this->acl_options[$ary_key][$option_key]]) || !$bitstring[$this->acl_options[$ary_key][$option_key]])
						{
							$bitstring[$this->acl_options[$ary_key][$option_key]] = 1;
						}
					}
					else
					{
						$bitstring[$id] = 0;
					}
				}

				// Now this bitstring defines the permission setting for the current forum $f (or global setting)
				$bitstring = implode('', $bitstring);

				// The line number indicates the id, therefore we have to add empty lines for those ids not present
				$hold_str .= str_repeat("\n", $f - $last_f);
			
				// Convert bitstring for storage - we do not use binary/bytes because PHP's string functions are not fully binary safe
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

	/**
	* Clear one or all users cached permission settings
	*/
	function acl_clear_prefetch($user_id = false)
	{
		global $db;

		$where_sql = ($user_id !== false) ? ' WHERE user_id ' . ((is_array($user_id)) ? ' IN (' . implode(', ', array_map('intval', $user_id)) . ')' : " = $user_id") : '';

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_permissions = ''
			$where_sql";
		$db->sql_query($sql);

		return;
	}

	/**
	* Get raw acl data based on user/option/forum
	*/
	function acl_raw_data($user_id = false, $opts = false, $forum_id = false)
	{
		global $db;

		$sql_user = ($user_id !== false) ? ((!is_array($user_id)) ? "user_id = $user_id" : 'user_id IN (' . implode(', ', $user_id) . ')') : '';
		$sql_forum = ($forum_id !== false) ? ((!is_array($forum_id)) ? "AND a.forum_id = $forum_id" : 'AND a.forum_id IN (' . implode(', ', $forum_id) . ')') : '';
		$sql_opts = ($opts !== false) ? ((!is_array($opts)) ? "AND ao.auth_option = '$opts'" : 'AND ao.auth_option IN (' . implode(', ', preg_replace('#^\s*(.*)\s*$#e', "\"'\" . \$db->sql_escape('\\1') . \"'\"", $opts)) . ')') : '';

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

	/**
	* Authentication plug-ins is largely down to Sergey Kanareykin, our thanks to him.
	* @todo replace this with a new system
	*/
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
					/**
					* @todo Login Attempt++
					*/
					return $login;
				}
				
				return $user->session_create($login['user_id'], $admin, $autologin, $viewonline);
			}
		}

		trigger_error('Authentication method not found', E_USER_ERROR);
	}
}

?>