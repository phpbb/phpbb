<?php
/**
*
* @package Cache
* @version $Id$
* @copyright (c) 2005 phpBB Group
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
* Class for grabbing/handling cached entries, extends acm_file or acm_db depending on the setup
* @package cache
*/
class cache extends base
{
	/**
	* The config.
	*
	* @var \phpbb\config\config
	*/
	protected $board_config;

	/**
	* Database connection.
	*
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

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
	* @param \phpbb\config\config $board_config The config
	* @param \phpbb\db\driver\driver_interface $db Database connection
	* @param string $phpbb_root_path Root path
	* @param string $php_ext PHP file extension
	*/
	public function __construct()
	{
		global $board_config, $db, $phpbb_root_path;
		$this->config = $board_config;
		$this->db = $db;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = substr(strrchr(__FILE__, '.'), 1);
	}
	
	/**
	* Get config values
	*/
	function obtain_config()
	{
		global $db;

		if (($board_config = $this->get('config')) !== false)
		{
			$sql = 'SELECT config_name, config_value
				FROM ' . CONFIG_TABLE;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$board_config[$row['config_name']] = $row['config_value'];
			}
			$db->sql_freeresult($result);
		}
		else
		{
			$board_config = $cached_config = array();

			$sql = 'SELECT *
				FROM ' . CONFIG_TABLE;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				if (!$row['is_dynamic'])
				{
					$cached_config[$row['config_name']] = $row['config_value'];
				}

				$board_config[$row['config_name']] = $row['config_value'];
			}
			$db->sql_freeresult($result);

