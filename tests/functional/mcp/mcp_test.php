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
class phpbb_functional_mcp_test extends phpbb_functional_test_case
{
	public function test_all_mcp_module_links()
	{
		$this->login();
		$this->add_lang(['common', 'mcp']);

		// Browse MCP main page
		$crawler = self::request('GET', 'index.php');
		$crawler = self::$client->click($crawler->selectLink($this->lang('MCP_SHORT'))->link());

		// Get all MCP module URLs array
		$mcp_modules = $crawler->filter('.tabs a')->each(
			function ($node, $i)
			{
				return $node->link();
			}
		);

		// Browse all MCP modules and get their mode URLs array
		$mcp_submodules = [];
		foreach ($mcp_modules as $module)
		{
			$crawler = self::$client->click($module);
			$mcp_submodules = array_merge($mcp_submodules, $crawler->filter('.cp-menu a')->each(
				function ($node, $i)
				{
					return $node->link();
				}
			));
		}

		// Browse all MCP submodules' modes
		foreach ($mcp_submodules as $mcp_submodule)
		{
			self::$client->click($mcp_submodule);
		}
	}
}
