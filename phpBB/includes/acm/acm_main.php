<?php
/** 
*
* @package acm
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package acm
* Class for grabbing/handling cached entries, extends acm_file or acm_db depending on the setup
*/
class cache extends acm
{
	/**
	* Get config values
	*/
	function obtain_config()
	{
		global $db;

		if ($config = $this->get('config'))
		{
			$sql = 'SELECT config_name, config_value
				FROM ' . CONFIG_TABLE . '
				WHERE is_dynamic = 1';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$config[$row['config_name']] = $row['config_value'];
			}
			$db->sql_freeresult($result);
		}
		else
		{
			$config = $cached_config = array();

			$sql = 'SELECT config_name, config_value, is_dynamic
				FROM ' . CONFIG_TABLE;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				if (!$row['is_dynamic'])
				{
					$cached_config[$row['config_name']] = $row['config_value'];
				}

				$config[$row['config_name']] = $row['config_value'];
			}
			$db->sql_freeresult($result);

			$this->put('config', $cached_config);
		}
	
		return $config;
	}

	/**
	* Obtain list of naughty words and build preg style replacement arrays for use by the
	* calling script
	*/
	function obtain_word_list(&$censors)
	{
		global $config, $user, $db;

		if (!$user->optionget('viewcensors') && $config['allow_nocensors'])
		{
			return false;
		}

		if ($this->exists('word_censors'))
		{
			$censors = $this->get('word_censors');
		}
		else
		{
			$sql = 'SELECT word, replacement
				FROM  ' . WORDS_TABLE;
			$result = $db->sql_query($sql);

			$censors = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$censors['match'][] = '#\b(' . str_replace('\*', '\w*?', preg_quote($row['word'], '#')) . ')\b#i';
				$censors['replace'][] = $row['replacement'];
			}
			$db->sql_freeresult($result);

			$this->put('word_censors', $censors);
		}

		return true;
	}

	/**
	* Obtain currently listed icons
	*/
	function obtain_icons(&$icons)
	{
		if ($this->exists('icons'))
		{
			$icons = $this->get('icons');
		}
		else
		{
			global $db;
	
			// Topic icons
			$sql = 'SELECT *
				FROM ' . ICONS_TABLE . '
				ORDER BY icons_order';
			$result = $db->sql_query($sql);

			$icons = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$icons[$row['icons_id']]['img'] = $row['icons_url'];
				$icons[$row['icons_id']]['width'] = (int) $row['icons_width'];
				$icons[$row['icons_id']]['height'] = (int) $row['icons_height'];
				$icons[$row['icons_id']]['display'] = (bool) $row['display_on_posting'];
			}
			$db->sql_freeresult($result);

			$this->put('icons', $icons);
		}

		return;
	}

	/**
	* Obtain ranks
	*/
	function obtain_ranks(&$ranks)
	{
		if ($this->exists('ranks'))
		{
			$ranks = $this->get('ranks');
		}
		else
		{
			global $db;
	
			$sql = 'SELECT *
				FROM ' . RANKS_TABLE . '
				ORDER BY rank_min DESC';
			$result = $db->sql_query($sql);

			$ranks = array();
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['rank_special'])
				{
					$ranks['special'][$row['rank_id']] = array(
						'rank_title'	=>	$row['rank_title'],
						'rank_image'	=>	$row['rank_image']
					);
				}
				else
				{
					$ranks['normal'][] = array(
						'rank_title'	=>	$row['rank_title'],
						'rank_min'		=>	$row['rank_min'],
						'rank_image'	=>	$row['rank_image']
					);
				}
			}
			$db->sql_freeresult($result);

			$this->put('ranks', $ranks);
		}

		return;
	}

	/**
	* Obtain allowed extensions
	*/
	function obtain_attach_extensions(&$extensions, $forum_id = false)
	{
		if ($this->exists('_extensions'))
		{
			$extensions = $this->get('_extensions');
		}
		else
		{
			global $db;
	
			// The rule is to only allow those extensions defined. ;)
			$sql = 'SELECT e.extension, g.*
				FROM ' . EXTENSIONS_TABLE . ' e, ' . EXTENSION_GROUPS_TABLE . ' g
				WHERE e.group_id = g.group_id
					AND g.allow_group = 1';
			$result = $db->sql_query($sql);

			$extensions = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$extension = strtolower(trim($row['extension']));

				$extensions[$extension] = array(
					'display_cat'	=> (int) $row['cat_id'],
					'download_mode'	=> (int) $row['download_mode'],
					'upload_icon'	=> trim($row['upload_icon']),
					'max_filesize'	=> (int) $row['max_filesize']
				);

				$allowed_forums = ($row['allowed_forums']) ? unserialize(trim($row['allowed_forums'])) : array();

				if ($row['allow_in_pm'])
				{
					$allowed_forums = array_merge($allowed_forums, array(0));
				}

				// Store allowed extensions forum wise
				$extensions['_allowed_'][$extension] = (!sizeof($allowed_forums)) ? 0 : $allowed_forums;
			}
			$db->sql_freeresult($result);

			$this->put('_extensions', $extensions);
		}

		if ($forum_id !== false)
		{
			$return = array();

			foreach ($extensions['_allowed_'] as $extension => $check)
			{
				$allowed = false;

				if (is_array($check))
				{
					// Check for private messaging
					if (sizeof($check) == 1 && $check[0] == 0)
					{
						$allowed = true;
						continue;
					}

					$allowed = (!in_array($forum_id, $check)) ? false : true;
				}
				else
				{
					$allowed = ($forum_id == 0) ? false : true;
				}
			
				if ($allowed)
				{
					$return['_allowed_'][$extension] = 0;
					$return[$extension] = $extensions[$extension];
				}
			}

			$extensions = $return;
		}

		return;
	}

	/**
	* Obtain active bots
	*/
	function obtain_bots(&$bots)
	{
		if ($this->exists('bots'))
		{
			$bots = $this->get('bots');
		}
		else
		{
			global $db;
	
			switch (SQL_LAYER)
			{
				case 'mssql':
				case 'mssql_odbc':
					$sql = 'SELECT user_id, bot_agent, bot_ip 
						FROM ' . BOTS_TABLE . '
						WHERE bot_active = 1
					ORDER BY LEN(bot_agent) DESC';
				break;
	
				// LENGTH supported by MySQL, IBM DB2 and Oracle for sure...
				default:
					$sql = 'SELECT user_id, bot_agent, bot_ip 
						FROM ' . BOTS_TABLE . '
						WHERE bot_active = 1
					ORDER BY LENGTH(bot_agent) DESC';
				break;
			}
			$result = $db->sql_query($sql);
		
			while ($row = $db->sql_fetchrow($result))
			{
				$bots[] = $row;
			}
			$db->sql_freeresult($result);

			$this->put('bots', $bots);
		}
	
		return;
	}

	/**
	* Obtain cfg file data
	*/
	function obtain_cfg_items($theme)
	{
		global $config, $phpbb_root_path;

		$parsed_items = array(
			'theme'		=> array(),
			'template'	=> array(),
			'imageset'	=> array()
		);

		foreach ($parsed_items as $key => $parsed_array)
		{
			$parsed_array = ($this->exists('_' . $key . '_cfg')) ? $this->get('_' . $key . '_cfg') : array();

			$reparse = false;
			$filename = $phpbb_root_path . 'styles/' . $theme[$key . '_path'] . '/' . $key . '/' . $key . '.cfg';
		
			if (!file_exists($filename))
			{
				continue;
			}

			if (!isset($parsed_array[$theme[$key . '_id']]) || (($config['load_tplcompile'] && @filemtime($filename) > $parsed_array['filetime'])))
			{
				$reparse = true;
			}
		
			// Re-parse cfg file
			if ($reparse)
			{
				$parsed_array = parse_cfg_file($filename);
				$parsed_array['filetime'] = @filemtime($filename);

				$this->put('_' . $key . '_cfg', $parsed_array);
			}
			$parsed_items[$key] = $parsed_array;
		}

		return $parsed_items;
	}

}

?>