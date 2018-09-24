<?php
/***************************************************************************
 *                                sessions.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: sessions.php,v 1.1 2010/10/10 15:05:27 orynider Exp $
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


/**
 * Class: Template.
 *
 * The mx_Template class extends the native phpBB Template class, in reality only redefining the make_filename method.
 * Thus modded phpBB templates (eg eXtreme Styles MOD) will also be available for mxBB.
 *
 * @package Style
 * @author Markus, Jon Ohlsson
 * @access public
 */
class phpbb_Template extends Template
{
	var $inherit_root = '';
	var $css_style_include = array();
	var $css_include = array();
	var $js_include = array();		
	var $module_template_path = '';
	var $cloned_template_name = 'subSilver';
	var $default_template_name = 'subsilver2';
	var $template_path = 'templates/';
	var $debug_paths;
	var $phpbb_root_path	= '/';
	
	/**
	 * Constructor.
	 *
	 * Simply calling parent construtor.
	 * This is required. Reason is constructors have different method names.
	 *
	 * @access private
	 */
	function phpbb_Template($root = '.')
	{
		parent::Template($root);
		
		global $phpbb_root_path;
		
		//
		// Temp solution when the rootdir is not created
		//
		if (empty($this->root))
		{
			$this->root = $root;
		}
		
		$this->phpbb_root_path = $phpbb_root_path;
		
		if (empty($this->template_path))
		{
			$this->template_path = $template_path;
		}		
	}
	
	/**
	 * This make_filename implementation overrides parent method.
	 *
	 * Generates a full path+filename for the given filename, which can either
	 * be an absolute name, or a name relative to the rootdir for this Template
	 * object.
	 */
	function make_filename($filename, $xs_include = false)
	{
		global $phpbb_root_path, $theme;

		//
		// ?
		//
		if($this->subtemplates)
		{
			$filename = $this->subtemplates_make_filename($filename);
		}

		//
		// Check replacements list
		//
		if(!$xs_include && isset($this->replace[$filename]))
		{
			$filename = $this->replace[$filename];
		}

		$style_path = $theme['template_name'];
		$this->styles_path = $phpbb_root_path . $this->template_path;
		$this->cloned_template_name = 'subSilver';
		$this->default_template_name = 'subsilver2';
		
		if( @file_exists(@phpbb_realpath($phpbb_root_path . $this->template_path . $theme['template_name'] . '/style.cfg')) )
		{
			$cfg = parse_cfg_file($this->styles_path . $theme['template_name'] . '/style.cfg');
			$this->cloned_template_name = !empty($cfg['parent']) ? $cfg['parent'] : 'prosilver';
			$this->default_template_name = 'prosilver';
		}
		
		$this->cloned_template_path = $this->template_path . $this->cloned_template_name;		
		
		//
		// Also search for "the other" file extension
		//
		$filename2 = substr_count($filename, 'html') ? str_replace(".html", ".tpl", $filename) : str_replace(".tpl", ".html", $filename);
		
		//
		// Look for new styles
		//
		if (!empty($phpbb_root_path))
		{				
			$moduleDefault = $this->default_template_name;
			
			
			$this->debug_paths .= '<br>phpbb';
			$fileSearch = array();
			$fileSearch[] = $style_path . '/template'; // First check current template
			$fileSearch[] = $this->cloned_template_name . '/template'; // Then check Cloned template
			$fileSearch[] = $moduleDefault . '/template'; // Finally check Default template
			$fileSearch[] = './'; // Compatibility with primitive modules

			$temppath = $this->doFileSearch($fileSearch, $filename, $filename2, 'templates/', $phpbb_root_path);
			if (!empty($this->module_template_path))
			{
				return $temppath;
			}
		}

		//
		// Look for old styles
		//
		if (!empty($phpbb_root_path))
		{				
			$moduleDefault = $this->default_template_name;
			
			
			$this->debug_paths .= '<br>phpbb';
			$fileSearch = array();
			$fileSearch[] = $style_path; // First check current template
			$fileSearch[] = $this->cloned_template_name; // Then check Cloned template
			$fileSearch[] = $moduleDefault; // Finally check Default template
			$fileSearch[] = './'; // Compatibility with primitive modules

			$temppath = $this->doFileSearch($fileSearch, $filename, $filename2, 'templates/', $phpbb_root_path);
			if (!empty($this->module_template_path))
			{
				return $temppath;
			}
		}
		
		//
		// Look at Root folder
		//		
		if (!empty($phpbb_root_path))
		{				
			$moduleDefault = $this->default_template_name;
			
			
			$this->debug_paths .= '<br>xs_mod';
			$fileSearch = array();
			$fileSearch[] = 'tpl';
			$fileSearch[] = $this->tpldir;			
			//$fileSearch[] = $style_path; // First check current template
			//$fileSearch[] = $this->cloned_template_name; // Then check Cloned template
			//$fileSearch[] = $moduleDefault; // Finally check Default template
			$fileSearch[] = './'; // Compatibility with primitive modules

			$temppath = $this->doFileSearch($fileSearch, $filename, $filename2, 'xs_mod/', $phpbb_root_path);
			if (!empty($this->module_template_path))
			{
				return $temppath;
			}
		}		
		//
		// Look at Root folder
		//
		$this->debug_paths .= '<br>CORE';
		$fileSearch = array();
		$fileSearch[] = $style_path; // First check current template
		$fileSearch[] = $this->cloned_template_name; // Then check Cloned template
		$fileSearch[] = $this->default_template_name; // Then check Default template
		$fileSearch[] = 'all';		
		$fileSearch[] = './';

		$temppath = $this->doFileSearch($fileSearch, $filename, $filename2, 'templates/', $phpbb_root_path);
		if (!empty($this->module_template_path))
		{
			return $temppath;
		}

		//
		// Look at Custom Root folder..............this is used my mx_mod installers too.......this does not use standard templates folders wich are set when the template was re-initialized and defined as custom var
		//
		$this->debug_paths .= '<br>This';
		$fileSearch = array();
		$fileSearch[] = './';
		$fileSearch[] = $style_path; // First check current template
		$fileSearch[] = $this->cloned_template_name; // Then check Cloned template
		$fileSearch[] = $this->default_template_name; // Then check Default template

		$temppath = $this->doFileSearch($fileSearch, $filename, $filename2, $this->root, $phpbb_root_path);
		
		if (!empty($this->module_template_path))
		{
			return $temppath;
		}

		//
		// Look at phpBB-Root folder...
		//
		$this->debug_paths .= '<br>phpbb2';
		$fileSearch = array();
		$fileSearch[] = $style_path; // First check current template
		$fileSearch[] = $this->cloned_template_name; // Then check Cloned template
		$fileSearch[] = $this->default_template_name; // Then check Default template
		$fileSearch[] = './';

		$temppath = $this->doFileSearch($fileSearch, $filename, $filename2, 'templates/', $phpbb_root_path, false);
		
		if (!empty($this->module_template_path))
		{
			return $temppath;
		}	

		if( !file_exists($filename) )
		{
			die("Template->make_filename(): Error - file $filename does not exist. <br />Class-Root: $this->root <br /> $this->debug_paths");
		}

		echo($this->debug_paths);
		die("Template->make_filename(): Error - file $filename does not exist. <br />Class-Root: $this->root <br />Core: $phpbb_root_path <br />Current style: $style_path <br />Cloned style: $this->cloned_template_name <br />Default style: $this->default_template_name <br />Custom module default style: $moduleDefault");
	}
	
	/**
	 * Help function
	 *
	 * @param unknown_type $fileSearch
	 * @param unknown_type $filename
	 * @param unknown_type $filename2
	 * @param unknown_type $module_root_path
	 * @return unknown
	 */
	function doFileSearch($fileSearch, $filename, $filename2, $root, $root_path = '', $check_file2 = true)
	{
		$this->module_template_path = '';	
		foreach ($fileSearch as $key => $path)
		{
			if (!empty($path) && ($path !== './'))
			{
				$this->debug_paths .= '<br>' . $root_path . $root . $path . '/' . $filename;
				
				if( file_exists($root_path . $root . $path . '/' . $filename) )
				{
					$this->module_template_path = $root . $path . '/';
					return $root_path . $root . $path . '/' . $filename;
				}
				else if( file_exists($root . '/' . $filename) )
				{
					$this->module_template_path = $root . '/';
					return $root . '/' . $filename;
				}
				
				if ($check_file2 && file_exists($root_path . $root . $path . '/' . $filename2))
				{
					$this->module_template_path = $root . $path . '/';
					return $root_path . $root . $path . '/' . $filename2;
				}
				else if ($check_file2 && file_exists($root . '/' . $filename2))
				{
					$this->module_template_path = $root . '/';
					return $root . '/' . $filename2;
				}				
			}
			else if ($path == './')
			{
				if( file_exists($root_path . $root . $filename) )
				{
					$this->module_template_path = $root;
					return $root_path . $root . $filename;
				}
				if ($check_file2)
				{
					if( file_exists($root_path . $root . $filename2) )
					{
						$this->module_template_path = $root;
						return $root_path . $root . $filename2;
					}
				}
			}
		}
	}

	/**
	 * set_template
	 *
	 * This set_template implementation overrides parent method.
	 * Generates a full path, which can either
	 * be an absolute, or relative to the rootdir for this Template
	 * object.
	 *
	 * @access public
	 */
	function set_template()
	{
		global $module_root_path, $root_path, $phpbb_root_path, $theme, $user, $block;

		$style_path = $theme['template_name'] . '/';

		//
		// Look at mxBB-Module folder.........................................................................mxBB-module
		//
		if (!empty($module_root_path))
		{
			$this->module_template_path = '';
			$moduleDefault = $default_template_name;

			if( file_exists($module_root_path . 'templates/' . $style_path . '/') )
			{
				//
				// First check current template
				//
				$this->root = $module_root_path . 'templates/' . $style_path . '/';
				$this->module_template_path = 'templates/' . $style_path . '/';
			}
			else if( file_exists($module_root_path . 'templates/' . $user->cloned_template_name . '/') && !empty($user->cloned_template_name))
			{
				//
				// Then check Cloned template
				//
				$this->root = $module_root_path . 'templates/' . $user->cloned_template_name . '/';
				$this->module_template_path = 'templates/' . $user->cloned_template_name . '/';
			}
			else if( file_exists($module_root_path . 'templates/' . $moduleDefault . '/') )
			{
				//
				// Then check default template
				//
				$this->root = $module_root_path . 'templates/' . $moduleDefault . '/';
				$this->module_template_path = 'templates/' . $moduleDefault . '/';
			}
			else if( file_exists($module_root_path . 'templates/') )
			{
				//
				// Finally check the template root (for compatibility with some old modules)
				//
				$this->root = $module_root_path . 'templates/';
				$this->module_template_path = 'templates/';
			}

			if (!empty($this->module_template_path))
			{
				return '';
			}
		}

		//
		// Look at mxBB-Root folder.........................................................................mxBB-Root
		//
		if( file_exists($root_path . 'templates/' . $style_path . '/') )
		{
			//
			// First check current template
			//
			$this->root = $root_path . 'templates/' . $style_path . '/';
			$this->cachepath = $root_path . 'cache/tpl_' . $style_path . '_';
		}
		else if( file_exists($root_path . 'templates/' . $user->cloned_template_name . '/') && !empty($user->cloned_template_name))
		{
			//
			// Then check Cloned template
			//
			$this->root = $root_path . 'templates/' . $user->cloned_template_name . '/';
			$this->cachepath = $root_path . 'cache/tpl_' . $user->cloned_template_name . '_';
		}
		else if( file_exists($root_path . 'templates/' . $user->default_template_name . '/') )
		{
			//
			// Then check Default template
			//
			$this->root = $root_path . 'templates/' . $user->default_template_name . '/';
			$this->cachepath = $root_path . 'cache/tpl_' . $user->default_template_name . '_';
		}
		//
		// Look at Custom Root folder..............this is used my mx_mod installers too.......this does not use standard templates folders wich are set when the template was re-initialized and defined as custom var
		//
		else if( file_exists( $this->root . '/') )
		{
			$this->root =  $this->root . '/';
			$this->cachepath = $root_path . 'cache/tpl_' . $this->root . '_';
		}
		else if( file_exists($this->root . '/' . $style_path . '/') )
		{
			//
			// First check current template
			//
			$this->root = $this->root . '/' . $style_path . '/';
			$this->cachepath = $root_path . 'cache/tpl_' . $style_path . '_';
		}
		else if( file_exists($this->root . '/' . $style_path . '/') )
		{
			//
			// tpl - html
			//
			$this->root = $this->root. '/' . $style_path . '/';
		}
		else if( file_exists($this->root . '/' . $user->default_template_name . '/') )
		{
			//
			// Then check current template
			//
			$this->root = $root_path . '/' . $user->default_template_name . '/';
			$this->cachepath = $root_path . 'cache/tpl_' . $user->default_template_name . '_';
		}
		else if( file_exists($this->root . '/' . $moduleDefault . '/') )
		{
			//
			// Finally check the Custom Root folde(for compatibility with some old modules)
			//
			$this->root = $this->root . '/' . $moduleDefault . '/';
			$this->cachepath = $root_path . 'cache/tpl_' . $moduleDefault . '_';
		}
		else
		{
			//
			// phpBB.........................................................................phpBB
			//
			if( file_exists($phpbb_root_path . 'templates/' . $style_path . '/') )
			{
				//
				// First check current template
				//
				$this->root = $phpbb_root_path . 'templates/' . $style_path . '/';
				$this->cachepath = $phpbb_root_path . 'cache/tpl_' . $style_path. '_';				
			}
			else if( file_exists($phpbb_root_path . 'templates/' . $user->cloned_template_name . '/') && !empty($user->cloned_template_name))
			{
				//
				// Then check Cloned
				//
				$this->root = $phpbb_root_path . 'templates/' . $user->cloned_template_name . '/';
				$this->cachepath = $phpbb_root_path . 'cache/tpl_' . $user->cloned_template_name . '_';				
			}
			else if( file_exists($phpbb_root_path . 'templates/' . $user->default_template_name . '/') && !empty($user->default_template_name))
			{
				//
				// Then check Default
				//
				$this->root = $phpbb_root_path . 'templates/' . $user->default_template_name . '/';
				$this->cachepath = $phpbb_root_path . 'cache/tpl_' . $user->default_template_name . '_';				
			}
			else if (file_exists($phpbb_root_path . 'styles/' . $user->theme['template_path'] . '/template'))
			{
				//
				// Then check phpBB3 style
				//			
				$this->root = $phpbb_root_path . 'styles/' . $user->theme['template_path'] . '/template';
				$this->cachepath = $phpbb_root_path . 'cache/tpl_' . $user->theme['template_path'] . '_';
			}			
			else if( file_exists($phpbb_root_path . $this->root . '/') )
			{
				$this->root = $phpbb_root_path . $this->root . '/';
				$this->cachepath = $phpbb_root_path . 'cache/tpl_' . $this->root . '_';
			}
			else if( file_exists($this->root . '/') )
			{
				//
				// Check if it's an absolute or relative path.
				//
				$this->root = phpbb_realpath($this->root . '/');
				$this->cachepath = $phpbb_root_path . 'cache/tpl_' . phpbb_realpath($this->root . '/') . '_';
			}
			else
			{
				trigger_error('Template path could not be found: styles/' . $user->theme['template_path'] . '/template', E_USER_ERROR);
			}		
		}
		$this->_rootref = &$this->_tpldata['.'][0];
		
		return true;	
	}
}
	
//
// Adds/updates a new session to the database for the given userid.
// Returns the new session ID on success.
//
function session_begin($user_id, $user_ip, $page_id, $auto_create = 0, $enable_autologin = 0, $admin = 0)
{
	global $db, $board_config;
	global $_COOKIE, $_GET, $SID;

	$cookiename = $board_config['cookie_name'];
	$cookiepath = $board_config['cookie_path'];
	$cookiedomain = $board_config['cookie_domain'];
	$cookiesecure = $board_config['cookie_secure'];

	if ( isset($_COOKIE[$cookiename . '_sid']) || isset($_COOKIE[$cookiename . '_data']) )
	{
		$session_id = isset($_COOKIE[$cookiename . '_sid']) ? $_COOKIE[$cookiename . '_sid'] : '';
		$sessiondata = isset($_COOKIE[$cookiename . '_data']) ? unserialize(stripslashes($_COOKIE[$cookiename . '_data'])) : array();
		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$sessiondata = array();
		$session_id = ( isset($_GET['sid']) ) ? $_GET['sid'] : '';
		$sessionmethod = SESSION_METHOD_GET;
	}

	//
	if (!preg_match('/^[A-Za-z0-9]*$/', $session_id)) 
	{
		$session_id = '';
	}

	$page_id = (int) $page_id;

	$last_visit = 0;
	$current_time = time();

	//
	// Are auto-logins allowed?
	// If allow_autologin is not set or is true then they are
	// (same behaviour as old 2.0.x session code)
	//
	if (isset($board_config['allow_autologin']) && !$board_config['allow_autologin'])
	{
		$enable_autologin = $sessiondata['autologinid'] = false;
	}

	// 
	// First off attempt to join with the autologin value if we have one
	// If not, just use the user_id value
	//
	$userdata = array();

	if ($user_id != ANONYMOUS)
	{
		if (isset($sessiondata['autologinid']) && (string) !empty($sessiondata['autologinid']) && $user_id)
		{
			$sql = 'SELECT u.*, u.user_id as user_colour, u.user_level as user_type, u.user_avatar as avatar, u.user_avatar_type as avatar_type 
				FROM ' . USERS_TABLE . ' u, ' . SESSIONS_KEYS_TABLE . ' k
				WHERE u.user_id = ' . (int) $user_id . "
					AND u.user_active = 1
					AND k.user_id = u.user_id
					AND k.key_id = '" . md5($sessiondata['autologinid']) . "'";
			if (!($result = $db->sql_query($sql)))
			{
				message_die(CRITICAL_ERROR, 'Error doing DB query userdata row fetch', '', __LINE__, __FILE__, $sql);
			}

			$userdata = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		
			$enable_autologin = $login = 1;
		}
		else if (!$auto_create)
		{
			$sessiondata['autologinid'] = '';
			$sessiondata['userid'] = $user_id;

			$sql = 'SELECT u.*, u.user_id as user_colour, u.user_level as user_type, u.user_avatar as avatar, u.user_avatar_type as avatar_type
				FROM ' . USERS_TABLE . ' u
				WHERE user_id = ' . (int) $user_id . '
					AND user_active = 1';
			if (!($result = $db->sql_query($sql)))
			{
				message_die(CRITICAL_ERROR, 'Error doing DB query userdata row fetch', '', __LINE__, __FILE__, $sql);
			}

			$userdata = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$login = 1;
		}
	}

	//
	// At this point either $userdata should be populated or
	// one of the below is true
	// * Key didn't match one in the DB
	// * User does not exist
	// * User is inactive
	//
	if (!sizeof($userdata) || !is_array($userdata) || !$userdata)
	{
		$sessiondata['autologinid'] = '';
		$sessiondata['userid'] = $user_id = ANONYMOUS;
		$enable_autologin = $login = 0;

		$sql = 'SELECT u.*, u.user_id as user_colour, u.user_level as user_type, u.user_avatar as avatar, u.user_avatar_type as avatar_type
			FROM ' . USERS_TABLE . ' u
			WHERE user_id = ' . (int) $user_id;
		if (!($result = $db->sql_query($sql)))
		{
			message_die(CRITICAL_ERROR, 'Error doing DB query userdata row fetch', '', __LINE__, __FILE__, $sql);
		}

		$userdata = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
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
		$sql .= " OR ban_email LIKE '" . str_replace("\'", "''", $userdata['user_email']) . "' 
			OR ban_email LIKE '" . substr(str_replace("\'", "''", $userdata['user_email']), strpos(str_replace("\'", "''", $userdata['user_email']), "@")) . "'";
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
		SET session_user_id = $user_id, session_start = $current_time, session_time = $current_time, session_page = $page_id, session_logged_in = $login, session_admin = $admin
		WHERE session_id = '" . $session_id . "' 
			AND session_ip = '$user_ip'";
	if ( !$db->sql_query($sql) || !$db->sql_affectedrows() )
	{
		$session_id = md5(dss_rand());

		$sql = "INSERT INTO " . SESSIONS_TABLE . "
			(session_id, session_user_id, session_start, session_time, session_ip, session_page, session_logged_in, session_admin)
			VALUES ('$session_id', $user_id, $current_time, $current_time, '$user_ip', $page_id, $login, $admin)";
		if ( !$db->sql_query($sql) )
		{
			message_die(CRITICAL_ERROR, 'Error creating new session', '', __LINE__, __FILE__, $sql);
		}
	}

	if ( $user_id != ANONYMOUS )
	{
		$last_visit = ( $userdata['user_session_time'] > 0 ) ? $userdata['user_session_time'] : $current_time; 

		if (!$admin)
		{
			$sql = "UPDATE " . USERS_TABLE . " 
				SET user_session_time = $current_time, user_session_page = $page_id, user_lastvisit = $last_visit
				WHERE user_id = $user_id";
			if ( !$db->sql_query($sql) )
			{
				message_die(CRITICAL_ERROR, 'Error updating last visit time', '', __LINE__, __FILE__, $sql);
			}
		}

		$userdata['user_lastvisit'] = $last_visit;

		//
		// Regenerate the auto-login key
		//
		if ($enable_autologin)
		{
			$auto_login_key = dss_rand() . dss_rand();
			
			if (isset($sessiondata['autologinid']) && (string) $sessiondata['autologinid'])
			{
				$sql = 'UPDATE ' . SESSIONS_KEYS_TABLE . "
					SET last_ip = '$user_ip', key_id = '" . md5($auto_login_key) . "', last_login = $current_time
					WHERE key_id = '" . md5($sessiondata['autologinid']) . "'";
			}
			else
			{
				$sql = 'INSERT INTO ' . SESSIONS_KEYS_TABLE . "(key_id, user_id, last_ip, last_login)
					VALUES ('" . md5($auto_login_key) . "', $user_id, '$user_ip', $current_time)";
			}

			if ( !$db->sql_query($sql) )
			{
				message_die(CRITICAL_ERROR, 'Error updating session key', '', __LINE__, __FILE__, $sql);
			}
			
			$sessiondata['autologinid'] = $auto_login_key;
			unset($auto_login_key);
		}
		else
		{
			$sessiondata['autologinid'] = '';
		}

//		$sessiondata['autologinid'] = (!$admin) ? (( $enable_autologin && $sessionmethod == SESSION_METHOD_COOKIE ) ? $auto_login_key : '') : $sessiondata['autologinid'];
		$sessiondata['userid'] = $user_id;
	}

	$userdata['session_id'] = $session_id;
	$userdata['session_ip'] = $user_ip;
	$userdata['session_user_id'] = $user_id;
	$userdata['session_logged_in'] = $login;
	$userdata['session_page'] = $page_id;
	$userdata['session_start'] = $current_time;
	$userdata['session_time'] = $current_time;
	$userdata['session_admin'] = $admin;
	$userdata['session_key'] = $sessiondata['autologinid'];

	setcookie($cookiename . '_data', serialize($sessiondata), $current_time + 31536000, $cookiepath, $cookiedomain, $cookiesecure);
	setcookie($cookiename . '_sid', $session_id, 0, $cookiepath, $cookiedomain, $cookiesecure);

	$SID = 'sid=' . $session_id;

	return $userdata;
}

//
// Checks for a given user session, tidies session table and updates user
// sessions at each page refresh
//
function session_pagestart($user_ip, $thispage_id)
{
	global $db, $lang, $board_config;
	global $_COOKIE, $_GET, $SID;

	$cookiename = $board_config['cookie_name'];
	$cookiepath = $board_config['cookie_path'];
	$cookiedomain = $board_config['cookie_domain'];
	$cookiesecure = $board_config['cookie_secure'];

	$current_time = time();
	unset($userdata);

	if ( isset($_COOKIE[$cookiename . '_sid']) || isset($_COOKIE[$cookiename . '_data']) )
	{
		$sessiondata = isset( $_COOKIE[$cookiename . '_data'] ) ? unserialize(stripslashes($_COOKIE[$cookiename . '_data'])) : array();
		$session_id = isset( $_COOKIE[$cookiename . '_sid'] ) ? $_COOKIE[$cookiename . '_sid'] : '';
		$sessionmethod = SESSION_METHOD_COOKIE;
	}
	else
	{
		$sessiondata = array();
		$session_id = ( isset($_GET['sid']) ) ? $_GET['sid'] : '';
		$sessionmethod = SESSION_METHOD_GET;
	}

	// 
	if (!preg_match('/^[A-Za-z0-9]*$/', $session_id))
	{
		$session_id = '';
	}

	$thispage_id = (int) $thispage_id;

	//
	// Does a session exist?
	//
	if ( !empty($session_id) )
	{
		//
		// session_id exists so go ahead and attempt to grab all
		// data in preparation
		//
		$sql = "SELECT u.*, u.user_id as user_colour, u.user_level as user_type, u.user_avatar as avatar, u.user_avatar_type as avatar_type, s.*
			FROM " . SESSIONS_TABLE . " s, " . USERS_TABLE . " u
			WHERE s.session_id = '$session_id'
				AND u.user_id = s.session_user_id";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(CRITICAL_ERROR, 'Error doing DB query userdata row fetch', '', __LINE__, __FILE__, $sql);
		}

		$userdata = $db->sql_fetchrow($result);

		//
		// Did the session exist in the DB?
		//
		if ( isset($userdata['user_id']) )
		{
			//
			// Do not check IP assuming equivalence, if IPv4 we'll check only first 24
			// bits ... I've been told (by vHiker) this should alleviate problems with 
			// load balanced et al proxies while retaining some reliance on IP security.
			//
			$ip_check_s = substr($userdata['session_ip'], 0, 6);
			$ip_check_u = substr($user_ip, 0, 6);

			if ($ip_check_s == $ip_check_u)
			{
				$SID = ($sessionmethod == SESSION_METHOD_GET || defined('IN_ADMIN')) ? 'sid=' . $session_id : '';

				//
				// Only update session DB a minute or so after last update
				//
				if ( $current_time - $userdata['session_time'] > 60 )
				{
					// A little trick to reset session_admin on session re-usage
					$update_admin = (!defined('IN_ADMIN') && $current_time - $userdata['session_time'] > ($board_config['session_length']+60)) ? ', session_admin = 0' : '';

					$sql = "UPDATE " . SESSIONS_TABLE . " 
						SET session_time = $current_time, session_page = $thispage_id$update_admin
						WHERE session_id = '" . $userdata['session_id'] . "'";
					if ( !$db->sql_query($sql) )
					{
						message_die(CRITICAL_ERROR, 'Error updating sessions table', '', __LINE__, __FILE__, $sql);
					}

					if ( $userdata['user_id'] != ANONYMOUS )
					{
						$sql = "UPDATE " . USERS_TABLE . " 
							SET user_session_time = $current_time, user_session_page = $thispage_id
							WHERE user_id = " . $userdata['user_id'];
						if ( !$db->sql_query($sql) )
						{
							message_die(CRITICAL_ERROR, 'Error updating sessions table', '', __LINE__, __FILE__, $sql);
						}
					}

					session_clean($userdata['session_id']);

					setcookie($cookiename . '_data', serialize($sessiondata), $current_time + 31536000, $cookiepath, $cookiedomain, $cookiesecure);
					setcookie($cookiename . '_sid', $session_id, 0, $cookiepath, $cookiedomain, $cookiesecure);
				}

				// Add the session_key to the userdata array if it is set
				if ( isset($sessiondata['autologinid']) && !empty($sessiondata['autologinid']) )
				{
					$userdata['session_key'] = $sessiondata['autologinid'];
				}

				return $userdata;
			}
		}
	}

	//
	// If we reach here then no (valid) session exists. So we'll create a new one,
	// using the cookie user_id if available to pull basic user prefs.
	//
	$user_id = ( isset($sessiondata['userid']) ) ? intval($sessiondata['userid']) : ANONYMOUS;

	if ( !($userdata = session_begin($user_id, $user_ip, $thispage_id, TRUE)) )
	{
		message_die(CRITICAL_ERROR, 'Error creating user session', '', __LINE__, __FILE__, $sql);
	}

	return $userdata;

}

/**
* Terminates the specified session
* It will delete the entry in the sessions table for this session,
* remove the corresponding auto-login key and reset the cookies
*/
function session_end($session_id, $user_id)
{
	global $db, $lang, $board_config, $userdata;
	global $_COOKIE, $_GET, $SID;

	$cookiename = $board_config['cookie_name'];
	$cookiepath = $board_config['cookie_path'];
	$cookiedomain = $board_config['cookie_domain'];
	$cookiesecure = $board_config['cookie_secure'];

	$current_time = time();

	if (!preg_match('/^[A-Za-z0-9]*$/', $session_id))
	{
		return;
	}
	
	//
	// Delete existing session
	//
	$sql = 'DELETE FROM ' . SESSIONS_TABLE . " 
		WHERE session_id = '$session_id' 
			AND session_user_id = $user_id";
	if ( !$db->sql_query($sql) )
	{
		message_die(CRITICAL_ERROR, 'Error removing user session', '', __LINE__, __FILE__, $sql);
	}

	//
	// Remove this auto-login entry (if applicable)
	//
	if ( isset($userdata['session_key']) && !empty($userdata['session_key']) )
	{
		$autologin_key = md5($userdata['session_key']);
		$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
			WHERE user_id = ' . (int) $user_id . "
				AND key_id = '$autologin_key'";
		if ( !$db->sql_query($sql) )
		{
			message_die(CRITICAL_ERROR, 'Error removing auto-login key', '', __LINE__, __FILE__, $sql);
		}
	}

	//
	// We expect that message_die will be called after this function,
	// but just in case it isn't, reset $userdata to the details for a guest
	//
	$sql = 'SELECT u.*, u.user_id as user_colour, u.user_level as user_type, u.user_avatar as avatar, u.user_avatar_type as avatar_type
		FROM ' . USERS_TABLE . ' u
		WHERE user_id = ' . ANONYMOUS;
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(CRITICAL_ERROR, 'Error obtaining user details', '', __LINE__, __FILE__, $sql);
	}
	if ( !($userdata = $db->sql_fetchrow($result)) )
	{
		message_die(CRITICAL_ERROR, 'Error obtaining user details', '', __LINE__, __FILE__, $sql);
	}
	$db->sql_freeresult($result);


	setcookie($cookiename . '_data', '', $current_time - 31536000, $cookiepath, $cookiedomain, $cookiesecure);
	setcookie($cookiename . '_sid', '', $current_time - 31536000, $cookiepath, $cookiedomain, $cookiesecure);

	return true;
}

/**
* Removes expired sessions and auto-login keys from the database
*/
function session_clean($session_id)
{
	global $board_config, $db;

	//
	// Delete expired sessions
	//
	$sql = 'DELETE FROM ' . SESSIONS_TABLE . ' 
		WHERE session_time < ' . (time() - (int) $board_config['session_length']) . " 
			AND session_id <> '$session_id'";
	if ( !$db->sql_query($sql) )
	{
		message_die(CRITICAL_ERROR, 'Error clearing sessions table', '', __LINE__, __FILE__, $sql);
	}

	//
	// Delete expired auto-login keys
	// If max_autologin_time is not set then keys will never be deleted
	// (same behaviour as old 2.0.x session code)
	//
	if (!empty($board_config['max_autologin_time']) && $board_config['max_autologin_time'] > 0)
	{
		$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
			WHERE last_login < ' . (time() - (86400 * (int) $board_config['max_autologin_time']));
		$db->sql_query($sql);
	}

	return true;
}

/**
* Reset all login keys for the specified user
* Called on password changes
*/
function session_reset_keys($user_id, $user_ip)
{
	global $db, $userdata, $board_config;

	$key_sql = ($user_id == $userdata['user_id'] && !empty($userdata['session_key'])) ? "AND key_id != '" . md5($userdata['session_key']) . "'" : '';

	$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
		WHERE user_id = ' . (int) $user_id . "
			$key_sql";

	if ( !$db->sql_query($sql) )
	{
		message_die(CRITICAL_ERROR, 'Error removing auto-login keys', '', __LINE__, __FILE__, $sql);
	}

	$where_sql = 'session_user_id = ' . (int) $user_id;
	$where_sql .= ($user_id == $userdata['user_id']) ? " AND session_id <> '" . $userdata['session_id'] . "'" : '';
	$sql = 'DELETE FROM ' . SESSIONS_TABLE . "
		WHERE $where_sql";
	if ( !$db->sql_query($sql) )
	{
		message_die(CRITICAL_ERROR, 'Error removing user session(s)', '', __LINE__, __FILE__, $sql);
	}

	if ( !empty($key_sql) )
	{
		$auto_login_key = dss_rand() . dss_rand();

		$current_time = time();
		
		$sql = 'UPDATE ' . SESSIONS_KEYS_TABLE . "
			SET last_ip = '$user_ip', key_id = '" . md5($auto_login_key) . "', last_login = $current_time
			WHERE key_id = '" . md5($userdata['session_key']) . "'";
		
		if ( !$db->sql_query($sql) )
		{
			message_die(CRITICAL_ERROR, 'Error updating session key', '', __LINE__, __FILE__, $sql);
		}

		// And now rebuild the cookie
		$sessiondata['userid'] = $user_id;
		$sessiondata['autologinid'] = $auto_login_key;
		$cookiename = $board_config['cookie_name'];
		$cookiepath = $board_config['cookie_path'];
		$cookiedomain = $board_config['cookie_domain'];
		$cookiesecure = $board_config['cookie_secure'];

		setcookie($cookiename . '_data', serialize($sessiondata), $current_time + 31536000, $cookiepath, $cookiedomain, $cookiesecure);
		
		$userdata['session_key'] = $auto_login_key;
		unset($sessiondata);
		unset($auto_login_key);
	}
}

