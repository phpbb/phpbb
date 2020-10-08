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
class phpbb_functional_acp_avatar_settings_test extends phpbb_functional_test_case
{
	public function test_avatar_upload_settings()
	{
		$this->add_lang(['acp/common', 'acp/board']);
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=avatar&sid=' . $this->sid);
		$this->assertContainsLang('ACP_AVATAR_SETTINGS', $this->get_content());
		$this->assertContainsLang('ACP_AVATAR_SETTINGS_EXPLAIN', $this->get_content());

		// Test disabling avatar uploading - valid
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[allow_avatar_upload]' => '0'
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('div[class="successbox"] > p')->text());

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=avatar&sid=' . $this->sid);

		// Test enabling avatar uploading - valid
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[allow_avatar_upload]' => '1'
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('div[class="successbox"] > p')->text());
	}
}
