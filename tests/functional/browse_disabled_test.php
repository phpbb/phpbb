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
class phpbb_functional_browse_disabled_test extends phpbb_functional_test_case
{
	public function setUp(): void
	{
		parent::setUp();

		$this->login();
		$this->admin_login();

		// Disable board
		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=settings&sid=' . $this->sid);
		$form = $crawler->selectButton('Submit')->form();
		$form->setValues(['config[board_disable]' => 1]);

		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('div[class="successbox"] > p')->text());

		$this->logout();
	}

	public function tearDown(): void
	{
		$this->login();
		$this->admin_login();

		// Disable board
		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=settings&sid=' . $this->sid);
		$form = $crawler->selectButton('Submit')->form();
		$form->setValues(['config[board_disable]' => 0]);

		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('div[class="successbox"] > p')->text());

		$this->logout();

		parent::tearDown();
	}

	public function test_disabled_index_admin()
	{
		$this->login();
		$this->admin_login();

		// Board should be fully visible for all variations for admins
		for ($i = 0; $i <= 2; $i++)
		{
			$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=settings&sid=' . $this->sid);
			$form = $crawler->selectButton('Submit')->form();
			$form->setValues(['config[board_disable_access]' => $i]);

			$crawler = self::submit($form);
			$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('div[class="successbox"] > p')->text());

			$crawler = self::request('GET', 'index.php');
			$this->assertGreaterThan(0, $crawler->filter('.topiclist')->count());
			$this->assertContainsLang('BOARD_DISABLED', $crawler->filter('div[class="rules"]')->text());
		}

		$this->logout();
	}

	public function test_disabled_index_global_moderator()
	{
		$this->create_user('moderator-disabled-index');
		$this->add_user_group('GLOBAL_MODERATORS', ['moderator-disabled-index']);

		// Board should be fully visible for options 1 & 2
		for ($i = 0; $i <= 2; $i++)
		{
			$this->login();
			$this->admin_login();

			$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=settings&sid=' . $this->sid);
			$form = $crawler->selectButton('Submit')->form();
			$form->setValues(['config[board_disable_access]' => $i]);

			$crawler = self::submit($form);
			$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('div[class="successbox"] > p')->text());

			$this->logout();

			$this->login('moderator-disabled-index');

			$crawler = self::request('GET', 'index.php');

			if ($i == 0)
			{
				$this->assertEquals(0, $crawler->filter('.topiclist')->count(), 'Board should not be visible for option ' . $i);
				$this->assertContainsLang('BOARD_DISABLE', $crawler->filter('div#message')->text());
			}
			else
			{
				$this->assertGreaterThan(0, $crawler->filter('.topiclist')->count(), 'Board should be visible for option ' . $i);
				$this->assertContainsLang('BOARD_DISABLED', $crawler->filter('div[class="rules"]')->text());
			}
			$this->logout();
		}
	}

	public function test_disabled_index_local_moderator()
	{
		$user_id = $this->create_user('moduser-disabled-index');

		// Set m_delete to yes for user --> user has moderator permission
		$this->add_lang('acp/permissions');
		$this->login();
		$this->admin_login();
		$crawler = self::request('GET', "adm/index.php?i=acp_permissions&icat=16&mode=setting_user_local&user_id[0]=$user_id&forum_id[0]=2&type=m_&sid={$this->sid}");
		$form = $crawler->selectButton($this->lang('APPLY_PERMISSIONS'))->form();
		$data = array("setting[$user_id][2][m_edit]" => ACL_YES);
		$form->setValues($data);
		self::submit($form);
		$this->logout();

		// Board should be fully visible for option 2 only
		for ($i = 0; $i <= 2; $i++)
		{
			$this->login();
			$this->admin_login();

			$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=settings&sid=' . $this->sid);
			$form = $crawler->selectButton('Submit')->form();
			$form->setValues(['config[board_disable_access]' => $i]);

			$crawler = self::submit($form);
			$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('div[class="successbox"] > p')->text());

			$this->logout();

			$this->login('moduser-disabled-index');

			$crawler = self::request('GET', 'index.php');

			if ($i < 2)
			{
				$this->assertEquals(0, $crawler->filter('.topiclist')->count(), 'Board should not be visible for option ' . $i);
				$this->assertContainsLang('BOARD_DISABLE', $crawler->filter('div#message')->text());
			}
			else
			{
				$this->assertGreaterThan(0, $crawler->filter('.topiclist')->count(), 'Board should be visible for option ' . $i);
				$this->assertContainsLang('BOARD_DISABLED', $crawler->filter('div[class="rules"]')->text());
			}
			$this->logout();
		}
	}
}
