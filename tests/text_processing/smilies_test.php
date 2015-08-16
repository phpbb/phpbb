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

class phpbb_text_processing_smilies_test extends phpbb_test_case
{
	/**
	* @dataProvider get_text_formatter_tests
	*/
	public function test_text_formatter($original, $expected)
	{
		$container = $this->get_test_case_helpers()->set_s9e_services(null, __DIR__ . '/fixtures/smilies.xml');
		$parser = $container->get('text_formatter.parser');
		$renderer = $container->get('text_formatter.renderer');

		$this->assertSame($expected, $renderer->render($parser->parse($original)));
	}

	public function get_text_formatter_tests()
	{
		return array(
			array(
				':) beginning',
				'<img class="smilies" src="phpBB/images/smilies/icon_e_smile.gif" alt=":)" title="Smile"> beginning'
			),
			array(
				'end :)',
				'end <img class="smilies" src="phpBB/images/smilies/icon_e_smile.gif" alt=":)" title="Smile">'
			),
			array(
				':)',
				'<img class="smilies" src="phpBB/images/smilies/icon_e_smile.gif" alt=":)" title="Smile">'
			),
			array(
				'xx (18) 8) xx',
				'xx (18) <img class="smilies" src="phpBB/images/smilies/custom.gif" alt="8)" title="8)"> xx'
			),
		);
	}
}
