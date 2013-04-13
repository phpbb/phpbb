<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
		$crawler = $this->request('GET', 'memberlist.php?sid=' . $this->sid);
		$this->assert_response_success();
		$this->assertContains('memberlist-test-user', $crawler->text());

		// restrict by first character
		$crawler = $this->request('GET', 'memberlist.php?first_char=m&sid=' . $this->sid);
		$this->assert_response_success();
		$this->assertContains('memberlist-test-user', $crawler->text());

		// make sure results for wrong character are not returned
		$crawler = $this->request('GET', 'memberlist.php?first_char=a&sid=' . $this->sid);
		$this->assert_response_success();
		$this->assertNotContains('memberlist-test-user', $crawler->text());
	}

	public function test_viewprofile()
	{
		$this->login();
		// XXX hardcoded user id
		$crawler = $this->request('GET', 'memberlist.php?mode=viewprofile&u=2&sid=' . $this->sid);
		$this->assert_response_success();
		$this->assertContains('admin', $crawler->filter('h2')->text());
	}

	protected function get_memberlist_leaders_crawler()
	{
		$crawler = $this->request('GET', 'memberlist.php?mode=leaders&sid=' . $this->sid);
		$this->assert_response_success();

		return $crawler;
	}

	protected function get_crawler_table_text($crawler, $table)
	{
		return $crawler->filter('.forumbg-table')->eq($table)->text();
	}

	public function test_leaders()
	{
		$this->login();
		$this->create_user('memberlist-test-moderator');

		$crawler = $this->get_memberlist_leaders_crawler();

		// Admin in admin group, but not in moderators
		$this->assertContains('admin', $this->get_crawler_table_text($crawler, 0));
		$this->assertNotContains('admin', $this->get_crawler_table_text($crawler, 1));

		// memberlist-test-user in neither group
		$this->assertNotContains('memberlist-test-user', $this->get_crawler_table_text($crawler, 0));
		$this->assertNotContains('memberlist-test-user', $this->get_crawler_table_text($crawler, 1));

		// memberlist-test-moderator in neither group
		$this->assertNotContains('memberlist-test-moderator', $this->get_crawler_table_text($crawler, 0));
		$this->assertNotContains('memberlist-test-moderator', $this->get_crawler_table_text($crawler, 1));
	}

	public function test_leaders_remove_users()
	{
		$this->login();

		// Remove admin from admins, but is now in moderators
		$this->remove_user_group('ADMINISTRATORS', array('admin'));
		$crawler = $this->get_memberlist_leaders_crawler();
		$this->assertNotContains('admin', $this->get_crawler_table_text($crawler, 0));
		$this->assertContains('admin', $this->get_crawler_table_text($crawler, 1));

		// Remove admin from moderators, should not be visible anymore
		$this->remove_user_group('GLOBAL_MODERATORS', array('admin'));
		$crawler = $this->get_memberlist_leaders_crawler();
		$this->assertNotContains('admin', $this->get_crawler_table_text($crawler, 0));
		$this->assertNotContains('admin', $this->get_crawler_table_text($crawler, 1));
	}

	public function test_leaders_add_users()
	{
		$this->login();

		// Add memberlist-test-moderator to moderators
		$this->add_user_group('GLOBAL_MODERATORS', array('memberlist-test-moderator'));
		$crawler = $this->get_memberlist_leaders_crawler();
		$this->assertNotContains('memberlist-test-moderator', $this->get_crawler_table_text($crawler, 0));
		$this->assertContains('memberlist-test-moderator', $this->get_crawler_table_text($crawler, 1));
	}
}
