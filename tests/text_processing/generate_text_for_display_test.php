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
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
require_once dirname(__FILE__) . '/../mock/user.php';
require_once dirname(__FILE__) . '/../mock/cache.php';

class phpbb_text_processing_generate_text_for_display_test extends phpbb_test_case
{
	public function setUp()
	{
		global $cache, $user, $phpbb_dispatcher;

		parent::setUp();

		$cache = new phpbb_mock_cache;

		$user = new phpbb_mock_user;
		$user->optionset('viewcensors', false);

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
	}

	public function test_empty_string()
	{
		$this->assertSame('', generate_text_for_display('', '', '', 0));
	}

	public function test_zero_string()
	{
		$this->assertSame('0', generate_text_for_display('0', '', '', 0));
	}
}
