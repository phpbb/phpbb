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
class phpbb_functional_acp_permissions_test extends phpbb_functional_test_case
{
	public function test_set_permission()
	{
		$this->login();
		$this->admin_login();
		$this->add_lang('acp/permissions');

		// Permissions tab
		// XXX hardcoded id
		$crawler = $this->request('GET', 'adm/index.php?i=16&sid=' . $this->sid);
		$this->assert_response_success();
		// these language strings are html
		$this->assertContains($this->lang('ACP_PERMISSIONS_EXPLAIN'), $this->client->getResponse()->getContent());

		// User permissions
		$crawler = $this->request('GET', 'adm/index.php?i=acp_permissions&icat=16&mode=setting_user_global&sid=' . $this->sid);
		$this->assert_response_success();
		$this->assertContains($this->lang('ACP_USERS_PERMISSIONS_EXPLAIN'), $this->client->getResponse()->getContent());

		// Select admin
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$data = array('username[0]' => 'admin');
		$form->setValues($data);
		$crawler = $this->client->submit($form);
		$this->assert_response_success();
		$this->assertContains($this->lang('ACL_SET'), $crawler->filter('h1')->eq(1)->text());

		// XXX globals for phpbb_auth, refactor it later
		global $db, $cache;
		$db = $this->get_db();
		$cache = new phpbb_mock_null_cache;

		$auth = new phpbb_auth;
		// XXX hardcoded id
		$user_data = $auth->obtain_user_data(2);
		$auth->acl($user_data);
		$this->assertEquals(1, $auth->acl_get('u_hideonline'));

		// Set u_hideonline to never
		$form = $crawler->selectButton($this->lang('APPLY_PERMISSIONS'))->form();
		// initially it should be a yes
		$values = $form->getValues();
		$this->assertEquals(1, $values['setting[2][0][u_hideonline]']);
		// set to never
		$data = array('setting[2][0][u_hideonline]' => '0');
		$form->setValues($data);
		$crawler = $this->client->submit($form);
		$this->assert_response_success();
		$this->assertContains($this->lang('AUTH_UPDATED'), $crawler->text());

		// check acl again
		$auth = new phpbb_auth;
		// XXX hardcoded id
		$user_data = $auth->obtain_user_data(2);
		$auth->acl($user_data);
		$this->assertEquals(0, $auth->acl_get('u_hideonline'));
	}
}
