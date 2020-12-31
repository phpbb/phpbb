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

namespace phpbb\report;

use phpbb\report\exception\factory_invalid_argument_exception;

class handler_factory
{
	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container;

	/**
	 * Constructor
	 *
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 */
	public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * Return a new instance of an appropriate report handler
	 *
	 * @param string	$type
	 * @return \phpbb\report\report_handler_interface
	 * @throws factory_invalid_argument_exception if $type is not valid
	 */
	public function get_instance($type)
	{
		switch ($type)
		{
			case 'pm':
				return $this->container->get('phpbb.report.handlers.report_handler_pm');
			break;
			case 'post':
				return $this->container->get('phpbb.report.handlers.report_handler_post');
			break;
		}

		throw new factory_invalid_argument_exception();
	}
}
