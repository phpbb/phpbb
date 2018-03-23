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

namespace phpbb\db\migration\data\v310;

class topic_sort_username extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return array(
			'change_columns' => array(
				$this->table_prefix . 'topics'	=> array(
					'topic_first_poster_name' => array('VCHAR_UNI:255', '', 'true_sort'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'change_columns' => array(
				$this->table_prefix . 'topics'	=> array(
					'topic_first_poster_name' => array('VCHAR_UNI:255', ''),
				),
			),
		);
	}
}
