<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @group functional
*/
abstract class phpbb_functional_common_groups_test extends phpbb_functional_test_case
{
	abstract protected function get_url();

	/**
	* Get group_manage form
	* @param int $group_id ID of the group that should be managed
	*/
	protected function get_group_manage_form($group_id = 5)
	{
		// Manage Administrators group
		$crawler = $this->request('GET', $this->get_url() . "&g=$group_id&sid=" . $this->sid);
		$this->assert_response_success();
		//var_export($this->client->getResponse()->getContent());
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		return $form;
	}

	/**
	* Execute login calls and add_lang() calls for tests
	*/
	protected function group_manage_login()
	{
		$this->login();
		$this->admin_login();
		$this->add_lang(array('ucp', 'acp/groups'));
	}

	public function groups_manage_test_data()
	{
		return array(
			array('', 'GROUP_UPDATED'),
			array('aa0000', 'GROUP_UPDATED'),

			array('AAG000','WRONG_DATA_COLOUR'),
			array('#AA0000', 'WRONG_DATA_COLOUR'),
		);
	}

	/**
	* @dataProvider groups_manage_test_data
	*/
	public function test_groups_manage($input, $expected)
	{
		$this->group_manage_login();

		// Manage Administrators group
		$form = $this->get_group_manage_form();
		$form['group_colour']->setValue($input);
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang($expected), $crawler->text());
	}
}
