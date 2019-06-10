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
class phpbb_functional_jumpbox_test extends phpbb_functional_test_case
{
	public function test_jumpbox()
	{
		$this->login();

		$this->crawler = $this->get_quickmod_page(1, 'MERGE_TOPIC');
		$this->check_valid_jump('Your first forum');

		$link = $this->crawler->filter('#jumpbox')->selectLink('Your first category')->link()->getUri();
		$this->crawler = self::request('GET', substr($link, strpos($link, 'mcp.')));
		$this->check_valid_jump('Your first category');
	}

	protected function check_valid_jump($forum)
	{
		$this->assertContains($this->lang('FORUM') . ": $forum", $this->crawler->filter('#cp-main h2')->text(), $this->crawler->text());
	}
}
