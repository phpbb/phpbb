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
	public function test_groups_manage()
	{
		$values = array();
		$this->login();
		$this->add_lang(array('ucp', 'acp/groups'));

		$crawler = $this->request('GET', 'ucp.php?i=groups&mode=manage&action=edit&g=5&sid=' . $this->sid);
		$this->assert_response_success();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['group_colour']->setValue('#AA0000');
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('GROUP_UPDATED'), $crawler->text());

		$crawler = $this->request('GET', 'ucp.php?i=groups&mode=manage&action=edit&g=5&sid=' . $this->sid);
		$this->assert_response_success();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$values = $form->getValues();
		$this->assertContains('AA0000', $values['group_colour']);
		$form['group_colour']->setValue('AA0000');
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('GROUP_UPDATED'), $crawler->text());

		$crawler = $this->request('GET', 'ucp.php?i=groups&mode=manage&action=edit&g=5&sid=' . $this->sid);
		$this->assert_response_success();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$values = $form->getValues();
		$this->assertContains('AA0000', $values['group_colour']);
		$form['group_colour']->setValue('AA0000v');
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('GROUP_UPDATED'), $crawler->text());

		$crawler = $this->request('GET', 'ucp.php?i=groups&mode=manage&action=edit&g=5&sid=' . $this->sid);
		$this->assert_response_success();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$values = $form->getValues();
		$this->assertContains('AA0000', $values['group_colour']);
		$form['group_colour']->setValue('vAA0000');
		$crawler = $this->client->submit($form);
		$this->assertContains($this->lang('GROUP_UPDATED'), $crawler->text());

		$crawler = $this->request('GET', 'ucp.php?i=groups&mode=manage&action=edit&g=5&sid=' . $this->sid);
		$this->assert_response_success();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$values = $form->getValues();
		$this->assertContains('vAA000', $values['group_colour']);
	}
}
