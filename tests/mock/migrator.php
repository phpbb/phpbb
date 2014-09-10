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

class phpbb_mock_migrator extends \phpbb\db\migrator
{
	public function __construct()
	{
	}

	public function load_migration_state()
	{
	}

	public function set_migrations($class_names)
	{
	}

	public function update()
	{
	}

	public function revert($migration)
	{
	}

	public function unfulfillable($name)
	{
	}

	public function finished()
	{
	}

	public function migration_state($migration)
	{
	}

	public function populate_migrations($migrations)
	{
	}

	public function create_migrations_table()
	{
	}
}
