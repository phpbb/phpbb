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
			array('#AA0000', 'WRONG_DATA_COLOUR'),
			array('AA0000', 'GROUP_UPDATED'),
			array('AA0000v', 'WRONG_DATA_COLOUR'),
			array('AA00000', 'WRONG_DATA_COLOUR'),
			array('vAA0000', 'WRONG_DATA_COLOUR'),
			array('AAG000','WRONG_DATA_COLOUR'),
			array('a00', 'GROUP_UPDATED'),
			array('ag0', 'WRONG_DATA_COLOUR'),
			array('#aa0', 'WRONG_DATA_COLOUR'),
			array('AA0000 ', 'GROUP_UPDATED'),
			array('AA0000 abf', 'WRONG_DATA_COLOUR'),
			array('AA0000 AA0000', 'WRONG_DATA_COLOUR'),
			array('', 'GROUP_UPDATED'),
			array('000', 'GROUP_UPDATED'),
			array('000000', 'GROUP_UPDATED'),
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
