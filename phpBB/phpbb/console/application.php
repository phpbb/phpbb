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

namespace phpbb\console;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class application extends \Symfony\Component\Console\Application
{
	/**
	* @var \phpbb\config\config Config object
	*/
	protected $config;

	/**
	* @var \phpbb\language\language Language object
	*/
	protected $language;

	/**
	* @param string						$name		The name of the application
	* @param string						$version	The version of the application
	* @param \phpbb\language\language	$language	The user which runs the application (used for translation)
	* @param \phpbb\config\config		$config		Config object
	*/
	public function __construct($name, $version, \phpbb\language\language $language, \phpbb\config\config $config)
	{
		$this->language = $language;
		$this->config = $config;

		parent::__construct($name, $version);
	}

	/**
	* {@inheritdoc}
	*/
	protected function getDefaultInputDefinition(): InputDefinition
	{
		$input_definition = parent::getDefaultInputDefinition();

		$this->register_global_options($input_definition);

		return $input_definition;
	}

	/**
	* Register a set of commands from the container
	*
	* @param \phpbb\di\service_collection	$command_collection	The console service collection
	*/
	public function register_container_commands(\phpbb\di\service_collection $command_collection)
	{
		$commands_list = array_keys($command_collection->getArrayCopy());
		foreach ($commands_list as $service_command)
		{
			// config_text DB table does not exist in phpBB prior to 3.1
			// Hence skip cron tasks as they include reparser cron as it uses config_text table
			if (phpbb_version_compare($this->config['version'], '3.1.0', '<') && strpos($service_command, 'cron') !== false)
			{
				continue;
			}
			$this->add($command_collection[$service_command]);

		}
	}

	/**
	 * Register global options
	 *
	 * @param InputDefinition $definition An InputDefinition instance
	 */
	protected function register_global_options(InputDefinition $definition)
	{
		try
		{
			$definition->addOption(new InputOption(
				'safe-mode',
				null,
				InputOption::VALUE_NONE,
				$this->language->lang('CLI_DESCRIPTION_OPTION_SAFE_MODE')
			));

			$definition->addOption(new InputOption(
				'env',
				'e',
				InputOption::VALUE_REQUIRED,
				$this->language->lang('CLI_DESCRIPTION_OPTION_ENV')
			));
		}
		catch (\LogicException $e)
		{
			// Do nothing
		}
	}
}
