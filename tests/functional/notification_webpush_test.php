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
class phpbb_functional_notification_webpush_test extends phpbb_functional_test_case
{
	public function test_acp_module()
	{
		$this->login();
		$this->admin_login();

		$this->add_lang(['acp/board', 'acp/common']);

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=webpush&sid=' . $this->sid);

		$this->assertContainsLang('ACP_WEBPUSH_SETTINGS', $crawler->filter('div.main > h1')->text());
		$this->assertContainsLang('ACP_WEBPUSH_SETTINGS_EXPLAIN', $crawler->filter('div.main > p')->text());
		$this->assertContainsLang('WEBPUSH_GENERATE_VAPID_KEYS', $crawler->filter('input[type="button"]')->attr('value'));

		$form_data = [
			'config[webpush_enable]'	=> 1,
			'config[webpush_vapid_public]'	=> 'BDnYSJHVZBxq834LqDGr893IfazEez7q-jYH2QBNlT0ji2C9UwGosiqz8Dp_ZN23lqAngBZyRjXVWF4ZLA8X2zI',
			'config[webpush_vapid_private]'	=> 'IE5OYlmfWsMbBU1lzvr0bxrxVAXIteSkAnwGlZIhmRk',
			'config[webpush_method_default_enable]'	=> 1,
			'config[webpush_dropdown_subscribe]'	=> 1,
		];
		$form = $crawler->selectButton('submit')->form($form_data);
		$crawler = self::submit($form);
		$this->assertStringContainsString($this->lang('CONFIG_UPDATED'), $crawler->filter('.successbox')->text());

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=webpush&sid=' . $this->sid);

		foreach ($form_data as $config_name => $config_value)
		{
			$config_value = ($config_name === 'config[webpush_vapid_private]') ? '********' : $config_value;
			$this->assertEquals($config_value, $crawler->filter('input[name="' . $config_name . '"]')->attr('value'));
		}
	}

	public function test_ucp_module()
	{
		$this->login();
		$this->admin_login();

		$this->add_lang('ucp');

		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&mode=notification_options');

		$this->assertContainsLang('NOTIFY_WEBPUSH_ENABLE', $crawler->filter('label[for="subscribe_webpush"]')->text());
		$this->assertContainsLang('NOTIFICATION_METHOD_WEBPUSH', $crawler->filter('th.mark')->eq(2)->text());

		// Assert checkbox is checked
		$wp_list = $crawler->filter('.table1');
		$this->assert_checkbox_is_checked($wp_list, 'notification.type.bookmark_notification.method.webpush');
		$this->assert_checkbox_is_checked($wp_list, 'notification.type.mention_notification.method.webpush');
		$this->assert_checkbox_is_checked($wp_list, 'notification.type.post_notification.method.webpush');
		$this->assert_checkbox_is_checked($wp_list, 'notification.type.quote_notification.method.webpush');
		$this->assert_checkbox_is_checked($wp_list, 'notification.type.topic_notification.method.webpush');
		$this->assert_checkbox_is_checked($wp_list, 'notification.type.forum_notification.method.webpush');
		$this->assert_checkbox_is_checked($wp_list, 'notification.type.group_request_notification.method.webpush');
		$this->assert_checkbox_is_checked($wp_list, 'notification.type.pm_notification.method.webpush');
		$this->assert_checkbox_is_checked($wp_list, 'notification.type.report_pm_closed_notification.method.webpush');
		$this->assert_checkbox_is_checked($wp_list, 'notification.type.report_post_closed_notification.method.webpush');

		$this->set_acp_option('webpush_method_default_enable', 0);

		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&mode=notification_options');

		// Assert checkbox is unchecked
		$wp_list = $crawler->filter('.table1');
		$this->assert_checkbox_is_unchecked($wp_list, 'notification.type.bookmark_notification.method.webpush');
		$this->assert_checkbox_is_unchecked($wp_list, 'notification.type.mention_notification.method.webpush');
		$this->assert_checkbox_is_unchecked($wp_list, 'notification.type.post_notification.method.webpush');
		$this->assert_checkbox_is_unchecked($wp_list, 'notification.type.quote_notification.method.webpush');
		$this->assert_checkbox_is_unchecked($wp_list, 'notification.type.topic_notification.method.webpush');
		$this->assert_checkbox_is_unchecked($wp_list, 'notification.type.forum_notification.method.webpush');
		$this->assert_checkbox_is_unchecked($wp_list, 'notification.type.group_request_notification.method.webpush');
		$this->assert_checkbox_is_unchecked($wp_list, 'notification.type.pm_notification.method.webpush');
		$this->assert_checkbox_is_unchecked($wp_list, 'notification.type.report_pm_closed_notification.method.webpush');
		$this->assert_checkbox_is_unchecked($wp_list, 'notification.type.report_post_closed_notification.method.webpush');
	}

	public function test_dropdown_subscribe_button()
	{
		$this->login();
		$this->admin_login();

		// Assert subscribe dropdown is present
		$crawler = self::request('GET', 'index.php');
		$this->assertCount(1, $crawler->filter('.webpush-subscribe'));
		$this->assertContainsLang('NOTIFY_WEB_PUSH_SUBSCRIBE', $crawler->filter('.webpush-subscribe #subscribe_webpush')->text());
		$this->assertContainsLang('NOTIFY_WEB_PUSH_SUBSCRIBED', $crawler->filter('.webpush-subscribe #unsubscribe_webpush')->text());

		// Assert subscribe button is not displayed in UCP when dropdown subscribe is present
		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&mode=notification_options');
		$this->assertCount(0, $crawler->filter('.webpush-subscribe'));

		$this->set_acp_option('webpush_dropdown_subscribe', 0);

		// Assert subscribe dropdown is not present by default
		$crawler = self::request('GET', 'index.php');
		$this->assertCount(0, $crawler->filter('.webpush-subscribe'));
	}

	protected function set_acp_option($option, $value)
	{
		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=webpush&sid=' . $this->sid);
		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();
		$values["config[{$option}]"] = $value;
		$form->setValues($values);
		$crawler = self::submit($form);
		$this->assertEquals(1, $crawler->filter('.successbox')->count());
	}
}
