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

class phpbb_mock_sql_insert_buffer extends \phpbb\db\sql_insert_buffer
{
	public function flush()
	{
		return (sizeof($this->buffer)) ? true : false;
	}

	public function get_buffer()
	{
		return $this->buffer;
	}
}
