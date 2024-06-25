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

namespace phpbb\db\tools;

use Doctrine\DBAL\Connection;

/**
 * A factory which serves the suitable tools instance for the given dbal
 */
class factory
{
	/**
	 * @return tools_interface
	 */
	public function get(Connection $connection, $return_statements = false)
	{
		return new doctrine($connection, $return_statements);
	}
}
