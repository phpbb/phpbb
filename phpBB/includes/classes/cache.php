<?php
/**
*
* @package acm
* @version $Id$
* @copyright (c) 2005, 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Class for obtaining cached entries, for example censor word list, configuration...
* @package acm
*/
class phpbb_cache
{
	/**
	* We do not want this object instantiable
	*/
	private function ___construct() { }

	/**
	* Required phpBB objects
	*/
	public $phpbb_required = array('config', 'acm', 'db');

	/**
	* Optional phpBB objects
	*/
	public $phpbb_optional = array();

	/**
	* Get config values
	*
	* @return array configuration
	* @access public
	*/
	public static function obtain_config()
	{
		if ((phpbb::$config = phpbb::$acm->get('#config')) !== false)
		{
			$sql = 'SELECT config_name, config_value
				FROM ' . CONFIG_TABLE . '
				WHERE is_dynamic = 1';
			$result = phpbb::$db->sql_query($sql);

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				phpbb::$config[$row['config_name']] = $row['config_value'];
			}
			phpbb::$db->sql_freeresult($result);
		}
		else
		{
			phpbb::$config = $cached_config = array();

			$sql = 'SELECT config_name, config_value, is_dynamic
				FROM ' . CONFIG_TABLE;
			$result = phpbb::$db->sql_query($sql);

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				if (!$row['is_dynamic'])
				{
					$cached_config[$row['config_name']] = $row['config_value'];
				}

				phpbb::$config[$row['config_name']] = $row['config_value'];
			}
			phpbb::$db->sql_freeresult($result);

