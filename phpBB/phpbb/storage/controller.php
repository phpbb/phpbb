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

namespace phpbb\storage;

class controller
{
	/**
	* @var ContainerInterface
	*/
	protected $container;

	/**
	* Constructor.
	*
	* @param ContainerInterface $container A ContainerInterface instance
	*/
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function get_file($storage, $file)
	{
		$storage = $this->container->get($storage);

		return $storage->get_contents($file);
	}
}
