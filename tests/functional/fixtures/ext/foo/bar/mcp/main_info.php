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

namespace foo\bar\mcp;

class main_info
{
	function module()
	{
		return [
			'filename'	=> '\foo\bar\mcp\main_module',
			'title'		=> 'MCP_FOOBAR_TITLE',
			'modes'		=> [
				'mode'		=> ['title' => 'MCP_FOOBAR_MODE', 'auth' => '', 'cat' => ['MCP_FOOBAR_TITLE']],
			],
		];
	}
}
