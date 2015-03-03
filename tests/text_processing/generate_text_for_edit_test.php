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

require_once __DIR__ . '/../../phpBB/includes/functions.php';
require_once __DIR__ . '/../../phpBB/includes/functions_content.php';

class phpbb_text_processing_generate_text_for_edit_test extends phpbb_test_case
{
	/**
	* @dataProvider get_legacy_tests
	*/
	public function test_legacy($original, $expected, $uid = '', $flags = 0)
	{
		global $cache, $user, $phpbb_dispatcher;

		$cache = new phpbb_mock_cache;
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher;

		$user = new phpbb_mock_user;
		$user->optionset('viewcensors', false);

		$return = generate_text_for_edit($original, $uid, $flags);

		$this->assertSame($expected, $return['text']);
	}

	public function get_legacy_tests()
	{
		return array(
			array(
				'',
				''
			),
			array(
				'0',
				'0'
			),
			array(
				'Hello [url=http&#58;//example&#46;org:1f4coh9x]world[/url:1f4coh9x] <!-- s:) --><img src="{SMILIES_PATH}/icon_e_smile.gif" alt=":)" title="Smile" /><!-- s:) -->',
				'Hello [url=http&#58;//example&#46;org]world[/url] :)',
				'1f4coh9x',
				0
			),
			array(
				"&amp;&lt;&gt;&quot;'",
				"&amp;&lt;&gt;&quot;'"
			)
		);
	}

	/**
	* @dataProvider get_text_formatter_tests
	*/
	public function test_text_formatter($original, $expected)
	{
		global $phpbb_dispatcher;
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher;
		$this->get_test_case_helpers()->set_s9e_services();

		$return = generate_text_for_edit($original, '', 0);

		$this->assertSame($expected, $return['text']);
	}

	public function get_text_formatter_tests()
	{
		return array(
			array(
				'<t>Plain text</t>',
				'Plain text'
			),
			array(
				'<r>Hello <URL url="http://example.org"><s>[url=http://example.org]</s>world<e>[/url]</e></URL> <E>:)</E></r>',
				'Hello [url=http://example.org]world[/url] :)'
			),
			array(
				'<t>&amp;&lt;&gt;"\'</t>',
				"&amp;&lt;&gt;&quot;'"
			)
		);
	}
}
