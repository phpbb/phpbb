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

namespace phpbb\install\converter\factory;

class dbal_connection_source_factory
{
	public static function get_connection($dbal_config, \phpbb\install\converter\controller\helper $helper)
	{
		$credentials_source=$helper->get_source_db();
		$db_source = \Doctrine\DBAL\DriverManager::getConnection($credentials_source, $dbal_config);
		return $db_source;
	}

}
