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
class phpbb_functional_group_create_test extends phpbb_functional_test_case
{

	public function test_create_group()
	{
		$this->login();
		$this->admin_login();
		$this->add_lang('acp/groups');

		$crawler = self::request('GET', 'adm/index.php?i=acp_groups&mode=manage&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, array('group_name' => 'testtest'));

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, array('group_name' => 'testtest'));

		$this->assertContainsLang('GROUP_CREATED', $crawler->filter('#main')->text());
	}
}
