<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace phpbb\console\command\config;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class set_atomic extends \phpbb\console\command\command
{
	/** @var \phpbb\config\config */
	protected $config;

	function __construct(\phpbb\config\config $config)
	{
		$this->config = $config;

		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('config:set-atomic')
			->setDescription('Sets a configuration option\'s value')
			->addArgument(
				'config-key',
				InputArgument::REQUIRED,
				'The configuration option\'s name'
			)
			->addArgument(
				'old-value',
				InputArgument::REQUIRED,
				'Current configuration value'
			)
			->addArgument(
				'new-value',
				InputArgument::REQUIRED,
				'New configuration value'
			)
			->addArgument(
				'use-cache',
				InputArgument::OPTIONAL,
				'Whether this variable should be cached or if it changes too frequently to be efficiently cached.',
				true
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$key = $input->getArgument('config-key');
		$old_value = $input->getArgument('old-value');
		$new_value = $input->getArgument('new-value');
		$use_cache = $input->getArgument('use-cache');
		$use_cache = (strtolower($use_cache) !== 'false' && $use_cache);

		if ($this->config->set_atomic($key, $old_value, $new_value, $use_cache))
		{
			$output->writeln("<info>Successfully set config $key</info>");
			return 0;
		}
		else
		{
			$output->writeln("<error>Could not set config $key</error>");
			return 1;
		}
	}
}
