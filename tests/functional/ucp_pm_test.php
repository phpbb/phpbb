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
class phpbb_functional_ucp_pm_test extends phpbb_functional_test_case
{
	public function setUp()
	{
		parent::setUp();
		$this->login();
		$this->admin_login();
	}

	public function test_pm_enabled()
	{
		$crawler = self::request('GET', 'ucp.php');
		$this->assertContainsLang('PRIVATE_MESSAGES', $crawler->filter('html')->text());
	}

	public function test_pm_disabled()
	{
		$this->set_allow_pm(0);
		$crawler = self::request('GET', 'ucp.php');
		$this->assertNotContainsLang('PRIVATE_MESSAGES', $crawler->filter('html')->text());
	}

	protected function set_allow_pm($enable_pm)
	{
		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid . '&i=acp_board&mode=message');

		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();

		$values["config[allow_privmsg]"] = $enable_pm;
		$form->setValues($values);
		$crawler = self::submit($form);
		$this->assertGreaterThan(0, $crawler->filter('.successbox')->count());
	}
}
