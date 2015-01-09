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

namespace phpbb\config;

/**
* Configuration container class
*/
class db extends \phpbb\config\config
{
	/**
	* Cache instance
	* @var \phpbb\cache\driver\driver_interface
	*/
	protected $cache;

	/**
	* Database connection
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/**
	* Name of the database table used for configuration.
	* @var string
	*/
	protected $table;

	/**
	* Creates a configuration container with a default set of values
	*
	* @param \phpbb\db\driver\driver_interface    $db    Database connection
	* @param \phpbb\cache\driver\driver_interface $cache Cache instance
	* @param string                       $table Configuration table name
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\cache\driver\driver_interface $cache, $table)
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

			while ($row = $this->db->sql_fetchrow($result))
			{
				$config[$row['config_name']] = $row['config_value'];
			}
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
	* Removes a configuration option
	*
	* @param  String $key       The configuration option's name
	* @param  bool   $use_cache Whether this variable should be cached or if it
	*                           changes too frequently to be efficiently cached
	* @return null
	*/
	public function delete($key, $use_cache = true)
	{
		$sql = 'DELETE FROM ' . $this->table . "
			WHERE config_name = '" . $this->db->sql_escape($key) . "'";
		$this->db->sql_query($sql);

		unset($this->config[$key]);

		if ($use_cache)
		{
			$this->cache->destroy('config');
		}
	}

	/**
	* Sets a configuration option's value
	*
	* @param string $key       The configuration option's name
	* @param string $value     New configuration value
	* @param bool   $use_cache Whether this variable should be cached or if it
	*                          changes too frequently to be efficiently cached.
	*/
	public function set($key, $value, $use_cache = true)
	{
		$this->set_atomic($key, false, $value, $use_cache);
	}

	/**
	* Sets a configuration option's value only if the old_value matches the
	* current configuration value or the configuration value does not exist yet.
	*
	* @param  string $key       The configuration option's name
	* @param  mixed  $old_value Current configuration value or false to ignore
	*                           the old value
	* @param  string $new_value New configuration value
	* @param  bool   $use_cache Whether this variable should be cached or if it
	*                           changes too frequently to be efficiently cached
	* @return bool              True if the value was changed, false otherwise
	*/
	public function set_atomic($key, $old_value, $new_value, $use_cache = true)
	{
		$sql = 'UPDATE ' . $this->table . "
			SET config_value = '" . $this->db->sql_escape($new_value) . "'
			WHERE config_name = '" . $this->db->sql_escape($key) . "'";

		if ($old_value !== false)
		{
			$sql .= " AND config_value = '" . $this->db->sql_escape($old_value) . "'";
		}

		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows() && isset($this->config[$key]))
		{
			return false;
		}

		if (!isset($this->config[$key]))
		{
			$sql = 'INSERT INTO ' . $this->table . ' ' . $this->db->sql_build_array('INSERT', array(
				'config_name'	=> $key,
				'config_value'	=> $new_value,
				'is_dynamic'	=> ($use_cache) ? 0 : 1));
			$this->db->sql_query($sql);
		}

		if ($use_cache)
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
	* @param bool   $use_cache Whether this variable should be cached or if it
	*                          changes too frequently to be efficiently cached.
	*/
	function increment($key, $increment, $use_cache = true)
	{
		if (!isset($this->config[$key]))
		{
			$this->set($key, '0', $use_cache);
		}

		$sql_update = $this->db->cast_expr_to_string($this->db->cast_expr_to_bigint('config_value') . ' + ' . (int) $increment);

		$this->db->sql_query('UPDATE ' . $this->table . '
			SET config_value = ' . $sql_update . "
			WHERE config_name = '" . $this->db->sql_escape($key) . "'");

		if ($use_cache)
		{
			$this->cache->destroy('config');
		}

		$this->config[$key] += $increment;
	}
}
