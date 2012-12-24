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
class phpbb_functional_auth_test extends phpbb_functional_test_case
{
	public function test_login()
	{
		$this->login();

		// check for logout link
		$crawler = $this->request('GET', 'index.php');
		$this->assert_response_success();
		$this->assertContains($this->lang('LOGOUT_USER', 'admin'), $crawler->filter('.navbar')->text());
	}

	public function test_login_other()
	{
		$this->create_user('anothertestuser');
		$this->login('anothertestuser');
		$crawler = $this->request('GET', 'index.php');
		$this->assert_response_success();
		$this->assertContains('anothertestuser', $crawler->filter('.icon-logout')->text());
	}

	/**
	* @depends test_login
	*/
	public function test_logout()
	{
		$this->login();
		$this->add_lang('ucp');

		// logout
		$crawler = $this->request('GET', 'ucp.php?sid=' . $this->sid . '&mode=logout');
		$this->assert_response_success();
		$this->assertContains($this->lang('LOGOUT_REDIRECT'), $crawler->filter('#message')->text());

		// look for a register link, which should be visible only when logged out
		$crawler = $this->request('GET', 'index.php');
		$this->assert_response_success();
		$this->assertContains($this->lang('REGISTER'), $crawler->filter('.navbar')->text());
	}

	public function test_acp_login()
	{
		$this->login();
		$this->admin_login();

		// check that we are logged in
		$crawler = $this->request('GET', 'adm/index.php?sid=' . $this->sid);
		$this->assert_response_success();
		$this->assertContains($this->lang('ADMIN_PANEL'), $crawler->filter('h1')->text());
	}
}
