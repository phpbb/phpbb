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

	public function test_leaders()
	{
		$this->login();
		$this->create_user('memberlist-test-moderator');

		$crawler = $this->request('GET', 'memberlist.php?mode=leaders&sid=' . $this->sid);
		$this->assert_response_success();

		// Admin in admin group, but not in moderators
		$this->assertContains('admin', $crawler->filter('.forumbg-table')->eq(0)->text());
		$this->assertNotContains('admin', $crawler->filter('.forumbg-table')->eq(1)->text());

		// memberlist-test-user in neither group
		$this->assertNotContains('memberlist-test-user', $crawler->filter('.forumbg-table')->eq(0)->text());
		$this->assertNotContains('memberlist-test-user', $crawler->filter('.forumbg-table')->eq(1)->text());

		// memberlist-test-moderator in neither group
		$this->assertNotContains('memberlist-test-moderator', $crawler->filter('.forumbg-table')->eq(0)->text());
		$this->assertNotContains('memberlist-test-moderator', $crawler->filter('.forumbg-table')->eq(1)->text());
	}

	public function test_leaders_remove_users()
	{
		$this->login();

		// Remove admin from admins, but is now in moderators
		$this->remove_user_group('ADMINISTRATORS', array('admin'));
		$crawler = $this->request('GET', 'memberlist.php?mode=leaders&sid=' . $this->sid);
		$this->assert_response_success();
		$this->assertNotContains('admin', $crawler->filter('.forumbg-table')->eq(0)->text());
		$this->assertContains('admin', $crawler->filter('.forumbg-table')->eq(1)->text());

		// Remove admin from moderators, should not be visible anymore
		$this->remove_user_group('GLOBAL_MODERATORS', array('admin'));
		$crawler = $this->request('GET', 'memberlist.php?mode=leaders&sid=' . $this->sid);
		$this->assertNotContains('admin', $crawler->filter('.forumbg-table')->eq(0)->text());
		$this->assertNotContains('admin', $crawler->filter('.forumbg-table')->eq(1)->text());
	}

	public function test_leaders_add_users()
	{
		$this->login();

		// Add memberlist-test-moderator to moderators
		$this->add_user_group('GLOBAL_MODERATORS', array('memberlist-test-moderator'));
		$crawler = $this->request('GET', 'memberlist.php?mode=leaders&sid=' . $this->sid);
		$this->assert_response_success();
		$this->assertNotContains('memberlist-test-moderator', $crawler->filter('.forumbg-table')->eq(0)->text());
		$this->assertContains('memberlist-test-moderator', $crawler->filter('.forumbg-table')->eq(1)->text());
	}
}
