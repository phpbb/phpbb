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
		// login
		$crawler = $this->request('GET', 'ucp.php');
		$form = $crawler->selectButton('Login')->form();
		$login = $this->client->submit($form, array('username' => 'admin', 'password' => 'admin'));

		// check for logout link
		$crawler = $this->request('GET', 'index.php');
		$this->assertContains("Logout [ admin ]", $crawler->filter('html')->text());
	}

	public function test_logout()
	{
		// logout
		$crawler = $this->request('GET', 'ucp.php?mode=logout');

		// try to access the UCP
		$crawler = $this->request('GET', 'ucp.php');
		$this->assertContains("Please login in order to access the User Control Panel.", $crawler->filter('h2')->text());

	}
}
