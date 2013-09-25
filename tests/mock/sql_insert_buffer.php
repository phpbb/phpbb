<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
