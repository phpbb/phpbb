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
		$mcp_link = substr_replace($crawler->selectLink($this->lang('MCP_SHORT'))->attr('href'), '', 0, 2); // Remove leading ./
		$crawler = self::request('GET', $mcp_link);

		// Get all MCP module URLs array
		$mcp_modules = $crawler->filter('.tabs a')->each(
			function ($node, $i)
			{
				return substr_replace($node->attr('href'), '', 0, 2); // Remove leading ./
			}
		);

		// Browse all MCP modules and get their mode URLs array
		$mcp_submodules = [];
		foreach ($mcp_modules as $module)
		{
			$crawler = self::request('GET', $module);
			$mcp_submodules = array_merge($mcp_submodules, $crawler->filter('.cp-menu a')->each(
				function ($node, $i)
				{
					return substr_replace($node->attr('href'), '', 0, 2); // Remove leading ./
				}
			));
		}

		// Browse all MCP submodules' modes
		$mcp_submodule_modes = [];
		foreach ($mcp_submodules as $mcp_submodule)
		{
			$crawler = self::request('GET', $mcp_submodule);
		}
	}
}