/**
 * Class: user.
 *
 * @package Style
 * @author FlorinCB
 * @access public
 */
class user
{
	//
	// Implementation Conventions:
	// Properties and methods prefixed with underscore are intented to be private. ;-)
	//

	// ------------------------------
	// Vars
	//
	/**#@+
	 * user class specific vars
	 *
	 */
	var $loaded_langs = array();
	var $loaded_styles = array();
	var $loaded_default_styles = array();
	
	var $lang_path = 'language/';
	var $template_path = 'templates/';
	var $styles_path = 'templates/';

	var $template_name = '';
	var $template_names = array();
	var $current_template_path = '';
	
	/**
	 * @var string	ISO code of the default board language
	 */
	protected $default_language;
	protected $default_language_name;
	/**
	 * @var string	ISO code of the User's language
	 */
	protected $user_language;
	protected $user_language_name;
	protected $phpbb_root_path;
	
	var $lang;		
	var $lang_iso = 'en';		
	var $lang_dir = 'lang_english';
	//
	var $img_lang_dir = 'en';
	var $lang_english_name = 'English';		
	var $lang_local_name = 'English United Kingdom';	
	var $language_list = array();	
	
	var $cloned_template_name = 'subSilver';
	var $default_template_name = 'subsilver2';
	
	var $cloned_current_template_name = 'prosilver';
	var $default_current_template_name = '';	
	
	var $cloned_current_template_path = 'templates/subSilver';
	var $default_current_template_path = 'templates/subsilver2';
	
	var $imageset_backend = 'phpbb2';
	var $ext_imageset_backend = 'phpbb2';	
	
	var $imageset_path = '/theme/images/';	
	var $img_array = array();
	
	var $default_module_style = '';
	var $module_lang_path = array();
	
	var $style = array();
	var $theme = array();
	
	// Able to add new options (up to id 31)
	var $keyoptions = array('viewimg' => 0, 'viewflash' => 1, 'viewsmilies' => 2, 'viewsigs' => 3, 'viewavatars' => 4, 'viewcensors' => 5, 'attachsig' => 6, 'bbcode' => 8, 'smilies' => 9, 'sig_bbcode' => 15, 'sig_smilies' => 16, 'sig_links' => 17);
	
	var $is_admin = false;
	
	var $page_id = '';
	var $user_ip = '';

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db; 

	var $cookie_data = array();
	var $page = array();
	var $data = array(); // For future Olympus comp.
	var $service_providers; // For future Ascraeus comp.
	var $browser = '';
	var $forwarded_for = '';
	var $host = '';
	var $session_id = '';
	var $ip = '';
	var $datetime = '';
	var $load = 0;
	var $time_now = 0;
	var $update_session_page = true;
	
	//var  $phpbb_root_path;	
	/**#@-*/

	// ------------------------------
	// Properties
	//

	// ------------------------------
	// Constructor
	//
	function user()
	{
		global $cache, $board_config, $db, $phpbb_root_path, $phpEx;
 		
		$this->cache = $cache;
		$this->config = $board_config;
		$this->db = $db;
		$this->user = $this;
		$this->service_providers = array('user_id'	=> 1, 'session_id'	=> 0, 'provider'	=> '', 'oauth_token' => '');		
		$this->phpbb_root_path = $phpbb_root_path;		
		$this->php_ext = $phpEx;

		
		$this->lang_path = $phpbb_root_path . 'language/';
		$this->load();
		$this->setup();
	}
	// ------------------------------
	// Private Methods
	//
	
	/**
	 * Load sessions
	 * @access public
	 *
	 */
	function load()
	{
		global $user_ip;
	
		$this->user_ip = $user_ip;
		$this->page_id = PAGE_INDEX;
		
		//
		// Populate user data
		//			
		$this->data = session_pagestart($this->user_ip, $this->page_id);
		
		if (preg_match('/bot|crawl|curl|dataprovider|search|get|spider|find|java|majesticsEO|google|yahoo|teoma|contaxe|yandex|libwww-perl|facebookexternalhit/i', $_SERVER['HTTP_USER_AGENT'])) 
		{
		    $this->data['is_bot'] = true;
		}
		else
		{
		    $this->data['is_bot'] = false;
		}
		
		$this->data['user_topic_sortby_type'] = 't';
		$this->data['user_topic_sortby_dir'] = 'd';
		$this->data['user_topic_show_days'] = 0;

		$this->data['user_post_sortby_type'] = 't';
		$this->data['user_post_sortby_dir'] = 'a';
		$this->data['user_post_show_days'] = 0;

		
		$this->data['user_form_salt'] = bin2hex(random_bytes(8));
		
		//
		// Populate session_id
		//
		$this->session_id = $this->data['session_id'];
	}
	
	// ------------------------------
	// Public Methods
	//

	/**
	 * Init user class.
	 * Populate $userdata, $lang
	 *
	 * @access public
	 * @param unknown_type $user_ip
	 * @param unknown_type $page_id
	 */
	function init( $user_ip, $page_id, $init_style = true )
	{
		//
		// Define basic constants
		//
		$this->page_id = $page_id;
		$this->user_ip = $user_ip;

		//
		// Inititate User data
		//
		$this->_init_session($user_ip, $thispage_id);
		$this->_init_userprefs();

		//
		// Inititate User style
		//
		if ( $init_style )
		{
			$this->_init_style();
		}
	}
	
	/**
	 * Init session.
	 *
	 * Start session management (phpBB 2.0.x)
	 * - populate $userdata, $user->data
	 *
	 * @access private
	 * @param unknown_type $user_ip
	 * @param unknown_type $page_id
	 */
	function _init_session()
	{
		global $userdata, $phpbb_root_path;
		$this->session_begin();
		$this->setup();		
		$this->data = $userdata; //for compatibility with Olympus style modules
		
		// Give us some basic information
		$this->time_now				= time();
		
		$this->browser				= $_SERVER['HTTP_USER_AGENT'];
		$this->referer				= $_SERVER['Referer'];
		$this->forwarded_for		= $_SERVER['X-Forwarded-For'];

		$this->host					= extract_current_hostname();
		$this->page					= extract_current_page($phpbb_root_path);
		
		$this->is_admin = $this->data['user_level'] == ADMIN && $this->data['session_logged_in'];
	}
	
