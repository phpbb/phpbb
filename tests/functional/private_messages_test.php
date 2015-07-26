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
class phpbb_functional_private_messages_test extends phpbb_functional_test_case
{
	public function test_setup_config()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?sid={$this->sid}&i=board&mode=message");

		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();

		// Set the maximum number of private messages per folder to 1
		$values['config[pm_max_msgs]'] = 1;

		$form->setValues($values);

		$crawler = self::submit($form);
		$this->assertContains($this->lang('CONFIG_UPDATED'), $crawler->filter('.successbox')->text());
	}

	public function test_inbox_full()
	{
		$this->login();
		$message_id = $this->create_private_message('Test private message #1', 'This is a test private message sent by the testing framework.', array(2));

		$crawler = self::request('GET', "ucp.php?i=pm&mode=view&sid{$this->sid}&p={$message_id}");
		$this->assertContains($this->lang('UCP_PM_VIEW'), $crawler->filter('html')->text());

		$message_id = $this->create_private_message('Test private message #2', 'This is a test private message sent by the testing framework.', array(2));

		$crawler = self::request('GET', "ucp.php?i=pm&mode=view&sid{$this->sid}&p={$message_id}");
		$this->assertContains($this->lang('NO_AUTH_READ_HOLD_MESSAGE'), $crawler->filter('html')->text());
	}

	public function test_restore_config()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?sid={$this->sid}&i=board&mode=message");

		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();

		$values['config[pm_max_msgs]'] = 50;

		$form->setValues($values);

		$crawler = self::submit($form);
		$this->assertContains($this->lang('CONFIG_UPDATED'), $crawler->filter('.successbox')->text());
	}

	public function test_quote_post()
	{
		$text = 'Test post';

		$this->login();
		$topic = $this->create_topic(2, 'Test Topic 1', 'Test topic');
		$post  = $this->create_post(2, $topic['topic_id'], 'Re: Test Topic 1', $text);

		$expected = '(\\[quote=admin post_id=' . $post['post_id'] . ' time=\\d+ user_id=2\\]' . $text . '\\[/quote\\])';

		$crawler = self::request('GET', 'ucp.php?i=pm&mode=compose&action=quotepost&p=' . $post['post_id'] . '&sid=' . $this->sid);

		$this->assertRegexp($expected, $crawler->filter('textarea#message')->text());
	}

	public function test_quote_pm()
	{
		$text     = 'This is a test private message sent by the testing framework.';
		$expected = "(\\[quote=admin time=\\d+ user_id=2\\]\n" . $text . "\n\\[/quote\\])";

		$this->login();
		$message_id = $this->create_private_message('Test', $text, array(2));

		$crawler = self::request('GET', 'ucp.php?i=pm&mode=compose&action=quote&p=' . $message_id . '&sid=' . $this->sid);

		$this->assertRegexp($expected, $crawler->filter('textarea#message')->text());
	}

	public function test_quote_forward()
	{
		$text     = 'This is a test private message sent by the testing framework.';
		$expected = "[quote=admin]\n" . $text . "\n[/quote]";

		$this->login();
		$message_id = $this->create_private_message('Test', $text, array(2));

		$crawler = self::request('GET', 'ucp.php?i=pm&mode=compose&action=forward&f=0&p=' . $message_id . '&sid=' . $this->sid);

		$this->assertContains($expected, $crawler->filter('textarea#message')->text());
	}
}
