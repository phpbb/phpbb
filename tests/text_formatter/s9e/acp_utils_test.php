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

class phpbb_textformatter_s9e_acp_utils_test extends phpbb_test_case
{
	/**
	* @dataProvider get_analyse_bbcode_tests
	*/
	public function test_analyse_bbcode($definition, $template, $expected)
	{
		$container = $this->get_test_case_helpers()->set_s9e_services();
		$factory   = $container->get('text_formatter.s9e.factory');
		$acp_utils = new \phpbb\textformatter\s9e\acp_utils($factory);
		$actual    = $acp_utils->analyse_bbcode($definition, $template);

		$this->assertEquals($expected, $actual);
	}

	public function get_analyse_bbcode_tests()
	{
		return [
			[
				'[x]{TEXT}[/x]',
				'<b>{TEXT}</b>',
				[
					'status' => 'safe',
					'name'   => 'X'
				]
			],
			[
				'[hr]',
				'<hr>',
				[
					'status' => 'safe',
					'name'   => 'HR'
				]
			],
			[
				'[x]{TEXT}[/x]',
				'<script>{TEXT}</script>',
				[
					'status'     => 'unsafe',
					'name'       => 'X',
					'error_text' => 'Cannot allow unfiltered data in this context',
					'error_html' => '&lt;script&gt;
  <span class="highlight">&lt;xsl:apply-templates/&gt;</span>
&lt;/script&gt;'
				]
			],
			[
				'???',
				'<hr>',
				[
					'status'     => 'invalid_definition',
					'error_text' => 'Cannot interpret the BBCode definition'
				]
			],
			[
				'[x]{TEXT}[/x]',
				'<xsl:invalid',
				[
					'status'     => 'invalid_template',
					'name'       => 'X',
					'error_text' => "Invalid XSL: Couldn't find end of Start Tag invalid line 1\n"
				]
			],
		];
	}
}
