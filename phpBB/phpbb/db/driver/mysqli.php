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

namespace phpbb\db\driver;

/**
* MySQLi Database Abstraction Layer
* mysqli-extension has to be compiled with:
* MySQL 4.1+ or MySQL 5.0+
*
* @deprecated 4.0.0-dev The driver interface is deprecated, please use Doctrine DBAL directly instead.
*/
class mysqli extends doctrine
{
	/**
	 * {@inheritdoc}
	 */
	public function get_sql_layer()
	{
		return 'mysqli';
	}
}
