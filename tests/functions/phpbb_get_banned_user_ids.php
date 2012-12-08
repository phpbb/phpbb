<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_user.php';

class phpbb_get_banned_user_ids_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/banned_users.xml');
	}

	public function test_phpbb_get_banned_user_ids()
	{
		global $db;

		$db = $this->new_dbal();

		$user_ids = array(1, 2, 4, 5);

		$this->assertEquals(phpbb_get_banned_user_ids($user_ids, true), array(2 => 2, 5 => 5));

		$this->assertEquals(phpbb_get_banned_user_ids($user_ids, false), array(2 => 2));

		$this->assertEquals(phpbb_get_banned_user_ids($user_ids, 2), array(2 => 2, 5 => 5));
	}
}
