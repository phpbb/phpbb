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

namespace phpbb\db\migration\tool;

/**
* Migration config tool
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
	* @throws \phpbb\db\migration\exception
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
	* @throws \phpbb\db\migration\exception
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
				if (count($arguments) == 1)
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

			case 'reverse':
				// Reversing a reverse is just the call itself
				$call = array_shift($arguments);
			break;
		}

		if ($call)
		{
			return call_user_func_array(array(&$this, $call), $arguments);
		}
	}
}
