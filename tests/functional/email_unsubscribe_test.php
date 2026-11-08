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
class phpbb_functional_email_unsubscribe_test extends phpbb_functional_test_case
{
	protected const EMAIL_METHOD = 'notification.method.email';

	protected const TOPIC_ID = 1;

	protected const USER_NOTIFICATIONS_TABLE = 'phpbb_user_notifications';

	/** @var array */
	protected $user_row;

	/** @var string */
	protected $username;

	protected function setUp(): void
	{
		parent::setUp();

		$this->add_lang('ucp');

		$this->username = 'unsub' . substr(md5($this->name() . mt_rand()), 0, 10);
		$user_id = $this->create_user($this->username, $this->username . '@example.com');
		$this->user_row = ['user_id' => (int) $user_id, 'user_email' => $this->username . '@example.com'];

		$db = $this->get_db();
		foreach (['notification.type.post', 'notification.type.quote'] as $item_type)
		{
			$db->sql_query('DELETE FROM ' . self::USER_NOTIFICATIONS_TABLE . ' WHERE user_id = ' . (int) $user_id . " AND item_type = '$item_type'");
			$db->sql_query('INSERT INTO ' . self::USER_NOTIFICATIONS_TABLE . ' ' . $db->sql_build_array('INSERT', [
				'item_type'	=> $item_type,
				'item_id'	=> 0,
				'user_id'	=> (int) $user_id,
				'method'	=> self::EMAIL_METHOD,
				'notify'	=> 1,
			]));
		}
		$db->sql_query('INSERT INTO ' . TOPICS_WATCH_TABLE . ' ' . $db->sql_build_array('INSERT', [
			'topic_id'		=> self::TOPIC_ID,
			'user_id'		=> (int) $user_id,
			'notify_status'	=> NOTIFY_YES,
		]));
		$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_allow_massemail = 1 WHERE user_id = ' . (int) $user_id);
	}

	protected function get_email_method(): \phpbb\notification\method\email
	{
		$db = $this->get_db();
		$result = $db->sql_query("SELECT config_value FROM " . CONFIG_TABLE . " WHERE config_name = 'email_unsubscribe_secret'");
		$secret = $db->sql_fetchfield('config_value');
		$db->sql_freeresult($result);

		$this->assertNotEmpty($secret, 'The installer must generate the unsubscribe secret');

		return new \phpbb\notification\method\email(
			$this->createMock(\phpbb\user_loader::class),
			$this->createMock(\phpbb\user::class),
			new \phpbb\config\config(['email_unsubscribe_secret' => $secret]),
			$this->createMock(\phpbb\db\driver\driver_interface::class),
			'',
			'php',
			'phpbb_notification_emails',
			$this->createMock(\phpbb\di\service_collection::class),
			$this->createMock(\phpbb\routing\router::class)
		);
	}

	protected function get_link(array $overrides = []): string
	{
		$params = $this->get_email_method()->get_unsubscribe_params($this->user_row, 'notification.type.post', 'topic', self::TOPIC_ID);

		return 'index.php/notifications/unsubscribe?' . http_build_query(array_merge($params, $overrides));
	}

	protected function is_watching(): bool
	{
		$db = $this->get_db();
		$result = $db->sql_query('SELECT notify_status FROM ' . TOPICS_WATCH_TABLE . ' WHERE topic_id = ' . self::TOPIC_ID . ' AND user_id = ' . $this->user_row['user_id']);
		$status = $db->sql_fetchfield('notify_status');
		$db->sql_freeresult($result);

		return $status !== false;
	}

	protected function get_notify(string $item_type): int
	{
		$db = $this->get_db();
		$result = $db->sql_query('SELECT notify FROM ' . self::USER_NOTIFICATIONS_TABLE . ' WHERE user_id = ' . $this->user_row['user_id'] . " AND item_type = '$item_type' AND method = '" . self::EMAIL_METHOD . "'");
		$notify = $db->sql_fetchfield('notify');
		$db->sql_freeresult($result);

		return (int) $notify;
	}

