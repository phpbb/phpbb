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
class phpbb_functional_acp_test extends phpbb_functional_test_case
{
	public function test_all_acp_module_links()
	{
		$this->login();
		$this->admin_login();
		$this->add_lang(['common']);

		// Browse ACP main page
		$crawler = self::request('GET', 'index.php');
		$crawler = self::$client->click($crawler->selectLink($this->lang('ACP_SHORT'))->link());
		self::assert_response_html();

		// Get all ACP module URLs array
		$acp_modules = $crawler->filter('li.tab a')->each(
			function ($node, $i)
			{
				// Filter out responsive mode links
				if (empty($node->attr('class')))
				{
					return $node->link();
				}
			}
		);
		$this->assertNotEmpty($acp_modules);

		// Browse all ACP modules and get their mode URLs array
		$acp_submodules = [];
		foreach ($acp_modules as $module)
		{
			$crawler = self::$client->click($module);
			self::assert_response_html();
			$acp_submodules = array_merge($acp_submodules, $crawler->filter('div.menu-block li a')->each(
				function ($node, $i)
				{
					return $node->link();
				}
			));
		}
		$this->assertNotEmpty($acp_submodules);

		// Browse all ACP submodules' modes
		foreach ($acp_submodules as $acp_submodule)
		{
			self::$client->click($acp_submodule);
			self::assert_response_html();
		}
	}
}
