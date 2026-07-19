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
		global $phpbb_root_path;

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
			'attachment[provider]' => \phpbb\storage\provider\local::class,
			'attachment[path]' => '',
			'avatar[provider]' => \phpbb\storage\provider\local::class,
			'avatar[path]' => '',
			'backup[provider]' => \phpbb\storage\provider\local::class,
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
			$filesystem = new \phpbb\filesystem\filesystem;
			$paths = [];

			// Set local storage for this test
			$this->set_storage_provider(\phpbb\storage\provider\local::class);

			try
			{
				$crawler = self::request('GET', 'adm/index.php?i=acp_storage&mode=settings&sid=' . $this->sid);
				$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
				$values = $form->getValues();

				$paths = [
					'attachment' => $values['attachment[path]'],
					'avatar' => $values['avatar[path]'],
					'backup' => $values['backup[path]'],
				];

				// Make the directories not writable
				foreach ($paths as $path)
				{
					$filesystem->chmod($phpbb_root_path . $path, 444);
					$this->assertFalse($filesystem->is_writable($phpbb_root_path . $path));
				}

				// Visit ACP Storage settings again - warning should be displayed
				$crawler = self::request('GET', 'adm/index.php?i=acp_storage&mode=settings&sid=' . $this->sid);
				$this->assertContainsLang('WARNING', $crawler->filter('div[class="errorbox"] > h3')->text());
				$errorbox_text = $crawler->filter('div[class="errorbox"]')->text();
				foreach (array_keys($paths) as $storage_name)
				{
					$storage_title = $this->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE');
					$this->assertStringContainsString($this->lang('STORAGE_PATH_NOT_EXISTS', $storage_title), $errorbox_text);
				}

				// Restore default state
				foreach ($paths as $path)
				{
					$filesystem->chmod($phpbb_root_path . $path, 777);
					$this->assertTrue($filesystem->is_writable($phpbb_root_path . $path));
				}

				$crawler = self::request('GET', 'adm/index.php?i=acp_storage&mode=settings&sid=' . $this->sid);
				$content = $this->get_content();
				foreach (array_keys($paths) as $storage_name)
				{
					$storage_title = $this->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE');
					$this->assertStringNotContainsString($this->lang('STORAGE_PATH_NOT_SET', $storage_title), $content);
					$this->assertStringNotContainsString($this->lang('STORAGE_PATH_NOT_EXISTS', $storage_title), $content);
				}
			}
			finally
			{
				foreach ($paths as $path)
				{
					$filesystem->chmod($phpbb_root_path . $path, 777);
				}

				// Restore db storage after test is finished
				$this->set_storage_provider(\phpbb\tests\functional\storage\db_provider::class);
			}
		}
	}

	private function set_storage_provider(string $provider): void
	{
		$config_names = [];
		foreach (['attachment', 'avatar', 'backup'] as $storage_name)
		{
			$config_names[] = 'storage\\' . $storage_name . '\\provider';
		}

		$sql = 'UPDATE ' . CONFIG_TABLE . "
			SET config_value = '" . $this->db->sql_escape($provider) . "'
			WHERE " . $this->db->sql_in_set('config_name', $config_names);
		$this->db->sql_query($sql);
		$this->purge_cache();
	}
}
