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

class phpbb_search_sphinx_clean_search_string_test extends phpbb_test_case
{
	/** @var \phpbb\search\fulltext_sphinx */
	protected $search;

	protected function setUp(): void
	{
		global $phpbb_container, $phpbb_root_path, $phpEx;

		parent::setUp();

		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('dbal.tools', new \stdClass());

		$config = new \phpbb\config\config(['fulltext_sphinx_id' => 'test1234test5678']);
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$error = null;

		$this->search = new \phpbb\search\fulltext_sphinx($error, $phpbb_root_path, $phpEx, null, $config, null, $user, $phpbb_dispatcher);
	}

	public function clean_search_string_data()
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