	protected function get_massemail(): int
	{
		$db = $this->get_db();
		$result = $db->sql_query('SELECT user_allow_massemail FROM ' . USERS_TABLE . ' WHERE user_id = ' . $this->user_row['user_id']);
		$massemail = $db->sql_fetchfield('user_allow_massemail');
		$db->sql_freeresult($result);

		return (int) $massemail;
	}

	public function test_get_shows_page_and_changes_nothing(): void
	{
		$crawler = self::request('GET', $this->get_link());
		$response = self::$client->getResponse();

		$this->assertCount(3, $crawler->filter('input[name="scope"]'));
		$this->assertStringContainsString('no-store', (string) $response->getHeader('Cache-Control'));
		$this->assertSame('no-referrer', $response->getHeader('Referrer-Policy'));
		$this->assertSame('noindex', $response->getHeader('X-Robots-Tag'));
		$this->assertStringNotContainsString($this->user_row['user_email'], $crawler->filter('#email_unsubscribe')->text());

		$this->assertTrue($this->is_watching());
		$this->assertSame(1, $this->get_notify('notification.type.post'));
		$this->assertSame(1, $this->get_massemail());
	}

	public function test_item_option_is_hidden_if_not_watching(): void
	{
		$this->get_db()->sql_query('DELETE FROM ' . TOPICS_WATCH_TABLE . ' WHERE user_id = ' . $this->user_row['user_id']);

		$crawler = self::request('GET', $this->get_link());

		$this->assertCount(2, $crawler->filter('input[name="scope"]'));
		$this->assertCount(0, $crawler->filter('input[name="scope"][value="item"]'));
	}

	public function test_submit_without_scope_changes_nothing(): void
	{
		self::$client->request('POST', self::$root_url . $this->get_link(), ['unsubscribe' => 'Unsubscribe']);

		$this->assertSame(200, self::$client->getResponse()->getStatusCode());
		$this->assertTrue($this->is_watching());
		$this->assertSame(1, $this->get_notify('notification.type.post'));
		$this->assertSame(1, $this->get_massemail());
	}

	public function test_invalid_link(): void
	{
		self::$client->request('GET', self::$root_url . $this->get_link(['hash' => str_repeat('0', 64)]));

		$this->assertSame(404, self::$client->getResponse()->getStatusCode());
		$this->assertContainsLang('EMAIL_UNSUBSCRIBE_LINK_INVALID', self::$client->getResponse()->getContent());
	}

	public function test_post_with_invalid_link_changes_nothing(): void
	{
		self::$client->request('POST', self::$root_url . $this->get_link(['hash' => str_repeat('0', 64)]), ['List-Unsubscribe' => 'One-Click']);

		$this->assertSame(404, self::$client->getResponse()->getStatusCode());
		$this->assertSame(1, $this->get_notify('notification.type.post'));
	}

	public function test_link_stops_working_after_email_change(): void
	{
		$link = $this->get_link();

		$this->get_db()->sql_query('UPDATE ' . USERS_TABLE . " SET user_email = 'changed@example.com' WHERE user_id = " . $this->user_row['user_id']);

		self::$client->request('GET', self::$root_url . $link);

		$this->assertSame(404, self::$client->getResponse()->getStatusCode());
	}

	public function test_one_click_stops_type_only_and_needs_no_cookies(): void
	{
		self::$client->getCookieJar()->clear();
		self::$client->request('POST', self::$root_url . $this->get_link(), ['List-Unsubscribe' => 'One-Click']);

		$this->assertSame(200, self::$client->getResponse()->getStatusCode());
		$this->assertSame(0, $this->get_notify('notification.type.post'));
		$this->assertSame(1, $this->get_notify('notification.type.quote'));
		$this->assertSame(1, $this->get_massemail());
		$this->assertTrue($this->is_watching());
	}

	public function test_unsubscribe_from_item(): void
	{
		$crawler = self::request('GET', $this->get_link());
		$form = $crawler->selectButton($this->lang('EMAIL_UNSUBSCRIBE_SUBMIT'))->form(['scope' => 'item']);
		$crawler = self::submit($form);

		$this->assertContainsLang('EMAIL_UNSUBSCRIBE_DONE', $crawler->text());
		$this->assertFalse($this->is_watching());
		$this->assertSame(1, $this->get_notify('notification.type.post'));
	}