			phpbb::$acm->put('#config', $cached_config);
		}

		return phpbb::$config;
	}

	/**
	* Obtain list of naughty words and build preg style replacement arrays for use by the calling script
	*
	* @return array Censored words
	* @access public
	*/
	public static function obtain_word_list()
	{
		if (($censors = phpbb::$acm->get('word_censors')) === false)
		{
			$sql = 'SELECT word, replacement
				FROM ' . WORDS_TABLE;
			$result = phpbb::$db->sql_query($sql);

			$censors = array();
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$censors['match'][] = '#(?<!\w)(' . str_replace('\*', '\w*?', preg_quote($row['word'], '#')) . ')(?!\w)#i';
				$censors['replace'][] = $row['replacement'];
			}
			phpbb::$db->sql_freeresult($result);

			phpbb::$acm->put('word_censors', $censors);
		}

		return $censors;
	}

	/**
	* Obtain currently listed icons
	*
	* @return array Icons
	* @access public
	*/
	public static function obtain_icons()
	{
		if (($icons = phpbb::$acm->get('icons')) === false)
		{
			// Topic icons
			$sql = 'SELECT *
				FROM ' . ICONS_TABLE . '
				ORDER BY icons_order';
			$result = phpbb::$db->sql_query($sql);

			$icons = array();
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$icons[$row['icons_id']]['img'] = $row['icons_url'];
				$icons[$row['icons_id']]['width'] = (int) $row['icons_width'];
				$icons[$row['icons_id']]['height'] = (int) $row['icons_height'];
				$icons[$row['icons_id']]['display'] = (bool) $row['display_on_posting'];
			}
			phpbb::$db->sql_freeresult($result);

			phpbb::$acm->put('icons', $icons);
		}

		return $icons;
	}

	/**
	* Obtain ranks
	*
	* @return Ranks
	* @access public
	*/
	public static function obtain_ranks()
	{
		if (($ranks = phpbb::$acm->get('ranks')) === false)
		{
			$sql = 'SELECT *
				FROM ' . RANKS_TABLE . '
				ORDER BY rank_min DESC';
			$result = phpbb::$db->sql_query($sql);

			$ranks = array();
			while ($row = phpbb::$db->sql_fetchrow($result))
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
			phpbb::$db->sql_freeresult($result);

			phpbb::$acm->put('ranks', $ranks);
		}

		return $ranks;
	}

	/**
	* Put attachment extensions data into cache
	*
	* @return array Cached extensions
	* @access private
	*/
	private static function cache_extensions()
	{
		$extensions = array(
			'_allowed_post'	=> array(),
			'_allowed_pm'	=> array(),
		);

		// The rule is to only allow those extensions defined. ;)
		$sql = 'SELECT e.extension, g.*
			FROM ' . EXTENSIONS_TABLE . ' e, ' . EXTENSION_GROUPS_TABLE . ' g
			WHERE e.group_id = g.group_id
				AND (g.allow_group = 1 OR g.allow_in_pm = 1)';
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$extension = strtolower(trim($row['extension']));

			$extensions[$extension] = array(
				'display_cat'	=> (int) $row['cat_id'],
				'download_mode'	=> (int) $row['download_mode'],
				'upload_icon'	=> trim($row['upload_icon']),
				'max_filesize'	=> (int) $row['max_filesize'],
				'allow_group'	=> $row['allow_group'],
				'allow_in_pm'	=> $row['allow_in_pm'],
			);

			$allowed_forums = ($row['allowed_forums']) ? unserialize(trim($row['allowed_forums'])) : array();

			// Store allowed extensions forum wise
			if ($row['allow_group'])
			{
				$extensions['_allowed_post'][$extension] = (!sizeof($allowed_forums)) ? 0 : $allowed_forums;
			}

			if ($row['allow_in_pm'])
			{
				$extensions['_allowed_pm'][$extension] = 0;
			}
		}
		phpbb::$db->sql_freeresult($result);

		phpbb::$acm->put('extensions', $extensions);
		return $extensions;
	}

	/**
	* Obtain allowed attachment extensions in private messages
	*
	* @return array Allowed extensions
	* @access public
	*/
	public static function obtain_extensions_pm()
	{
		if (($extensions = phpbb::$acm->get('extensions')) === false)
		{
			$extensions = self::cache_extensions();
		}

		// We are checking for private messages, therefore we only need to get the pm extensions...
		$result = array('_allowed_' => array());

		foreach ($extensions['_allowed_pm'] as $extension => $check)
		{
			$result['_allowed_'][$extension] = 0;
			$result[$extension] = $extensions[$extension];
		}

		return $result;
	}

	/**
	* Obtain allowed attachment extensions in specific forum
	*
	* @param int $forum_id The forum id
	* @return array Allowed extensions within the specified forum
	* @access public
	*/
	public static function obtain_extensions_forum($forum_id)
	{
		if (($extensions = phpbb::$acm->get('extensions')) === false)
		{
			$extensions = self::cache_extensions();
		}

		$forum_id = (int) $forum_id;
		$result = array('_allowed_' => array());

		foreach ($extensions['_allowed_post'] as $extension => $check)
		{
			// Check for allowed forums
			if (is_array($check))
			{
				$allowed = (!in_array($forum_id, $check)) ? false : true;
			}
			else
			{
				$allowed = true;
			}

			if ($allowed)
			{
				$result['_allowed_'][$extension] = 0;
				$result[$extension] = $extensions[$extension];
			}
		}

		if (!isset($result['_allowed_']))
		{
			$result['_allowed_'] = array();
		}

		return $result;
	}

	/**
	* Obtain general attachment extension information
	*
	* @return array Cached extension information
	* @access public
	*/
	public static function obtain_extensions()
	{
		if (($extensions = phpbb::$acm->get('extensions')) === false)
		{
			$extensions = self::cache_extensions();
		}

		return $extensions;
	}

	/**
	* Obtain active bots
	*
	* @return array Active bots
	* @access public
	*/
	public static function obtain_bots()
	{
		if (($bots = phpbb::$acm->get('bots')) === false)
		{
			// @todo We order by last visit date. This way we are able to safe some cycles by checking the most active ones first.
			$sql = 'SELECT user_id, bot_agent, bot_ip
				FROM ' . BOTS_TABLE . '
				WHERE bot_active = 1
				ORDER BY ' . phpbb::$db->sql_function('length_varchar', 'bot_agent') . 'DESC';
			$result = phpbb::$db->sql_query($sql);

			$bots = array();
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$bots[] = $row;
			}
			phpbb::$db->sql_freeresult($result);

			phpbb::$acm->put('bots', $bots);
		}

		return $bots;
	}

	/**
	* Obtain Styles .cfg file data
	*
	* @param array $theme An array containing the path to the items
	* @param string $item The specific item to get: 'theme', 'template', or 'imageset'
	* @return array The configuration
	* @access public
	*/
	public static function obtain_cfg_item($theme, $item = 'theme')
	{
		$parsed_array = phpbb::$acm->get('cfg_' . $item . '_' . $theme[$item . '_path']);

		if ($parsed_array === false)
		{
			$parsed_array = array();
		}

		$reparse = false;
		$filename = PHPBB_ROOT_PATH . 'styles/' . $theme[$item . '_path'] . '/' . $item . '/' . $item . '.cfg';

		if (!file_exists($filename))
		{
			return $parsed_array;
		}

		if (!isset($parsed_array['filetime']) || ((phpbb::$config['load_tplcompile'] && @filemtime($filename) > $parsed_array['filetime'])))
		{
			$reparse = true;
		}

		// Re-parse cfg file
		if ($reparse)
		{
			$parsed_array = parse_cfg_file($filename);
			$parsed_array['filetime'] = @filemtime($filename);

			phpbb::$acm->put('cfg_' . $item . '_' . $theme[$item . '_path'], $parsed_array);
		}

		return $parsed_array;
	}

	/**
	* Obtain disallowed usernames
	*
	* @return array Disallowed usernames
	* @access public
	*/
	public static function obtain_disallowed_usernames()
	{
		if (($usernames = phpbb::$acm->get('disallowed_usernames')) === false)
		{
			$sql = 'SELECT disallow_username
				FROM ' . DISALLOW_TABLE;
			$result = phpbb::$db->sql_query($sql);

			$usernames = array();
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$usernames[] = str_replace('%', '.*?', preg_quote(utf8_clean_string($row['disallow_username']), '#'));
			}
			phpbb::$db->sql_freeresult($result);

			phpbb::$acm->put('disallowed_usernames', $usernames);
		}

		return $usernames;
	}
}

?>