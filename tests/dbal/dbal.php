<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once 'test_framework/framework.php';
require_once '../phpBB/includes/functions.php';

class phpbb_dbal_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/two_users.xml');
	}

	public function test_select_row()
	{
		$db = $this->new_dbal();

		$result = $db->sql_query('SELECT username_clean
			FROM phpbb_users
			WHERE user_id = 2');
		$row = $db->sql_fetchrow($result);

		$this->assertEquals(array('username_clean' => 'foobar'), $row);
	}

	public function test_select_field()
	{
		$db = $this->new_dbal();

		$result = $db->sql_query('SELECT username_clean
			FROM phpbb_users
			WHERE user_id = 2');

		$this->assertEquals('foobar', $db->sql_fetchfield('username_clean'));
	}
}