			$this->put('config', $cached_config);
		}

		return $board_config;
	}

	/**
	* Obtain list of naughty words and build preg style replacement arrays for use by the
	* calling script
	*/
	function obtain_word_list()
	{
		global $db;

		if (($censors = $this->get('_word_censors')) === false)
		{
			$sql = 'SELECT word, replacement
				FROM ' . WORDS_TABLE;
			$result = $db->sql_query($sql);

			$censors = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$censors['match'][] = get_censor_preg_expression($row['word']);
				$censors['replace'][] = $row['replacement'];
			}
			$db->sql_freeresult($result);

			$this->put('_word_censors', $censors);
		}

		return $censors;
	}

	/**
	* Obtain currently listed icons
	*/
	function obtain_icons()
	{
		if (($icons = $this->get('_icons')) === false)
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

			$this->put('_icons', $icons);
		}

		return $icons;
	}

	/**
	* Obtain ranks
	*/
	function obtain_ranks()
	{
		if (($ranks = $this->get('_ranks')) === false)
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

			$this->put('_ranks', $ranks);
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
		if (($extensions = $this->get('_extensions')) === false)
		{
			global $db;

			$extensions = array(
				'_allowed_post'	=> array(),
				'_allowed_pm'	=> array(),
			);

			// The rule is to only allow those extensions defined. ;)
			$sql = 'SELECT e.extension, g.*
				FROM ' . EXTENSIONS_TABLE . ' e, ' . EXTENSION_GROUPS_TABLE . ' g
				WHERE e.group_id = g.group_id
					AND (g.allow_group = 1 OR g.allow_in_pm = 1)';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
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
			$db->sql_freeresult($result);

			$this->put('_extensions', $extensions);
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
		if (($bots = $this->get('_bots')) === false)
		{
			global $db;

			switch ($db->sql_layer)
			{
				case 'mssql':
				case 'mssql_odbc':
				case 'mssqlnative':
					$sql = 'SELECT user_id, bot_agent, bot_ip
						FROM ' . BOTS_TABLE . '
						WHERE bot_active = 1
					ORDER BY LEN(bot_agent) DESC';
				break;

				case 'firebird':
					$sql = 'SELECT user_id, bot_agent, bot_ip
						FROM ' . BOTS_TABLE . '
						WHERE bot_active = 1
					ORDER BY CHAR_LENGTH(bot_agent) DESC';
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

			$bots = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$bots[] = $row;
			}
			$db->sql_freeresult($result);

			$this->put('_bots', $bots);
		}

		return $bots;
	}

	/**
	* Obtain cfg file data
	*/
	function obtain_cfg_items($theme)
	{
		global $user, $board_config, $phpbb_root_path;

		$parsed_items = array(
			'theme'		=> array(),
			'template'	=> array(),
			'imageset'	=> array()
		);

		foreach ($parsed_items as $key => $parsed_array)
		{
			$parsed_array = $this->get('_cfg_' . $key . '_' . $theme[$key . '_path']);

			if ($parsed_array === false)
			{
				$parsed_array = array();
			}

			$reparse = false;
			$filename = $phpbb_root_path . $user->template_path . $theme[$key . '_path'] . '/' . $key . '/' . $key . '.cfg';

			if (!file_exists($filename))
			{
				continue;
			}

			if (!isset($parsed_array['filetime']) || (($board_config['load_tplcompile'] && @filemtime($filename) > $parsed_array['filetime'])))
			{
				$reparse = true;
			}

			// Re-parse cfg file
			if ($reparse)
			{
				$parsed_array = parse_cfg_file($filename);
				$parsed_array['filetime'] = @filemtime($filename);

				$this->put('_cfg_' . $key . '_' . $theme[$key . '_path'], $parsed_array);
			}
			$parsed_items[$key] = $parsed_array;
		}

		return $parsed_items;
	}

	/**
	* Obtain disallowed usernames
	*/
	function obtain_disallowed_usernames()
	{
		if (($usernames = $this->get('_disallowed_usernames')) === false)
		{
			global $db;

			$sql = 'SELECT disallow_username
				FROM ' . DISALLOW_TABLE;
			$result = $db->sql_query($sql);

			$usernames = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$usernames[] = str_replace('%', '.*?', preg_quote(utf8_clean_string($row['disallow_username']), '#'));
			}
			$db->sql_freeresult($result);

			$this->put('_disallowed_usernames', $usernames);
		}

		return $usernames;
	}

	/**
	* Obtain hooks...
	*/
	function obtain_hooks()
	{
		global $phpbb_root_path, $phpEx;

		if (($hook_files = $this->get('_hooks')) === false)
		{
			$hook_files = array();

			// Now search for hooks...
			$dh = @opendir($phpbb_root_path . 'includes/hooks/');

			if ($dh)
			{
				while (($file = readdir($dh)) !== false)
				{
					if (strpos($file, 'hook_') === 0 && substr($file, -(strlen($phpEx) + 1)) === '.' . $phpEx)
					{
						$hook_files[] = substr($file, 0, -(strlen($phpEx) + 1));
					}
				}
				closedir($dh);
			}

			$this->put('_hooks', $hook_files);
		}

		return $hook_files;
	}
	/**
	* Set cache path
	*/
	function acm()
	{
		$this->cache_dir = defined('MAIN_CACHE_FOLDER') ? MAIN_CACHE_FOLDER : 'cache/';
		$this->cache_dir_sql = defined('SQL_CACHE_FOLDER') ? SQL_CACHE_FOLDER : 'cache/sql/';
		$this->cache_dir_backup = $this->cache_dir;

		$this->cache_dirs = defined('MAIN_CACHE_FOLDER') ? array(MAIN_CACHE_FOLDER, CMS_CACHE_FOLDER, FORUMS_CACHE_FOLDER, POSTS_CACHE_FOLDER, SQL_CACHE_FOLDER, TOPICS_CACHE_FOLDER, USERS_CACHE_FOLDER) : array($this->cache_dir, $this->cache_dir_sql);
	}

	/**
	* Load global cache
	*/
	function load()
	{
		return $this->_read('data_global', $this->cache_dir);
	}

	/**
	* Unload cache object
	*/
	function unload()
	{
		$this->save();
		unset($this->vars);
		unset($this->var_expires);
		unset($this->sql_rowset);
		unset($this->sql_row_pointer);

		$this->vars = array();
		$this->var_expires = array();
		$this->sql_rowset = array();
		$this->sql_row_pointer = array();
	}

	/**
	* Save modified objects
	*/
	function save()
	{
		global $phpEx;
		
		if (!$this->is_modified)
		{
			return;
		}

		if (!$this->_write('data_global', $this->vars, $this->var_expires, $this->cache_dir))
		{
			// Now, this occurred how often? ... phew, just tell the user then...
			if (!@is_writable($this->cache_dir))
			{
				// We need to use die() here, because else we may encounter an infinite loop (the message handler calls $cache->unload())
				die($this->cache_dir . ' is NOT writable.');
				exit;
			}

			die('Not able to open ' . $this->cache_dir . 'data_global.' . $phpEx);
			exit;
		}

		$this->is_modified = false;
	}

	/**
	* Tidy cache
	*/
	function tidy()
	{
		global $phpEx;
		
		foreach ($this->cache_dirs as $cache_folder)
		{
			$cache_folder = $this->validate_cache_folder($cache_folder, false, true);
			$dir = @opendir($cache_folder);

			if (!$dir)
			{
				return;
			}

			$time = time();

			while (($entry = readdir($dir)) !== false)
			{
				if (!preg_match('/^(sql_|data_(?!global))/', $entry))
				{
					continue;
				}

				$expired = $this->is_expired($time, $entry, $cache_folder);
				if ($expired)
				{
					$this->remove_file($entry, false, $cache_folder);
				}
			}
			closedir($dir);
		}

		if (file_exists($this->cache_dir . 'data_global.' . $phpEx))
		{
			if (!sizeof($this->vars))
			{
				$this->load();
			}

			foreach ($this->var_expires as $var_name => $expires)
			{
				if ($time >= $expires)
				{
					$this->destroy($var_name);
				}
			}
		}

		set_config('cron_cache_last_run', time());
	}

	/**
	* Get saved cache object
	*/
	function get($var_name)
	{
		if ($var_name[0] == '_')
		{
			if (!$this->_exists($var_name))
			{
				return false;
			}

			return $this->_read('data' . $var_name, $this->cache_dir);
		}
		else
		{
			return ($this->_exists($var_name)) ? $this->vars[$var_name] : false;
		}
	}

	/**
	* Put data into cache
	*/
	function put($var_name, $var, $ttl = 31536000)
	{
		if ($var_name[0] == '_')
		{
			$this->_write('data' . $var_name, $var, time() + $ttl);
		}
		else
		{
			$this->vars[$var_name] = $var;
			$this->var_expires[$var_name] = time() + $ttl;
			$this->is_modified = true;
		}
	}

	/**
	* Purge cache data
	*/
	function purge()
	{
		// Purge all cache files
		foreach ($this->cache_dirs as $cache_folder)
		{
			$cache_folder = $this->validate_cache_folder($cache_folder, false, true);
			$dir = @opendir($cache_folder);

			if (!$dir)
			{
				return;
			}

			while (($entry = readdir($dir)) !== false)
			{
				if ((strpos($entry, 'sql_') !== 0) && (strpos($entry, 'data_') !== 0) && (strpos($entry, 'ctpl_') !== 0) && (strpos($entry, 'tpl_') !== 0))
				{
					continue;
				}

				$this->remove_file($entry, false, $cache_folder);
			}
			closedir($dir);
		}

		unset($this->vars);
		unset($this->var_expires);
		unset($this->sql_rowset);
		unset($this->sql_row_pointer);

		$this->vars = array();
		$this->var_expires = array();
		$this->sql_rowset = array();
		$this->sql_row_pointer = array();

		$this->is_modified = false;
	}

	/**
	* Destroy cache data
	*/
	function destroy($var_name, $table = '', $cache_folder = '')
	{
		global $phpEx;
		if (($var_name == 'sql') && !empty($table))
		{
			if (!is_array($table))
			{
				$table = array($table);
			}

			$cache_folder = $this->validate_cache_folder($cache_folder, true, false);
			$dir = @opendir($cache_folder);

			if (!$dir)
			{
				return;
			}

			while (($entry = readdir($dir)) !== false)
			{
				if (strpos($entry, 'sql_') !== 0)
				{
					continue;
				}

				$query = $this->get_query_string($entry);

				if (empty($query))
				{
					continue;
				}

				foreach ($table as $check_table)
				{
					// Better catch partial table names than no table names. ;)
					if (strpos($query, $check_table) !== false)
					{
						$this->remove_file($entry, false, $cache_folder);
						break;
					}
				}
			}
			closedir($dir);

			return;
		}

		if (!$this->_exists($var_name))
		{
			return;
		}

		if ($var_name[0] == '_')
		{
			$this->remove_file('data' . $var_name . '.' . $phpEx, true, $this->cache_dir);
		}
		elseif (isset($this->vars[$var_name]))
		{
			$this->is_modified = true;
			unset($this->vars[$var_name]);
			unset($this->var_expires[$var_name]);

			// We save here to let the following cache hits succeed
			$this->save();
		}
	}

	/**
	* Destroy cache data files
	*/
	function destroy_datafiles($datafiles, $cache_folder = '', $prefix = 'data', $prefix_lookup = false)
	{
		global $phpEx;
		$deleted = 0;
		if (empty($datafiles))
		{
			return $deleted;
		}

		$cache_folder = $this->validate_cache_folder($cache_folder, false, true);
		$datafiles = !is_array($datafiles) ? array($datafiles) : $datafiles;

		if (!$prefix_lookup)
		{
			foreach ($datafiles as $datafile)
			{
				$file_deleted = $this->remove_file($prefix . $datafile . '.' . $phpEx, false, $cache_folder);
				$deleted = $file_deleted ? $deleted++ : $deleted;
			}
		}
		else
		{
			$dir = @opendir($cache_folder);

			if (!$dir)
			{
				return;
			}

			while (($entry = readdir($dir)) !== false)
			{
				foreach ($datafiles as $datafile)
				{
					if ((strpos($entry, $prefix . $datafile) === 0) && (substr($entry, -(strlen($phpEx) + 1)) === ('.' . $phpEx)))
					{
						$file_deleted = $this->remove_file($entry, false, $cache_folder);
						$deleted = $file_deleted ? $deleted++ : $deleted;
						break;
					}
				}
			}
		}

		return $deleted;
	}

	/**
	* Check if a given cache entry exist
	*/
	function _exists($var_name)
	{
		global $phpEx;
		if ($var_name[0] == '_')
		{
			return file_exists($this->cache_dir . 'data' . $var_name . '.' . $phpEx);
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
	* Build query Hash
	*/
	function sql_query_hash($query = '')
	{
		return md5($query);
	}

	/**
	* Load cached sql query
	*/
	function sql_load($query, $cache_prefix = '', $cache_folder = '')
	{
		$cache_prefix = 'sql_' . $cache_prefix;
		$cache_folder = $this->validate_cache_folder($cache_folder, true, false);

		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		if (($rowset = $this->_read($cache_prefix . $this->sql_query_hash($query), $cache_folder)) === false)
		{
			return false;
		}

		$this->sql_query_id = sizeof($this->sql_rowset);
		$this->sql_rowset[$this->sql_query_id] = $rowset;
		$this->sql_row_pointer[$this->sql_query_id] = 0;

		return $this->sql_query_id;
	}

	/**
	* Save sql query
	*/
	function sql_save($query, &$query_result, $ttl = CACHE_SQL_EXPIRY, $cache_prefix = '', $cache_folder = '')
	{
		global $db;

		$cache_prefix = 'sql_' . $cache_prefix;
		$cache_folder = $this->validate_cache_folder($cache_folder, true, true);

		// Remove extra spaces and tabs
		//$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		$this->sql_query_id = sizeof($this->sql_rowset);
		$this->sql_rowset[$this->sql_query_id] = array();
		$this->sql_row_pointer[$this->sql_query_id] = 0;

		while ($row = $db->sql_fetchrow($query_result))
		{
			$this->sql_rowset[$this->sql_query_id][] = $row;
		}
		$db->sql_freeresult($query_result);

		if ($this->_write($cache_prefix . $this->sql_query_hash($query), $this->sql_rowset[$this->sql_query_id], time() + $ttl, $query, $cache_folder))
		{
			$query_result = $this->sql_query_id;
		}
	}

	/**
	* Check if a given sql query exist in cache
	*/
	function sql_exists($query_id)
	{
		return isset($this->sql_rowset[$query_id]);
	}

	/**
	* Fetch row from cache (database)
	*/
	function sql_fetchrow($query_id)
	{
		if ($this->sql_row_pointer[$query_id] < sizeof($this->sql_rowset[$query_id]))
		{
			return $this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]++];
		}

		return false;
	}

	/**
	* Fetch a field from the current row of a cached database result (database)
	*/
	function sql_fetchfield($query_id, $field)
	{
		if ($this->sql_row_pointer[$query_id] < sizeof($this->sql_rowset[$query_id]))
		{
			return (isset($this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]][$field])) ? $this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]++][$field] : false;
		}

		return false;
	}

	/**
	* Seek a specific row in an a cached database result (database)
	*/
	function sql_rowseek($rownum, $query_id)
	{
		if ($rownum >= sizeof($this->sql_rowset[$query_id]))
		{
			return false;
		}

		$this->sql_row_pointer[$query_id] = $rownum;
		return true;
	}

	/**
	* Free memory used for a cached database result (database)
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

	/**
	* Read cached data from a specified file
	*
	* @access private
	* @param string $filename Filename to write
	* @return mixed False if an error was encountered, otherwise the data type of the cached data
	*/
	function _read($filename, $cache_folder = '')
	{
		global $phpEx;
		
		if (!empty($this->use_old_ip_cache))
		{
			return $this->_read_ip($filename, $cache_folder);
		}

		$cache_folder = $this->validate_cache_folder($cache_folder, false, false);
		$file = $cache_folder . $filename . '.' . $phpEx;

		$type = substr($filename, 0, strpos($filename, '_'));

		if (!file_exists($file))
		{
			return false;
		}

		if (!($handle = @fopen($file, 'rb')))
		{
			return false;
		}

		// Skip the PHP header
		fgets($handle);

		if ($filename == 'data_global')
		{
			$this->vars = $this->var_expires = array();

			$time = time();

			while (($expires = (int) fgets($handle)) && !feof($handle))
			{
				// Number of bytes of data
				$bytes = substr(fgets($handle), 0, -1);

				if (!is_numeric($bytes) || ($bytes = (int) $bytes) === 0)
				{
					// We cannot process the file without a valid number of bytes so we discard it
					fclose($handle);

					$this->vars = $this->var_expires = array();
					$this->is_modified = false;

					$this->remove_file($file, false, $cache_folder);

					return false;
				}

				if ($time >= $expires)
				{
					fseek($handle, $bytes, SEEK_CUR);

					continue;
				}

				$var_name = substr(fgets($handle), 0, -1);

				// Read the length of bytes that consists of data.
				$data = fread($handle, $bytes - strlen($var_name));
				$data = @unserialize($data);

				// Don't use the data if it was invalid
				if ($data !== false)
				{
					$this->vars[$var_name] = $data;
					$this->var_expires[$var_name] = $expires;
				}

				// Absorb the LF
				fgets($handle);
			}

			fclose($handle);

			$this->is_modified = false;

			return true;
		}
		else
		{
			$data = false;
			$line = 0;

			while (($buffer = fgets($handle)) && !feof($handle))
			{
				$buffer = substr($buffer, 0, -1); // Remove the LF

				// $buffer is only used to read integers
				// if it is non numeric we have an invalid
				// cache file, which we will now remove.
				if (!is_numeric($buffer))
				{
					break;
				}

				if ($line == 0)
				{
					$expires = (int) $buffer;

					if (time() >= $expires)
					{
						break;
					}

					if ($type == 'sql')
					{
						// Skip the query
						fgets($handle);
					}
				}
				elseif ($line == 1)
				{
					$bytes = (int) $buffer;

					// Never should have 0 bytes
					if (!$bytes)
					{
						break;
					}

					// Grab the serialized data
					$data = fread($handle, $bytes);

					// Read 1 byte, to trigger EOF
					fread($handle, 1);

					if (!feof($handle))
					{
						// Somebody tampered with our data
						$data = false;
					}
					break;
				}
				else
				{
					// Something went wrong
					break;
				}
				$line++;
			}
			fclose($handle);

			// unserialize if we got some data
			$data = ($data !== false) ? @unserialize($data) : $data;

			if ($data === false)
			{
				$this->remove_file($file, false, $cache_folder);
				return false;
			}

			return $data;
		}
	}

	/**
	* Write cache data to a specified file
	*
	* 'data_global' is a special case and the generated format is different for this file:
	* <code>
	* < ? php exit; ? >
	* (expiration)
	* (length of var and serialised data)
	* (var)
	* (serialised data)
	* ... (repeat)
	* </code>
	*
	* The other files have a similar format:
	* <code>
	* < ? php exit; ? >
	* (expiration)
	* (query) [SQL files only]
	* (length of serialised data)
	* (serialised data)
	* </code>
	*
	* @access private
	* @param string $filename Filename to write
	* @param mixed $data Data to store
	* @param int $expires Timestamp when the data expires
	* @param string $query Query when caching SQL queries
	* @return bool True if the file was successfully created, otherwise false
	*/
	function _write($filename, $data = null, $expires = 0, $query = '', $cache_folder = '')
	{
		global $phpEx;
		
		if (!empty($this->use_old_ip_cache))
		{
			return $this->_write_ip($filename, $data, $expires, $query, $cache_folder);
		}

		$cache_folder = $this->validate_cache_folder($cache_folder, false, false);
		$file = $cache_folder . $filename . '.' . $phpEx;

		if ($handle = @fopen($file, 'wb'))
		{
			@flock($handle, LOCK_EX);

			// File header
			fwrite($handle, '<' . '?php exit; ?' . '>');

			if ($filename == 'data_global')
			{
				// Global data is a different format
				foreach ($this->vars as $var => $data)
				{
					if ((strpos($var, "\r") !== false) || (strpos($var, "\n") !== false))
					{
						// CR/LF would cause fgets() to read the cache file incorrectly
						// do not cache test entries, they probably won't be read back
						// the cache keys should really be alphanumeric with a few symbols.
						continue;
					}
					$data = serialize($data);

					// Write out the expiration time
					fwrite($handle, "\n" . $this->var_expires[$var] . "\n");

					// Length of the remaining data for this var (ignoring two LF's)
					fwrite($handle, strlen($data . $var) . "\n");
					fwrite($handle, $var . "\n");
					fwrite($handle, $data);
				}
			}
			else
			{
				fwrite($handle, "\n" . $expires . "\n");

				if (strpos($filename, 'sql_') === 0)
				{
					fwrite($handle, $query . "\n");
				}
				$data = serialize($data);

				fwrite($handle, strlen($data) . "\n");
				fwrite($handle, $data);
			}

			@flock($handle, LOCK_UN);
			fclose($handle);

			if (!function_exists('phpbb_chmod'))
			{
				include(PHPBB_ROOT_PATH . 'includes/functions.' . $phpEx);
			}

			phpbb_chmod($file, CHMOD_READ | CHMOD_WRITE);

			return true;
		}

		return false;
	}

	/**
	* Read cached data (IP Version)
	*
	* @access private
	* @param string $filename Filename to write
	* @return mixed False if an error was encountered, otherwise the data type of the cached data
	*/
	function _read_ip($filename, $cache_folder = '')
	{
		global $phpEx;
		
		$cache_folder = $this->validate_cache_folder($cache_folder, false, false);
		$file = $cache_folder . $filename . '.' . $phpEx;

		if (file_exists($file))
		{
			@include($file);
			if (!empty($expired))
			{
				$this->remove_file($filename . '.' . $phpEx, true, $cache_folder);
				return false;
			}
		}
		else
		{
			return false;
		}

		if ($filename == 'data_global')
		{
			return true;
		}
		else
		{
			return (isset($data)) ? $data : false;
		}
	}

	/**
	* Write cache data to a specified file (IP Version)
	*
	* @access private
	* @param string $filename Filename to write
	* @param mixed $data Data to store
	* @param int $expires Timestamp when the data expires
	* @param string $query Query when caching SQL queries
	* @return bool True if the file was successfully created, otherwise false
	*/
	function _write_ip($filename, $data = null, $expires = 0, $query = '', $cache_folder = '')
	{
		global $phpEx;
		
		$cache_folder = $this->validate_cache_folder($cache_folder, false, false);
		$file = $cache_folder . $filename . '.' . $phpEx;

		if ($fp = @fopen($file, 'wb'))
		{
			@flock($fp, LOCK_EX);

			$file_content = "<" . "?php\nif (!defined('IN_ICYPHOENIX')) exit;\n\n";
			$file_content .= "\$created = " . time() . "; // " . gmdate('Y/m/d - H:i:s') . "\n";
			if ($filename == 'data_global')
			{
				$file_content .= "\n\$this->vars = " . var_export($data, true) . ";\n";
				$file_content .= "\n\$this->var_expires = " . var_export($expires, true) . ";\n";
			}
			elseif (!empty($query))
			{
				$file_content .= "/* " . str_replace('*/', '*\/', $query) . " */\n";
				$file_content .= "\$expired = (time() >= " . $expires . ") ? true : false;\nif (\$expired) { return; }\n";
				$file_content .= "\n\$this->sql_rowset[\$this->sql_query_id] = " . (sizeof($this->sql_rowset[$this->sql_query_id]) ? "unserialize(" . var_export(serialize($this->sql_rowset[$this->sql_query_id]), true) . ");" : 'array();') . "\n";
			}
			else
			{
				$file_content .= "\$expired = (time() >= " . $expires . ") ? true : false;\nif (\$expired) { return; }\n";
				$file_content .= "\n\$data = " . (sizeof($data) ? "unserialize(" . var_export(serialize($data), true) . ");" : 'array();') . "\n";
			}
			$file_content .= "\n?" . ">";

			fwrite($fp, $file_content);
			@flock($fp, LOCK_UN);
			fclose($fp);

			if (!function_exists('phpbb_chmod'))
			{
				include(PHPBB_ROOT_PATH . 'includes/functions.' . $phpEx);
			}

			phpbb_chmod($file, CHMOD_WRITE);

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Removes/unlinks file
	*/
	function remove_file($filename, $check = false, $cache_folder = '')
	{
		$cache_folder = $this->validate_cache_folder($cache_folder, false, false);
		$cache_filename = $cache_folder . $filename;
		if (@file_exists($cache_filename))
		{
			$file_unlink = @unlink($cache_filename);
			if ($check && !$file_unlink && !@is_writable($cache_folder))
			{
				// Better avoid calling trigger_error
				die('Unable to remove ' . $cache_filename . '. Please check directory permissions.');
			}
			return $file_unlink;
		}

		return true;
	}

	/**
	* Checks cache folder
	*/
	function validate_cache_folder($cache_folder, $is_sql = false, $deep_check = false)
	{

		$default_cache_folder = (!empty($is_sql) ? $this->cache_dir_sql : $this->cache_dir);
		$cache_folder = (is_array($cache_folder) && in_array($cache_folder, $this->cache_dirs)) ? $cache_folder : $default_cache_folder;
		if (!empty($deep_check))
		{
			$cache_folder = @is_dir($cache_folder) ? $cache_folder : $default_cache_folder;
			// This part of code should should ensure realpath folder identified...
			$cache_folder = @is_dir($cache_folder) ? $cache_folder : @phpbb_realpath($cache_folder);
		}

		return $cache_folder;
	}

	/**
	* Checks if cache expired
	*/
	function is_expired($time, $filename, $cache_folder = '')
	{
		$cache_folder = $this->validate_cache_folder($cache_folder, false, false);

		if (!file_exists($cache_folder . $filename))
		{
			return false;
		}

		if (!empty($this->use_old_ip_cache))
		{
			$expired = true;
			@include($cache_folder . $filename);
			return (!empty($expired) ? true : false);
		}
		else
		{
			if (!($handle = @fopen($cache_folder . $filename, 'rb')))
			{
				return true;
			}

			// Skip the PHP header
			fgets($handle);

			// Skip expiration
			$expires = (int) fgets($handle);

			fclose($handle);

			$expired = ($time >= $expires) ? true : false;
			return (!empty($expired) ? true : false);
		}
	}

	/**
	* Gets query string
	*/
	function get_query_string($filename, $cache_folder = '')
	{
		$cache_folder = $this->validate_cache_folder($cache_folder, false, false);

		if (!empty($this->use_old_ip_cache))
		{
			$check_line = @file_get_contents($cache_folder . $filename);

			if (empty($check_line))
			{
				return false;
			}

			// Now get the contents between /* and */
			$query = substr($check_line, strpos($check_line, '/* ') + 3, strpos($check_line, ' */') - strpos($check_line, '/* ') - 3);
		}
		else
		{
			if (!($handle = @fopen($cache_folder . $filename, 'rb')))
			{
				return false;
			}

			// Skip the PHP header
			fgets($handle);

			// Skip expiration
			fgets($handle);

			// Grab the query, remove the LF
			$query = substr(fgets($handle), 0, -1);

			fclose($handle);
		}

		return $query;
	}
	
	/*
	* Get default style
	*/
	function obtain_default_style($from_cache = false)
	{
		global $db, $board_config;

		if (($default_style = $this->get('config_style')) === false)
		{
			$default_style = array();
			$style_id = (int) $board_config['default_style'];
			$default_style = get_style($style_id, $from_cache);

			$this->put('config_style', $default_style);
		}

		return $default_style;
	}

	/*
	* Get newest user
	*/
	function obtain_newest_user()
	{
		global $board_config;

		if (($newest_user = $this->get('newest_user')) === false)
		{
			$newest_user = colorize_username($board_config['last_user_id']);

			$this->put('newest_user', $newest_user);
		}

		return $newest_user;
	}

	/*
	* Get moderators
	*/
	function obtain_moderators($from_cache = false)
	{
		global $db, $board_config;

		if (($moderators = $this->get('_moderators')) === false)
		{
			$moderators = array();

			//
			// Obtain list of moderators of each forum
			// First users, then groups ... broken into two queries
			//
			$sql = "SELECT aa.forum_id, u.user_id, u.username, u.user_active, u.user_color
					FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g, " . USERS_TABLE . " u
					WHERE aa.auth_mod = " . TRUE . "
						AND g.group_single_user = 1
						AND ug.group_id = aa.group_id
						AND g.group_id = aa.group_id
						AND u.user_id = ug.user_id
					GROUP BY u.user_id, u.username, aa.forum_id
					ORDER BY aa.forum_id, u.user_id";
			$result = $from_cache ? $db->sql_query($sql, 0, 'moderators_') : $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$moderators['users'][] = $row;
			}
			$db->sql_freeresult($result);

			$sql = "SELECT aa.forum_id, g.group_id, g.group_name, g.group_color
					FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g
					WHERE aa.auth_mod = " . TRUE . "
						AND g.group_single_user = 0
						AND g.group_type <> " . GROUP_HIDDEN . "
						AND ug.group_id = aa.group_id
						AND g.group_id = aa.group_id
					GROUP BY g.group_id, g.group_name, aa.forum_id
					ORDER BY aa.forum_id, g.group_id";
				$result = $from_cache ? $db->sql_query($sql, 0, 'moderators_') : $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$moderators['groups'][] = $row;
			}
			$db->sql_freeresult($result);

			$this->put('_moderators', $moderators);
		}

		return $moderators;
	}

	/*
	* Get smileys
	*/
	function obtain_smileys($from_cache = false)
	{
		global $db, $board_config;

		if (($smileys = $this->get('_smileys')) === false)
		{
			$smileys = array();
			$smileys_path = create_server_url() . $board_config['smilies_path'] . '/';

			$sql = "SELECT code, smile_url FROM " . SMILIES_TABLE . " ORDER BY smilies_order";
			$result = $from_cache ? $db->sql_query($sql, 0, 'smileys_') : $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$smileys[] = array(
					'code' => $row['code'],
					'replace' => '<img src="' . $smileys_path . $row['smile_url'] . '" alt="" />'
				);
			}
			$db->sql_freeresult($result);

			$this->put('_smileys', $smileys);
		}

		return $smileys;
	}

	/*
	* Get styles
	*/
	function obtain_styles($from_cache = false)
	{
		global $db;

		if (($styles = $this->get('_styles')) === false)
		{
			$styles = array();
			//$sql = "SELECT * FROM " . THEMES_TABLE . " ORDER BY style_name, themes_id";
			$sql = "SELECT themes_id, style_name FROM " . THEMES_TABLE . " ORDER BY LOWER(style_name), themes_id";
			$result = $from_cache ? $db->sql_query($sql, 0, 'styles_') : $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$styles[$row['themes_id']] = $row['style_name'];
			}
			$db->sql_freeresult($result);

			$this->put('_styles', $styles);
		}

		return $styles;
	}

	/*
	* Get bbcodes
	*/
	function obtain_bbcodes($from_cache = false)
	{
		global $db, $board_config;

		if (($bbcodes = $this->get('_bbcodes')) === false)
		{
			$bbcodes = array();

			$sql = "SELECT * FROM " . BBCODES_TABLE . " ORDER BY bbcode_id";
			$result = $from_cache ? $db->sql_query($sql, 0, 'bbcodes_') : $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$bbcodes[] = $row;
			}
			$db->sql_freeresult($result);

			$this->put('_bbcodes', $bbcodes);
		}

		return $bbcodes;
	}

	/*
	* Get plugins config values
	*/
	function obtain_plugins_config($from_cache = false)
	{
		global $db;

		if (($board_config = $this->get('config_plugins')) === false)
		{
			$board_config = array();

			$sql = "SELECT * FROM " . PLUGINS_TABLE . " ORDER BY plugin_name";
			$result = $from_cache ? $db->sql_query($sql, 0, 'config_plugins_') : $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$board_config[$row['plugin_name']] = $row;
			}
			$db->sql_freeresult($result);

			$this->put('config_plugins', $board_config);
		}

		return $board_config;
	}

	/**
	* Get today visitors
	*/
	function obtain_today_visitors()
	{
		global $db, $board_config, $lang, $user;

		if (($today_visitors = $this->get('_today_visitors_' . $board_config['board_timezone'] . '_' . $user->data['user_level'])) === false)
		{

			$today_visitors['admins'] = '';
			$today_visitors['mods'] = '';
			$today_visitors['users'] = '';
			$today_visitors['reg_hidden'] = 0;
			$today_visitors['reg_visible'] = 0;
			$today_visitors['last_hour'] = 0;

			$time_now = time();
			$time1Hour = $time_now - 3600;
			$minutes = gmdate('is', $time_now);
			$hour_now = $time_now - (60 * ($minutes[0] . $minutes[1])) - ($minutes[2] . $minutes[3]);
			$dato = create_date('H', $time_now, $board_config['board_timezone']);
			$timetoday = $hour_now - (3600 * $dato);
			$sql = 'SELECT session_ip, MAX(session_time) as session_time
							FROM ' . SESSIONS_TABLE . '
							WHERE session_user_id="' . ANONYMOUS . '"
								AND session_time >= ' . $timetoday . '
								AND session_time < ' . ($timetoday + 86399) . '
							GROUP BY session_ip';
			$result = $db->sql_query($sql);

			while($guest_list = $db->sql_fetchrow($result))
			{
				if ($guest_list['session_time'] > $time1Hour)
				{
					$today_visitors['last_hour']++;
				}
			}
			$today_visitors['total_guests'] = $db->sql_numrows($result);
			$db->sql_freeresult($result);

			// Changed sorting by username_clean instead of username
			$sql = 'SELECT user_id, username, user_active, user_color, user_allow_viewonline, user_level, user_lastvisit
							FROM ' . USERS_TABLE . '
							WHERE user_id != "' . ANONYMOUS . '"
								AND user_session_time >= ' . $timetoday . '
								AND user_session_time < ' . ($timetoday + 86399) . '
							ORDER BY username_clean';
			$result = $db->sql_query($sql);

			while($todayrow = $db->sql_fetchrow($result))
			{
				$todayrow['user_level'] = ($todayrow['user_level'] == JUNIOR_ADMIN) ? ADMIN : $todayrow['user_level'];
				$style_color = '';
				if ($todayrow['user_lastvisit'] >= $time1Hour)
				{
					$today_visitors['last_hour']++;
				}
				$colored_user = colorize_username($todayrow['user_id'], $todayrow['username'], $todayrow['user_color'], $todayrow['user_active']);
				$colored_user = (($todayrow['user_allow_viewonline']) ? $colored_user : (($user->data['user_level'] == ADMIN) ? '<i>' . $colored_user . '</i>' : ''));
				if ($todayrow['user_allow_viewonline'] || ($user->data['user_level'] == ADMIN))
				{
					switch ($todayrow['user_level'])
					{
						case ADMIN:
							$today_visitors['admins'] .= (empty($today_visitors['admins']) ? '' : ', ') . $colored_user;
						break;
						case MOD:
							$today_visitors['mods'] .= (empty($today_visitors['mods']) ? '' : ', ') . $colored_user;
						break;
						default:
							$today_visitors['users'] .= (empty($today_visitors['users']) ? '' : ', ') . $colored_user;
						break;
					}
				}

				if (!$todayrow['user_allow_viewonline'])
				{
					$today_visitors['reg_hidden']++;
				}
				else
				{
					$today_visitors['reg_visible']++;
				}
			}

			$today_visitors['total_users'] = $db->sql_numrows($result) + $today_visitors['total_guests'];
			$db->sql_freeresult($result);

			//You can set once per day... but that is too restrictive... better once every hour!
			//$cache_expiry = create_date_midnight(time(), $board_config['board_timezone']) - time() + 86400;
			$cache_expiry = 3600 - ((int) gmdate('i') * 60) - (int) gmdate('s');
			$this->put('_today_visitors_' . $board_config['board_timezone'] . '_' . $user->data['user_level'], $today_visitors, $cache_expiry);
		}

		return $today_visitors;
	}

	/**
	* Obtain fonts files...
	*/
	function obtain_fonts()
	{
		if (($fonts_files = $this->get('_fonts')) === false)
		{
			$fonts_files = array();

			// Now search for fonts...
			$dir = @opendir(FONTS_DIR);

			if ($dir)
			{
				while (($file = @readdir($dir)) !== false)
				{
					if ((substr($file, -4) === '.otf') || (substr($file, -4) === '.ttf'))
					{
						//$fonts_files[] = substr($file, 0, -4);
						$fonts_files[] = $file;
					}
				}
				@closedir($dir);
			}

			$this->put('_fonts', $fonts_files);
		}

		return $fonts_files;
	}

	/**
	* Obtain settings files...
	*/
	function obtain_settings()
	{
		if (($settings_files = $this->get('_settings')) === false)
		{
			$settings_files = array();

			// Now search for settings...
			$dir = @opendir(PHPBB_ROOT_PATH . 'includes/' . SETTINGS_PATH);

			if ($dir)
			{
				while (($file = @readdir($dir)) !== false)
				{
					if ((strpos($file, 'settings_') === 0) && (substr($file, -(strlen($phpEx) + 1)) === '.' . $phpEx))
					{
						$settings_files[] = substr($file, 0, -(strlen($phpEx) + 1));
					}
				}
				@closedir($dir);
			}

			$this->put('_settings', $settings_files);
		}

		return $settings_files;
	}

	/**
	* Obtain lang files...
	*/
	function obtain_lang_files()
	{
		global $board_config, $phpEx;

		if (($lang_files = $this->get('_lang_' . $board_config['default_lang'])) === false)
		{
			$lang_files = array();

			// Now search for langs...
			$dir = @opendir(PHPBB_ROOT_PATH . 'language/lang_' . $board_config['default_lang'] . '/');

			if ($dir)
			{
				while (($file = @readdir($dir)) !== false)
				{
					if ((strpos($file, 'lang_extend_') === 0) && (substr($file, -(strlen($phpEx) + 1)) === '.' . $phpEx))
					{
						$lang_files[] = substr($file, 0, -(strlen($phpEx) + 1));
					}
				}
				@closedir($dir);
			}

			$this->put('_lang_' . $board_config['default_lang'], $lang_files);
		}

		return $lang_files;
	}

	/**
	* Obtain avatars size...
	*/
	function obtain_avatars_size()
	{
		global $board_config, $user, $lang;

		$avatar_dir_size_string = '';
		$avatar_dir_size = 0;

		if (($avatar_dir_size_string = $this->get('_avatars_size')) === false)
		{
			$avatars_path = PHPBB_ROOT_PATH . $board_config['avatar_path'];
			$allowed_avatars_ext = array('gif', 'jpg', 'jpeg', 'png');

			// Now search for avatars...
			$dir = @opendir($avatars_path);

			if ($dir)
			{
				while (($file = @readdir($dir)) !== false)
				{
					if (!@is_dir($file) && !@is_link($file) && ($file != '.') && ($file != '..'))
					{
						$file_ext = substr(strrchr($file, '.'), 1);
						if (in_array($file_ext, $allowed_avatars_ext))
						{
							$avatar_dir_size += @filesize($avatars_path . '/' . $file);
						}
					}
				}
				@closedir($dir);

				$avatar_dir_size_string = get_formatted_filesize($avatar_dir_size);
			}
			else
			{
				$avatar_dir_size_string = $lang['Not_available'];
			}

			$this->put('_avatars_size', $avatar_dir_size_string);
		}

		return $avatar_dir_size_string;
	}
	
}

