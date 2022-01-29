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

namespace phpbb\cache;

/**
* Class for grabbing/handling cached entries
*/
class service
{
	/** @var string Name of event used for cache purging */
	private const PURGE_DEFERRED_ON_EVENT = 'core.garbage_collection';

	/** @var bool Flag whether cache purge has been deferred */
	private $cache_purge_deferred = false;

	/**
	* Cache driver.
	*
	* @var \phpbb\cache\driver\driver_interface
	*/
	protected $driver;

	/**
	* The config.
	*
	* @var \phpbb\config\config
	*/
	protected $config;

	/**
	* Database connection.
	*
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/** @var \phpbb\event\dispatcher phpBB Event dispatcher */
	protected $dispatcher;

	/**
	* Root path.
	*
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* PHP file extension.
	*
	* @var string
	*/
	protected $php_ext;

	/**
	* Creates a cache service around a cache driver
	*
	* @param \phpbb\cache\driver\driver_interface $driver The cache driver
	* @param \phpbb\config\config $config The config
	* @param \phpbb\db\driver\driver_interface $db Database connection
	* @param \phpbb\event\dispatcher $dispatcher Event dispatcher
	* @param string $phpbb_root_path Root path
	* @param string $php_ext PHP file extension
	*/
	public function __construct(\phpbb\cache\driver\driver_interface $driver, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\event\dispatcher $dispatcher, $phpbb_root_path, $php_ext)
	{
		$this->set_driver($driver);
		$this->config = $config;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Returns the cache driver used by this cache service.
	*
	* @return \phpbb\cache\driver\driver_interface The cache driver
	*/
	public function get_driver()
	{
		return $this->driver;
	}

	/**
	 * Deferred purge of the cache.
	 *
	 * A deferred purge will be executed after rendering a page.
	 * It is recommended to be used in cases where an instant purge of the cache
	 * is not required, i.e. when the goal of a cache purge is to start from a
	 * clear cache at the next page load.
	 *
	 * @return void
	 */
	public function deferred_purge(): void
	{
		if (!$this->cache_purge_deferred)
		{
			$this->dispatcher->addListener(self::PURGE_DEFERRED_ON_EVENT, [$this, 'purge']);
			$this->cache_purge_deferred = true;
		}
	}

	/**
	* Replaces the cache driver used by this cache service.
	*
	* @param \phpbb\cache\driver\driver_interface $driver The cache driver
	*/
	public function set_driver(\phpbb\cache\driver\driver_interface $driver)
	{
		$this->driver = $driver;
	}

	public function __call($method, $arguments)
	{
		return call_user_func_array(array($this->driver, $method), $arguments);
	}

	/**
	* Obtain list of naughty words and build preg style replacement arrays for use by the
	* calling script
	*/
	function obtain_word_list()
	{
		if (($censors = $this->driver->get('_word_censors')) === false)
		{
			$sql = 'SELECT word, replacement
				FROM ' . WORDS_TABLE;
			$result = $this->db->sql_query($sql);

			$censors = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$censors['match'][] = get_censor_preg_expression($row['word']);
				$censors['replace'][] = $row['replacement'];
			}
			$this->db->sql_freeresult($result);

			$this->driver->put('_word_censors', $censors);
		}

		return $censors;
	}

	/**
	* Obtain currently listed icons
	*/
	function obtain_icons()
	{
		if (($icons = $this->driver->get('_icons')) === false)
		{
			// Topic icons
			$sql = 'SELECT *
				FROM ' . ICONS_TABLE . '
				ORDER BY icons_order';
			$result = $this->db->sql_query($sql);

			$icons = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$icons[$row['icons_id']]['img'] = $row['icons_url'];
				$icons[$row['icons_id']]['width'] = (int) $row['icons_width'];
				$icons[$row['icons_id']]['height'] = (int) $row['icons_height'];
				$icons[$row['icons_id']]['alt'] = ($row['icons_alt']) ? $row['icons_alt'] : '';
				$icons[$row['icons_id']]['display'] = (bool) $row['display_on_posting'];
			}
			$this->db->sql_freeresult($result);

			$this->driver->put('_icons', $icons);
		}

