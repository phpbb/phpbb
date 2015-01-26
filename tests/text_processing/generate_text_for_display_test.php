<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
		global $cache, $user;

		parent::setUp();

		$cache = new phpbb_mock_cache;

		$user = new phpbb_mock_user;
		$user->optionset('viewcensors', false);
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
