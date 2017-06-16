<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */
namespace phpbb\install\converter\factory;

class dbal_connection_destination_factory
{
	public static function get_connection($dbal_config, \phpbb\install\converter\controller\helper $helper)
	{
		$credentials_destination = $helper->get_destination_db();
		$db_destination = \Doctrine\DBAL\DriverManager::getConnection($credentials_destination, $dbal_config);
		return $db_destination;
	}

}
