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
	* @dataProvider get_outermost_quote_authors_tests
	*/
	public function test_get_outermost_quote_authors($original, $expected)
	{
		$container = $this->get_test_case_helpers()->set_s9e_services();
		$utils     = $container->get('text_formatter.utils');
		$parser    = $container->get('text_formatter.parser');

		$this->assertSame($expected, $utils->get_outermost_quote_authors($parser->parse($original)));
	}

	public function get_outermost_quote_authors_tests()
	{
		return array(
			array(
				'No quotes here',
				array()
			),
			array(
				'[quote="foo"]..[/quote] [quote]..[/quote]',
				array('foo')
			),
			array(
				'[quote=foo]..[/quote] [quote]..[/quote]',
				array('foo')
			),
			array(
				'[quote=foo]..[/quote] [quote=bar]..[/quote]',
				array('foo', 'bar')
			),
			array(
				'[quote=foo].[quote=baz]..[/quote].[/quote] [quote=bar]..[/quote]',
				array('foo', 'bar')
			),
		);
	}

	/**
	* @dataProvider get_generate_quote_tests
	*/
	public function test_generate_quote($text, $params, $expected)
	{
		$container = $this->get_test_case_helpers()->set_s9e_services();
		$utils     = $container->get('text_formatter.utils');

		$this->assertSame($expected, $utils->generate_quote($text, $params));
	}

	public function get_generate_quote_tests()
	{
		return array(
			array(
				'...',
				array(),
				'[quote]...[/quote]',
			),
			array(
				'...',
				array('author' => 'Brian Kibler'),
				'[quote="Brian Kibler"]...[/quote]',
			),
			array(
				'...',
				array('author' => 'Brian "Brian Kibler" Kibler of Brian Kibler Gaming'),
				'[quote=\'Brian "Brian Kibler" Kibler of Brian Kibler Gaming\']...[/quote]',
			),
			array(
				'...',
				array('author' => "Brian Kibler Gaming's Brian Kibler"),
				'[quote="Brian Kibler Gaming\'s Brian Kibler"]...[/quote]',
			),
			array(
				'...',
				array('author' => "\\\"'"),
				'[quote="\\\\\\"\'"]...[/quote]',
			),
			array(
				'...',
				array('author' => 'Lots of doubles """ one single \' one backslash \\'),
				'[quote=\'Lots of doubles """ one single \\\' one backslash \\\\\']...[/quote]',
			),
			array(
				'...',
				array('author' => "Lots of singles ''' one double \" one backslash \\"),
				'[quote="Lots of singles \'\'\' one double \\" one backslash \\\\"]...[/quote]',
			),
			array(
				'...',
				array('author' => 'Defaults to doublequotes """\'\'\''),
				'[quote="Defaults to doublequotes \\"\\"\\"\'\'\'"]...[/quote]',
			),
			array(
				'...',
				array(
					'author'  => 'user',
					'post_id' => 123,
					'url'     => 'http://example.org'
				),
				'[quote=user post_id=123 url=http://example.org]...[/quote]',
			),
			array(
				'...',
				array(
					'author'  => 'user',
					'post_id' => 123,
					'user_id' => ANONYMOUS
				),
				'[quote=user post_id=123]...[/quote]',
			),
			array(
				'...',
				array('author'  => ' '),
				'[quote=" "]...[/quote]',
			),
			array(
				'...',
				array('author'  => 'foo bar'),
				'[quote="foo bar"]...[/quote]',
			),
			array(
				'...',
				array('author'  => '\\'),
				'[quote="\\\\"]...[/quote]',
			),
			array(
				'...',
				array('author'  => '[quote="foo"]'),
				'[quote=\'[quote="foo"]\']...[/quote]',
			),
			array(
				'...',
				array('author'  => '""'),
				'[quote=\'""\']...[/quote]',
			),
			array(
				'...',
				array('author'  => "''"),
				'[quote="\'\'"]...[/quote]',
			),
			array(
				'This is a long quote that is definitely going to exceed 80 characters',
				array(),
				"[quote]\nThis is a long quote that is definitely going to exceed 80 characters\n[/quote]",
			),
			array(
				'  This is a short quote on its own line  ',
				array(),
				'[quote]This is a short quote on its own line[/quote]',
			),
			array(
				"This is a short quote\non two lines",
				array(),
				"[quote]\nThis is a short quote\non two lines\n[/quote]",
			),
		);
	}

	/**
	* @dataProvider get_remove_bbcode_tests
	*/
	public function test_remove_bbcode($original, $name, $depth, $expected)
	{
		$container = $this->get_test_case_helpers()->set_s9e_services();
		$parser    = $container->get('text_formatter.parser');
		$utils     = $container->get('text_formatter.utils');

		$parsed = $parser->parse($original);
		$actual = $utils->unparse($utils->remove_bbcode($parsed, $name, $depth));

		$this->assertSame($expected, $actual);
	}

	public function get_remove_bbcode_tests()
	{
		return array(
			array(
				'Plain text',
				'b',
				1,
				'Plain text'
			),
			array(
				'[quote="u0"][quote="u1"][quote="u2"]q2[/quote]q1[/quote]q0[/quote][b]bold[/b]',
				'quote',
				0,
				'[b]bold[/b]',
			),
			array(
				'[quote="u0"][quote="u1"][quote="u2"]q2[/quote]q1[/quote]q0[/quote][b]bold[/b]',
				'quote',
				1,
				'[quote="u0"]q0[/quote][b]bold[/b]',
			),
			array(
				'[quote="u0"][quote="u1"][quote="u2"]q2[/quote]q1[/quote]q0[/quote][b]bold[/b]',
				'quote',
				2,
				'[quote="u0"][quote="u1"]q1[/quote]q0[/quote][b]bold[/b]',
			),
		);
	}
}
