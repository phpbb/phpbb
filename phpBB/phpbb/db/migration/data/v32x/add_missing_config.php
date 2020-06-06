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

namespace phpbb\db\migration\data\v32x;

use phpbb\db\migration\migration_interface;
use phpbb\db\migrator;

class add_missing_config extends \phpbb\db\migration\container_aware_migration
{
	static public function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v32x\v329',
		];
	}

	public function update_data()
	{
		$migration_classes = [
			'\phpbb\db\migration\data\v30x\release_3_0_3_rc1',
			'\phpbb\db\migration\data\v30x\release_3_0_6_rc1',
			'\phpbb\db\migration\data\v31x\add_jabber_ssl_context_config_options',
			'\phpbb\db\migration\data\v31x\add_smtp_ssl_context_config_options',
			'\phpbb\db\migration\data\v31x\update_hashes',
			'\phpbb\db\migration\data\v320\font_awesome_update',
			'\phpbb\db\migration\data\v320\text_reparser',
			'\phpbb\db\migration\data\v32x\cookie_notice_p2',
		];

		/** @var migrator $migrator */
		$migrator = $this->container->get('migrator');

		$update_data = [];

		foreach ($migration_classes as $migration_class)
		{
			/** @var migration_interface $migration */
			$migration = $migrator->get_migration($migration_class);

			$migration_update_data = $migration->update_data();

			foreach ($migration_update_data as $entry)
			{
				if ($entry[0] == 'config.add')
				{
					$update_data[] = $entry;
				}
			}
		}

		return $update_data;
	}
}
