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

use Symfony\Component\Console\Shell;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\TaggedContainerInterface;

class application extends \Symfony\Component\Console\Application
{
	/**
	* @var bool Indicates whether or not we are in a shell
	*/
	protected $in_shell = false;

	/**
	* @var \phpbb\user User object
	*/
	protected $user;

	/**
	* @param string			$name		The name of the application
	* @param string			$version	The version of the application
	* @param \phpbb\user	$user		The user which runs the application (used for translation)
	*/
	public function __construct($name, $version, \phpbb\user $user)
	{
		parent::__construct($name, $version);

		$this->user = $user;
	}

	/**
	* Gets the help message.
	*
	* It's a hack of the default help message to display the --shell
	* option only for the application and not for all the commands.
	*
	* @return string A help message.
	*/
	public function getHelp()
	{
		// If we are already in a shell
		// we do not want to have the --shell option available
		if ($this->in_shell)
		{
			return parent::getHelp();
		}

		$this->getDefinition()->addOption(new InputOption(
			'--shell',
			'-s',
			InputOption::VALUE_NONE,
			$this->user->lang('CLI_DESCRIPTION_OPTION_SHELL')
		));

		return parent::getHelp();
	}

	/**
	* Register a set of commands from the container
	*
	* @param TaggedContainerInterface	$container	The container
	* @param string						$tag		The tag used to register the commands
	*/
	public function register_container_commands(TaggedContainerInterface $container, $tag = 'console.command')
	{
		foreach($container->findTaggedServiceIds($tag) as $id => $void)
		{
			$this->add($container->get($id));
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function doRun(InputInterface $input, OutputInterface $output)
	{
		// Run a shell if the --shell (or -s) option is set and if no command name is specified
		// Also, we do not want to have the --shell option available if we are already in a shell
		if (!$this->in_shell && $this->getCommandName($input) === null && $input->hasParameterOption(array('--shell', '-s')))
		{
			$shell = new Shell($this);
			$this->in_shell = true;
			$shell->run();

			return 0;
		}

		return parent::doRun($input, $output);
	}
}