/**#@+
 * Class request_vars specific definitions
 *
 * Following flags are options for the $type parameter in method _read()
 *
 */
define('MX_TYPE_ANY'		, 0);		// Retrieve the get/post var as-is (only stripslashes() will be applied).
define('MX_TYPE_INT'		, 1);		// Be sure we get a request var of type INT.
define('MX_TYPE_FLOAT'		, 2);		// Be sure we get a request var of type FLOAT.
define('MX_TYPE_NO_HTML'	, 4);		// Be sure we get a request var of type STRING (htmlspecialchars).
define('MX_TYPE_NO_TAGS'	, 8);		// Be sure we get a request var of type STRING (strip_tags + htmlspecialchars).
define('MX_TYPE_NO_STRIP'	, 16);		// By default strings are slash stripped, this flag avoids this.
define('MX_TYPE_SQL_QUOTED'	, 32);		// Be sure we get a request var of type STRING, safe for SQL statements (single quotes escaped)
define('MX_TYPE_POST_VARS'	, 64);		// Read a POST variable.
define('MX_TYPE_GET_VARS'	, 128);		// Read a GET variable.
define('MX_NOT_EMPTY'		, true);	//
/**#@-*/

/**
 * Class: request_vars.
 *
 * This is the CORE request vars object. Encapsulate several functions related to GET/POST variables.
 * More than one flag can specified by OR'ing the $type argument. Examples:
 * - For instance, we could use ( MX_TYPE_POST_VARS | MX_TYPE_GET_VARS ), see method request().
 * - or we could use ( MX_TYPE_NO_TAGS | MX_TYPE_SQL_QUOTED ).
 * - However, MX_TYPE_NO_HTML and MX_TYPE_NO_TAGS can't be specified at a time (defaults to MX_TYPE_NO_TAGS which is more restritive).
 * - Also, MX_TYPE_INT and MX_TYPE_FLOAT ignore flags MX_TYPE_NO_*
 * Usage examples:
 * - $mode = $request->post('mode', MX_TYPE_NO_TAGS, '');
 * - $page_id = $request->get('page', MX_TYPE_INT, 1);
 * This class IS instatiated in common.php ;-)
 *
 * @access public
 * @author Markus Petrux (c) 2003-2005
 * @author Jon Ohlsson (c) 2005-2007
 * @author FlorinCB (c) 2007-2009
 * @package Core
 */
