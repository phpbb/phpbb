<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\tool;

/**
* Migration config tool
*
* @package db
*/
class config implements \phpbb\db\migration\tool\tool_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/**
	* Constructor
	*
	* @param \phpbb\config\config $config
	*/
	public function __construct(\phpbb\config\config $config)
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
	* Add a config setting.
	*
	* @param string $config_name The name of the config setting
	* 	you would like to add
	* @param mixed $config_value The value of the config setting
	* @param bool $is_dynamic True if it is dynamic (changes very often)
	* 	and should not be stored in the cache, false if not.
	* @return null
	*/
	public function add($config_name, $config_value, $is_dynamic = false)
	{
		if (isset($this->config[$config_name]))
		{
			return;
		}

		$this->config->set($config_name, $config_value, !$is_dynamic);
	}

	/**
	* Update an existing config setting.
	*
	* @param string $config_name The name of the config setting you would
	* 	like to update
	* @param mixed $config_value The value of the config setting
	* @return null
	*/
	public function update($config_name, $config_value)
	{
		if (!isset($this->config[$config_name]))
		{
			throw new \phpbb\db\migration\exception('CONFIG_NOT_EXIST', $config_name);
		}

		$this->config->set($config_name, $config_value);
	}

	/**
	* Update a config setting if the first argument equal to the
	* current config value
	*
	* @param string $compare If equal to the current config value, will be
	* 	updated to the new config value, otherwise not
	* @param string $config_name The name of the config setting you would
	* 	like to update
	* @param mixed $config_value The value of the config setting
	* @return null
	*/
	public function update_if_equals($compare, $config_name, $config_value)
	{
		if (!isset($this->config[$config_name]))
		{
			throw new \phpbb\db\migration\exception('CONFIG_NOT_EXIST', $config_name);
		}

		$this->config->set_atomic($config_name, $compare, $config_value);
	}

	/**
	* Remove an existing config setting.
	*
	* @param string $config_name The name of the config setting you would
	* 	like to remove
	* @return null
	*/
	public function remove($config_name)
	{
		if (!isset($this->config[$config_name]))
		{
			return;
		}

		$this->config->delete($config_name);
	}

	/**
	* {@inheritdoc}
	*/
	public function reverse()
	{
		$arguments = func_get_args();
		$original_call = array_shift($arguments);

		$call = false;
		switch ($original_call)
		{
			case 'add':
				$call = 'remove';
			break;

			case 'remove':
				$call = 'add';
				if (sizeof($arguments) == 1)
				{
					$arguments[] = '';
				}
			break;

			case 'update_if_equals':
				$call = 'update_if_equals';

				// Set to the original value if the current value is what we compared to originally
				$arguments = array(
					$arguments[2],
					$arguments[1],
					$arguments[0],
				);
			break;
		}

		if ($call)
		{
			return call_user_func_array(array(&$this, $call), $arguments);
		}
	}
}