	/**
	 * Init userprefs.
	 *
	 * Initialise user settings on page load.
	 * - populate $lang, $theme, $images and initiate $template
	 *
	 * @access private
	 */
	function _init_userprefs()
	{
		global $userdata, $board_config, $portal_config, $theme, $images;
		global $template, $lang, $phpEx, $phpbb_root_path, $db;
		global $nav_links;

		if ( $userdata['user_id'] != ANONYMOUS )
		{
			if ( !empty($userdata['user_lang']))
			{
				$language = phpbb_ltrim(basename(phpbb_rtrim($userdata['user_lang'])), "'");
			}

			if ( !empty($userdata['user_dateformat']) )
			{
				$board_config['default_dateformat'] = $userdata['user_dateformat'];
			}

			if ( isset($userdata['user_timezone']) )
			{
				$board_config['board_timezone'] = $userdata['user_timezone'];
			}
		}
		//
		// New code (phpBB 2.0.21) from here on, to comment below
		//
		else
		{
			$language = phpbb_ltrim(basename(phpbb_rtrim($board_config['default_lang'])), "'");
		}

		if ( !file_exists(@phpbb_realpath($phpbb_root_path . 'language/lang_' . $language . '/lang_main.'.$phpEx)) )
		{
			if ( $userdata['user_id'] != ANONYMOUS )
			{
				// For logged in users, try the board default language next
				$language = phpbb_ltrim(basename(phpbb_rtrim($board_config['default_lang'])), "'");
			}
			else
			{
				// For guests it means the default language is not present, try english
				// This is a long shot since it means serious errors in the setup to reach here,
				// but english is part of a new install so it's worth us trying
				$language = 'english';
			}

			if ( !file_exists(@phpbb_realpath($phpbb_root_path . 'language/lang_' . $language . '/lang_main.'.$phpEx)) )
			{
				message_die(CRITICAL_ERROR, 'Could not locate valid language pack');
			}
		}

		// If we've had to change the value in any way then let's write it back to the database
		// before we go any further since it means there is something wrong with it
		if ( $userdata['user_id'] != ANONYMOUS && $userdata['user_lang'] !== $language )
		{
			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_lang = '" . $language . "'
				WHERE user_lang = '" . $userdata['user_lang'] . "'";

			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(CRITICAL_ERROR, 'Could not update user language info');
			}

			$userdata['user_lang'] = $language;
		}
		elseif ( $userdata['user_id'] === ANONYMOUS && $board_config['default_lang'] !== $language )
		{
			$sql = 'UPDATE ' . CONFIG_TABLE . "
				SET config_value = '" . $language . "'
				WHERE config_name = 'default_lang'";

			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(CRITICAL_ERROR, 'Could not update user language info');
			}
		}

		$board_config['default_lang'] = $language;

		include($phpbb_root_path . 'language/lang_' . $language . '/lang_main.' . $phpEx); // Also include phpBB lang keys

		if ( defined('IN_ADMIN') )
		{
			if ((@include $phpbb_root_path . "language/lang_" . $language . "/lang_main.$phpEx") === false)
			{
				$board_config['default_lang'] = 'english'; 
			}					
			// Also include phpBB lang keys
			elseif ((@include $phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/lang_main.$phpEx") === false)
			{
				message_die(CRITICAL_ERROR, 'Language file ' . $phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . ' & Language file ' . $phpbb_root_path . 'language/lang_' . $language . '/lang_main.$phpEx' . ' couldn\'t be opened.');
			}			
		}

		//
		// Mozilla navigation bar
		// Default items that should be valid on all pages.
		// Defined here to correctly assign the Language Variables
		// and be able to change the variables within code.
		//
		$nav_links['top'] = array (
			'url' => append_sid($phpbb_root_path . 'index.' . $phpEx),
			'title' => sprintf($lang['Forum_Index'], $board_config['sitename'])
		);
		$nav_links['search'] = array (
			'url' => append_sid($phpbb_root_path . 'search.' . $phpEx),
			'title' => $lang['Search']
		);
		$nav_links['help'] = array (
			'url' => append_sid($phpbb_root_path . 'faq.' . $phpEx),
			'title' => $lang['FAQ']
		);
		$nav_links['author'] = array (
			'url' => append_sid($phpbb_root_path . 'memberlist.' . $phpEx),
			'title' => $lang['Memberlist']
		);
	}	
	
	//
	// Adds/updates a new session to the database for the given userid.
	// Returns the new session ID on success.
	//
	function session_begin($user_id = 1, $user_ip = false, $page_id = 1, $auto_create = 0, $enable_autologin = 0, $admin = 0)
	{
		global $db, $board_config, $backend;
		global $request_vars, $SID;
		$user_ip = $user_ip ? $user_ip : $this->user_ip;
		$cookiename = $board_config['cookie_name'];
		$cookiepath = $board_config['cookie_path'];
		$cookiedomain = $board_config['cookie_domain'];
		$cookiesecure = $board_config['cookie_secure'];

		if ( isset($_COOKIE[$cookiename . '_sid']) || isset($_COOKIE[$cookiename . '_data']) )
		{
			$session_id = isset($_COOKIE[$cookiename . '_sid']) ? $_COOKIE[$cookiename . '_sid'] : '';
			$sessiondata = isset($_COOKIE[$cookiename . '_data']) ? unserialize(stripslashes($_COOKIE[$cookiename . '_data'])) : array();
			$sessionmethod = SESSION_METHOD_COOKIE;
		}
		else
		{
			$sessiondata = array();
			$session_id = is_get('sid', '');
			$sessionmethod = SESSION_METHOD_GET;
		}

		//
		if (!preg_match('/^[A-Za-z0-9]*$/', $session_id))
		{
			$session_id = '';
		}

		$page_id = (int) $page_id;

		$last_visit = 0;
		$current_time = time();

		//
		// Are auto-logins allowed?
		// If allow_autologin is not set or is true then they are
		// (same behaviour as old 2.0.x session code)
		//
		if (isset($board_config['allow_autologin']) && !$board_config['allow_autologin'])
		{
			$enable_autologin = $persist_login = $sessiondata['autologinid'] = false;
			$this->cookie_data['k'] = false;			
		}

		//
		// First off attempt to join with the autologin value if we have one
		// If not, just use the user_id value
		//
		$userdata = array();
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
		
		if ($user_id != ANONYMOUS)
		{
			if (isset($sessiondata['autologinid']) && (string) $sessiondata['autologinid'] != '' && $user_id)
			{
				$sql = 'SELECT u.*, u.user_id as user_colour, u.user_level as user_type, u.user_avatar as avatar, u.user_avatar_type as avatar_type
					FROM ' . USERS_TABLE . ' u, ' . SESSIONS_KEYS_TABLE . ' k
					WHERE u.user_id = ' . (int) $user_id . "
						AND u.user_active = 1
						AND k.user_id = u.user_id
						AND k.key_id = '" . md5($sessiondata['autologinid']) . "'";
				if (!($result = $db->sql_query($sql)))
				{
					message_die(CRITICAL_ERROR, 'Error doing DB query userdata row fetch', '', __LINE__, __FILE__, $sql);
				}

				$userdata = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				
				$enable_autologin = $login = 1;
			}
			else if (!$auto_create)
			{
				$sessiondata['autologinid'] = '';
				$sessiondata['userid'] = $user_id;

				$sql = 'SELECT u.*, u.user_id as user_colour, u.user_level as user_type, u.user_avatar as avatar, u.user_avatar_type as avatar_type
					FROM ' . USERS_TABLE . ' u
					WHERE user_id = ' . (int) $user_id . '
						AND user_active = 1';
				if (!($result = $db->sql_query($sql)))
				{
					message_die(CRITICAL_ERROR, 'Error doing DB query userdata row fetch', '', __LINE__, __FILE__, $sql);
				}

				$userdata = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				
				$login = 1;
			}
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
		
		// If no data was returned one or more of the following occurred:
		// * Key didn't match one in the DB
		// * User does not exist
		// * User is inactive
		//
		if (!sizeof($userdata) || !is_array($userdata) || !$userdata)
		{
			$this->cookie_data['k'] = $sessiondata['autologinid'] = '';
			$this->cookie_data['u'] = $sessiondata['userid'] = $user_id = ANONYMOUS;		
			
			$enable_autologin = $login = 0;

			$sql = 'SELECT u.*, u.user_id as user_colour, u.user_level as user_type, u.user_avatar as avatar, u.user_avatar_type as avatar_type
				FROM ' . USERS_TABLE . ' u
				WHERE user_id = ' . (int) $user_id;
			if (!($result = $db->sql_query($sql)))
			{
				message_die(CRITICAL_ERROR, 'Error doing DB query ANONYMOUS user userdata row fetch', '', __LINE__, __FILE__, $sql);
			}

			$userdata = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
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
			$sql .= " OR ban_email LIKE '" . str_replace("\'", "''", $userdata['user_email']) . "'
				OR ban_email LIKE '" . substr(str_replace("\'", "''", $userdata['user_email']), strpos(str_replace("\'", "''", $userdata['user_email']), "@")) . "'";
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
			SET session_user_id = $user_id, session_start = $current_time, session_time = $current_time, session_page = $page_id, session_logged_in = $login, session_admin = $admin
			WHERE session_id = '" . $session_id . "'
				AND session_ip = '$user_ip'";
		if ( !$db->sql_query($sql) || !$db->sql_affectedrows() )
		{
			$session_id = md5(dss_rand());

			$sql = "INSERT INTO " . SESSIONS_TABLE . "
				(session_id, session_user_id, session_start, session_time, session_ip, session_page, session_logged_in, session_admin)
				VALUES ('$session_id', $user_id, $current_time, $current_time, '$user_ip', $page_id, $login, $admin)";
			if ( !$db->sql_query($sql) )
			{
				message_die(CRITICAL_ERROR, 'Error creating new session', '', __LINE__, __FILE__, $sql);
			}
		}

		if ( $user_id != ANONYMOUS )
		{
			$last_visit = ( $userdata['user_session_time'] > 0 ) ? $userdata['user_session_time'] : $current_time;

			if (!$admin)
			{
				$sql = "UPDATE " . USERS_TABLE . "
					SET user_session_time = $current_time, user_session_page = $page_id, user_lastvisit = $last_visit
					WHERE user_id = $user_id";
				if ( !$db->sql_query($sql) )
				{
					message_die(CRITICAL_ERROR, 'Error updating last visit time', '', __LINE__, __FILE__, $sql);
				}
			}

			$userdata['user_lastvisit'] = $last_visit;

			//
			// Regenerate the auto-login key
			//
			if ($enable_autologin)
			{
				$auto_login_key = $backend->dss_rand() . $backend->dss_rand();

				if (isset($sessiondata['autologinid']) && (string) $sessiondata['autologinid'] != '')
				{
					$sql = 'UPDATE ' . SESSIONS_KEYS_TABLE . "
						SET last_ip = '$user_ip', key_id = '" . md5($auto_login_key) . "', last_login = $current_time
						WHERE key_id = '" . md5($sessiondata['autologinid']) . "'";
				}
				else
				{
					$sql = 'INSERT INTO ' . SESSIONS_KEYS_TABLE . "(key_id, user_id, last_ip, last_login)
						VALUES ('" . md5($auto_login_key) . "', $user_id, '$user_ip', $current_time)";
				}

				if ( !$db->sql_query($sql) )
				{
					$sql2 = 'UPDATE ' . SESSIONS_KEYS_TABLE . "
						SET last_login = $current_time
						WHERE key_id = '" . md5($sessiondata['autologinid']) . "'";
					if ( !$db->sql_query($sql2) )
					{
						message_die(CRITICAL_ERROR, 'Error updating session key', '', __LINE__, __FILE__, $sql);
					}
				}
				$sessiondata['autologinid'] = $auto_login_key;
				unset($auto_login_key);
			}
			else
			{
				$sessiondata['autologinid'] = '';
			}

	//		$sessiondata['autologinid'] = (!$admin) ? (( $enable_autologin && $sessionmethod == SESSION_METHOD_COOKIE ) ? $auto_login_key : '') : $sessiondata['autologinid'];
			$sessiondata['userid'] = $user_id;
		}

		$userdata['session_id'] = $session_id;
		$userdata['session_ip'] = $user_ip;
		$userdata['session_user_id'] = $user_id;
		$userdata['session_logged_in'] = $login;
		$userdata['session_page'] = $page_id;
		$userdata['session_start'] = $current_time;
		$userdata['session_time'] = $current_time;
		$userdata['session_admin'] = $admin;
		$userdata['session_key'] = $sessiondata['autologinid'];

		setcookie($cookiename . '_data', serialize($sessiondata), $current_time + 31536000, $cookiepath, $cookiedomain, $cookiesecure);
		setcookie($cookiename . '_sid', $session_id, 0, $cookiepath, $cookiedomain, $cookiesecure);

		$SID = 'sid=' . $session_id;

		return $userdata;
	}

	//
	// Checks for a given user session, tidies session table and updates user
	// sessions at each page refresh
	//
	function session_pagestart($user_ip, $thispage_id)
	{
		global $db, $lang, $board_config;
		global $SID;

		$cookiename = $board_config['cookie_name'];
		$cookiepath = $board_config['cookie_path'];
		$cookiedomain = $board_config['cookie_domain'];
		$cookiesecure = $board_config['cookie_secure'];

		$current_time = time();
		unset($userdata);

		if ( isset($_COOKIE[$cookiename . '_sid']) || isset($_COOKIE[$cookiename . '_data']) )
		{
			$sessiondata = isset( $_COOKIE[$cookiename . '_data'] ) ? unserialize(stripslashes($_COOKIE[$cookiename . '_data'])) : array();
			$session_id = isset( $_COOKIE[$cookiename . '_sid'] ) ? $_COOKIE[$cookiename . '_sid'] : '';
			$sessionmethod = SESSION_METHOD_COOKIE;
		}
		else
		{
			$sessiondata = array();
			$session_id = is_get('sid');
			$sessionmethod = SESSION_METHOD_GET;
		}

		//
		if (!preg_match('/^[A-Za-z0-9]*$/', $session_id))
		{
			$session_id = '';
		}

		$thispage_id = (int) $thispage_id;

		//
		// Does a session exist?
		//
		if ( !empty($session_id) )
		{
			//
			// session_id exists so go ahead and attempt to grab all
			// data in preparation
			//
			$sql = "SELECT u.*, u.user_id as user_colour, u.user_level as user_type, u.user_avatar as avatar, u.user_avatar_type as avatar_type, s.*
				FROM " . SESSIONS_TABLE . " s, " . USERS_TABLE . " u
				WHERE s.session_id = '$session_id'
					AND u.user_id = s.session_user_id";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(CRITICAL_ERROR, 'Error doing DB query userdata row fetch', '', __LINE__, __FILE__, $sql);
			}
			$userdata = $db->sql_fetchrow($result);
			
			//
			// Did the session exist in the DB?
			//
			if ( isset($userdata['user_id']) )
			{
				//
				// Do not check IP assuming equivalence, if IPv4 we'll check only first 24
				// bits ... I've been told (by vHiker) this should alleviate problems with
				// load balanced et al proxies while retaining some reliance on IP security.
				//
				$ip_check_s = substr($userdata['session_ip'], 0, 6);
				$ip_check_u = substr($user_ip, 0, 6);

				if ($ip_check_s == $ip_check_u)
				{
					$SID = ($sessionmethod == SESSION_METHOD_GET || defined('IN_ADMIN')) ? 'sid=' . $session_id : '';

					//
					// Only update session DB a minute or so after last update
					//
					if ( $current_time - $userdata['session_time'] > 60 )
					{
						// A little trick to reset session_admin on session re-usage
						$update_admin = (!defined('IN_ADMIN') && $current_time - $userdata['session_time'] > ($board_config['session_length']+60)) ? ', session_admin = 0' : '';

						$sql = "UPDATE " . SESSIONS_TABLE . "
							SET session_time = $current_time, session_page = $thispage_id$update_admin
							WHERE session_id = '" . $userdata['session_id'] . "'";
						if ( !$db->sql_query($sql) )
						{
							message_die(CRITICAL_ERROR, 'Error updating sessions table', '', __LINE__, __FILE__, $sql);
						}

						if ( $userdata['user_id'] != ANONYMOUS )
						{
							$sql = "UPDATE " . USERS_TABLE . "
								SET user_session_time = $current_time, user_session_page = $thispage_id
								WHERE user_id = " . $userdata['user_id'];
							if ( !$db->sql_query($sql) )
							{
								message_die(CRITICAL_ERROR, 'Error updating sessions table', '', __LINE__, __FILE__, $sql);
							}
						}

						$this->session_clean($userdata['session_id']);

						setcookie($cookiename . '_data', serialize($sessiondata), $current_time + 31536000, $cookiepath, $cookiedomain, $cookiesecure);
						setcookie($cookiename . '_sid', $session_id, 0, $cookiepath, $cookiedomain, $cookiesecure);
					}

					// Add the session_key to the userdata array if it is set
					if ( isset($sessiondata['autologinid']) && $sessiondata['autologinid'] != '' )
					{
						$userdata['session_key'] = $sessiondata['autologinid'];
					}

					return $userdata;
				}
			}
		}

		//
		// If we reach here then no (valid) session exists. So we'll create a new one,
		// using the cookie user_id if available to pull basic user prefs.
		//
		$user_id = ( isset($sessiondata['userid']) ) ? intval($sessiondata['userid']) : ANONYMOUS;

		if ( !($userdata = $this->session_begin($user_id, $user_ip, $thispage_id, TRUE)) )
		{
			message_die(CRITICAL_ERROR, 'Error creating user session', '', __LINE__, __FILE__, $sql);
		}

		return $userdata;

	}

	/**
	* Terminates the specified session
	* It will delete the entry in the sessions table for this session,
	* remove the corresponding auto-login key and reset the cookies
	*/
	function session_end($session_id, $user_id)
	{
		global $db, $lang, $board_config, $userdata;
		global $SID;

		$cookiename = $board_config['cookie_name'];
		$cookiepath = $board_config['cookie_path'];
		$cookiedomain = $board_config['cookie_domain'];
		$cookiesecure = $board_config['cookie_secure'];

		$current_time = time();

		if (!preg_match('/^[A-Za-z0-9]*$/', $session_id))
		{
			return;
		}

		//
		// Delete existing session
		//
		$sql = 'DELETE FROM ' . SESSIONS_TABLE . "
			WHERE session_id = '$session_id'
				AND session_user_id = $user_id";
		if ( !$db->sql_query($sql) )
		{
			message_die(CRITICAL_ERROR, 'Error removing user session', '', __LINE__, __FILE__, $sql);
		}

		//
		// Remove this auto-login entry (if applicable)
		//
		if ( isset($userdata['session_key']) && $userdata['session_key'] != '' )
		{
			$autologin_key = md5($userdata['session_key']);
			$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
				WHERE user_id = ' . (int) $user_id . "
					AND key_id = '$autologin_key'";
			if ( !$db->sql_query($sql) )
			{
				message_die(CRITICAL_ERROR, 'Error removing auto-login key', '', __LINE__, __FILE__, $sql);
			}
		}

		//
		// We expect that message_die will be called after this function,
		// but just in case it isn't, reset $userdata to the details for a guest
		//
		$sql = 'SELECT u.*, u.user_id as user_colour, u.user_level as user_type, u.user_avatar as avatar, u.user_avatar_type as avatar_type
			FROM ' . USERS_TABLE . ' u
			WHERE user_id = ' . ANONYMOUS;
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(CRITICAL_ERROR, 'Error obtaining user details', '', __LINE__, __FILE__, $sql);
		}
		if ( !($userdata = $db->sql_fetchrow($result)) )
		{
			message_die(CRITICAL_ERROR, 'Error obtaining user details', '', __LINE__, __FILE__, $sql);
		}
		$db->sql_freeresult($result);


		setcookie($cookiename . '_data', '', $current_time - 31536000, $cookiepath, $cookiedomain, $cookiesecure);
		setcookie($cookiename . '_sid', '', $current_time - 31536000, $cookiepath, $cookiedomain, $cookiesecure);

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
		// We expect that message_die will be called after this function,
		// but just in case it isn't, reset $userdata to the details for a guest
		//
		$sql = 'SELECT u.*, u.user_id as user_colour, u.user_level as user_type, u.user_avatar as avatar, u.user_avatar_type as avatar_type
			FROM ' . USERS_TABLE . ' u
			WHERE user_id = ' . ANONYMOUS;
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(CRITICAL_ERROR, 'Error obtaining user details', '', __LINE__, __FILE__, $sql);
		}
		if ( !($userdata = $db->sql_fetchrow($result)) )
		{
			message_die(CRITICAL_ERROR, 'Error obtaining user details', '', __LINE__, __FILE__, $sql);
		}
		$db->sql_freeresult($result);

		// Bot user, if they have a SID in the Request URI we need to get rid of it otherwise they'll index this page with the SID, duplicate content oh my!
		if (isset($_GET['sid']) && !empty($this->data['is_bot']))
		{
			send_status_line(301, 'Moved Permanently');
			redirect(build_url(array('sid')));
		}
		$this->data['session_last_visit'] = $this->time_now;
			
		setcookie($cookiename . '_data', '', $current_time - 31536000, $cookiepath, $cookiedomain, $cookiesecure);
		setcookie($cookiename . '_sid', '', $current_time - 31536000, $cookiepath, $cookiedomain, $cookiesecure);

		return true;
	}	
	
	/**
	* Removes expired sessions and auto-login keys from the database
	*/
	function session_clean($session_id)
	{
		global $board_config, $db;

		//
		// Delete expired sessions
		//
		$sql = 'DELETE FROM ' . SESSIONS_TABLE . '
			WHERE session_time < ' . (time() - (int) $board_config['session_length']) . "
				AND session_id <> '$session_id'";
		if ( !$db->sql_query($sql) )
		{
			message_die(CRITICAL_ERROR, 'Error clearing sessions table', '', __LINE__, __FILE__, $sql);
		}

		//
		// Delete expired auto-login keys
		// If max_autologin_time is not set then keys will never be deleted
		// (same behaviour as old 2.0.x session code)
		//
		if (!empty($board_config['max_autologin_time']) && $board_config['max_autologin_time'] > 0)
		{
			$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
				WHERE last_login < ' . (time() - (86400 * (int) $board_config['max_autologin_time']));
			$db->sql_query($sql);
		}

		return true;
	}

	/**
	* Reset all login keys for the specified user
	* Called on password changes
	*/
	function session_reset_keys($user_id, $user_ip)
	{
		global $db, $userdata, $board_config, $backend;

		$key_sql = ($user_id == $userdata['user_id'] && !empty($userdata['session_key'])) ? "AND key_id != '" . md5($userdata['session_key']) . "'" : '';

		$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
			WHERE user_id = ' . (int) $user_id . "
				$key_sql";

		if ( !$db->sql_query($sql) )
		{
			message_die(CRITICAL_ERROR, 'Error removing auto-login keys', '', __LINE__, __FILE__, $sql);
		}

		$where_sql = 'session_user_id = ' . (int) $user_id;
		$where_sql .= ($user_id == $userdata['user_id']) ? " AND session_id <> '" . $userdata['session_id'] . "'" : '';
		$sql = 'DELETE FROM ' . SESSIONS_TABLE . "
			WHERE $where_sql";
		if ( !$db->sql_query($sql) )
		{
			message_die(CRITICAL_ERROR, 'Error removing user session(s)', '', __LINE__, __FILE__, $sql);
		}

		if ( !empty($key_sql) )
		{
			$auto_login_key = $backend->dss_rand() . $backend->dss_rand();

			$current_time = time();

			$sql = 'UPDATE ' . SESSIONS_KEYS_TABLE . "
				SET last_ip = '$user_ip', key_id = '" . md5($auto_login_key) . "', last_login = $current_time
				WHERE key_id = '" . md5($userdata['session_key']) . "'";

			if ( !$db->sql_query($sql) )
			{
				message_die(CRITICAL_ERROR, 'Error updating session key', '', __LINE__, __FILE__, $sql);
			}

			// And now rebuild the cookie
			$sessiondata['userid'] = $user_id;
			$sessiondata['autologinid'] = $auto_login_key;
			$cookiename = $board_config['cookie_name'];
			$cookiepath = $board_config['cookie_path'];
			$cookiedomain = $board_config['cookie_domain'];
			$cookiesecure = $board_config['cookie_secure'];

			setcookie($cookiename . '_data', serialize($sessiondata), $current_time + 31536000, $cookiepath, $cookiedomain, $cookiesecure);

			$userdata['session_key'] = $auto_login_key;
			unset($sessiondata);
			unset($auto_login_key);
		}
	}

	/** *******************************************************************************************************
	 * Include the User class
	 ******************************************************************************************************* */
	 

	
	/** *******************************************************************************************************
	 * Include the User class
	 ******************************************************************************************************* */

	/**
	* Define backend specific lang defs
	*/
	function setup($lang_set = false, $style_id = false)
	{
		global $cache, $board_config, $theme, $images;
		global $db, $board_config, $userdata, $auth, $phpbb_root_path;		
		global $template, $lang, $phpEx, $nav_links;
		
		$this->data = !empty($this->data['user_id']) ? $this->data : session_pagestart($this->user_ip, $this->page_id);
		
		$this->cache = is_object($cache) ? $cache : new base();
		
		if (preg_match('/bot|crawl|curl|dataprovider|search|get|spider|find|java|majesticsEO|google|yahoo|teoma|contaxe|yandex|libwww-perl|facebookexternalhit/i', $_SERVER['HTTP_USER_AGENT'])) 
		{
		    $this->data['is_bot'] = true;
		}
		else
		{
		    $this->data['is_bot'] = false;
		}

		//
		// Populate session_id
		//
		$this->session_id = $this->data['session_id'];
		
		$this->lang_path = $phpbb_root_path . 'language/';
		
		$lang_set = !$lang_set ? (defined('IN_ADMIN') ? 'lang_admin' : 'lang_main') : $lang_set;
		
		//
		// Grab phpBB global variables, re-cache if necessary
		// - optional parameter to enable/disable cache for config data. If enabled, remember to refresh the MX-Publisher cache whenever updating phpBB config settings
		// - true: enable cache, false: disable cache
		if (empty($board_config['script_path']))
		{
			$board_config = $cache->obtain_config(false);
		}		
				
		$board_config['user_timezone'] = !empty($board_config['user_timezone']) ? $board_config['user_timezone'] : $board_config['board_timezone'];
		$this->data['user_dst'] = !empty($this->data['user_dst']) ? $this->data['user_dst'] : $this->data['user_timezone'];
		
		$this->date_format = $board_config['default_dateformat'];
		$this->timezone = $board_config['user_timezone'] * 3600;
		$this->dst = $this->data['user_timezone'] * 3600;
		
		$sign = ($board_config['board_timezone'] < 0) ? '-' : '+';
		$time_offset = abs($board_config['board_timezone']);

		$offset_seconds	= $time_offset % 3600;
		$offset_minutes	= $offset_seconds / 60;
		$offset_hours	= ($time_offset - $offset_seconds) / 3600;		
		
		// Zone offset
		$zone_offset = $this->timezone + $this->dst;
		
		$offset_string = sprintf($board_config['default_dateformat'], $sign, $offset_hours, $offset_minutes);
				
		$s_date = gmdate("Y-m-d\TH:i:s", time() + $zone_offset) . $offset_string;
		
		// Format Timezone. We are unable to use array_pop here, because of PHP3 compatibility
		$l_timezone = explode('.', $board_config['board_timezone']);
		$l_timezone = (count($l_timezone) > 1) ? $this->lang(sprintf('%.1f', $board_config['board_timezone'])) : $offset_string;

		$server_name = !empty($board_config['server_name']) ? preg_replace('/^\/?(.*?)\/?$/', "\\1", trim($board_config['server_name'])) : 'localhost';
		$server_protocol = ($board_config['cookie_secure'] ) ? 'https://' : 'http://';
		$server_port = (($board_config['server_port']) && ($board_config['server_port'] <> 80)) ? ':' . trim($board_config['server_port']) . '/' : '/';
		$script_name_phpbb = preg_replace('/^\/?(.*?)\/?$/', "\\1", trim($board_config['script_path'])) . '/';		
		$server_url = $server_protocol . str_replace("//", "/", $server_name . $server_port . '/'); //On some server the slash is not added and this trick will fix it	
		$corrected_url = $server_protocol . $server_name . $server_port . $script_name_phpbb;
		$board_url = $server_url . $script_name_phpbb;
		$web_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? $board_url : $corrected_url;
	
		@define('PHPBB_URL', $board_url);
		
		//
		// Send a proper content-language to the output
		//
		$img_lang = $board_config['default_lang'];
		
		$default_lang = ($this->data['user_lang']) ? $this->data['user_lang'] : $board_config['default_lang'];
		
		if ($this->data['user_id'] != ANONYMOUS)
		{
			if (!empty($this->data['user_lang']))
			{
				$default_lang = phpbb_ltrim(basename(phpbb_rtrim($this->data['user_lang'])), "'");
			}

			if (!empty($this->data['user_dateformat']))
			{
				$board_config['default_dateformat'] = $this->data['user_dateformat'];
			}

			if (isset($userdata['user_timezone']))
			{
				$board_config['board_timezone'] = $this->data['user_timezone'];
			}
		}
		else
		{
			$default_lang = phpbb_ltrim(basename(phpbb_rtrim($board_config['default_lang'])), "'");
		}

		if (!file_exists(@phpbb_realpath($phpbb_root_path . 'language/lang_' . $default_lang . '/lang_main.'.$phpEx)))
		{
			if ($userdata['user_id'] != ANONYMOUS)
			{
				// For logged in users, try the board default language next
				$default_lang = phpbb_ltrim(basename(phpbb_rtrim($board_config['default_lang'])), "'");
			}
			else
			{
				// For guests it means the default language is not present, try english
				// This is a long shot since it means serious errors in the setup to reach here,
				// but english is part of a new install so it's worth us trying
				$default_lang = 'english';
			}

			if (!file_exists(@phpbb_realpath($phpbb_root_path . 'language/lang_' . $default_lang . '/lang_main.'.$phpEx)))
			{
				message_die(CRITICAL_ERROR, 'Could not locate valid language pack');
			}
		}

		// If we've had to change the value in any way then let's write it back to the database
		// before we go any further since it means there is something wrong with it
		if ($this->data['user_id'] != ANONYMOUS && $this->data['user_lang'] !== $default_lang)
		{
			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_lang = '" . $default_lang . "'
				WHERE user_lang = '" . $this->data['user_lang'] . "'";

			if (!($result = $db->sql_query($sql)))
			{
				message_die(CRITICAL_ERROR, 'Could not update user language info');
			}

			$this->data['user_lang'] = $default_lang;
		}
		elseif ($this->data['user_id'] == ANONYMOUS && $board_config['default_lang'] !== $default_lang)
		{
			$sql = 'UPDATE ' . CONFIG_TABLE . "
				SET config_value = '" . $default_lang . "'
				WHERE config_name = 'default_lang'";

			if (!($result = $db->sql_query($sql)))
			{
				message_die(CRITICAL_ERROR, 'Could not update user language info');
			}
		}

		$board_config['default_lang'] = $default_lang;

		$this->lang_name = $this->lang['default_lang'] = $default_lang;
		$this->lang_path = $shared_lang_path = $phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/';
		
		//
		// We include common language file here to not load it every time a custom language file is included
		//
		$lang = &$this->lang;

		/** Sort of pointless here, since we have already included all main lang files **/
		if ((@include $this->lang_path . "lang_main.$phpEx") === false)
		{
			//this will fix the path for anonymouse users
			if ((@include $phpbb_root_path . $this->lang_path . "lang_main.$phpEx") === false)
			{
				die('Language file ' . $this->lang_path . "lang_main.$phpEx" . ' couldn\'t be opened.');
			}
		}
		//  include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.' . $phpEx);
		
		$this->add_lang($lang_set);

		//  We include common language file here to not load it every time a custom language file is included
		//  $lang = &$this->lang;
		
		unset($lang_set);
			
		if (defined('IN_ADMIN'))
		{
			if(!file_exists(@phpbb_realpath($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_admin.'.$phpEx)))
			{
				$board_config['default_lang'] = 'english';
			}

			include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_admin.' . $phpEx);
		}
		
		//
		// We setup common user language variables
		//
		$this->lang = &$lang;
		
		$this->user_lang = !empty($this->lang['USER_LANG']) ? $this->lang['USER_LANG'] : $this->encode_lang($this->lang_name);
		$user_lang = $this->user_lang;
		
		$this->user_language		= $this->encode_lang($this->lang_name);
		$this->default_language		= $this->encode_lang($board_config['default_lang']);
		
		$this->user_language_name		= $this->decode_lang($this->lang_name);
		$this->default_language_name	= $this->decode_lang($board_config['default_lang']);
		
		$counter = 0; //First language pack lang_id		
		$lang_ids = array();
		$lang_list = $this->get_lang_list();
		
		if (is_array($lang_list))
		{		
			foreach ($lang_list as $lang_english_name => $lang_local_name)
			{
				$lang_ids[$lang_english_name] = $counter;
				$counter++;	
			}	
		}	
		
		$lang_entries = array(
			'lang_id' => !empty($lang_ids['lang_' . $this->user_language_name]) ? $lang_ids['lang_' . $this->user_language_name] : $counter,
			'lang_iso' => !empty($lang['USER_LANG']) ? $lang['USER_LANG'] : $this->encode_lang($this->lang_name),
			'lang_dir' => 'lang_' . $this->lang_name,
			'lang_english_name' => $this->user_language_name,
			'lang_local_name' => $this->ucstrreplace('lang_', '', $this->lang_name),
			'lang_author' => !empty($lang['TRANSLATION_INFO']) ? $lang['TRANSLATION_INFO'] : 'Language pack author not set in ACP.'
		);
		
		//
		// Finishing setting language variables to ouput
		//
		$this->lang_iso = $lang_iso = $lang_entries['lang_iso'];		
		$this->lang_dir = $lang_dir = $lang_entries['lang_dir'];
		$this->lang_english_name = $lang_english_name = $lang_entries['lang_english_name'];		
		$this->lang_local_name = $lang_local_name = $lang_entries['lang_local_name'];
		
		//
		// Set up style to output
		//
		if ($this->data['user_id'] == ANONYMOUS && empty($this->data['user_style']))
		{
			$this->data['user_style'] = $board_config['default_style'];
		}
		
		$style_request = request_var('style', 0);
		
		if ($style_request && (!$board_config['override_user_style'] || !defined('IN_ADMIN')))
		{
			global $SID, $_EXTRA_URL;

			$style_id = $style_request;
			$SID .= '&amp;style=' . $style_id;
			$_EXTRA_URL = array('style=' . $style_id);
		}
		else
		{
			// Set up style
			$style_id = ($style_id) ? $style_id : ((!$board_config['override_user_style']) ? $this->data['user_style'] : $board_config['default_style']);
		}
		
		$sql = 'SELECT s.*
			FROM ' . THEMES_TABLE . " s
			WHERE s.themes_id = $style_id";
		$result = $db->sql_query($sql, 3600);
		$this->style = $this->theme = $theme = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		/** Fallback to user's standard style **/
		if (!$this->style && $style_id != $this->data['user_style'])
		{
			$style_id = $this->data['user_style'];

			$sql = 'SELECT s.*
				FROM ' . THEMES_TABLE . " s
				WHERE s.themes_id = $style_id";
			$result = $db->sql_query($sql, 3600);
			$this->style = $this->theme = $theme = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}
		/** Fallback to user's standard style **/
		if (!$this->style)
		{
			$this->style = $this->theme = $theme = setup_style($style_id);
			
			//message_die(CRITICAL_ERROR, "Could not query database for phpbb_styles info style_id [$style_id]", "", __LINE__, __FILE__, $sql);
		}
		
		$this->template_name = $theme['template_name'];
			
		// We are trying to setup a style which does not exist in the database
		// Try to fallback to the board default (if the user had a custom style)
		// and then any users using this style to the default if it succeeds
		if ($theme['themes_id'] != $board_config['default_style'])
		{					
			$sql = 'SELECT template_name
					FROM ' . THEMES_TABLE . '
					WHERE themes_id = ' . (int) $board_config['default_style'];
			
			if ($row = $db->sql_fetchrow($result = $db->sql_query($sql)))
			{
				$db->sql_freeresult($result);
				$this->default_current_template_name = !empty($row['template_name']) ? $row['template_name'] : $this->default_current_template_name;
			}				
		}
		
		//Setup cloned template	as prosilver based for phpBB3 styles		
		if( @file_exists(@phpbb_realpath($phpbb_root_path . $this->template_path . $this->template_name . '/style.cfg')) )
		{
			$cfg = parse_cfg_file($phpbb_root_path . $this->template_path . $this->template_name . '/style.cfg');
			$this->cloned_template_name = !empty($cfg['parent']) ? $cfg['parent'] : 'prosilver';
			$this->cloned_template_path = $this->template_path . $this->cloned_template_name;			
			$this->default_template_name = !empty($cfg['parent']) ? $cfg['parent'] : 'prosilver';
		}
		
		//Setup current_template_path	
		$this->default_current_template_path = $this->template_path . $this->default_current_template_name;
		$this->current_template_path = $this->template_path . $this->template_name;
		$this->theme['theme_path'] = $this->template_name;			
		
		$parsed_array = $this->cache->get('_cfg_' . $this->template_path);

		if ($parsed_array === false)
		{
			$parsed_array = array();
		}	
		
		if( @file_exists(@phpbb_realpath($phpbb_root_path . $this->current_template_path . '/style.cfg')) )
		{
			//parse phpBB3 style cfg file
			$cfg_file_name = 'style.cfg';			
			$cfg_file = $phpbb_root_path . $this->current_template_path . '/style.cfg';
					
			if (!isset($parsed_array['filetime']) || (@filemtime($cfg_file) > $parsed_array['filetime']))
			{
				// Re-parse cfg file
				$parsed_array = parse_cfg_file($cfg_file);		
				$parsed_array['filetime'] = @filemtime($cfg_file);				
				$this->cache->put('_cfg_' . $this->template_path, $parsed_array);
			}							
		}
		else
		{	
			//parse phpBB2 style cfg file	
			$cfg_file_name = $this->template_name . '.cfg';
			$cfg_file = $phpbb_root_path . $this->current_template_path . '/' . $cfg_file_name;
			
			if (file_exists($phpbb_root_path .  $this->current_template_path . '/' . $cfg_file_name))
			{
				if (!isset($parsed_array['filetime']) || (@filemtime($cfg_file) > $parsed_array['filetime']))
				{				
					$parsed_array = parse_cfg_file($cfg_file);		
					$parsed_array['filetime'] = @filemtime($cfg_file);
					$this->cache->put('_cfg_' . $this->template_path, $parsed_array);				
				}
			}		
		}
		
		$check_for = array(
			'pagination_sep'    => (string) ', '
		);

		foreach ($check_for as $key => $default_value)
		{
			$this->style[$key] = (isset($parsed_array[$key])) ? $parsed_array[$key] : $default_value;
			$this->theme[$key] = (isset($parsed_array[$key])) ? $parsed_array[$key] : $default_value;
			settype($this->style[$key], gettype($default_value));
			settype($this->theme[$key], gettype($default_value));
			if (is_string($default_value))
			{
				$this->style[$key] = htmlspecialchars($this->style[$key]);
				$this->theme[$key] = htmlspecialchars($this->theme[$key]);
			}
		}
		
 		// If the style author specified the theme needs to be cached
		// (because of the used paths and variables) than make sure it is the case.
		// For example, if the theme uses language-specific images it needs to be stored in db.
		if (file_exists($phpbb_root_path . $this->template_path . $this->template_name . '/theme/stylesheet.css'))
		{
			//phpBB3 Style Sheet
			$theme_file = 'stylesheet.css'; 
			$css_file_path = $this->template_path . $this->template_name . '/theme/';
			$stylesheet = file_get_contents("{$phpbb_root_path}{$this->template_path}{$this->template_name}/theme/stylesheet.css");
		}
		else
		{	
			//phpBB2 Style Sheet	
			$theme_file = !empty($this->theme['head_stylesheet']) ?  $this->theme['head_stylesheet'] : $this->template_name . '.css'; 
			$css_file_path = $this->template_path . $this->template_name . '/';
			if (file_exists($phpbb_root_path . $this->template_path . $this->template_name . '/' . $theme_file))
			{
				$stylesheet = file_get_contents("{$phpbb_root_path}{$this->template_path}{$this->template_name}/{$theme_file}");
			}		
		}		
		
		if (!empty($stylesheet))
		{			
			// Match CSS imports
			$matches = array();
			preg_match_all('/@import url\(["\'](.*)["\']\);/i', $stylesheet, $matches);
			
			if (sizeof($matches))
			{
				$content = '';
				foreach ($matches[0] as $idx => $match)
				{
					if ($content = @file_get_contents("{$phpbb_root_path}{$css_file_path}" . $matches[1][$idx]))
					{
						$content = trim($content);
					}
					else
					{
						$content = '';
					}
					$stylesheet = str_replace($match, $content, $stylesheet);
				}
				unset($content);
			}

			$stylesheet = str_replace('./', $css_file_path, $stylesheet);

			$theme_info = array(
				'theme_data'	=> $stylesheet,
				'theme_mtime'	=> time(),
				'theme_storedb'	=> 0
			);
			$theme_data = &$theme_info['theme_data'];
		}			
		
		//		
		// - First try old Olympus image sets then phpBB2  and phpBB3 Proteus template lang images 	
		//		
		if (@is_dir("{$phpbb_root_path}{$this->template_path}{$this->template_name}/imageset/"))
		{
			$this->imageset_path = '/imageset/'; //Olympus ImageSet
			$this->img_lang = (file_exists($phpbb_root_path . $this->template_path . $this->template_name . $this->imageset_path . $this->lang_iso)) ? $this->lang_iso : $this->default_language;
			$this->img_lang_dir = $this->img_lang;
			$this->imageset_backend = 'olympus';		
		}
		elseif (@is_dir("{$phpbb_root_path}{$this->template_path}{$this->template_name}/theme/images/"))
		{
			$this->imageset_path = '/theme/images/';  //phpBB3 Images
			if ((@is_dir("{$phpbb_root_path}{$this->template_path}{$this->template_name}/theme/lang_{$this->user_language_name}")) || (@is_dir("{$phpbb_root_path}{$this->template_path}{$this->template_name}/theme/lang_{$this->default_language_name}")))
			{
				$this->img_lang = (file_exists($phpbb_root_path . $this->template_path . $this->template_name . '/theme/' . 'lang_' . $this->user_language_name)) ? $this->user_language_name : $this->default_language_name;
				$this->img_lang_dir = 'lang_' . $this->img_lang;
				$this->imageset_backend = 'phpbb2';	
			}
			if ((@is_dir("{$phpbb_root_path}{$this->template_path}{$this->template_name}/theme/{$this->user_language}")) || (@is_dir("{$phpbb_root_path}{$this->template_path}{$this->template_name}/theme/{$this->default_language}")))
			{
				$this->img_lang = (file_exists($phpbb_root_path . $this->template_path . $this->template_name . '/theme/' . $this->user_language_name)) ? $this->user_language : $this->default_language;
				$this->img_lang_dir = $this->img_lang;
				$this->imageset_backend = 'phpbb3';	
			}			
		}		
		elseif (@is_dir("{$phpbb_root_path}{$this->template_path}{$this->template_name}/images/"))
		{
			$this->imageset_path = '/images/';  //phpBB2 Images
			$this->img_lang = (file_exists($phpbb_root_path . $this->template_path . $this->template_name . $this->imageset_path . '/images/lang_' . $this->user_language_name)) ? $this->user_language_name : $this->default_language_name;
			$this->img_lang_dir = 'lang_' . $this->img_lang;
			$this->imageset_backend = 'phpbb2';	
		}
		
		//		
		// Olympus image sets main images
		//		
		if (@file_exists("{$phpbb_root_path}{$this->template_path}{$this->template_name}{$this->imageset_path}/imageset.cfg"))
		{		
			$cfg_data_imageset = parse_cfg_file("{$phpbb_root_path}{$this->template_path}{$this->template_name}{$this->imageset_path}/imageset.cfg");
			
			foreach ($cfg_data_imageset as $image_name => $value)
			{
				if (strpos($value, '*') !== false)
				{
					if (substr($value, -1, 1) === '*')
					{
						list($image_filename, $image_height) = explode('*', $value);
						$image_width = 0;
					}
					else
					{
						list($image_filename, $image_height, $image_width) = explode('*', $value);
					}
				}
				else
				{
					$image_filename = $value;
					$image_height = $image_width = 0;
				}
				
				if (strpos($image_name, 'img_') === 0 && $image_filename)
				{
					$image_name = substr($image_name, 4);				
					$row[] = array(
						'image_name'		=> (string) $image_name,
						'image_filename'	=> (string) $image_filename,
						'image_height'		=> (int) $image_height,
						'image_width'		=> (int) $image_width,
						'imageset_id'		=> (int) $style_id,
						'image_lang'		=> '',
					);
					
					if (!empty($row['image_lang']))
					{
						$localised_images = true;
					}					
					$row['image_filename'] = !empty($row['image_filename']) ? rawurlencode($row['image_filename']) : '';
					$row['image_name'] = !empty($row['image_name']) ? rawurlencode($row['image_name']) : '';
					$this->img_array[$row['image_name']] = $row;									
				}
			}		
		}
		
		//		
		// - Olympus image sets lolalised images	
		//		
		if (@file_exists("{$phpbb_root_path}{$this->template_path}{$this->template_name}{$this->imageset_path}{$this->img_lang}/imageset.cfg"))
		{
			$cfg_data_imageset_data = parse_cfg_file("{$phpbb_root_path}{$this->template_path}{$this->template_name}{$this->imageset_path}{$this->img_lang}/imageset.cfg");
			foreach ($cfg_data_imageset_data as $image_name => $value)
			{
				if (strpos($value, '*') !== false)
				{
					if (substr($value, -1, 1) === '*')
					{
						list($image_filename, $image_height) = explode('*', $value);
						$image_width = 0;
					}
					else
					{
						list($image_filename, $image_height, $image_width) = explode('*', $value);
					}
				}
				else
				{
					$image_filename = $value;
					$image_height = $image_width = 0;
				}

				if (strpos($image_name, 'img_') === 0 && $image_filename)
				{
					$image_name = substr($image_name, 4);
					$row[] = array(
						'image_name'		=> (string) $image_name,
						'image_filename'	=> (string) $image_filename,
						'image_height'		=> (int) $image_height,
						'image_width'		=> (int) $image_width,
						'imageset_id'		=> !empty($this->theme['imageset_id']) ? (int) $this->theme['imageset_id'] : 0,
						'image_lang'		=> (string) $this->img_lang,
					);
					
					if (!empty($row['image_lang']))
					{
						$localised_images = true;
					}					
					$row['image_filename'] = !empty($row['image_filename']) ? rawurlencode($row['image_filename']) : '';
					$row['image_name'] = !empty($row['image_name']) ? rawurlencode($row['image_name']) : '';
					$this->img_array[$row['image_name']] = $row;									
				}
			}
		}
		

		//		
		// - Try redefining phpBB2 images 	
		//		
		/** 
		* Now check for the correct existance of all images of the $user->style['style_path'] 
		* $template->set_template();
		* print_r($this->images['forum']);
		*/
		$this->setup_style();			
		
		//		
		// - phpBB3 Rhea and Proteus lang images 	
		//		
		if (empty($this->img_array))
		{
			/** 
				* Now check for the correct existance of all of the images into
				* each image of a prosilver based style. 			
			* /
			
			/* Here we overwrite phpBB images from the template db or configuration file  */		
			$rows = $this->image_rows($this->images);		
			
			foreach ($rows as $row)
			{
				$row['image_filename'] = rawurlencode($row['image_filename']);
				
				if(empty($row['image_name']))
				{
					//print_r('Your style configuration file has a typo! ');
					//print_r($row);
					$row['image_name'] = 'spacer.gif';
				}
							
				$this->img_array[$row['image_name']] = $row;				
			}	
		}		
		
		//
		// Mozilla navigation bar
		// Default items that should be valid on all pages.
		// Defined here to correctly assign the Language Variables
		// and be able to change the variables within code.
		//
		$nav_links['top'] = array (
			'url' => append_sid($phpbb_root_path . 'index.' . $phpEx),
			'title' => sprintf($lang['Forum_Index'], $board_config['sitename'])
		);
		$nav_links['search'] = array (
			'url' => append_sid($phpbb_root_path . 'search.' . $phpEx),
			'title' => $lang['Search']
		);
		$nav_links['help'] = array (
			'url' => append_sid($phpbb_root_path . 'faq.' . $phpEx),
			'title' => $lang['FAQ']
		);
		$nav_links['author'] = array (
			'url' => append_sid($phpbb_root_path . 'memberlist.' . $phpEx),
			'title' => $lang['Memberlist']
		);

		//
		// Dummy include, to make all original phpBB functions available
		//
		include_once($phpbb_root_path . 'includes/functions.' . $phpEx); // In case we need old functions...

		//
		// Is phpBB File Attachment MOD present?
		//
		if( file_exists($phpbb_root_path . 'attach_mod') )
		{
			include_once($phpbb_root_path . 'attach_mod/attachment_mod.' . $phpEx);
		}
		
		return;
	}

	/**
	 * Setup style
	 *
	 * Define backend specific style defs
	 *
	 */
	function setup_style()
	{		
		$template = new phpbb_Template($this->phpbb_root_path . $this->template_path . $this->template_name);
		@define('IP_ROOT_PATH', $this->phpbb_root_path); //for ICY-PHOENIX Styles
		
		if (is_object($template))
		{
			if(is_dir($this->phpbb_root_path . $this->current_template_path . '/theme/images/'))
			{
				$current_template_images = $this->current_template_images = $this->current_template_path . "/theme/images";						
			}
			elseif(is_dir($this->phpbb_root_path . $this->current_template_path . '/images/'))
			{
				$current_template_images = $this->current_template_images = $this->current_template_path . "/images";					
			}			
			
			$phpbb_root_path = $this->phpbb_root_path;			
			$current_template_path = $this->template_path . $this->template_name;
			
			$row = array();
			
			$row['body_background'] = $this->theme['body_background'];
			
			if(@file_exists(@phpbb_realpath($phpbb_root_path . $this->template_path . $this->template_name . '/' . $this->template_name . '.cfg')) )
			{
				include($phpbb_root_path . $this->template_path . $this->template_name . '/' . $this->template_name . '.cfg');
				
				if (!defined('TEMPLATE_CONFIG'))
				{
					//
					// Do not alter this line!
					//
					@define(TEMPLATE_CONFIG, TRUE);					
				}				
			}			
			elseif( @file_exists(@phpbb_realpath($phpbb_root_path . $this->template_path . $this->template_name . "/style.cfg")) )
			{
				//
				// Do not alter this line!
				//
				@define(TEMPLATE_CONFIG, TRUE);

				//		
				// - First try phpBB2 then phpBB3 template lang images then old Olympus image sets
				//		
				if ( file_exists($phpbb_root_path . $this->current_template_path . '/images/') )
				{
					$this->current_template_images = $this->current_template_path . '/images';
				}		
				else if ( file_exists($phpbb_root_path . $this->current_template_path  . '/theme/images/') )
				{		
					$this->current_template_images = $this->current_template_path  . '/theme/images';
				}		
				if ( file_exists($phpbb_root_path . $this->current_template_path  . '/imageset/') )
				{		
					$this->current_template_images = $this->current_template_path  . '/imageset';
				}
				
				$current_template_images = $this->current_template_images;
				
				$images['icon_quote'] = "$current_template_images/{LANG}/" . $this->img('icon_post_quote.gif', '', '', '', 'filename');
				$images['icon_edit'] = "$current_template_images/{LANG}/" . $this->img('icon_post_edit.gif', '', '', '', 'filename');			
				$images['icon_search'] = "$current_template_images/{LANG}/" . $this->img('icon_user_search.gif', '', '', '', 'filename');
				$images['icon_profile'] = "$current_template_images/{LANG}/" . $this->img('icon_user_profile.gif', '', '', '', 'filename');
				$images['icon_pm'] = "$current_template_images/{LANG}/" . $this->img('icon_contact_pm.gif', '', '', '', 'filename');
				$images['icon_email'] = "$current_template_images/{LANG}/" . $this->img('icon_contact_email.gif', '', '', '', 'filename');
				$images['icon_delpost'] = "$current_template_images/{LANG}/" . $this->img('icon_post_delete.gif', '', '', '', 'filename');
				$images['icon_ip'] = "$current_template_images/{LANG}/" . $this->img('icon_user_ip.gif', '', '', '', 'filename');
				$images['icon_www'] = "$current_template_images/{LANG}/" . $this->img('icon_contact_www.gif', '', '', '', 'filename');
				$images['icon_icq'] = "$current_template_images/{LANG}/" . $this->img('icon_contact_icq_add.gif', '', '', '', 'filename');
				$images['icon_aim'] = "$current_template_images/{LANG}/" . $this->img('icon_contact_aim.gif', '', '', '', 'filename');
				$images['icon_yim'] = "$current_template_images/{LANG}/" . $this->img('icon_contact_yim.gif', '', '', '', 'filename');
				$images['icon_msnm'] = "$current_template_images/{LANG}/" . $this->img('icon_contact_msnm.gif', '', '', '', 'filename');
				$images['icon_minipost'] = "$current_template_images/" . $this->img('icon_post_target.gif', '', '', '', 'filename');
				$images['icon_gotopost'] = "$current_template_images/" . $this->img('icon_gotopost.gif', '', '', '', 'filename');
				$images['icon_minipost_new'] = "$current_template_images/" . $this->img('icon_post_target_unread.gif', '', '', '', 'filename');
				$images['icon_latest_reply'] = "$current_template_images/" . $this->img('icon_latest_reply.gif', '', '', '', 'filename');
				$images['icon_newest_reply'] = "$current_template_images/" . $this->img('icon_newest_reply.gif', '', '', '', 'filename');

				$images['forum'] = "$current_template_images/" . $this->img('forum_read.gif', '', '27', '', 'filename');
				$images['forum_new'] = "$current_template_images/" . $this->img('forum_unread.gif', '', '', '', 'filename');
				$images['forum_locked'] = "$current_template_images/" . $this->img('forum_read_locked.gif', '', '', '', 'filename');

				// Begin Simple Subforums MOD
				$images['forums'] = "$current_template_images/" . $this->img('forum_read_subforum.gif', '', '', '', 'filename');
				$images['forums_new'] = "$current_template_images/" . $this->img('forum_unread_subforum.gif', '', '', '', 'filename');
				// End Simple Subforums MOD

				$images['folder'] = "$current_template_images/" . $this->img('topic_read.gif', '', '', '', 'filename');
				$images['folder_new'] = "$current_template_images/" . $this->img('topic_unread.gif', '', '', '', 'filename');
				$images['folder_hot'] = "$current_template_images/" . $this->img('topic_read_hot.gif', '', '', '', 'filename');
				$images['folder_hot_new'] = "$current_template_images/" . $this->img('topic_unread_hot.gif', '', '', '', 'filename');
				$images['folder_locked'] = "$current_template_images/" . $this->img('topic_read_locked.gif', '', '', '', 'filename');
				$images['folder_locked_new'] = "$current_template_images/" . $this->img('topic_unread_locked.gif', '', '', '', 'filename');
				$images['folder_sticky'] = "$current_template_images/" . $this->img('topic_read_mine.gif', '', '', '', 'filename');
				$images['folder_sticky_new'] = "$current_template_images/" . $this->img('topic_unread_mine.gif', '', '', '', 'filename');
				$images['folder_announce'] = "$current_template_images/" . $this->img('announce_read.gif', '', '', '', 'filename');
				$images['folder_announce_new'] = "$current_template_images/" . $this->img('announce_unread.gif', '', '', '', 'filename');

				$images['post_new'] = "$current_template_images/{LANG}/" . $this->img('button_topic_new.gif', '', '', '', 'filename');
				$images['post_locked'] = "$current_template_images/{LANG}/" . $this->img('button_topic_locked.gif', '', '', '', 'filename');
				$images['reply_new'] = "$current_template_images/{LANG}/" . $this->img('button_topic_reply.gif', '', '', '', 'filename');
				$images['reply_locked'] = "$current_template_images/{LANG}/" . $this->img('icon_post_target_unread.gif', '', '', '', 'filename');

				$images['pm_inbox'] = "$current_template_images/" . $this->img('msg_inbox.gif', '', '', '', 'filename');
				$images['pm_outbox'] = "$current_template_images/" . $this->img('msg_outbox.gif', '', '', '', 'filename');
				$images['pm_savebox'] = "$current_template_images/" . $this->img('msg_savebox.gif', '', '', '', 'filename');
				$images['pm_sentbox'] = "$current_template_images/" . $this->img('msg_sentbox.gif', '', '', '', 'filename');
				$images['pm_readmsg'] = "$current_template_images/" . $this->img('topic_read.gif', '', '', '', 'filename');
				$images['pm_unreadmsg'] = "$current_template_images/" . $this->img('topic_unread.gif', '', '', '', 'filename');
				$images['pm_replymsg'] = "$current_template_images/{LANG}/" . $this->img('reply.gif', '', '', '', 'filename');
				$images['pm_postmsg'] = "$current_template_images/{LANG}/" . $this->img('msg_newpost.gif', '', '', '', 'filename');
				$images['pm_quotemsg'] = "$current_template_images/{LANG}/" . $this->img('icon_quote.gif', '', '', '', 'filename');
				$images['pm_editmsg'] = "$current_template_images/{LANG}/" . $this->img('icon_edit.gif', '', '', '', 'filename');
				$images['pm_new_msg'] = "";
				$images['pm_no_new_msg'] = "";

				$images['Topic_watch'] = "";
				$images['topic_un_watch'] = "";
				$images['topic_mod_lock'] = "$current_template_images/" . $this->img('topic_lock.gif', '', '', '', 'filename');
				$images['topic_mod_unlock'] = "$current_template_images/" . $this->img('topic_unlock.gif', '', '', '', 'filename');
				$images['topic_mod_split'] = "$current_template_images/" . $this->img('topic_split.gif', '', '', '', 'filename');
				$images['topic_mod_move'] = "$current_template_images/" . $this->img('topic_move.gif', '', '', '', 'filename');
				$images['topic_mod_delete'] = "$current_template_images/" . $this->img('topic_delete.gif', '', '', '', 'filename');

				$images['voting_graphic'][0] = "$current_template_images/voting_bar.gif";
				$images['voting_graphic'][1] = "$current_template_images/voting_bar.gif";
				$images['voting_graphic'][2] = "$current_template_images/voting_bar.gif";
				$images['voting_graphic'][3] = "$current_template_images/voting_bar.gif";
				$images['voting_graphic'][4] = "$current_template_images/voting_bar.gif";

				//
				// Vote graphic length defines the maximum length of a vote result
				// graphic, ie. 100% = this length
				//
				$board_config['vote_graphic_length'] = 205;
				$board_config['privmsg_graphic_length'] = 175;			
			}
			else		
			{
				@include($phpbb_root_path . $this->template_path . 'prosilver/prosilver.cfg');
			}
			
			if (!defined('TEMPLATE_CONFIG'))
			{
				message_die(CRITICAL_ERROR, "Could not open $this->template_name template config file", '', __LINE__, __FILE__, $sql);
			}
			
			$img_lang = (file_exists(@phpbb_realpath($phpbb_root_path . $this->current_template_path . '/images/lang_' . $board_config['default_lang']))) ? $board_config['default_lang'] : 'english';
		
			while(list($key, $value) = @each($images))
			{
				if (!is_array($value))
				{
					$this->images[$key] = $images[$key] = str_replace('{LANG}', $this->img_lang_dir, $value);
				}
			}
		}
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
		global $phpbb_root_path, $phpEx;

		// $lang == $this->lang
		// $help == $this->help
		// - add appropriate variables here, name them as they are used within the language file...
		if (!$use_db)
		{
			if ($use_help && strpos($lang_file, '/') !== false)
			{
				$language_filename = $this->lang_path . substr($lang_file, 0, stripos($lang_file, '/') + 1) . 'help_' . substr($lang_file, stripos($lang_file, '/') + 1) . '.' . $phpEx;
			}
			else
			{
				$language_filename = $this->lang_path . (($use_help) ? 'help_' : '') . $lang_file . '.' . $phpEx;
			}

			//fix for mxp
			if ((@include $language_filename) === false)
			{
				//
				//this will fix the path for shared language files
				//				
				$language_phpbb2_filename = substr_count($language_filename, 'phpbb3') ? str_replace("phpbb3", "phpbb2", $language_filename) : str_replace("phpbb3", "phpbb2", $language_filename);
				$language_phpbb3_filename = substr_count($language_filename, 'phpbb2') ? str_replace("phpbb2", "phpbb3", $language_filename) : str_replace("phpb2", "phpbb3", $language_filename);				
											
				//
				//this will fix the path for anonymouse users
				//				
				$shared_phpbb2_path = substr_count($phpbb_root_path, 'phpbb3') ? str_replace("phpbb3", "phpbb2", $phpbb_root_path) : str_replace("phpbb3", "phpbb2", $phpbb_root_path);
				$shared_phpbb3_path = substr_count($phpbb_root_path, 'phpbb2') ? str_replace("phpbb2", "phpbb3", $phpbb_root_path) : str_replace("phpb2", "phpbb3", $phpbb_root_path);				
							
				if ((@include $language_phpbb3_filename) !== false)
				{
					//continue;
				}
				elseif ((@include $language_phpbb2_filename) !== false)
				{
					//continue;
				}				
				elseif ((@include $phpbb_root_path . $language_filename) !== false)
				{
					//continue;
				}
				elseif ((@include $phpbb_root_path . $language_filename) !== false)
				{
					//continue;
				}				
				elseif ((@include str_replace("phpbb3", "phpbb2", $language_filename)) !== false)
				{
					//continue;
				}
				elseif ((@include str_replace("phpbb2", "phpbb3", $language_filename)) === false)
				{
					$language_filename = $phpbb_root_path . '/language/' .$this->lang_english_name . (($use_help) ? 'help_' : '') . $lang_file . '.' . $phpEx;
					
					if ((@include str_replace("phpbb3", "phpbb2", $language_filename)) !== false)
					{
						die('Language file (set_lang) ' . str_replace("phpbb2", "phpbb3", $language_filename) . ' couldn\'t be opened by set_lang().');
					}					
				}				
			}
		}
		else
		{
			// Get Database Language Strings
			// Put them into $lang if nothing is prefixed, put them into $help if help: is prefixed
			// For example: help:faq, posting
			die("You should not use db with phpbb2!");
		}

		// We include common language file here to not load it every time a custom language file is included
		$this->lang = &$lang;
	}
	
	/**
	* Add Language Items from an extension - use_db and use_help are assigned where needed (only use them to force inclusion)
	*
	* @param string $ext_name The extension to load language from, or empty for core files
	* @param mixed $lang_set specifies the language entries to include
	* @param bool $use_db internal variable for recursion, do not use
	* @param bool $use_help internal variable for recursion, do not use
	*
	* Note: $use_db and $use_help should be removed. Kept for BC purposes.
	*
	* @deprecated: 3.2.0-dev (To be removed: 4.0.0)
	*/
	function add_lang_ext($ext_name, $lang_set, $use_db = false, $use_help = false)
	{
		if ($ext_name === '/')
		{
			$ext_name = '';
		}

		$this->add_lang($lang_set, $use_db, $use_help, $ext_name);
	}
	
	/**
	* More advanced language substitution
	* Function to mimic sprintf() with the possibility of using phpBB's language system to substitute nullar/singular/plural forms.
	* Params are the language key and the parameters to be substituted.
	* This function/functionality is inspired by SHS` and Ashe.
	*
	* Example call: <samp>$user->lang('NUM_POSTS_IN_QUEUE', 1);</samp>
	*/
	/**
	 * Advanced language substitution
	 *
	 * Function to mimic sprintf() with the possibility of using phpBB's language system to substitute nullar/singular/plural forms.
	 * Params are the language key and the parameters to be substituted.
	 * This function/functionality is inspired by SHS` and Ashe.
	 *
	 * Example call: <samp>$user->lang('NUM_POSTS_IN_QUEUE', 1);</samp>
	 *
	 * If the first parameter is an array, the elements are used as keys and subkeys to get the language entry:
	 * Example: <samp>$user->lang(array('datetime', 'AGO'), 1)</samp> uses $user->lang['datetime']['AGO'] as language entry.
	 *
	 * @return string	Return localized string or the language key if the translation is not available
	 */
	public function lang()
	{
		$args = func_get_args();
		$key = $args[0];
		//$key = array_shift($args);
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
			global $lang;
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
				break;
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
		//return $this->lang_array($key, $args);
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
				'is_short'		=> strpos($format, '|'),
				'format_short'	=> substr($format, 0, strpos($format, '|')) . '||' . substr(strrchr($format, '|'), 1),
				'format_long'	=> str_replace('|', '', $format),
				// Filter out values that are not strings (e.g. arrays) for strtr().
				'lang'			=> array_filter($this->lang['datetime'], 'is_string'),
			);

			// Short representation of month in format? Some languages use different terms for the long and short format of May
			if ((strpos($format, '\M') === false && strpos($format, 'M') !== false) || (strpos($format, '\r') === false && strpos($format, 'r') !== false))
			{
				$date_cache[$format]['lang']['May'] = $this->lang('datetime', 'May_short');
			}
		}

		// Zone offset
		$zone_offset = $this->timezone + $this->dst;

		// Show date <= 1 hour ago as 'xx min ago' but not greater than 60 seconds in the future
		// A small tolerence is given for times in the future but in the same minute are displayed as '< than a minute ago'
		if ($delta <= 3600 && $delta > -60 && ($delta >= -5 || (($now / 60) % 60) == (($gmepoch / 60) % 60)) && $date_cache[$format]['is_short'] !== false && !$forcedate && isset($this->lang['datetime']['AGO']))
		{
			return $this->lang(array('datetime', 'AGO'), max(0, (int) floor($delta / 60)));
		}

		if (!$midnight)
		{
			list($d, $m, $y) = explode(' ', gmdate('j n Y', time() + $zone_offset));
			$midnight = gmmktime(0, 0, 0, $m, $d, $y) - $zone_offset;
		}

		if ($date_cache[$format]['is_short'] !== false && !$forcedate && !($gmepoch < $midnight - 86400 || $gmepoch > $midnight + 172800))
		{
			$day = false;

			if ($gmepoch > $midnight + 86400)
			{
				$day = 'TOMORROW';
			}
			else if ($gmepoch > $midnight)
			{
				$day = 'TODAY';
			}
			else if ($gmepoch > $midnight - 86400)
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
	* Create a \phpbb\datetime object in the context of the current user
	*
	* @since 3.1
	* @param string $time String in a format accepted by strtotime().
	* @param DateTimeZone $timezone Time zone of the time.
	* @return \phpbb\datetime Date time object linked to the current users locale
	*/
	public function create_datetime($time = 'now', \DateTimeZone $timezone = null)
	{
		$timezone = $timezone ?: $this->timezone;
		/**
		$timezones = array('Europe/London', 'Mars/Olympus', 'Mars/Ascraeus', timezone_name_from_abbr('', $timezone, 0));
				
		foreach ($timezones as $tz) 
		{
		    try 
			{
		        $mars = new DateTimeZone($tz);
		    } 
			
			catch(Exception $e) 
			{
		        echo $e->getMessage() . '<br />';
		    }
		}
		*/		
		return new DateTime($time, new DateTimeZone(timezone_name_from_abbr('', $timezone, 0)));
	}

	/**
	* Get the UNIX timestamp for a datetime in the users timezone, so we can store it in the database.
	*
	* @param	string			$format		Format of the entered date/time
	* @param	string			$time		Date/time with the timezone applied
	* @param	DateTimeZone	$timezone	Timezone of the date/time, falls back to timezone of current user
	* @return	int			Returns the unix timestamp
	*/
	public function get_timestamp_from_format($format, $time, \DateTimeZone $timezone = null)
	{
		$timezone = $timezone ?: $this->timezone;
		$date = \DateTime::createFromFormat($format, $time, $timezone);
		return ($date !== false) ? $date->format('U') : false;
	}

	/**
	* Get language id currently used by the user
	*/
	function get_iso_lang_id()
	{
		global $board_config, $db;

		if (!empty($this->lang_id))
		{
			return $this->lang_id;
		}
		
		if (!$this->lang_name)
		{
			$this->lang_name = $board_config['default_lang'];
		}

		$sql = 'SELECT lang_id
			FROM ' . LANG_TABLE . "
			WHERE lang_iso = '" . $db->sql_escape($this->lang_name) . "'";
		$result = $db->sql_query($sql);
		$this->lang_id = (int) $db->sql_fetchfield('lang_id');
		$db->sql_freeresult($result);

		return $this->lang_id;
	}
	
	/**
	* Generates default bitfield
	*
	* This bitfield decides which bbcodes are defined in a template.
	*
	* @return string Bitfield
	*/
	public function default_bitfield()
	{
		static $value;
		
		if (isset($value))
		{
			return $value;
		}

		// Hardcoded template bitfield to add for new templates
		$default_bitfield = '1111111111111';
		
		if (!class_exists('bitfield'))
		{		
			global $phpbb_root_path, $phpEx;
			include_once($phpbb_root_path . 'includes/bbcode.'.$phpEx);
		}			
		
		$bitfield = new bitfield();		
		
		for ($i = 0; $i < strlen($default_bitfield); $i++)
		{
			if ($default_bitfield[$i] == '1')
			{
				$bitfield->set($i);
			}
		}

		return $bitfield->get_base64();
	}
	
	/**
	* Read style configuration file
	*
	* @param string $dir style directory
	* @return array|bool Style data, false on error
	*/
	protected function read_style_cfg($dir)
	{
		static $required = array('name', 'phpbb_version', 'copyright');
		$cfg = parse_cfg_file($this->styles_path . $dir . '/style.cfg');

		// Check if it is a valid file
		foreach ($required as $key)
		{
			if (!isset($cfg[$key]))
			{
				return false;
			}
		}

		// Check data
		if (!isset($cfg['parent']) || !is_string($cfg['parent']) || $cfg['parent'] == $cfg['name'])
		{
			$cfg['parent'] = '';
		}
		if (!isset($cfg['template_bitfield']))
		{
			$cfg['template_bitfield'] = $this->default_bitfield();
		}

		return $cfg;
	}
	
	/**
	* Specify/Get phpBB3 images array from phpBB2 images  variable
	*/
	function image_rows($images)
	{	
			/* Here we overwrite phpBB images from the template db or configuration file  */		
			$rows = array( 
			array(	'image_id' => 1, 
					'image_name' => $this->img_name_ext('site_logo.gif', false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext('site_logo.gif', false, false, $type = 'filename'), 
					'image_lang' => '',
					'image_height' => 52, 
					'image_width' => 139, 
					'imageset_id' => 1 
				), 
			array(	'image_id' => 2, 
					'image_name' => 'forum_link', 
					'image_filename' => 'forum_link.gif', 
					'image_lang' => '', 
					'image_height' => 27, 
					'image_width' => 27, 
					'imageset_id' => 1 
				), 
			array( 'image_id' => 3, 
					'image_name' => $this->img_name_ext($images['forum'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['forum'], false, false, $type = 'filename'), 
					'image_lang' => '', 
					'image_height' => 27, 
					'image_width' => 27, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 4, 
					'image_name' => $this->img_name_ext($images['forum_locked'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['forum_locked'], false, false, $type = 'filename'), 
					'image_lang' => '',
					'image_height' => 27, 
					'image_width' => 27, 
					'imageset_id' => 1 
					),
			array( 'image_id' => 5, 
					'image_name' => $this->img_name_ext($images['forums'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['forums'], false, false, $type = 'filename'), 
					'image_lang' => '',
					'image_height' => 27, 
					'image_width' => 27, 
					'imageset_id' => 1 
					), 
			array( 
					'image_id' => 6, 
					'image_name' => $this->img_name_ext($images['forum_new'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['forum_new'], false, false, $type = 'filename'), 
					'image_lang' => '',
					'image_height' => 27, 
					'image_width' => 27, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 7, 
					'image_name' => 'forum_unread_locked', 
					'image_filename' => 'forum_unread_locked.gif', 
					'image_lang' => '', 'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 8, 
					'image_name' => $this->img_name_ext($images['forums_new'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['forums_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 9, 
					'image_name' => 'topic_moved', 
					'image_filename' => 'topic_moved.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 10, 
					'image_name' => $this->img_name_ext($images['folder'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 11, 
					'image_name' => $this->img_name_ext($images['folder_sticky'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_sticky'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 12, 
					'image_name' => $this->img_name_ext($images['folder_hot'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_hot'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 13, 
					'image_name' => 'topic_read_hot_mine', 
					'image_filename' => 'topic_read_hot_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 14, 
					'image_name' => $this->img_name_ext($images['folder_locked'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_locked'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 15, 
					'image_name' => 'topic_read_locked_mine', 
					'image_filename' => 'topic_read_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 16, 
					'image_name' => $this->img_name_ext($images['folder_new'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 17, 
					'image_name' => $this->img_name_ext($images['folder_sticky_new'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_sticky_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 18, 
					'image_name' => $this->img_name_ext($images['folder_hot_new'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_hot_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 19, 
					'image_name' => 'topic_unread_hot_mine', 
					'image_filename' => 'topic_unread_hot_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					),
			array( 'image_id' => 20, 
					'image_name' => $this->img_name_ext($images['folder_locked_new'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_locked_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 21, 
					'image_name' => 'topic_unread_locked_mine', 
					'image_filename' => 'topic_unread_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 22, 
					'image_name' => 'sticky_read', 
					'image_filename' => 'sticky_read.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 23, 
					'image_name' => 'sticky_read_mine', 
					'image_filename' => 'sticky_read_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 24, 
					'image_name' => 'sticky_read_locked', 
					'image_filename' => 'sticky_read_locked.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 25, 
					'image_name' => 'sticky_read_locked_mine', 
					'image_filename' => 'sticky_read_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 26, 
					'image_name' => 'sticky_unread', 
					'image_filename' => 'sticky_unread.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 27, 
					'image_name' => 'sticky_unread_mine', 
					'image_filename' => 'sticky_unread_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 28, 
					'image_name' => 'sticky_unread_locked', 
					'image_filename' => 'sticky_unread_locked.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 29, 
					'image_name' => 'sticky_unread_locked_mine', 
					'image_filename' => 'sticky_unread_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 30, 
					'image_name' => $this->img_name_ext($images['folder_announce'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_announce'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 31, 
					'image_name' => 'announce_read_mine', 
					'image_filename' => 'announce_read_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 32, 
					'image_name' => 'announce_read_locked', 
					'image_filename' => 'announce_read_locked.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 33, 
					'image_name' => 'announce_read_locked_mine', 
					'image_filename' => 'announce_read_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 34, 
					'image_name' => $this->img_name_ext($images['folder_announce_new'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['folder_announce_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 35, 
					'image_name' => 'announce_unread_mine', 
					'image_filename' => 'announce_unread_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 36, 
					'image_name' => 'announce_unread_locked', 
					'image_filename' => 'announce_unread_locked.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 37, 
					'image_name' => 'announce_unread_locked_mine', 
					'image_filename' => 'announce_unread_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 38, 
					'image_name' => 'global_read', 
					'image_filename' => $this->img_name_ext($images['folder_announce'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 39, 
					'image_name' => 'global_read_mine', 
					'image_filename' => 'announce_read_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 40, 
					'image_name' => 'global_read_locked', 
					'image_filename' => 'announce_read_locked.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 41, 
					'image_name' => 'global_read_locked_mine', 
					'image_filename' => 'announce_read_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1
					), 
			array( 'image_id' => 42, 
					'image_name' => 'global_unread', 
					'image_filename' => $this->img_name_ext($images['folder_announce_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 43, 
					'image_name' => 'global_unread_mine', 
					'image_filename' => 'announce_unread_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 44, 
					'image_name' => 'global_unread_locked', 
					'image_filename' => 'announce_unread_locked.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 45, 
					'image_name' => 'global_unread_locked_mine', 
					'image_filename' => 'announce_unread_locked_mine.gif', 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 46, 
					'image_name' => 'pm_read', 
					'image_filename' => $this->img_name_ext($images['folder'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 47, 
					'image_name' => 'pm_unread', 
					'image_filename' => $this->img_name_ext($images['folder_new'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 27, 
					'image_width' => 27 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 48, 
					'image_name' => 'icon_back_top', 
					'image_filename' => 'icon_back_top.gif', 
					'image_lang' => '',  
					'image_height' => 11, 
					'image_width' => 11 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 49, 
					'image_name' => $this->img_name_ext($images['icon_aim'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['icon_aim'], false, false, $type = 'filename'), 
					'image_lang' => '{LANG}',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 50, 
					'image_name' => $this->img_name_ext($images['icon_email'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['icon_email'], false, false, $type = 'filename'), 
					'image_lang' => '{LANG}',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 51, 
					'image_name' => $this->img_name_ext($images['icon_icq'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['icon_icq'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 52, 
					'image_name' => 'icon_contact_jabber', 
					'image_filename' => 'icon_contact_jabber.gif', 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 53, 
					'image_name' => $this->img_name_ext($images['icon_msnm'], false, false, $type = 'name'),  
					'image_filename' => $this->img_name_ext($images['icon_msnm'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 54, 
					'image_name' => $this->img_name_ext($images['icon_www'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['icon_www'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 55, 
					'image_name' => $this->img_name_ext($images['icon_yim'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['icon_yim'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 56, 
					'image_name' => $this->img_name_ext($images['icon_delpost'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['icon_delpost'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 57, 
					'image_name' => 'icon_post_info', 
					'image_filename' => 'icon_post_info.gif', 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 58, 
					'image_name' => 'icon_post_report', 
					'image_filename' => 'icon_post_report.gif', 
					'image_lang' => '',  
					'image_height' => 20, 
					'image_width' => 20, 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 59, 
					'image_name' => $this->img_name_ext($images['icon_minipost'], false, false, $type = 'name'), 
					'image_filename' => $this->img_name_ext($images['icon_minipost'], false, false, $type = 'filename'), 
					'image_lang' => '',  
					'image_height' => 9, 
					'image_width' => 11 , 
					'imageset_id' => 1 
					), 
			array( 'image_id' => 60, 
					 'image_name' => $this->img_name_ext($images['icon_minipost_new'], false, false, $type = 'name'), 
					 'image_filename' => $this->img_name_ext($images['icon_minipost_new'], false, false, $type = 'filename'), 
					 'image_lang' => '',  
					 'image_height' => 9, 
					 'image_width' => 11 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 61, 
					 'image_name' => 'icon_topic_attach', 
					 'image_filename' => 'icon_topic_attach.gif', 
					 'image_lang' => '',  
					 'image_height' => 10, 
					 'image_width' => 7 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 62, 
					 'image_name' => 'icon_topic_latest', 
					 'image_filename' => 'icon_topic_latest.gif', 
					 'image_lang' => '',  
					 'image_height' => 9, 
					 'image_width' => 11 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 63, 
					 'image_name' => 'icon_topic_newest', 
					 'image_filename' => 'icon_topic_newest.gif', 
					 'image_lang' => '',  
					 'image_height' => 9, 
					 'image_width' => 11 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 64, 
					 'image_name' => 'icon_topic_reported', 
					 'image_filename' => 'icon_topic_reported.gif', 
					 'image_lang' => '',  
					 'image_height' => 14, 
					 'image_width' => 16 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 65, 
					 'image_name' => 'icon_topic_unapproved', 
					 'image_filename' => 'icon_topic_unapproved.gif', 
					 'image_lang' => '',  
					 'image_height' => 14, 
					 'image_width' => 16 , 
					 'imageset_id' => 1
					 ), 
			array( 'image_id' => 66, 
					 'image_name' => 'icon_user_warn', 
					 'image_filename' => 'icon_user_warn.gif', 
					 'image_lang' => '',  
					 'image_height' => 20, 
					 'image_width' => 20, 
					 'imageset_id' => 1
					 ), 
			array( 'image_id' => 67, 
					 'image_name' => 'subforum_read', 
					 'image_filename' => 'subforum_read.gif', 
					 'image_lang' => '',  
					 'image_height' => 9, 
					 'image_width' => 11 , 
					 'imageset_id' => 1
					 ), 
			array( 'image_id' => 68, 
					 'image_name' => 'subforum_unread', 
					 'image_filename' => 'subforum_unread.gif', 
					 'image_lang' => '',  
					 'image_height' => 9, 
					 'image_width' => 11 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 69, 
					 'image_name' => $this->img_name_ext($images['icon_pm'], false, false, $type = 'name'), 
					 'image_filename' => $this->img_name_ext($images['icon_pm'], false, false, $type = 'filename'), 
					 'image_lang' => '{LANG}',   
					 'image_height' => 20, 
					 'image_width' => 28 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 70, 
					 'image_name' => $this->img_name_ext($images['icon_edit'], false, false, $type = 'name'), 
					 'image_filename' => $this->img_name_ext($images['icon_edit'], false, false, $type = 'filename'),  
					 'image_lang' => '{LANG}', 
					 'image_height' => 20, 
					 'image_width' => 42 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 71, 
					 'image_name' => $this->img_name_ext($images['icon_quote'], false, false, $type = 'name'), 
					 'image_filename' => $this->img_name_ext($images['icon_quote'], false, false, $type = 'filename'), 
					 'image_lang' => '{LANG}', 
					 'image_height' => 20, 
					 'image_width' => 54 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 72, 
					 'image_name' => 'icon_user_online', 
					 'image_filename' => 'icon_user_online.gif', 
					 'image_lang' => '{LANG}', 
					 'image_height' => 58, 
					 'image_width' => 58 , 
					 'imageset_id' => 1 
					 ),
			array( 'image_id' => 73, 
					 'image_name' => 'button_pm_forward', 
					 'image_filename' => 'button_pm_forward.gif', 
					 'image_lang' => '{LANG}', 
					 'image_height' => 25, 
					 'image_width' => 96 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 74, 
					 'image_name' => 'button_pm_new', 
					 'image_filename' => 'button_pm_new.gif', 
					 'image_lang' => '{LANG}', 
					 'image_height' => 25, 
					 'image_width' => 84 , 
					 'imageset_id' => 1 
					 ), 
			array( 'image_id' => 75, 
					 'image_name' => 'button_pm_reply', 
					 'image_filename' => 'button_pm_reply.gif', 
					 'image_lang' => '{LANG}', 
					 'image_height' => 25, 
					 'image_width' => 96 , 
					 'imageset_id' => 1 
					), 
			array( 'image_id' => 76, 
					 'image_name' => $this->img_name_ext($images['post_locked'], false, false, $type = 'name'), 
					 'image_filename' => $this->img_name_ext($images['post_locked'], false, false, $type = 'filename'), 
					 'image_lang' => '{LANG}', 
					 'image_height' => 25, 
					 'image_width' => 88 , 
					 'imageset_id' => 1 
					), 
			array( 'image_id' => 77, 
					 'image_name' => $this->img_name_ext($images['post_new'], false, false, $type = 'name'), 
					 'image_filename' => $this->img_name_ext($images['post_new'], false, false, $type = 'filename'), 
					 'image_lang' => '{LANG}', 
					 'image_height' => 25, 
					 'image_width' => 96 , 
					 'imageset_id' => 1 
					), 
			array( 'image_id' => 78, 
					 'image_name' => $this->img_name_ext($images['reply_new'], false, false, $type = 'name'), 
					 'image_filename' => $this->img_name_ext($images['reply_new'], false, false, $type = 'filename'), 
					 'image_lang' => '{LANG}', 
					 'image_height' => 25, 
					 'image_width' => 96 , 
					 'imageset_id' => 1
				)	
			);
		return $rows;
	}	
	
	/**
	* Specify/Get image name , extension
	*/
	function img_name_ext($img, $prefix = 'img_', $new_prefix = '', $type = 'filename')
	{	
		if (strpos($img, '.') !== false)
		{
			// Nested img
			$image_filename = $img;
			$img_ext = substr(strrchr($image_filename, '.'), 1);
			$img = basename($image_filename, '.' . $img_ext);			
			
			unset($img_name, $image_filename);
		}
		else
		{
			$img_ext = 'gif';			
		}		
		
		switch ($type)
		{						
			case 'filename':
				return $img . '.' . $img_ext;
			break;
			
			case 'class':
				return $prefix . '_' . $img;
			break;
			
			case 'name':		
				return $img;
			break;
			
			case 'ext':
				return $img_ext;
			break;
		}		
	}	
	
	/**
	* Specify/Get image
	//
	// phpBB2 Graphics - redefined for mxBB
	// - Uncomment and redefine phpBB graphics
	//
	// If you need to redefine some phpBB graphics, look within the phpBB/templates folder for the template_name.cfg file and
	// redefine those $image['xxx'] you want. Note: Many phpBB images are reused all over mxBB (eg see below), thus if you redefine
	// common phpBB images, this will have immedaite effect for all mxBB pages.
	//
	*/
	function img($img, $alt = '', $width = false, $suffix = '', $type = '')
	{
		static $imgs;
		global $phpbb_root_path, $root_path, $theme;
		
		$title = '';

		if ($alt)
		{
			$alt = $this->lang($alt);
			$title = ' title="' . $alt . '"';
		}
		
		if (strpos($img, '.') !== false)
		{
			// Nested img
			$image_filename = $img;
			$img_ext = substr(strrchr($image_filename, '.'), 1);
			$img = basename($image_filename, '.' . $img_ext);
			$this->img_array['image_filename'] = array(
				'img_'.$img => $img . '.' . $img_ext,
			);			
			unset($img_name, $image_filename);
		}
		
		if ($width !== false)
		{
			$this->img_array['image_width'] = array(
				'img_'.$img => $width,
			);	
		}		
				
		// print_r($this->img_array['image_filename']);
		// array ( [img_forum_read] => forum_read.gif )
		// Load phpBB Template configuration data
		$current_template_path = $this->current_template_path;
		$template_name = $this->template_name;
		
		//		
		// - First try phpBB2 then phpBB3 template
		//		
		if ( file_exists($phpbb_root_path . $this->current_template_path . '/' . $this->template_name . '.cfg') )
		{
			@include($phpbb_root_path . $this->current_template_path . '/' . $this->template_name . '.cfg'); 
			@define('TEMPLATE_CONFIG', true);
			
			//$img_keys = array_keys($images);
			//$img_values = array_values($images);
			
			$rows = $this->image_rows($images);
					
			foreach ($rows as $row)
			{
				$row['image_filename'] = rawurlencode($row['image_filename']);
				
				if(empty($row['image_name']))
				{
					//print_r('Your style configuration file has a typo! ');
					//print_r($phpbb_root_path . $this->current_template_path . '/' . $this->template_name . '.cfg ');			
					//print_r($row);
					$row['image_name'] = 'spacer.gif';
				}
				/** 
				* Now check for the correct existance of all of the images into
				* each image of a prosilver based style. 
				*/
				$this->img_array[$row['image_name']] = $row;				
			}	
		}		
		else if ( file_exists($phpbb_root_path . $current_template_path  . '/theme/stylesheet.css') )
		{		
			@define('TEMPLATE_CONFIG', true);
			$current_template_images = $current_template_path . "/theme/images";
		}
		
		//
		// Since we have no current Template Config file, try the cloned template instead
		//
		if ( file_exists($phpbb_root_path . $this->cloned_current_template_path . '/' . $this->cloned_template_name . '.cfg') && !defined('TEMPLATE_CONFIG') )
		{
			$current_template_path = $this->cloned_current_template_path;
			$template_name = $this->cloned_template_name;

			@include($phpbb_root_path . $this->cloned_current_template_path . '/' . $this->cloned_template_name . '.cfg');
			
			$rows = $this->image_rows($images);
					
			foreach ($rows as $row)
			{
				$row['image_filename'] = rawurlencode($row['image_filename']);
				
				if(empty($row['image_name']))
				{
					print_r('Your style configuration file has a typo! ');
					print_r($phpbb_root_path . $this->current_template_path . '/' . $this->template_name . '.cfg ');			
					print_r($row);
				}
				/** 
				* Now check for the correct existance of all of the images into
				* each image of a prosilver based style. 
				*/
				$this->img_array[$row['image_name']] = $row;				
			}	
		}
		
		//
		// Last attempt, use default template intead
		//
		if ( file_exists($phpbb_root_path . $this->default_current_template_path . '/' . $this->default_template_name . '.cfg') && !defined('TEMPLATE_CONFIG') )
		{
			$current_template_path = $this->default_current_template_path;
			$template_name = $this->default_template_name;

			@include($phpbb_root_path . $this->default_current_template_path . '/' . $this->default_template_name . '.cfg');
			
			$rows = $this->image_rows($images);
					
			foreach ($rows as $row)
			{
				$row['image_filename'] = rawurlencode($row['image_filename']);
				
				if(empty($row['image_name']))
				{
					print_r('Your style configuration file has a typo! ');
					print_r($phpbb_root_path . $this->current_template_path . '/' . $this->template_name . '.cfg ');			
					print_r($row);
				}
				/** 
				* Now check for the correct existance of all of the images into
				* each image of a prosilver based style. 
				*/
				$this->img_array[$row['image_name']] = $row;				
			}			
		}		
		
		//		
		// - First try phpBB2 then phpBB3 template lang images then old Olympus image sets
		// default language		
		if ( file_exists($phpbb_root_path . $current_template_path . '/images/lang_' . $this->default_language_name . '/') )
		{
			$this->img_lang = $this->default_language_name;
		}		
		else if ( file_exists($phpbb_root_path . $current_template_path  . '/theme/images/lang_' . $this->default_language_name . '/') )
		{		
			$this->img_lang = $this->default_language_name;
		}		
		else if ( file_exists($phpbb_root_path . $current_template_path  . '/theme/images/' . $this->default_language . '/') )
		{		
			$this->img_lang = $this->default_language;
		}
		else if ( file_exists($phpbb_root_path . $current_template_path  . '/theme/imageset/' . $this->default_language . '/') )
		{		
			$this->img_lang = $this->default_language;
		}		
		
		//		
		// - First try phpBB2 then phpBB3 template lang images then old Olympus image sets
		// user language		
		if ( file_exists($phpbb_root_path . $current_template_path . '/images/lang_' . $this->user_language_name . '/') )
		{
			$this->img_lang = $this->user_language_name;
		}		
		else if ( file_exists($phpbb_root_path . $current_template_path  . '/theme/images/lang_' . $this->user_language_name . '/') )
		{		
			$this->img_lang = $this->user_language_name;
		}		
		else if ( file_exists($phpbb_root_path . $current_template_path  . '/theme/images/' . $this->user_language . '/') )
		{		
			$this->img_lang = $this->user_language;
		}
		else if ( file_exists($phpbb_root_path . $current_template_path  . '/theme/imageset/' . $this->user_language . '/') )
		{		
			$this->img_lang = $this->user_language;
		}
		
		if (empty($this->img_array))
		{
			/** 
				* Now check for the correct existance of all of the images into
				* each image of a prosilver based style. 
				foreach ($rows as $row)
				{
					$row['image_filename'] = rawurlencode($row['image_filename']);
					$this->img_array[$row['image_name']] = $row;				
				}
			*/
			trigger_error('NO_STYLE_DATA', E_USER_ERROR);
		}		
					
		$img_data = &$this->img_array['image_filename'][$img];
		
		if (empty($img_data))
		{		
			if (!isset($this->img_array['image_filename']['img_'.$img]) && !isset($this->img_array['image_filename'][$img]))
			{
				// Do not fill the image to let designers decide what to do if the image is empty
				$img_data = '';
				return $img_data;
			}
			
			if (isset($this->img_array['image_lang']['img_'.$img]) && isset($this->img_array['image_lang'][$img]))
			{
				//		
				// - First try phpBB2 then phpBB3 template lang images
				//		
				if ( file_exists($phpbb_root_path . $current_template_path . '/images/' . $this->img_array['image_lang']['img_'.$img] . '/') )
				{
					$current_template_images = $current_template_path . '/images/' . $this->img_array['image_lang']['img_'.$img];
				}		
				else if ( file_exists($phpbb_root_path . $current_template_path  . '/theme/images/' . $this->img_array['image_lang']['img_'.$img] . '/') )
				{		
					$current_template_images = $current_template_path . '/theme/images/' . $this->img_array['image_lang']['img_'.$img];
				}
				else if ( file_exists($phpbb_root_path . $current_template_path  . '/theme/images/' . $this->encode_lang($this->lang_name) . '/') )
				{		
					$current_template_images = $current_template_path  . '/theme/images/' . $this->encode_lang($this->lang_name);
				}
				else if ( file_exists($phpbb_root_path . $current_template_path  . '/theme/imageset/' . $this->encode_lang($this->lang_name) . '/') )
				{		
					$current_template_images = $current_template_path  . '/theme/imageset/' . $this->encode_lang($this->lang_name);
				}				
			}
			
			$img_data['src'] = PHPBB_URL . $current_template_images  . '/' . (!empty($this->img_array['image_filename']['img_'.$img]) ? $this->img_array['image_filename']['img_'.$img] : $this->img_array['image_filename'][$img]);
			$img_data['width'] = !empty($height) ? $height : (!empty($this->img_array['image_width']) ? (!empty($this->img_array['image_width']['img_'.$img]) ? $this->img_array['image_width']['img_'.$img] : (!empty($this->img_array['image_width'][$img]) ? $this->img_array['image_width'][$img] : 47)) : 47);
			$img_data['height'] = !empty($height) ? $height : (!empty($this->img_array['image_height']) ? (!empty($this->img_array['image_width']['img_'.$img]) ? $this->img_array['image_height']['img_'.$img] : (!empty($this->img_array['image_height'][$img]) ? $this->img_array['image_height'][$img] : 47)) : 47);
		}
		
		$alt = (!empty($this->lang[$alt])) ? $this->lang[$alt] : $alt;
		
		$use_width = ($width === false) ? $img_data['width'] : $width;
		
		switch ($type)
		{
			case 'src':
				return $img_data['src'];
			break;

			case 'width':
				return $use_width;
			break;

			case 'height':
				return $img_data['height'];
			break;
							
			case 'filename':
				return $img . '.' . $img_ext;
			break;
			
			case 'class':			
			case 'name':		
				return $img;
			break;
			
			case 'alt':
				return $alt;
			break;
			
			case 'ext':
				return $img_ext;
			break;
			
			case 'full_tag':
				return '<img src="' . $img_data['src'] . '"' . (($use_width) ? ' width="' . $use_width . '"' : '') . (($img_data['height']) ? ' height="' . $img_data['height'] . '"' : '') . ' alt="' . $alt . '" title="' . $alt . '" />';
			break;
			
			case 'html':			
			default:		
				return '<span class="imageset ' . $img . '"' . $title . '>' . $alt . '</span>';						
			break;
		}
	}

	/**
	* Get option bit field from user options.
	*
	* @param int $key option key, as defined in $keyoptions property.
	* @param int $data bit field value to use, or false to use $this->data['user_options']
	* @return bool true if the option is set in the bit field, false otherwise
	* /
	function optionget($key, $data = false)
	{
		$var = ($data !== false) ? $data : $this->data['user_options'];
		return phpbb_optionget($this->keyoptions[$key], $var);
	}
	/** */
	
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
	function optionget($key, $data = false)
	{
		if (!isset($this->keyvalues[$key]))
		{
			$var = ($data) ? $data : '230271'; //$this->data['user_options'];
			$this->keyvalues[$key] = ($var & 1 << $this->keyoptions[$key]) ? true : false;
		}

		return $this->keyvalues[$key];
	}

	/**
	* Set option bit field for user options
	*/
	function optionset($key, $value, $data = false)
	{
		$var = ($data) ? $data : '230271'; //$this->data['user_options'];

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
	
	/**
	 * Load available languages list
	 * author: Jan Kalah aka culprit_cz
	 * @return array available languages list: KEY = folder name
	 */
	function get_lang_list($ext_root_path = '')
	{
		if (count($this->language_list))
		{
			return $this->language_list;
		}
		/* c:\Wamp\www\Rhea\language\ */
		$dir = opendir($this->phpbb_root_path . 'language/');			
		while($f = readdir($dir))
		{
			if (($f == '.' || $f == '..') || !is_dir($this->phpbb_root_path . 'language/' . $f))
			{
				continue;
			}
			$this->language_list[$f] =  $this->ucstrreplace('lang_', '', $f);	
		}
		closedir($dir);
		if (!empty($ext_root_path))
		{	
			$dir = opendir($this->phpbb_root_path . 'ext/' . $ext_root_path . '/language/');			
			while($f = readdir($dir))
			{
				if (($f == '.' || $f == '..') || !is_dir($this->phpbb_root_path . 'ext/' . $ext_root_path . '/language/' . $f))
				{
					continue;
				}
				$this->ext_language_list[$f] =  $this->ucstrreplace('lang_', '', $f);	
			}
			closedir($dir);
			return $this->language_list = array_merge($this->ext_language_list, $this->language_list);
		}			
		return $this->language_list;
	}
	
	/**
	 * encode_lang
	 *
	 * This function is used with phpBB2 backend to specify xml:lang  in overall headers (only two chars are allowed)
	 * Do not change!
	 *
	 * $default_lang = $user->encode_lang($board_config['default_lang']);
	 *
	 * @param unknown_type $lang
	 * @return unknown
	 */
	function encode_lang($lang)
	{
			switch($lang)
			{
				case 'afar':
					$lang_name = 'aa';
				break;
				case 'abkhazian':
					$lang_name = 'ab';
				break;
				case 'avestan':
					$lang_name = 'ae';
				break;
				case 'afrikaans':
					$lang_name = 'af';
				break;
				case 'akan':
					$lang_name = 'ak';
				break;
				case 'amharic':
					$lang_name = 'am';
				break;
				case 'aragonese':
					$lang_name = 'an';
				break;
				case 'arabic':
					$lang_name = 'ar';
				break;
				case 'assamese':
					$lang_name = 'as';
				break;
				case 'avaric':
					$lang_name = 'av';
				break;
				case 'aymara':
					$lang_name = 'ay';
				break;
				case 'azerbaijani':
					$lang_name = 'az';
				break;
				case 'bashkir':
					$lang_name = 'ba';
				break;
				case 'belarusian':
					$lang_name = 'be';
				break;
				case 'bulgarian':
					$lang_name = 'bg';
				break;
				case 'bihari':
					$lang_name = 'bh';
				break;
				case 'bislama':
					$lang_name = 'bi';
				break;
				case 'bambara':
					$lang_name = 'bm';
				break;
				case 'bengali':
					$lang_name = 'bn';
				break;
				case 'tibetan':
					$lang_name = 'bo';
				break;
				case 'breton':
					$lang_name = 'br';
				break;
				case 'bosnian':
					$lang_name = 'bs';
				break;
				case 'catalan':
					$lang_name = 'ca';
				break;
				case 'chechen':
					$lang_name = 'ce';
				break;
				case 'chamorro':
					$lang_name = 'ch';
				break;
				case 'corsican':
					$lang_name = 'co';
				break;
				case 'cree':
					$lang_name = 'cr';
				break;
				case 'czech':
					$lang_name = 'cs';
				break;
				case 'slavonic':
					$lang_name = 'cu';
				break;
				case 'chuvash':
					$lang_name = 'cv';
				break;
				case 'welsh_cymraeg':
					$lang_name = 'cy';
				break;
				case 'danish':
					$lang_name = 'da';
				break;
				case 'german':
					$lang_name = 'de';
				break;
				case 'divehi':
					$lang_name = 'dv';
				break;
				case 'dzongkha':
					$lang_name = 'dz';
				break;
				case 'ewe':
					$lang_name = 'ee';
				break;
				case 'greek':
					$lang_name = 'el';
				break;
				case 'hebrew':
					$lang_name = 'he';
				break;
				case 'english':
					$lang_name = '{LANG}';
				break;
				case 'english_us':
					$lang_name = 'en_us';
				break;
				case 'esperanto':
					$lang_name = 'eo';
				break;
				case 'spanish':
					$lang_name = 'es';
				break;
				case 'estonian':
					$lang_name = 'et';
				break;
				case 'basque':
					$lang_name = 'eu';
				break;
				case 'persian':
					$lang_name = 'fa';
				break;
				case 'fulah':
					$lang_name = 'ff';
				break;
				case 'finnish':
					$lang_name = 'fi';
				break;
				case 'fijian':
					$lang_name = 'fj';
				break;
				case 'faroese':
					$lang_name = 'fo';
				break;
				case 'french':
					$lang_name = 'fr';
				break;
				case 'frisian':
					$lang_name = 'fy';
				break;
				case 'irish':
					$lang_name = 'ga';
				break;
				case 'scottish':
					$lang_name = 'gd';
				break;
				case 'galician':
					$lang_name = 'gl';
				break;
				case 'guaraní':
					$lang_name = 'gn';
				break;
				case 'gujarati':
					$lang_name = 'gu';
				break;
				case 'manx':
					$lang_name = 'gv';
				break;
				case 'hausa':
					$lang_name = 'ha';
				break;
				case 'hebrew':
					$lang_name = 'he';
				break;
				case 'hindi':
					$lang_name = 'hi';
				break;
				case 'hiri_motu':
					$lang_name = 'ho';
				break;
				case 'croatian':
					$lang_name = 'hr';
				break;
				case 'haitian':
					$lang_name = 'ht';
				break;
				case 'hungarian':
					$lang_name = 'hu';
				break;
				case 'armenian':
					$lang_name = 'hy';
				break;
				case 'herero':
					$lang_name = 'hz';
				break;
				case 'interlingua':
					$lang_name = 'ia';
				break;
				case 'indonesian':
					$lang_name = 'id';
				break;
				case 'interlingue':
					$lang_name = 'ie';
				break;
				case 'igbo':
					$lang_name = 'ig';
				break;
				case 'sichuan_yi':
					$lang_name = 'ii';
				break;
				case 'inupiaq':
					$lang_name = 'ik';
				break;
				case 'ido':
					$lang_name = 'io';
				break;
				case 'icelandic':
					$lang_name = 'is';
				break;
				case 'italian':
					$lang_name = 'it';
				break;
				case 'inuktitut':
					$lang_name = 'iu';
				break;
				case 'japanese':
					$lang_name = 'ja';
				break;
				case 'javanese':
					$lang_name = 'jv';
				break;
				case 'georgian':
					$lang_name = 'ka';
				break;
				case 'kongo':
					$lang_name = 'kg';
				break;
				case 'kikuyu':
					$lang_name = 'ki';
				break;
				case 'kwanyama':
					$lang_name = 'kj';
				break;
				case 'kazakh':
					$lang_name = 'kk';
				break;
				case 'kalaallisut':
					$lang_name = 'kl';
				break;
				case 'khmer':
					$lang_name = 'km';
				break;
				case 'kannada':
					$lang_name = 'kn';
				break;
				case 'korean':
					$lang_name = 'ko';
				break;
				case 'kanuri':
					$lang_name = 'kr';
				break;
				case 'kashmiri':
					$lang_name = 'ks';
				break;
				case 'kurdish':
					$lang_name = 'ku';
				break;
				case 'kv':
					$lang_name = 'komi';
				break;
				case 'cornish_kernewek':
					$lang_name = 'kw';
				break;
				case 'kirghiz':
					$lang_name = 'ky';
				break;
				case 'latin':
					$lang_name = 'la';
				break;
				case 'luxembourgish':
					$lang_name = 'lb';
				break;
				case 'ganda':
					$lang_name = 'lg';
				break;
				case 'limburgish':
					$lang_name = 'li';
				break;
				case 'lingala':
					$lang_name = 'ln';
				break;
				case 'lao':
					$lang_name = 'lo';
				break;
				case 'lithuanian':
					$lang_name = 'lt';
				break;
				case 'luba-katanga':
					$lang_name = 'lu';
				break;
				case 'latvian':
					$lang_name = 'lv';
				break;
				case 'malagasy':
					$lang_name = 'mg';
				break;
				case 'marshallese':
					$lang_name = 'mh';
				break;
				case 'maori':
					$lang_name = 'mi';
				break;
				case 'macedonian':
					$lang_name = 'mk';
				break;
				case 'malayalam':
					$lang_name = 'ml';
				break;
				case 'mongolian':
					$lang_name = 'mn';
				break;
				case 'moldavian':
					$lang_name = 'mo';
				break;
				case 'marathi':
					$lang_name = 'mr';
				break;
				case 'malay':
					$lang_name = 'ms';
				break;
				case 'maltese':
					$lang_name = 'mt';
				break;
				case 'burmese':
					$lang_name = 'my';
				break;
				case 'nauruan':
					$lang_name = 'na';
				break;
				case 'norwegian':
					$lang_name = 'nb';
				break;
				case 'ndebele':
					$lang_name = 'nd';
				break;
				case 'nepali':
					$lang_name = 'ne';
				break;
				case 'ndonga':
					$lang_name = 'ng';
				break;
				case 'dutch':
					$lang_name = 'nl';
				break;
				case 'norwegian_nynorsk':
					$lang_name = 'nn';
				break;
				case 'norwegian':
					$lang_name = 'no';
				break;
				case 'southern_ndebele':
					$lang_name = 'nr';
				break;
				case 'navajo':
					$lang_name = 'nv';
				break;
				case 'chichewa':
					$lang_name = 'ny';
				break;
				case 'occitan':
					$lang_name = 'oc';
				break;
				case 'ojibwa':
					$lang_name = 'oj';
				break;
				case 'oromo':
					$lang_name = 'om';
				break;
				case 'oriya':
					$lang_name = 'or';
				break;
				case 'ossetian':
					$lang_name = 'os';
				break;
				case 'panjabi':
					$lang_name = 'pa';
				break;
				case 'pali':
					$lang_name = 'pi';
				break;
				case 'polish':
					$lang_name = 'pl';
				break;
				case 'pashto':
					$lang_name = 'ps';
				break;
				case 'portuguese':
					$lang_name = 'pt';
				break;
				case 'portuguese_brasil':
					$lang_name = 'pt_br';
				break;
				case 'quechua':
					$lang_name = 'qu';
				break;
				case 'romansh':
					$lang_name = 'rm';
				break;
				case 'kirundi':
					$lang_name = 'rn';
				break;
				case 'romanian':
					$lang_name = 'ro';
				break;
				case 'russian':
					$lang_name = 'ru';
				break;
				case 'kinyarwanda':
					$lang_name = 'rw';
				break;
				case 'sanskrit':
					$lang_name = 'sa';
				break;
				case 'sardinian':
					$lang_name = 'sc';
				break;
				case 'sindhi':
					$lang_name = 'sd';
				break;
				case 'northern_sami':
					$lang_name = 'se';
				break;
				case 'sango':
					$lang_name = 'sg';
				break;
				case 'serbo-croatian':
					$lang_name = 'sh';
				break;
				case 'sinhala':
					$lang_name = 'si';
				break;
				case 'slovak':
					$lang_name = 'sk';
				break;
				case 'slovenian':
					$lang_name = 'sl';
				break;
				case 'samoan':
					$lang_name = 'sm';
				break;
				case 'shona':
					$lang_name = 'sn';
				break;
				case 'somali':
					$lang_name = 'so';
				break;
				case 'albanian':
					$lang_name = 'sq';
				break;
				case 'serbian':
					$lang_name = 'sr';
				break;
				case 'swati':
					$lang_name = 'ss';
				break;
				case 'sotho':
					$lang_name = 'st';
				break;
				case 'sundanese':
					$lang_name = 'su';
				break;
				case 'swedish':
					$lang_name = 'sv';
				break;
				case 'swahili':
					$lang_name = 'sw';
				break;
				case 'tamil':
					$lang_name = 'ta';
				break;
				case 'telugu':
					$lang_name = 'te';
				break;
				case 'tajik':
					$lang_name = 'tg';
				break;
				case 'thai':
					$lang_name = 'th';
				break;
				case 'tigrinya':
					$lang_name = 'ti';
				break;
				case 'turkmen':
					$lang_name = 'tk';
				break;
				case 'tagalog':
					$lang_name = 'tl';
				break;
				case 'tswana':
					$lang_name = 'tn';
				break;
				case 'tonga':
					$lang_name = 'to';
				break;
				case 'turkish':
					$lang_name = 'tr';
				break;
				case 'tsonga':
					$lang_name = 'ts';
				break;
				case 'tatar':
					$lang_name = 'tt';
				break;
				case 'twi':
					$lang_name = 'tw';
				break;
				case 'tahitian':
					$lang_name = 'ty';
				break;
				case 'uighur':
					$lang_name = 'ug';
				break;
				case 'ukrainian':
					$lang_name = 'uk';
				break;
				case 'urdu':
					$lang_name = 'ur';
				break;
				case 'uzbek':
					$lang_name = 'uz';
				break;
				case 'venda':
					$lang_name = 've';
				break;
				case 'vietnamese':
					$lang_name = 'vi';
				break;
				case 'volapuk':
					$lang_name = 'vo';
				break;
				case 'walloon':
					$lang_name = 'wa';
				break;
				case 'wolof':
					$lang_name = 'wo';
				break;
				case 'xhosa':
					$lang_name = 'xh';
				break;
				case 'yiddish':
					$lang_name = 'yi';
				break;
				case 'yoruba':
					$lang_name = 'yo';
				break;
				case 'zhuang':
					$lang_name = 'za';
				break;
				case 'chinese':
					$lang_name = 'zh';
				break;
				case 'chinese_simplified':
					$lang_name = 'zh_cmn_hans';
				break;
				case 'chinese_traditional':
					$lang_name = 'zh_cmn_hant';
				break;
				case 'zulu':
					$lang_name = 'zu';
				break;
				default:
					$lang_name = (strlen($lang) > 2) ? substr($lang, 0, 2) : $lang;
					break;
			}
		return $lang_name;
	}
 
	/**
	 * decode_lang
	 *
	 * $default_lang = $user->decode_lang($board_config['default_lang']);
	 *
	 * @param unknown_type $lang
	 * @return unknown
	 */
	function decode_lang($lang)
	{
		//To be upgraded for phpBB3 backend
		return $lang;
	}
	
	/**
	 * ucstrreplace
	 *
	 * $lang_local_name = $user->ucstrreplace($board_config['default_lang']);
	 *
	 * @param unknown_type $lang
	 * @return unknown
	 */
	function ucstrreplace($pattern = '%{$regex}%i', $matches = '', $string) 
	{
		/* return with no uppercase if patern not in string */
		if (strpos($string, $pattern) === false)
		{
			/* known languages */
			switch($string)
			{
				case 'aa':
					$lang_name = 'afar';
				break;
				case 'ab':
					$lang_name = 'abkhazian';
				break;
				case 'ae':
					$lang_name = 'avestan';
				break;
				case 'af':
					$lang_name = 'afrikaans';
				break;
				case 'ak':
					$lang_name = 'akan';
				break;
				case 'am':
					$lang_name = 'amharic';
				break;
				case 'an':
					$lang_name = 'aragonese';
				break;
				case 'ar':
					$lang_name = 'arabic';
				break;
				case 'as':
					$lang_name = 'assamese';
				break;
				case 'av':
					$lang_name = 'avaric';
				break;
				case 'ay':
					$lang_name = 'aymara';
				break;
				case 'az':
					$lang_name = 'azerbaijani';
				break;
				case 'ba':
					$lang_name = 'bashkir';
				break;
				case 'be':
					$lang_name = 'belarusian';
				break;
				case 'bg':
					$lang_name = 'bulgarian';
				break;
				case 'bh':
					$lang_name = 'bihari';
				break;
				case 'bi':
					$lang_name = 'bislama';
				break;
				case 'bm':
					$lang_name = 'bambara';
				break;
				case 'bn':
					$lang_name = 'bengali';
				break;
				case 'bo':
					$lang_name = 'tibetan';
				break;
				case 'br':
					$lang_name = 'breton';
				break;
				case 'bs':
					$lang_name = 'bosnian';
				break;
				case 'ca':
					$lang_name = 'catalan';
				break;
				case 'ce':
					$lang_name = 'chechen';
				break;
				case 'ch':
					$lang_name = 'chamorro';
				break;
				case 'co':
					$lang_name = 'corsican';
				break;
				case 'cr':
					$lang_name = 'cree';
				break;
				case 'cs':
					$lang_name = 'czech';
				break;
				case 'cu':
					$lang_name = 'slavonic';
				break;
				case 'cv':
					$lang_name = 'chuvash';
				break;
				case 'cy':
					$lang_name = 'welsh_cymraeg';
				break;
				case 'da':
					$lang_name = 'danish';
				break;
				case 'de':
					$lang_name = 'german';
				break;
				case 'dv':
					$lang_name = 'divehi';
				break;
				case 'dz':
					$lang_name = 'dzongkha';
				break;
				case 'ee':
					$lang_name = 'ewe';
				break;
				case 'el':
					$lang_name = 'greek';
				break;
				case 'he':
					$lang_name = 'hebrew';
				break;
				case '{LANG}':
					$lang_name = 'english';
				break;
				case 'en_us':
					$lang_name = 'english';
				break;
				case 'eo':
					$lang_name = 'esperanto';
				break;
				case 'es':
					$lang_name = 'spanish';
				break;
				case 'et':
					$lang_name = 'estonian';
				break;
				case 'eu':
					$lang_name = 'basque';
				break;
				case 'fa':
					$lang_name = 'persian';
				break;
				case 'ff':
					$lang_name = 'fulah';
				break;
				case 'fi':
					$lang_name = 'finnish';
				break;
				case 'fj':
					$lang_name = 'fijian';
				break;
				case 'fo':
					$lang_name = 'faroese';
				break;
				case 'fr':
					$lang_name = 'french';
				break;
				case 'fy':
					$lang_name = 'frisian';
				break;
				case 'ga':
					$lang_name = 'irish';
				break;
				case 'gd':
					$lang_name = 'scottish';
				break;
				case 'gl':
					$lang_name = 'galician';
				break;
				case 'gn':
					$lang_name = 'guaraní';
				break;
				case 'gu':
					$lang_name = 'gujarati';
				break;
				case 'gv':
					$lang_name = 'manx';
				break;
				case 'ha':
					$lang_name = 'hausa';
				break;
				case 'he':
					$lang_name = 'hebrew';
				break;
				case 'hi':
					$lang_name = 'hindi';
				break;
				case 'ho':
					$lang_name = 'hiri_motu';
				break;
				case 'hr':
					$lang_name = 'croatian';
				break;
				case 'ht':
					$lang_name = 'haitian';
				break;
				case 'hu':
					$lang_name = 'hungarian';
				break;
				case 'hy':
					$lang_name = 'armenian';
				break;
				case 'hz':
					$lang_name = 'herero';
				break;
				case 'ia':
					$lang_name = 'interlingua';
				break;
				case 'id':
					$lang_name = 'indonesian';
				break;
				case 'ie':
					$lang_name = 'interlingue';
				break;
				case 'ig':
					$lang_name = 'igbo';
				break;
				case 'ii':
					$lang_name = 'sichuan_yi';
				break;
				case 'ik':
					$lang_name = 'inupiaq';
				break;
				case 'io':
					$lang_name = 'ido';
				break;
				case 'is':
					$lang_name = 'icelandic';
				break;
				case 'it':
					$lang_name = 'italian';
				break;
				case 'iu':
					$lang_name = 'inuktitut';
				break;
				case 'ja':
					$lang_name = 'japanese';
				break;
				case 'jv':
					$lang_name = 'javanese';
				break;
				case 'ka':
					$lang_name = 'georgian';
				break;
				case 'kg':
					$lang_name = 'kongo';
				break;
				case 'ki':
					$lang_name = 'kikuyu';
				break;
				case 'kj':
					$lang_name = 'kwanyama';
				break;
				case 'kk':
					$lang_name = 'kazakh';
				break;
				case 'kl':
					$lang_name = 'kalaallisut';
				break;
				case 'km':
					$lang_name = 'khmer';
				break;
				case 'kn':
					$lang_name = 'kannada';
				break;
				case 'ko':
					$lang_name = 'korean';
				break;
				case 'kr':
					$lang_name = 'kanuri';
				break;
				case 'ks':
					$lang_name = 'kashmiri';
				break;
				case 'ku':
					$lang_name = 'kurdish';
				break;
				case 'kv':
					$lang_name = 'komi';
				break;
				case 'kw':
					$lang_name = 'cornish_kernewek';
				break;
				case 'ky':
					$lang_name = 'kirghiz';
				break;
				case 'la':
					$lang_name = 'latin';
				break;
				case 'lb':
					$lang_name = 'luxembourgish';
				break;
				case 'lg':
					$lang_name = 'ganda';
				break;
				case 'li':
					$lang_name = 'limburgish';
				break;
				case 'ln':
					$lang_name = 'lingala';
				break;
				case 'lo':
					$lang_name = 'lao';
				break;
				case 'lt':
					$lang_name = 'lithuanian';
				break;
				case 'lu':
					$lang_name = 'luba-katanga';
				break;
				case 'lv':
					$lang_name = 'latvian';
				break;
				case 'mg':
					$lang_name = 'malagasy';
				break;
				case 'mh':
					$lang_name = 'marshallese';
				break;
				case 'mi':
					$lang_name = 'maori';
				break;
				case 'mk':
					$lang_name = 'macedonian';
				break;
				case 'ml':
					$lang_name = 'malayalam';
				break;
				case 'mn':
					$lang_name = 'mongolian';
				break;
				case 'mo':
					$lang_name = 'moldavian';
				break;
				case 'mr':
					$lang_name = 'marathi';
				break;
				case 'ms':
					$lang_name = 'malay';
				break;
				case 'mt':
					$lang_name = 'maltese';
				break;
				case 'my':
					$lang_name = 'burmese';
				break;
				case 'na':
					$lang_name = 'nauruan';
				break;
				case 'nb':
					$lang_name = 'norwegian';
				break;
				case 'nd':
					$lang_name = 'ndebele';
				break;
				case 'ne':
					$lang_name = 'nepali';
				break;
				case 'ng':
					$lang_name = 'ndonga';
				break;
				case 'nl':
					$lang_name = 'dutch';
				break;
				case 'nn':
					$lang_name = 'norwegian_nynorsk';
				break;
				case 'no':
					$lang_name = 'norwegian';
				break;
				case 'nr':
					$lang_name = 'southern_ndebele';
				break;
				case 'nv':
					$lang_name = 'navajo';
				break;
				case 'ny':
					$lang_name = 'chichewa';
				break;
				case 'oc':
					$lang_name = 'occitan';
				break;
				case 'oj':
					$lang_name = 'ojibwa';
				break;
				case 'om':
					$lang_name = 'oromo';
				break;
				case 'or':
					$lang_name = 'oriya';
				break;
				case 'os':
					$lang_name = 'ossetian';
				break;
				case 'pa':
					$lang_name = 'panjabi';
				break;
				case 'pi':
					$lang_name = 'pali';
				break;
				case 'pl':
					$lang_name = 'polish';
				break;
				case 'ps':
					$lang_name = 'pashto';
				break;
				case 'pt':
					$lang_name = 'portuguese';
				break;
				case 'pt_br':
					$lang_name = 'portuguese_brasil';
				break;
				case 'qu':
					$lang_name = 'quechua';
				break;
				case 'rm':
					$lang_name = 'romansh';
				break;
				case 'rn':
					$lang_name = 'kirundi';
				break;
				case 'ro':
					$lang_name = 'romanian';
				break;
				case 'ru':
					$lang_name = 'russian';
				break;
				case 'rw':
					$lang_name = 'kinyarwanda';
				break;
				case 'sa':
					$lang_name = 'sanskrit';
				break;
				case 'sc':
					$lang_name = 'sardinian';
				break;
				case 'sd':
					$lang_name = 'sindhi';
				break;
				case 'se':
					$lang_name = 'northern_sami';
				break;
				case 'sg':
					$lang_name = 'sango';
				break;
				case 'sh':
					$lang_name = 'serbo-croatian';
				break;
				case 'si':
					$lang_name = 'sinhala';
				break;
				case 'sk':
					$lang_name = 'slovak';
				break;
				case 'sl':
					$lang_name = 'slovenian';
				break;
				case 'sm':
					$lang_name = 'samoan';
				break;
				case 'sn':
					$lang_name = 'shona';
				break;
				case 'so':
					$lang_name = 'somali';
				break;
				case 'sq':
					$lang_name = 'albanian';
				break;
				case 'sr':
					$lang_name = 'serbian';
				break;
				case 'ss':
					$lang_name = 'swati';
				break;
				case 'st':
					$lang_name = 'sotho';
				break;
				case 'su':
					$lang_name = 'sundanese';
				break;
				case 'sv':
					$lang_name = 'swedish';
				break;
				case 'sw':
					$lang_name = 'swahili';
				break;
				case 'ta':
					$lang_name = 'tamil';
				break;
				case 'te':
					$lang_name = 'telugu';
				break;
				case 'tg':
					$lang_name = 'tajik';
				break;
				case 'th':
					$lang_name = 'thai';
				break;
				case 'ti':
					$lang_name = 'tigrinya';
				break;
				case 'tk':
					$lang_name = 'turkmen';
				break;
				case 'tl':
					$lang_name = 'tagalog';
				break;
				case 'tn':
					$lang_name = 'tswana';
				break;
				case 'to':
					$lang_name = 'tonga';
				break;
				case 'tr':
					$lang_name = 'turkish';
				break;
				case 'ts':
					$lang_name = 'tsonga';
				break;
				case 'tt':
					$lang_name = 'tatar';
				break;
				case 'tw':
					$lang_name = 'twi';
				break;
				case 'ty':
					$lang_name = 'tahitian';
				break;
				case 'ug':
					$lang_name = 'uighur';
				break;
				case 'uk':
					$lang_name = 'ukrainian';
				break;
				case 'ur':
					$lang_name = 'urdu';
				break;
				case 'uz':
					$lang_name = 'uzbek';
				break;
				case 've':
					$lang_name = 'venda';
				break;
				case 'vi':
					$lang_name = 'vietnamese';
				break;
				case 'vo':
					$lang_name = 'volapuk';
				break;
				case 'wa':
					$lang_name = 'walloon';
				break;
				case 'wo':
					$lang_name = 'wolof';
				break;
				case 'xh':
					$lang_name = 'xhosa';
				break;
				case 'yi':
					$lang_name = 'yiddish';
				break;
				case 'yo':
					$lang_name = 'yoruba';
				break;
				case 'za':
					$lang_name = 'zhuang';
				break;
				case 'zh':
					$lang_name = 'chinese';
				break;
				case 'zh_cmn_hans':
					$lang_name = 'chinese_simplified';
				break;
				case 'zh_cmn_hant':
					$lang_name = 'chinese_traditional';
				break;
				case 'zu':
					$lang_name = 'zulu';
				break;
				default:
					$lang_name = (strlen($string) > 2) ? ucfirst(str_replace($pattern, '', $string)) : $string;
				break;
			}		
			return ucwords(str_replace(array(" ","-","_"), ' ', $lang_name));	
		}
		return ucwords(str_replace(array(" ","-","_"), ' ', str_replace($pattern, '', $string)));
	}	
}
/**
 * Language file loader
 */
class language_file_loader
{
	/**
	 * @var string	Path to phpBB's root
	 */
	protected $phpbb_root_path;
	
	/**
	 * @var string	Extension of PHP files
	 */
	protected $php_ext;

	/**
	 * @var \phpbb\extension\manager	Extension manager
	 */
	protected $extension_manager;

	/**
	 * Constructor
	 *
	 * @param string	$phpbb_root_path	Path to phpBB's root
	 * @param string	$php_ext Extension of PHP files
	 */
	public function __construct()
	{
		global $phpbb_root_path;
		
		$this->phpbb_root_path	= $phpbb_root_path;	
		$this->php_ext = substr(strrchr(__FILE__, '.'), 1);	
		$this->extension_manager = null;
	}

	/**
	 * Extension manager setter
	 *
	 * @param \phpbb\extension\manager	$extension_manager	Extension manager
	 */
	public function set_extension_manager()
	{
		$extension_manager = new mx_user();
		$this->extension_manager = $extension_manager;
	}

	/**
	 * Loads language array for the given component
	 *
	 * @param string		$component	Name of the language component
	 * @param string|array	$locale		ISO code of the language to load, or array of ISO codes if you want to
	 * 									specify additional language fallback steps
	 * @param array			$lang		Array reference containing language strings
	 */
	public function load($component, $locale, &$lang)
	{
		$locale = (array) $locale;

		// Determine path to language directory
		$path = $this->phpbb_root_path . 'language/';

		$this->load_file($path, $component, $locale, $lang);
	}
	/**
	 * Load core language file
	 *
	 * @param string	$component	Name of the component to load
	 */
	protected function load_core_file($component)
	{
		// Check if the component is already loaded
		if (isset($this->loaded_language_sets['PHPBB'][$component]))
		{
			return;
		}

		$this->loader->load($component, $this->language_fallback, $this->lang);
		$this->loaded_language_sets['PHPBB'][$component] = true;
	}
	/**
	 * Loads language array for the given extension component
	 *
	 * @param string		$extension	Name of the extension
	 * @param string		$component	Name of the language component
	 * @param string|array	$locale ISO code of the language to load, or array of ISO codes if you want to
	 * 					specify additional language fallback steps
	 * @param array	$lang	Array reference containing language strings
	 */
	public function load_extension($extension, $component, $locale = '', &$lang = '')
	{
		// Check if extension manager was loaded
		if ($this->extension_manager === null)
		{
			// If not, let's return
			return;
		}
		$locale = !empty($locale) ? (array) $locale : $this->language_fallback;
		
		$lang = !empty($lang) ? $lang : $this->lang;
		
		// Determine path to language directory
		$path = $this->extension_manager->get_extension_path($extension, true) . 'language/';

		$this->load_file($path, $component, $locale, $lang);
	}

	/**
	 * Prepares language file loading
	 *
	 * @param string	$path		Path to search for file in
	 * @param string	$component	Name of the language component
	 * @param array		$locale		Array containing language fallback options
	 * @param array		$lang		Array reference of language strings
	 */
	protected function load_file($path, $component, $locale, &$lang)
	{
		// This is BC stuff and not the best idea as it makes language fallback
		// implementation quite hard like below.
		if (strpos($this->phpbb_root_path . $component, $path) === 0)
		{
			// Filter out the path
			$path_diff = str_replace($path, '', dirname($this->phpbb_root_path . $component));
			$language_file = basename($component, '.' . $this->php_ext);
			$component = '';

			// This step is needed to resolve language/en/subdir style $component
			// $path already points to the language base directory so we need to eliminate
			// the first directory from the path (that should be the language directory)
			$path_diff_parts = explode('/', $path_diff);

			if (count($path_diff_parts) > 1)
			{
				array_shift($path_diff_parts);
				$component = implode('/', $path_diff_parts) . '/';
			}

			$component .= $language_file;
		}

		// Determine filename
		$filename = $component . '.' . $this->php_ext;
		
		// Determine path to file
		$file_path = $this->get_language_file_path($path, $filename, $locale);
		
		// Load language array
		$this->load_language_file($file_path, $lang);
	}

	/**
	 * This function implements language fallback logic
	 *
	 * @param string	$path		Path to language directory
	 * @param string	$filename	Filename to load language strings from
	 *
	 * @return string	Relative path to language file
	 *
	 * @throws language_file_not_found	When the path to the file cannot be resolved
	 */
	protected function get_language_file_path($path, $filename, $locales)
	{
		$language_file_path = $filename;
		
		// Language fallback logic
		foreach ($locales as $locale)
		{
			$language_file_path = $path . $locale . '/' . $filename;
			
			// If we are in install, try to use the updated version, when available
			if (defined('IN_INSTALL'))
			{
				$install_language_path = str_replace('language/', 'install/update/new/language/', $language_file_path);
				if (file_exists($install_language_path))
				{
					return $install_language_path;
				}
			}

			if (file_exists($language_file_path))
			{
				return $language_file_path;
			}
		}

		// The language file is not exist throw new language_file_not_found(
		print_r('Language file (get_language_file_path) ' . $language_file_path . ' couldn\'t be opened.');
	}

	/**
	 * Loads language file
	 *
	 * @param string	$path	Path to language file to load
	 * @param array	$lang	Reference of the array of language strings
	 */
	protected function load_language_file($path, &$lang)
	{
		// Do not suppress error if in DEBUG mode
		if (defined('DEBUG'))
		{
			include $path;
		}
		else
		{
			@include $path;
		}
	}
}
/**
 * Wrapper class for loading translations
 */
class language extends language_file_loader
{
	/**
	 * Global fallback language
	 *
	 * ISO code of the language to fallback to when the specified language entries
	 * cannot be found.
	 *
	 * @var string
	 */
	const FALLBACK_LANGUAGE = '{LANG}';

	/**
	 * @var array	List of common language files
	 */
	protected $common_language_files;

	/**
	 * @var bool
	 */
	protected $common_language_files_loaded;

	/**
	 * @var string	ISO code of the default board language
	 */
	protected $default_language;

	/**
	 * @var string	ISO code of the User's language
	 */
	protected $user_language;

	/**
	 * @var array	Language fallback array (the order is important)
	 */
	protected $language_fallback;

	/**
	 * @var array	Array of language variables
	 */
	protected $lang;

	/**
	 * @var array	Loaded language sets
	 */
	protected $loaded_language_sets;

	/**
	 * @var \phpbb\language\language_file_loader Language file loader
	 */
	protected $loader;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language_file_loader	$loader			Language file loader
	 * @param array|null							$common_modules	Array of common language modules to load (optional)
	 */
	public function __construct($common_modules = null)
	{
		$this->loader = $this;

		global $board_config, $user;		
		global $phpbb_root_path;
		
		$this->phpbb_root_path	= $phpbb_root_path;
		
		$this->user	= $user;	
		$this->config = $board_config;
	
		$this->php_ext = substr(strrchr(__FILE__, '.'), 1);
		// Set up default information
		$this->user_language		= false;
		$this->default_language		= false;
		$this->lang					= array();
		$this->loaded_language_sets	= array(
			'MXP'	=> array(), //mxp_core
			'MODS'	=> array(), //mxp_modules
			'PHPBB'	=> array(), //phpbb_core
			'phpbb_ext'	=> array(),	//phpbb_ext
		);		
		// Common language files
		if (is_array($common_modules))
		{
			$this->common_language_files = $common_modules;
		}
		else
		{
			$this->common_language_files = array(
				'common',
			);
		}
		$this->common_language_files_loaded = false;
		$this->language_fallback = array(self::FALLBACK_LANGUAGE);
	}
	/**
	 * encode_lang
	 *
	 * $default_lang = $language->encode_lang($config['default_lang']);
	 *
	 * @param unknown_type $lang
	 * @return unknown
	 */
	function encode_lang($lang)
	{
			if ($this->backend == 'phpbb2')
			{
				return $lang;
			}
			else
			{
				$lang = str_replace('lang_', '', $lang);
			}			
			switch($lang)
			{
				case 'afar':
					$lang_name = 'aa';
				break;
				case 'abkhazian':
					$lang_name = 'ab';
				break;
				case 'avestan':
					$lang_name = 'ae';
				break;
				case 'afrikaans':
					$lang_name = 'af';
				break;
				case 'akan':
					$lang_name = 'ak';
				break;
				case 'amharic':
					$lang_name = 'am';
				break;
				case 'aragonese':
					$lang_name = 'an';
				break;
				case 'arabic':
					$lang_name = 'ar';
				break;
				case 'assamese':
					$lang_name = 'as';
				break;
				case 'avaric':
					$lang_name = 'av';
				break;
				case 'aymara':
					$lang_name = 'ay';
				break;
				case 'azerbaijani':
					$lang_name = 'az';
				break;
				case 'bashkir':
					$lang_name = 'ba';
				break;
				case 'belarusian':
					$lang_name = 'be';
				break;
				case 'bulgarian':
					$lang_name = 'bg';
				break;
				case 'bihari':
					$lang_name = 'bh';
				break;
				case 'bislama':
					$lang_name = 'bi';
				break;
				case 'bambara':
					$lang_name = 'bm';
				break;
				case 'bengali':
					$lang_name = 'bn';
				break;
				case 'tibetan':
					$lang_name = 'bo';
				break;
				case 'breton':
					$lang_name = 'br';
				break;
				case 'bosnian':
					$lang_name = 'bs';
				break;
				case 'catalan':
					$lang_name = 'ca';
				break;
				case 'chechen':
					$lang_name = 'ce';
				break;
				case 'chamorro':
					$lang_name = 'ch';
				break;
				case 'corsican':
					$lang_name = 'co';
				break;
				case 'cree':
					$lang_name = 'cr';
				break;
				case 'czech':
					$lang_name = 'cs';
				break;
				case 'slavonic':
					$lang_name = 'cu';
				break;
				case 'chuvash':
					$lang_name = 'cv';
				break;
				case 'welsh_cymraeg':
					$lang_name = 'cy';
				break;
				case 'danish':
					$lang_name = 'da';
				break;
				case 'german':
					$lang_name = 'de';
				break;
				case 'divehi':
					$lang_name = 'dv';
				break;
				case 'dzongkha':
					$lang_name = 'dz';
				break;
				case 'ewe':
					$lang_name = 'ee';
				break;
				case 'greek':
					$lang_name = 'el';
				break;
				case 'hebrew':
					$lang_name = 'he';
				break;
				case 'english':
					$lang_name = '{LANG}';
				break;
				case 'english_us':
					$lang_name = 'en_us';
				break;
				case 'esperanto':
					$lang_name = 'eo';
				break;
				case 'spanish':
					$lang_name = 'es';
				break;
				case 'estonian':
					$lang_name = 'et';
				break;
				case 'basque':
					$lang_name = 'eu';
				break;
				case 'persian':
					$lang_name = 'fa';
				break;
				case 'fulah':
					$lang_name = 'ff';
				break;
				case 'finnish':
					$lang_name = 'fi';
				break;
				case 'fijian':
					$lang_name = 'fj';
				break;
				case 'faroese':
					$lang_name = 'fo';
				break;
				case 'french':
					$lang_name = 'fr';
				break;
				case 'frisian':
					$lang_name = 'fy';
				break;
				case 'irish':
					$lang_name = 'ga';
				break;
				case 'scottish':
					$lang_name = 'gd';
				break;
				case 'galician':
					$lang_name = 'gl';
				break;
				case 'guaraní':
					$lang_name = 'gn';
				break;
				case 'gujarati':
					$lang_name = 'gu';
				break;
				case 'manx':
					$lang_name = 'gv';
				break;
				case 'hausa':
					$lang_name = 'ha';
				break;
				case 'hebrew':
					$lang_name = 'he';
				break;
				case 'hindi':
					$lang_name = 'hi';
				break;
				case 'hiri_motu':
					$lang_name = 'ho';
				break;
				case 'croatian':
					$lang_name = 'hr';
				break;
				case 'haitian':
					$lang_name = 'ht';
				break;
				case 'hungarian':
					$lang_name = 'hu';
				break;
				case 'armenian':
					$lang_name = 'hy';
				break;
				case 'herero':
					$lang_name = 'hz';
				break;
				case 'interlingua':
					$lang_name = 'ia';
				break;
				case 'indonesian':
					$lang_name = 'id';
				break;
				case 'interlingue':
					$lang_name = 'ie';
				break;
				case 'igbo':
					$lang_name = 'ig';
				break;
				case 'sichuan_yi':
					$lang_name = 'ii';
				break;
				case 'inupiaq':
					$lang_name = 'ik';
				break;
				case 'ido':
					$lang_name = 'io';
				break;
				case 'icelandic':
					$lang_name = 'is';
				break;
				case 'italian':
					$lang_name = 'it';
				break;
				case 'inuktitut':
					$lang_name = 'iu';
				break;
				case 'japanese':
					$lang_name = 'ja';
				break;
				case 'javanese':
					$lang_name = 'jv';
				break;
				case 'georgian':
					$lang_name = 'ka';
				break;
				case 'kongo':
					$lang_name = 'kg';
				break;
				case 'kikuyu':
					$lang_name = 'ki';
				break;
				case 'kwanyama':
					$lang_name = 'kj';
				break;
				case 'kazakh':
					$lang_name = 'kk';
				break;
				case 'kalaallisut':
					$lang_name = 'kl';
				break;
				case 'khmer':
					$lang_name = 'km';
				break;
				case 'kannada':
					$lang_name = 'kn';
				break;
				case 'korean':
					$lang_name = 'ko';
				break;
				case 'kanuri':
					$lang_name = 'kr';
				break;
				case 'kashmiri':
					$lang_name = 'ks';
				break;
				case 'kurdish':
					$lang_name = 'ku';
				break;
				case 'kv':
					$lang_name = 'komi';
				break;
				case 'cornish_kernewek':
					$lang_name = 'kw';
				break;
				case 'kirghiz':
					$lang_name = 'ky';
				break;
				case 'latin':
					$lang_name = 'la';
				break;
				case 'luxembourgish':
					$lang_name = 'lb';
				break;
				case 'ganda':
					$lang_name = 'lg';
				break;
				case 'limburgish':
					$lang_name = 'li';
				break;
				case 'lingala':
					$lang_name = 'ln';
				break;
				case 'lao':
					$lang_name = 'lo';
				break;
				case 'lithuanian':
					$lang_name = 'lt';
				break;
				case 'luba-katanga':
					$lang_name = 'lu';
				break;
				case 'latvian':
					$lang_name = 'lv';
				break;
				case 'malagasy':
					$lang_name = 'mg';
				break;
				case 'marshallese':
					$lang_name = 'mh';
				break;
				case 'maori':
					$lang_name = 'mi';
				break;
				case 'macedonian':
					$lang_name = 'mk';
				break;
				case 'malayalam':
					$lang_name = 'ml';
				break;
				case 'mongolian':
					$lang_name = 'mn';
				break;
				case 'moldavian':
					$lang_name = 'mo';
				break;
				case 'marathi':
					$lang_name = 'mr';
				break;
				case 'malay':
					$lang_name = 'ms';
				break;
				case 'maltese':
					$lang_name = 'mt';
				break;
				case 'burmese':
					$lang_name = 'my';
				break;
				case 'nauruan':
					$lang_name = 'na';
				break;
				case 'norwegian':
					$lang_name = 'nb';
				break;
				case 'ndebele':
					$lang_name = 'nd';
				break;
				case 'nepali':
					$lang_name = 'ne';
				break;
				case 'ndonga':
					$lang_name = 'ng';
				break;
				case 'dutch':
					$lang_name = 'nl';
				break;
				case 'norwegian_nynorsk':
					$lang_name = 'nn';
				break;
				case 'norwegian':
					$lang_name = 'no';
				break;
				case 'southern_ndebele':
					$lang_name = 'nr';
				break;
				case 'navajo':
					$lang_name = 'nv';
				break;
				case 'chichewa':
					$lang_name = 'ny';
				break;
				case 'occitan':
					$lang_name = 'oc';
				break;
				case 'ojibwa':
					$lang_name = 'oj';
				break;
				case 'oromo':
					$lang_name = 'om';
				break;
				case 'oriya':
					$lang_name = 'or';
				break;
				case 'ossetian':
					$lang_name = 'os';
				break;
				case 'panjabi':
					$lang_name = 'pa';
				break;
				case 'pali':
					$lang_name = 'pi';
				break;
				case 'polish':
					$lang_name = 'pl';
				break;
				case 'pashto':
					$lang_name = 'ps';
				break;
				case 'portuguese':
					$lang_name = 'pt';
				break;
				case 'portuguese_brasil':
					$lang_name = 'pt_br';
				break;
				case 'quechua':
					$lang_name = 'qu';
				break;
				case 'romansh':
					$lang_name = 'rm';
				break;
				case 'kirundi':
					$lang_name = 'rn';
				break;
				case 'romanian':
					$lang_name = 'ro';
				break;
				case 'russian':
					$lang_name = 'ru';
				break;
				case 'kinyarwanda':
					$lang_name = 'rw';
				break;
				case 'sanskrit':
					$lang_name = 'sa';
				break;
				case 'sardinian':
					$lang_name = 'sc';
				break;
				case 'sindhi':
					$lang_name = 'sd';
				break;
				case 'northern_sami':
					$lang_name = 'se';
				break;
				case 'sango':
					$lang_name = 'sg';
				break;
				case 'serbo-croatian':
					$lang_name = 'sh';
				break;
				case 'sinhala':
					$lang_name = 'si';
				break;
				case 'slovak':
					$lang_name = 'sk';
				break;
				case 'slovenian':
					$lang_name = 'sl';
				break;
				case 'samoan':
					$lang_name = 'sm';
				break;
				case 'shona':
					$lang_name = 'sn';
				break;
				case 'somali':
					$lang_name = 'so';
				break;
				case 'albanian':
					$lang_name = 'sq';
				break;
				case 'serbian':
					$lang_name = 'sr';
				break;
				case 'swati':
					$lang_name = 'ss';
				break;
				case 'sotho':
					$lang_name = 'st';
				break;
				case 'sundanese':
					$lang_name = 'su';
				break;
				case 'swedish':
					$lang_name = 'sv';
				break;
				case 'swahili':
					$lang_name = 'sw';
				break;
				case 'tamil':
					$lang_name = 'ta';
				break;
				case 'telugu':
					$lang_name = 'te';
				break;
				case 'tajik':
					$lang_name = 'tg';
				break;
				case 'thai':
					$lang_name = 'th';
				break;
				case 'tigrinya':
					$lang_name = 'ti';
				break;
				case 'turkmen':
					$lang_name = 'tk';
				break;
				case 'tagalog':
					$lang_name = 'tl';
				break;
				case 'tswana':
					$lang_name = 'tn';
				break;
				case 'tonga':
					$lang_name = 'to';
				break;
				case 'turkish':
					$lang_name = 'tr';
				break;
				case 'tsonga':
					$lang_name = 'ts';
				break;
				case 'tatar':
					$lang_name = 'tt';
				break;
				case 'twi':
					$lang_name = 'tw';
				break;
				case 'tahitian':
					$lang_name = 'ty';
				break;
				case 'uighur':
					$lang_name = 'ug';
				break;
				case 'ukrainian':
					$lang_name = 'uk';
				break;
				case 'urdu':
					$lang_name = 'ur';
				break;
				case 'uzbek':
					$lang_name = 'uz';
				break;
				case 'venda':
					$lang_name = 've';
				break;
				case 'vietnamese':
					$lang_name = 'vi';
				break;
				case 'volapuk':
					$lang_name = 'vo';
				break;
				case 'walloon':
					$lang_name = 'wa';
				break;
				case 'wolof':
					$lang_name = 'wo';
				break;
				case 'xhosa':
					$lang_name = 'xh';
				break;
				case 'yiddish':
					$lang_name = 'yi';
				break;
				case 'yoruba':
					$lang_name = 'yo';
				break;
				case 'zhuang':
					$lang_name = 'za';
				break;
				case 'chinese':
					$lang_name = 'zh';
				break;
				case 'chinese_simplified':
					$lang_name = 'zh_cmn_hans';
				break;
				case 'chinese_traditional':
					$lang_name = 'zh_cmn_hant';
				break;
				case 'zulu':
					$lang_name = 'zu';
				break;
				default:
					$lang_name = (strlen($lang) > 2) ? substr($lang, 0, 2) : $lang;
				break;
			}
		return $lang_name;
	}
	
	function ucstrreplace($pattern = '%{$regex}%i', $matches = '', $string) 
	{
		/* return with no uppercase if patern not in string */
		if (strpos($string, $pattern) === false)
		{
			/* known languages */
			switch($string)
			{
				case 'aa':
					$lang_name = 'afar';
				break;
				case 'ab':
					$lang_name = 'abkhazian';
				break;
				case 'ae':
					$lang_name = 'avestan';
				break;
				case 'af':
					$lang_name = 'afrikaans';
				break;
				case 'ak':
					$lang_name = 'akan';
				break;
				case 'am':
					$lang_name = 'amharic';
				break;
				case 'an':
					$lang_name = 'aragonese';
				break;
				case 'ar':
					$lang_name = 'arabic';
				break;
				case 'as':
					$lang_name = 'assamese';
				break;
				case 'av':
					$lang_name = 'avaric';
				break;
				case 'ay':
					$lang_name = 'aymara';
				break;
				case 'az':
					$lang_name = 'azerbaijani';
				break;
				case 'ba':
					$lang_name = 'bashkir';
				break;
				case 'be':
					$lang_name = 'belarusian';
				break;
				case 'bg':
					$lang_name = 'bulgarian';
				break;
				case 'bh':
					$lang_name = 'bihari';
				break;
				case 'bi':
					$lang_name = 'bislama';
				break;
				case 'bm':
					$lang_name = 'bambara';
				break;
				case 'bn':
					$lang_name = 'bengali';
				break;
				case 'bo':
					$lang_name = 'tibetan';
				break;
				case 'br':
					$lang_name = 'breton';
				break;
				case 'bs':
					$lang_name = 'bosnian';
				break;
				case 'ca':
					$lang_name = 'catalan';
				break;
				case 'ce':
					$lang_name = 'chechen';
				break;
				case 'ch':
					$lang_name = 'chamorro';
				break;
				case 'co':
					$lang_name = 'corsican';
				break;
				case 'cr':
					$lang_name = 'cree';
				break;
				case 'cs':
					$lang_name = 'czech';
				break;
				case 'cu':
					$lang_name = 'slavonic';
				break;
				case 'cv':
					$lang_name = 'chuvash';
				break;
				case 'cy':
					$lang_name = 'welsh_cymraeg';
				break;
				case 'da':
					$lang_name = 'danish';
				break;
				case 'de':
					$lang_name = 'german';
				break;
				case 'dv':
					$lang_name = 'divehi';
				break;
				case 'dz':
					$lang_name = 'dzongkha';
				break;
				case 'ee':
					$lang_name = 'ewe';
				break;
				case 'el':
					$lang_name = 'greek';
				break;
				case 'he':
					$lang_name = 'hebrew';
				break;
				case '{LANG}':
					$lang_name = 'english';
				break;
				case 'en_us':
					$lang_name = 'english';
				break;
				case 'eo':
					$lang_name = 'esperanto';
				break;
				case 'es':
					$lang_name = 'spanish';
				break;
				case 'et':
					$lang_name = 'estonian';
				break;
				case 'eu':
					$lang_name = 'basque';
				break;
				case 'fa':
					$lang_name = 'persian';
				break;
				case 'ff':
					$lang_name = 'fulah';
				break;
				case 'fi':
					$lang_name = 'finnish';
				break;
				case 'fj':
					$lang_name = 'fijian';
				break;
				case 'fo':
					$lang_name = 'faroese';
				break;
				case 'fr':
					$lang_name = 'french';
				break;
				case 'fy':
					$lang_name = 'frisian';
				break;
				case 'ga':
					$lang_name = 'irish';
				break;
				case 'gd':
					$lang_name = 'scottish';
				break;
				case 'gl':
					$lang_name = 'galician';
				break;
				case 'gn':
					$lang_name = 'guaraní';
				break;
				case 'gu':
					$lang_name = 'gujarati';
				break;
				case 'gv':
					$lang_name = 'manx';
				break;
				case 'ha':
					$lang_name = 'hausa';
				break;
				case 'he':
					$lang_name = 'hebrew';
				break;
				case 'hi':
					$lang_name = 'hindi';
				break;
				case 'ho':
					$lang_name = 'hiri_motu';
				break;
				case 'hr':
					$lang_name = 'croatian';
				break;
				case 'ht':
					$lang_name = 'haitian';
				break;
				case 'hu':
					$lang_name = 'hungarian';
				break;
				case 'hy':
					$lang_name = 'armenian';
				break;
				case 'hz':
					$lang_name = 'herero';
				break;
				case 'ia':
					$lang_name = 'interlingua';
				break;
				case 'id':
					$lang_name = 'indonesian';
				break;
				case 'ie':
					$lang_name = 'interlingue';
				break;
				case 'ig':
					$lang_name = 'igbo';
				break;
				case 'ii':
					$lang_name = 'sichuan_yi';
				break;
				case 'ik':
					$lang_name = 'inupiaq';
				break;
				case 'io':
					$lang_name = 'ido';
				break;
				case 'is':
					$lang_name = 'icelandic';
				break;
				case 'it':
					$lang_name = 'italian';
				break;
				case 'iu':
					$lang_name = 'inuktitut';
				break;
				case 'ja':
					$lang_name = 'japanese';
				break;
				case 'jv':
					$lang_name = 'javanese';
				break;
				case 'ka':
					$lang_name = 'georgian';
				break;
				case 'kg':
					$lang_name = 'kongo';
				break;
				case 'ki':
					$lang_name = 'kikuyu';
				break;
				case 'kj':
					$lang_name = 'kwanyama';
				break;
				case 'kk':
					$lang_name = 'kazakh';
				break;
				case 'kl':
					$lang_name = 'kalaallisut';
				break;
				case 'km':
					$lang_name = 'khmer';
				break;
				case 'kn':
					$lang_name = 'kannada';
				break;
				case 'ko':
					$lang_name = 'korean';
				break;
				case 'kr':
					$lang_name = 'kanuri';
				break;
				case 'ks':
					$lang_name = 'kashmiri';
				break;
				case 'ku':
					$lang_name = 'kurdish';
				break;
				case 'kv':
					$lang_name = 'komi';
				break;
				case 'kw':
					$lang_name = 'cornish_kernewek';
				break;
				case 'ky':
					$lang_name = 'kirghiz';
				break;
				case 'la':
					$lang_name = 'latin';
				break;
				case 'lb':
					$lang_name = 'luxembourgish';
				break;
				case 'lg':
					$lang_name = 'ganda';
				break;
				case 'li':
					$lang_name = 'limburgish';
				break;
				case 'ln':
					$lang_name = 'lingala';
				break;
				case 'lo':
					$lang_name = 'lao';
				break;
				case 'lt':
					$lang_name = 'lithuanian';
				break;
				case 'lu':
					$lang_name = 'luba-katanga';
				break;
				case 'lv':
					$lang_name = 'latvian';
				break;
				case 'mg':
					$lang_name = 'malagasy';
				break;
				case 'mh':
					$lang_name = 'marshallese';
				break;
				case 'mi':
					$lang_name = 'maori';
				break;
				case 'mk':
					$lang_name = 'macedonian';
				break;
				case 'ml':
					$lang_name = 'malayalam';
				break;
				case 'mn':
					$lang_name = 'mongolian';
				break;
				case 'mo':
					$lang_name = 'moldavian';
				break;
				case 'mr':
					$lang_name = 'marathi';
				break;
				case 'ms':
					$lang_name = 'malay';
				break;
				case 'mt':
					$lang_name = 'maltese';
				break;
				case 'my':
					$lang_name = 'burmese';
				break;
				case 'na':
					$lang_name = 'nauruan';
				break;
				case 'nb':
					$lang_name = 'norwegian';
				break;
				case 'nd':
					$lang_name = 'ndebele';
				break;
				case 'ne':
					$lang_name = 'nepali';
				break;
				case 'ng':
					$lang_name = 'ndonga';
				break;
				case 'nl':
					$lang_name = 'dutch';
				break;
				case 'nn':
					$lang_name = 'norwegian_nynorsk';
				break;
				case 'no':
					$lang_name = 'norwegian';
				break;
				case 'nr':
					$lang_name = 'southern_ndebele';
				break;
				case 'nv':
					$lang_name = 'navajo';
				break;
				case 'ny':
					$lang_name = 'chichewa';
				break;
				case 'oc':
					$lang_name = 'occitan';
				break;
				case 'oj':
					$lang_name = 'ojibwa';
				break;
				case 'om':
					$lang_name = 'oromo';
				break;
				case 'or':
					$lang_name = 'oriya';
				break;
				case 'os':
					$lang_name = 'ossetian';
				break;
				case 'pa':
					$lang_name = 'panjabi';
				break;
				case 'pi':
					$lang_name = 'pali';
				break;
				case 'pl':
					$lang_name = 'polish';
				break;
				case 'ps':
					$lang_name = 'pashto';
				break;
				case 'pt':
					$lang_name = 'portuguese';
				break;
				case 'pt_br':
					$lang_name = 'portuguese_brasil';
				break;
				case 'qu':
					$lang_name = 'quechua';
				break;
				case 'rm':
					$lang_name = 'romansh';
				break;
				case 'rn':
					$lang_name = 'kirundi';
				break;
				case 'ro':
					$lang_name = 'romanian';
				break;
				case 'ru':
					$lang_name = 'russian';
				break;
				case 'rw':
					$lang_name = 'kinyarwanda';
				break;
				case 'sa':
					$lang_name = 'sanskrit';
				break;
				case 'sc':
					$lang_name = 'sardinian';
				break;
				case 'sd':
					$lang_name = 'sindhi';
				break;
				case 'se':
					$lang_name = 'northern_sami';
				break;
				case 'sg':
					$lang_name = 'sango';
				break;
				case 'sh':
					$lang_name = 'serbo-croatian';
				break;
				case 'si':
					$lang_name = 'sinhala';
				break;
				case 'sk':
					$lang_name = 'slovak';
				break;
				case 'sl':
					$lang_name = 'slovenian';
				break;
				case 'sm':
					$lang_name = 'samoan';
				break;
				case 'sn':
					$lang_name = 'shona';
				break;
				case 'so':
					$lang_name = 'somali';
				break;
				case 'sq':
					$lang_name = 'albanian';
				break;
				case 'sr':
					$lang_name = 'serbian';
				break;
				case 'ss':
					$lang_name = 'swati';
				break;
				case 'st':
					$lang_name = 'sotho';
				break;
				case 'su':
					$lang_name = 'sundanese';
				break;
				case 'sv':
					$lang_name = 'swedish';
				break;
				case 'sw':
					$lang_name = 'swahili';
				break;
				case 'ta':
					$lang_name = 'tamil';
				break;
				case 'te':
					$lang_name = 'telugu';
				break;
				case 'tg':
					$lang_name = 'tajik';
				break;
				case 'th':
					$lang_name = 'thai';
				break;
				case 'ti':
					$lang_name = 'tigrinya';
				break;
				case 'tk':
					$lang_name = 'turkmen';
				break;
				case 'tl':
					$lang_name = 'tagalog';
				break;
				case 'tn':
					$lang_name = 'tswana';
				break;
				case 'to':
					$lang_name = 'tonga';
				break;
				case 'tr':
					$lang_name = 'turkish';
				break;
				case 'ts':
					$lang_name = 'tsonga';
				break;
				case 'tt':
					$lang_name = 'tatar';
				break;
				case 'tw':
					$lang_name = 'twi';
				break;
				case 'ty':
					$lang_name = 'tahitian';
				break;
				case 'ug':
					$lang_name = 'uighur';
				break;
				case 'uk':
					$lang_name = 'ukrainian';
				break;
				case 'ur':
					$lang_name = 'urdu';
				break;
				case 'uz':
					$lang_name = 'uzbek';
				break;
				case 've':
					$lang_name = 'venda';
				break;
				case 'vi':
					$lang_name = 'vietnamese';
				break;
				case 'vo':
					$lang_name = 'volapuk';
				break;
				case 'wa':
					$lang_name = 'walloon';
				break;
				case 'wo':
					$lang_name = 'wolof';
				break;
				case 'xh':
					$lang_name = 'xhosa';
				break;
				case 'yi':
					$lang_name = 'yiddish';
				break;
				case 'yo':
					$lang_name = 'yoruba';
				break;
				case 'za':
					$lang_name = 'zhuang';
				break;
				case 'zh':
					$lang_name = 'chinese';
				break;
				case 'zh_cmn_hans':
					$lang_name = 'chinese_simplified';
				break;
				case 'zh_cmn_hant':
					$lang_name = 'chinese_traditional';
				break;
				case 'zu':
					$lang_name = 'zulu';
				break;
				default:
					$lang_name = (strlen($string) > 2) ? ucfirst(str_replace($pattern, '', $string)) : $string;
				break;
			}		
			return ucwords(str_replace(array(" ","-","_"), ' ', $lang_name));	
		}
		return ucwords(str_replace(array(" ","-","_"), ' ', str_replace($pattern, '', $string)));
	}
	
	/* replacement for eregi($pattern, $string); outputs 0 or 1*/
	function trisstr($pattern = '%{$regex}%i', $string, $matches = '') 
	{         
		return preg_match('/' . $pattern . '/i', $string, $matches);	
	}
	
	/**
	 * Enter description here...
	 *
	 * @access private
	 * @param unknown_type $lang_mode
	 */	
	function _load_lang($path, $filename, $require = true)
	{
		$lang = array();
		
		$board_config = $this->config;		
		$php_ext = $this->php_ext;
		
		// Now only the root for mxp blocks
		$user_path = $path . 'language/lang_' . $this->data['user_lang'] . '/' . $filename . '.' . $php_ext;
		$board_path = $path . 'language/lang_' . $board_config['default_lang'] . '/' . $filename . '.' . $php_ext;
		$default_path = $path . 'language/lang_english/' . $filename . '.' . $php_ext;
				
		// phpBB		
		if (($path != 'phpbb2') && ($path != 'phpbb3'))	
		{
			$phpbb_user_path = $path . 'language/' . $this->data['user_lang'] . '/' . $filename . '.' . $php_ext;
			$phpbb_board_path = $path . 'language/' . $board_config['default_lang'] . '/' . $filename . '.' . $php_ext;
			$phpbb_default_path = $path . 'language/en/' . $filename . '.' . $php_ext;		
		}		
		
		// Shared phpBB2		
		if ($path = 'phpbb2')	
		{
			$phpbb2_shared_path = $this->phpbb_root_path . '';
			
			$phpbb_user_path = $phpbb2_shared_path . 'language/lang_' . $this->data['user_lang'] . '/' . $filename . '.' . $php_ext;
			$phpbb_board_path = $phpbb2_shared_path . 'language/lang_' . $board_config['default_lang'] . '/' . $filename . '.' . $php_ext;
			$phpbb_default_path = $phpbb2_shared_path . 'language/lang_english/' . $filename . '.' . $php_ext;			
		}							
		
		// Shared phpBB3		
		if ($path = 'phpbb3')	
		{
			$phpbb3_shared_path = $this->phpbb_root_path . '';

			$phpbb_user_path = $phpbb3_shared_path . 'language/lang_' . $this->data['user_lang'] . '/' . $filename . '.' . $php_ext;
			$phpbb_board_path = $phpbb3_shared_path . 'language/lang_' . $board_config['default_lang'] . '/' . $filename . '.' . $php_ext;
			$phpbb_default_path = $phpbb3_shared_path . 'language/lang_english/' . $filename . '.' . $php_ext;				
		}	
		
		if (file_exists($user_path))
		{
			include_once($user_path);
		}
		else if ($require)
		{
			if (file_exists($board_path))
			{
				include_once($board_path);
			}
			else if (file_exists($default_path))
			{
				include_once($default_path);
			}
		}
		else if (is_file($phpbb_user_path))
		{
			include_once($phpbb_user_path);
		}
		if ((@include $phpbb_user_path) === false)
		{
			if ($require)
			{
				if ((@include $phpbb_board_path) === false)
				{
					if ((@include $phpbb_default_path) === false)
					{
						continue;
					}
				}			
			}
		}			
	
		if (count($lang) == 0)
		{
			// If the language entry is an empty array, we just return the language key $this->lang = $this->lang;	
			// The language file is not exist throw new language_file_not_found(
			print_r('Language file ' . $path . '|' . $filename . '.' . $php_ext . '|' . ($require ? $phpbb_user_path : $phpbb_board_path) . ' couldn\'t be opened.');			
		}					
		else
		{
			// If the language entry is not an empty array, we merge the language keys
			$this->lang = array_merge($this->lang, $lang);		
		}		
	}	
	
	/**
	 * Loads common language files
	 */
	protected function load_common_language_files()
	{
		if (!$this->common_language_files_loaded)
		{
		
			/*
			* Load vanilla phpBB2 lang files for old modules if is possible
			*/
			$shared_lang_path = $this->phpbb_root_path . 'language/';
			
			// AdminCP
			if (defined('IN_ADMIN'))
			{
				// Core
				$this->_load_lang($this->phpbb_root_path, 'lang_admin');
			}	

			// Core Main Translation after shared phpBB keys so we can overwrite some settings
			$this->_load_lang($this->phpbb_root_path, 'lang_main');
			//
			// Load backend specific lang defs.
			//
			//$this->user->setup();	
			$this->common_language_files_loaded = true;
		}
	}	
	/**
	 * Returns the raw value associated to a language key or the language key no translation is available.
	 * No parameter substitution is performed, can be a string or an array.
	 *
	 * @param string|array	$key	Language key
	 *
	 * @return array|string
	 */
	public function lang_raw($key)
	{
		// Load common language files if they not loaded yet
		if (!$this->common_language_files_loaded)
		{
			$this->load_common_language_files();							
		}
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
		return $lang;
	}

	/**
	 * Act like lang() but takes a key and an array of parameters instead of using variadic
	 *
	 * @param string|array	$key	Language key
	 * @param array			$args	Parameters
	 *
	 * @return string
	 */
	public function lang_array($key, $args = array())
	{
		$lang = $this->lang_raw($key);
		
		if ($lang === $key)
		{
			return $key;
		}
		// If the language entry is a string, we simply mimic sprintf() behaviour
		if (is_string($lang))
		{
			if (count($args) === 0)
			{
				return $lang;
			}
			// Replace key with language entry and simply pass along...
			return vsprintf($lang, $args);
		}
		else if (count($lang) == 0)
		{
			// If the language entry is an empty array, we just return the language key
			return $key;
		}
		// It is an array... now handle different nullar/singular/plural forms
		$key_found = false;

		// We now get the first number passed and will select the key based upon this number
		for ($i = 0, $num_args = count($args); $i < $num_args; $i++)
		{
			if (is_int($args[$i]) || is_float($args[$i]))
			{
				if ($args[$i] == 0 && isset($lang[0]))
				{
					// We allow each translation using plural forms to specify a version for the case of 0 things,
					// so that "0 users" may be displayed as "No users".
					$key_found = 0;
					break;
				}
				else
				{
					$use_plural_form = $this->get_plural_form($args[$i]);
					if (isset($lang[$use_plural_form]))
					{
						// The key we should use exists, so we use it.
						$key_found = $use_plural_form;
					}
					else
					{
						// If the key we need to use does not exist, we fall back to the previous one.
						$numbers = array_keys($lang);

						foreach ($numbers as $num)
						{
							if ($num > $use_plural_form)
							{
								break;
							}
							$key_found = $num;
						}
					}
					break;
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
		return vsprintf($lang[$key_found], $args);
	}	
	/**
	 * Determine which plural form we should use.
	 *
	 * For some languages this is not as simple as for English.
	 *
	 * @param int|float		$number		The number we want to get the plural case for. Float numbers are floored.
	 * @param int|bool		$force_rule	False to use the plural rule of the language package
	 *									or an integer to force a certain plural rule
	 *
	 * @return int	The plural-case we need to use for the number plural-rule combination
	 *
	 * @throws \phpbb\language\exception\invalid_plural_rule_exception	When $force_rule has an invalid value
	 */
	public function get_plural_form($number, $force_rule = false)
	{
		$number			= (int) $number;
		$plural_rule	= ($force_rule !== false) ? $force_rule : ((isset($this->lang['PLURAL_RULE'])) ? $this->lang['PLURAL_RULE'] : 1);

		if ($plural_rule > 15 || $plural_rule < 0)
		{
			throw new invalid_plural_rule_exception('INVALID_PLURAL_RULE', array(
				'plural_rule' => $plural_rule,
			));
		}

		/**
		 * The following plural rules are based on a list published by the Mozilla Developer Network
		 * https://developer.mozilla.org/en/Localization_and_Plurals
		 */
		switch ($plural_rule)
		{
			case 0:
				/**
				 * Families: Asian (Chinese, Japanese, Korean, Vietnamese), Persian, Turkic/Altaic (Turkish), Thai, Lao
				 * 1 - everything: 0, 1, 2, ...
				 */
			return 1;
			case 1:
				/**
				 * Families: Germanic (Danish, Dutch, English, Faroese, Frisian, German, Norwegian, Swedish), Finno-Ugric (Estonian, Finnish, Hungarian), Language isolate (Basque), Latin/Greek (Greek), Semitic (Hebrew), Romanic (Italian, Portuguese, Spanish, Catalan)
				 * 1 - 1
				 * 2 - everything else: 0, 2, 3, ...
				 */
			return ($number === 1) ? 1 : 2;
			case 2:
				/**
				 * Families: Romanic (French, Brazilian Portuguese)
				 * 1 - 0, 1
				 * 2 - everything else: 2, 3, ...
				 */
			return (($number === 0) || ($number === 1)) ? 1 : 2;
			case 3:
				/**
				 * Families: Baltic (Latvian)
				 * 1 - 0
				 * 2 - ends in 1, not 11: 1, 21, ... 101, 121, ...
				 * 3 - everything else: 2, 3, ... 10, 11, 12, ... 20, 22, ...
				 */
			return ($number === 0) ? 1 : ((($number % 10 === 1) && ($number % 100 != 11)) ? 2 : 3);
			case 4:
				/**
				 * Families: Celtic (Scottish Gaelic)
				 * 1 - is 1 or 11: 1, 11
				 * 2 - is 2 or 12: 2, 12
				 * 3 - others between 3 and 19: 3, 4, ... 10, 13, ... 18, 19
				 * 4 - everything else: 0, 20, 21, ...
				 */
			return ($number === 1 || $number === 11) ? 1 : (($number === 2 || $number === 12) ? 2 : (($number >= 3 && $number <= 19) ? 3 : 4));
			case 5:
				/**
				 * Families: Romanic (Romanian)
				 * 1 - 1
				 * 2 - is 0 or ends in 01-19: 0, 2, 3, ... 19, 101, 102, ... 119, 201, ...
				 * 3 - everything else: 20, 21, ...
				 */
			return ($number === 1) ? 1 : ((($number === 0) || (($number % 100 > 0) && ($number % 100 < 20))) ? 2 : 3);
			case 6:
				/**
				 * Families: Baltic (Lithuanian)
				 * 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, ...
				 * 2 - ends in 0 or ends in 10-20: 0, 10, 11, 12, ... 19, 20, 30, 40, ...
				 * 3 - everything else: 2, 3, ... 8, 9, 22, 23, ... 29, 32, 33, ...
				 */
			return (($number % 10 === 1) && ($number % 100 != 11)) ? 1 : ((($number % 10 < 2) || (($number % 100 >= 10) && ($number % 100 < 20))) ? 2 : 3);
			case 7:
				/**
				 * Families: Slavic (Croatian, Serbian, Russian, Ukrainian)
				 * 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, ...
				 * 2 - ends in 2-4, not 12-14: 2, 3, 4, 22, 23, 24, 32, ...
				 * 3 - everything else: 0, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 25, 26, ...
				 */
			return (($number % 10 === 1) && ($number % 100 != 11)) ? 1 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 10) || ($number % 100 >= 20))) ? 2 : 3);
			case 8:
				/**
				 * Families: Slavic (Slovak, Czech)
				 * 1 - 1
				 * 2 - 2, 3, 4
				 * 3 - everything else: 0, 5, 6, 7, ...
				 */
			return ($number === 1) ? 1 : ((($number >= 2) && ($number <= 4)) ? 2 : 3);
			case 9:
				/**
				 * Families: Slavic (Polish)
				 * 1 - 1
				 * 2 - ends in 2-4, not 12-14: 2, 3, 4, 22, 23, 24, 32, ... 104, 122, ...
				 * 3 - everything else: 0, 5, 6, ... 11, 12, 13, 14, 15, ... 20, 21, 25, ...
				 */
			return ($number === 1) ? 1 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 12) || ($number % 100 > 14))) ? 2 : 3);
			case 10:
				/**
				 * Families: Slavic (Slovenian, Sorbian)
				 * 1 - ends in 01: 1, 101, 201, ...
				 * 2 - ends in 02: 2, 102, 202, ...
				 * 3 - ends in 03-04: 3, 4, 103, 104, 203, 204, ...
				 * 4 - everything else: 0, 5, 6, 7, 8, 9, 10, 11, ...
				 */
			return ($number % 100 === 1) ? 1 : (($number % 100 === 2) ? 2 : ((($number % 100 === 3) || ($number % 100 === 4)) ? 3 : 4));
			case 11:
				/**
				 * Families: Celtic (Irish Gaeilge)
				 * 1 - 1
				 * 2 - 2
				 * 3 - is 3-6: 3, 4, 5, 6
				 * 4 - is 7-10: 7, 8, 9, 10
				 * 5 - everything else: 0, 11, 12, ...
				 */
			return ($number === 1) ? 1 : (($number === 2) ? 2 : (($number >= 3 && $number <= 6) ? 3 : (($number >= 7 && $number <= 10) ? 4 : 5)));
			case 12:
				/**
				 * Families: Semitic (Arabic)
				 * 1 - 1
				 * 2 - 2
				 * 3 - ends in 03-10: 3, 4, ... 10, 103, 104, ... 110, 203, 204, ...
				 * 4 - ends in 11-99: 11, ... 99, 111, 112, ...
				 * 5 - everything else: 100, 101, 102, 200, 201, 202, ...
				 * 6 - 0
				 */
			return ($number === 1) ? 1 : (($number === 2) ? 2 : ((($number % 100 >= 3) && ($number % 100 <= 10)) ? 3 : ((($number % 100 >= 11) && ($number % 100 <= 99)) ? 4 : (($number != 0) ? 5 : 6))));
			case 13:
				/**
				 * Families: Semitic (Maltese)
				 * 1 - 1
				 * 2 - is 0 or ends in 01-10: 0, 2, 3, ... 9, 10, 101, 102, ...
				 * 3 - ends in 11-19: 11, 12, ... 18, 19, 111, 112, ...
				 * 4 - everything else: 20, 21, ...
				 */
			return ($number === 1) ? 1 : ((($number === 0) || (($number % 100 > 1) && ($number % 100 < 11))) ? 2 : ((($number % 100 > 10) && ($number % 100 < 20)) ? 3 : 4));
			case 14:
				/**
				 * Families: Slavic (Macedonian)
				 * 1 - ends in 1: 1, 11, 21, ...
				 * 2 - ends in 2: 2, 12, 22, ...
				 * 3 - everything else: 0, 3, 4, ... 10, 13, 14, ... 20, 23, ...
				 */
			return ($number % 10 === 1) ? 1 : (($number % 10 === 2) ? 2 : 3);
			case 15:
				/**
				 * Families: Icelandic
				 * 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, 131, ...
				 * 2 - everything else: 0, 2, 3, ... 10, 11, 12, ... 20, 22, ...
				 */
			return (($number % 10 === 1) && ($number % 100 != 11)) ? 1 : 2;
		}
	}
	/**
	 * Add Language Items
	 *
	 * Examples:
	 * <code>
	 * $component = array('posting');
	 * $component = array('posting', 'viewtopic')
	 * $component = 'posting'
	 * </code>
	 *
	 * @param string|array	$component		The name of the language component to load
	 * @param string|null	$extension_name	Name of the extension to load component from, or null for core file
	 */
	public function add_lang($component, $extension_name = null)
	{
		// Load common language files if they not loaded yet
		// This needs to be here to correctly merge language arrays
		if (!$this->common_language_files_loaded)
		{
			$this->load_common_language_files();
		}

		if (!is_array($component))
		{
			if (!is_null($extension_name))
			{
				$this->load_extension($extension_name, $component);
			}
			else
			{
				$this->load_core_file($component);
			}
		}
		else
		{
			foreach ($component as $lang_file)
			{
				$this->add_lang($lang_file, $extension_name);
			}
		}
	}
	/**
	 * @param $key array|string		The language key we want to know more about. Can be string or array.
	 *
	 * @return bool		Returns whether the language key is set.
	 */
	public function is_set($key)
	{
		// Load common language files if they not loaded yet
		if (!$this->common_language_files_loaded)
		{
			$this->load_common_language_files();
		}

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

		return isset($lang);
	}

	/**
	 * Advanced language substitution
	 *
	 * Function to mimic sprintf() with the possibility of using phpBB's language system to substitute nullar/singular/plural forms.
	 * Params are the language key and the parameters to be substituted.
	 * This function/functionality is inspired by SHS` and Ashe.
	 *
	 * Example call: <samp>$user->lang('NUM_POSTS_IN_QUEUE', 1);</samp>
	 *
	 * If the first parameter is an array, the elements are used as keys and subkeys to get the language entry:
	 * Example: <samp>$user->lang(array('datetime', 'AGO'), 1)</samp> uses $user->lang['datetime']['AGO'] as language entry.
	 *
	 * @return string	Return localized string or the language key if the translation is not available
	 */
	public function lang()
	{
		$args = func_get_args();
		$key = array_shift($args);

		return $this->lang_array($key, $args);
	}
	/**
	 * BC function for loading language files
	 *
	 * @deprecated 3.2.0-dev (To be removed: 4.0.0)
	 */
	private function set_lang($lang_set, $use_help, $ext_name)
	{
		if (empty($ext_name))
		{
			$ext_name = null;
		}
		/**/
		if ($use_help && strpos($lang_set, '/') !== false)
		{
			$component = dirname($lang_set) . '/help_' . basename($lang_set);
			if ($component[0] === '/')
			{
				$component = substr($component, 1);
			}
		}
		else
		{
			$component = (($use_help) ? 'help_' : '') . $lang_set;
		}
		/**/
		$this->add_lang($component, $ext_name);
		/**/
	}

	/**
	* Add Language Items from an extension - use_db and use_help are assigned where needed (only use them to force inclusion)
	*
	* @param string $ext_name The extension to load language from, or empty for core files
	* @param mixed $lang_set specifies the language entries to include
	* @param bool $use_db internal variable for recursion, do not use
	* @param bool $use_help internal variable for recursion, do not use
	*
	* Note: $use_db and $use_help should be removed. Kept for BC purposes.
	*
	* @deprecated: 3.2.0-dev (To be removed: 4.0.0)
	*/
	function add_lang_ext($ext_name, $lang_set, $use_db = false, $use_help = false)
	{
		if ($ext_name === '/')
		{
			$ext_name = '';
		}
		$this->add_lang($lang_set, $use_db, $use_help, $ext_name);
	}	
}
/**
* Permission/Auth class for phpBB2 forums
*/
class auth_base
{
	function auth($type, $forum_id, $userdata, $f_access = '')
	{
		global $user, $db, $lang;

		switch( $type )
		{
			case AUTH_ALL:
				$a_sql = 'a.auth_view, a.auth_read, a.auth_post, a.auth_reply, a.auth_edit, a.auth_delete, a.auth_sticky, a.auth_announce, a.auth_vote, a.auth_pollcreate';
				$auth_fields = array('auth_view', 'auth_read', 'auth_post', 'auth_reply', 'auth_edit', 'auth_delete', 'auth_sticky', 'auth_announce', 'auth_vote', 'auth_pollcreate');
			break;

			case AUTH_VIEW:
				$a_sql = 'a.auth_view';
				$auth_fields = array('auth_view');
			break;

			case AUTH_READ:
				$a_sql = 'a.auth_read';
				$auth_fields = array('auth_read');
			break;
			case AUTH_POST:
				$a_sql = 'a.auth_post';
				$auth_fields = array('auth_post');
			break;
			case AUTH_REPLY:
				$a_sql = 'a.auth_reply';
				$auth_fields = array('auth_reply');
			break;
			case AUTH_EDIT:
				$a_sql = 'a.auth_edit';
				$auth_fields = array('auth_edit');
				break;
			case AUTH_DELETE:
				$a_sql = 'a.auth_delete';
				$auth_fields = array('auth_delete');
			break;

			case AUTH_ANNOUNCE:
				$a_sql = 'a.auth_announce';
				$auth_fields = array('auth_announce');
			break;
			case AUTH_STICKY:
				$a_sql = 'a.auth_sticky';
				$auth_fields = array('auth_sticky');
			break;

			case AUTH_POLLCREATE:
				$a_sql = 'a.auth_pollcreate';
				$auth_fields = array('auth_pollcreate');
				break;
			case AUTH_VOTE:
				$a_sql = 'a.auth_vote';
				$auth_fields = array('auth_vote');
			break;
			case AUTH_ATTACH:
			break;

			default:
			break;
		}

		//
		// If f_access has been passed, or auth is needed to return an array of forums
		// then we need to pull the auth information on the given forum (or all forums)
		//
		if ( empty($f_access) )
		{
			$forum_match_sql = ( $forum_id != AUTH_LIST_ALL ) ? "WHERE a.forum_id = $forum_id" : '';

			$sql = "SELECT a.forum_id, $a_sql
				FROM " . FORUMS_TABLE . " a
				$forum_match_sql";
			if ( !($result = $db->sql_query($sql)) )
			{
				//message_die(GENERAL_ERROR, 'Failed obtaining forum access control lists', '', __LINE__, __FILE__, $sql);
			}

			$sql_fetchrow = ( $forum_id != AUTH_LIST_ALL ) ? 'sql_fetchrow' : 'sql_fetchrowset';

			if ( !($f_access = $db->$sql_fetchrow($result)) )
			{
				$db->sql_freeresult($result);
				return array();
			}
			$db->sql_freeresult($result);
		}

		//
		// If the user isn't logged on then all we need do is check if the forum
		// has the type set to ALL, if yes they are good to go, if not then they
		// are denied access
		//
		$u_access = array();
		if ( $userdata['session_logged_in'] )
		{
			$forum_match_sql = ( $forum_id != AUTH_LIST_ALL ) ? "AND a.forum_id = $forum_id" : '';

			$sql = "SELECT a.forum_id, $a_sql, a.auth_mod
				FROM " . AUTH_ACCESS_TABLE . " a, " . USER_GROUP_TABLE . " ug
				WHERE ug.user_id = ".$userdata['user_id']. "
					AND ug.user_pending = 0
					AND a.group_id = ug.group_id
					$forum_match_sql";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Failed obtaining forum access control lists', '', __LINE__, __FILE__, $sql);
			}

			if ( $row = $db->sql_fetchrow($result) )
			{
				do
				{
					if ( $forum_id != AUTH_LIST_ALL)
					{
						$u_access[] = $row;
					}
					else
					{
						$u_access[$row['forum_id']][] = $row;
					}
				}
				while( $row = $db->sql_fetchrow($result) );
			}
			$db->sql_freeresult($result);
		}

		$is_admin = ($userdata['user_level'] == ADMIN && $userdata['session_logged_in']) ? TRUE : 0;

		$auth_user = array();
		
		for($i = 0; $i < count($auth_fields); $i++)
		{
			$key = $auth_fields[$i];
			
			//
			// If the user is logged on and the forum type is either ALL or REG then the user has access
			//
			// If the type if ACL, MOD or ADMIN then we need to see if the user has specific permissions
			// to do whatever it is they want to do ... to do this we pull relevant information for the
			// user (and any groups they belong to)
			//
			// Now we compare the users access level against the forums. We assume here that a moderator
			// and admin automatically have access to an ACL forum, similarly we assume admins meet an
			// auth requirement of MOD
			//
			if ( $forum_id != AUTH_LIST_ALL )
			{
				$value = $f_access[$key];

				switch($value)
				{
					case AUTH_ALL:
						$auth_user[$key] = TRUE;
						$auth_user[$key . '_type'] = $user->lang('Auth_Anonymous_Users');
					break;

					case AUTH_REG:
						$auth_user[$key] = ( $userdata['session_logged_in'] ) ? TRUE : 0;
						$auth_user[$key . '_type'] = $lang['Auth_Registered_Users'];
					break;

					case AUTH_ACL:
						$auth_user[$key] = ( $userdata['session_logged_in'] ) ? $this->auth_check_user(AUTH_ACL, $key, $u_access, $is_admin) : 0;
						$auth_user[$key . '_type'] = $lang['Auth_Users_granted_access'];
					break;

					case AUTH_MOD:
						$auth_user[$key] = ( $userdata['session_logged_in'] ) ? $this->auth_check_user(AUTH_MOD, 'auth_mod', $u_access, $is_admin) : 0;
						$auth_user[$key . '_type'] = $lang['Auth_Moderators'];
					break;

					case AUTH_ADMIN:
						$auth_user[$key] = $is_admin;
						$auth_user[$key . '_type'] = $lang['Auth_Administrators'];
					break;

					default:
						$auth_user[$key] = 0;
					break;
				}
			}
			else
			{
				for($k = 0; $k < count($f_access); $k++)
				{
					$value = $f_access[$k][$key];
					$f_forum_id = $f_access[$k]['forum_id'];
					$u_access[$f_forum_id] = isset($u_access[$f_forum_id]) ? $u_access[$f_forum_id] : array();

					switch( $value )
					{
						case AUTH_ALL:
							$auth_user[$f_forum_id][$key] = TRUE;
							$auth_user[$f_forum_id][$key . '_type'] = $user->lang('Auth_Anonymous_Users');
						break;

						case AUTH_REG:
							$auth_user[$f_forum_id][$key] = ( $userdata['session_logged_in'] ) ? TRUE : 0;
							$auth_user[$f_forum_id][$key . '_type'] = $lang['Auth_Registered_Users'];
						break;

						case AUTH_ACL:
							$auth_user[$f_forum_id][$key] = ( $userdata['session_logged_in'] ) ? $this->auth_check_user(AUTH_ACL, $key, $u_access[$f_forum_id], $is_admin) : 0;
							$auth_user[$f_forum_id][$key . '_type'] = $lang['Auth_Users_granted_access'];
						break;

						case AUTH_MOD:
							$auth_user[$f_forum_id][$key] = ( $userdata['session_logged_in'] ) ? $this->auth_check_user(AUTH_MOD, 'auth_mod', $u_access[$f_forum_id], $is_admin) : 0;
							$auth_user[$f_forum_id][$key . '_type'] = $lang['Auth_Moderators'];
						break;

						case AUTH_ADMIN:
							$auth_user[$f_forum_id][$key] = $is_admin;
							$auth_user[$f_forum_id][$key . '_type'] = $lang['Auth_Administrators'];
						break;

						default:
							$auth_user[$f_forum_id][$key] = 0;
						break;
					}
				}
			}
		}

		//
		// Is user a moderator?
		//
		if ( $forum_id != AUTH_LIST_ALL )
		{
			$auth_user['auth_mod'] = ( $userdata['session_logged_in'] ) ? $this->auth_check_user(AUTH_MOD, 'auth_mod', $u_access, $is_admin) : 0;
		}
		else
		{
			for($k = 0; $k < count($f_access); $k++)
			{
				$f_forum_id = $f_access[$k]['forum_id'];
				$u_access[$f_forum_id] = isset($u_access[$f_forum_id]) ? $u_access[$f_forum_id] : array();

				$auth_user[$f_forum_id]['auth_mod'] = ( $userdata['session_logged_in'] ) ? $this->auth_check_user(AUTH_MOD, 'auth_mod', $u_access[$f_forum_id], $is_admin) : 0;
			}
		}

		return $auth_user;
	}

	function auth_check_user($type, $key, $u_access, $is_admin)
	{
		$auth_user = 0;

		if ( count($u_access) )
		{
			for($j = 0; $j < count($u_access); $j++)
			{
				$result = 0;
				switch($type)
				{
					case AUTH_ACL:
						$result = $u_access[$j][$key];

					case AUTH_MOD:
						$result = $result || $u_access[$j]['auth_mod'];

					case AUTH_ADMIN:
						$result = $result || $is_admin;
					break;
				}

				$auth_user = $auth_user || $result;
			}
		}
		else
		{
			$auth_user = $is_admin;
		}

		return $auth_user;
	}
}


/**
* Permission/Auth class
* User Levels <- Do not change the values of USER or ADMIN
* /
define('DELETED', -1);
define('ANONYMOUS', -1);

define('USER', 0);
define('ADMIN', 1);
define('MOD', 2);
/** **/
@define('USER_FOUNDER', 3);

// ACL
define('ACL_NEVER', 0);
define('ACL_YES', 1);
define('ACL_NO', -1);

class auth extends auth_base
{
	var $acl = array();
	var $cache = array();
	var $acl_options = array();
	var $acl_forum_ids = false;
	
	/**
	* Init permissions
	*/
	function acl(&$userdata)
	{
		global $db, $cache;

		$this->acl = $this->cache = $this->acl_options = array();
		$this->acl_forum_ids = false;

		if (($this->acl_options = $cache->get('_acl_options')) === false)
		{
			$this->acl_options = array ( 
				'local' => array ( 
					'f_' => 0, 
					'f_announce' => 1, 
					'f_announce_global' => 2, 
					'f_attach' => 3, 
					'f_bbcode' => 4, 
					'f_bump' => 5, 
					'f_delete' => 6, 
					'f_download' => 7, 
					'f_edit' => 8, 
					'f_email' => 9, 
					'f_flash' => 10, 
					'f_icons' => 11, 
					'f_ignoreflood' => 12, 
					'f_img' => 13, 
					'f_list' => 14, 
					'f_list_topics' => 15, 
					'f_noapprove' => 16, 
					'f_poll' => 17, 
					'f_post' => 18, 
					'f_postcount' => 19, 
					'f_print' => 20, 
					'f_read' => 21, 
					'f_reply' => 22, 
					'f_report' => 23, 
					'f_search' => 24, 
					'f_sigs' => 25, 
					'f_smilies' => 26, 
					'f_sticky' => 27, 
					'f_subscribe' => 28, 
					'f_user_lock' => 29, 
					'f_vote' => 30, 
					'f_votechg' => 31, 
					'f_softdelete' => 32, 
					'm_' => 33, 
					'm_approve' => 34, 
					'm_chgposter' => 35, 
					'm_delete' => 36, 
					'm_edit' => 37, 
					'm_info' => 38, 
					'm_lock' => 39, 
					'm_merge' => 40, 
					'm_move' => 41, 
					'm_report' => 42, 
					'm_split' => 43, 
					'm_softdelete' => 44 
				), 
				'id' => array ( 
					'f_' => 1, 
					'f_announce' => 2, 
					'f_announce_global' => 3, 
					'f_attach' => 4, 
					'f_bbcode' => 5, 
					'f_bump' => 6, 
					'f_delete' => 7, 
					'f_download' => 8, 
					'f_edit' => 9, 
					'f_email' => 10, 
					'f_flash' => 11, 
					'f_icons' => 12, 
					'f_ignoreflood' => 13, 
					'f_img' => 14, 
					'f_list' => 15, 
					'f_list_topics' => 16, 
					'f_noapprove' => 17, 
					'f_poll' => 18, 
					'f_post' => 19, 
					'f_postcount' => 20, 
					'f_print' => 21, 
					'f_read' => 22, 
					'f_reply' => 23, 
					'f_report' => 24, 
					'f_search' => 25, 
					'f_sigs' => 26, 
					'f_smilies' => 27, 
					'f_sticky' => 28, 
					'f_subscribe' => 29, 
					'f_user_lock' => 30, 
					'f_vote' => 31, 
					'f_votechg' => 32, 
					'f_softdelete' => 33, 
					'm_' => 34, 
					'm_approve' => 35, 
					'm_chgposter' => 36, 
					'm_delete' => 37, 
					'm_edit' => 38, 
					'm_info' => 39, 
					'm_lock' => 40, 
					'm_merge' => 41, 
					'm_move' => 42, 
					'm_report' => 43, 
					'm_split' => 44, 
					'm_softdelete' => 45, 
					'm_ban' => 46, 
					'm_pm_report' => 47, 
					'm_warn' => 48, 
					'a_' => 49, 
					'a_aauth' => 50, 
					'a_attach' => 51, 
					'a_authgroups' => 52, 
					'a_authusers' => 53, 
					'a_backup' => 54, 
					'a_ban' => 55, 
					'a_bbcode' => 56, 
					'a_board' => 57, 
					'a_bots' => 58, 
					'a_clearlogs' => 59, 
					'a_email' => 60, 
					'a_extensions' => 61, 
					'a_fauth' => 62, 
					'a_forum' => 63, 
					'a_forumadd' => 64, 
					'a_forumdel' => 65, 
					'a_group' => 66, 
					'a_groupadd' => 67, 
					'a_groupdel' => 68, 
					'a_icons' => 69, 
					'a_jabber' => 70, 
					'a_language' => 71, 
					'a_mauth' => 72, 
					'a_modules' => 73, 
					'a_names' => 74, 
					'a_phpinfo' => 75, 
					'a_profile' => 76, 
					'a_prune' => 77, 
					'a_ranks' => 78, 
					'a_reasons' => 79, 
					'a_roles' => 80, 
					'a_search' => 81, 
					'a_server' => 82, 
					'a_storage' => 83, 
					'a_styles' => 84, 
					'a_switchperm' => 85, 
					'a_uauth' => 86, 
					'a_user' => 87, 
					'a_userdel' => 88, 
					'a_viewauth' => 89, 
					'a_viewlogs' => 90, 
					'a_words' => 91,
					'u_' => 92,
					'u_attach' => 93,
					'u_chgavatar' => 94,
					'u_chgcensors' => 95,
					'u_chgemail' => 96,
					'u_chggrp' => 97,
					'u_chgname' => 98,
					'u_chgpasswd' => 99,
					'u_chgprofileinfo' => 100,
					'u_download' => 101,
					'u_hideonline' => 102,
					'u_ignoreflood' => 103,
					'u_masspm' => 104,
					'u_masspm_group' => 105,
					'u_pm_attach' => 106,
					'u_pm_bbcode' => 107,
					'u_pm_delete' => 108,
					'u_pm_download' => 109,
					'u_pm_edit' => 110,
					'u_pm_emailpm' => 111,
					'u_pm_flash' => 112,
					'u_pm_forward' => 113,
					'u_pm_img' => 114,
					'u_pm_printpm' => 115,
					'u_pm_smilies' => 116,
					'u_readpm' => 117,
					'u_savedrafts' => 118,
					'u_search' => 119,
					'u_sendemail' => 120,
					'u_sendim' => 121,
					'u_sendpm' => 122,
					'u_sig' => 123,
					'u_viewonline' => 124,
					'u_viewprofile' => 125,
					'u_dae_user' => 142, 
					'a_dae_admin' => 143 
				), 
				'option' => array ( 
					'1' => 'f_', 
					'2' => 'f_announce', 
					'3' => 'f_announce_global', 
					'4' => 'f_attach', 
					'5' => 'f_bbcode', 
					'6' => 'f_bump', 
					'7' => 'f_delete', 
					'8' => 'f_download', 
					'9' => 'f_edit', 
					'10' => 'f_email', 
					'11' => 'f_flash', 
					'12' => 'f_icons', 
					'13' => 'f_ignoreflood', 
					'14' => 'f_img', 
					'15' => 'f_list',
					'16' => 'f_list_topics', 
					'17' => 'f_noapprove', 
					'18' => 'f_poll', 
					'19' => 'f_post', 
					'20' => 'f_postcount', 
					'21' => 'f_print', 
					'22' => 'f_read', 
					'23' => 'f_reply', 
					'24' => 'f_report', 
					'25' => 'f_search', 
					'26' => 'f_sigs', 
					'27' => 'f_smilies', 
					'28' => 'f_sticky', 
					'29' => 'f_subscribe', 
					'30' => 'f_user_lock', 
					'31' => 'f_vote', 
					'32' => 'f_votechg', 
					'33' => 'f_softdelete', 
					'34' => 'm_', 
					'35' => 'm_approve', 
					'36' => 'm_chgposter', 
					'37' => 'm_delete', 
					'38' => 'm_edit', 
					'39' => 'm_info', 
					'40' => 'm_lock', 
					'41' => 'm_merge', 
					'42' => 'm_move', 
					'43' => 'm_report', 
					'44' => 'm_split', 
					'45' => 'm_softdelete', 
					'46' => 'm_ban', 
					'47' => 'm_pm_report', 
					'48' => 'm_warn', 
					'49' => 'a_', 
					'50' => 'a_aauth', 
					'51' => 'a_attach', 
					'52' => 'a_authgroups', 
					'53' => 'a_authusers', 
					'54' => 'a_backup', 
					'55' => 'a_ban', 
					'56' => 'a_bbcode', 
					'57' => 'a_board', 
					'58' => 'a_bots', 
					'59' => 'a_clearlogs', 
					'60' => 'a_email', 
					'61' => 'a_extensions', 
					'62' => 'a_fauth', 
					'63' => 'a_forum', 
					'64' => 'a_forumadd', 
					'65' => 'a_forumdel', 
					'66' => 'a_group', 
					'67' => 'a_groupadd', 
					'68' => 'a_groupdel', 
					'69' => 'a_icons', 
					'70' => 'a_jabber', 
					'71' => 'a_language', 
					'72' => 'a_mauth', 
					'73' => 'a_modules', 
					'74' => 'a_names', 
					'75' => 'a_phpinfo', 
					'76' => 'a_profile', 
					'77' => 'a_prune', 
					'78' => 'a_ranks', 
					'79' => 'a_reasons', 
					'80' => 'a_roles', 
					'81' => 'a_search', 
					'82' => 'a_server', 
					'83' => 'a_storage', 
					'84' => 'a_styles', 
					'85' => 'a_switchperm', 
					'86' => 'a_uauth', 
					'87' => 'a_user', 
					'88' => 'a_userdel', 
					'89' => 'a_viewauth', 
					'90' => 'a_viewlogs', 
					'91' => 'a_words', 
					'92' => 'u_', 
					'93' => 'u_attach', 
					'94' => 'u_chgavatar', 
					'95' => 'u_chgcensors', 
					'96' => 'u_chgemail', 
					'97' => 'u_chggrp', 
					'98' => 'u_chgname', 
					'99' => 'u_chgpasswd', 
					'100' => 'u_chgprofileinfo', 
					'101' => 'u_download', 
					'102' => 'u_hideonline', 
					'103' => 'u_ignoreflood', 
					'104' => 'u_masspm', 
					'105' => 'u_masspm_group', 
					'106' => 'u_pm_attach', 
					'107' => 'u_pm_bbcode', 
					'108' => 'u_pm_delete', 
					'109' => 'u_pm_download', 
					'110' => 'u_pm_edit', 
					'111' => 'u_pm_emailpm',
					'112' => 'u_pm_flash', 
					'113' => 'u_pm_forward', 
					'114' => 'u_pm_img', 
					'115' => 'u_pm_printpm', 
					'116' => 'u_pm_smilies', 
					'117' => 'u_readpm',
					'118' => 'u_savedrafts', 
					'119' => 'u_search', 
					'120' => 'u_sendemail', 
					'121' => 'u_sendim', 
					'122' => 'u_sendpm', 
					'123' => 'u_sig', 
					'124' => 'u_viewonline', 
					'125' => 'u_viewprofile', 
					'142' => 'u_dae_user', 
					'143' => 'a_dae_admin' 
				), 
				'global' => array ( 
					'm_' => 0, 
					'm_approve' => 1,  
					'm_chgposter' => 2,  
					'm_delete' => 3,  
					'm_edit' => 4,  
					'm_info' => 5,  
					'm_lock' => 6,  
					'm_merge' => 7,  
					'm_move' => 8,  
					'm_report' => 9,  
					'm_split' => 10,  
					'm_softdelete' => 11,  
					'm_ban' => 12,  
					'm_pm_report' => 13,  
					'm_warn' => 14,  
					'a_' => 15,  
					'a_aauth' => 16,  
					'a_attach' => 17,  
					'a_authgroups' => 18,  
					'a_authusers' => 19,  
					'a_backup' => 20,  
					'a_ban' => 21,  
					'a_bbcode' => 22,  
					'a_board' => 23,  
					'a_bots' => 24, 
					'a_clearlogs' => 25, 
					'a_email' => 26, 
					'a_extensions' => 27, 
					'a_fauth' => 28, 
					'a_forum' => 29, 
					'a_forumadd' => 30, 
					'a_forumdel' => 31, 
					'a_group' => 32, 
					'a_groupadd' => 33, 
					'a_groupdel' => 34, 
					'a_icons' => 35, 
					'a_jabber' => 36, 
					'a_language' => 37, 
					'a_mauth' => 38, 
					'a_modules' => 39, 
					'a_names' => 40, 
					'a_phpinfo' => 41, 
					'a_profile' => 42, 
					'a_prune' => 43, 
					'a_ranks' => 44, 
					'a_reasons' => 45, 
					'a_roles' => 46, 
					'a_search' => 47, 
					'a_server' => 48, 
					'a_storage' => 49, 
					'a_styles' => 50, 
					'a_switchperm' => 51, 
					'a_uauth' => 52, 
					'a_user' => 53, 
					'a_userdel' => 54, 
					'a_viewauth' => 55, 
					'a_viewlogs' => 56, 
					'a_words' => 57, 
					'u_' => 58, 
					'u_attach' => 59, 
					'u_chgavatar' => 60, 
					'u_chgcensors' => 61, 
					'u_chgemail' => 62, 
					'u_chggrp' => 63, 
					'u_chgname' => 64, 
					'u_chgpasswd' => 65, 
					'u_chgprofileinfo' => 66, 
					'u_download' => 67, 
					'u_hideonline' => 68, 
					'u_ignoreflood' => 69, 
					'u_masspm' => 70, 
					'u_masspm_group' => 71, 
					'u_pm_attach' => 72, 
					'u_pm_bbcode' => 73, 
					'u_pm_delete' => 74, 
					'u_pm_download' => 75, 
					'u_pm_edit' => 76, 
					'u_pm_emailpm' => 77, 
					'u_pm_flash' => 78, 
					'u_pm_forward' => 79, 
					'u_pm_img' => 80, 
					'u_pm_printpm' => 81, 
					'u_pm_smilies' => 82, 
					'u_readpm' => 83, 
					'u_savedrafts' => 84, 
					'u_search' => 85, 
					'u_sendemail' => 86, 
					'u_sendim' => 87, 
					'u_sendpm' => 88, 
					'u_sig' => 89, 
					'u_viewonline' => 90, 
					'u_viewprofile' => 91, 
					'u_dae_user' => 92, 
					'a_dae_admin' => 93 
				) 
			);
			
			$cache->put('_acl_options', $this->acl_options);
		}
		
		if (!isset($userdata['user_permissions']) || !trim($userdata['user_permissions']))
		{
			$this->acl_cache($userdata);
		}

		// Fill ACL array
		$this->_fill_acl($userdata['user_permissions']);
		
		// Verify bitstring length with options provided...
		$renew = false;
		$global_length = count($this->acl_options['global']);
		$local_length = count($this->acl_options['local']);

		// Specify comparing length (bitstring is padded to 31 bits)
		$global_length = ($global_length % 31) ? ($global_length - ($global_length % 31) + 31) : $global_length;
		$local_length = ($local_length % 31) ? ($local_length - ($local_length % 31) + 31) : $local_length;

		// You thought we are finished now? Noooo... now compare them.
		foreach ($this->acl as $forum_id => $bitstring)
		{
			if (($forum_id && strlen($bitstring) != $local_length) || (!$forum_id && strlen($bitstring) != $global_length))
			{
				$renew = true;
				break;
			}
		}

		// If a bitstring within the list does not match the options, we have a user with incorrect permissions set and need to renew them
		if ($renew)
		{
			$this->acl_cache($userdata);
			$this->_fill_acl($userdata['user_permissions']);
		}

		return;
	}
	
	/**
	* Build bitstring from permission set
	*/
	function build_bitstring(&$hold_ary)
	{
		$hold_str = '';

		if (count($hold_ary))
		{
			ksort($hold_ary);

			$last_f = 0;

			foreach ($hold_ary as $f => $auth_ary)
			{
				$ary_key = (!$f) ? 'global' : 'local';

				$bitstring = array();
				foreach ($this->acl_options[$ary_key] as $opt => $id)
				{
					if (isset($auth_ary[$this->acl_options['id'][$opt]]))
					{
						$bitstring[$id] = $auth_ary[$this->acl_options['id'][$opt]];

						$option_key = substr($opt, 0, strpos($opt, '_') + 1);

						// If one option is allowed, the global permission for this option has to be allowed too
						// example: if the user has the a_ permission this means he has one or more a_* permissions
						if ($auth_ary[$this->acl_options['id'][$opt]] == ACL_YES && (!isset($bitstring[$this->acl_options[$ary_key][$option_key]]) || $bitstring[$this->acl_options[$ary_key][$option_key]] == ACL_NEVER))
						{
							$bitstring[$this->acl_options[$ary_key][$option_key]] = ACL_YES;
						}
					}
					else
					{
						$bitstring[$id] = ACL_NEVER;
					}
				}

				// Now this bitstring defines the permission setting for the current forum $f (or global setting)
				$bitstring = implode('', $bitstring);

				// The line number indicates the id, therefore we have to add empty lines for those ids not present
				$hold_str .= str_repeat("\n", $f - $last_f);

				// Convert bitstring for storage - we do not use binary/bytes because PHP's string functions are not fully binary safe
				for ($i = 0, $bit_length = strlen($bitstring); $i < $bit_length; $i += 31)
				{
					$hold_str .= str_pad(base_convert(str_pad(substr($bitstring, $i, 31), 31, 0, STR_PAD_RIGHT), 2, 36), 6, 0, STR_PAD_LEFT);
				}

				$last_f = $f;
			}
			unset($bitstring);

			$hold_str = rtrim($hold_str);
		}

		return $hold_str;
	}
	
	/**
	* Cache data to user_permissions row
	*/
	function acl_cache(&$userdata)
	{
		global $db;
		
		// Empty user_permissions
		$userdata['user_permissions'] = '';

		$hold_ary = $this->acl_raw_data_single_user($userdata['user_id']);

		// Key 0 in $hold_ary are global options, all others are forum_ids

		// If this user is founder we're going to force fill the admin options ...
		if ($userdata['user_level'] == ADMIN)
		{
			foreach ($this->acl_options['global'] as $opt => $id)
			{
				if (strpos($opt, 'a_') === 0)
				{
					$hold_ary[0][$this->acl_options['id'][$opt]] = ACL_YES;
				}
			}
		}
		
		$hold_str = $this->build_bitstring($hold_ary);

		if ($hold_str)
		{			
			$userdata['user_permissions'] = $hold_str;
			
			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_permissions = '" . $db->sql_escape($userdata['user_permissions']) . "',
					user_perm_from = 0
				WHERE user_id = " . $userdata['user_id'];
			if (!($db->sql_query($sql)))
			{
				// If the column exists we change it, else we add it ;)
				$table = USERS_TABLE;
				
				$column_data = $userdata;				
				
				if (!class_exists('phpbb_db_tools') && !class_exists('tools'))
				{
					global $phpbb_root_path, $phpEx;
					require($phpbb_root_path . 'includes/db/tools.' . $phpEx);
				}
				
				if (class_exists('phpbb_db_tools'))
				{
					$db_tools = new phpbb_db_tools($db);					
				}				
				elseif (class_exists('tools'))
				{
					$db_tools = new tools($db);					
				}
				
				if (is_object($db_tools))
				{
					if ($db_tools->sql_column_exists($table, 'user_perm_from'))
					{
						$result = true;
					}
					else
					{
						$column_name = 'user_perm_from';
						
						$column_data['column_type_sql'] = 'TEXT';
						$column_data['user_perm_from'] = '0';
						
						$result = $db_tools->sql_column_add($table, $column_name, $column_data, true);				
					
						if (!$result)
						{											
							$after = (!empty($column_data['after'])) ? ' AFTER ' . $column_data['after'] : '';
							$sql = 'ALTER TABLE `' . $table . '` ADD `' . $column_name . '` ' . (($column_data['column_type_sql'] = 'NULL') ? 'TEXT' :  $column_data['column_type_sql']) . ' ' . (!empty($column_data[$column_name]) ? $column_data[$column_name] : 'NULL') . ' DEFAULT NULL'  . $after;					
						
							// We could add error handling here...
							$result = $db->sql_query($sql);					
							if (!($result))
							{		
								message_die(CRITICAL_ERROR, "Could not info", '', __LINE__, __FILE__, $sql);
							}						
						}										
					}				
					
					if ($db_tools->sql_column_exists($table, 'user_permissions'))
					{
						$result = true;
					}
					else					
					{
						$column_name = 'user_permissions';
						
						$column_data['column_type_sql'] = 'TEXT';
						$column_data['user_permissions'] = 'NULL';
						
						$result = $db_tools->sql_column_add($table, $column_name, $column_data, true);				
					
						if (!$result)
						{											
							$after = (!empty($column_data['after'])) ? ' AFTER ' . $column_data['after'] : '';
							$sql = 'ALTER TABLE `' . $table . '` ADD `' . $column_name . '` ' . (($column_data['column_type_sql'] = 'NULL') ? 'TEXT' :  $column_data['column_type_sql']) . ' ' . (!empty($column_data[$column_name]) ? $column_data[$column_name] : 'NULL') . ' DEFAULT NULL'  . $after;
							
							// We could add error handling here...
							$result = $db->sql_query($sql);					
							if (!($result))
							{		
								message_die(CRITICAL_ERROR, "Could not info", '', __LINE__, __FILE__, $sql);
							}							
						}										
					}
					
					if ($db_tools->sql_column_exists($table, 'user_birthday'))
					{
						$result = true;
					}
					else					
					{
						$column_name = 'user_birthday';
						
						$column_data['column_type_sql'] = 'TEXT';
						$column_data['user_birthday'] = 'NULL';
						
						$result = $db_tools->sql_column_add($table, $column_name, $column_data, true);
						
						if (!$result)
						{											
							$after = (!empty($column_data['after'])) ? ' AFTER ' . $column_data['after'] : '';
							$statements[] = 'ALTER TABLE `' . $table . '` ADD `' . $column_name . '` ' . (($column_data['column_type_sql'] = 'NULL') ? 'TEXT' :  $column_data['column_type_sql']) . ' ' . (!empty($column_data[$column_name]) ? $column_data[$column_name] : 'NULL') . ' DEFAULT NULL'  . $after;					
							
							// We could add error handling here...
							$result = $db->sql_query($sql);					
							if (!($result))
							{		
								message_die(CRITICAL_ERROR, "Could not info", '', __LINE__, __FILE__, $sql);
							}					
						}																		
					}
				}				
			}
		}

		return;
	}
	
	/**
 	* get_auth_forum
 	*
 	* @param unknown_type $mode
 	* @return unknown
 	*/
	function get_auth_forum($mode = 'phpbb')
	{
		global $userdata, $root_path, $phpEx;

		//
		// Try to reuse auth_view query result.
		//
		$userdata_key = 'get_auth_' . $mode . $userdata['user_id'];
		if( !empty($userdata[$userdata_key]) )
		{
			$auth_data_sql = $userdata[$userdata_key];
			return $auth_data_sql;
		}

		//
		// Now, this tries to optimize DB access involved in auth(),
		// passing AUTH_LIST_ALL will load info for all forums at once.
		//
		$is_auth_ary = $this->auth(AUTH_VIEW, AUTH_LIST_ALL, $userdata);

		//
		// Loop through the list of forums to retrieve the ids for
		// those with AUTH_VIEW allowed.
		//
		$auth_data_sql = '';
		foreach( $is_auth_ary as $fid => $is_auth_row )
		{
			if( ($is_auth_row['auth_view']) )
			{
				$auth_data_sql .= ( $auth_data_sql != '' ) ? ', ' . $fid : $fid;
			}
		}

		if( empty($auth_data_sql) )
		{
			$auth_data_sql = -1;
		}

		$userdata[$userdata_key] = $auth_data_sql;
		return $auth_data_sql;
	}

	/**
	* function acl_getfignore()
	* $auth_level_read can be a value or array;
	* $ignore_forum_ids can have this sintax: forum_id(1), forum_id(2), ..., forum_is(n);
	* 1st test 25.06.2008 by FlorinCB
	 */
	function acl_getfignore($auth_level_read, $ignore_forum_ids)
	{
		global $phpbb_root_path, $user;

		$ignore_forum_ids = ($ignore_forum_ids) ? $ignore_forum_ids : -1;

		$auth_user = array();

		if (is_array($auth_level_read))
		{
			foreach ($auth_level_read as $auth_level)
			{
				$auth_user = $this->auth($auth_level, AUTH_LIST_ALL, $user->data);

				if ($num_forums = count($auth_user))
				{
					while ( list($forum_id, $auth_mod) = each($auth_user) )
					{
						$unauthed = false;

						if (!$auth_mod[$auth_level] && (strstr($ignore_forum_ids,$auth_mod['forum_id']) === FALSE))
						{
							$unauthed = true;
						}
						if (!$auth_level && !$auth_mod['auth_read'] && (strstr($ignore_forum_ids,$auth_mod['forum_id']) === FALSE))
						{
		   					$unauthed = true;
						}
						if ($unauthed)
						{
							$ignore_forum_ids .= ($ignore_forum_ids) ? ',' . $forum_id : $forum_id;
						}
					}
				}
				unset($auth_level_read);
			}
		}
		else
		{
			$auth_user = $this->auth($auth_level_read, AUTH_LIST_ALL, $user->data);

			foreach($auth_user as $forum_id => $is_auth_row)
			{
				$unauthed = true;

				if($auth_level_read && ($is_auth_row[$auth_level_read]))
				{
					$unauthed = false;
				}

				if(strstr($ignore_forum_ids, $forum_id))
				{
					$unauthed = false;
				}

				if ($unauthed)
				{
					$ignore_forum_ids .= ($ignore_forum_ids) ? ',' . $forum_id : $forum_id;
				}

			}
		}
		$ignore_forum_ids = ($ignore_forum_ids) ? $ignore_forum_ids : -1;
		return $ignore_forum_ids;
	}
	
	/**
	* Retrieves data wanted by acl function from the database for the
	* specified user.
	*
	* @param int $user_id User ID
	* @return array User attributes
	*/
	public function obtain_user_data($user_id)
	{
		global $db;

		$sql = 'SELECT u.user_id, u.username, u.user_permissions, u.user_type, u.user_id as user_colour, u.user_level as user_type, u.user_avatar as avatar, u.user_avatar_type as avatar_type
			FROM ' . USERS_TABLE . ' u
			WHERE user_id = ' . $user_id;
		if (!($result = $db->sql_query($sql)))
		{
			message_die(CRITICAL_ERROR, 'Could not query user info');
		}		
		$user_data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		return $user_data;
	}

	/**
	* Fill ACL array with relevant bitstrings from user_permissions column
	* @access private
	*/
	function _fill_acl($user_permissions)
	{
		$seq_cache = array();
		$this->acl = array();
		$user_permissions = explode("\n", $user_permissions);

		foreach ($user_permissions as $f => $seq)
		{
			if ($seq)
			{
				$i = 0;

				if (!isset($this->acl[$f]))
				{
					$this->acl[$f] = '';
				}

				while ($subseq = substr($seq, $i, 6))
				{
					if (isset($seq_cache[$subseq]))
					{
						$converted = $seq_cache[$subseq];
					}
					else
					{
						$converted = $seq_cache[$subseq] = str_pad(base_convert($subseq, 36, 2), 31, 0, STR_PAD_LEFT);
					}

					// We put the original bitstring into the acl array
					$this->acl[$f] .= $converted;
					$i += 6;
				}
			}
		}
	}
 
	/**
	* Look up an option
	* if the option is prefixed with !, then the result becomes negated
	*
	* If a forum id is specified the local option will be combined with a global option if one exist.
	* If a forum id is not specified, only the global option will be checked.
	*/
	function acl_get($opt, $f = 0)
	{
		$negate = false;

		if (strpos($opt, '!') === 0)
		{
			$negate = true;
			$opt = substr($opt, 1);
		}

		if (!isset($this->cache[$f][$opt]))
		{
			// We combine the global/local option with an OR because some options are global and local.
			// If the user has the global permission the local one is true too and vice versa
			$this->cache[$f][$opt] = false;

			// Is this option a global permission setting?
			if (isset($this->acl_options['global'][$opt]))
			{
				if (isset($this->acl[0]))
				{
					$this->cache[$f][$opt] = $this->acl[0][$this->acl_options['global'][$opt]];
				}
			}

			// Is this option a local permission setting?
			// But if we check for a global option only, we won't combine the options...
			if ($f != 0 && isset($this->acl_options['local'][$opt]))
			{
				if (isset($this->acl[$f]) && isset($this->acl[$f][$this->acl_options['local'][$opt]]))
				{
					$this->cache[$f][$opt] |= $this->acl[$f][$this->acl_options['local'][$opt]];
				}
			}
		}

		// Founder always has all global options set to true...
		return ($negate) ? !$this->cache[$f][$opt] : $this->cache[$f][$opt];
	}

	/**
	* Get forums with the specified permission setting
	* if the option is prefixed with !, then the result becomes nagated
	*
	* @param bool $clean set to true if only values needs to be returned which are set/unset
	*/
	function acl_getf($opt, $clean = false)
	{
		$acl_f = array();
		$negate = false;

		if (strpos($opt, '!') === 0)
		{
			$negate = true;
			$opt = substr($opt, 1);
		}

		// If we retrieve a list of forums not having permissions in, we need to get every forum_id
		if ($negate)
		{
			if ($this->acl_forum_ids === false)
			{
				global $db;

				$sql = 'SELECT forum_id
					FROM ' . FORUMS_TABLE;

				if (sizeof($this->acl))
				{
					$sql .= ' WHERE ' . $db->sql_in_set('forum_id', array_keys($this->acl), true);
				}
				$result = $db->sql_query($sql);

				$this->acl_forum_ids = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$this->acl_forum_ids[] = $row['forum_id'];
				}
				$db->sql_freeresult($result);
			}
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

				$allowed = (!isset($this->cache[$f][$opt])) ? $this->acl_get($opt, $f) : $this->cache[$f][$opt];

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

		// If we get forum_ids not having this permission, we need to fill the remaining parts
		if ($negate && sizeof($this->acl_forum_ids))
		{
			foreach ($this->acl_forum_ids as $f)
			{
				$acl_f[$f][$opt] = 1;
			}
		}

		return $acl_f;
	}

	/**
	* Get local permission state for any forum.
	*
	* Returns true if user has the permission in one or more forums, false if in no forum.
	* If global option is checked it returns the global state (same as acl_get($opt))
	* Local option has precedence...
	*/
	function acl_getf_global($opt)
	{
		if (is_array($opt))
		{
			// evaluates to true as soon as acl_getf_global is true for one option
			foreach ($opt as $check_option)
			{
				if ($this->acl_getf_global($check_option))
				{
					return true;
				}
			}

			return false;
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

				// as soon as the user has any permission we're done so return true
				if ((!isset($this->cache[$f][$opt])) ? $this->acl_get($opt, $f) : $this->cache[$f][$opt])
				{
					return true;
				}
			}
		}
		else if (isset($this->acl_options['global'][$opt]))
		{
			return $this->acl_get($opt);
		}

		return false;
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
		if ($user_id !== false && !is_array($user_id) && $opts === false && $forum_id === false)
		{
			$hold_ary = array($user_id => $this->acl_raw_data_single_user($user_id));
		}
		else
		{
			$hold_ary = $this->acl_raw_data($user_id, $opts, $forum_id);
		}

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
	* Get raw acl data based on user for caching user_permissions
	* This function returns the same data as acl_raw_data(), but without the user id as the first key within the array.
	*/
	function acl_raw_data_single_user($user_id)
	{
		global $db, $cache, $user;

		$hold_ary = array();
		$hold_ary = $this->auth(AUTH_VIEW, AUTH_LIST_ALL, $user->data);
		return $hold_ary;
	}	
	
}

//
// Init the auth class
//$auth = new auth();
//movrd to common.php
define('CACHE_DIR', $phpbb_root_path . 'cache/');
/**
 * Class: cache.
 *
 * This is the custom cache for eg config data.
 *
 * @package cache
 * @author Jon Ohlsson
 * @author www.phpbb.com
 * @access public
 *
 */
class base
{
	//
	// Implementation Conventions:
	// Properties and methods prefixed with underscore are intented to be private. ;-)
	//

	// ------------------------------
	// Vars
	//
	
	/**#@+
	 * Class Flags
	 * @access private
	 */
	var $vars = array();
	var $var_expires = array();
	var $cache_dir = CACHE_DIR;
	var $is_modified = false;
	var $sql_rowset = array('1' => '1'); // Cache fix. Now also FIRST query can be cached. Unsolved phpBB bug...i think ;)
	/**#@-*/

	// ------------------------------
	// Private Methods
	//
	//
	
	/**
	 * Constructor.
	 *	 
	* Creates a cache service around a cache driver
	 *
	 * @return cache
	 */
	public function __construct()
	{
		global $board_config, $db, $phpbb_root_path, $phpEx;
		
		$this->config = $board_config;
		$this->db = $db;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
		
		$this->cache_dir = $phpbb_root_path . 'cache/';
	}


	/**
	 * Load.
	 *
	 * @access private
	 * @return unknown
	 */
	function load()
	{
		global $phpEx;
		if (file_exists($this->cache_dir . 'data_global.' . $phpEx))
		{
			include($this->cache_dir . 'data_global.' . $phpEx);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Enter description here...
	 * @access private
	 */
	function save()
	{
		if (!$this->is_modified)
		{
			return;
		}

		global $phpEx;
		$file = '<?php $this->vars=' . $this->format_array($this->vars) . ";\n\$this->var_expires=" . $this->format_array($this->var_expires) . ' ?>';

		if ($fp = @fopen($this->cache_dir . 'data_global.' . $phpEx, 'wb'))
		{
			@flock($fp, LOCK_EX);
			fwrite($fp, $file);
			@flock($fp, LOCK_UN);
			fclose($fp);
		}

		$this->is_modified = false;
	}

	/**
	 * Enter description here...
	 *
	 * @access private
	 * @param unknown_type $array
	 * @return unknown
	 */
	function format_array($array)
	{
		$lines = array();
		foreach ($array as $k => $v)
		{
			if (is_array($v))
			{
				$lines[] = "'$k'=>" . $this->format_array($v);
			}
			else if (is_int($v))
			{
				$lines[] = "'$k'=>$v";
			}
			else if (is_bool($v))
			{
				$lines[] = "'$k'=>" . (($v) ? 'true' : 'false');
			}
			else
			{
				$lines[] = "'$k'=>'" . str_replace("'", "\\'", str_replace('\\', '\\\\', $v)) . "'";
			}
		}
		return 'array(' . implode(',', $lines) . ')';
	}

	/**
	 * Enter description here...
	 *
	 * @access private
	 * @param unknown_type $query
	 * @return unknown
	 */
	function sql_load($query)
	{
		global $phpEx;

		/** Remove extra spaces and tabs ** /
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		/**  **/
		$query_id = sizeof($this->sql_rowset);
		/**  ** /
		if (!file_exists($this->cache_dir . 'sql_' . md5($query) . ".$phpEx"))
		{
			return false;
		}
		/**  ** /
		@include($this->cache_dir . 'sql_' . md5($query) . ".$phpEx");

		if (!isset($expired))
		{
			return false;
		}
		else if ($expired)
		{
			unlink($this->cache_dir . 'sql_' . md5($query) . ".$phpEx");
			return false;
		}
		/**  **/		
		return $query_id;
	}

	/**
	 * Enter description here...
	 *
	 * @access private
	 * @param unknown_type $query
	 * @param unknown_type $query_result
	 * @param unknown_type $ttl
	 */
	function sql_save($query, &$query_result, $ttl)
	{
		global $db, $phpEx;

		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);

		if ($fp = @fopen($this->cache_dir . 'sql_' . md5($query) . '.' . $phpEx, 'wb'))
		{
			@flock($fp, LOCK_EX);

			$lines = array();
			$query_id = sizeof($this->sql_rowset);
			$this->sql_rowset[$query_id] = array();

			while ($row = $db->sql_fetchrow($query_result))
			{
				$this->sql_rowset[$query_id][] = $row;

				$lines[] = "unserialize('" . str_replace("'", "\\'", str_replace('\\', '\\\\', serialize($row))) . "')";
			}
			$db->sql_freeresult($query_result);

			fwrite($fp, "<?php\n\n/*\n$query\n*/\n\n\$expired = (time() > " . (time() + $ttl) . ") ? true : false;\nif (\$expired) { return; }\n\n\$this->sql_rowset[\$query_id] = array(" . implode(',', $lines) . ') ?>');
			@flock($fp, LOCK_UN);
			fclose($fp);

			$query_result = $query_id;
		}
	}

	/**
	 * Enter description here...
	 *
	 * @access private
	 * @param unknown_type $query_id
	 * @return unknown
	 */
	function sql_exists($query_id)
	{
		return isset($this->sql_rowset[$query_id]);
	}

	/**
	 * Enter description here...
	 *
	 * @access private
	 * @param unknown_type $query_id
	 * @return unknown
	 */
	function sql_fetchrow($query_id)
	{
		return @array_shift($this->sql_rowset[$query_id]);
	}


	/**
	* {@inheritDoc}
	*/
	function sql_fetchfield($query_id, $field)
	{
		if ($this->sql_row_pointer[$query_id] < count($this->sql_rowset[$query_id]))
		{
			return (isset($this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]][$field])) ? $this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]++][$field] : false;
		}

		return false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_rowseek($rownum, $query_id)
	{
		if ($rownum >= count($this->sql_rowset[$query_id]))
		{
			return false;
		}

		$this->sql_row_pointer[$query_id] = $rownum;
		return true;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_freeresult($query_id)
	{
		if (!isset($this->sql_rowset[$query_id]))
		{
			return false;
		}

		unset($this->sql_rowset[$query_id]);
		unset($this->sql_row_pointer[$query_id]);

		return true;
	}	

	// ------------------------------
	// Public Methods
	//
	//

	/**
	 * Enter description here...
	 *
	 * @access public
	 * @param unknown_type $var_name
	 * @return unknown
	 */
	function _exists($var_name)
	{
		if ($var_name{0} == '_')
		{
			global $phpEx;
			return file_exists($this->cache_dir . 'data' . $var_name . ".$phpEx");
		}
		else
		{
			if (!sizeof($this->vars))
			{
				$this->load();
			}

			if (!isset($this->var_expires[$var_name]))
			{
				return false;
			}

			return (time() > $this->var_expires[$var_name]) ? false : isset($this->vars[$var_name]);
		}
	}

	/**
	 * Unload.
	 *
	 * Unload and save modified cache, must be done before the DB connection if closed
	 * <code>
	 * if (!empty($cache))
	 * {
	 * 		$cache->unload();
	 * }
	 * </code>
	 *
	 * @access public
	 */
	function unload()
	{
		$this->save();
		unset($this->vars);
		unset($this->var_expires);
		unset($this->sql_rowset);
	}

	/**
	 * Tidy.
	 *
	 * Tidy cache. Remove expired files etc
	 * - $cache->tidy();
	 *
	 * @access public
	 */
	function tidy()
	{
		global $phpEx;

		$dir = opendir($this->cache_dir);
		while (($entry = readdir($dir)) !== false)
		{
			if (!preg_match('/^(sql_|data_(?!global))/', $entry))
			{
				continue;
			}

			$expired = true;
			include($this->cache_dir . $entry);
			if ($expired)
			{
				unlink($this->cache_dir . $entry);
			}
		}
		@closedir($dir);

		if (file_exists($this->cache_dir . 'data_global.' . $phpEx))
		{
			if (!sizeof($this->vars))
			{
				$this->load();
			}

			foreach ($this->var_expires as $var_name => $expires)
			{
				if (time() > $expires)
				{
					$this->destroy($var_name);
				}
			}
		}
	}

	/**
	 * Enter description here...
	 * - $cache->get('some_data')
	 *
	 * @access public
	 * @param unknown_type $var_name
	 * @return unknown
	 */
	function get($var_name)
	{
		if ($var_name{0} == '_')
		{
			global $phpEx;

			if (!$this->_exists($var_name))
			{
				return false;
			}

			include($this->cache_dir . 'data' . $var_name . ".$phpEx");
			return (isset($data)) ? $data : false;
		}
		else
		{
			return ($this->_exists($var_name)) ? $this->vars[$var_name] : false;
		}
	}

	/**
	 * Enter description here...
	 * - $cache->put('some_data', $this->some_data)
	 *
	 * @access public
	 * @param unknown_type $var_name
	 * @param unknown_type $var
	 * @param unknown_type $ttl
	 */
	function put($var_name, $var, $ttl = 31536000)
	{
		if ($var_name{0} == '_')
		{
			global $phpEx;

			if ($fp = @fopen($this->cache_dir . 'data' . $var_name . ".$phpEx", 'wb'))
			{
				@flock($fp, LOCK_EX);
				fwrite($fp, "<?php\n\$expired = (time() > " . (time() + $ttl) . ") ? true : false;\nif (\$expired) { return; }\n\n\$data = unserialize('" . str_replace("'", "\\'", str_replace('\\', '\\\\', serialize($var))) . "');\n?>");
				@flock($fp, LOCK_UN);
				fclose($fp);
			}
		}
		else
		{
			$this->vars[$var_name] = $var;
			$this->var_expires[$var_name] = time() + $ttl;
			$this->is_modified = true;
		}
	}

	/**
	 * Destroy.
	 *
	 * Remove cache file.
	 * - $cache->destroy('sql', SOME_TABLE);
	 * - $cache->destroy('some_data');
	 *
	 * @access public
	 * @param unknown_type $var_name
	 * @param unknown_type $table
	 */
	function destroy($var_name, $table = '')
	{
		global $phpEx;

		if ($var_name == 'sql' && !empty($table))
		{
			$regex = '(' . ((is_array($table)) ? implode('|', $table) : $table) . ')';

			$dir = opendir($this->cache_dir);
			while (($entry = readdir($dir)) !== false)
			{
				if (strpos($entry, 'sql_') !== 0)
				{
					continue;
				}

				$fp = fopen($this->cache_dir . $entry, 'rb');
				$file = fread($fp, filesize($this->cache_dir . $entry));
				@fclose($fp);

				if (preg_match('#/\*.*?\W' . $regex . '\W.*?\*/#s', $file, $m))
				{
					unlink($this->cache_dir . $entry);
				}
			}
			@closedir($dir);

			return;
		}

		if (!$this->_exists($var_name))
		{
			return;
		}

		if ($var_name{0} == '_')
		{
			@unlink($this->cache_dir . 'data' . $var_name . ".$phpEx");
		}
		else if (isset($this->vars[$var_name]))
		{
			$this->is_modified = true;
			unset($this->vars[$var_name]);
			unset($this->var_expires[$var_name]);

			// We save here to let the following cache hits succeed
			$this->save();
		}
	}
}

//
//Moved to functions.php
//This file is sometime included for this function 
//and so we keep it here for phpBB2 backend
//
if (!function_exists('append_sid'))
{
	//
	// Append $SID to a url. Borrowed from phplib and modified. This is an
	// extra routine utilised by the session code above and acts as a wrapper
	// around every single URL and form action. If you replace the session
	// code you must include this routine, even if it's empty.
	//
	function phpbb_append_sid($url, $non_html_amp = false)
	{
		global $SID;

		if ( !empty($SID) && !preg_match('#sid=#', $url) )
		{
			$url .= ((strpos($url, '?') !== false) ?  (($non_html_amp) ? '&' : '&amp;') : '?') . $SID;
		}

		return $url;
	}
}
?>