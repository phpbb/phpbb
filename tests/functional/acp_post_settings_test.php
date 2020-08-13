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
class phpbb_functional_acp_post_settings_test extends phpbb_functional_test_case
{
	public function test_allowed_schemes_links()
	{
		$this->add_lang(['acp/common', 'acp/board']);
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=post&sid=' . $this->sid);
		$this->assertContainsLang('ACP_POST_SETTINGS_EXPLAIN', $this->get_content());

		// Test trailing comma - invalid
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[allowed_schemes_links]' => 'http,https,ftp,'
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('WARNING', $crawler->filter('div[class="errorbox"] > h3')->text());
		$this->assertStringContainsString($this->lang('CSV_INVALID', $this->lang('ALLOWED_SCHEMES_LINKS')), $crawler->filter('div[class="errorbox"] > p')->text());

		// Test trailing comma and invalid scheme - invalid
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[allowed_schemes_links]' => 'http,https,2ftp,'
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('WARNING', $crawler->filter('div[class="errorbox"] > h3')->text());
		$this->assertStringContainsString($this->lang('CSV_INVALID', $this->lang('ALLOWED_SCHEMES_LINKS')), $crawler->filter('div[class="errorbox"] > p')->text());
		$this->assertStringContainsString($this->lang('URL_SCHEME_INVALID', $this->lang('ALLOWED_SCHEMES_LINKS'), '2ftp'), $crawler->filter('div[class="errorbox"] > p')->text());

		// Test empty setting - valid
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[allowed_schemes_links]' => ''
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('div[class="successbox"] > p')->text());

		// Restore default setting - 'http,https,ftp'
		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=post&sid=' . $this->sid);
		$this->assertContainsLang('ACP_POST_SETTINGS_EXPLAIN', $this->get_content());
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[allowed_schemes_links]' => 'http,https,ftp'
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('div[class="successbox"] > p')->text());
	}
}
