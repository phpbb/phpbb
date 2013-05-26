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
		$this->login();
		$this->admin_login();
		$this->add_lang(array('ucp', 'acp/groups'));

		// Manage Administrators group
		$crawler = $this->request('GET', $this->get_url() . '&g=5&sid=' . $this->sid);
		$this->assert_response_success();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['group_colour']->setValue($input);
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang($expected), $crawler->text());
	}
}
