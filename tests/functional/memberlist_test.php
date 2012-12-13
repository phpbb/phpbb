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
	public function test_view()
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
}
