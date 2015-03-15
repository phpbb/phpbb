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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_boolean_processor_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/boolean_processor.xml');
	}

	public function test_double_and_with_not_of_or()
	{
		$db = $this->new_dbal();

		$db->sql_return_on_error(true);

		$sql_ary = array(
			'SELECT'	=> 'u.user_id',
			'FROM'		=> array(
				'phpbb_users'		=> 'u',
				'phpbb_user_group'	=> 'ug',
			),
			'WHERE'		=> array('AND',
				array('NOT',
					array('OR',
						array('ug.group_id', '=', 1),
						array('ug.group_id', '=', 2),
					),
				),
				array('u.user_id', '=', 'ug.user_id'),
			),
			'ORDER_BY'	=> 'u.user_id',
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(), $db->sql_fetchrowset($result));
	}

	public function test_triple_and_with_is_null()
	{
		$db = $this->new_dbal();

		$db->sql_return_on_error(true);

		$sql_ary = array(
			'SELECT'	=> 'u.username',
			'FROM'		=> array(
				'phpbb_users'		=> 'u',
				'phpbb_user_group'	=> 'ug',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(
						'phpbb_banlist'	=> 'b',
					),
					'ON'	=> 'u.user_id = b.ban_userid',
				),
			),
			'WHERE'		=> array('AND',
				array('ug.group_id', '=', 1),
				array('u.user_id', '=', 'ug.user_id'),
				array('b.ban_id', 'IS', NULL),
			),
			'ORDER_BY'	=> 'u.username',
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);

		$db->sql_return_on_error(false);

		$this->assertEquals(array(
			array('username' => 'helper'),
			array('username' => 'mass email'),
			), $db->sql_fetchrowset($result));
	}
}
