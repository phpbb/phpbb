<?php


namespace phpbb\tests\unit\ban;

use phpbb\ban\exception\invalid_length_exception;
use phpbb\ban\exception\no_valid_emails_exception;
use phpbb\ban\exception\no_valid_ips_exception;
use phpbb\ban\exception\no_valid_users_exception;
use phpbb\ban\exception\type_not_found_exception;

require_once __DIR__ . '/../test_framework/phpbb_session_test_case.php';

class ban_manager_test extends \phpbb_session_test_case
{
	protected $ban_manager;

	protected $phpbb_container;


	protected function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/sessions_banlist.xml');
	}

	public function setUp(): void
	{
		parent::setUp();

		global $config, $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		$language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$user = new \phpbb\user($language, '\phpbb\datetime');
		$user->data['user_id'] = 2;
		$user->data['user_email'] = 'foo@bar.com';
		$user->data['user_timezone'] = 0;
		$config = new \phpbb\config\config([]);
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$phpbb_container = new \phpbb_mock_container_builder();
		$ban_type_email = new \phpbb\ban\type\email($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_user = new \phpbb\ban\type\user($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_ip = new \phpbb\ban\type\ip($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$phpbb_container->set('ban.type.email', $ban_type_email);
		$phpbb_container->set('ban.type.user', $ban_type_user);
		$phpbb_container->set('ban.type.ip', $ban_type_ip);
		$collection = new \phpbb\di\service_collection($phpbb_container);
		$collection->add('ban.type.email');
		$collection->add('ban.type.user');
		$collection->add('ban.type.ip');
		$phpbb_log = new \phpbb\log\dummy();

		$this->ban_manager = new \phpbb\ban\manager($collection, new \phpbb\cache\driver\dummy(), $this->db, $language, $phpbb_log, $user, 'phpbb_bans', 'phpbb_users');
		$phpbb_container->set('ban.manager', $this->ban_manager);
		$this->phpbb_container = $phpbb_container;
	}

	public function data_check_ban(): array
	{
		return [
			[
				[],
				false
			],
			[
				['user_ip' => '127.0.0.1'],
				[
					'item'		=> '127.0.0.1',
					'end'		=> '0',
					'reason'	=> '1',
					'mode'		=> 'ip',
				],
			],
			[
				['user_ip' => '10.0.0.1'], // first IP for 10.0.0.1/28 range
				[
					'item'		=> '10.0.0.1/28',
					'end'		=> '0',
					'reason'	=> '1',
					'mode'		=> 'ip',
				],
			],
			[
				['user_ip' => '10.0.0.14'], // last IP for 10.0.0.1/28 range
				[
					'item'		=> '10.0.0.1/28',
					'end'		=> '0',
					'reason'	=> '1',
					'mode'		=> 'ip',
				],
			],
			[
				['user_ip' => '10.0.0.15'], // first IP outside 10.0.0.1/28 range
				[
					'item'		=> '10.0.0.1/28',
					'end'		=> '0',
					'reason'	=> '1',
					'mode'		=> 'ip',
				],
			],
			[
				['user_ip' => '2001:4860:4860::8888'], // first IP in 2001:4860:4860::8888/12 range
				[
					'item'		=> '2001:4860:4860::8888/12',
					'end'		=> '0',
					'reason'	=> '1',
					'mode'		=> 'ip',
				],
			],
			[
				['user_ip' => '200F:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF'], // last IP in 2001:4860:4860::8888/12 range
				[
					'item'		=> '2001:4860:4860::8888/12',
					'end'		=> '0',
					'reason'	=> '1',
					'mode'		=> 'ip',
				],
			],
			[
				['user_ip' => '2010:4860:4860::1'], // IP outside the 2001:4860:4860::8888/12 range
				false,
			],
			[
				['user_id'	=> 2],
				false,
			],
			[
				['user_id'	=> 5], // there is only an expired ban
				false,
			],
			[
				['user_id'	=> 4],
				[
					'item'		=> '4',
					'end'		=> '0',
					'reason'	=> '1',
					'mode'		=> 'user',
				],
			],
			[
				['user_email'	=> 'test@phpbb.com'],
				false,
			],
			[
				['user_email'	=> 'bar@example.org'],
				[
					'item'		=> 'bar@example.org',
					'end'		=> '0',
					'reason'	=> '1',
					'mode'		=> 'email',
				],
			],
			[
				['user_email'	=> 'test@foo.bar'],
				[
					'item'		=> '*@foo.bar',
					'end'		=> '0',
					'reason'	=> '1',
					'mode'		=> 'email',
				],
			],
		];
	}

	/**
	 * @dataProvider data_check_ban
	 */
	public function test_check_ban($user_data, $expected)
	{
		$this->assertEquals($expected, $this->ban_manager->check($user_data));
	}

	public function data_get_bans(): array
	{
		return [
			[
				'foo',
				'',
				type_not_found_exception::class
			],
			[
				'ip',
				[
					[
						'ban_id' => '6',
						'ban_userid' => 0,
						'ban_item' => '10.0.0.1/28',
						'ban_start' => '1111',
						'ban_end' => '0',
						'ban_reason' => 'HAHAHA',
						'ban_reason_display' => '1',
						'ban_mode' => 'ip',
					],
					[
						'ban_id' => '2',
						'ban_userid' => 0,
						'ban_item' => '127.0.0.1',
						'ban_start' => '1111',
						'ban_end' => '0',
						'ban_reason' => 'HAHAHA',
						'ban_reason_display' => '1',
						'ban_mode' => 'ip',
					],
					[
						'ban_id' => '3',
						'ban_userid' => 0,
						'ban_item' => '127.1.1.1',
						'ban_start' => '1111',
						'ban_end' => '0',
						'ban_reason' => 'HAHAHA',
						'ban_reason_display' => '1',
						'ban_mode' => 'ip',
					],
					[
						'ban_id' => '7',
						'ban_userid' => 0,
						'ban_item' => '2001:4860:4860::8888/12',
						'ban_start' => '1111',
						'ban_end' => '0',
						'ban_reason' => 'HAHAHA',
						'ban_reason_display' => '1',
						'ban_mode' => 'ip',
					],
				],
			],
			[
				'email',
				[
					[
						'ban_id' => '9',
						'ban_userid' => 0,
						'ban_item' => '*@foo.bar',
						'ban_start' => '1111',
						'ban_end' => '0',
						'ban_reason' => 'HAHAHA',
						'ban_reason_display' => '1',
						'ban_mode' => 'email',
					],
					[
						'ban_id' => '5',
						'ban_userid' => 0,
						'ban_item' => 'bar@example.org',
						'ban_start' => '1111',
						'ban_end' => '0',
						'ban_reason' => 'HAHAHA',
						'ban_reason_display' => '1',
						'ban_mode' => 'email',
					],
				],
			],
			[
				'user',
				[
					[
						'ban_id' => '4',
						'ban_item' => '4',
						'ban_start' => '1111',
						'ban_end' => '0',
						'ban_reason' => 'HAHAHA',
						'ban_reason_display' => '1',
						'ban_mode' => 'user',
						'ban_userid' => 4,
						'user_id' => '4',
						'username' => 'ipv6_user',
						'username_clean' => 'ipv6_user',
						'label' => 'ipv6_user',
					],
				],
			],
		];
	}

	/**
	 * @dataProvider data_get_bans
	 */
	public function test_get_bans($ban_type, $expected, $expected_exception = false)
	{
		if ($expected_exception !== false)
		{
			$this->expectException($expected_exception);
		}

		$actual = $this->ban_manager->get_bans($ban_type);
		// Sort both arrays by ban_item to be synced
		if (is_array($expected) && !empty($actual))
		{
			usort($expected, function($a, $b)
			{
				return strcmp($a['ban_item'], $b['ban_item']) <=> 0;
			}
			);
			usort($actual, function($a, $b)
			{
				return strcmp($a['ban_item'], $b['ban_item']) <=> 0;
			}
			);
		}
		$this->assertEquals($expected, $actual);
	}

	public function data_get_ban_end(): array
	{
		return [
			[
				0,
				20,
				0,
			],
			[
				80, // 1 minute plus 20 seconds
				20,
				1,
			],
			[
				20,
				20,
				-1,
			],
			[
				2 * 86400, // Ban end should be before this time
				20,
				-1,
				'1970-01-02',
			],
			[
				0,
				20,
				-1,
				'1970-01-02-15:30', // wrong format
				invalid_length_exception::class,
			],
		];
	}

	/**
	 * @dataProvider data_get_ban_end
	 */
	public function test_get_ban_end($expected, $ban_start, $length, $end_date = '', $expected_exception = false)
	{
		if ($expected_exception)
		{
			$this->expectException($expected_exception);
		}

		$start_time = new \DateTime();
		$start_time->setTimestamp($ban_start);

		$expected_end = new \DateTime();
		$expected_end->setTimestamp($expected);

		$ban_end = $this->ban_manager->get_ban_end($start_time, $length, $end_date);

		if ($length >= 0 || !$end_date)
		{
			$this->assertEquals($expected_end, $ban_end);
		}
		else
		{
			$this->assertLessThan($expected_end, $ban_end);
			$this->assertGreaterThan($start_time, $ban_end);
		}
	}

	public function test_get_banned_users()
	{
		$banned_users = $this->ban_manager->get_banned_users();
		$this->assertEquals(
			[
				4	=> 0,
				5	=> 0
			],
			$banned_users
		);
	}

	public function test_get_banned_users_own_method()
	{
		global $phpbb_root_path, $phpEx;

		$phpbb_container = new \phpbb_mock_container_builder();
		$ban_type_email = new \phpbb\ban\type\email($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_user = new \phpbb\ban\type\user($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_ip = $this->getMockBuilder(\phpbb\ban\type\ip::class)
			->setConstructorArgs([$this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys'])
			->getMock();
		$ban_type_ip->method('get_banned_users')
			->willReturn([19 => 1234, 20 => 0]);
		$phpbb_container->set('ban.type.email', $ban_type_email);
		$phpbb_container->set('ban.type.user', $ban_type_user);
		$phpbb_container->set('ban.type.ip', $ban_type_ip);
		$collection = new \phpbb\di\service_collection($phpbb_container);
		$collection->add('ban.type.email');
		$collection->add('ban.type.user');
		$collection->add('ban.type.ip');

		$language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$user = new \phpbb\user($language, '\phpbb\datetime');
		$phpbb_log = new \phpbb\log\dummy();

		$ban_manager = new \phpbb\ban\manager($collection, new \phpbb\cache\driver\dummy(), $this->db, $language, $phpbb_log, $user, 'phpbb_bans', 'phpbb_users');

		$this->assertEquals(
			[
				4 => 0,
				5 => 0,
				19 => 1234,
				20 => 0,
			],
			$ban_manager->get_banned_users()
		);

		$ban_type_ip_reflection = new \ReflectionClass($ban_type_ip);
		$get_excluded_reflection = $ban_type_ip_reflection->getMethod('get_excluded');
		$get_excluded_reflection->setAccessible(true);
		$this->assertFalse($get_excluded_reflection->invoke($ban_type_ip));
	}

	public function test_ban_empty_ban_items()
	{
		global $phpbb_root_path, $phpEx;

		$phpbb_container = new \phpbb_mock_container_builder();
		$ban_type_email = new \phpbb\ban\type\email($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_user = new \phpbb\ban\type\user($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_ip = $this->getMockBuilder(\phpbb\ban\type\ip::class)
			->setConstructorArgs([$this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys'])
			->getMock();
		$ban_type_ip->method('prepare_for_storage')
			->willReturn([]);
		$ban_type_ip->method('get_type')
			->willReturn('ip');
		$phpbb_container->set('ban.type.email', $ban_type_email);
		$phpbb_container->set('ban.type.user', $ban_type_user);
		$phpbb_container->set('ban.type.ip', $ban_type_ip);
		$collection = new \phpbb\di\service_collection($phpbb_container);
		$collection->add('ban.type.email');
		$collection->add('ban.type.user');
		$collection->add('ban.type.ip');

		$language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$user = new \phpbb\user($language, '\phpbb\datetime');
		$phpbb_log = new \phpbb\log\dummy();

		$ban_manager = new \phpbb\ban\manager($collection, new \phpbb\cache\driver\dummy(), $this->db, $language, $phpbb_log, $user, 'phpbb_bans', 'phpbb_users');

		$start_time = new \DateTime();
		$start_time->setTimestamp(1000);
		$end_time = new \DateTime();
		$end_time->setTimestamp(0);

		$this->assertFalse($ban_manager->ban(
			'ip',
			['192.168.1.1'],
			$start_time,
			$end_time,
			''
		));
	}

	public function data_test_ban(): array
	{
		return [
			[
				'user',
				['normal_user'],
				1000,
				500, // end before start
				'',
				'',
				false,
				invalid_length_exception::class,
			],
			[
				'foo', // invalid ban type
				['normal_user'],
				1000,
				0, // end before start
				'',
				'',
				false,
				type_not_found_exception::class,
			],
			[
				'user',
				[], // empty user list
				1000,
				0,
				'',
				'',
				false,
				no_valid_users_exception::class,
			],
			[
				'user',
				['founder'], // user same as current user
				1000,
				0,
				'',
				'',
				false,
				no_valid_users_exception::class,
			],
			[
				'user',
				['normal_user'],
				1000,
				0,
				'',
				'',
				true,
			],
			[
				'user',
				['normal_u*'],
				1000,
				0,
				'',
				'',
				true,
			],
			[
				'ip',
				[],
				1000,
				0,
				'',
				'',
				false,
				no_valid_ips_exception::class,
			],
			[
				'ip',
				['192.168.I.1'], // invalid IP
				1000,
				0,
				'',
				'',
				false,
				no_valid_ips_exception::class,
			],
			[
				'ip',
				['192.168.1.1'],
				1000,
				0,
				'',
				'',
				true,
			],
			[
				'email',
				['this_is_not_an_email'],
				1000,
				0,
				'',
				'',
				false,
				no_valid_emails_exception::class
			],
			[
				'email',
				['test@example.com'],
				1000,
				0,
				'',
				'',
				true,
			],
			[
				'email',
				['*@foo.bar'],
				1000,
				0,
				'',
				'',
				true,
			],
			[
				'email',
				['test@example.com', str_repeat('a', 100) . '@example.com'], // one email too long, shouldn't cause any issues though
				1000,
				0,
				'',
				'',
				true,
			],
		];
	}

	/**
	 * @dataProvider data_test_ban
	 */
	public function test_ban($mode, $items, $start, $end, $reason, $display_reason, $expected, $expected_exception = '')
	{
		if ($expected_exception)
		{
			$this->expectException($expected_exception);
		}

		$start_time = new \DateTime();
		$start_time->setTimestamp($start);
		$end_time = new \DateTime();
		$end_time->setTimestamp($end);

		$ban_return = $this->ban_manager->ban($mode, $items, $start_time, $end_time, $reason, $display_reason);

		$this->assertEquals($expected, $ban_return);
	}

	public function test_ban_actual()
	{
		$start_time = new \DateTime();
		$start_time->setTimestamp(1000);
		$end_time = new \DateTime();
		$end_time->setTimestamp(0);

		$ban_return = $this->ban_manager->ban('ip', ['121.122.123.124'], $start_time, $end_time, '', 'because');

		$this->assertTrue($ban_return);

		$this->assertEquals(
			[
				'item'		=> '121.122.123.124',
				'end'		=> 0,
				'reason'	=> 'because',
				'mode'		=> 'ip'
			],
			$this->ban_manager->check(['user_ip' => '121.122.123.124'])
		);
	}

	public function data_test_unban(): array
	{
		return [
			[
				'does_not_exist',
				[10],
				[],
				type_not_found_exception::class
			],
			[
				'user',
				[4],
				[
					[
						'ban_id' => '4',
						'ban_userid' => '4',
						'ban_item' => '4',
						'ban_start' => '1111',
						'ban_end' => '0',
						'ban_reason' => 'HAHAHA',
						'ban_reason_display' => '1',
						'ban_mode' => 'user',
						'user_id' => '4',
						'username' => 'ipv6_user',
						'username_clean' => 'ipv6_user',
						'label' => 'ipv6_user',
					],
				],
			],
		];
	}

	/**
	 * @dataProvider data_test_unban
	 */
	public function test_unban($mode, $items, $expected, $expected_exception = '')
	{
		if ($expected_exception)
		{
			$this->expectException($expected_exception);
		}

		$before_bans = $this->ban_manager->get_bans($mode);

		$this->ban_manager->unban($mode, $items);

		$after_bans = $this->ban_manager->get_bans($mode);

		$ban_diff = array_diff_assoc($before_bans, count($after_bans) ? $after_bans : []);

		$this->assertEquals($expected, $ban_diff);
	}

	public function test_unban_invalid_type()
	{
		$this->expectException(type_not_found_exception::class);

		$this->ban_manager->unban('does_not_exist', []);
	}

	public function test_base_type_methods()
	{
		$ban_type_ip = $this->phpbb_container->get('ban.type.ip');
		$base_type_reflection = new \ReflectionClass(\phpbb\ban\type\base::class);
		$after_unban = $base_type_reflection->getMethod('after_unban');
		$this->assertEquals([], $after_unban->invoke($ban_type_ip, ['items' => ['foo']]));

		$check = $base_type_reflection->getMethod('check');
		$this->assertFalse($check->invoke($ban_type_ip, [], []));
	}

	public function data_get_ban_message(): array
	{
		return [
			[
				[
					'end'	=> 0,
				],
				'foobar',
				'http://foo.bar',
				'You have been <strong>permanently</strong> banned from this board.<br><br>Please contact the <a href="http://foo.bar">Board Administrator</a> for more information.<br><br><em>BAN_TRIGGERED_BY_FOOBAR</em>',
			],
			[
				[
					'end'	=> 1,
				],
				'foobar',
				'http://foo.bar',
				'You have been banned from this board until <strong></strong>.<br><br>Please contact the <a href="http://foo.bar">Board Administrator</a> for more information.<br><br><em>BAN_TRIGGERED_BY_FOOBAR</em>',
			],
			[
				[
					'end'	=> 1,
					'reason'	=> 'just because',
				],
				'foobar',
				'http://foo.bar',
				'You have been banned from this board until <strong></strong>.<br><br>Please contact the <a href="http://foo.bar">Board Administrator</a> for more information.<br><br>Reason given for ban: <strong>just because</strong><br><br><em>BAN_TRIGGERED_BY_FOOBAR</em>',
			],
		];
	}

	/**
	 * @dataProvider data_get_ban_message
	 */
	public function test_get_ban_message($ban_row, $ban_triggered_by, $contact_link, $expected)
	{
		$this->assertEquals($expected, $this->ban_manager->get_ban_message($ban_row, $ban_triggered_by, $contact_link));
	}

	public function test_get_ban_options_user()
	{
		$foo = $this->ban_manager->get_bans('user');

		$this->assertEquals(
			[
				[
					'ban_id'	=> 4,
					'ban_userid' => '4',
					'ban_mode' => 'user',
					'ban_item' => '4',
					'ban_start' => '1111',
					'ban_end' => '0',
					'ban_reason' => 'HAHAHA',
					'ban_reason_display' => '1',
					'user_id' => '4',
					'username' => 'ipv6_user',
					'username_clean' => 'ipv6_user',
					'label' => 'ipv6_user',
				],
			],
			$foo
		);
	}
}
