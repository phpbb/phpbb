<?php
/***************************************************************************
 *                             functions_ucp.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
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

// Handles manipulation of user data. Primary used in registration
// and user profile manipulation
class ucp extends user
{
	var $modules = array();
	var $error = array();

	// Loads a given module (if it isn't already available), instantiates
	// a new object, and where appropriate calls the modules init method
	function load_module($module_name)
	{
		if (!class_exists('ucp_' . $module_name))
		{
			global $phpbb_root_path, $phpEx;

			require_once($phpbb_root_path . 'includes/ucp/ucp_' . $module_name . '.'.$phpEx);
			eval('$this->module[' . $module_name . '] = new ucp_' . $module_name . '();');

			if (method_exists($this->module[$module_name], 'init'))
			{
				$this->module[$module_name]->init();
			}
		}
	}

	// This is replaced by the loaded module
	function main($module_id = false)
	{
		return false;
	}

	// This generates the block template variable for outputting the list
	// of submodules, should be called with an associative array of modules
	// in the form 'LANG_STRING' => 'LINK'
	function subsection(&$module_ary, &$selected_module)
	{
		global $template, $user, $phpEx, $SID;

		foreach($module_ary as $section_title => $module_link)
		{
			$template->assign_block_vars('ucp_subsection', array(
				'L_TITLE'	=> $user->lang['UCP_' . $section_title],

				'S_SELECTED'=> ($section_title == strtoupper($selected_module)) ? true : false, 

				'U_TITLE'	=> "ucp.$phpEx$SID&amp;$module_link")
			);
		}
	}

	// Displays the appropriate template with the given title
	function display(&$page_title, $tpl_name)
	{
		global $template, $phpEx;

		page_header($page_title);

		$template->set_filenames(array(
			'body' => $tpl_name)
		);
		make_jumpbox('viewforum.'.$phpEx);

		page_footer();
	}

	// Generates list of additional fields, their type, appropriate data
	// etc. for admin defined fields
	function extra_fields($page)
	{
		return false;
	}

	// Generates an alphanumeric random string of given length
	function gen_rand_string($num_chars)
	{
		$chars = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',  'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',  'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9');

		list($usec, $sec) = explode(' ', microtime()); 
		mt_srand($sec * $usec); 

		$max_chars = count($chars) - 1;
		$rand_str = '';
		for ($i = 0; $i < $num_chars; $i++)
		{
			$rand_str .= $chars[mt_rand(0, $max_chars)];
		}

		return $rand_str;
	}	

	// Normalises supplied data dependant on required type/length, errors
	// on incorrect data
	function normalise_data(&$data, &$normalise)
	{
		$valid_data = array();
		foreach ($normalise as $var_type => $var_ary)
		{
			foreach ($var_ary as $var_name => $var_limits)
			{
				$var_name = (is_string($var_name)) ? $var_name : $var_limits; 

				if (isset($data[$var_name]))
				{
					switch ($var_type)
					{
						case 'int':
							$valid_data[$var_name] = (int) $data[$var_name];
							break;

						case 'float':
							$valid_data[$var_name] = (double) $data[$var_name];
							break;

						case 'bool':
							$valid_data[$var_name] = ($data[$var_name] <= 0) ? 0 : 1;
							break;

						case 'string':
							// Cleanup data, remove excess spaces, run entites
							$valid_data[$var_name] = htmlentities(trim(preg_replace('#\s{2,}#s', ' ', strtr((string) $data[$var_name], array_flip(get_html_translation_table(HTML_ENTITIES))))));

							// How should we check this data?
							if (!is_array($var_limits))
							{
								// Is the match a string? If it is, process it further, else we'll
								// assume it's a maximum length
								if (is_string($var_limits))
								{
									if (strstr($var_limits, ','))
									{
										list($min_value, $max_value) = explode(',', $var_limits);
										if (!empty($valid_data[$var_name]) && strlen($valid_data[$var_name]) < $min_value)
										{
											$this->error[] = strtoupper($var_name) . '_TOO_SHORT';
										}

										if (strlen($valid_data[$var_name]) > $max_value)
										{
											$this->error[] = strtoupper($var_name) . '_TOO_LONG';
										}
									}
								}
								else
								{
									if (strlen($valid_data[$var_name]) > $var_limits)
									{
										$this->error[] = strtoupper($var_name) . '_TOO_LONG';
									}
								}
							}
							break;
					}
				}
			}
		}

		return $valid_data;
	}

	// Validates data subject to supplied requirements, errors appropriately
	function validate_data(&$data, &$validate)
	{
		global $db, $user, $config;

		foreach ($validate as $operation => $var_ary)
		{
			foreach ($var_ary as $var_name => $compare)
			{
				if (!empty($compare))
				{
					switch ($operation)
					{
						case 'match':
							if (is_array($compare))
							{
								foreach ($compare as $match)
								{
									if (!preg_match($match, $data[$var_name]))
									{
										$this->error[] = strtoupper($var_name) . '_WRONG_DATA';
									}
								}
							}
							else if (!preg_match($compare, $data[$var_name]))
							{
								$this->error[] = strtoupper($var_name) . '_WRONG_DATA';
							}
							break;

						case 'compare':
							if (is_array($compare))
							{
								if (!in_array($data[$var_name], $compare))
								{
									$this->error[] = strtoupper($var_name) . '_MISMATCH';
								}
							}
							else if ($data[$var_name] != $compare)
							{
								$this->error[] = strtoupper($var_name) . '_MISMATCH';
							}
							break;

						case 'function':
							if ($result = $this->$compare($data[$var_name]))
							{
								$this->error[] = $result;
							}

							break;

						case 'reqd':
							if (!isset($data[$compare]) || (is_string($data[$compare]) && $data[$compare] === ''))
							{
								$this->error[] = strtoupper($compare) . '_MISSING_DATA';
							}
							break;
					}
				}
			}
		}
	}
	
	// Check to see if the username has been taken, or if it is disallowed.
	// Also checks if it includes the " character, which we don't allow in usernames.
	// Used for registering, changing names, and posting anonymously with a username
	function validate_username($username)
	{
		global $db, $user;

		$sql = "SELECT username
			FROM " . USERS_TABLE . "
			WHERE LOWER(username) = '" . strtolower($db->sql_escape($username)) . "'";
		$result = $db->sql_query($sql);
	
		if ($row = $db->sql_fetchrow($result))
		{
			return 'USERNAME_TAKEN';
		}
		$db->sql_freeresult($result);
	
		$sql = "SELECT group_name
			FROM " . GROUPS_TABLE . "
			WHERE LOWER(group_name) = '" . strtolower($db->sql_escape($username)) . "'";
		$result = $db->sql_query($sql);
	
		if ($row = $db->sql_fetchrow($result))
		{
			return 'USERNAME_TAKEN';
		}
		$db->sql_freeresult($result);

		$sql = "SELECT disallow_username
			FROM " . DISALLOW_TABLE;
		$result = $db->sql_query($sql);
	
		while ($row = $db->sql_fetchrow($result))
		{
			if (preg_match('#(' . str_replace('\*', '.*?', preg_quote($row['disallow_username'], '#')) . ')#i', $username))
			{
				return 'USERNAME_DISALLOWED';
			}
		}
		$db->sql_freeresult($result);

		$sql = "SELECT word
			FROM  " . WORDS_TABLE;
		$result = $db->sql_query($sql);
	
		while ($row = $db->sql_fetchrow($result))
		{
			if (preg_match('#(' . str_replace('\*', '.*?', preg_quote($row['word'], '#')) . ')#i', $username))
			{
				return 'USERNAME_DISALLOWED';
			}
		}
		$db->sql_freeresult($result);

		return false;
	}
	
	// Check to see if email address is banned or already present in the DB
	function validate_email($email)
	{
		global $db, $user;
	
		if ($email != '')
		{
			if (preg_match('#^[a-z0-9\.\-_\+]+@(.*?\.)*?[a-z0-9\-_]+\.[a-z]+$#is', $email))
			{
				$sql = "SELECT ban_email
					FROM " . BANLIST_TABLE;
				$result = $db->sql_query($sql);
	
				while ($row = $db->sql_fetchrow($result))
				{
					if (preg_match('#^' . str_replace('*', '.*?', $row['ban_email']) . '$#is', $email))
					{
						return 'EMAIL_BANNED';
					}
				}
				$db->sql_freeresult($result);
	
				$sql = "SELECT user_email
					FROM " . USERS_TABLE . "
					WHERE user_email = '" . $db->sql_escape($email) . "'";
				$result = $db->sql_query($sql);
	
				if ($row = $db->sql_fetchrow($result))
				{
					return 'EMAIL_TAKEN';
				}
				$db->sql_freeresult($result);
	
				return false;
			}
		}
	
		return 'EMAIL_INVALID';
	}

	function update_user($userdata)
	{
		
		
	}
}

?>