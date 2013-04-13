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
class phpbb_functional_extension_permission_lang_test extends phpbb_functional_test_case
{
	protected $phpbb_extension_manager;

	public function setUp()
	{
		parent::setUp();

		$this->phpbb_extension_manager = $this->get_extension_manager();

		$this->purge_cache();

		$this->login();
		$this->admin_login();
		$this->add_lang('acp/permissions');
	}

	public function test_auto_include_permission_lang_from_extensions()
	{
		$this->phpbb_extension_manager->enable('foo/bar');

		// User permissions
		$crawler = $this->request('GET', 'adm/index.php?i=acp_permissions&icat=16&mode=setting_user_global&sid=' . $this->sid);
		$this->assert_response_success();

		// Select admin
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$data = array('username[0]' => 'admin');
		$form->setValues($data);
		$crawler = $this->client->submit($form);
		$this->assert_response_success();
		$this->assertContains('Can view foo', $crawler->filter('body')->text());
	}

	public function permissions_data()
	{
		return array(
			// description
			// permission type
			// permission name
			// mode
			// object name
			// object id
			array(
				'user permission',
				'u_',
				'acl_u_foo',
				'setting_user_global',
				'user_id',
				2,
			),
		);
	}
}
