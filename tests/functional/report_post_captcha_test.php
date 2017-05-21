<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
 * @group functional
 */
class phpbb_functional_report_post_captcha_test extends phpbb_functional_test_case
{
	public function test_guest_report_post()
	{
		$crawler = self::request('GET', 'app.php/post/1/report', array(), false);
		$this->assert_response_html(403);
		$this->add_lang('mcp');
		$this->assertContains($this->lang('USER_CANNOT_REPORT'), $crawler->filter('html')->text());

		$this->set_reporting_guest(1);
		$crawler = self::request('GET', 'app.php/post/1/report');
		$this->assertContains($this->lang('CONFIRM_CODE'), $crawler->filter('html')->text());
		$this->set_reporting_guest(-1);
	}

	public function test_user_report_post()
	{
		$this->login();
		$crawler = self::request('GET', 'app.php/post/1/report');
		$this->assertNotContains($this->lang('CONFIRM_CODE'), $crawler->filter('html')->text());

		$this->add_lang('mcp');
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form);
		$this->assertContains($this->lang('POST_REPORTED_SUCCESS'), $crawler->text());
	}

	protected function set_reporting_guest($report_post_allowed)
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
		$values["setting[1][2][f_report]"] = $report_post_allowed;
		$form->setValues($values);
		$crawler = self::submit($form);

		$crawler = self::request('GET', 'ucp.php?mode=logout&sid=' . $this->sid);
	}
}
