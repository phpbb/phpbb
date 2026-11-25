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
class phpbb_functional_mcp_warn_notes_test extends phpbb_functional_test_case
{
	/**
	* The MCP warn-user, warn-post and user-notes pages load without PHP
	* notices. Their template events pass avatar_img, which must be set.
	*/
	public function test_warn_and_notes_pages()
	{
		$user_id = $this->create_user('mcp-warn-notes-user');

		$this->login('mcp-warn-notes-user');
		$post = $this->create_topic(2, 'MCP warn notes topic', 'MCP warn notes post');
		$this->logout();

		$this->login();
		$this->add_lang('mcp');

		$crawler = self::request('GET', "mcp.php?i=warn&mode=warn_user&u={$user_id}&sid={$this->sid}");
		$this->assertStringContainsString('mcp-warn-notes-user', $crawler->filter('#page-body')->text());

		$crawler = self::request('GET', "mcp.php?i=warn&mode=warn_post&p={$post['post_id']}&sid={$this->sid}");
		$this->assertStringContainsString('MCP warn notes post', $crawler->filter('#page-body')->text());

		$crawler = self::request('GET', "mcp.php?i=notes&mode=user_notes&u={$user_id}&sid={$this->sid}");
		$this->assertStringContainsString('mcp-warn-notes-user', $crawler->filter('#page-body')->text());
	}
}