class request_vars
{
	/**#@+
	* Constant identifying the super global with the same name.
	*/
	const POST = 0;
	const GET = 1;
	const REQUEST = 2;
	const COOKIE = 3;
	const SERVER = 4;
	const FILES = 5;
	/**#@-*/		
	
	//
	// Implementation Conventions:
	// Properties and methods prefixed with underscore are intented to be private. ;-)
	//
	
	/**
	* @var	array	The names of super global variables that this class should protect if super globals are disabled.
	*/
	protected $super_globals = array(
		self::POST 		=> '_POST',
		self::GET 		=> '_GET',
		self::REQUEST 	=> '_REQUEST',
		self::COOKIE 	=> '_COOKIE',
		self::SERVER 	=> '_SERVER',
		self::FILES 	=> '_FILES',
	);
	
	/**
	* @var	array	Stores original contents of $_REQUEST array.
	*/
	protected $original_request = null;

	/**
	* @var
	*/
	protected $super_globals_disabled = false;

	/**
	* @var	array	An associative array that has the value of super global constants as keys and holds their data as values.
	*/
	protected $input;

	/**
	* @var	\phpbb\request\type_cast_helper_interface	An instance of a type cast helper providing convenience methods for type conversions.
	*/
	protected $type_cast_helper;	
	
