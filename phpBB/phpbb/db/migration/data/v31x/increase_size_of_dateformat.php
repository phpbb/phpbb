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

namespace phpbb\db\migration\data\v31x;

class increase_size_of_dateformat  extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v31x\v316',
		);
	}

	public function update_schema()
	{
		return array(
			'change_columns' => array(
				$this->table_prefix . 'users' => array(
					'user_dateformat' => array('VCHAR:64', 'd M Y H:i'),
				),
			),
		);
	}
}
