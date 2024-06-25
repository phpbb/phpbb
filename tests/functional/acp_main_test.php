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

	public function test_all_acp_module_links()
	{
		$this->add_lang('common');
		$this->login();
		$this->admin_login();

		// Browse ACP main page
		$crawler = self::request('GET', 'index.php');
		$crawler = self::$client->click($crawler->selectLink($this->lang('ACP_SHORT'))->link());

		// Get all ACP module URLs array
		$acp_modules = $crawler->filter('.tabs a')->each(
			function ($node, $i)
			{
				return $node->link();
			}
		);

		// Browse all ACP modules and get their mode URLs array
		$acp_submodules = [];
		foreach ($acp_modules as $module)
		{
			$crawler = self::$client->click($module);
			$acp_submodules = array_merge($acp_submodules, $crawler->filter('.menu-block > ul a')->each(
				function ($node, $i)
				{
					return $node->link();
				}
			));
		}

		// Browse all ACP submodules' modes
		foreach ($acp_submodules as $acp_submodule)
		{
			self::$client->click($acp_submodule);
		}
	}
}
