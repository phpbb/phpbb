<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';

class phpbb_text_processing_strip_bbcode_test extends phpbb_test_case
{
	public function test_legacy()
	{
		$original = '[b:20m4ill1]bold[/b:20m4ill1]';
		$expected = ' bold ';

		$actual = $original;
		strip_bbcode($actual);

		$this->assertSame($expected, $actual, '20m4ill1');
	}

	public function test_s9e()
	{
		$phpbb_container = $this->get_test_case_helpers()->set_s9e_services();

		$original = '<r><B><s>[b]</s>bold<e>[/b]</e></B></r>';
		$expected = ' bold ';

		$actual = $original;
		strip_bbcode($actual);

		$this->assertSame($expected, $actual);
	}
}
