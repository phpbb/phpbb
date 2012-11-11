<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migrationtools_config extends phpbb_db_migration
{
	/**
	* Config Exists
	*
	* This function is to check to see if a config variable exists or if it does not.
	*
	* @param string $config_name The name of the config setting you wish to check for.
	* @param bool $return_result - return the config value/default if true : default false.
	*
	* @return bool true/false if config exists
	*/
	function config_exists($config_name, $return_result = false)
	{
		$sql = 'SELECT *
				FROM ' . CONFIG_TABLE . "
				WHERE config_name = '" . $this->db->sql_escape($config_name) . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row)
		{
			if (!isset($this->config[$config_name]))
			{
				$this->config[$config_name] = $row['config_value'];

				if (!$row['is_dynamic'])
				{
					$this->cache->destroy('config');
				}
			}

			return ($return_result) ? $row : true;
		}

		// this should never happen, but if it does, we need to remove the config from the array
		if (isset($this->config[$config_name]))
		{
			unset($this->config[$config_name]);
			$this->cache->destroy('config');
		}

		return false;
	}

	/**
	* Config Add
	*
	* This function allows you to add a config setting.
	*
	* @param string $config_name The name of the config setting you would like to add
	* @param mixed $config_value The value of the config setting
	* @param bool $is_dynamic True if it is dynamic (changes very often) and should not be stored in the cache, false if not.
	*
	* @return result
	*/
	function config_add($config_name, $config_value = '', $is_dynamic = false)
	{
		if ($this->config_exists($config_name))
		{
			return $this->umil_end('CONFIG_ALREADY_EXISTS', $config_name);
		}

		set_config($config_name, $config_value, $is_dynamic);
	}

	/**
	* Config Update
	*
	* This function allows you to update an existing config setting.
	*
	* @param string $config_name The name of the config setting you would like to update
	* @param mixed $config_value The value of the config setting
	* @param bool $is_dynamic True if it is dynamic (changes very often) and should not be stored in the cache, false if not.
	*
	* @return result
	*/
	function config_update($config_name, $config_value = '')
	{
		if (!$this->config_exists($config_name))
		{
			return $this->umil_end('CONFIG_NOT_EXIST', $config_name);
		}

		set_config($config_name, $config_value);
	}

	/**
	* Config Remove
	*
	* This function allows you to remove an existing config setting.
	*
	* @param string $config_name The name of the config setting you would like to remove
	*
	* @return result
	*/
	function config_remove($config_name)
	{
		if (!$this->config_exists($config_name))
		{
			return $this->umil_end('CONFIG_NOT_EXIST', $config_name);
		}

		$sql = 'DELETE FROM ' . CONFIG_TABLE . "
			WHERE config_name = '" . $this->db->sql_escape($config_name) . "'";
		$this->db->sql_query($sql);

		unset($this->config[$config_name]);
		$this->cache->destroy('config');
	}
}