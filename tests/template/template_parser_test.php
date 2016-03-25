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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/template_test_case.php';

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
