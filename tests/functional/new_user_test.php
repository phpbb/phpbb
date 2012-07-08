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
class phpbb_functional_new_user_test extends phpbb_functional_test_case
{
	public function test_create_user()
	{
		$this->create_user('user');
		$this->login();
		$crawler = $this->request('GET', 'memberlist.php?sid=' . $this->sid);
		$this->assertContains('user', $crawler->filter('#memberlist tr')->eq(1)->text());
	}

	/**
	* @depends test_create_user
	*/
	public function test_delete_user()
	{
		$this->delete_user('user');
		$this->login();
		$crawler = $this->request('GET', 'memberlist.php?sid=' . $this->sid);
		$this->assertEquals(2, $crawler->filter('#memberlist tr')->count());
	}

	/**
	* @depends test_delete_user
	*/
	public function test_login_other()
	{
		$this->create_user('user');
		$this->login('user');
		$crawler = $this->request('GET', 'index.php');
		$this->assertContains('user', $crawler->filter('.icon-logout')->text());
		$this->delete_user('user');
	}
}
