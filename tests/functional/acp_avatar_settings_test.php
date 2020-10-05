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

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=avatar&sid=' . $this->sid);

		// Test empty avatar storage path while avatar uploading is enabled - invalid
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[avatar_path]' => ''
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('WARNING', $crawler->filter('div[class="errorbox"] > h3')->text());
		$this->assertContainsLang('AVATAR_NO_UPLOAD_PATH', $crawler->filter('div[class="errorbox"] > p')->text());

		// Test avatar upload path became not writable on the server afterwards
		// Unix tests only
		if (!defined('PHP_WINDOWS_VERSION_MAJOR'))
		{
			$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=avatar&sid=' . $this->sid);
			$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
			$values = $form->getValues();
			$avatar_upload_path = $values['config[avatar_path]'];
			$filesystem = new \phpbb\filesystem\filesystem;
			// Make the directory not writable
			global $phpbb_root_path;
			$filesystem->chmod($phpbb_root_path . $avatar_upload_path, 444);
			$this->assertFalse($filesystem->is_writable($phpbb_root_path . $avatar_upload_path));

			// Visit Avatar ACP settings again - warning should be displayed
			$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=avatar&sid=' . $this->sid);
			$this->assertContainsLang('WARNING', $crawler->filter('div[class="errorbox"] > h3')->text());
			$this->assertContainsLang('AVATAR_NO_UPLOAD_DIR', $crawler->filter('div[class="errorbox"] > p')->text());
			
			// Restore default state
			$filesystem->chmod($phpbb_root_path . $avatar_upload_path, 777);
			$this->assertTrue($filesystem->is_writable($phpbb_root_path . $avatar_upload_path));

			$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=avatar&sid=' . $this->sid);
			$this->assertNotContainsLang('AVATAR_NO_UPLOAD_DIR', $this->get_content());
			$this->assertNotContainsLang('AVATAR_NO_UPLOAD_PATH', $this->get_content());
		}
	}
}
