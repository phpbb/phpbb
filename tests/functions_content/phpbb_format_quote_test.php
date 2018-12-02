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

require_once dirname(__FILE__) . '/../../phpBB/includes/message_parser.php';

class phpbb_functions_content_phpbb_format_quote_test extends phpbb_test_case
{
	public function setUp()
	{
		global $cache, $user, $phpbb_root_path, $phpEx;

		$lang_file_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_file_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$cache = new phpbb_mock_cache();

		parent::setUp();
	}

	public function data_phpbb_format_quote()
	{
		return [
			[true, ['author' => 'admin', 'user_id' => 2], '[quote=&quot;username&quot;]quoted[/quote]', "[quote=admin user_id=2][quote=&quot;username&quot;]quoted[/quote][/quote]\n\n"],
			[false, ['author' => 'admin', 'user_id' => 2], '[quote=&quot;username&quot;]quoted[/quote]', "admin wrote:\n&gt; [quote=&quot;username&quot;]quoted[/quote]\n"]
		];
	}


	/**
	 * @dataProvider data_phpbb_format_quote
	 */
	public function test_phpbb_format_quote($bbcode_status, $quote_attributes, $message, $expected)
	{
		$text_formatter_utils = new \phpbb\textformatter\s9e\utils();

		$message_parser = new parse_message($message);

		phpbb_format_quote($bbcode_status, $quote_attributes, $text_formatter_utils, $message_parser);

		$this->assertEquals($expected, $message_parser->message);
	}
}
