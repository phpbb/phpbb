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

namespace phpbb\tests\functional\storage;

use phpbb\storage\provider\provider_interface;

/**
 * Database storage provider for functional tests.
 */
class db_provider implements provider_interface
{
	/**
	 * {@inheritdoc}
	 */
	public function get_name(): string
	{
		return 'db';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_title(): string
	{
		return 'Database';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_adapter_class(): string
	{
		return db_adapter::class;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_options(): array
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available(): bool
	{
		return true;
	}
}
