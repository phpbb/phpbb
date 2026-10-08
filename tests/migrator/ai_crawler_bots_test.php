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

class phpbb_migrator_ai_crawler_bots_test extends phpbb_database_test_case
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\db\migration\data\v33x\ai_crawler_bots */
	protected $migration;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/ai_crawler_bots.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $db, $config, $phpbb_dispatcher, $phpbb_container, $phpbb_root_path, $phpEx;

		$db = $this->db = $this->new_dbal();
		$config = new \phpbb\config\config([
			'board_timezone'		=> 'UTC',
			'default_dateformat'	=> 'D M d, Y g:i a',
			'default_lang'			=> 'en',
			'default_style'			=> 1,
			'new_member_post_limit'	=> 0,
			'newest_user_id'		=> 0,
		]);
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('cache.driver', new phpbb_mock_cache());
		$phpbb_container->set('notification_manager', $this->getMockBuilder('\phpbb\notification\manager')
			->disableOriginalConstructor()
			->getMock()
		);

		$factory = new \phpbb\db\tools\factory();

		$this->migration = new \phpbb\db\migration\data\v33x\ai_crawler_bots(
			$config,
			$this->db,
			$factory->get($this->db),
			$phpbb_root_path,
			$phpEx,
			'phpbb_'
		);
	}

	public function test_existing_agent_is_not_added_again()
	{
		$this->migration->add_ai_crawlers();

		$sql = 'SELECT bot_name, bot_agent, user_id
			FROM phpbb_bots
			ORDER BY bot_id ASC';
		$result = $this->db->sql_query($sql);
		$bots = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$bot_names = array_column($bots, 'bot_name');
		$bot_agents = array_map('strtolower', array_column($bots, 'bot_agent'));

		// The administrator's bot with the same agent string is kept as is
		$this->assertSame('Some Bot', $bots[0]['bot_name']);
		$this->assertSame('gptbot/', $bots[0]['bot_agent']);
		$this->assertSame(10, (int) $bots[0]['user_id']);

		// No second bot for the already configured agent string
		$this->assertNotContains('GPTBot [Bot]', $bot_names);
		$this->assertSame(1, count(array_keys($bot_agents, 'gptbot/', true)));

		// The other AI crawlers are still added to the AI crawlers group
		$this->assertContains('ClaudeBot [Bot]', $bot_names);
		$this->assertSame(count($bot_agents), count(array_unique($bot_agents)));

		$sql = 'SELECT user_id, group_id
			FROM phpbb_users
			WHERE ' . $this->db->sql_build_array('SELECT', ['username_clean' => utf8_clean_string('GPTBot [Bot]')]);
		$result = $this->db->sql_query($sql);
		$this->assertFalse($this->db->sql_fetchrow($result));
		$this->db->sql_freeresult($result);

		$sql = 'SELECT user_id, group_id
			FROM phpbb_users
			WHERE ' . $this->db->sql_build_array('SELECT', ['username_clean' => utf8_clean_string('ClaudeBot [Bot]')]);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertSame(8, (int) $row['group_id']);
	}
}
