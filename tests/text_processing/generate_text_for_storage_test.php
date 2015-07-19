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
require_once __DIR__ . '/../../phpBB/includes/functions_compatibility.php';
require_once __DIR__ . '/../../phpBB/includes/functions_content.php';
require_once __DIR__ . '/../../phpBB/includes/utf/utf_tools.php';

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
	public function test_text_formatter($original, $expected, $allow_bbcode, $allow_urls, $allow_smilies, $allow_img_bbcode, $allow_flash_bbcode, $allow_quote_bbcode, $allow_url_bbcode, $setup = null)
	{
		$actual   = $original;
		$uid      = '';
		$bitfield = '';
		$flags    = 0;

		if (isset($setup))
		{
			$setup();
		}

		generate_text_for_storage($actual, $uid, $bitfield, $flags, $allow_bbcode, $allow_urls, $allow_smilies, $allow_img_bbcode, $allow_flash_bbcode, $allow_quote_bbcode, $allow_url_bbcode);

		$this->assertSame($expected, $actual);
	}

	public function get_text_formatter_tests()
	{
		return array(
			array(
				'Hello world',
				'<t>Hello world</t>',
				true,
				true,
				true,
				true,
				true,
				true,
				true,
			),
			array(
				'Hello [url=http://example.org]world[/url] :)',
				'<r>Hello <URL url="http://example.org"><s>[url=http://example.org]</s>world<e>[/url]</e></URL> <E>:)</E></r>',
				true,
				true,
				true,
				true,
				true,
				true,
				true,
			),
			array(
				'&<>"\'',
				'<t>&amp;&lt;&gt;"\'</t>',
				true,
				true,
				true,
				true,
				true,
				true,
				true,
			),
			array(
				'[b]..[/b] http://example.org :) [img]http://example.org/img.png[/img] [flash=123,123]http://example.org/flash.swf[/flash] [quote]...[/quote] [url]http://example.org[/url]',
				'<t>[b]..[/b] http://example.org :) [img]http://example.org/img.png[/img] [flash=123,123]http://example.org/flash.swf[/flash] [quote]...[/quote] [url]http://example.org[/url]</t>',
				false,
				false,
				false,
				false,
				false,
				false,
				false,
			),
			array(
				'[b]..[/b] http://example.org :) [img]http://example.org/img.png[/img] [flash=123,123]http://example.org/flash.swf[/flash] [quote]...[/quote] [url]http://example.org[/url]',
				'<r><B><s>[b]</s>..<e>[/b]</e></B> http://example.org :) [img]http://example.org/img.png[/img] [flash=123,123]http://example.org/flash.swf[/flash] [quote]...[/quote] [url]http://example.org[/url]</r>',
				true,
				false,
				false,
				false,
				false,
				false,
				false,
			),
			array(
				'[b]..[/b] http://example.org :) [img]http://example.org/img.png[/img] [flash=123,123]http://example.org/flash.swf[/flash] [quote]...[/quote] [url]http://example.org[/url]',
				'<r>[b]..[/b] <URL url="http://example.org">http://example.org</URL> :) [img]<URL url="http://example.org/img.png">http://example.org/img.png</URL>[/img] [flash=123,123]<URL url="http://example.org/flash.swf">http://example.org/flash.swf</URL>[/flash] [quote]...[/quote] [url]<URL url="http://example.org">http://example.org</URL>[/url]</r>',
				false,
				true,
				false,
				false,
				false,
				false,
				true,
			),
			array(
				'[b]..[/b] http://example.org :) [img]http://example.org/img.png[/img] [flash=123,123]http://example.org/flash.swf[/flash] [quote]...[/quote] [url]http://example.org[/url]',
				'<r>[b]..[/b] http://example.org <E>:)</E> [img]http://example.org/img.png[/img] [flash=123,123]http://example.org/flash.swf[/flash] [quote]...[/quote] [url]http://example.org[/url]</r>',
				false,
				false,
				true,
				false,
				false,
				false,
				false,
			),
			array(
				'[b]..[/b] http://example.org :) [img]http://example.org/img.png[/img] [flash=123,123]http://example.org/flash.swf[/flash] [quote]...[/quote] [url]http://example.org[/url]',
				'<r><B><s>[b]</s>..<e>[/b]</e></B> http://example.org :) <IMG src="http://example.org/img.png"><s>[img]</s>http://example.org/img.png<e>[/img]</e></IMG> [flash=123,123]http://example.org/flash.swf[/flash] [quote]...[/quote] [url]http://example.org[/url]</r>',
				true,
				false,
				false,
				true,
				false,
				false,
				false,
			),
			array(
				'[b]..[/b] http://example.org :) [img]http://example.org/img.png[/img] [flash=123,123]http://example.org/flash.swf[/flash] [quote]...[/quote] [url]http://example.org[/url]',
				'<r><B><s>[b]</s>..<e>[/b]</e></B> http://example.org :) [img]http://example.org/img.png[/img] <FLASH height="123" url="http://example.org/flash.swf" width="123"><s>[flash=123,123]</s>http://example.org/flash.swf<e>[/flash]</e></FLASH> [quote]...[/quote] [url]http://example.org[/url]</r>',
				true,
				false,
				false,
				false,
				true,
				false,
				false,
			),
			array(
				'[b]..[/b] http://example.org :) [img]http://example.org/img.png[/img] [flash=123,123]http://example.org/flash.swf[/flash] [quote]...[/quote] [url]http://example.org[/url]',
				'<r><B><s>[b]</s>..<e>[/b]</e></B> http://example.org :) [img]http://example.org/img.png[/img] [flash=123,123]http://example.org/flash.swf[/flash] <QUOTE><s>[quote]</s>...<e>[/quote]</e></QUOTE> [url]http://example.org[/url]</r>',
				true,
				false,
				false,
				false,
				false,
				true,
				false,
			),
			array(
				'[b]..[/b] http://example.org :) [img]http://example.org/img.png[/img] [flash=123,123]http://example.org/flash.swf[/flash] [quote]...[/quote] [url]http://example.org[/url]',
				'<r><B><s>[b]</s>..<e>[/b]</e></B> http://example.org :) [img]http://example.org/img.png[/img] [flash=123,123]http://example.org/flash.swf[/flash] [quote]...[/quote] <URL url="http://example.org"><s>[url]</s>http://example.org<e>[/url]</e></URL></r>',
				true,
				false,
				false,
				false,
				false,
				false,
				true,
			),
		);
	}
}
