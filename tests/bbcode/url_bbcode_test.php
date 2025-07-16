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

require_once __DIR__ . '/../../phpBB/includes/bbcode.php';
require_once __DIR__ . '/../../phpBB/includes/message_parser.php';

class phpbb_url_bbcode_test extends phpbb_test_case
{
	public static function url_bbcode_test_data()
	{
		return array(
			array(
				'url only',
				'[url]http://www.phpbb.com/community/[/url]',
				'[url:]http&#58;//www&#46;phpbb&#46;com/community/[/url:]'
			),
			array(
				'url with title',
				'[url=http://www.phpbb.com/community/]One line URL text[/url]',
				'[url=http&#58;//www&#46;phpbb&#46;com/community/:]One line URL text[/url:]'
			),
			array(
				'url with multiline title',
				"[url=http://www.phpbb.com/community/]Multiline\x0AURL\x0Atext[/url]",
				"[url=http&#58;//www&#46;phpbb&#46;com/community/:]Multiline\x0AURL\x0Atext[/url:]"
			),
			array(
				'unclosed url with multiline',
				"test [url] test \x0A test [url=http://www.phpbb.com/]test[/url] test",
				"test [url] test \x0A test [url=http&#58;//www&#46;phpbb&#46;com/:]test[/url:] test"
			),
			array(
				'unclosed url with multiline and title',
				"test [url=http://www.phpbb.com/]test \x0A [url]http://phpbb.com[/url] test",
				"test [url=http&#58;//www&#46;phpbb&#46;com/:]test \x0A [url]http://phpbb.com[/url:] test"
			),
		);
	}

	/**
	* @dataProvider url_bbcode_test_data
	*/
	public function test_url($description, $message, $expected)
	{
		global $user, $request, $symfony_request, $phpbb_dispatcher, $config, $phpEx, $request, $symfony_request;
		$phpEx = 'php';
		$config = new \phpbb\config\config([
			'max_post_font_size' => 0,
			'force_server_vars' => 0,
			'server_name' => 'testhost',
		]);
		$user = new phpbb_mock_user;
		$user->lang['UNAUTHORISED_BBCODE'] = 'UNAUTHORISED_BBCODE';
		$request = new phpbb_mock_request;
		$symfony_request = new \phpbb\symfony_request($request);
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		$bbcode = new bbcode_firstpass();
		$bbcode->message = $message;
		$bbcode->bbcode_init(false);
		$bbcode->parse_bbcode();
		$this->assertEquals($expected, $bbcode->message);
	}
}
