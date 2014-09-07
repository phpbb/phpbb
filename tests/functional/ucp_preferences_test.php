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
class phpbb_functional_ucp_preferences_test extends phpbb_functional_test_case
{
	public function test_submitting_preferences_view()
	{
		$this->add_lang('ucp');
		$this->login();

		$crawler = self::request('GET', 'ucp.php?i=ucp_prefs&mode=view');
		$this->assertContainsLang('UCP_PREFS_VIEW', $crawler->filter('#cp-main h2')->text());

		$form = $crawler->selectButton('Submit')->form(array(
			'topic_sk'	=> 'a',
			'topic_sd'	=> 'a',
			'topic_st'	=> '1',
			'post_sk'	=> 'a',
			'post_sd'	=> 'a',
			'post_st'	=> '1',
		));

		$crawler = self::submit($form);
		$this->assertContainsLang('PREFERENCES_UPDATED', $crawler->filter('#message')->text());
	}

	public function test_submitting_invalid_preferences_view()
	{
		$this->add_lang('ucp');
		$this->login();

		$crawler = self::request('GET', 'ucp.php?i=ucp_prefs&mode=view');
		$this->assertContainsLang('UCP_PREFS_VIEW', $crawler->filter('#cp-main h2')->text());
		$form = $crawler->selectButton('Submit')->form();

		if (!method_exists($form, 'disableValidation'))
		{
			$this->markTestIncomplete('The crawler cannot select invalid values, until Symfony 2.4!');
		}

		$form = $form->disableValidation();
		$form['topic_sk']->select('z');
		$form['topic_sd']->select('z');
		$form['topic_st']->select('test');
		$form['post_sk']->select('z');
		$form['post_sd']->select('z');
		$form['post_st']->select('test');

		$crawler = self::submit($form);
		$this->assertContainsLang('WRONG_DATA_POST_SD', $crawler->filter('#cp-main')->text());
		$this->assertContainsLang('WRONG_DATA_POST_SK', $crawler->filter('#cp-main')->text());
		$this->assertContainsLang('WRONG_DATA_TOPIC_SD', $crawler->filter('#cp-main')->text());
		$this->assertContainsLang('WRONG_DATA_TOPIC_SK', $crawler->filter('#cp-main')->text());
	}

	public function test_read_preferences_view()
	{
		$this->add_lang('ucp');
		$this->login();

		$crawler = self::request('GET', 'ucp.php?i=ucp_prefs&mode=view');
		$this->assertContainsLang('UCP_PREFS_VIEW', $crawler->filter('#cp-main h2')->text());
		$form = $crawler->selectButton('Submit')->form();

		$this->assertEquals('a', $form->get('topic_sk')->getValue());
		$this->assertEquals('a', $form->get('topic_sd')->getValue());
		$this->assertEquals('1', $form->get('topic_st')->getValue());
		$this->assertEquals('a', $form->get('post_sk')->getValue());
		$this->assertEquals('a', $form->get('post_sd')->getValue());
		$this->assertEquals('1', $form->get('post_st')->getValue());
	}
}
