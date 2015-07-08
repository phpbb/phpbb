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

class phpbb_installer_database_helper_test extends phpbb_test_case
{
	/**
	 * @var phpbb\install\helper\database
	 */
	private $database_helper;

	public function setUp()
	{
		$filesystem = new \phpbb\filesystem\filesystem();
		$phpbb_root_path = '';
		$this->database_helper = new \phpbb\install\helper\database($filesystem, $phpbb_root_path);
	}

	/**
	 * @param string	$input
	 * @param string	$expected
	 *
	 * @dataProvider	comment_string_provider
	 */
	public function test_remove_comments($input, $expected)
	{
		$this->assertEquals($expected, $this->database_helper->remove_comments($input));
	}

	/**
	 * @param array		$expected
	 * @param string	$sql
	 * @param string	$delimiter
	 *
	 * @dataProvider	sql_file_string_provider
	 */
	public function test_split_sql($expected, $sql, $delimiter)
	{
		$this->assertEquals($expected, $this->database_helper->split_sql_file($sql, $delimiter));
	}

	/**
	 * @param bool|array	$expected
	 * @param string		$test_string
	 *
	 * @dataProvider	prefix_test_case_provider
	 */
	public function test_validate_table_prefix($expected, $test_string)
	{
		$this->assertEquals($expected, $this->database_helper->validate_table_prefix('sqlite3', $test_string));
	}

	// Data provider for the remove comments function
	public function comment_string_provider()
	{
		return array(
			array(
				'abc',
				'abc',
			),
			array(
				'abc /* asdf */',
				"abc \n",
			),
			array(
				'abc /* asdf */ f',
				"abc \n f",
			),
			array(
				'# abc',
				"\n",
			),
		);
	}

	// Data provider for the sql file splitter function
	public function sql_file_string_provider()
	{
		return array(
			array(
				array(
					'abcd "efgh"' . "\n" . 'qwerty',
					'SELECT * FROM table',
				),
				'abcd "efgh"' . "\n" .
				'qwerty;' . "\n" .
				'SELECT * FROM table',
				';',
			),
		);
	}

	// Test data for prefix test
	public function prefix_test_case_provider()
	{
		return array(
			array(
				true,
				'phpbb_',
			),
			array(
				true,
				'phpbb',
			),
			array(
				array(
					array('title' => 'INST_ERR_DB_INVALID_PREFIX'),
				),
				'1hpbb_',
			),
			array(
				array(
					array('title' => 'INST_ERR_DB_INVALID_PREFIX'),
				),
				'?hpbb_',
			),
			array(
				array(
					array('title' => array('INST_ERR_PREFIX_TOO_LONG', 200)),
				),
				'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
			),
			array(
				array(
					array('title' => 'INST_ERR_DB_INVALID_PREFIX'),
					array('title' => array('INST_ERR_PREFIX_TOO_LONG', 200)),
				),
				'_AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
			),
		);
	}
}
