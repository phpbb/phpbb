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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\TaggedContainerInterface;

class application extends \Symfony\Component\Console\Application
{
	/**
	* @param string			$name		The name of the application
	* @param string			$version	The version of the application
	* @param \phpbb\user	$user		The user which runs the application (used for translation)
	*/
	public function __construct($name, $version, \phpbb\user $user)
	{
		parent::__construct($name, $version);

		$this->getDefinition()->addOption(new InputOption(
			'--shell',
			'-s',
			InputOption::VALUE_NONE,
			$user->lang('CLI_DESCRIPTION_OPTION_SHELL')
		));
	}

	/**
	* Register a set of commands from the container
	*
	* @param TaggedContainerInterface	$container	The container
	* @param string						$tag		The tag used to register the commands
	*/
	function register_container_commands(TaggedContainerInterface $container, $tag = 'console.command')
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
		if ($input->hasParameterOption(array('--shell', '-s')) === true)
		{
			$shell = new Shell($this);
			$shell->run();

			return 0;
		}

		parent::doRun($input, $output);
	}
}
