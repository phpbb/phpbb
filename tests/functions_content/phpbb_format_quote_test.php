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

require_once __DIR__ . '/../../phpBB/includes/message_parser.php';

class phpbb_functions_content_phpbb_format_quote_test extends phpbb_test_case
{
	/** @var \phpbb\language\language */
	protected $lang;

	protected function setUp(): void
	{
		global $cache, $user, $phpbb_root_path, $phpEx;

		$lang_file_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$this->lang = new \phpbb\language\language($lang_file_loader);
		$user = new \phpbb\user($this->lang, '\phpbb\datetime');
		$user->data['user_options'] = 230271;
		$cache = new phpbb_mock_cache();

		parent::setUp();
	}

	public function data_phpbb_format_quote()
	{
		return [
			[true, ['author' => 'admin', 'user_id' => 2], '[quote=&quot;username&quot;]quoted[/quote]', '', "[quote=admin user_id=2][quote=&quot;username&quot;]quoted[/quote][/quote]\n\n"],
			[false, ['author' => 'admin', 'user_id' => 2], '[quote=&quot;username&quot;]quoted[/quote]', '', "admin wrote:\n&gt; [quote=&quot;username&quot;]quoted[/quote]\n"],
			[true, ['author' => 'admin', 'user_id' => 2], '[quote=&quot;username&quot;]quoted[/quote]', "[url=http://viewtopic.php?p=1#p1]Subject: Foo[/url]\n\n", "[url=http://viewtopic.php?p=1#p1]Subject: Foo[/url]\n\n[quote=admin user_id=2][quote=&quot;username&quot;]quoted[/quote][/quote]\n\n"],
			[false, ['author' => 'admin', 'user_id' => 2],  '[quote=&quot;username&quot;]quoted[/quote]', "http://viewtopic.php?p=1#p1 - Subject: Foo\n\n", "http://viewtopic.php?p=1#p1 - Subject: Foo\n\nadmin wrote:\n&gt; [quote=&quot;username&quot;]quoted[/quote]\n"],
		];
	}


	/**
	 * @dataProvider data_phpbb_format_quote
	 */
	public function test_phpbb_format_quote($bbcode_status, $quote_attributes, $message, $message_link, $expected)
	{
		$text_formatter_utils = new \phpbb\textformatter\s9e\utils();

		$message_parser = new parse_message($message);

		phpbb_format_quote($this->lang, $message_parser, $text_formatter_utils, $bbcode_status, $quote_attributes, $message_link);

		$this->assertEquals($expected, $message_parser->message);
	}
}
