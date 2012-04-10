<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @group functional
*/
class phpbb_functional_auth_test extends phpbb_functional_test_case
{
	public function test_login()
	{
		$this->login();

		// check for logout link
		$crawler = $this->request('GET', 'index.php');
		$this->assertContains('Logout [ admin ]', $crawler->filter('.navbar')->text());
	}

	/**
	* @depends test_login
	*/
	public function test_logout()
	{
		$this->login();

		// logout
		$crawler = $this->request('GET', 'ucp.php?sid=' . $this->sid . '&mode=logout');
		$this->assertContains('You have been successfully logged out.', $crawler->filter('#message')->text());

		// look for a register link, which should be visible only when logged out
		$crawler = $this->request('GET', 'index.php');
		$this->assertContains('Register', $crawler->filter('.navbar')->text());
	}
}