		return $icons;
	}

	/**
	* Obtain ranks
	*/
	function obtain_ranks()
	{
		if (($ranks = $this->driver->get('_ranks')) === false)
		{
			$sql = 'SELECT *
				FROM ' . RANKS_TABLE . '
				ORDER BY rank_min DESC';
			$result = $this->db->sql_query($sql);

			$ranks = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				if ($row['rank_special'])
				{
					unset($row['rank_min']);
					$ranks['special'][$row['rank_id']] = $row;
				}
				else
				{
					$ranks['normal'][$row['rank_id']] = $row;
				}
			}
			$this->db->sql_freeresult($result);

			$this->driver->put('_ranks', $ranks);
		}

		return $ranks;
	}

	/**
	* Obtain allowed extensions
	*
	* @param mixed $forum_id If false then check for private messaging, if int then check for forum id. If true, then only return extension informations.
	*
	* @return array allowed extensions array.
	*/
	function obtain_attach_extensions($forum_id)
	{
		if (($extensions = $this->driver->get('_extensions')) === false)
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
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$extension = strtolower(trim($row['extension']));

				$extensions[$extension] = array(
					'display_cat'	=> (int) $row['cat_id'],
					'download_mode'	=> (int) $row['download_mode'],
					'upload_icon'	=> trim($row['upload_icon']),
					'max_filesize'	=> (int) $row['max_filesize'],
					'allow_group'	=> $row['allow_group'],
					'allow_in_pm'	=> $row['allow_in_pm'],
					'group_name'	=> $row['group_name'],
				);

				$allowed_forums = ($row['allowed_forums']) ? unserialize(trim($row['allowed_forums'])) : array();

				// Store allowed extensions forum wise
				if ($row['allow_group'])
				{
					$extensions['_allowed_post'][$extension] = (!count($allowed_forums)) ? 0 : $allowed_forums;
				}

				if ($row['allow_in_pm'])
				{
					$extensions['_allowed_pm'][$extension] = 0;
				}
			}
			$this->db->sql_freeresult($result);

			$this->driver->put('_extensions', $extensions);
		}

		// Forum post
		if ($forum_id === false)
		{
			// We are checking for private messages, therefore we only need to get the pm extensions...
			$return = array('_allowed_' => array());

			foreach ($extensions['_allowed_pm'] as $extension => $check)
			{
				$return['_allowed_'][$extension] = 0;
				$return[$extension] = $extensions[$extension];
			}

			$extensions = $return;
		}
		else if ($forum_id === true)
		{
			return $extensions;
		}
		else
		{
			$forum_id = (int) $forum_id;
			$return = array('_allowed_' => array());

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
					$return['_allowed_'][$extension] = 0;
					$return[$extension] = $extensions[$extension];
				}
			}

			$extensions = $return;
		}

		if (!isset($extensions['_allowed_']))
		{
			$extensions['_allowed_'] = array();
		}

		return $extensions;
	}

	/**
	* Obtain active bots
	*/
	function obtain_bots()
	{
		if (($bots = $this->driver->get('_bots')) === false)
		{
			switch ($this->db->get_sql_layer())
			{
				case 'mssql_odbc':
				case 'mssqlnative':
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
			$result = $this->db->sql_query($sql);

			$bots = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$bots[] = $row;
			}
			$this->db->sql_freeresult($result);

			$this->driver->put('_bots', $bots);
		}

		return $bots;
	}

	/**
	* Obtain cfg file data
	*/
	function obtain_cfg_items($style)
	{
		$parsed_array = $this->driver->get('_cfg_' . $style['style_path']);

		if ($parsed_array === false)
		{
			$parsed_array = array();
		}

		$filename = $this->phpbb_root_path . 'styles/' . $style['style_path'] . '/style.cfg';

		if (!file_exists($filename))
		{
			return $parsed_array;
		}

		if (!isset($parsed_array['filetime']) || (($this->config['load_tplcompile'] && @filemtime($filename) > $parsed_array['filetime'])))
		{
			// Re-parse cfg file
			$parsed_array = parse_cfg_file($filename);
			$parsed_array['filetime'] = @filemtime($filename);

			$this->driver->put('_cfg_' . $style['style_path'], $parsed_array);
		}

		return $parsed_array;
	}

	/**
	* Obtain disallowed usernames
	*/
	function obtain_disallowed_usernames()
	{
		if (($usernames = $this->driver->get('_disallowed_usernames')) === false)
		{
			$sql = 'SELECT disallow_username
				FROM ' . DISALLOW_TABLE;
			$result = $this->db->sql_query($sql);

			$usernames = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$usernames[] = str_replace('%', '.*?', preg_quote(utf8_clean_string($row['disallow_username']), '#'));
			}
			$this->db->sql_freeresult($result);

			$this->driver->put('_disallowed_usernames', $usernames);
		}

		return $usernames;
	}
}
