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

/**
* @group functional
*/
class phpbb_functional_mcp_logs_test extends phpbb_functional_test_case
{
	public function test_delete_logs()
	{
		$this->add_lang(['mcp', 'common']);

		$this->login();
		$crawler = self::request('GET', "mcp.php?i=mcp_logs&mode=front&sid={$this->sid}");
		$this->assertGreaterThanOrEqual(1, $crawler->filter('input[type=checkbox]')->count());

		$form = $crawler->selectButton($this->lang('DELETE_ALL'))->form();
		$crawler = self::submit($form);

		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);

		$this->assertCount(0, $crawler->filter('input[type=checkbox]'));
	}
}
