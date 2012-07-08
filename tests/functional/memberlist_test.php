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
}
