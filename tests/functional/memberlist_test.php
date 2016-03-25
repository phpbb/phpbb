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
class phpbb_functional_memberlist_test extends phpbb_functional_test_case
{
	public function test_memberlist()
	{
		$this->create_user('memberlist-test-user');
		// logs in as admin
		$this->login();
		$crawler = self::request('GET', 'memberlist.php?sid=' . $this->sid);
		$this->assertContains('memberlist-test-user', $crawler->text());

		// restrict by first character
		$crawler = self::request('GET', 'memberlist.php?first_char=m&sid=' . $this->sid);
		$this->assertContains('memberlist-test-user', $crawler->text());

		// make sure results for wrong character are not returned
		$crawler = self::request('GET', 'memberlist.php?first_char=a&sid=' . $this->sid);
		$this->assertNotContains('memberlist-test-user', $crawler->text());
	}

	public function test_viewprofile()
	{
		$this->login();
		// XXX hardcoded user id
		$crawler = self::request('GET', 'memberlist.php?mode=viewprofile&u=2&sid=' . $this->sid);
		$this->assertContains('admin', $crawler->filter('h2')->text());
	}

	protected function get_memberlist_leaders_table_crawler()
	{
		$crawler = self::request('GET', 'memberlist.php?mode=team&sid=' . $this->sid);
		return $crawler->filter('.forumbg-table');
	}

	public function test_leaders()
	{
		$this->login();
		$this->create_user('memberlist-test-moderator');

		$crawler = $this->get_memberlist_leaders_table_crawler();

		// Admin in admin group, but not in moderators
		$this->assertContains('admin', $crawler->eq(0)->text());
		$this->assertNotContains('admin', $crawler->eq(1)->text());

		// memberlist-test-user in neither group
		$this->assertNotContains('memberlist-test-user', $crawler->eq(0)->text());
		$this->assertNotContains('memberlist-test-user', $crawler->eq(1)->text());

		// memberlist-test-moderator in neither group
		$this->assertNotContains('memberlist-test-moderator', $crawler->eq(0)->text());
		$this->assertNotContains('memberlist-test-moderator', $crawler->eq(1)->text());
	}

	public function test_leaders_remove_users()
	{
		$this->login();

		// Remove admin from admins, but is now in moderators
		$this->remove_user_group('ADMINISTRATORS', array('admin'));
		$crawler = $this->get_memberlist_leaders_table_crawler();
		$this->assertNotContains('admin', $crawler->eq(0)->text());
		$this->assertContains('admin', $crawler->eq(1)->text());

		// Remove admin from moderators, should not be visible anymore
		$this->remove_user_group('GLOBAL_MODERATORS', array('admin'));
		$crawler = $this->get_memberlist_leaders_table_crawler();
		$this->assertNotContains('admin', $crawler->eq(0)->text());
		$this->assertNotContains('admin', $crawler->eq(1)->text());
	}

	public function test_leaders_add_users()
	{
		$this->login();

		// Add memberlist-test-moderator to moderators
		$this->add_user_group('GLOBAL_MODERATORS', array('memberlist-test-moderator'));
		$crawler = $this->get_memberlist_leaders_table_crawler();
		$this->assertNotContains('memberlist-test-moderator', $crawler->eq(0)->text());
		$this->assertContains('memberlist-test-moderator', $crawler->eq(1)->text());

		// Add admin to moderators, should be visible as moderator
		$this->add_user_group('GLOBAL_MODERATORS', array('admin'), true);
		$crawler = $this->get_memberlist_leaders_table_crawler();
		$this->assertNotContains('admin', $crawler->eq(0)->text());
		$this->assertContains('admin', $crawler->eq(1)->text());

		// Add admin to admins as leader, should be visible as admin, not moderator
		$this->add_user_group('ADMINISTRATORS', array('admin'), true, true);
		$crawler = $this->get_memberlist_leaders_table_crawler();
		$this->assertContains('admin', $crawler->eq(0)->text());
		$this->assertNotContains('admin', $crawler->eq(1)->text());
	}

	public function test_group_rank()
	{
		copy(__DIR__ . '/fixtures/files/valid.jpg', __DIR__ . '/../../phpBB/images/ranks/valid.jpg');

		$this->login();
		$this->admin_login();
		$this->add_lang(array('acp/groups', 'acp/posting'));

		// Set a group rank to the registered users
		$crawler = self::request('GET', "adm/index.php?sid={$this->sid}&i=acp_groups&mode=manage&action=edit&g=2");
		$form = $crawler->selectButton('Submit')->form();
		$form['group_rank']->select('1');
		$crawler = self::submit($form);
		$this->assertContainsLang('GROUP_UPDATED', $crawler->filter('.successbox')->text());

		// Set a rank image for site_admin
		$crawler = self::request('GET', "adm/index.php?sid={$this->sid}&i=acp_ranks&mode=ranks&action=edit&id=1");
		$form = $crawler->selectButton('Submit')->form();
		$form['rank_image']->select('valid.jpg');
		$crawler = self::submit($form);
		$this->assertContainsLang('RANK_UPDATED', $crawler->filter('.successbox')->text());

		$crawler = self::request('GET', 'memberlist.php?mode=group&g=2');
		$this->assertContains('memberlist-test-user', $crawler->text());

		unlink(__DIR__ . '/../../phpBB/images/ranks/valid.jpg');
	}
}
