<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/template_test_case.php';
require_once dirname(__FILE__) . '/../../phpBB/phpbb/template/twig/node/expression/binary/equalequal.php';
require_once dirname(__FILE__) . '/../../phpBB/phpbb/template/twig/node/expression/binary/notequalequal.php';

class phpbb_template_template_parser_test extends phpbb_template_template_test_case
{
	public function test_set_filenames()
	{
		$this->template->set_filenames(array(
			'basic'	=> 'basic.html',
		));

		$this->assertEquals("passpasspass<!-- DUMMY var -->", str_replace(array("\n", "\r", "\t"), '', $this->template->assign_display('basic')));

		$this->template->set_filenames(array(
			'basic'	=> 'if.html',
		));

		$this->assertEquals("03!false", str_replace(array("\n", "\r", "\t"), '', $this->template->assign_display('basic')));
	}
}
