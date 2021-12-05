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

namespace phpbb\db\doctrine\oci8;

use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\OracleSchemaManager;
use Doctrine\DBAL\Schema\Table;

class schema_manager extends OracleSchemaManager
{
	/**
	 * {@inheritdoc}
	 *
	 * Copied from upstream to lowercase 'COMMENTS'
	 */
	public function listTableDetails($name): Table
	{
		$table = AbstractSchemaManager::listTableDetails($name);

		$platform = $this->_platform;
		assert($platform instanceof OraclePlatform);
		$sql = $platform->getListTableCommentsSQL($name);

		$tableOptions = $this->_conn->fetchAssociative($sql);

		if ($tableOptions !== false)
		{
			$table->addOption('comment', $tableOptions['comments']);
		}

		return $table;
	}
}