	public function test_unsubscribe_from_type(): void
	{
		$crawler = self::request('GET', $this->get_link());
		$form = $crawler->selectButton($this->lang('EMAIL_UNSUBSCRIBE_SUBMIT'))->form(['scope' => 'type']);
		self::submit($form);

		$this->assertSame(0, $this->get_notify('notification.type.post'));
		$this->assertSame(1, $this->get_notify('notification.type.quote'));
		$this->assertTrue($this->is_watching());
	}

	public function test_unsubscribe_from_all(): void
	{
		$crawler = self::request('GET', $this->get_link());
		$form = $crawler->selectButton($this->lang('EMAIL_UNSUBSCRIBE_SUBMIT'))->form();
		$this->assertFalse($form['scope']->hasValue(), 'No scope may be preselected');
		$form['scope'] = 'all';
		self::submit($form);

		$this->assertSame(0, $this->get_notify('notification.type.post'));
		$this->assertSame(0, $this->get_notify('notification.type.quote'));
		$this->assertSame(0, $this->get_massemail());
		$this->assertTrue($this->is_watching());
	}

	public function test_user_can_turn_emails_back_on_in_ucp(): void
	{
		$crawler = self::request('GET', $this->get_link());
		$crawler = self::submit($crawler->selectButton($this->lang('EMAIL_UNSUBSCRIBE_SUBMIT'))->form(['scope' => 'type']));

		$this->assertSame(0, $this->get_notify('notification.type.post'));
		$this->assertStringContainsString('mode=notification_options', $crawler->selectLink($this->lang('EMAIL_UNSUBSCRIBE_SETTINGS'))->attr('href'));

		$this->login($this->username);

		$crawler = self::request('GET', 'ucp.php?i=ucp_notifications&mode=notification_options');
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$this->assertFalse($form['notification.type.post_notification.method.email']->hasValue());
		$form['notification.type.post_notification.method.email']->tick();
		self::submit($form);

		$this->assertSame(1, $this->get_notify('notification.type.post'));
	}

	public function test_inactive_user_can_unsubscribe(): void
	{
		$this->get_db()->sql_query('UPDATE ' . USERS_TABLE . ' SET user_type = ' . USER_INACTIVE . ' WHERE user_id = ' . $this->user_row['user_id']);

		self::$client->request('POST', self::$root_url . $this->get_link(), ['List-Unsubscribe' => 'One-Click']);

		$this->assertSame(200, self::$client->getResponse()->getStatusCode());
		$this->assertSame(0, $this->get_notify('notification.type.post'));
	}

	public function test_actions_are_logged_for_the_user(): void
	{
		$crawler = self::request('GET', $this->get_link());
		self::submit($crawler->selectButton($this->lang('EMAIL_UNSUBSCRIBE_SUBMIT'))->form(['scope' => 'all']));

		$db = $this->get_db();
		$result = $db->sql_query('SELECT log_operation FROM ' . LOG_TABLE . ' WHERE reportee_id = ' . $this->user_row['user_id'] . " AND log_operation = 'LOG_USER_EMAIL_UNSUBSCRIBE_ALL'");
		$operation = $db->sql_fetchfield('log_operation');
		$db->sql_freeresult($result);

		$this->assertSame('LOG_USER_EMAIL_UNSUBSCRIBE_ALL', $operation);
	}

	public function test_disabled_board_shows_its_message(): void
	{
		$link = $this->get_link();

		$db = $this->get_db();
		$db->sql_query('UPDATE ' . CONFIG_TABLE . " SET config_value = '1' WHERE config_name = 'board_disable'");
		$this->purge_cache();

		try
		{
			self::$client->request('POST', self::$root_url . $link, ['List-Unsubscribe' => 'One-Click']);
			$this->assertSame(1, $this->get_notify('notification.type.post'));
		}
		finally
		{
			$db->sql_query('UPDATE ' . CONFIG_TABLE . " SET config_value = '0' WHERE config_name = 'board_disable'");
			$this->purge_cache();
		}
	}
}
