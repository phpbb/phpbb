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
class phpbb_functional_acp_main_test extends phpbb_functional_test_case
{
	public function test_acp_database_size()
	{
		$this->add_lang(['acp/common', 'acp/board']);
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid);
		$this->assertContainsLang('WELCOME_PHPBB', $this->get_content());
		$this->assertContainsLang('ADMIN_INTRO', $this->get_content());
		$this->assertContainsLang('DATABASE_SIZE', $crawler->filter('tbody > tr')->eq(2)->filter('td[class="tabled"]')->eq(0)->text());
		$this->assertNotContainsLang('NOT_AVAILABLE', $crawler->filter('tbody > tr')->eq(2)->filter('td[class="tabled"]')->eq(1)->text());
	}
}
