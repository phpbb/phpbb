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

class phpbb_text_processing_tickets_test extends phpbb_test_case
{
	/**
	* @dataProvider get_tickets_data
	*/
	public function test_tickets($ticket_id, $original, $expected, $fixture, $before_assert, $after_assert)
	{
		global $phpbb_container;

		$phpbb_container = new phpbb_mock_container_builder;

		$this->get_test_case_helpers()->set_s9e_services($phpbb_container, $fixture);

		$parser   = $phpbb_container->get('text_formatter.parser');
		$renderer = $phpbb_container->get('text_formatter.renderer');

		if (isset($before_assert))
		{
			$test = $this;
			$before_assert(get_defined_vars());
		}

		$parsed_text = $parser->parse($original);

		$this->assertSame($expected, $renderer->render($parsed_text));

		if (isset($after_assert))
		{
			$test = $this;
			$after_assert(get_defined_vars());
		}
	}

	public function get_tickets_data()
	{
		$tests = array();

		foreach (glob(__DIR__ . '/tickets_data/*.txt') as $txt_filename)
		{
			$ticket_id     = basename($txt_filename, '.txt');
			$html_filename = substr($txt_filename, 0, -3) . 'html';
			$xml_filename  = substr($txt_filename, 0, -3) . 'xml';
			$before_filename = substr($txt_filename, 0, -3) . 'before.php';
			$after_filename  = substr($txt_filename, 0, -3) . 'after.php';

			if (!file_exists($xml_filename))
			{
				$xml_filename = __DIR__ . '/../fixtures/empty.xml';
			}

			$before_assert = null;
			if (file_exists($before_filename))
			{
				include($before_filename);
				$before_assert = 'before_assert_' . strtolower(str_replace('-', '_', $ticket_id));
			}

			$after_assert = null;
			if (file_exists($after_filename))
			{
				include($after_filename);
				$after_assert = 'after_assert_' . strtolower(str_replace('-', '_', $ticket_id));
			}

			$tests[] = array(
				$ticket_id,
				file_get_contents($txt_filename),
				file_get_contents($html_filename),
				$xml_filename,
				$before_assert,
				$after_assert
			);
		}

		return $tests;
	}
}
