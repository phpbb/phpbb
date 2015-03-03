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

require_once __DIR__ . '/../../../phpBB/includes/functions.php';
require_once __DIR__ . '/../../../phpBB/includes/functions_content.php';

class phpbb_textformatter_s9e_utils_test extends phpbb_test_case
{
	/**
	* @dataProvider get_unparse_tests
	*/
	public function test_unparse($original, $expected)
	{
		$container = $this->get_test_case_helpers()->set_s9e_services();
		$utils     = $container->get('text_formatter.utils');

		$this->assertSame($expected, $utils->unparse($original));
	}

	public function get_unparse_tests()
	{
		return array(
			array(
				'<t>Plain text</t>',
				'Plain text'
			),
			array(
				"<t>Multi<br/>\nline</t>",
				"Multi\nline"
			),
			array(
				'<r><B><s>[b]</s>bold<e>[/b]</e></B></r>',
				'[b]bold[/b]'
			)
		);
	}

	/**
	* @dataProvider get_remove_formatting_tests
	*/
	public function test_remove_formatting($original, $expected)
	{
		$container = $this->get_test_case_helpers()->set_s9e_services();
		$utils     = $container->get('text_formatter.utils');

		$this->assertSame($expected, $utils->remove_formatting($original));
	}

	public function get_remove_formatting_tests()
	{
		return array(
			array(
				'<t>Plain text</t>',
				'Plain text'
			),
			array(
				"<t>Multi<br/>\nline</t>",
				"Multi\nline"
			),
			array(
				'<r><B><s>[b]</s>bold<e>[/b]</e></B></r>',
				'bold'
			)
		);
	}

	/**
	* @dataProvider get_clean_formatting_tests
	*/
	public function test_clean_formatting($original, $expected)
	{
		$container = $this->get_test_case_helpers()->set_s9e_services();
		$utils     = $container->get('text_formatter.utils');

		$this->assertSame($expected, $utils->clean_formatting($original));
	}

	public function get_clean_formatting_tests()
	{
		return array(
			array(
				'<t>Plain text</t>',
				'Plain text'
			),
			array(
				"<t>Multi<br/>\nline</t>",
				"Multi\nline"
			),
			array(
				'<r><B><s>[b]</s>bold<e>[/b]</e></B></r>',
				' bold '
			)
		);
	}

	/**
	* @dataProvider get_remove_bbcode_tests
	*/
	public function test_remove_bbcode($original, $name, $depth, $expected)
	{
		$container = $this->get_test_case_helpers()->set_s9e_services();
		$utils     = $container->get('text_formatter.utils');

		$this->assertSame($expected, $utils->remove_bbcode($original, $name, $depth));
	}

	public function get_remove_bbcode_tests()
	{
		return array(
			array(
				'<t>Plain text</t>',
				'b',
				1,
				'<t>Plain text</t>'
			),
			array(
				'<r><QUOTE author="u0"><s>[quote="u0"]</s><QUOTE author="u1"><s>[quote="u1"]</s><QUOTE author="u2"><s>[quote="u2"]</s>q2<e>[/quote]</e></QUOTE>
q1<e>[/quote]</e></QUOTE>
q0<e>[/quote]</e></QUOTE>
<B><s>[b]</s>bold<e>[/b]</e></B></r>',
				'quote',
				0,
				'<r>
<B><s>[b]</s>bold<e>[/b]</e></B></r>'
			),
			array(
				'<r><QUOTE author="u0"><s>[quote="u0"]</s><QUOTE author="u1"><s>[quote="u1"]</s><QUOTE author="u2"><s>[quote="u2"]</s>q2<e>[/quote]</e></QUOTE>
q1<e>[/quote]</e></QUOTE>
q0<e>[/quote]</e></QUOTE>
<B><s>[b]</s>bold<e>[/b]</e></B></r>',
				'quote',
				1,
				'<r><QUOTE author="u0"><s>[quote="u0"]</s>
q0<e>[/quote]</e></QUOTE>
<B><s>[b]</s>bold<e>[/b]</e></B></r>'
			),
			array(
				'<r><QUOTE author="u0"><s>[quote="u0"]</s><QUOTE author="u1"><s>[quote="u1"]</s><QUOTE author="u2"><s>[quote="u2"]</s>q2<e>[/quote]</e></QUOTE>
q1<e>[/quote]</e></QUOTE>
q0<e>[/quote]</e></QUOTE>
<B><s>[b]</s>bold<e>[/b]</e></B></r>',
				'quote',
				2,
				'<r><QUOTE author="u0"><s>[quote="u0"]</s><QUOTE author="u1"><s>[quote="u1"]</s>
q1<e>[/quote]</e></QUOTE>
q0<e>[/quote]</e></QUOTE>
<B><s>[b]</s>bold<e>[/b]</e></B></r>'
			),
		);
	}
}