	// ------------------------------
	// Properties
	//

	/* ------------------------------
	* Constructor
	* Initialises the request class, that means it stores all input data in {@link $input input}
	* and then calls {@link deactivated_super_global deactivated_super_global}
	*/
	public function __construct($disable_super_globals = false)
	{
		foreach ($this->super_globals as $const => $super_global)
		{
			$this->input[$const] = isset($GLOBALS[$super_global]) ? $GLOBALS[$super_global] : array();
		}

		// simulate request_order = GP
		$this->original_request = $this->input[self::REQUEST];
		$this->input[self::REQUEST] = $this->input[self::POST] + $this->input[self::GET];

		if ($disable_super_globals)
		{
			$this->disable_super_globals();
		}
	}

	/**
	* Getter for $super_globals_disabled
	*
	* @return	bool	Whether super globals are disabled or not.
	*/
	public function super_globals_disabled()
	{
		return $this->super_globals_disabled;
	}

	/**
	* Disables access of super globals specified in $super_globals.
	* This is achieved by overwriting the super globals with instances of {@link \phpbb\request\deactivated_super_global \phpbb\request\deactivated_super_global}
	*/
	public function disable_super_globals()
	{
		if (!$this->super_globals_disabled)
		{
			foreach ($this->super_globals as $const => $super_global)
			{
				unset($GLOBALS[$super_global]);
				$GLOBALS[$super_global] = new deactivated_super_global($this, $super_global, $const);
			}

			$this->super_globals_disabled = true;
		}
	}

