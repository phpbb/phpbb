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
class phpbb_functional_ucp_profile_test extends phpbb_functional_test_case
{
	public function test_submitting_profile_info()
	{
		$this->add_lang('ucp');
		$this->login();

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$this->assertContainsLang('UCP_PROFILE_PROFILE_INFO', $crawler->filter('#cp-main h2')->text());

		$form = $crawler->selectButton('Submit')->form(array(
			'pf_phpbb_location'	=> 'Bertie´s Empire',
		));
		$crawler = self::submit($form);
		$this->assertContainsLang('PROFILE_UPDATED', $crawler->filter('#message')->text());

		$crawler = self::request('GET', 'ucp.php?i=ucp_profile&mode=profile_info');
		$form = $crawler->selectButton('Submit')->form();
		$this->assertEquals('Bertie´s Empire', $form->get('pf_phpbb_location')->getValue());
	}
}
