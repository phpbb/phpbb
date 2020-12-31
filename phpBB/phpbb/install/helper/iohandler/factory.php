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

namespace phpbb\install\helper\iohandler;

use phpbb\install\helper\iohandler\exception\iohandler_not_implemented_exception;

/**
 * Input-output handler factory
 */
class factory
{
	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container;

	/**
	 * @var string
	 */
	protected $environment;

	/**
	 * Constructor
	 *
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Dependency injection container
	 */
	public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
	{
		$this->container	= $container;
		$this->environment	= null;
	}

	/**
	 * @param string	$environment	The name of the input-output handler to use
	 */
	public function set_environment($environment)
	{
		$this->environment = $environment;
	}

	/**
	 * Factory getter for iohandler
	 *
	 * @return \phpbb\install\helper\iohandler\iohandler_interface
	 *
	 * @throws iohandler_not_implemented_exception
	 * 		When the specified iohandler_interface does not exists
	 */
	public function get()
	{
		switch ($this->environment)
		{
			case 'ajax':
				return $this->container->get('installer.helper.iohandler_ajax');
			break;
			case 'nojs':
				// @todo replace this
				return $this->container->get('installer.helper.iohandler_ajax');
			break;
			case 'cli':
				return $this->container->get('installer.helper.iohandler_cli');
			break;
			default:
				throw new iohandler_not_implemented_exception();
			break;
		}
	}
}
