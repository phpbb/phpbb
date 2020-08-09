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
* PostgreSQL Database Abstraction Layer
* Minimum Requirement is Version 8.3+
*
* @deprecated 4.0.0-dev The driver interface is deprecated, please use Doctrine DBAL directly instead.
*/
class postgres extends doctrine
{
	/**
	 * {@inheritdoc}
	 */
	public function get_sql_layer()
	{
		return 'postgres';
	}
}
