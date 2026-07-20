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

/**
* Exposes the protected sphinx_clean_search_string() for testing
*/
class phpbb_search_sphinx_clean_search_string_test_wrapper extends \phpbb\search\backend\fulltext_sphinx
{
	public function sphinx_clean_search_string($search_string)
	{
		return parent::sphinx_clean_search_string($search_string);
	}
}

class phpbb_search_sphinx_clean_search_string_test extends phpbb_test_case
{
	/** @var phpbb_search_sphinx_clean_search_string_test_wrapper */
	protected $search;

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx;

		parent::setUp();

		$auth = $this->createMock(\phpbb\auth\auth::class);
		$config = new \phpbb\config\config(['fulltext_sphinx_id' => 'test1234test5678']);
		$db = $this->createMock(\phpbb\db\driver\driver_interface::class);
		$db_tools = $this->createMock(\phpbb\db\tools\tools_interface::class);
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$language = new \phpbb\language\language($lang_loader);
		$log = $this->createMock(\phpbb\log\log::class);
		$user = new \phpbb\user($language, '\phpbb\datetime');

		$this->search = new phpbb_search_sphinx_clean_search_string_test_wrapper($auth, $config, $db, $db_tools, $phpbb_dispatcher, $language, $log, $user, $phpbb_root_path, $phpEx);
	}

	public static function clean_search_string_data()
	{
		return [
			// Unmatched closing parenthesis is removed together with its operator (PHPBB-17614)
			['+test +)', '+test '],
			['+test +) ', '+test  '],
			['+test)', '+test'],
			['+) ', ' '],
			// Unmatched opening parenthesis is removed
			['+test +(word ', '+test +word '],
			['+a +)) +(( ', '+a   '],
			// Empty groups are removed together with their operator
			['+test +() ', '+test   '],
			['+test +( +) ', '+test   '],
			// Operators without an operand are removed
			['+test -) ', '+test  '],
			['+(word |) ', '+(word ) '],
			// Unmatched quotation mark is removed
			['+test +" ', '+test  '],
			// Balanced groups and phrases are preserved
			['+(either +or) ', '+(either +or) '],
			['+((nested)) ', '+((nested)) '],
			['+test +"exact phrase" ', '+test +"exact phrase" '],
			// Parentheses within quotation marks carry no meaning and do not affect balancing
			['+(a +"b )" +c) ', '+(a +"b )" +c) '],
			// Hyphenated words keep their special treatment
			['know-it-all', '("know it all"|knowitall*)'],
			// Plain queries are left alone
			['+test +word ', '+test +word '],
		];
	}

	/**
	* @dataProvider clean_search_string_data
	*/
	public function test_clean_search_string($input, $expected)
	{
		$this->assertEquals($expected, $this->search->sphinx_clean_search_string($input));
	}
}
