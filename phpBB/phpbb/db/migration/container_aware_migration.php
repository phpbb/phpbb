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

namespace phpbb\db\migration;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Abstract base class for container aware database migrations.
*/
abstract class container_aware_migration extends migration
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * {@inheritdoc}
	 */
	public function setContainer(ContainerInterface|null $container = null)
	{
		$this->container = $container;
	}
}