	/**
	* Enables access of super globals specified in $super_globals if they were disabled by {@link disable_super_globals disable_super_globals}.
	* This is achieved by making the super globals point to the data stored within this class in {@link $input input}.
	*/
	public function enable_super_globals()
	{
		if ($this->super_globals_disabled)
		{
			foreach ($this->super_globals as $const => $super_global)
			{
				$GLOBALS[$super_global] = $this->input[$const];
			}

			$GLOBALS['_REQUEST'] = $this->original_request;

			$this->super_globals_disabled = false;
		}
	}
	
	// ------------------------------
	// Public Methods
	//
	
	/**
	* This function allows overwriting or setting a value in one of the super global arrays.
	*
	* Changes which are performed on the super globals directly will not have any effect on the results of
	* other methods this class provides. Using this function should be avoided if possible! It will
	* consume twice the the amount of memory of the value
	*
	* @param	string	$var_name	The name of the variable that shall be overwritten
	* @param	mixed	$value		The value which the variable shall contain.
	* 								If this is null the variable will be unset.
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	* 								Specifies which super global shall be changed
	*/
	public function overwrite($var_name, $value, $super_global = self::REQUEST)
	{
		if (!isset($this->super_globals[$super_global]))
		{
			return;
		}

		$this->type_cast_helper->add_magic_quotes($value);

		// setting to null means unsetting
		if ($value === null)
		{
			unset($this->input[$super_global][$var_name]);
			if (!$this->super_globals_disabled())
			{
				unset($GLOBALS[$this->super_globals[$super_global]][$var_name]);
			}
		}
		else
		{
			$this->input[$super_global][$var_name] = $value;
			if (!$this->super_globals_disabled())
			{
				$GLOBALS[$this->super_globals[$super_global]][$var_name] = $value;
			}
		}
	}
	
	// ------------------------------
	// Private Methods
	//

	/**
	 * Function: _read().
	 *
	 * Get the value of the specified request var (post or get) and force the result to be
	 * of specified type. It might also transform the result (stripslashes, htmlspecialchars) for security
	 * purposes. It all depends on the $type argument.
	 * If the specified request var does not exist, then the default ($dflt) value is returned.
	 * Note the $type argument behaves as a bit array where more than one option can be specified by OR'ing
	 * the passed argument. This is tipical practice in languages like C, but it can also be done with PHP.
	 *
	 * @access private
	 * @param unknown_type $var
	 * @param unknown_type $type
	 * @param unknown_type $dflt
	 * @return unknown
	 */
	public function _read($var, $type = MX_TYPE_ANY, $dflt = '', $not_null = false)
	{
		if( ($type & (MX_TYPE_POST_VARS|MX_TYPE_GET_VARS)) == 0 )
		{
			$type |= (MX_TYPE_POST_VARS|MX_TYPE_GET_VARS);
		}

		if( ($type & MX_TYPE_POST_VARS) && isset($_POST[$var]) ||
			($type & MX_TYPE_GET_VARS)  && isset($_GET[$var]) )
		{
			$val = ( ($type & MX_TYPE_POST_VARS) && isset($_POST[$var]) ? $_POST[$var] : $_GET[$var] );
			if( !($type & MX_TYPE_NO_STRIP) )
			{
				if( is_array($val) )
				{
					foreach( $val as $k => $v )
					{
						$val[$k] = trim(stripslashes($v));
					}
				}
				else
				{
					$val = trim(stripslashes($val));
				}
			}
		}
		else
		{
			$val = $dflt;
		}

		if( $type & MX_TYPE_INT )		// integer
		{
			return $not_null && empty($val) ? $dflt : intval($val);
		}

		if( $type & MX_TYPE_FLOAT )		// float
		{
			return $not_null && empty($val) ? $dflt : floatval($val);
		}

		if( $type & MX_TYPE_NO_TAGS )	// ie username
		{
			if( is_array($val) )
			{
				foreach( $val as $k => $v )
				{
					$val[$k] = htmlspecialchars(strip_tags(ltrim(rtrim($v, " \t\n\r\0\x0B\\"))));
				}
			}
			else
			{
				$val = htmlspecialchars(strip_tags(ltrim(rtrim($val, " \t\n\r\0\x0B\\"))));
			}
		}
		elseif( $type & MX_TYPE_NO_HTML )	// no slashes nor html
		{
			if( is_array($val) )
			{
				foreach( $val as $k => $v )
				{
					$val[$k] = htmlspecialchars(ltrim(rtrim($v, " \t\n\r\0\x0B\\")));
				}
			}
			else
			{
				$val = htmlspecialchars(ltrim(rtrim($val, " \t\n\r\0\x0B\\")));
			}
		}

		if( $type & MX_TYPE_SQL_QUOTED )
		{
			if( is_array($val) )
			{
				foreach( $val as $k => $v )
				{
					$val[$k] = str_replace(($type & MX_TYPE_NO_STRIP ? "\'" : "'"), "''", $v);
				}
			}
			else
			{
				$val = str_replace(($type & MX_TYPE_NO_STRIP ? "\'" : "'"), "''", $val);
			}
		}

		return $not_null && empty($val) ? $dflt : $val;
	}

