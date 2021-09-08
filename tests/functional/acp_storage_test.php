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
class phpbb_functional_acp_storage_test extends phpbb_functional_test_case
{
	public function test_acp_storage_free_space()
	{
		$this->add_lang(['acp/common', 'acp/storage']);
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_storage&mode=settings&sid=' . $this->sid);
		$this->assertContainsLang('STORAGE_TITLE', $this->get_content());
		$this->assertNotContainsLang('STORAGE_UNKNOWN', $crawler->filter('div#main div.main table.table1')->text());
	}
}
