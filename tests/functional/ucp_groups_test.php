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
class phpbb_functional_ucp_groups_test extends phpbb_functional_test_case
{
	public function groups_manage_test_data()
	{
		return array(
			array('#AA0000', 'GROUP_UPDATED'),
			array('AA0000', 'GROUP_UPDATED'),
			array('AA0000v', 'COLOUR_INVALID'),
			array('vAA0000', 'COLOUR_INVALID'),
			array('AAG000', 'COLOUR_INVALID'),
			array('#a00', 'GROUP_UPDATED'),
			array('ag0', 'COLOUR_INVALID'),
			array('#ag0', 'COLOUR_INVALID'),
			array('##bcc', 'COLOUR_INVALID'),
		);
	}

	/**
	* @dataProvider groups_manage_test_data
	*/
	public function test_groups_manage($input, $expected)
	{
		$this->login();
		$this->add_lang(array('ucp', 'acp/groups'));

		$crawler = $this->request('GET', 'ucp.php?i=groups&mode=manage&action=edit&g=5&sid=' . $this->sid);
		$this->assert_response_success();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['group_colour']->setValue($input);
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang($expected), $crawler->text());
	}
}