	// ------------------------------
	// Public Methods
	//

	/**
	* Central type safe input handling function.
	* All variables in GET or POST requests should be retrieved through this function to maximise security.
	*
	* @param	string|array	$var_name	The form variable's name from which data shall be retrieved.
	* 										If the value is an array this may be an array of indizes which will give
	* 										direct access to a value at any depth. E.g. if the value of "var" is array(1 => "a")
	* 										then specifying array("var", 1) as the name will return "a".
	* @param	mixed			$default	A default value that is returned if the variable was not set.
	* 										This function will always return a value of the same type as the default.
	* @param	bool			$multibyte	If $default is a string this parameter has to be true if the variable may contain any UTF-8 characters
	*										Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	* 										Specifies which super global should be used
	*
	* @return	mixed	The value of $_REQUEST[$var_name] run through {@link set_var set_var} to ensure that the type is the
	*					the same as that of $default. If the variable is not set $default is returned.
	*/
	public function variable($var_name, $default, $multibyte = false, $super_global = self::REQUEST)
	{
		return $this->_variable($var_name, $default, $multibyte, $super_global, true);
	}

	/**
	* Get a variable, but without trimming strings.
	* Same functionality as variable(), except does not run trim() on strings.
	* This method should be used when handling passwords.
	*
	* @param	string|array	$var_name	The form variable's name from which data shall be retrieved.
	* 										If the value is an array this may be an array of indizes which will give
	* 										direct access to a value at any depth. E.g. if the value of "var" is array(1 => "a")
	* 										then specifying array("var", 1) as the name will return "a".
	* @param	mixed			$default	A default value that is returned if the variable was not set.
	* 										This function will always return a value of the same type as the default.
	* @param	bool			$multibyte	If $default is a string this parameter has to be true if the variable may contain any UTF-8 characters
	*										Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	* 										Specifies which super global should be used
	*
	* @return	mixed	The value of $_REQUEST[$var_name] run through {@link set_var set_var} to ensure that the type is the
	*					the same as that of $default. If the variable is not set $default is returned.
	*/
	public function untrimmed_variable($var_name, $default, $multibyte = false, $super_global = self::REQUEST)
	{
		return $this->_variable($var_name, $default, $multibyte, $super_global, false);
	}

	/**
	 * Enter description here...
	 */
	public function raw_variable($var_name, $default, $super_global = self::REQUEST)
	{
		$path = false;

		// deep direct access to multi dimensional arrays
		if (is_array($var_name))
		{
			$path = $var_name;
			// make sure at least the variable name is specified
			if (empty($path))
			{
				return (is_array($default)) ? array() : $default;
			}
			// the variable name is the first element on the path
			$var_name = array_shift($path);
		}

		if (!isset($this->input[$super_global][$var_name]))
		{
			return (is_array($default)) ? array() : $default;
		}
		$var = $this->input[$super_global][$var_name];

		if ($path)
		{
			// walk through the array structure and find the element we are looking for
			foreach ($path as $key)
			{
				if (is_array($var) && isset($var[$key]))
				{
					$var = $var[$key];
				}
				else
				{
					return (is_array($default)) ? array() : $default;
				}
			}
		}

		return $var;
	}

	/**
	* Shortcut method to retrieve SERVER variables.
	*
	* Also fall back to getenv(), some CGI setups may need it (probably not, but
	* whatever).
	*
	* @param	string|array	$var_name		See \phpbb\request\request_interface::variable
	* @param	mixed			$Default		See \phpbb\request\request_interface::variable
	*
	* @return	mixed	The server variable value.
	*/
	public function server($var_name, $default = '')
	{
		$multibyte = true;

		if ($this->is_set($var_name, self::SERVER))
		{
			return $this->variable($var_name, $default, $multibyte, self::SERVER);
		}
		else
		{
			$var = getenv($var_name);
			//( ( isset($_ENV['REMOTE_ADDR']) ) ? $_ENV['REMOTE_ADDR'] 
			//$this->type_cast_helper->recursive_set_var($var, $default, $multibyte);
			return $var;
		}
	}

	/**
	* Shortcut method to retrieve the value of client HTTP headers.
	*
	* @param	string|array	$header_name	The name of the header to retrieve.
	* @param	mixed			$default		See \phpbb\request\request_interface::variable
	*
	* @return	mixed	The header value.
	*/
	public function header($header_name, $default = '')
	{
		$var_name = 'HTTP_' . str_replace('-', '_', strtoupper($header_name));
		return $this->server($var_name, $default);
	}

	/**
	* Shortcut method to retrieve $_FILES variables
	*
	* @param string $form_name The name of the file input form element
	*
	* @return array The uploaded file's information or an empty array if the
	* variable does not exist in _FILES.
	*/
	public function file($form_name)
	{
		return $this->variable($form_name, array('name' => 'none'), true, self::FILES);
	}
	
	/**
	 * Request POST variable.
	 *
	 * _read() wrappers to retrieve POST, GET or any REQUEST (both) variable.
	 *
	 * @access public
	 * @param string $var
	 * @param integer $type
	 * @param string $dflt
	 * @return string
	 */
	public function post($var, $type = MX_TYPE_ANY, $dflt = '', $not_null = false)
	{
		if (!$this->super_globals_disabled())
		{
			return $this->_read($var, ($type | MX_TYPE_POST_VARS), $dflt, $not_null);
		}
		else	
		{
			$super_global = self::POST;
			$multibyte = false; //UTF-8 ?
			$default = $dflt;
			return $this->_variable($var_name, $default, $multibyte, $super_global, true);
		}			
		
	}
	
	/** ** /	
	public function post($var_name, $default, $multibyte = false, $super_global = self::POST)
	{
		return $this->_variable($var_name, $default, $multibyte, $super_global, true);
	}
	/** **/	
	
	/**
	 * Request GET variable.
	 *
	 * _read() wrappers to retrieve POST, GET or any REQUEST (both) variable.
	 *
	 * @access public
	 * @param string $var
	 * @param integer $type
	 * @param string $dflt
	 * @return string
	 */
	public function get($var, $type = MX_TYPE_ANY, $dflt = '', $not_null = false)
	{
		if (!$this->super_globals_disabled())
		{
			return $this->_read($var, ($type | MX_TYPE_GET_VARS), $dflt, $not_null);
		}
		else	
		{
			$super_global = self::GET;
			$multibyte = false; //UTF-8 ?
			$default = $dflt;
			return $this->_variable($var_name, $default, $multibyte, $super_global, true);
		}		

	}
	
	/** ** /
	public function get($var_name, $default, $multibyte = false, $super_global = self::GET)
	{
		return $this->_variable($var_name, $default, $multibyte, $super_global, true);
	}
	/** **/
	
	/**
	 * Request GET or POST variable.
	 *
	 * _read() wrappers to retrieve POST, GET or any REQUEST (both) variable.
	 *
	 * @access public
	 * @param string $var
	 * @param integer $type
	 * @param string $dflt
	 * @return string
	 */
	public function request($var, $type = MX_TYPE_ANY, $dflt = '', $not_null = false)
	{
		if (!$this->super_globals_disabled())
		{
			return $this->_read($var, ($type | MX_TYPE_POST_VARS | MX_TYPE_GET_VARS), $dflt, $not_null);	
		}
		else	
		{
			$super_global = self::REQUEST;
			$multibyte = false; //UTF-8 ?
			$default = $dflt;
			return $this->_variable($var_name, $default, $multibyte, $super_global, true);
		}	
	}

