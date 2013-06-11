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
class phpbb_functional_report_post_captcha_test extends phpbb_functional_test_case
{
	public function test_user_report_post()
	{
		$this->login();
		$crawler = self::request('GET', 'report.php?f=2&p=1');
		$this->assertNotContains($this->lang('CONFIRM_CODE'), $crawler->filter('html')->text());
	}

	public function test_guest_report_post()
	{
		$this->enable_reporting_guest();
		$crawler = self::request('GET', 'report.php?f=2&p=1');
		$this->assertContains($this->lang('CONFIRM_CODE'), $crawler->filter('html')->text());
	}

	protected function enable_reporting_guest()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=permissions&icat=12&mode=setting_group_local&sid=' . $this->sid);
		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();
		$values["group_id[0]"] = 1;
		$form->setValues($values);
		$crawler = self::submit($form);

		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();
		$values["forum_id"] = 2;
		$form->setValues($values);
		$crawler = self::submit($form);

		$this->add_lang('acp/permissions');
		$form = $crawler->selectButton($this->lang('APPLY_ALL_PERMISSIONS'))->form();
		$values = $form->getValues();
		$values["setting[1][2][f_report]"] = 1;
		$form->setValues($values);
		$crawler = self::submit($form);

		$crawler = self::request('GET', 'ucp.php?mode=logout&sid=' . $this->sid);
	}
}
