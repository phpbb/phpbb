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

class m_softdelete_global extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v311');
	}

	public function update_data()
	{
		return array(
			// Make m_softdelete global. The add method will take care of updating
			// it if it already exists.
			array('permission.add', array('m_softdelete', true)),
		);
	}
}
