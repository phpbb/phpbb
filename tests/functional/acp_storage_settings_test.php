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
class phpbb_functional_acp_storage_settings_test extends phpbb_functional_test_case
{
	public function test_storage_settings()
	{
		$this->add_lang(['common', 'acp/storage']);
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_storage&mode=settings&sid=' . $this->sid);
		$this->assertContainsLang('STORAGE_TITLE', $this->get_content());
		$this->assertContainsLang('STORAGE_TITLE_EXPLAIN', $this->get_content());

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('INFORMATION', $crawler->filter('div[class="errorbox"] > h3')->text());
		$this->assertContainsLang('STORAGE_NO_CHANGES', $crawler->filter('div[class="errorbox"] > p')->text());

		// Test empty storage paths - invalid
		$crawler = self::request('GET', 'adm/index.php?i=acp_storage&mode=settings&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'attachment[path]' => '',
			'avatar[path]' => '',
			'backup[path]' => '',
		]);
		$crawler = self::submit($form);
		$this->assertContainsLang('INFORMATION', $crawler->filter('div[class="errorbox"] > h3')->text());
		$this->assertStringContainsString($this->lang('STORAGE_PATH_NOT_SET', $this->lang('STORAGE_ATTACHMENT_TITLE')), $crawler->filter('div[class="errorbox"] > p')->text());
		$this->assertStringContainsString($this->lang('STORAGE_PATH_NOT_SET', $this->lang('STORAGE_AVATAR_TITLE')), $crawler->filter('div[class="errorbox"] > p')->text());
		$this->assertStringContainsString($this->lang('STORAGE_PATH_NOT_SET', $this->lang('STORAGE_BACKUP_TITLE')), $crawler->filter('div[class="errorbox"] > p')->text());

		// Test storage paths became not writable on the server afterwards
		// Unix tests only
		if (!defined('PHP_WINDOWS_VERSION_MAJOR'))
		{
			$crawler = self::request('GET', 'adm/index.php?i=acp_storage&mode=settings&sid=' . $this->sid);
			$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
			$values = $form->getValues();

			$attachments_storage_path = $values['attachment[path]'];
			$avatar_upload_path = $values['avatar[path]'];
			$backup_storage_path = $values['backup[path]'];

			$filesystem = new \phpbb\filesystem\filesystem;

			// Make the directory not writable
			global $phpbb_root_path;
			$filesystem->chmod($phpbb_root_path . $attachments_storage_path, 444);
			$filesystem->chmod($phpbb_root_path . $avatar_upload_path, 444);
			$filesystem->chmod($phpbb_root_path . $backup_storage_path, 444);
			$this->assertFalse($filesystem->is_writable($phpbb_root_path . $avatar_upload_path));

			// Visit ACP Storage settings again - warning should be displayed
			$crawler = self::request('GET', 'adm/index.php?i=acp_storage&mode=settings&sid=' . $this->sid);
			$this->assertContainsLang('WARNING', $crawler->filter('div[class="errorbox"] > h3')->text());
			$this->assertStringContainsString($this->lang('STORAGE_PATH_NOT_EXISTS', $this->lang('STORAGE_ATTACHMENT_TITLE')), $crawler->filter('div[class="errorbox"]')->text());
			$this->assertStringContainsString($this->lang('STORAGE_PATH_NOT_EXISTS', $this->lang('STORAGE_AVATAR_TITLE')), $crawler->filter('div[class="errorbox"]')->text());
			$this->assertStringContainsString($this->lang('STORAGE_PATH_NOT_EXISTS', $this->lang('STORAGE_BACKUP_TITLE')), $crawler->filter('div[class="errorbox"]')->text());

			// Restore default state
			$filesystem->chmod($phpbb_root_path . $attachments_storage_path, 777);
			$filesystem->chmod($phpbb_root_path . $avatar_upload_path, 777);
			$filesystem->chmod($phpbb_root_path . $backup_storage_path, 777);
			$this->assertTrue($filesystem->is_writable($phpbb_root_path . $attachments_storage_path));
			$this->assertTrue($filesystem->is_writable($phpbb_root_path . $avatar_upload_path));
			$this->assertTrue($filesystem->is_writable($phpbb_root_path . $backup_storage_path));

			$crawler = self::request('GET', 'adm/index.php?i=acp_storage&mode=settings&sid=' . $this->sid);
			$this->assertStringNotContainsString($this->lang('STORAGE_PATH_NOT_SET', $this->lang('STORAGE_ATTACHMENT_TITLE')), $this->get_content());
			$this->assertStringNotContainsString($this->lang('STORAGE_PATH_NOT_SET', $this->lang('STORAGE_AVATAR_TITLE')), $this->get_content());
			$this->assertStringNotContainsString($this->lang('STORAGE_PATH_NOT_SET', $this->lang('STORAGE_BACKUP_TITLE')), $this->get_content());

			$this->assertStringNotContainsString($this->lang('STORAGE_PATH_NOT_EXISTS', $this->lang('STORAGE_ATTACHMENT_TITLE')), $this->get_content());
			$this->assertStringNotContainsString($this->lang('STORAGE_PATH_NOT_EXISTS', $this->lang('STORAGE_AVATAR_TITLE')), $this->get_content());
			$this->assertStringNotContainsString($this->lang('STORAGE_PATH_NOT_EXISTS', $this->lang('STORAGE_BACKUP_TITLE')), $this->get_content());
		}
	}
}
