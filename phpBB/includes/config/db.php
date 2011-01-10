<?php
/**
*
* @package phpBB3
* @copyright (c) 2010 phpBB Group
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
* Configuration container class
* @package phpBB3
*/
class phpbb_config_db extends phpbb_config
{
	/**
	* Cache instance
	* @var phpbb_cache_driver_interface
	*/
	protected $cache;

	/**
	* Database connection
	* @var dbal
	*/
	protected $db;

	/**
	* Creates a configuration container with a default set of values
	*
	* @param phpbb_cache_driver_interface $cache Cache instance
	* @param dbal                         $db    Database connection
	* @param string                       $table Configuration table name
	*/
	public function __construct(phpbb_cache_driver_interface $cache, dbal $db, $table)
	{
		$this->db = $db;
		$this->cache = $cache;
		$this->table = $table;

		if (($config = $cache->get('config')) !== false)
		{
			$sql = 'SELECT config_name, config_value
				FROM ' . $this->table . '
				WHERE is_dynamic = 1';
			$result = $this->db->sql_query($sql);
			$config += $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);
		}
		else
		{
			$config = $cached_config = array();

			$sql = 'SELECT config_name, config_value, is_dynamic
				FROM ' . $this->table;
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				if (!$row['is_dynamic'])
				{
					$cached_config[$row['config_name']] = $row['config_value'];
				}

				$config[$row['config_name']] = $row['config_value'];
			}
			$this->db->sql_freeresult($result);

			$cache->put('config', $cached_config);
		}

		parent::__construct($config);
	}

	/**
	* Sets a configuration option's value
	*
	* @param string $key   The configuration option's name
	* @param string $value New configuration value
	* @param bool   $cache Whether this variable should be cached or if it
	*                      changes too frequently to be efficiently cached.
	*/
	public function set($key, $value, $cache = true)
	{
		$this->set_atomic($key, false, $value, $cache);
	}

	/**
	* Sets a configuration option's value only if the old_value matches the
	* current configuration value or the configuration value does not exist yet.
	*
	* @param  string $key       The configuration option's name
	* @param  mixed  $old_value Current configuration value or false to ignore
	*                           the old value
	* @param  string $new_value New configuration value
	* @param  bool   $cache     Whether this variable should be cached or if it
	*                           changes too frequently to be efficiently cached
	* @return bool              True if the value was changed, false otherwise
	*/
	public function set_atomic($key, $old_value, $new_value, $cache = true)
	{
		$sql = 'UPDATE ' . $this->table . "
			SET config_value = '" . $this->db->sql_escape($new_value) . "'
			WHERE config_name = '" . $this->db->sql_escape($key) . "'";

		if ($old_value !== false)
		{
			$sql .= " AND config_value = '" . $this->db->sql_escape($old_value) . "'";
		}

		$result = $this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows($result) && isset($this->config[$key]))
		{
			return false;
		}

		if (!isset($this->config[$key]))
		{
			$sql = 'INSERT INTO ' . $this->table . ' ' . $this->db->sql_build_array('INSERT', array(
				'config_name'	=> $key,
				'config_value'	=> $new_value,
				'is_dynamic'	=> ($cache) ? 0 : 1));
			$this->db->sql_query($sql);
		}

		if ($cache)
		{
			$this->cache->destroy('config');
		}

		$this->config[$key] = $new_value;
		return true;
	}

	/**
	* Increments an integer config value directly in the database.
	*
	* Using this method instead of setting the new value directly avoids race
	* conditions and unlike set_atomic it cannot fail.
	*
	* @param string $key       The configuration option's name
	* @param int    $increment Amount to increment by
	* @param bool   $cache     Whether this variable should be cached or if it
	*                          changes too frequently to be efficiently cached.
	*/
	function increment($key, $increment, $cache = true)
	{
		if (!isset($this->config[$key]))
		{
			$this->set($key, '0', $cache);
		}

		$sql_update = $this->db->cast_expr_to_string($this->db->cast_expr_to_bigint('config_value') . ' + ' . (int) $increment);

		$this->db->sql_query('UPDATE ' . $this->table . '
			SET config_value = ' . $sql_update . "
			WHERE config_name = '" . $this->db->sql_escape($key) . "'");

		if ($cache)
		{
			$this->cache->destroy('config');
		}

		$this->config[$key] += $increment;
	}
}
