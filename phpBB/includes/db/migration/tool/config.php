<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_tool_config implements phpbb_db_migration_tool_interface
{
	/** @var phpbb_config */
	protected $config = null;

	public function __construct(phpbb_config $config)
	{
		$this->config = $config;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_name()
	{
		return 'config';
	}

	/**
	* Config Add
	*
	* This function allows you to add a config setting.
	*
	* @param string $config_name The name of the config setting you would like to add
	* @param mixed $config_value The value of the config setting
	* @param bool $is_dynamic True if it is dynamic (changes very often) and should not be stored in the cache, false if not.
	*/
	public function add($config_name, $config_value = '', $is_dynamic = false)
	{
		if (isset($this->config[$config_name]))
		{
			throw new phpbb_db_migration_exception('CONFIG_ALREADY_EXISTS', $config_name);
		}

		$this->config->set($config_name, $config_value, !$is_dynamic);

		return false;
	}

	/**
	* Config Update
	*
	* This function allows you to update an existing config setting.
	*
	* @param string $config_name The name of the config setting you would like to update
	* @param mixed $config_value The value of the config setting
	*/
	public function update($config_name, $config_value = '')
	{
		if (!isset($this->config[$config_name]))
		{
			throw new phpbb_db_migration_exception('CONFIG_NOT_EXIST', $config_name);
		}

		$this->config->set($config_name, $config_value);

		return false;
	}

	/**
	* Config Update If Equals
	*
	* This function allows you to update a config setting if the first argument equal to the current config value
	*
	* @param bool $compare If equal to the current config value, will be updated to the new config value, otherwise not
	* @param string $config_name The name of the config setting you would like to update
	* @param mixed $config_value The value of the config setting
	*/
	public function update_if_equals($compare, $config_name, $config_value = '')
	{
		if (!isset($this->config[$config_name]))
		{
			throw new phpbb_db_migration_exception('CONFIG_NOT_EXIST', $config_name);
		}

		$this->config->set_atomic($config_name, $compare, $config_value);

		return false;
	}

	/**
	* Config Remove
	*
	* This function allows you to remove an existing config setting.
	*
	* @param string $config_name The name of the config setting you would like to remove
	*/
	public function remove($config_name)
	{
		if (!isset($this->config[$config_name]))
		{
			throw new phpbb_db_migration_exception('CONFIG_NOT_EXIST', $config_name);
		}

		$this->config->delete($config_name);

		return false;
	}
}
