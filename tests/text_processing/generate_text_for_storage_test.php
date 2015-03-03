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
require_once __DIR__ . '/../../phpBB/includes/utf/utf_tools.php';
require_once __DIR__ . '/../mock/container_builder.php';

class phpbb_text_processing_generate_text_for_storage_test extends phpbb_test_case
{
	public function setUp()
	{
		global $config, $phpbb_container, $phpbb_dispatcher;

		parent::setUp();

		$config = new \phpbb\config\config(array());
		set_config(null, null, null, $config);

		$phpbb_container = new phpbb_mock_container_builder;
		$phpbb_container->set('config', $config);
		$this->get_test_case_helpers()->set_s9e_services($phpbb_container);

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher;
	}

	/**
	* @dataProvider get_text_formatter_tests
	*/
	public function test_text_formatter($original, $expected, $allow_bbcode = true, $allow_urls = true, $allow_smilies = true, $setup = null)
	{
		$actual   = $original;
		$uid      = '';
		$bitfield = '';
		$flags    = 0;

		if (isset($setup))
		{
			$setup();
		}

		generate_text_for_storage($actual, $uid, $bitfield, $flags, $allow_bbcode, $allow_urls, $allow_smilies);

		$this->assertSame($expected, $actual);
	}

	public function get_text_formatter_tests()
	{
		return array(
			array(
				'Hello world',
				'<t>Hello world</t>'
			),
			array(
				'Hello [url=http://example.org]world[/url] :)',
				'<r>Hello <URL url="http://example.org"><s>[url=http://example.org]</s>world<e>[/url]</e></URL> <E>:)</E></r>'
			),
			array(
				'&<>"\'',
				'<t>&amp;&lt;&gt;"\'</t>'
			),
		);
	}
}
