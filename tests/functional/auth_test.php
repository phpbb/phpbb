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
	// since we need to login for each test (sessions aren't held) let's make it simple
	private function login()
	{
		$crawler = $this->request('GET', 'ucp.php');
		$this->assertContains("Please login in order to access the User Control Panel.", $crawler->filter('html')->text());

		$form = $crawler->selectButton('Login')->form();
		$login = $this->client->submit($form, array('username' => 'admin', 'password' => 'admin'));
	}

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
	// comment this test out for now while I get the logout test to work
	/*
	public function test_new_topic()
	{
		$this->login();

		//navigate to posting
	//	$crawler = $this->request('GET', 'posting.php?f=2&amp;mode=new');
		
	//	$form = $crawler->selectButton('Submit')->form();
		//$post_topic = $this->client->submit($form, array('subject' => 'Test', 'message' => 'This is a test'));
	}
	*/

	/**
	* @depends test_login
	*/
	public function test_logout()
	{
		$this->login();

		// logout
		$crawler = $this->request('GET', 'ucp.php?mode=logout');
		$this->assertContains('You have been successfully logged out.', $crawler->filter('#message')->text());

		// look for a register link, which should be visible only when logged out
		$crawler = $this->request('GET', 'index.php');
		$this->assertContains('Register', $crawler->filter('.linklist')->text());
	}
}