	/**
	 * Is POST var?
	 *
	 * Boolean method to check for existence of POST variable.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_post($var)
	{
		// Note: _x and _y are used by (at least IE) to return the mouse position at onclick of INPUT TYPE="img" elements.	
		return ($this->is_set_post($var) || $this->is_set_post($var.'_x') && $this->is_set_post($var.'_y')) ? 1 : 0;		
	}

	/**
	 * Is GET var?
	 *
	 * Boolean method to check for existence of GET variable.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_get($var)
	{
		//return isset($_GET[$var]) ? 1 : 0 ;
		return $this->is_set($var, self::GET);		
	}

	/**
	 * Is REQUEST (either GET or POST) var?
	 *
	 * Boolean method to check for existence of any REQUEST (both) variable.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_request($var)
	{
		return ($this->is_get($var) || $this->is_post($var)) ? 1 : 0;
		//return $this->is_set($var, self::REQUEST);
	}	
	
	/**
	 * Is POST var empty?
	 *
	 * Boolean method to check if POST variable is empty
	 * as it might be set but still be empty.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_empty_post($var)
	{
		//return (empty($_POST[$var]) && ( empty($_POST[$var.'_x']) || empty($_POST[$var.'_y']))) ? 1 : 0 ;
		return ($this->is_empty($var, self::POST) && ($this->is_empty($var.'_x', self::POST) || $this->is_empty($var.'_y', self::POST))) ? 1 : 0;		
	}
	
	/**
	 * Is GET var empty?
	 *
	 * Boolean method to check if GET variable is empty
	 * as it might be set but still be empty
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_empty_get($var)
	{
		//return empty($_GET[$var]) ? 1 : 0;
		return $this->is_empty($var, self::GET);		
	}

	/**
	 * Is REQUEST empty (GET and POST) var?
	 *
	 * Boolean method to check if REQUEST (both) variable is empty.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_empty_request($var)
	{
		return ($this->is_empty_get($var) && $this->is_empty_post($var)) ? 1 : 0;
	}
	
	/**
	* Checks whether a certain variable was sent via POST.
	* To make sure that a request was sent using POST you should call this function
	* on at least one variable.
	*
	* @param	string	$name	The name of the form variable which should have a
	*							_p suffix to indicate the check in the code that creates the form too.
	*
	* @return	bool			True if the variable was set in a POST request, false otherwise.
	*/
	public function is_set_post($name)
	{
		return $this->is_set($name, self::POST);
	}

	
	/**
	* Checks whether a certain variable was sent via GET.
	* To make sure that a request was sent using GET you should call this function
	* on at least one variable.
	*
	* @param	string	$name	The name of the form variable which should have a
	*							_p suffix to indicate the check in the code that creates the form too.
	*
	* @return	bool			True if the variable was set in a GET request, false otherwise.
	*/
	public function is_set_get($name)
	{
		return $this->is_set($name, self::GET);
	}	
	
	/**
	* Checks whether a certain variable is empty in one of the super global
	* arrays.
	*
	* @param	string	$var	Name of the variable
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	*							Specifies the super global which shall be checked
	*
	* @return	bool			True if the variable was sent as input
	*/
	public function is_empty($var, $super_global = self::REQUEST)
	{
		return empty($this->input[$super_global][$var]);
	}	
	
	/**
	* Checks whether a certain variable is set in one of the super global
	* arrays.
	*
	* @param	string	$var	Name of the variable
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	*							Specifies the super global which shall be checked
	*
	* @return	bool			True if the variable was sent as input
	*/
	public function is_set($var, $super_global = self::REQUEST)
	{
		return isset($this->input[$super_global][$var]);
	}
	
	/**
	* Checks whether the current request is an AJAX request (XMLHttpRequest)
	*
	* @return	bool			True if the current request is an ajax request
	*/
	public function is_ajax()
	{
		return $this->header('X-Requested-With') == 'XMLHttpRequest';
	}

	/**
	* Checks if the current request is happening over HTTPS.
	*
	* @return	bool			True if the request is secure.
	*/
	public function is_secure()
	{
		$https = $this->server('HTTPS');
		$https = $this->server('HTTP_X_FORWARDED_PROTO') === 'https' ? 'on' : $https;
		return !empty($https) && $https !== 'off';
	}

	/**
	* Returns all variable names for a given super global
	*
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	*					The super global from which names shall be taken
	*
	* @return	array	All variable names that are set for the super global.
	*					Pay attention when using these, they are unsanitised!
	*/
	public function variable_names($super_global = self::REQUEST)
	{
		if (!isset($this->input[$super_global]))
		{
			return array();
		}

		return array_keys($this->input[$super_global]);
	}

	/**
	* Helper function used by variable() and untrimmed_variable().
	*
	* @param	string|array	$var_name	The form variable's name from which data shall be retrieved.
	* 										If the value is an array this may be an array of indizes which will give
	* 										direct access to a value at any depth. E.g. if the value of "var" is array(1 => "a")
	* 										then specifying array("var", 1) as the name will return "a".
	* @param	mixed			$default	A default value that is returned if the variable was not set.
	* 										This function will always return a value of the same type as the default.
	* @param	bool			$multibyte	If $default is a string this parameter has to be true if the variable may contain any UTF-8 characters
	*										Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	* 										Specifies which super global should be used
	* @param	bool			$trim		Indicates whether trim() should be applied to string values.
	*
	* @return	mixed	The value of $_REQUEST[$var_name] run through {@link set_var set_var} to ensure that the type is the
	*					the same as that of $default. If the variable is not set $default is returned.
	*/
	protected function _variable($var_name, $default, $multibyte = false, $super_global = self::REQUEST, $trim = true)
	{
		$var = $this->raw_variable($var_name, $default, $super_global);

		// Return prematurely if raw variable is empty array or the same as
		// the default. Using strict comparison to ensure that one can't
		// prevent proper type checking on any input variable
		if ($var === array() || $var === $default)
		{
			return $var;
		}

		//$this->type_cast_helper->recursive_set_var($var, $default, $multibyte, $trim);

		return $var;
	}

	/**
	* Enter description here...
	*/
	public function get_super_global($super_global = self::REQUEST)
	{
		return $this->input[$super_global];
	}

	/**
	 * Enter description here...
	 */
	public function escape($var, $multibyte)
	{
		if (is_array($var))
		{
			$result = array();
			foreach ($var as $key => $value)
			{
				//$this->type_cast_helper->set_var($key, $key, gettype($key), $multibyte);
				$result[$key] = $this->escape($value, $multibyte);
			}
			$var = $result;
		}
		else
		{
			//$this->type_cast_helper->set_var($var, $var, 'string', $multibyte);
		}

		return $var;
	}

	/**
	* Check GET POST vars exists
	*/
	function check_http_var_exists($var_name, $empty_var = false)
	{
		if ($empty_var)
		{
			if (isset($_GET[$var_name]) || isset($_POST[$var_name]))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			if (!empty($_GET[$var_name]) || !empty($_POST[$var_name]))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		return false;
	}

	/**
	* Check variable value against default array
	*/
	function check_var_value($var, $var_array, $var_default = false)
	{
		if (!is_array($var_array) || empty($var_array))
		{
			return $var;
		}
		$var_default = (($var_default === false) ? $var_array[0] : $var_default);
		$var = in_array($var, $var_array) ? $var : $var_default;
		return $var;
	}	

}	// class mx_request_vars

/**
* Replacement for a superglobal (like $_GET or $_POST) which calls
* trigger_error on all operations but isset, overloads the [] operator with SPL.
*/
class deactivated_super_global implements \ArrayAccess, \Countable, \IteratorAggregate
{
	/**
	* @var	string	Holds the name of the superglobal this is replacing.
	*/
	private $name;

	/**
	* @var	\phpbb\request\request_interface::POST|GET|REQUEST|COOKIE	Super global constant.
	*/
	private $super_global;

	/**
	* @var	request_vars	The request class instance holding the actual request data.
	*/
	private $request;

	/**
	* Constructor generates an error message fitting the super global to be used within the other functions.
	*
	* @param	request_vars	$request	A request class instance holding the real super global data.
	* @param	string	$name		Name of the super global this is a replacement for - e.g. '_GET'.
	* @param	request_vars::POST|GET|REQUEST|COOKIE	$super_global	The variable's super global constant.
	*/
	public function __construct(request_vars $request, $name, $super_global)
	{
		$this->request = $request;
		$this->name = $name;
		$this->super_global = $super_global;
	}

	/**
	* Calls trigger_error with the file and line number the super global was used in.
	*/
	private function error()
	{
		$file = '';
		$line = 0;

		$message = 'Illegal use of $' . $this->name . '. You must use the request class to access input data. Found in %s on line %d. This error message was generated by deactivated_super_global.';

		$backtrace = debug_backtrace();
		if (isset($backtrace[1]))
		{
			$file = $backtrace[1]['file'];
			$line = $backtrace[1]['line'];
		}
		trigger_error(sprintf($message, $file, $line), E_USER_ERROR);
	}

	/**
	* Redirects isset to the correct request class call.
	*
	* @param	string	$offset	The key of the super global being accessed.
	*
	* @return	bool	Whether the key on the super global exists.
	*/
	public function offsetExists($offset)
	{
		return $this->request->is_set($offset, $this->super_global);
	}

	/**#@+
	* Part of the \ArrayAccess implementation, will always result in a FATAL error.
	*/
	public function offsetGet($offset)
	{
		$this->error();
	}

	public function offsetSet($offset, $value)
	{
		$this->error();
	}

	public function offsetUnset($offset)
	{
		$this->error();
	}
	/**#@-*/

	/**
	* Part of the \Countable implementation, will always result in a FATAL error
	*/
	public function count()
	{
		$this->error();
	}

	/**
	* Part of the Traversable/IteratorAggregate implementation, will always result in a FATAL error
	*/
	public function getIterator()
	{
		$this->error();
	}
}	// class request_vars


?>